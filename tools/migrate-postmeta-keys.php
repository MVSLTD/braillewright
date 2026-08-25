<?php
/**
 * Braillewright - PER-POST meta migration (Period / Period Pro DB keys -> braillewright).
 *
 * THE GAP THIS CLOSES
 * -------------------
 * The 2026-06-19 fusion renamed the theme's CODE namespace (see ops/fusion_rename.py).
 * `tools/migrate-fusion-keys.php` was meant to be the DATA half, but it carries only two
 * OPTIONS plus one post-meta key whose name is wrong on both sides: it maps
 * `period-last-updated` -> `braillewright-last-updated`, while a real Period Pro site
 * stores `ct_period_last_updated` and the fused theme reads `braillewright_last_updated`
 * (underscores, not hyphens).
 *
 * So every PER-POST setting Period Pro stored was left behind. Measured 2026-08-25:
 *   drkirkadams.com  45 published posts render differently -- 44 featured videos gone
 *   donnajodhan.com   8 published posts would lose their featured video on cutover
 *   toptechtidbits.com and accessinformationnews.com  0 -- neither ever used featured videos,
 *                     which is why the gap survived two prior migrations unnoticed
 *
 * ⚠️ TWO KEYS MUST TRAVEL TOGETHER OR THE VIDEO STAYS HIDDEN.
 * features/inc/featured-videos.php:268-273 is the render path:
 *     $display_blog = get_post_meta( $post->ID, 'braillewright_features_video_display_key', true );
 *     if ( ( is_singular() && ( $display_blog == 'post' || $display_blog == 'both' ) ) || ... )
 * There is NO empty()->'post' fallback there; that fallback exists only in the editor meta
 * box at line 70. An absent display key is '', which matches neither 'post' nor 'both', so
 * carrying `..._video_key` WITHOUT `..._video_display_key` fixes nothing on posts.
 *
 * HOW THE KEY MAP GETS HERE
 * -------------------------
 * It is NOT hardcoded in this file. `ops/backfill_postmeta.py` extracts every post-meta key
 * the theme actually reads straight from the theme source, inverts ops/fusion_rename.py's
 * rename rules to get each legacy name, and writes the resulting pairs to a JSON file that
 * this script reads. If the theme gains or renames a key, the map follows automatically and
 * nothing here needs editing.
 *
 * SAFETY
 *   * DRY-RUN by default. Define BW_POSTMETA_APPLY (truthy) to write.
 *   * NON-DESTRUCTIVE. Legacy keys are left exactly where they are, so the old theme still
 *     works and a revert needs no undo step.
 *   * IDEMPOTENT. A post is skipped when its NEW key already holds a non-empty value, so the
 *     newer value always wins and a second run is a no-op.
 *   * Empty legacy values are skipped -- writing '' would create a row that means nothing and
 *     would make the coverage gate's "already migrated" test lie.
 *
 * Usage (the driver does this for you):
 *   wp eval-file tools/migrate-postmeta-keys.php                                              # DRY-RUN
 *   wp eval "define('BW_POSTMETA_APPLY', true); require 'tools/migrate-postmeta-keys.php';"   # APPLY
 *
 * The map file path defaults to /tmp/bw-postmeta-map.json and can be overridden by defining
 * BW_POSTMETA_MAP before requiring this file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bw_apply    = defined( 'BW_POSTMETA_APPLY' ) && BW_POSTMETA_APPLY;
$bw_map_file = defined( 'BW_POSTMETA_MAP' ) ? BW_POSTMETA_MAP : '/tmp/bw-postmeta-map.json';

echo $bw_apply
	? "=== Braillewright post-meta migration: APPLY ===\n"
	: "=== Braillewright post-meta migration: DRY-RUN (no writes) ===\n";

if ( ! is_readable( $bw_map_file ) ) {
	echo "ABORT: key map not readable at {$bw_map_file}\n";
	return;
}

$bw_map = json_decode( file_get_contents( $bw_map_file ), true );
if ( ! is_array( $bw_map ) || ! $bw_map ) {
	echo "ABORT: key map at {$bw_map_file} did not decode to a non-empty array\n";
	return;
}

echo 'Map: ' . count( $bw_map ) . " key pair(s) from {$bw_map_file}\n\n";

global $wpdb;

$bw_total_moved   = 0;
$bw_total_skipped = 0;

foreach ( $bw_map as $bw_old => $bw_new ) {

	if ( ! is_string( $bw_old ) || ! is_string( $bw_new ) || '' === $bw_old || '' === $bw_new ) {
		echo "  skip  malformed map entry\n";
		continue;
	}

	// Every post carrying a NON-EMPTY legacy value that does not already carry a
	// NON-EMPTY new value. Post status is deliberately not filtered: a draft or a
	// scheduled post keeps its setting too.
	$bw_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT o.post_id, o.meta_value
			   FROM {$wpdb->postmeta} o
			  WHERE o.meta_key = %s
			    AND o.meta_value IS NOT NULL
			    AND o.meta_value <> ''
			    AND NOT EXISTS (
			        SELECT 1 FROM {$wpdb->postmeta} n
			         WHERE n.post_id  = o.post_id
			           AND n.meta_key = %s
			           AND n.meta_value IS NOT NULL
			           AND n.meta_value <> ''
			    )",
			$bw_old,
			$bw_new
		)
	);

	$bw_n = count( $bw_rows );
	echo esc_html( sprintf( '  %-44s -> %-46s %d post(s)', $bw_old, $bw_new, $bw_n ) ) . "\n";
	$bw_total_moved += $bw_n;

	if ( ! $bw_apply || ! $bw_n ) {
		continue;
	}

	foreach ( $bw_rows as $bw_row ) {
		update_post_meta( (int) $bw_row->post_id, $bw_new, $bw_row->meta_value );
	}
}

echo "\n";
echo $bw_apply
	? "Done (applied). {$bw_total_moved} post-meta value(s) written.\n"
	: "Dry-run complete; {$bw_total_moved} value(s) would be written. "
	  . "Re-run with BW_POSTMETA_APPLY defined.\n";
