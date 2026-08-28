<?php
/**
 * Self-test for assert-token-changes-expected.php - PROVEN IN BOTH DIRECTIONS.
 *
 * WHY THIS EXISTS
 * ---------------
 * The Stage 2 gate passed on its first real run: 44 files, 30,489 tokens, 792
 * changes, zero unexplained. That is exactly the situation in which a guard is most
 * likely to be worthless, because a guard that has never rejected anything has not
 * been shown to be capable of rejecting anything. This project's CLAUDE.md puts it
 * plainly: a guard with no failing state is worse than no guard, because it converts
 * "we did not look" into "we checked and it was clean".
 *
 * So this feeds the classifier hand-built before/after pairs with a KNOWN verdict and
 * asserts it returns that verdict.
 *
 *   MUST-PASS  edits that really are the inert Stage 2 transformations. If one of
 *              these fails, the gate cries wolf and will be ignored on the run where
 *              it is right.
 *   MUST-FAIL  edits that look superficially like a Stage 2 transformation but change
 *              behaviour. Each one is a regression the sweep could plausibly ship. If
 *              the gate passes one of these it is decorative.
 *
 * The MUST-FAIL set is drawn from what this codebase has actually been bitten by:
 * a comparison operator quietly rewritten (PR #48, 37 real occurrences), an
 * identifier renamed under cover of a case change, and a string whose VALUE moved
 * rather than its quoting.
 *
 * USAGE
 *   php .github/scripts/test-token-gate.php
 *
 * Exit 0 = every case behaved as specified. Exit 1 = at least one did not.
 */

declare(strict_types=1);

$gate = __DIR__ . '/assert-token-changes-expected.php';
if (!is_file($gate)) {
    fwrite(STDERR, "ABORT: gate not found at {$gate}\n");
    exit(2);
}

$php = PHP_BINARY;
$tmp = sys_get_temp_dir() . '/bw-gate-selftest-' . getmypid();
@mkdir($tmp, 0700, true);

/**
 * Each case: [name, expectation, before-source, after-source].
 * expectation is 'PASS' (gate must exit 0) or 'FAIL' (gate must exit 1).
 */
$cases = [];

// ---------------------------------------------------------------------------
// MUST-PASS - the nine classes, one case each, in their genuinely inert form.
// ---------------------------------------------------------------------------

$cases[] = ['TRAILING_COMMA is inert', 'PASS',
    "<?php\n\$a = array(\n\t'x' => 1,\n\t'y' => 2\n);\n",
    "<?php\n\$a = array(\n\t'x' => 1,\n\t'y' => 2,\n);\n"];

$cases[] = ['REQUOTE with identical value', 'PASS',
    "<?php\n\$s = \"plain text\";\n",
    "<?php\n\$s = 'plain text';\n"];

$cases[] = ['REQUOTE where the double-quoted form held an escaped quote', 'PASS',
    "<?php\n\$s = \"it's fine\";\n",
    "<?php\n\$s = 'it\\'s fine';\n"];

$cases[] = ['KEYWORD_CASE on the ABSPATH guard', 'PASS',
    "<?php\ndefined( 'ABSPATH' ) OR exit;\n",
    "<?php\ndefined( 'ABSPATH' ) or exit;\n"];

$cases[] = ['INCLUDE_PARENS on a whole-argument require_once', 'PASS',
    "<?php\nrequire_once( get_template_directory() . '/inc/x.php' );\n",
    "<?php\nrequire_once get_template_directory() . '/inc/x.php';\n"];

$cases[] = ['ELSEIF_MERGE', 'PASS',
    "<?php\nif ( \$a ) {\n\techo 1;\n} else if ( \$b ) {\n\techo 2;\n}\n",
    "<?php\nif ( \$a ) {\n\techo 1;\n} elseif ( \$b ) {\n\techo 2;\n}\n"];

$cases[] = ['PRE_INCREMENT on a stand-alone statement', 'PASS',
    "<?php\n\$p = 1;\n\$p++;\n",
    "<?php\n\$p = 1;\n++\$p;\n"];

