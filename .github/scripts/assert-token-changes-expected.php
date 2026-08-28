<?php
/**
 * Prove that every PHP token a reformat changed belongs to a NAMED, INERT class.
 *
 * WHY THIS EXISTS - AND WHY THE STAGE 1 GATE COULD NOT DO IT
 * ----------------------------------------------------------
 * assert-token-stream-unchanged.php makes one claim: "not one PHP token changed."
 * That is the strongest claim available and it is exactly right for Stage 1, whose
 * sniffs only ever move whitespace between tokens.
 *
 * Stage 2 cannot make that claim and is not supposed to. Its whole job is to change
 * tokens - add a trailing comma, requote a string, drop the parentheses off a
 * require_once, merge `else if` into `elseif`. Running the Stage 1 gate against a
 * Stage 2 patch produces a wall of "token count changed" on a CORRECT run.
 *
 * That is not a harmless mismatch. It is the failure mode this project's own
 * CLAUDE.md names: a check that cries wolf gets ignored on the run where it is
 * right. A gate that always fails proves nothing, and it hides the one thing worth
 * knowing - whether some edit in there is NOT one of the expected kinds.
 *
 * So this gate asks the Stage 2 question instead:
 *
 *     Not "did anything change?" but "does EVERY change belong to a class we have
 *     named in advance and can argue is inert?"
 *
 * It walks the before and after token streams in lockstep. While they agree it
 * advances. Where they diverge it tries each rule below in turn; a rule that matches
 * consumes the tokens it explains and records its class. If NO rule matches, that
 * edit is UNCLASSIFIED and the run FAILS - it is never assumed benign.
 *
 * ⛔ IT FAILS CLOSED, DELIBERATELY, LIKE ops/phpcs_stage_planner.py.
 * A sniff that is added to a stage later - by a Dependabot bump to WPCS, say - will
 * produce edits no rule here explains, and this will stop. It will not quietly widen
 * to fit. Adding a class is a human decision with a written justification, and the
 * justification belongs next to the rule.
 *
 * WHAT "INERT" MEANS FOR EACH CLASS
 * ---------------------------------
 *   TRAILING_COMMA       a `,` inserted immediately before the `)` or `]` that
 *                        closes the same construct. PHP has allowed this in array
 *                        literals forever, in calls since 7.3 and in parameter lists
 *                        since 8.0; this repo requires >= 8.3. It changes no value.
 *   REQUOTE              a string literal whose QUOTING changed and whose DECODED
 *                        VALUE did not. The comparison decodes both literals rather
 *                        than comparing bytes, so "a\tb" -> 'a\tb' would NOT pass:
 *                        those are different strings.
 *   KEYWORD_CASE         a reserved keyword token whose spelling differs only by
 *                        ASCII case. PHP keywords are case insensitive. Restricted
 *                        to an explicit keyword allow-list, so a T_STRING - a
 *                        function or constant name - can never slip through here.
 *   INCLUDE_PARENS       `require_once( X );` -> `require_once X;`. Matched at
 *                        STATEMENT level: the parentheses must wrap the entire
 *                        argument up to the terminating `;`, and every token inside
 *                        must survive unchanged. include/require are statements, so
 *                        the parentheses were only ever grouping.
 *   ELSEIF_MERGE         `else` `if` -> `elseif`. Identical semantics; the only
 *                        difference PHP draws is that `else if` is illegal inside
 *                        the alternative `:`/`endif` syntax, which is stricter, not
 *                        looser.
 *   PRE_INCREMENT        `$x++;` -> `++$x;` as a STAND-ALONE statement, verified by
 *                        requiring a statement boundary on both sides. The value of
 *                        the expression differs, but a stand-alone statement discards
 *                        it, so the observable effect is identical.
 *   ADD_SEMICOLON        a `;` inserted immediately before `?>`. PHP treats the close
 *                        tag as an implicit statement terminator, so this makes
 *                        explicit what the engine already did.
 *   DROP_EMPTY_SEMICOLON a `;` removed where it followed another `;` or a `{` or `}`
 *                        - an empty statement, which compiles to nothing.
 *   INLINE_HTML_WS       inline HTML, an open/close tag, or a comment whose content
 *                        is identical once runs of whitespace are collapsed.
 *                        ⚠️ COUNTED AND REPORTED PER FILE, never silent: HTML is
 *                        whitespace insensitive between tokens so the render is
 *                        unchanged, but the BYTES sent to a browser moved and a
 *                        human should know which files those were.
 *
 * ⚠️ WHAT THIS GATE DOES NOT CLAIM. It proves the edits are of the expected kinds.
 * It does not prove the resulting code is correct, that the sniffs were worth
 * applying, or that anything renders. Staging still has to say so.
 *
 * USAGE
 *   php assert-token-changes-expected.php --stage 2 --base <ref>
 *   php assert-token-changes-expected.php --stage 2 --pairs <manifest.json>
 *
 * --base   compares every *.php differing between <ref> and the working tree. Needs
 *          git; this is the mode CI uses.
 * --pairs  takes a JSON array of {"label":..,"before":..,"after":..} file paths and
 *          needs no git or repo at all. This exists so the SAME classifier can be run
 *          from a machine with no PHP by shipping the pairs to a host that has one.
 *          One implementation, two front ends - a second implementation would be a
 *          second thing to drift, and an instrument that re-implements what it
 *          measures fails flatteringly.
 *
 * Exit 0 = every change classified. Exit 1 = at least one was not. Exit 2 = the gate
 * could not do its job, which is never reported as a pass.
 */

