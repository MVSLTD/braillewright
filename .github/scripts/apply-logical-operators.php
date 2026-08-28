<?php
/**
 * Rewrite the word logical operators to their symbol form: `or` -> `||`, `and` -> `&&`.
 *
 * WHY A TOOL AND NOT NINETEEN HAND EDITS
 * --------------------------------------
 * Stage 4 is the only stage phpcbf cannot do - `Squiz.Operators.ValidLogicalOperators`
 * has no fixer - so the alternative is editing files by hand, which is the thing this
 * project keeps being bitten by. A tool also means the next file that arrives carrying
 * `or` is handled by running something rather than by remembering.
 *
 * ⭐ IT USES PHP'S OWN LEXER, NOT A REGULAR EXPRESSION. `or` appears in this codebase
 * inside GPL headers, translated strings and comments far more often than it appears as
 * an operator - "version 3 of the License, or", "Layout can be overridden for any post
 * or page". A text substitution would corrupt every one of those. token_get_all() knows
 * the difference between T_LOGICAL_OR and the letters o and r.
 *
 * ⛔⛔ THIS TOOL MAKES NO SAFETY JUDGEMENT, DELIBERATELY.
 * `or` and `||` differ in PRECEDENCE, so the swap is inert only when nothing of
 * intermediate precedence shares the statement - assignment, `??`, `?:`, `yield`,
 * `throw`, `print`, or another word operator. That rule lives in exactly ONE place,
 * .github/scripts/assert-token-changes-expected.php, and this tool does not duplicate
 * it. It rewrites every occurrence and the GATE decides whether the result may ship.
 *
 * Two implementations of a safety rule are two things that drift, and the one that
 * drifts silently is always the copy. So the division is: this tool is dumb, the gate
 * is the authority, and a rewrite the gate refuses simply never gets committed.
 *
 * ⚠️ `xor` is left alone. PHP has no `^^`, so there is nothing to swap it to and any
 * "fix" would be a rewrite of the expression rather than a change of spelling.
 *
 * USAGE
 *   php apply-logical-operators.php --check <file>...   report only, change nothing
 *   php apply-logical-operators.php --apply <file>...   rewrite in place
 *
 * Exit 0 = done (or nothing to do). Exit 2 = it could not do its job.
 */

declare(strict_types=1);

const SWAP = [
    T_LOGICAL_OR => '||',
    T_LOGICAL_AND => '&&',
];

$args = array_slice($argv, 1);
$mode = null;
$files = [];
foreach ($args as $a) {
    if ($a === '--check' || $a === '--apply') {
        $mode = $a;
        continue;
    }
    $files[] = $a;
}
if ($mode === null || !$files) {
    fwrite(STDERR, "usage: apply-logical-operators.php --check|--apply <file>...\n");
    exit(2);
}

$totalSites = 0;
$totalFiles = 0;

foreach ($files as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "SKIP missing {$path}\n");
        continue;
    }
    $src = (string) file_get_contents($path);
    $toks = @token_get_all($src);
    if ($toks === false || $toks === []) {
        fwrite(STDERR, "ABORT: could not tokenise {$path}\n");
        exit(2);
    }

    $out = '';
    $hits = [];
    foreach ($toks as $tok) {
        if (is_string($tok)) {
            $out .= $tok;
            continue;
        }
        [$id, $text, $line] = $tok;
        if (isset(SWAP[$id])) {
            $hits[] = "{$line}: {$text} -> " . SWAP[$id];
            $out .= SWAP[$id];
            continue;
        }
        $out .= $text;
    }

    if (!$hits) {
        continue;
    }
    $totalFiles++;
    $totalSites += count($hits);
    echo ($mode === '--apply' ? 'REWROTE ' : 'WOULD REWRITE ') . $path . "\n";
    foreach ($hits as $h) {
        echo "    {$h}\n";
    }

    if ($mode === '--apply') {
        // Round-trip assertion: reassembling every token must reproduce the file
        // except for the operators we deliberately changed. If the rebuilt source
        // does not re-tokenise to the same length, something was lost and this stops
        // rather than writing a damaged file.
        $check = @token_get_all($out);
        if ($check === false || count($check) !== count($toks)) {
            fwrite(STDERR, "ABORT: rewrite of {$path} changed the token count "
                . count($toks) . ' -> ' . (is_array($check) ? count($check) : 'error')
                . ". Nothing written.\n");
            exit(2);
        }
        if (file_put_contents($path, $out) === false) {
            fwrite(STDERR, "ABORT: could not write {$path}\n");
            exit(2);
        }
    }
}

echo "\n";
echo ($mode === '--apply' ? 'rewritten' : 'would rewrite')
    . ": {$totalSites} operator(s) in {$totalFiles} file(s)\n";
echo "\n⚠️  This tool made NO safety judgement. Run the stage-4 token gate before\n";
echo "    committing - it is the only place the precedence rule lives:\n";
echo "      php .github/scripts/assert-token-changes-expected.php --stage 4 --base HEAD\n";
exit(0);
