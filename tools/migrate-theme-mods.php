<?php
/**
 * Braillewright - theme_mods migration (period -> braillewright).
 *
 * Customizer settings (colours, logo, menus, layouts, and the former Pro plugin's
 * settings) live in the option 'theme_mods_{slug}'. Switching a site from Period to
 * Braillewright would otherwise reset them, so this copies them across.
 *
 * ⛔⛔ TWO FAULTS WERE FIXED HERE ON 2026-08-25. BOTH FAILED SILENTLY AND BOTH PRINTED
 * SUCCESS-SHAPED OUTPUT.
 *
 * 1. IT READ THE WRONG OPTION ON A CHILD-THEME SITE.
 *    It was hardcoded to `get_option( 'theme_mods_period' )`. WordPress reads Customizer
 *    settings from `theme_mods_{get_option('stylesheet')}` -- the CHILD theme when one is
 *    active. drkirkadams.com ran `period-child`, so its live settings were in
 *    `theme_mods_period-child` (78 keys) while `theme_mods_period` was a stale 54-key
 *    fossil: 25 keys existed only in the child and 14 more held different values (brand
 *    blue #0071eb vs #3e9aff, footer year 2026 vs 2024, body text 22 vs 18). Copying the
 *    fossil populates the option with a plausible-looking payload and quietly reverts the
 *    design. The ops-side installer learned this and resolved the stylesheet at runtime;
 *    this file, the one an adopter would actually run, did not.
 *    ✅ It now resolves the source from the live `stylesheet` and never guesses.
 *
 * 2. THE THEME ITSELF COULD MAKE THIS SCRIPT SKIP ITS ENTIRE JOB.
 *    `braillewright_set_default_layouts()` (functions.php) is hooked `after_setup_theme`
 *    with no guard. The first time Braillewright boots it sees `braillewright_layouts_set`
 *    unset and calls `set_theme_mod()` three times -- which CREATES
 *    `theme_mods_braillewright`. The old guard here was `if ( is_array( $to ) && ! empty(
 *    $to ) ) { echo "already populated; skipping"; return; }`, so from that moment on this
 *    script refused to run and reported it as a clean skip. WP-CLI boots WordPress fully,
 *    so running `wp eval-file tools/migrate-theme-mods.php` AFTER activation triggers the
 *    write during its own bootstrap and can never succeed. The net effect is the entire
 *    Period design lost while both migration tools print success.
 *    ✅ A destination that contains ONLY the keys that bootstrap writes is now recognised
 *    as "not really migrated" and is overwritten. A genuinely populated destination still
 *    stops the run -- but now with a NON-ZERO exit, so a caller cannot mistake it for
 *    success.
 *
 * ⛔ MIGRATE FIRST, ACTIVATE SECOND. That ordering is still the rule; this file is now
 *    merely honest when it is broken rather than silently useless.
 *
 * Idempotent + non-destructive: the source option is never modified or deleted.
 *
 *   wp eval-file tools/migrate-theme-mods.php
 *   wp eval "define('BW_MIGRATE_FORCE', true); require 'tools/migrate-theme-mods.php';"
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The keys braillewright_set_default_layouts() writes on first boot. A destination
// holding nothing but these is a bootstrap artefact, not a migration.
$bw_bootstrap_only_keys = array( 'layout_pages', 'layout_blog', 'layout_archives' );

$bw_stylesheet = get_option( 'stylesheet' );
$bw_template   = get_option( 'template' );

if ( 'braillewright' === $bw_stylesheet ) {
	// Braillewright is already active, so the live stylesheet can no longer name the
	// theme we are migrating FROM. Fall back to the parent recorded on the site, then
	// to 'period', and say plainly which was used.
	$bw_from_slug = ( $bw_template && 'braillewright' !== $bw_template ) ? $bw_template : 'period';
	echo esc_html( "NOTE: braillewright is already the active theme; sourcing from 'theme_mods_$bw_from_slug'." ) . "\n";
	echo "      Run this BEFORE activating braillewright so the active stylesheet can be trusted.\n";
} else {
	$bw_from_slug = $bw_stylesheet ? $bw_stylesheet : 'period';
}

$bw_from_option = 'theme_mods_' . $bw_from_slug;

echo esc_html( "Source option: $bw_from_option   (stylesheet=$bw_stylesheet, template=$bw_template)" ) . "\n";

$bw_from = get_option( $bw_from_option );
$bw_to   = get_option( 'theme_mods_braillewright' );

if ( false === $bw_from || ! is_array( $bw_from ) || ! $bw_from ) {
	echo esc_html( "ABORT: $bw_from_option not found or empty; nothing to migrate." ) . "\n";
	return;
}

if ( is_array( $bw_to ) && ! empty( $bw_to ) && ! defined( 'BW_MIGRATE_FORCE' ) ) {

	$bw_real_keys = array_diff( array_keys( $bw_to ), $bw_bootstrap_only_keys );

	if ( empty( $bw_real_keys ) ) {
		echo "theme_mods_braillewright holds only the keys the theme writes on its first\n";
		echo "boot (" . esc_html( implode( ', ', $bw_bootstrap_only_keys ) ) . "). That is a bootstrap\n";
		echo "artefact, not a migration -- overwriting it.\n";
	} else {
		echo esc_html(
			'REFUSED: theme_mods_braillewright is genuinely populated (' . count( $bw_real_keys )
			. ' key(s) beyond the bootstrap set). Nothing was copied.'
		) . "\n";
		echo "Define BW_MIGRATE_FORCE to overwrite it deliberately.\n";
		// ⚠️ Non-zero, because this used to read as a successful no-op.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::halt( 1 );
		}
		return;
	}
}

update_option( 'theme_mods_braillewright', $bw_from );

$bw_check = get_option( 'theme_mods_braillewright' );
$bw_n     = is_array( $bw_check ) ? count( $bw_check ) : 0;

if ( $bw_n !== count( $bw_from ) ) {
	echo esc_html( "ABORT: wrote $bw_n key(s) but the source had " . count( $bw_from ) . '.' ) . "\n";
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::halt( 1 );
	}
	return;
}

echo esc_html( "Copied $bw_n theme mod(s): $bw_from_option -> theme_mods_braillewright." ) . "\n";
