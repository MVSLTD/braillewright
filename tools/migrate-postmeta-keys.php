<?php
/**
 * Braillewright - PER-POST meta migration (Period / Period Pro DB keys -> braillewright).
 *
 * THE GAP THIS CLOSES
 * -------------------
 * The 2026-06-19 fusion renamed the theme's CODE namespace (see ops/fusion_rename.py).
 * `tools/migrate-fusion-keys.php` was meant to be the DATA half, but it carried only two
 * OPTIONS plus one post-meta key whose name was wrong on both sides: it mapped
 * `period-last-updated` -> `braillewright-last-updated`, while a real Period Pro site
 * stores `ct_period_last_updated` and the fused theme reads `braillewright_last_updated`
 * (underscores, not hyphens). That mapping matched ZERO rows on all four fleet sites.
 *
 * So every PER-POST setting Period Pro stored was left behind. Measured 2026-08-25:
 *   drkirkadams.com  45 published posts render differently -- 44 featured videos gone
 *   donnajodhan.com   8 published posts would lose their featured video on cutover
 *   toptechtidbits.com and accessinformationnews.com  0 -- neither ever used featured
 *                     videos, which is why the gap survived two prior migrations unseen
 *
 * ⚠️ TWO KEYS MUST TRAVEL TOGETHER OR THE VIDEO STAYS HIDDEN.
 * features/inc/featured-videos.php's render path reads:
 *     $display_blog = get_post_meta( $post->ID, 'braillewright_features_video_display_key', true );
 *     if ( ( is_singular() && ( $display_blog == 'post' || $display_blog == 'both' ) ) || ... )
 * There is NO empty()->'post' fallback there; that fallback exists only in the editor
 * meta box. An absent display key is '', which matches neither 'post' nor 'both', so
 * carrying the video URL WITHOUT its display key fixes nothing on posts.
 *
 * ⚠️ AND MOVING THE DATA IS NOT SUFFICIENT ON ITS OWN. On drkirkadams.com this migration
 * completed perfectly and all 44 videos were still invisible, because functions.php ran
 * the featured-image slot through wp_kses_post(), which strips <iframe>. Always verify
 * that a video RENDERS, not merely that its row moved.
 *
 * HOW THE KEY MAP GETS HERE
 * -------------------------
 * It is NOT hardcoded. `ops/backfill_postmeta.py` (and `ops/djj_install.py`) extract every
 * post-meta key the theme actually reads from the theme source, invert
 * ops/fusion_rename.py's own rename rules to get each legacy name, and let the LIVE
 * DATABASE resolve which candidate exists. The pairs arrive here as JSON. Hardcoding a
 * map is exactly how the original tool came to ship one that was wrong on both sides.
 *
 * SAFETY
 *   * DRY-RUN by default. Define BW_POSTMETA_APPLY (truthy) to write.
 *   * NON-DESTRUCTIVE. Legacy keys are left exactly where they are, so the old theme
 *     still works and a revert needs no undo step.
 *   * IDEMPOTENT. A post is skipped when its NEW key already holds a non-empty value, so
 *     the newer value always wins and a second run is a no-op.
 *   * Empty legacy values are skipped -- writing '' would create a row that means nothing
 *     and would make the coverage gate's "already migrated" test lie.
 *
 * Usage (the driver does this for you):
 *   wp eval-file tools/migrate-postmeta-keys.php                                              # DRY-RUN
 *   wp eval "define('BW_POSTMETA_APPLY', true); require 'tools/migrate-postmeta-keys.php';"   # APPLY
 *
 * Map file defaults to /tmp/bw-postmeta-map.json; override with BW_POSTMETA_MAP.
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every echo below is
 * CLI diagnostic text written to a terminal, never rendered as HTML. Escaping it turns
 * the quotes in key names and paths into &#039; and makes the report harder to read
 * without making anything safer.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$braillewright_apply    = defined( 'BW_POSTMETA_APPLY' ) && BW_POSTMETA_APPLY;
$braillewright_map_file = defined( 'BW_POSTMETA_MAP' ) ? BW_POSTMETA_MAP : '/tmp/bw-postmeta-map.json';

echo $braillewright_apply
	? "=== Braillewright post-meta migration: APPLY ===\n"
	: "=== Braillewright post-meta migration: DRY-RUN (no writes) ===\n";

if ( ! is_readable( $braillewright_map_file ) ) {
	echo "ABORT: key map not readable at {$braillewright_map_file}\n";
	exit( 1 );
}

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local map file written by the installer moments earlier; wp_remote_get is for URLs.
$braillewright_map = json_decode( (string) file_get_contents( $braillewright_map_file ), true );

if ( ! is_array( $braillewright_map ) || ! $braillewright_map ) {
	echo "ABORT: key map at {$braillewright_map_file} did not decode to a non-empty array\n";
	exit( 1 );
}

echo 'Map: ' . count( $braillewright_map ) . " key pair(s) from {$braillewright_map_file}\n\n";

global $wpdb;

$braillewright_total_moved = 0;
$braillewright_failures    = 0;

foreach ( $braillewright_map as $braillewright_old => $braillewright_new ) {

	if ( ! is_string( $braillewright_old ) || ! is_string( $braillewright_new ) || '' === $braillewright_old || '' === $braillewright_new ) {
		echo "  skip  malformed map entry\n";
		++$braillewright_failures;
		continue;
	}

	// Every post carrying a NON-EMPTY legacy value that does not already carry a
	// NON-EMPTY new value. Post status is deliberately not filtered: a draft or a
	// scheduled post keeps its setting too.
	$braillewright_rows = $wpdb->get_results(
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
			$braillewright_old,
			$braillewright_new
		)
	);

	$braillewright_n = is_array( $braillewright_rows ) ? count( $braillewright_rows ) : 0;
	printf( "  %-44s -> %-46s %d post(s)\n", $braillewright_old, $braillewright_new, $braillewright_n );
	$braillewright_total_moved += $braillewright_n;

	if ( ! $braillewright_apply || ! $braillewright_n ) {
		continue;
	}

	foreach ( $braillewright_rows as $braillewright_row ) {
		update_post_meta( (int) $braillewright_row->post_id, $braillewright_new, $braillewright_row->meta_value );

		// ⚠️ update_post_meta() returns false BOTH on failure and when the value was
		// already identical, so its return value is not a result. Read it back.
		$braillewright_readback = get_post_meta( (int) $braillewright_row->post_id, $braillewright_new, true );
		if ( (string) $braillewright_readback !== (string) $braillewright_row->meta_value ) {
			echo "      FAILED post {$braillewright_row->post_id}: {$braillewright_new} did not read back as written\n";
			++$braillewright_failures;
		}
	}
}

echo "\n";

if ( $braillewright_failures ) {
	echo "FAILED: {$braillewright_failures} problem(s). Inspect before continuing.\n";
	exit( 1 );
}

echo $braillewright_apply
	? "Done (applied). {$braillewright_total_moved} post-meta value(s) written and verified.\n"
	: "Dry-run complete; {$braillewright_total_moved} value(s) would be written. "
		. "Re-run with BW_POSTMETA_APPLY defined.\n";