$cases[] = ['ADD_SEMICOLON before a close tag', 'PASS',
    "<?php\nif ( \$x ) { ?>\n<b>hi</b>\n<?php echo 'z' ?>\n",
    "<?php\nif ( \$x ) { ?>\n<b>hi</b>\n<?php echo 'z'; ?>\n"];

$cases[] = ['DROP_EMPTY_SEMICOLON', 'PASS',
    "<?php\n\$a = 1;;\n",
    "<?php\n\$a = 1;\n"];

$cases[] = ['INLINE_HTML_WS when a <?php moves to its own line', 'PASS',
    "<option value=\"2-1\" <?php if ( \$r ) {\n\techo 'selected';\n} ?>>\n",
    "<option value=\"2-1\" \n\t<?php\n\tif ( \$r ) {\n\t\techo 'selected';\n\t} ?>>\n"];

// ---------------------------------------------------------------------------
// MUST-FAIL - each one is a real regression wearing a Stage 2 costume.
// ---------------------------------------------------------------------------

// The exact defect class PR #48 repaired by hand across 37 source lines.
$cases[] = ['comparison operator rewritten == to ===', 'FAIL',
    "<?php\nif ( \$page == '1' ) {\n\techo 'yes';\n}\n",
    "<?php\nif ( \$page === '1' ) {\n\techo 'yes';\n}\n"];

// A string whose VALUE moved, not its quoting. The decoder must catch this:
// "a\tb" is a tab, 'a\tb' is a backslash and a t.
$cases[] = ['requote that CHANGES the string value (tab escape)', 'FAIL',
    "<?php\n\$s = \"a\\tb\";\n",
    "<?php\n\$s = 'a\\tb';\n"];

// Same shape, plainer: the text itself edited under cover of a requote.
$cases[] = ['requote that also edits the text', 'FAIL',
    "<?php\n\$s = \"hello world\";\n",
    "<?php\n\$s = 'hello wrld';\n"];

// A function name is T_STRING, not a keyword. Case-folding it must NOT be waved
// through as KEYWORD_CASE - user function names are the thing renames break.
$cases[] = ['identifier case change is not a keyword case change', 'FAIL',
    "<?php\nbraillewright_setup();\n",
    "<?php\nBraillewright_Setup();\n"];

// A trailing comma is inert; an extra ARGUMENT is not.
$cases[] = ['an added argument is not a trailing comma', 'FAIL',
    "<?php\nfoo(\n\t1,\n\t2\n);\n",
    "<?php\nfoo(\n\t1,\n\t2,\n\t3\n);\n"];

// Dropping parens that did NOT wrap the whole argument changes precedence of the
// concatenation, so the file included is a different file.
$cases[] = ['include parens that do not wrap the whole argument', 'FAIL',
    "<?php\nrequire_once( \$dir . '/a.php' );\n",
    "<?php\nrequire_once \$dir; . '/a.php';\n"];

// A whole statement removed. Nothing about this is a style fix.
$cases[] = ['a deleted statement', 'FAIL',
    "<?php\n\$a = 1;\ndo_action( 'braillewright_body_top' );\n\$b = 2;\n",
    "<?php\n\$a = 1;\n\$b = 2;\n"];

// Increment used for its VALUE - not a stand-alone statement, so reordering it
// changes what is assigned.
$cases[] = ['post-increment used for its value is not stand-alone', 'FAIL',
    "<?php\n\$i = 0;\n\$b = \$i++;\n",
    "<?php\n\$i = 0;\n\$b = ++\$i;\n"];

// Inline HTML whose CONTENT changed, not just its whitespace.
$cases[] = ['inline HTML content edited, not just reindented', 'FAIL',
    "<div class=\"wrap\">\n<?php echo 'x'; ?>\n",
    "<div class=\"wrapper\">\n<?php echo 'x'; ?>\n"];

