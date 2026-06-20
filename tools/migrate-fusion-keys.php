<?php
/**
 * Braillewright - fusion key migration (period/pro DB keys -> braillewright).
 *
 * The 2026-06-19 fusion renamed the theme's internal namespace, so a few PERSISTED
 * keys changed name. Activating the fused theme reads the new names, so the old saved
 * values must be copied across or they appear reset.
 *
 * Scope (verified against the live TTT staging DB, 2026-06-19):
 *   options:
 *     period_layouts_set                     -> braillewright_layouts_set
 *     ct_period_pro_header_image_link_check  -> braillewright_features_header_image_link_check
 *   post-meta:
 *     period-last-updated                    -> braillewright-last-updated
 *   theme_mods: NONE. The theme_mods_braillewright keys are all plain (layout_*,
 *     colors_*, *_font_size_*, header_image_*, ct_widget_*) and were not renamed.
 *
 * NOT touched (dead cruft the fused code no longer reads; leave or clean separately):
 *   ct_period_pro_active, ct_period_pro_license_key{,_status,_expires}, theme_mods_period.
 *
 * Idempotent + non-destructive: copies old -> new only when the new key is absent,
 * and leaves the old keys in place as harmless orphans. DRY-RUN by default; define
 * BW_FUSION_APPLY (truthy) to actually write.
 *
 * Run over SSH on STAGING first, around activating the fused theme:
 *   wp eval-file tools/migrate-fusion-keys.php                                              # DRY-RUN
 *   wp eval "define('BW_FUSION_APPLY', true); require 'tools/migrate-fusion-keys.php';"     # APPLY
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bw_apply = defined( 'BW_FUSION_APPLY' ) && BW_FUSION_APPLY;

echo $bw_apply
	? "=== Braillewright fusion migration: APPLY ===\n"
	: "=== Braillewright fusion migration: DRY-RUN (no writes) ===\n";

$bw_option_map = array(
	'period_layouts_set'                    => 'braillewright_layouts_set',
	'ct_period_pro_header_image_link_check' => 'braillewright_features_header_image_link_check',
);

foreach ( $bw_option_map as $bw_old => $bw_new ) {
	$bw_old_val = get_option( $bw_old, null );
	if ( null === $bw_old_val ) {
		echo esc_html( "  option  skip  '$bw_old' is not set" ) . "\n";
		continue;
	}
	if ( null !== get_option( $bw_new, null ) ) {
		echo esc_html( "  option  skip  '$bw_new' already set" ) . "\n";
		continue;
	}
	echo esc_html( "  option  copy  '$bw_old' -> '$bw_new'" ) . "\n";
	if ( $bw_apply ) {
		update_option( $bw_new, $bw_old_val );
	}
}

global $wpdb;

$bw_meta_map = array(
	'period-last-updated' => 'braillewright-last-updated',
);

foreach ( $bw_meta_map as $bw_old => $bw_new ) {
	$bw_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = %s
			 AND post_id NOT IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s )",
			$bw_old,
			$bw_new
		)
	);
	echo esc_html( "  meta    '$bw_old' -> '$bw_new' on " . count( $bw_ids ) . ' post(s)' ) . "\n";
	if ( $bw_apply ) {
		foreach ( $bw_ids as $bw_pid ) {
			update_post_meta( (int) $bw_pid, $bw_new, get_post_meta( (int) $bw_pid, $bw_old, true ) );
		}
	}
}

echo $bw_apply
	? "Done (applied).\n"
	: "Dry-run complete; re-run with BW_FUSION_APPLY defined to write.\n";