declare(strict_types=1);

const TOLERANT_WS = [
    T_COMMENT,
    T_DOC_COMMENT,
    T_INLINE_HTML,
    T_OPEN_TAG,
    T_OPEN_TAG_WITH_ECHO,
    T_CLOSE_TAG,
];

/**
 * Reserved keywords whose case may change. Deliberately an ALLOW-LIST.
 *
 * `true`, `false` and `null` are NOT here and must not be: PHP tokenises them as
 * T_STRING, the same token class as every function and constant name in the
 * codebase. Allowing a T_STRING case change to pass as "keyword case" would let a
 * rename of an actual identifier through. Generic.PHP.LowerCaseConstant is the sniff
 * for those, it is not in Stage 2, and if it is ever added this list must NOT simply
 * grow to meet it.
 */
const CASE_INSENSITIVE_KEYWORDS = [
    T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR,
    T_IF, T_ELSE, T_ELSEIF, T_ENDIF,
    T_WHILE, T_ENDWHILE, T_DO, T_FOR, T_ENDFOR, T_FOREACH, T_ENDFOREACH, T_AS,
    T_SWITCH, T_ENDSWITCH, T_CASE, T_DEFAULT, T_BREAK, T_CONTINUE,
    T_FUNCTION, T_RETURN, T_YIELD, T_FN,
    T_ECHO, T_PRINT, T_EXIT, T_UNSET, T_ISSET, T_EMPTY, T_LIST, T_ARRAY,
    T_NEW, T_CLONE, T_INSTANCEOF, T_CLASS, T_INTERFACE, T_TRAIT, T_EXTENDS,
    T_IMPLEMENTS, T_ABSTRACT, T_FINAL, T_PUBLIC, T_PRIVATE, T_PROTECTED,
    T_STATIC, T_CONST, T_VAR, T_GLOBAL,
    T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE,
    T_NAMESPACE, T_USE, T_TRY, T_CATCH, T_FINALLY, T_THROW,
];

const INCLUDE_KEYWORDS = [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE];

/** How many consecutive equal tokens are needed to trust a resynchronisation. */
const RESYNC_ANCHOR = 6;
/** How far ahead to look for that anchor before giving up on the file. */
const RESYNC_WINDOW = 400;

function fail(string $msg): never
{
    fwrite(STDERR, "ABORT: {$msg}\n");
    exit(2);
}

/** For commands whose OUTPUT IS A LIST, where per-line trailing space is noise. */
function shell(string $cmd, ?int &$status = null): string
{
    $out = [];
    exec($cmd . ' 2>/dev/null', $out, $status);
    return implode("\n", $out);
}

/**
 * Capture a command's stdout BYTE FOR BYTE.
 *
 * ⛔ DO NOT USE shell() TO READ FILE CONTENT. PHP's exec() rtrims every line it
 * captures, and implode("\n") cannot restore a final newline that was never there.
 * Measured on PHP 8.3: "alpha  \nbeta\t\ngamma" comes back as "alpha\nbeta\ngamma".
 *
 * That matters here more than it looks. In --base mode the BEFORE side comes from
 * `git show`, while the AFTER side is read with file_get_contents(). If one is rtrimmed
 * and the other is not, the two front ends DISAGREE ABOUT THE SAME FILE - and the
 * disagreement shows up as an unexplained token change, i.e. a false failure on
 * correct work, in the mode CI uses and not in the mode a human runs locally.
 *
 * This is not hypothetical in this repo: at the time of writing the theme carries 62
 * lines with trailing whitespace (Stage 2's EmbeddedPhp fixer creates them, e.g.
 * `<option value="2-1" ` in features/inc/featured-image-size.php) and 35 files with no
 * final newline. Redirecting to a file and reading it keeps every byte and makes both
 * front ends read through the same code path.
 */