// ---------------------------------------------------------------------------
// Added 2026-08-28 after an adversarial review of the gate. Each of these was
// ACCEPTED by the first version of the rules; every one is a PHP compile error or a
// real rename, so the gate was attesting "inert" for code that does not run.
// ---------------------------------------------------------------------------

// The reachable one: the sniff edits arrays, and 11 arrays in the 44 changed files
// ALREADY ended in a trailing comma. array(1,,) is fatal - "Cannot use empty array
// elements in arrays" - but the character-only closer test waved it through.
$cases[] = ['a SECOND trailing comma in an array literal', 'FAIL',
    "<?php\n\$a = array(\n\t1,\n);\n",
    "<?php\n\$a = array(\n\t1,,\n);\n"];

// A comma before the ")" of a construct that forbids one. empty() appears 98 times in
// the changed files, so this is the shape most likely to be reached by accident.
$cases[] = ['a trailing comma inside empty()', 'FAIL',
    "<?php\nif ( empty( \$x ) ) {\n\techo 1;\n}\n",
    "<?php\nif ( empty( \$x, ) ) {\n\techo 1;\n}\n"];

// Array ACCESS is not an array literal; a trailing comma there is a syntax error.
$cases[] = ['a trailing comma inside an array access', 'FAIL',
    "<?php\n\$v = \$a[ \$k ];\n",
    "<?php\n\$v = \$a[ \$k, ];\n"];

// A trailing comma after a variadic parameter is a syntax error.
$cases[] = ['a trailing comma after a variadic parameter', 'FAIL',
    "<?php\nfunction f(\n\t...\$args\n) {}\n",
    "<?php\nfunction f(\n\t...\$args,\n) {}\n"];

// The keyword allow-list is not enough on its own: since PHP 7 a reserved word can be
// a CLASS-CONSTANT name, and constant names ARE case sensitive even though the lexer
// still reports T_LIST / T_IF / T_MATCH for them.
$cases[] = ['case fold of a class constant that happens to be a keyword', 'FAIL',
    "<?php\n\$v = Braillewright_Fonts::LIST;\n",
    "<?php\n\$v = Braillewright_Fonts::list;\n"];

// Property names are case sensitive too.
$cases[] = ['case fold of a property name that happens to be a keyword', 'FAIL',
    "<?php\n\$v = \$obj->LIST;\n",
    "<?php\n\$v = \$obj->list;\n"];

// And the corresponding MUST-PASS, so the fixes above did not simply make the rules
// refuse everything: an ordinary trailing comma in a nested call must still be inert.
$cases[] = ['trailing comma in a nested function call is still inert', 'PASS',
    "<?php\nadd_action(\n\t'init',\n\tarray( \$this, 'go' )\n);\n",
    "<?php\nadd_action(\n\t'init',\n\tarray( \$this, 'go' ),\n);\n"];

// A genuine keyword case fold in KEYWORD position must still pass.
$cases[] = ['keyword case fold in keyword position is still inert', 'PASS',
    "<?php\nIF ( \$a ) {\n\tRETURN 1;\n}\n",
    "<?php\nif ( \$a ) {\n\treturn 1;\n}\n"];

// ---------------------------------------------------------------------------
// STAGE 4 - `or` -> `||`. Added 2026-08-28.
//
// These run against stage 2's allow-list, which does NOT contain LOGICAL_OPERATOR,
// so the two must-pass cases below are written as stage-4 checks further down; here
// the point is the PRECEDENCE guard, which fails for stage-2 reasons AND stage-4
// reasons alike. The dedicated stage-4 pass cases live in $stage4Cases.
// ---------------------------------------------------------------------------

/** Cases run against stage 4 rather than stage 2. */
$stage4Cases = [];

// The exact idiom at all 19 Braillewright sites. Measured identical on PHP 8.3 -
// same value and same short-circuit trace under both operators.
$stage4Cases[] = ['the ABSPATH guard, the shape all 19 sites use', 'PASS',
    "<?php\ndefined( 'ABSPATH' ) or exit;\n",
    "<?php\ndefined( 'ABSPATH' ) || exit;\n"];

