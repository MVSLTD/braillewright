<?php
/**
 * Braillewright - fusion OPTION migration (period/pro DB keys -> braillewright).
 *
 * The 2026-06-19 fusion renamed the theme's internal namespace, so a few PERSISTED
 * keys changed name. Activating the fused theme reads the new names, so the old saved
 * values must be copied across or they appear reset.
 *
 * Scope (verified against the live TTT staging DB 2026-06-19, re-verified on all four
 * fleet sites 2026-08-25):
 *   options:
 *     period_layouts_set                     -> braillewright_layouts_set
 *     ct_period_pro_header_image_link_check  -> braillewright_features_header_image_link_check
 *   theme_mods: NONE renamed -- but the OPTION they live in is, which is
 *     tools/migrate-theme-mods.php's job, not this file's.
 *
 * ⛔⛔ POST META IS NO LONGER THIS FILE'S JOB, AND ITS OLD ENTRY WAS WRONG ON BOTH SIDES.
 * This script used to carry a single post-meta mapping, `period-last-updated` ->
 * `braillewright-last-updated`. Measured across all four fleet sites on 2026-08-25:
 * `period-last-updated` exists on NONE of them (the real stored key is
 * `ct_period_last_updated`) and `braillewright-last-updated` is read by NOTHING (the
 * theme reads `braillewright_last_updated`, with underscores). It matched zero rows on
 * every site -- a measured no-op that looked like coverage, and that appearance is most
 * of why the per-post gap went unnoticed through three migrations.
 *
 * ✅ Post meta now has a real tool: tools/migrate-postmeta-keys.php, driven by
 *    ops/backfill_postmeta.py, which derives the whole key map at run time instead of
 *    hardcoding one. RUN IT -- this file does not cover post meta at all.
 *
 * ⚠️ AND THE THEME CAN MAKE THIS SCRIPT SKIP ITS WORK.
 * `braillewright_features_set_header_image_link()` (features/inc/header-image.php) is
 * hooked `admin_init` and sets `braillewright_features_header_image_link_check` to 'yes'
 * on the first wp-admin page view -- and also writes `header_image_link`. The old guard
 * was `if ( null !== get_option( $bw_new, null ) ) { echo "skip: already set"; continue; }`,
 * so after that one page view this script reported a clean skip forever. ✅ It now
 * separates "already equal" (harmless) from "differs" (a real conflict), and a conflict
 * exits non-zero instead of reading as success.
 *
 * ⚠️ ON get_option( $name, null ) AS AN "IS IT SET" TEST. It is not exact: an option that
 * genuinely holds null is indistinguishable from an absent one. Neither key in the map
 * above ever holds null -- both are the literal string 'yes' -- so the test is sound for
 * THIS map, and the "differs" branch catches anything unexpected rather than overwriting
 * it. Do not copy the pattern to a map where null is a legal value.
 *
 * NOT touched (dead cruft the fused code no longer reads; leave or clean separately):
 *   ct_period_pro_active, ct_period_pro_license_key{,_status,_expires}, theme_mods_period.
 *
 *   wp eval-file tools/migrate-fusion-keys.php                                              # DRY-RUN
 *   wp eval "define('BW_FUSION_APPLY', true); require 'tools/migrate-fusion-keys.php';"     # APPLY
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every echo below is
 * CLI diagnostic text written to a terminal, never rendered as HTML. esc_html() actively
 * corrupted it: option names and var_export() output are full of quotes, which came out
 * as &#039; and made a conflict dump unreadable.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bw_apply = defined( 'BW_FUSION_APPLY' ) && BW_FUSION_APPLY;

echo $bw_apply
	? "=== Braillewright fusion OPTION migration: APPLY ===\n"
	: "=== Braillewright fusion OPTION migration: DRY-RUN (no writes) ===\n";

$bw_option_map = array(
	'period_layouts_set'                    => 'braillewright_layouts_set',
	'ct_period_pro_header_image_link_check' => 'braillewright_features_header_image_link_check',
);

$bw_problems = 0;

foreach ( $bw_option_map as $bw_old => $bw_new ) {

	$bw_old_val = get_option( $bw_old, null );

	if ( null === $bw_old_val ) {
		echo "  option  skip      '$bw_old' is not set\n";
		continue;
	}

	$bw_new_val = get_option( $bw_new, null );

	if ( null === $bw_new_val ) {
		echo "  option  copy      '$bw_old' -> '$bw_new'\n";
		if ( $bw_apply ) {
			update_option( $bw_new, $bw_old_val );
			// ⚠️ update_option() returns false BOTH when the write fails and when the
			// value was already identical, so its return value cannot be read as a
			// result. Read the option back instead.
			if ( get_option( $bw_new, null ) !== $bw_old_val ) {
				echo "  option  FAILED    '$bw_new' did not read back as written\n";
				++$bw_problems;
			}
		}
		continue;
	}

	// Present already. That is only harmless when the values AGREE -- otherwise the
	// theme's own first-boot write has landed on top of a real migrated value.
	if ( $bw_new_val === $bw_old_val ) {
		echo "  option  in step   '$bw_new' already holds the same value\n";
		continue;
	}

	++$bw_problems;
	echo "  option  CONFLICT  '$bw_new' exists and DIFFERS from '$bw_old'\n";
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- WP-CLI migration transcript, never shipped: the release zip and the wpcom artifact are theme/braillewright alone.
	echo '           old=' . var_export( $bw_old_val, true ) . "\n";
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- Pair of the line above; phpcs:ignore covers ONE line only.
	echo '           new=' . var_export( $bw_new_val, true ) . "\n";
	echo "           Most likely the theme wrote it on first boot BEFORE this ran.\n";
	echo "           Migrate BEFORE activating braillewright. Not overwriting.\n";
}

echo "\n";
echo "  post meta: NOT handled here. Run tools/migrate-postmeta-keys.php\n";
echo "             (driver: ops/backfill_postmeta.py) -- see the header of this file.\n\n";

if ( $bw_problems ) {
	echo "FAILED: $bw_problems option problem(s). Nothing was overwritten.\n";
	exit( 1 );
}

echo $bw_apply
	? "Done (applied).\n"
	: "Dry-run complete; re-run with BW_FUSION_APPLY defined to write.\n";