function captureBytes(string $cmd, ?int &$status = null): ?string
{
    $tmp = tempnam(sys_get_temp_dir(), 'bwgate');
    if ($tmp === false) {
        fail('could not create a temporary file to capture command output');
    }
    $ignored = [];
    exec($cmd . ' > ' . escapeshellarg($tmp) . ' 2>/dev/null', $ignored, $status);
    $data = $status === 0 ? file_get_contents($tmp) : null;
    @unlink($tmp);
    return $data === false ? null : $data;
}

/**
 * One token as this gate compares them.
 *
 * @return array<int, array{id:int,name:string,text:string,line:int}>
 */
function lex(string $src, string $label): array
{
    $raw = @token_get_all($src);
    if ($raw === false || $raw === []) {
        fail("could not tokenise {$label}");
    }
    $out = [];
    // token_get_all() returns single-character tokens as bare strings with NO line
    // number. Stamping 0 made every failure report that landed on punctuation say
    // "line ~0", which is the least useful thing a gate can print - a `,` or a `)` is
    // exactly where these diffs diverge. Carry the last known line forward instead.
    $line = 1;
    foreach ($raw as $tok) {
        if (is_string($tok)) {
            $out[] = ['id' => -1, 'name' => 'CHAR', 'text' => $tok, 'line' => $line];
            continue;
        }
        [$id, $text, $tokLine] = $tok;
        $line = (int) $tokLine + substr_count((string) $text, "\n");
        if ($id === T_WHITESPACE) {
            continue;
        }
        $out[] = ['id' => $id, 'name' => token_name($id), 'text' => $text, 'line' => (int) $tokLine];
    }
    return $out;
}

function same(array $a, array $b): bool
{
    return $a['name'] === $b['name'] && $a['text'] === $b['text'];
}

function isChar(?array $t, string $c): bool
{
    return $t !== null && $t['name'] === 'CHAR' && $t['text'] === $c;
}

function at(array $s, int $i): ?array
{
    return $s[$i] ?? null;
}

function collapse(string $s): string
{
    return trim((string) preg_replace('/\s+/', ' ', $s));
}

/** Decode a single-quoted PHP literal to the string the engine would build. */
function decodeSingleQuoted(string $body): string
{
    $bs = chr(92);
    $out = '';
    $n = strlen($body);
    for ($i = 0; $i < $n; $i++) {
        $c = $body[$i];
        if ($c === $bs && $i + 1 < $n) {
            $next = $body[$i + 1];
            if ($next === $bs || $next === "'") {
                $out .= $next;
                $i++;
                continue;
            }
        }
        $out .= $c;
    }
    return $out;
}

/**
 * Decode a double-quoted PHP literal.
 *
 * Only reached for T_CONSTANT_ENCAPSED_STRING, which by definition contains no
 * interpolation - an interpolating string is lexed as several tokens instead.
 * An unrecognised escape keeps its backslash, which is what PHP itself does.
 */
function decodeDoubleQuoted(string $body): string
{
    $bs = chr(92);
    $simple = [
        'n' => "\n", 't' => "\t", 'r' => "\r", 'v' => chr(11),
        'e' => chr(27), 'f' => chr(12), '"' => '"', '$' => '$',
    ];
    $out = '';
    $n = strlen($body);
    for ($i = 0; $i < $n; $i++) {
        $c = $body[$i];
        if ($c !== $bs || $i + 1 >= $n) {
            $out .= $c;
            continue;
        }
        $next = $body[$i + 1];
        if ($next === $bs) {
            $out .= $bs;
            $i++;
            continue;
        }
        if (isset($simple[$next])) {
            $out .= $simple[$next];
            $i++;
            continue;
        }
        if (preg_match('/^x([0-9A-Fa-f]{1,2})/', substr($body, $i + 1), $m)) {
            $out .= chr((int) hexdec($m[1]));
            $i += 1 + strlen($m[0]) - 1;
            continue;
        }
        if (preg_match('/^u\{([0-9A-Fa-f]+)\}/', substr($body, $i + 1), $m)) {
            $out .= mb_chr((int) hexdec($m[1]), 'UTF-8');
            $i += strlen($m[0]);
            continue;
        }
        if (preg_match('/^([0-7]{1,3})/', substr($body, $i + 1), $m)) {
            $out .= chr(octdec($m[1]) & 0xFF);
            $i += strlen($m[1]);
            continue;
        }
        // Unknown escape: PHP keeps the backslash verbatim.
        $out .= $bs;
    }
    return $out;
}