$stage4Cases[] = ['a bare two-call guard statement', 'PASS',
    "<?php\nfunction f(\$a,\$b) {\n\tbw_check( \$a ) or bw_bail( \$b );\n}\n",
    "<?php\nfunction f(\$a,\$b) {\n\tbw_check( \$a ) || bw_bail( \$b );\n}\n"];

$stage4Cases[] = ['and -> && in a bare statement', 'PASS',
    "<?php\nfunction f(\$a,\$b) {\n\tbw_check( \$a ) and bw_go( \$b );\n}\n",
    "<?php\nfunction f(\$a,\$b) {\n\tbw_check( \$a ) && bw_go( \$b );\n}\n"];

// ⛔ THE ONE THAT MATTERS. Assignment sits between `or` and `||` in precedence, so
// this swap CHANGES WHAT $x GETS. Measured on PHP 8.3: false vs true.
$stage4Cases[] = ['or -> || with an assignment in the statement', 'FAIL',
    "<?php\nfunction f(\$a,\$b) {\n\t\$x = bw_check( \$a ) or bw_bail( \$b );\n\treturn \$x;\n}\n",
    "<?php\nfunction f(\$a,\$b) {\n\t\$x = bw_check( \$a ) || bw_bail( \$b );\n\treturn \$x;\n}\n"];

// ?? also sits between them. Measured: false vs true.
$stage4Cases[] = ['or -> || with a null-coalesce in the statement', 'FAIL',
    "<?php\nfunction f(\$n,\$b) {\n\t\$x = ( \$n ?? bw_check() ) or bw_bail( \$b );\n\treturn \$x;\n}\n",
    "<?php\nfunction f(\$n,\$b) {\n\t\$x = ( \$n ?? bw_check() ) || bw_bail( \$b );\n\treturn \$x;\n}\n"];

// A ternary in the statement is the same hazard.
$stage4Cases[] = ['or -> || with a ternary in the statement', 'FAIL',
    "<?php\nfunction f(\$a,\$b) {\n\t\$x = ( \$a ? 1 : 2 ) or bw_bail( \$b );\n\treturn \$x;\n}\n",
    "<?php\nfunction f(\$a,\$b) {\n\t\$x = ( \$a ? 1 : 2 ) || bw_bail( \$b );\n\treturn \$x;\n}\n"];

// A compound assignment is an assignment.
$stage4Cases[] = ['or -> || with a compound assignment', 'FAIL',
    "<?php\nfunction f(\$a,\$b) {\n\t\$x .= bw_check( \$a ) or bw_bail( \$b );\n\treturn \$x;\n}\n",
    "<?php\nfunction f(\$a,\$b) {\n\t\$x .= bw_check( \$a ) || bw_bail( \$b );\n\treturn \$x;\n}\n"];

// ⛔ Stage 4 may ONLY swap operators. Anything else riding along is not Stage 4 work.
$stage4Cases[] = ['a requote riding along with the operator swap', 'FAIL',
    "<?php\ndefined( \"ABSPATH\" ) or exit;\n",
    "<?php\ndefined( 'ABSPATH' ) || exit;\n"];

// ---------------------------------------------------------------------------
// STAGE 5 - variable renames, added comments, and in_array strictness.
// Added 2026-08-28.
//
// ⛔ VARIABLE_RENAME is the first class in this gate whose safety is NOT a token-level
// property. The must-fail cases below are the three ways a rename goes wrong, and the
// third one is the dangerous one because the code still runs afterwards.
// ---------------------------------------------------------------------------

/** Cases run against stage 5. */
$stage5Cases = [];

$stage5Cases[] = ['a consistent rename of a template-local', 'PASS',
    "<?php\n\$output = '';\n\$output .= 'a';\necho \$output;\n",
    "<?php\n\$braillewright_output = '';\n\$braillewright_output .= 'a';\necho \$braillewright_output;\n"];

