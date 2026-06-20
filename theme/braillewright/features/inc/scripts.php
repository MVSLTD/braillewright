<?php
defined( 'ABSPATH' ) OR exit;

// Front-end
function braillewright_features_enqueue_front_end_styles() {

	if ( is_rtl() ) {
		wp_enqueue_style( 'braillewright-features-style-rtl', BRAILLEWRIGHT_FEATURES_URL . 'styles/rtl.min.css' );
	} else {
		wp_enqueue_style( 'braillewright-features-style', BRAILLEWRIGHT_FEATURES_URL . 'styles/style.min.css' );
	}
	// main JS file (braillewright-js dependency contains fitvids)
	wp_enqueue_script( 'braillewright-features-js', BRAILLEWRIGHT_FEATURES_URL . 'js/build/functions.min.js', array(
		'jquery',
		'braillewright-js'
	), '', true );
}
add_action( 'wp_enqueue_scripts', 'braillewright_features_enqueue_front_end_styles', 11 );

// Back-end
function braillewright_features_enqueue_admin_styles( $hook ) {

	if ( $hook == 'post.php' || $hook == 'post-new.php' ) {

		// Admin CSS
		wp_enqueue_style( 'braillewright-features-admin-style', BRAILLEWRIGHT_FEATURES_URL . 'styles/admin.min.css' );

		// Fitvids JS
		wp_enqueue_script( 'fitvids', BRAILLEWRIGHT_FEATURES_URL . 'js/fitvids.js', array( 'jquery' ), '', true );

		// Admin JS
		wp_enqueue_script( 'braillewright-features-admin-js', BRAILLEWRIGHT_FEATURES_URL . 'js/build/admin.min.js', array(
			'jquery',
			'fitvids'
		), '', true );

		// Nonce for the add_oembed AJAX video preview (consumed by admin.min.js as braillewright_features_admin.nonce).
		wp_localize_script( 'braillewright-features-admin-js', 'braillewright_features_admin', array(
			'nonce' => wp_create_nonce( 'braillewright_features_add_oembed' ),
		) );
	}
	if ( $hook == 'appearance_page_braillewright-options' ) {
		// Admin CSS
		wp_enqueue_style( 'braillewright-features-admin-style', BRAILLEWRIGHT_FEATURES_URL . 'styles/admin.min.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'braillewright_features_enqueue_admin_styles' );

// Customizer
function braillewright_features_enqueue_customizer_scripts() {
	wp_enqueue_script( 'braillewright-features-customizer-js', BRAILLEWRIGHT_FEATURES_URL . 'js/build/customizer.min.js', array( 'jquery' ), '', true );
	wp_enqueue_style( 'braillewright-features-customizer-css', BRAILLEWRIGHT_FEATURES_URL . 'styles/customizer.min.css' );

	wp_localize_script( 'braillewright-features-customizer-js', 'braillewright_features_objectL10n', array(
		'BRAILLEWRIGHT_FEATURES_URL' => BRAILLEWRIGHT_FEATURES_URL
	) );
}
add_action( 'customize_controls_enqueue_scripts', 'braillewright_features_enqueue_customizer_scripts' );

/*
 * Script for live updating with customizer options. Has to be loaded separately on customize_preview_init hook
 * transport => postMessage
 */
function braillewright_features_enqueue_customizer_post_message_scripts() {
	wp_enqueue_script( 'braillewright-features-post-message-js', BRAILLEWRIGHT_FEATURES_URL . 'js/build/postMessage.min.js', array( 'jquery' ), '', true );

	wp_localize_script( 'braillewright-features-post-message-js', 'braillewright_features_objectL10n', array(
		'BRAILLEWRIGHT_FEATURES_URL' => BRAILLEWRIGHT_FEATURES_URL
	) );
}
add_action( 'customize_preview_init', 'braillewright_features_enqueue_customizer_post_message_scripts' );