/** @return string|null null when the literal is not a form this gate understands. */
function decodeLiteral(string $lit): ?string
{
    if (strlen($lit) < 2) {
        return null;
    }
    $q = $lit[0];
    if ($q !== "'" && $q !== '"') {
        return null;
    }
    if (substr($lit, -1) !== $q) {
        return null;
    }
    $body = substr($lit, 1, -1);
    return $q === "'" ? decodeSingleQuoted($body) : decodeDoubleQuoted($body);
}

/**
 * Constructs whose closing `)` tolerates a trailing comma, identified by the token
 * immediately BEFORE the opening `(`. An explicit allow-list: anything not named here
 * is refused, so a construct nobody thought about is a failure rather than a pass.
 */
const COMMA_TOLERANT_BEFORE_PAREN = [
    T_ARRAY,        // array( ... )
    T_STRING,       // foo( ... ) and new Foo( ... )
    T_VARIABLE,     // $callable( ... )
    T_FUNCTION,     // function ( ... )
    T_FN,           // fn ( ... )
    T_USE,          // function () use ( ... )
    T_ISSET,        // isset( ... )
    T_UNSET,        // unset( ... )
    T_LIST,         // list( ... )
    T_CLASS,        // new class( ... )
    T_STATIC,       // static function ( ... )
    T_NAME_QUALIFIED,
    T_NAME_FULLY_QUALIFIED,
];

/**
 * Walk back from a closer to its matching opener and answer: is a trailing comma legal
 * inside this construct?
 *
 * Refuses (returns false) unless it can positively identify a comma-tolerant construct.
 */
function commaIsInert(array $A, int $i, array $B, int $j): bool
{
    // 1. Never a SECOND comma. `array( 1, )` -> `array( 1,, )` is fatal, and it is the
    //    reachable case, because the sniff edits arrays that may already end in one.
    $bPrev = at($B, $j - 1);
    if ($bPrev !== null && isChar($bPrev, ',')) {
        return false;
    }
    $aPrev = at($A, $i - 1);
    if ($aPrev !== null && isChar($aPrev, ',')) {
        return false;
    }

    // 2. Find the matching opener by depth, scanning backwards from the closer.
    $closer = $A[$i]['text'];
    $opener = $closer === ')' ? '(' : '[';
    $depth = 0;
    $open = -1;
    for ($k = $i; $k >= 0; $k--) {
        $t = $A[$k];
        if ($t['name'] !== 'CHAR') {
            continue;
        }
        if ($t['text'] === $closer) {
            $depth++;
        } elseif ($t['text'] === $opener) {
            $depth--;
            if ($depth === 0) {
                $open = $k;
                break;
            }
        }
    }
    if ($open < 0) {
        return false;
    }

    // 3. A `...` anywhere at this level makes a trailing comma illegal - both the
    //    variadic parameter `function f( ...$a, )` and the first-class callable
    //    `strlen( ..., )`.
    for ($k = $open + 1; $k < $i; $k++) {
        if ($A[$k]['name'] === 'T_ELLIPSIS') {
            return false;
        }
    }

    $before = at($A, $open - 1);

    if ($closer === ']') {
        // `[` is a short array literal (comma legal) OR array access (comma illegal).
        // It is array ACCESS exactly when the preceding token can end an operand.
        if ($before === null) {
            return true;
        }
        $operandEnd = in_array($before['name'], [
            'T_VARIABLE', 'T_STRING', 'T_CONSTANT_ENCAPSED_STRING',
            'T_NAME_QUALIFIED', 'T_NAME_FULLY_QUALIFIED',
        ], true) || ($before['name'] === 'CHAR' && in_array($before['text'], [')', ']'], true));
        return !$operandEnd;
    }

    // `(` - require a positively identified comma-tolerant construct.
    if ($before === null) {
        return false;
    }
    if ($before['name'] === 'CHAR' && in_array($before['text'], [')', ']'], true)) {
        return true;                      // chained call: foo()( ... )
    }
    return in_array($before['id'], COMMA_TOLERANT_BEFORE_PAREN, true);
}

/**
 * Every rule takes the two streams and the two cursors and returns
 * [tokens consumed from A, tokens consumed from B, class name] or null.
 *
 * Order matters only in that the first match wins; the rules are written to be
 * mutually exclusive at any given position.
 *
 * @return array{0:int,1:int,2:string}|null
 */
