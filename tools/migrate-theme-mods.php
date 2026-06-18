<?php
/**
 * Braillewright - theme_mods migration (period -> braillewright).
 *
 * Customizer settings (colours, logo, menus, layouts, and the Pro plugin's
 * settings) are stored in the option 'theme_mods_{slug}'. Switching a site
 * from the Period theme to Braillewright would otherwise reset them, so this
 * copies them across. Idempotent + non-destructive: it will NOT overwrite an
 * already-populated braillewright set unless BW_MIGRATE_FORCE is defined.
 *
 * Run over SSH in Phase 4 (STAGING first), BEFORE/around activating Braillewright:
 *   wp eval-file tools/migrate-theme-mods.php
 *
 * Note: the plugin's own options (ct_period_pro_*) are keyed by the retained
 * 'ct_period' prefix and carry over without migration.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$from = get_option( 'theme_mods_period' );
$to   = get_option( 'theme_mods_braillewright' );

if ( false === $from ) {
    echo "No theme_mods_period found; nothing to migrate.\n";
    return;
}
if ( is_array( $to ) && ! empty( $to ) && ! defined( 'BW_MIGRATE_FORCE' ) ) {
    echo "theme_mods_braillewright already populated; skipping (define BW_MIGRATE_FORCE to overwrite).\n";
    return;
}
update_option( 'theme_mods_braillewright', $from );
echo 'Copied ' . ( is_array( $from ) ? count( $from ) : 0 ) . " theme mod(s): period -> braillewright.\n";
