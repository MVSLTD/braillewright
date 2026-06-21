<?php
/**
 * Braillewright self-update channel.
 *
 * Braillewright is given away free OUTSIDE the WordPress.org directory
 * (community-maintained by Top Tech Tidbits), so it ships its own update checker
 * — the GPL "Plugin Update Checker" library in lib/ — pointed at a self-hosted
 * JSON manifest. When a newer version is published there, every site running
 * Braillewright is offered the update through the normal WordPress update UI.
 *
 * Per the project's accessibility + security commitment, auto-updates are forced
 * ON for this theme (the auto_update_theme filter below) and a transparent notice
 * tells the site owner. This is GPL software on the owner's own server, so it is
 * not an absolute lock — a developer can change this file — but it is the default
 * for everyone who installs the theme as shipped.
 *
 * The checker is wired up ONLY in the contexts that actually manage updates —
 * wp-admin, WP-Cron (the scheduled check + auto-apply) and WP-CLI. It is
 * deliberately not loaded on ordinary front-end page views: a public request
 * never manages updates, so running the third-party library there adds nothing
 * and keeps its admin/update machinery (and any failure in it) away from site
 * visitors.
 */

defined( 'ABSPATH' ) OR exit;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/*
 * Force automatic updates ON for Braillewright, overriding the per-theme toggle,
 * so accessibility + security fixes reach every site without manual action.
 */
function braillewright_force_auto_update( $update, $item ) {
	$slug = '';
	if ( isset( $item->theme ) ) {
		$slug = $item->theme;
	} elseif ( isset( $item->slug ) ) {
		$slug = $item->slug;
	}
	return ( 'braillewright' === $slug ) ? true : $update;
}

/*
 * Transparent notice: WordPress does not reflect a forced auto-update in the UI,
 * so tell the site owner about the policy on theme-related admin screens.
 */
function braillewright_auto_update_notice() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'dashboard', 'update-core' ), true ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p>'
		. esc_html__( 'This theme keeps itself updated for your security and ongoing accessibility.', 'braillewright' )
		. '</p></div>';
}

/*
 * Only load + wire the update checker where updates are actually managed:
 * wp-admin, WP-Cron and WP-CLI. A normal front-end request never manages
 * updates, so the third-party library is not loaded there at all.
 */
if (
	is_admin()
	|| wp_doing_cron()
	|| ( defined( 'WP_CLI' ) && WP_CLI )
) {
	require_once trailingslashit( get_template_directory() ) . 'lib/plugin-update-checker/plugin-update-checker.php';

	/*
	 * Build the update checker. The second argument must point at a file in the
	 * theme ROOT — PUC reads the adjacent style.css to identify the theme and its
	 * version — so we pass the theme's style.css. (Passing __FILE__ would fail:
	 * this file lives in inc/, and PUC only looks for style.css in that same
	 * directory.) The manifest URL is filterable so a staging/dev site can point
	 * at a test endpoint via the 'braillewright_update_manifest_url' filter
	 * without editing this file.
	 */
	PucFactory::buildUpdateChecker(
		apply_filters(
			'braillewright_update_manifest_url',
			'https://toptechtidbits.com/wp-content/uploads/braillewright/details.json'
		),
		get_template_directory() . '/style.css',
		'braillewright'
	);

	add_filter( 'auto_update_theme', 'braillewright_force_auto_update', 10, 2 );
	add_action( 'admin_notices', 'braillewright_auto_update_notice' );
}