$stage5Cases[] = ['a translators comment added above an i18n call', 'PASS',
    "<?php\nprintf( __( 'Published %s', 'braillewright' ), \$d );\n",
    "<?php\n/* translators: %s: the publication date. */\nprintf( __( 'Published %s', 'braillewright' ), \$d );\n"];

$stage5Cases[] = ['a phpcs:ignore annotation added', 'PASS',
    "<?php\n\$GLOBALS['comment'] = \$comment;\n",
    "<?php\n// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- required by core.\n\$GLOBALS['comment'] = \$comment;\n"];

$stage5Cases[] = ['in_array gains the strict flag', 'PASS',
    "<?php\nif ( in_array( \$v, \$list ) ) {\n\techo 1;\n}\n",
    "<?php\nif ( in_array( \$v, \$list, true ) ) {\n\techo 1;\n}\n"];

// The real Squiz.ControlSignature shape in features/inc/colors.php: a section-divider
// comment sits between the brace and the keyword, so satisfying "} elseif" RELOCATES the
// comment. Inert - a comment never executes wherever it sits.
$stage5Cases[] = ['a section-divider comment relocated past a closing brace', 'PASS',
    "<?php\nif ( \$a ) {\n\techo 1;\n}\n/***** Header *****/\nelseif ( \$b ) {\n\techo 2;\n}\n",
    "<?php\nif ( \$a ) {\n\techo 1;\n/***** Header *****/\n} elseif ( \$b ) {\n\techo 2;\n}\n"];

// ⛔ A move and a deletion are token-identical LOCALLY. Only the whole file tells them
// apart, and losing a comment can lose a phpcs:ignore or a translators note.
$stage5Cases[] = ['a comment that vanishes rather than moving', 'FAIL',
    "<?php\nif ( \$a ) {\n\techo 1;\n}\n/***** Header *****/\nelseif ( \$b ) {\n\techo 2;\n}\n",
    "<?php\nif ( \$a ) {\n\techo 1;\n} elseif ( \$b ) {\n\techo 2;\n}\n"];

// ⛔ INCONSISTENT: one occurrence left behind, so the file now has TWO variables.
$stage5Cases[] = ['a rename that misses an occurrence', 'FAIL',
    "<?php\n\$output = '';\n\$output .= 'a';\necho \$output;\n",
    "<?php\n\$braillewright_output = '';\n\$output .= 'a';\necho \$braillewright_output;\n"];

// ⛔ COLLISION: the worst of the three, because the result still runs. Two distinct
// variables silently become one.
$stage5Cases[] = ['a rename onto a name that already exists', 'FAIL',
    "<?php\n\$a = 1;\n\$b = 2;\necho \$a . \$b;\n",
    "<?php\n\$b = 1;\n\$b = 2;\necho \$b . \$b;\n"];

// ⛔ NOT LOCAL: renaming a variable the file binds to global scope detaches the code
// from the global it referred to, while still running.
$stage5Cases[] = ['renaming a variable declared global', 'FAIL',
    "<?php\nfunction f() {\n\tglobal \$post;\n\treturn \$post->ID;\n}\n",
    "<?php\nfunction f() {\n\tglobal \$post;\n\treturn \$braillewright_post->ID;\n}\n"];

// ⛔ A comment REMOVAL is not a comment addition - it can delete a phpcs:ignore.
$stage5Cases[] = ['a comment removed rather than added', 'FAIL',
    "<?php\n/* translators: %s: the date. */\nprintf( __( 'x %s', 'braillewright' ), \$d );\n",
    "<?php\nprintf( __( 'x %s', 'braillewright' ), \$d );\n"];

// ⛔ The strict flag belongs to in_array, not to whatever call happens to be there.
$stage5Cases[] = ['a third argument added to a different function', 'FAIL',
    "<?php\n\$x = str_replace( \$a, \$b );\n",
    "<?php\n\$x = str_replace( \$a, \$b, true );\n"];

