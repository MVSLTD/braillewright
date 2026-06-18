<?php
defined( 'ABSPATH' ) OR exit;

// Front-end
function ct_period_pro_enqueue_front_end_styles() {

	if ( is_rtl() ) {
		wp_enqueue_style( 'ct-period-pro-style-rtl', PERIOD_PRO_URL . 'styles/rtl.min.css' );
	} else {
		wp_enqueue_style( 'ct-period-pro-style', PERIOD_PRO_URL . 'styles/style.min.css' );
	}
	// main JS file (ct-period-js dependency contains fitvids)
	wp_enqueue_script( 'ct-period-pro-js', PERIOD_PRO_URL . 'js/build/functions.min.js', array(
		'jquery',
		'ct-period-js'
	), '', true );
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_enqueue_front_end_styles', 11 );

// Back-end
function ct_period_pro_enqueue_admin_styles( $hook ) {

	if ( $hook == 'post.php' || $hook == 'post-new.php' ) {

		// Admin CSS
		wp_enqueue_style( 'ct-period-pro-admin-style', PERIOD_PRO_URL . 'styles/admin.min.css' );

		// Fitvids JS
		wp_enqueue_script( 'fitvids', PERIOD_PRO_URL . 'js/fitvids.js', array( 'jquery' ), '', true );

		// Admin JS
		wp_enqueue_script( 'ct-period-pro-admin-js', PERIOD_PRO_URL . 'js/build/admin.min.js', array(
			'jquery',
			'fitvids'
		), '', true );
	}
	if ( $hook == 'appearance_page_period-options' ) {
		// Admin CSS
		wp_enqueue_style( 'ct-period-pro-admin-style', PERIOD_PRO_URL . 'styles/admin.min.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'ct_period_pro_enqueue_admin_styles' );

// Customizer
function ct_period_pro_enqueue_customizer_scripts() {
	wp_enqueue_script( 'ct-period-pro-customizer-js', PERIOD_PRO_URL . 'js/build/customizer.min.js', array( 'jquery' ), '', true );
	wp_enqueue_style( 'ct-period-pro-customizer-css', PERIOD_PRO_URL . 'styles/customizer.min.css' );

	wp_localize_script( 'ct-period-pro-customizer-js', 'ct_period_pro_objectL10n', array(
		'PERIOD_PRO_URL' => PERIOD_PRO_URL
	) );
}
add_action( 'customize_controls_enqueue_scripts', 'ct_period_pro_enqueue_customizer_scripts' );

/*
 * Script for live updating with customizer options. Has to be loaded separately on customize_preview_init hook
 * transport => postMessage
 */
function ct_period_pro_enqueue_customizer_post_message_scripts() {
	wp_enqueue_script( 'ct-period-pro-post-message-js', PERIOD_PRO_URL . 'js/build/postMessage.min.js', array( 'jquery' ), '', true );

	wp_localize_script( 'ct-period-pro-post-message-js', 'ct_period_pro_objectL10n', array(
		'PERIOD_PRO_URL' => PERIOD_PRO_URL
	) );
}
add_action( 'customize_preview_init', 'ct_period_pro_enqueue_customizer_post_message_scripts' );