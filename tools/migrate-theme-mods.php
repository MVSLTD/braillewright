<?php
/**
 * Braillewright - theme_mods migration (period -> braillewright).
 *
 * Customizer settings (colours, logo, menus, layouts, and the former Pro plugin's
 * settings) live in the option 'theme_mods_{slug}'. Switching a site from Period to
 * Braillewright would otherwise reset them, so this copies them across.
 *
 * ⛔⛔ FOUR FAULTS WERE FIXED HERE ON 2026-08-25. EVERY ONE OF THEM FAILED SILENTLY.
 *
 * 1. IT READ THE WRONG OPTION ON A CHILD-THEME SITE.
 *    It was hardcoded to `get_option( 'theme_mods_period' )`. WordPress reads Customizer
 *    settings from `theme_mods_{get_option('stylesheet')}` -- the CHILD theme when one is
 *    active. drkirkadams.com ran `period-child`, so its live settings were in
 *    `theme_mods_period-child` (78 keys) while `theme_mods_period` was a stale 54-key
 *    fossil: 25 keys existed only in the child and 14 more held different values (brand
 *    blue #0071eb vs #3e9aff, footer year 2026 vs 2024, body text 22 vs 18). Copying the
 *    fossil populates the option with a plausible-looking payload and quietly reverts the
 *    design.
 *
 * 2. THE THEME ITSELF COULD MAKE THIS SCRIPT SKIP ITS ENTIRE JOB.
 *    `braillewright_set_default_layouts()` (functions.php) is hooked `after_setup_theme`
 *    with no guard. The first time Braillewright boots it sees `braillewright_layouts_set`
 *    unset and calls `set_theme_mod()` three times -- which CREATES
 *    `theme_mods_braillewright`. The old guard was `if ( is_array( $to ) && ! empty( $to ) )
 *    { echo "already populated; skipping"; return; }`, so from that moment this script
 *    refused to run and reported it as a clean skip. WP-CLI boots WordPress fully, so
 *    running this AFTER activation triggers the write during its own bootstrap and it can
 *    never succeed.
 *
 * 3. IT GUESSED WHEN IT COULD NOT RESOLVE THE SOURCE, WHILE CLAIMING NOT TO.
 *    The first repair still fell back to the literal 'period' when the stylesheet was
 *    unhelpful. A guess that is documented as "never guesses" is worse than no repair.
 *    ✅ It now ABORTS and asks to be told, via BW_MIGRATE_FROM.
 *
 * 4. FAILURE PATHS EXITED 0.
 *    "Source not found" and "destination already populated" both `return`ed, so a driver
 *    reading the exit code saw success. ✅ Every failure path now exits 1.
 *
 * ⛔ MIGRATE FIRST, ACTIVATE SECOND is still the rule. This file is now merely honest
 *    when it is used wrongly, rather than silently useless.
 *
 * Idempotent: re-running after a successful migration reports "already migrated" and
 * exits 0. Non-destructive: the source option is never modified or deleted.
 *
 *   wp eval-file tools/migrate-theme-mods.php
 *   wp eval "define('BW_MIGRATE_FROM','period-child'); require 'tools/migrate-theme-mods.php';"
 *   wp eval "define('BW_MIGRATE_FORCE', true); require 'tools/migrate-theme-mods.php';"
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every echo below is
 * CLI diagnostic text written to a terminal, never rendered as HTML. Escaping it turns
 * apostrophes in option names and messages into &#039; and makes the output harder to
 * read without making anything safer. Same rationale as the meta-elements echo in
 * theme/braillewright/functions.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keys braillewright_set_default_layouts() writes on first boot. A destination holding
 * nothing but these (plus WordPress's own numeric placeholder) is a bootstrap artefact,
 * not a migration.
 */
$braillewright_bootstrap_keys = array( 'layout_pages', 'layout_blog', 'layout_archives' );

