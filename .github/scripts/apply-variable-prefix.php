<?php
/**
 * Re-prefix file-scope variables: $bw_foo -> $braillewright_foo.
 *
 * WHY A TOOL
 * ----------
 * WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound has no fixer, so
 * the alternative is editing 171 occurrences across four files by hand. It is also the
 * single most mechanical change in the whole sweep, which makes hand-editing exactly the
 * wrong instrument.
 *
 * ⭐ IT USES PHP'S LEXER, NOT A REGULAR EXPRESSION, and here that is not a nicety.
 * `$bw_map_file` appears inside COMMENTS in these files - historical notes recording what
 * a line used to print - and inside DOUBLE-QUOTED STRINGS as an interpolation. A text
 * substitution cannot tell those three cases apart. token_get_all() can:
 *
 *   T_VARIABLE                     renamed - this is the code
 *   T_VARIABLE inside "{$x}"       ALSO renamed, correctly: an interpolation really is
 *                                  a reference to the variable and must follow it
 *   T_COMMENT / T_DOC_COMMENT      left alone - prose, and in this repo the two hits are
 *                                  deliberate records of PAST code
 *
 * ⛔⛔ IT RENAMES VARIABLES ONLY, AND NEVER CONSTANTS.
 * BW_FUSION_APPLY, BW_POSTMETA_APPLY, BW_POSTMETA_MAP, BW_METABOX_APPLY and
 * BW_METABOX_MAP are a LIVE PUBLIC API: ops/djj_install.py, ops/ain_install.py,
 * ops/backfill_postmeta.py and ops/deploy_fusion.py all run
 *     wp eval "define('BW_FUSION_APPLY', true); require '<tool>';"
 * over SSH against production hosts. Renaming one would break every installer silently -
 * the define would simply not match the defined() check and the tool would run in
 * dry-run mode while reporting success. Constants are T_STRING, this only touches
 * T_VARIABLE, and that separation is the safeguard.
 *
 * ⛔ NO SAFETY JUDGEMENT HERE EITHER, same division as apply-logical-operators.php.
 * Whether a rename is CONSISTENT, whether it COLLIDES with a name that already exists,
 * and whether the variable is one the file binds with `global` are whole-file questions,
 * and they are answered in exactly one place: validateRenames() inside
 * assert-token-changes-expected.php. This tool rewrites; the gate judges.
 *
 * USAGE
 *   php apply-variable-prefix.php --from '$bw_' --to '$braillewright_' --check <file>...
 *   php apply-variable-prefix.php --from '$bw_' --to '$braillewright_' --apply <file>...
 *
 * Exit 0 = done. Exit 2 = it could not do its job.
 */

declare(strict_types=1);

$args = array_slice($argv, 1);
$mode = null;
$from = null;
$to = null;
$files = [];
for ($i = 0; $i < count($args); $i++) {
    $a = $args[$i];
    if ($a === '--check' || $a === '--apply') {
        $mode = $a;
    } elseif ($a === '--from') {
        $from = $args[++$i] ?? null;
    } elseif ($a === '--to') {
        $to = $args[++$i] ?? null;
    } else {
        $files[] = $a;
    }
}
if ($mode === null || $from === null || $to === null || !$files) {
    fwrite(STDERR, "usage: apply-variable-prefix.php --from '\$old_' --to '\$new_' "
        . "--check|--apply <file>...\n");
    exit(2);
}
if ($from[0] !== '$' || $to[0] !== '$') {
    fwrite(STDERR, "ABORT: --from and --to must both begin with \$\n");
    exit(2);
}

$totalSites = 0;
$totalFiles = 0;
$names = [];

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
    $hits = 0;
    foreach ($toks as $tok) {
        if (is_string($tok)) {
            $out .= $tok;
            continue;
        }
        [$id, $text] = $tok;
        if ($id === T_VARIABLE && str_starts_with($text, $from)) {
            $renamed = $to . substr($text, strlen($from));
            $names[$text] = $renamed;
            $hits++;
            $out .= $renamed;
            continue;
        }
        $out .= $text;
    }

    if (!$hits) {
        continue;
    }
    $totalFiles++;
    $totalSites += $hits;
    echo ($mode === '--apply' ? 'REWROTE ' : 'WOULD REWRITE ') . $path
        . "  ({$hits} occurrence(s))\n";

    if ($mode === '--apply') {
        // Round trip: the rebuilt source must tokenise to the SAME token count. A
        // rename cannot add or remove a token, so a difference means something was
        // lost and nothing gets written.
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

echo "\n" . ($mode === '--apply' ? 'renamed' : 'would rename')
    . ": {$totalSites} occurrence(s) of " . count($names) . " distinct name(s) in "
    . "{$totalFiles} file(s)\n\n";
ksort($names);
foreach ($names as $old => $new) {
    echo "    {$old}  ->  {$new}\n";
}

echo "\n⚠️  NO safety judgement was made here. Consistency, collisions and `global`\n";
echo "    bindings are checked by the stage-5 token gate, which is the only place\n";
echo "    that rule lives:\n";
echo "      php .github/scripts/assert-token-changes-expected.php --stage 5 --base HEAD\n";
exit(0);
