<?php
/**
 * Prove that a whitespace-only reformat changed NO PHP token.
 *
 * WHY THIS EXISTS
 * ---------------
 * Stage 1 of the coding-standards sweep runs phpcbf across the theme and rewrites the
 * spacing of nearly every line in the five biggest files, including functions.php, which
 * bootstraps the whole theme on six live sites. "CI went green" does not prove that safe:
 * this project has already shipped a feature that was dead through every CI run for
 * months, because nothing asserted what actually reached a browser.
 *
 * A green test suite says the code still runs. This says something much stronger and much
 * cheaper to trust: that the PHP the engine sees is IDENTICAL. It tokenises the file
 * before and after with PHP's own lexer, removes the whitespace tokens, and requires the
 * remaining stream to match token for token, type and content. If that holds, a behaviour
 * change is not unlikely - it is impossible, because the compiler receives the same input.
 *
 * WHAT IS DELIBERATELY TOLERATED, AND WHY IT IS STILL SAFE
 * -------------------------------------------------------
 *   T_WHITESPACE                 dropped entirely. This is the thing being changed.
 *   T_COMMENT / T_DOC_COMMENT    compared with internal whitespace collapsed, because
 *                                reindenting a block comment rewrites its leading spaces
 *                                without changing a word of it. The words must still match.
 *   T_INLINE_HTML                compared with whitespace collapsed, and reported
 *                                SEPARATELY when only the whitespace differs - because
 *                                that IS a change to what a browser receives, even though
 *                                it is not a change to what PHP executes.
 *
 * Anything else - a moved brace, a deleted semicolon, a rewritten operator, a changed
 * string - is a hard failure that names the file, the line and both tokens.
 *
 * USAGE
 *   php .github/scripts/assert-token-stream-unchanged.php <base-ref>
 *
 * Compares every file that differs between <base-ref> and the working tree.
 * Exit 0 = the token streams are identical. Exit 1 = they are not. Exit 2 = it could not
 * do its job, which is never reported as a pass.
 */

declare(strict_types=1);

function fail(string $msg, int $code = 2): never {
    fwrite(STDERR, "ABORT: {$msg}\n");
    exit($code);
}

function run(string $cmd, ?int &$status = null): string {
    $out = [];
    exec($cmd . ' 2>/dev/null', $out, $status);
    return implode("\n", $out);
}

$base = $argv[1] ?? '';
if ($base === '') {
    fail('usage: assert-token-stream-unchanged.php <base-ref>');
}

run('git rev-parse --verify ' . escapeshellarg($base), $st);
if ($st !== 0) {
    fail("not a valid git ref: {$base}");
}

$changed = array_values(array_filter(
    explode("\n", run('git diff --name-only ' . escapeshellarg($base) . ' -- "*.php"')),
    static fn(string $l): bool => trim($l) !== ''
));

if (!$changed) {
    echo "No PHP file differs from {$base}. Nothing to prove.\n";
    echo "RESULT: PASS (vacuous - 0 files compared)\n";
    // A vacuous pass is reported as vacuous. A gate that silently passes on an empty
    // set is exactly how "measured nothing" comes to read as "measured clean".
    exit(0);
}

/**
 * Reduce a token stream to what the PHP engine actually consumes.
 *
 * @return array<int, array{0:string,1:string,2:int}>
 */
function significant(string $src, string $label): array {
    $raw = @token_get_all($src);
    if ($raw === false || $raw === []) {
        fail("could not tokenise {$label}");
    }
    $out = [];
    foreach ($raw as $tok) {
        if (is_string($tok)) {
            $out[] = ['CHAR', $tok, 0];
            continue;
        }
        [$id, $text, $line] = $tok;
        if ($id === T_WHITESPACE) {
            continue;
        }
        $name = token_name($id);
        if ($id === T_COMMENT || $id === T_DOC_COMMENT || $id === T_INLINE_HTML) {
            // Collapse runs of whitespace so a reindent is not read as a content change.
            $text = trim((string) preg_replace('/\s+/', ' ', $text));
        }
        $out[] = [$name, $text, (int) $line];
    }
    return $out;
}

