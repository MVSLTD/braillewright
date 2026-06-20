<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_features_output_backgrounds() {

	// build array of ids and image urls
	$customizations = array(
		'header_image'   => get_theme_mod( 'background_image_header' ),
		'main_image'     => get_theme_mod( 'background_image_main' ),
		'header_texture' => get_theme_mod( 'background_texture_header' ),
		'main_texture'   => get_theme_mod( 'background_texture_main' )
	);

	$custom_css = '';

	// $key = id, $customization = url
	foreach ( $customizations as $key => $customization ) {

		if ( $customization ) {

			if ( $key == 'header_image' ) {
				$custom_css .= "#site-header {background-image: url('" . esc_attr( $customization ) . "');}";
			}
			if ( $key == 'main_image' ) {
				$custom_css .= ".main-background-image {background-image: url('" . esc_attr( $customization ) . "');}";
			}
			if ( $key == 'header_texture' && get_theme_mod( 'background_texture_header_show' ) == 'yes' ) {
				$custom_css .= ".site-header {background-image: url('" . esc_url( BRAILLEWRIGHT_FEATURES_URL ) . 'assets/images/textures/' . esc_attr( $customization ) . "');}";
			}
			if ( $key == 'main_texture' && get_theme_mod( 'background_texture_main_show' ) == 'yes' ) {
				$custom_css .= "body {background-image: url('" . esc_url( BRAILLEWRIGHT_FEATURES_URL ) . 'assets/images/textures/' . esc_attr( $customization ) . "');}";
			}
		}
	}

	$custom_css = braillewright_features_sanitize_css( $custom_css );

	wp_add_inline_style( 'braillewright-style', $custom_css );
	wp_add_inline_style( 'braillewright-style-rtl', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'braillewright_features_output_backgrounds', 30 );

function braillewright_features_add_main_bg_image() {

	if ( get_theme_mod( 'background_image_main' ) ) {
		echo '<div id="main-background-image" class="main-background-image"></div>';
	}
}
add_action( 'body_bottom', 'braillewright_features_add_main_bg_image' );

function braillewright_features_textures_array() {

	$images_array = array();
	$images       = glob( BRAILLEWRIGHT_FEATURES_PATH . 'assets/images/textures/*.png' );

	// put each file name into the array (ex. back_pattern.png)
	foreach ( $images as $image ) {

		$image = basename( $image );

		$images_array[ $image ] = $image;
	}

	return $images_array;
}

function braillewright_features_background_textures_data() {

	// can't be further refactored since i18n doesn't allow for $variables
	$texture_data = array(
		array(
			'setting_id' => 'background_texture_header_show',
			'label'      => esc_html__( 'Show a texture in the header?', 'braillewright' ),
			'type'       => 'show'
		),
		array(
			'setting_id' => 'background_texture_header',
			'label'      => esc_html__( 'Choose a texture for the header:', 'braillewright' ),
			'type'       => 'textures'
		),
		array(
			'setting_id' => 'background_texture_main_show',
			'label'      => esc_html__( 'Show a texture in the body?', 'braillewright' ),
			'type'       => 'show'
		),
		array(
			'setting_id' => 'background_texture_main',
			'label'      => esc_html__( 'Choose a texture for the body:', 'braillewright' ),
			'type'       => 'textures'
		)
	);

	return $texture_data;
}


function braillewright_features_body_classes( $classes ) {

	if ( get_theme_mod( 'background_image_header' ) ) {
		$classes[] = 'site-header-image';
	}

	return $classes;
}
add_action( 'body_class', 'braillewright_features_body_classes', 20 );