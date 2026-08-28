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

$pass = 0;
$bad = [];
$n = 0;

foreach ($cases as [$name, $expect, $before, $after]) {
    $n++;
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
        . ' --stage 2 --pairs ' . escapeshellarg($man) . ' 2>&1';
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

$total = count($cases);
$mustPass = count(array_filter($cases, static fn($c): bool => $c[1] === 'PASS'));
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
