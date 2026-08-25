<?php
/**
 * Braillewright - editor meta-box ID migration (period/pro -> braillewright).
 *
 * WHAT THIS IS FOR
 * ----------------
 * WordPress stores each user's editor-screen preferences in wp_usermeta under fixed
 * keys whose VALUES are meta-box IDs:
 *
 *     closedpostboxes_{screen}   array of ids the user collapsed
 *     metaboxhidden_{screen}     array of ids the user hid
 *     meta-box-order_{screen}    array of column => comma-joined id list
 *
 * The 2026-06-19 fusion renamed the theme's meta-box IDs
 * (ct_period_pro_video -> braillewright_features_video, and so on), so after a cutover
 * those stored preferences point at IDs that no longer exist. The visible effect is
 * small but real: panels return to their default order, and a box the user had
 * deliberately HIDDEN reappears, because the stale ID in metaboxhidden_ no longer
 * matches anything.
 *
 * ⚠️ Nothing published changes. This is an editor-screen-only repair.
 *
 * Measured across the fleet 2026-08-25: donnajodhan.com has TWO affected rows, both
 * belonging to a single user — closedpostboxes_post holding ct_period_pro_slider, and
 * meta-box-order_post holding ct_period_pro_fi_size, ct_period_pro_post_layout and
 * ct_period_last_updated. drkirkadams.com, toptechtidbits.com and
 * accessinformationnews.com have ZERO.
 *
 * THE MAP IS NOT HARDCODED. ops/djj_install.py extracts every meta-box ID the theme
 * registers straight from its add_meta_box() calls, inverts ops/fusion_rename.py's own
 * REPLACEMENTS to get the legacy names, and writes the pairs to a JSON file this script
 * reads. Hardcoding a map is how tools/migrate-fusion-keys.php came to ship one that
 * matched zero rows on every site.
 *
 * SAFETY
 *   * DRY-RUN by default. Define BW_METABOX_APPLY to write.
 *   * Only ever REPLACES a legacy id with its new name inside these three key families.
 *     No row is created or deleted, and any id it does not recognise is left alone.
 *   * Idempotent: a row containing no legacy id is skipped.
 *
 *   wp eval-file tools/migrate-metabox-ids.php
 *   wp eval "define('BW_METABOX_APPLY', true); require 'tools/migrate-metabox-ids.php';"
 *
 * Map file defaults to /tmp/bw-metabox-map.json; override with BW_METABOX_MAP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bw_apply    = defined( 'BW_METABOX_APPLY' ) && BW_METABOX_APPLY;
$bw_map_file = defined( 'BW_METABOX_MAP' ) ? BW_METABOX_MAP : '/tmp/bw-metabox-map.json';

echo $bw_apply
	? "=== Braillewright meta-box ID migration: APPLY ===\n"
	: "=== Braillewright meta-box ID migration: DRY-RUN (no writes) ===\n";

if ( ! is_readable( $bw_map_file ) ) {
	echo esc_html( "ABORT: map not readable at $bw_map_file" ) . "\n";
	return;
}

$bw_map = json_decode( file_get_contents( $bw_map_file ), true );
if ( ! is_array( $bw_map ) || ! $bw_map ) {
	echo esc_html( "ABORT: map at $bw_map_file did not decode to a non-empty array" ) . "\n";
	return;
}

echo 'Map: ' . count( $bw_map ) . " id pair(s)\n\n";

global $wpdb;

$bw_rows = $wpdb->get_results(
	"SELECT umeta_id, user_id, meta_key, meta_value
	   FROM {$wpdb->usermeta}
	  WHERE meta_key LIKE 'closedpostboxes\\_%'
	     OR meta_key LIKE 'metaboxhidden\\_%'
	     OR meta_key LIKE 'meta-box-order\\_%'"
);

$bw_changed = 0;

/**
 * Replace legacy ids anywhere inside a stored value, whatever its shape.
 * These values are arrays of ids, or arrays of column => comma-joined id string.
 */
function bw_metabox_replace( $value, array $map, &$hit ) {
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $k => $v ) {
			$out[ $k ] = bw_metabox_replace( $v, $map, $hit );
		}
		return $out;
	}
	if ( ! is_string( $value ) ) {
		return $value;
	}
	// Split on commas so a replacement can only ever match a WHOLE id, never a
	// substring of a longer one.
	$parts = explode( ',', $value );
	foreach ( $parts as $i => $part ) {
		$trimmed = trim( $part );
		if ( isset( $map[ $trimmed ] ) ) {
			$parts[ $i ] = $map[ $trimmed ];
			$hit         = true;
		}
	}
	return implode( ',', $parts );
}

foreach ( $bw_rows as $bw_row ) {

	$bw_value = maybe_unserialize( $bw_row->meta_value );
	$bw_hit   = false;
	$bw_new   = bw_metabox_replace( $bw_value, $bw_map, $bw_hit );

	if ( ! $bw_hit ) {
		continue;
	}

	++$bw_changed;
	echo esc_html( "  user {$bw_row->user_id}  {$bw_row->meta_key}" ) . "\n";
	echo esc_html( '      before: ' . substr( wp_json_encode( $bw_value ), 0, 200 ) ) . "\n";
	echo esc_html( '      after : ' . substr( wp_json_encode( $bw_new ), 0, 200 ) ) . "\n";

	if ( $bw_apply ) {
		update_user_meta( (int) $bw_row->user_id, $bw_row->meta_key, $bw_new );
	}
}

echo "\n";
echo $bw_apply
	? esc_html( "Done (applied). $bw_changed row(s) rewritten." ) . "\n"
	: esc_html( "Dry-run complete; $bw_changed row(s) would be rewritten." ) . "\n";