function tryRules(array $A, int $i, array $B, int $j): ?array
{
    $a = at($A, $i);
    $b = at($B, $j);

    // --- TRAILING_COMMA: a `,` appears in AFTER, immediately before the closer
    //     that BEFORE is already sitting on. A pure insertion; nothing else moves.
    //
    // ⚠️ THE CLOSER'S CHARACTER IS NOT ENOUGH, and the first version of this rule
    // tested nothing else. A trailing comma is inert in an array literal, a call, a
    // parameter list, a closure `use`, isset/unset/list and short-array destructuring -
    // and is a COMPILE ERROR in empty(), exit(), eval(), declare(), a control-structure
    // paren, an array-access [ ], after a variadic or first-class-callable `...`, and
    // after a comma that is already there. `array( 1, )` becoming `array( 1,, )` was
    // accepted by the character-only test and is fatal: "Cannot use empty array
    // elements in arrays". So the rule now resolves the OPENER and refuses anything it
    // cannot positively identify as comma-tolerant.
    if ($b !== null && isChar($b, ',') && $a !== null
        && ($a['text'] === ')' || $a['text'] === ']') && $a['name'] === 'CHAR') {
        $bNext = at($B, $j + 1);
        if ($bNext !== null && same($a, $bNext) && commaIsInert($A, $i, $B, $j)) {
            return [0, 1, 'TRAILING_COMMA'];
        }
    }

    // --- REQUOTE: same string VALUE, different quoting. Decoded, not compared raw.
    if ($a !== null && $b !== null
        && $a['name'] === 'T_CONSTANT_ENCAPSED_STRING'
        && $b['name'] === 'T_CONSTANT_ENCAPSED_STRING') {
        $da = decodeLiteral($a['text']);
        $db = decodeLiteral($b['text']);
        if ($da !== null && $db !== null && $da === $db) {
            return [1, 1, 'REQUOTE'];
        }
    }

    // --- KEYWORD_CASE: an allow-listed reserved keyword, differing only by case.
    //
    // ⚠️ THE TOKEN CLASS ALONE IS NOT ENOUGH. Since PHP 7's context-sensitive lexer,
    // a reserved word may be used as a CLASS-CONSTANT or ENUM-CASE name - `Foo::IF`,
    // `Status::MATCH` - and those names ARE case sensitive, unlike the keyword itself.
    // The lexer still reports T_IF there, so the token id says "keyword" while the
    // meaning is "identifier". Property names after -> and ?-> are case sensitive for
    // the same reason. So refuse whenever the preceding token puts us in a name
    // position rather than a keyword position.
    if ($a !== null && $b !== null
        && $a['name'] === $b['name']
        && in_array($a['id'], CASE_INSENSITIVE_KEYWORDS, true)
        && strtolower($a['text']) === strtolower($b['text'])) {
        $prev = at($A, $i - 1);
        $namePosition = $prev !== null && in_array($prev['name'], [
            'T_DOUBLE_COLON',                 // Foo::IF  - class constant / enum case
            'T_OBJECT_OPERATOR',              // $o->list - property name
            'T_NULLSAFE_OBJECT_OPERATOR',     // $o?->list
            'T_CONST',                        // const IF = 1;
        ], true);
        if (!$namePosition) {
            return [1, 1, 'KEYWORD_CASE'];
        }
    }

    // --- ELSEIF_MERGE: `else` `if` collapses to a single `elseif`.
    if ($a !== null && $a['name'] === 'T_ELSE'
        && ($a2 = at($A, $i + 1)) !== null && $a2['name'] === 'T_IF'
        && $b !== null && $b['name'] === 'T_ELSEIF') {
        return [2, 1, 'ELSEIF_MERGE'];
    }

    // --- PRE_INCREMENT / PRE_DECREMENT on a stand-alone statement.
    //     Requires a statement boundary before and a `;` after on BOTH sides, so an
    //     increment used for its VALUE inside an expression can never match here.
    if ($a !== null && $a['name'] === 'T_VARIABLE'
        && ($a2 = at($A, $i + 1)) !== null
        && ($a2['name'] === 'T_INC' || $a2['name'] === 'T_DEC')
        && $b !== null && ($b['name'] === 'T_INC' || $b['name'] === 'T_DEC')
        && ($b2 = at($B, $j + 1)) !== null && $b2['name'] === 'T_VARIABLE'
        && $a2['name'] === $b['name']
        && $a['text'] === $b2['text']) {
        $prev = at($A, $i - 1);
        $startsStatement = $prev === null
            || (in_array($prev['text'], [';', '{', '}'], true) && $prev['name'] === 'CHAR')
            || $prev['name'] === 'T_OPEN_TAG';
        $endsA = isChar(at($A, $i + 2), ';');
        $endsB = isChar(at($B, $j + 2), ';');
        if ($startsStatement && $endsA && $endsB) {
            return [2, 2, 'PRE_INCREMENT'];
        }
    }

    // --- ADD_SEMICOLON: an explicit `;` before a close tag that already terminated
    //     the statement implicitly.
    if ($b !== null && isChar($b, ';') && $a !== null && $a['name'] === 'T_CLOSE_TAG') {
        $bNext = at($B, $j + 1);
        if ($bNext !== null && $bNext['name'] === 'T_CLOSE_TAG') {
            return [0, 1, 'ADD_SEMICOLON'];
        }
    }

    // --- DROP_EMPTY_SEMICOLON: a `;` that terminated nothing.
    if ($a !== null && isChar($a, ';')) {
        $prev = at($A, $i - 1);
        $emptyStatement = $prev !== null && $prev['name'] === 'CHAR'
            && in_array($prev['text'], [';', '{', '}'], true);
        if ($emptyStatement) {
            return [1, 0, 'DROP_EMPTY_SEMICOLON'];
        }
    }

    // --- INCLUDE_PARENS, matched across the whole statement.
    if ($a !== null && isChar($a, '(')) {
        $prev = at($A, $i - 1);
        if ($prev !== null && in_array($prev['id'], INCLUDE_KEYWORDS, true)) {
            $depth = 0;
            $close = -1;
            for ($k = $i; $k < count($A); $k++) {
                if ($A[$k]['name'] === 'CHAR' && $A[$k]['text'] === '(') {
                    $depth++;
                } elseif ($A[$k]['name'] === 'CHAR' && $A[$k]['text'] === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $close = $k;
                        break;
                    }
                }
            }
            // The parentheses must wrap the ENTIRE argument: the very next token
            // after the closer has to end the statement. Anything else - a
            // concatenation outside the parens, say - is not this transformation.
            if ($close > $i && isChar(at($A, $close + 1), ';')) {
                $inner = $close - $i - 1;
                $ok = true;
                for ($t = 0; $t < $inner; $t++) {
                    $ta = at($A, $i + 1 + $t);
                    $tb = at($B, $j + $t);
                    if ($ta === null || $tb === null || !same($ta, $tb)) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok && isChar(at($B, $j + $inner), ';')) {
                    return [$inner + 2, $inner, 'INCLUDE_PARENS'];
                }
            }
        }
    }

    // --- INLINE_HTML_WS: tolerated types whose content matches once whitespace runs
    //     are collapsed. Counted per file and reported, never silent.
    if ($a !== null && $b !== null
        && $a['name'] === $b['name']
        && in_array($a['id'], TOLERANT_WS, true)
        && collapse($a['text']) === collapse($b['text'])) {
        return [1, 1, 'INLINE_HTML_WS'];
    }
    // --- INLINE_HTML_DROPPED: a tolerated token that collapses to NOTHING is inserted
    //     or removed outright, which happens when a `<?php` moves to its own line and
    //     the whitespace-only inline-HTML run between two blocks disappears.
    //
    // ⚠️ THIS IS A SEPARATE CLASS FROM INLINE_HTML_WS ON PURPOSE. The two were one
    // class and one inertness argument, and the argument only covered one of them:
    // INLINE_HTML_WS compares two tokens whose content is identical once runs of
    // whitespace collapse, which is exactly what an HTML parser does. DELETING a
    // whitespace token is a different claim - the whitespace is gone from the output,
    // not merely rewritten - and inside <pre> or a <textarea> that is visible. It is
    // still allowed for stage 2 because the sniffs there only move a `<?php` across a
    // line boundary, but it is now COUNTED SEPARATELY so it shows up in the census
    // instead of hiding inside a bigger number, and it has to be allow-listed on its
    // own merits by any future stage.
    if ($a !== null && in_array($a['id'], TOLERANT_WS, true) && collapse($a['text']) === '') {
        return [1, 0, 'INLINE_HTML_DROPPED'];
    }
    if ($b !== null && in_array($b['id'], TOLERANT_WS, true) && collapse($b['text']) === '') {
        return [0, 1, 'INLINE_HTML_DROPPED'];
    }

    return null;
}

