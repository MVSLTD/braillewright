<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_display_controls_css() {

	$css = '';

	if ( get_theme_mod( 'display_site_title' ) == 'hide' ) {
		$css .= '.site-title { display: none; }';
	}
	if ( get_theme_mod( 'display_tagline' ) == 'hide' ) {
		$css .= '.tagline { display: none; }';
	}
	if ( get_theme_mod( 'display_primary_menu' ) == 'hide' ) {
		$css .= '.menu-primary, .toggle-navigation { display: none; }';
	}
	if ( get_theme_mod( 'display_post_title' ) == 'hide' ) {
		$css .= '.post-title { display: none; }';
	}
	if ( get_theme_mod( 'display_post_featured_image' ) == 'hide' ) {
		$css .= '.single-post .featured-image { display: none; }';
	}
	if ( get_theme_mod( 'display_featured_images' ) == 'hide' ) {
		$css .= '.blog .featured-image, .archive .featured-image { display: none; }';
	}
	if ( get_theme_mod( 'display_more_link' ) == 'hide' ) {
		$css .= '.more-link { display: none; }';
	}
	if ( get_theme_mod( 'display_comments_link' ) == 'hide' ) {
		$css .= '.full-post .comments-link, .comments-link { display: none; }';
		$css .= '.more-link { margin-right: 0; }';
	}
	if ( get_theme_mod( 'display_post_categories' ) == 'hide' ) {
		$css .= '.post-categories { display: none; }';
	}
	if ( get_theme_mod( 'display_post_tags' ) == 'hide' ) {
		$css .= '.post-tags { display: none; }';
	}
	if ( get_theme_mod( 'display_post_nav' ) == 'hide' ) {
		$css .= '.further-reading { display: none; }';
	}
	if ( get_theme_mod( 'display_comment_count' ) == 'hide' ) {
		$css .= '.comments-number { display: none; }';
	}
	if ( get_theme_mod( 'display_comment_date' ) == 'hide' ) {
		$css .= '.comment-date { display: none; }';
	}
	if ( get_theme_mod( 'display_archive_title' ) == 'hide' ) {
		$css .= '.archive-header h1 { display: none; }';
	}
	if ( get_theme_mod( 'display_archive_description' ) == 'hide' ) {
		$css .= '.archive-header p { display: none; }';
	}
	if ( get_theme_mod( 'display_footer' ) == 'hide' ) {
		$css .= '.site-footer { display: none; }';
	}

	$css = ct_period_pro_sanitize_css( $css );

	wp_add_inline_style( 'ct-period-style', $css );
	wp_add_inline_style( 'ct-period-style-rtl', $css );
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_display_controls_css', 99 );