// ⛔ The strict flag must be the ONLY change to the call. Here the haystack is swapped
// for a DIFFERENT VARIABLE THAT ALREADY EXISTS, which is the dangerous real-world shape:
// the code still runs and searches the wrong array.
//
// ⚠️ HONEST LIMITATION, recorded rather than papered over. If the haystack were swapped
// for a name that appears NOWHERE ELSE, this gate cannot tell that apart from a genuine
// one-occurrence rename - the two are token-identical. That case is caught by a
// different layer: `Static analysis (PHPStan + WordPress stubs)` is a required check and
// flags the undefined variable. Layered guards, not one guard pretending to see
// everything.
$stage5Cases[] = ['in_array haystack swapped for another existing variable', 'FAIL',
    "<?php\n\$list = a();\n\$other = b();\nif ( in_array( \$v, \$list ) ) {\n\techo 1;\n}\n",
    "<?php\n\$list = a();\n\$other = b();\nif ( in_array( \$v, \$other, true ) ) {\n\techo 1;\n}\n"];

// ---------------------------------------------------------------------------

// Tag each case with the stage it must be graded against, then run one list. A case is
// only meaningful against its own stage's allow-list: LOGICAL_OPERATOR is permitted in
// stage 4 and NOT in stage 2, and running a stage-4 case against stage 2 would "pass"
// for the wrong reason.
$all = [];
foreach ($cases as $c) {
    $all[] = [2, $c[0], $c[1], $c[2], $c[3]];
}
foreach ($stage4Cases as $c) {
    $all[] = [4, $c[0], $c[1], $c[2], $c[3]];
}
foreach ($stage5Cases as $c) {
    $all[] = [5, $c[0], $c[1], $c[2], $c[3]];
}

$pass = 0;
$bad = [];
$n = 0;

foreach ($all as [$stage, $name, $expect, $before, $after]) {
    $n++;
    $name = "[s{$stage}] {$name}";
    $bFile = "{$tmp}/c{$n}-before.php";
    $aFile = "{$tmp}/c{$n}-after.php";
    file_put_contents($bFile, $before);
    file_put_contents($aFile, $after);
    $man = "{$tmp}/c{$n}.json";
    file_put_contents($man, json_encode([[
        'label' => "case{$n}",
        'before' => $bFile,
        'after' => $aFile,
    ]]));

    $cmd = escapeshellarg($php) . ' ' . escapeshellarg($gate)
        . " --stage {$stage} --pairs " . escapeshellarg($man) . ' 2>&1';
    $out = [];
    $rc = 0;
    exec($cmd, $out, $rc);
    $got = $rc === 0 ? 'PASS' : ($rc === 1 ? 'FAIL' : "ERROR({$rc})");

    if ($got === $expect) {
        $pass++;
        printf("  ok    [%-4s] %s\n", $expect, $name);
    } else {
        $bad[] = $name;
        printf("  NOT OK[%-4s got %-8s] %s\n", $expect, $got, $name);
        foreach (array_slice($out, -8) as $line) {
            echo "          | {$line}\n";
        }
    }

    @unlink($bFile);
    @unlink($aFile);
    @unlink($man);
}
@rmdir($tmp);

$total = count($all);
$mustPass = count(array_filter($all, static fn($c): bool => $c[2] === 'PASS'));
$mustFail = $total - $mustPass;

echo "\n";
echo "cases      : {$total}  ({$mustPass} must-pass, {$mustFail} must-fail)\n";
echo "behaved    : {$pass}\n";

if ($bad) {
    echo "misbehaved : " . count($bad) . "\n\n";
    foreach ($bad as $b) {
        echo "  - {$b}\n";
    }
    echo "\nRESULT: FAIL - the gate does not behave as specified.\n";
    echo "A gate that cannot reject a regression is decorative; a gate that rejects\n";
    echo "correct work will be ignored on the run where it matters. Both are defects.\n";
    exit(1);
}

echo "misbehaved : 0\n";
echo "\nRESULT: PASS - the gate accepts every inert transformation and rejects every\n";
echo "regression in the must-fail set. Proven in BOTH directions.\n";
exit(0);