/**
 * Walk one file's two streams.
 *
 * @return array{classes:array<string,int>,problems:array<int,string>}
 */
function compareStreams(array $A, array $B): array
{
    $classes = [];
    $problems = [];
    $i = 0;
    $j = 0;
    $na = count($A);
    $nb = count($B);

    while ($i < $na || $j < $nb) {
        if ($i < $na && $j < $nb && same($A[$i], $B[$j])) {
            $i++;
            $j++;
            continue;
        }

        $hit = tryRules($A, $i, $B, $j);
        if ($hit !== null) {
            [$di, $dj, $class] = $hit;
            $classes[$class] = ($classes[$class] ?? 0) + 1;
            $i += $di;
            $j += $dj;
            continue;
        }

        // Unexplained. Record it with real context, then try to resynchronise so the
        // rest of the file is still analysed and the report is complete rather than
        // stopping at the first surprise.
        $line = $B[$j]['line'] ?? ($A[$i]['line'] ?? 0);
        $beforeTxt = $i < $na ? $A[$i]['name'] . ' ' . substr($A[$i]['text'], 0, 60) : '<end of file>';
        $afterTxt = $j < $nb ? $B[$j]['name'] . ' ' . substr($B[$j]['text'], 0, 60) : '<end of file>';

        $resynced = false;
        for ($w = 1; $w <= RESYNC_WINDOW && !$resynced; $w++) {
            foreach ([[$w, 0], [0, $w], [$w, $w]] as [$da, $db]) {
                if ($i + $da + RESYNC_ANCHOR > $na || $j + $db + RESYNC_ANCHOR > $nb) {
                    continue;
                }
                $anchored = true;
                for ($k = 0; $k < RESYNC_ANCHOR; $k++) {
                    if (!same($A[$i + $da + $k], $B[$j + $db + $k])) {
                        $anchored = false;
                        break;
                    }
                }
                if ($anchored) {
                    $problems[] = sprintf(
                        "line ~%d  UNCLASSIFIED (skipped %d before / %d after token(s) to resync)\n"
                        . "            before: %s\n            after : %s",
                        $line,
                        $da,
                        $db,
                        $beforeTxt,
                        $afterTxt
                    );
                    $i += $da;
                    $j += $db;
                    $resynced = true;
                    break;
                }
            }
        }

        if (!$resynced) {
            $problems[] = sprintf(
                "line ~%d  UNCLASSIFIED and could not resynchronise - rest of file not analysed\n"
                . "            before: %s\n            after : %s",
                $line,
                $beforeTxt,
                $afterTxt
            );
            break;
        }
    }

    return ['classes' => $classes, 'problems' => $problems];
}