$braillewright_stylesheet = (string) get_option( 'stylesheet' );
$braillewright_template   = (string) get_option( 'template' );

if ( defined( 'BW_MIGRATE_FROM' ) && BW_MIGRATE_FROM ) {
	$braillewright_from_slug = (string) BW_MIGRATE_FROM;
	echo "Source theme given explicitly: $braillewright_from_slug\n";
} elseif ( 'braillewright' !== $braillewright_stylesheet && '' !== $braillewright_stylesheet ) {
	$braillewright_from_slug = $braillewright_stylesheet;
} else {
	echo "ABORT: braillewright is already the active theme, so the live stylesheet can no\n";
	echo "       longer name the theme being migrated FROM, and this script will not guess.\n";
	echo "       Run it BEFORE activating braillewright, or name the source explicitly:\n";
	echo "         wp eval \"define('BW_MIGRATE_FROM','period'); require 'tools/migrate-theme-mods.php';\"\n";
	exit( 1 );
}

$braillewright_from_option = 'theme_mods_' . $braillewright_from_slug;

echo "Source option: $braillewright_from_option   (stylesheet=$braillewright_stylesheet, template=$braillewright_template)\n";

$braillewright_from = get_option( $braillewright_from_option );
$braillewright_to   = get_option( 'theme_mods_braillewright' );

if ( ! is_array( $braillewright_from ) || ! $braillewright_from ) {
	echo "ABORT: $braillewright_from_option not found, not an array, or empty. Nothing to migrate.\n";
	exit( 1 );
}

if ( is_array( $braillewright_to ) && $braillewright_to === $braillewright_from ) {
	echo 'Already migrated: theme_mods_braillewright is identical to ' . $braillewright_from_option
		. ' (' . count( $braillewright_from ) . " keys). Nothing to do.\n";
	return;
}

if ( is_array( $braillewright_to ) && ! empty( $braillewright_to ) && ! defined( 'BW_MIGRATE_FORCE' ) ) {

	// ⚠️ PHP casts a numeric string key to an int, so WordPress's own '0' placeholder
	// arrives here as int 0 and array_diff against a list of strings never removes it.
	// That alone left one "real" key behind and made the bootstrap check inert.
	$braillewright_real_keys = array();
	foreach ( array_keys( $braillewright_to ) as $braillewright_k ) {
		if ( is_int( $braillewright_k ) ) {
			continue;
		}
		if ( in_array( $braillewright_k, $braillewright_bootstrap_keys, true ) ) {
			continue;
		}
		$braillewright_real_keys[] = $braillewright_k;
	}

	if ( empty( $braillewright_real_keys ) ) {
		echo "theme_mods_braillewright holds only the keys the theme writes on its first boot\n";
		echo '(' . implode( ', ', $braillewright_bootstrap_keys ) . "). That is a bootstrap artefact, not a\n";
		echo "migration -- overwriting it.\n";
	} else {
		echo 'REFUSED: theme_mods_braillewright is genuinely populated (' . count( $braillewright_real_keys )
			. " key(s) beyond the bootstrap set). Nothing was copied.\n";
		echo "Define BW_MIGRATE_FORCE to overwrite it deliberately.\n";
		exit( 1 );
	}
}

update_option( 'theme_mods_braillewright', $braillewright_from );

// Prove the write, by VALUE and not merely by key count -- a count check passes on a
// wholesale corruption that happens to preserve the number of keys.
$braillewright_check = get_option( 'theme_mods_braillewright' );

if ( ! is_array( $braillewright_check ) || $braillewright_check !== $braillewright_from ) {
	echo "ABORT: the write did not read back identical to the source. Nothing can be\n";
	echo "       assumed about the destination -- inspect it before continuing.\n";
	exit( 1 );
}

echo 'Copied ' . count( $braillewright_check ) . " theme mod(s): $braillewright_from_option -> theme_mods_braillewright"
	. " (verified identical).\n";