/** Whitespace-sensitive view of inline HTML, for the advisory report only. */
function inlineHtmlExact(string $src): array {
    $raw = @token_get_all($src);
    $out = [];
    foreach ($raw ?: [] as $tok) {
        if (is_array($tok) && $tok[0] === T_INLINE_HTML) {
            $out[] = $tok[1];
        }
    }
    return $out;
}

$hardFailures = 0;
$htmlWhitespaceOnly = 0;
$filesCompared = 0;
$tokensCompared = 0;

foreach ($changed as $path) {
    $before = run('git show ' . escapeshellarg($base . ':' . $path), $st);
    if ($st !== 0) {
        echo "  SKIP  {$path} (new file - nothing to compare against)\n";
        continue;
    }
    if (!is_file($path)) {
        echo "  SKIP  {$path} (deleted)\n";
        continue;
    }
    $after = (string) file_get_contents($path);

    $a = significant($before, "{$base}:{$path}");
    $b = significant($after, $path);
    $filesCompared++;
    $tokensCompared += count($a);

    if (count($a) !== count($b)) {
        echo "  FAIL  {$path}\n";
        echo "        token count changed: " . count($a) . " -> " . count($b) . "\n";
        // Name the first divergence so the report points at a line, not a file.
        $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            if ($a[$i][0] !== $b[$i][0] || $a[$i][1] !== $b[$i][1]) {
                printf("        first divergence at token %d (line ~%d)\n", $i + 1, $b[$i][2]);
                printf("          before: %-22s %s\n", $a[$i][0], substr($a[$i][1], 0, 60));
                printf("          after : %-22s %s\n", $b[$i][0], substr($b[$i][1], 0, 60));
                break;
            }
        }
        $hardFailures++;
        continue;
    }

    $diverged = false;
    foreach ($a as $i => $tokA) {
        $tokB = $b[$i];
        if ($tokA[0] === $tokB[0] && $tokA[1] === $tokB[1]) {
            continue;
        }
        if (!$diverged) {
            echo "  FAIL  {$path}\n";
            $diverged = true;
            $hardFailures++;
        }
        printf("        token %d, line ~%d\n", $i + 1, $tokB[2]);
        printf("          before: %-22s %s\n", $tokA[0], substr($tokA[1], 0, 60));
        printf("          after : %-22s %s\n", $tokB[0], substr($tokB[1], 0, 60));
    }

    if ($diverged) {
        continue;
    }

    // Identical to the engine. Now say whether the bytes a BROWSER receives moved.
    $ha = inlineHtmlExact($before);
    $hb = inlineHtmlExact($after);
    if ($ha !== $hb) {
        $htmlWhitespaceOnly++;
        echo "  OK*   {$path}   (PHP identical; inline-HTML whitespace differs)\n";
    } else {
        echo "  OK    {$path}\n";
    }
}

echo "\n";
echo "files compared : {$filesCompared}\n";
echo "tokens compared: {$tokensCompared}\n";
echo "hard failures  : {$hardFailures}\n";
echo "inline-HTML whitespace-only differences: {$htmlWhitespaceOnly}\n";

if ($filesCompared === 0) {
    fail('every changed file was skipped - nothing was actually compared');
}

if ($hardFailures > 0) {
    echo "\nRESULT: FAIL - the PHP token stream CHANGED. This is not a whitespace-only edit.\n";
    exit(1);
}

if ($htmlWhitespaceOnly > 0) {
    echo "\nRESULT: PASS - no PHP token changed.\n";
    echo "NOTE: {$htmlWhitespaceOnly} file(s) changed inline-HTML whitespace. PHP behaviour is\n";
    echo "      unchanged, but the bytes sent to a browser moved. Review those on staging.\n";
    exit(0);
}

echo "\nRESULT: PASS - no PHP token changed, and no inline HTML moved.\n";
exit(0);