// ---------------------------------------------------------------------------
// Front ends. Both feed the SAME comparator above.
// ---------------------------------------------------------------------------

$opts = getopt('', ['stage:', 'base:', 'pairs:', 'allow:', 'paths:']);
$stage = isset($opts['stage']) ? (string) $opts['stage'] : '';
if ($stage === '') {
    fail('usage: --stage <n> (--base <ref> | --pairs <manifest.json>)');
}

$mapPath = dirname(__DIR__) . '/phpcbf-stages.json';
if (!is_file($mapPath)) {
    fail("stage map not found at {$mapPath}");
}
$map = json_decode((string) file_get_contents($mapPath), true);
if (!is_array($map) || !isset($map['stages'][$stage])) {
    fail("no such stage in the map: {$stage}");
}
$stageDef = $map['stages'][$stage];
$allowed = $stageDef['token_classes'] ?? null;
if (!is_array($allowed)) {
    fail(
        "stage {$stage} declares no token_classes in .github/phpcbf-stages.json. "
        . 'This gate refuses to invent an allow-list; add one with a written reason.'
    );
}
if (isset($opts['allow'])) {
    // Escape hatch for a one-off investigation. Never used by CI - the map is the
    // contract, and a flag that widens it silently would defeat the whole point.
    $allowed = array_merge($allowed, array_map('trim', explode(',', (string) $opts['allow'])));
}

/** @var array<int, array{label:string,before:string,after:string|null}> $pairs */
$pairs = [];

