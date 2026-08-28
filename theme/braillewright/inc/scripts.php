<?php

/**
 * The style handle that Customizer-generated CSS has to be attached to.
 *
 * On a right-to-left site braillewright-style-rtl is enqueued after, and depends on,
 * braillewright-style -- so inline CSS must ride the RTL handle to be printed after
 * rtl.css rather than before it. On a left-to-right site that handle is never
 * registered and the main handle is the correct target.
 *
 * Testing registration rather than is_rtl() keeps this right for any locale that has
 * its own stylesheet, and means the two can never drift apart: whatever
 * braillewright_load_scripts_styles() actually enqueued is what gets used.
 *
 * @return string A registered style handle, always safe for wp_add_inline_style().
 */
if ( ! function_exists( 'braillewright_customizer_style_handle' ) ) {
	function braillewright_customizer_style_handle() {
		return wp_style_is( 'braillewright-style-rtl', 'registered' )
			? 'braillewright-style-rtl'
			: 'braillewright-style';
	}
}

// Front-end scripts
function braillewright_load_scripts_styles() {

	$font_args = array(
		'family'  => urlencode( 'Roboto:300,300italic,400,700' ),
		'subset'  => urlencode( 'latin,latin-ext' ),
		'display' => 'swap'
	);
	$fonts_url = add_query_arg( $font_args, '//fonts.googleapis.com/css' );

	// External CDN URL - not ours to version, so null is the explicit "no version".
	wp_enqueue_style( 'braillewright-google-fonts', $fonts_url, array(), null );

	wp_enqueue_script( 'braillewright-js', get_template_directory_uri() . '/js/build/production.min.js', array( 'jquery' ), BRAILLEWRIGHT_VERSION, true );
	wp_localize_script(
		'braillewright-js',
		'braillewright_objectL10n',
		array(
			'openMenu'       => esc_html_x( 'open menu', 'verb: open the menu', 'braillewright' ),
			'closeMenu'      => esc_html_x( 'close menu', 'verb: close the menu', 'braillewright' ),
			'openChildMenu'  => esc_html_x( 'open dropdown menu', 'verb: open the dropdown menu', 'braillewright' ),
			'closeChildMenu' => esc_html_x( 'close dropdown menu', 'verb: close the dropdown menu', 'braillewright' )
		)
	);

	wp_enqueue_style( 'braillewright-font-awesome', get_template_directory_uri() . '/assets/font-awesome/css/all.min.css', array(), BRAILLEWRIGHT_VERSION );

	wp_enqueue_style( 'braillewright-style', get_stylesheet_uri(), array(), BRAILLEWRIGHT_VERSION );

	/*
	 * WordPress core already loads this theme's rtl.css by itself: locale_stylesheet()
	 * is hooked to wp_head at priority 10 and prints get_locale_stylesheet_uri(), which
	 * resolves to <stylesheet dir>/rtl.css whenever the locale is right-to-left. The file
	 * is therefore NOT dead -- but core prints it at priority 10 while wp_print_styles()
	 * runs at priority 8, so core's <link> lands AFTER every Customizer <style> block and
	 * silently overrides the site owner's own settings.
	 *
	 * Measured on an Arabic-locale page load on 2026-08-25: 30 of 31 colliding Customizer
	 * declarations lost to rtl.css, including the link colour (#0000cc -> #333333, which
	 * makes links the same colour as body text) and the focus colour on .site-title and
	 * .social-media-icons links (#ffcc00 -> #D4D4D4).
	 *
	 * Enqueueing the same URI core would have printed, as a real handle that depends on
	 * braillewright-style, puts it back in the styles queue where the cascade is
	 * predictable -- and gives the wp_add_inline_style( 'braillewright-style-rtl', ... )
	 * calls a handle that actually exists. Core's duplicate is then removed.
	 *
	 * get_locale_stylesheet_uri() is used rather than a hard-coded '/rtl.css' so a child
	 * theme's own rtl.css, a locale-specific <locale>.css, and the locale_stylesheet_uri
	 * filter all keep working exactly as core intends.
	 */
	$braillewright_locale_stylesheet = get_locale_stylesheet_uri();

	if ( $braillewright_locale_stylesheet ) {
		wp_enqueue_style(
			'braillewright-style-rtl',
			$braillewright_locale_stylesheet,
			array( 'braillewright-style' ),
			BRAILLEWRIGHT_VERSION
		);
		remove_action( 'wp_head', 'locale_stylesheet' );
	}

	// enqueue comment-reply script only on posts & pages with comments open ( included in WP core )
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'braillewright_load_scripts_styles' );

// Back-end scripts
function braillewright_enqueue_admin_styles( $hook ) {

	if ( $hook == 'appearance_page_braillewright-options' ) {
		wp_enqueue_style( 'braillewright-admin-styles', get_template_directory_uri() . '/styles/admin.min.css', array(), BRAILLEWRIGHT_VERSION );
	}
	if ( $hook == 'post.php' || $hook == 'post-new.php' ) {

		$font_args = array(
			'family' => urlencode( 'Roboto:300,300i,400,700' ),
			'subset' => urlencode( 'latin,latin-ext' )
		);
		$fonts_url = add_query_arg( $font_args, '//fonts.googleapis.com/css' );

		wp_enqueue_style( 'braillewright-google-fonts', $fonts_url, array(), null );
	}
}
add_action( 'admin_enqueue_scripts', 'braillewright_enqueue_admin_styles' );

// Customizer scripts
function braillewright_enqueue_customizer_scripts() {
	wp_enqueue_script( 'braillewright-customizer-js', get_template_directory_uri() . '/js/build/customizer.min.js', array( 'jquery' ), BRAILLEWRIGHT_VERSION, true );
	wp_enqueue_style( 'braillewright-customizer-styles', get_template_directory_uri() . '/styles/customizer.min.css', array(), BRAILLEWRIGHT_VERSION );
}
add_action( 'customize_controls_enqueue_scripts', 'braillewright_enqueue_customizer_scripts' );

/*
 * Script for live updating with customizer options. Has to be loaded separately on customize_preview_init hook
 * transport => postMessage
 */
function braillewright_enqueue_customizer_post_message_scripts() {
	wp_enqueue_script( 'braillewright-customizer-post-message-js', get_template_directory_uri() . '/js/build/postMessage.min.js', array( 'jquery' ), BRAILLEWRIGHT_VERSION, true );
}
add_action( 'customize_preview_init', 'braillewright_enqueue_customizer_post_message_scripts' );