if (isset($opts['base'])) {
    $base = (string) $opts['base'];
    shell('git rev-parse --verify ' . escapeshellarg($base), $st);
    if ($st !== 0) {
        fail("not a valid git ref: {$base}");
    }
    // --paths narrows the comparison to a pathspec, e.g. --paths "theme tools".
    // Without it every changed *.php is compared, which is right in CI - phpcbf has
    // just run and nothing else is in the working tree - but wrong when a human is
    // grading a branch that also contains hand edits to the tooling. Those hand edits
    // are real unexplained changes and the gate is correct to reject them; they simply
    // are not what is being asked about.
    $pathspec = '"*.php"';
    if (isset($opts['paths']) && trim((string) $opts['paths']) !== '') {
        $parts = preg_split('/\s+/', trim((string) $opts['paths'])) ?: [];
        $pathspec = implode(' ', array_map(
            static fn(string $p): string => escapeshellarg(rtrim($p, '/') . '/*.php'),
            $parts
        ));
    }
    $changed = array_values(array_filter(
        explode("\n", shell('git diff --name-only ' . escapeshellarg($base) . ' -- ' . $pathspec)),
        static fn(string $l): bool => trim($l) !== ''
    ));
    foreach ($changed as $path) {
        // captureBytes, NOT shell - see the comment on captureBytes. The AFTER side is
        // read with file_get_contents below, so the BEFORE side must be byte-exact too
        // or the two sides are not comparable.
        $before = captureBytes('git show ' . escapeshellarg($base . ':' . $path), $st2);
        if ($st2 !== 0 || $before === null) {
            echo "  SKIP  {$path} (new file)\n";
            continue;
        }
        if (!is_file($path)) {
            echo "  SKIP  {$path} (deleted)\n";
            continue;
        }
        $pairs[] = ['label' => $path, 'before' => $before, 'after' => (string) file_get_contents($path)];
    }
} elseif (isset($opts['pairs'])) {
    $manifest = (string) $opts['pairs'];
    if (!is_file($manifest)) {
        fail("no such manifest: {$manifest}");
    }
    $rows = json_decode((string) file_get_contents($manifest), true);
    if (!is_array($rows)) {
        fail("manifest is not a JSON array: {$manifest}");
    }
    foreach ($rows as $row) {
        if (!isset($row['before'], $row['after'])) {
            fail('every manifest row needs "before" and "after"');
        }
        if (!is_file($row['before']) || !is_file($row['after'])) {
            fail('manifest names a file that does not exist: ' . ($row['label'] ?? '?'));
        }
        $pairs[] = [
            'label' => (string) ($row['label'] ?? $row['after']),
            'before' => (string) file_get_contents($row['before']),
            'after' => (string) file_get_contents($row['after']),
        ];
    }
} else {
    fail('one of --base or --pairs is required');
}

if (!$pairs) {
    // A vacuous pass is reported as vacuous. A gate that silently passes on an empty
    // set is how "measured nothing" comes to read as "measured clean".
    echo "No PHP file pairs to compare.\n";
    echo "RESULT: PASS (vacuous - 0 files compared)\n";
    exit(0);
}

echo "Stage {$stage}: " . ($stageDef['name'] ?? '?') . "\n";
echo 'allowed classes: ' . implode(', ', $allowed) . "\n\n";

$totals = [];
$failures = 0;
$filesWithHtmlMove = [];
$tokensCompared = 0;

foreach ($pairs as $p) {
    $A = lex($p['before'], 'before:' . $p['label']);
    $B = lex($p['after'], $p['label']);
    $tokensCompared += count($A);
    $res = compareStreams($A, $B);

    $bad = [];
    foreach ($res['classes'] as $class => $n) {
        $totals[$class] = ($totals[$class] ?? 0) + $n;
        if (!in_array($class, $allowed, true)) {
            $bad[] = "{$class} x{$n} is not permitted in stage {$stage}";
        }
    }

    $problems = array_merge($bad, $res['problems']);
    if ($problems) {
        $failures++;
        echo "  FAIL  {$p['label']}\n";
        foreach ($problems as $prob) {
            echo '        ' . $prob . "\n";
        }
        continue;
    }

    $summary = [];
    foreach ($res['classes'] as $class => $n) {
        $summary[] = "{$class} x{$n}";
    }
    if (isset($res['classes']['INLINE_HTML_WS']) || isset($res['classes']['INLINE_HTML_DROPPED'])) {
        $filesWithHtmlMove[] = $p['label'];
    }
    if (!$summary) {
        echo "  OK    {$p['label']}   (identical)\n";
    } else {
        echo "  OK    {$p['label']}   " . implode(', ', $summary) . "\n";
    }
}

echo "\n";
echo 'files compared : ' . count($pairs) . "\n";
echo "tokens compared: {$tokensCompared}\n";
echo "files failing  : {$failures}\n\n";
echo "-- change census --\n";
ksort($totals);
$grand = 0;
foreach ($totals as $class => $n) {
    $mark = in_array($class, $allowed, true) ? ' ' : '!';
    printf("  %s %-22s %6d\n", $mark, $class, $n);
    $grand += $n;
}
printf("  %s %-22s %6d\n", ' ', 'TOTAL', $grand);

if ($filesWithHtmlMove) {
    echo "\n⚠️  " . count($filesWithHtmlMove) . " file(s) moved inline-HTML bytes. The render is\n";
    echo "    unchanged (HTML ignores whitespace between tokens) but the served bytes\n";
    echo "    differ. Worth a look on staging:\n";
    foreach ($filesWithHtmlMove as $f) {
        echo "      {$f}\n";
    }
}

if ($failures > 0) {
    echo "\nRESULT: FAIL - {$failures} file(s) carry a change this gate cannot explain.\n";
    echo "Every edit must fall into a class named in .github/phpcbf-stages.json for\n";
    echo "this stage. An unexplained edit is not assumed benign.\n";
    exit(1);
}

echo "\nRESULT: PASS - every token change is an expected, inert class for stage {$stage}.\n";
exit(0);
