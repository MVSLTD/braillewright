<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_output_backgrounds() {

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
				$custom_css .= ".site-header {background-image: url('" . esc_url( PERIOD_PRO_URL ) . 'assets/images/textures/' . esc_attr( $customization ) . "');}";
			}
			if ( $key == 'main_texture' && get_theme_mod( 'background_texture_main_show' ) == 'yes' ) {
				$custom_css .= "body {background-image: url('" . esc_url( PERIOD_PRO_URL ) . 'assets/images/textures/' . esc_attr( $customization ) . "');}";
			}
		}
	}

	$custom_css = ct_period_pro_sanitize_css( $custom_css );

	wp_add_inline_style( 'ct-period-style', $custom_css );
	wp_add_inline_style( 'ct-period-style-rtl', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_output_backgrounds', 30 );

function ct_period_pro_add_main_bg_image() {

	if ( get_theme_mod( 'background_image_main' ) ) {
		echo '<div id="main-background-image" class="main-background-image"></div>';
	}
}
add_action( 'body_bottom', 'ct_period_pro_add_main_bg_image' );

function ct_period_pro_textures_array() {

	$images_array = array();
	$images       = glob( PERIOD_PRO_PATH . 'assets/images/textures/*.png' );

	// put each file name into the array (ex. back_pattern.png)
	foreach ( $images as $image ) {

		$image = basename( $image );

		$images_array[ $image ] = $image;
	}

	return $images_array;
}

function ct_period_pro_background_textures_data() {

	// can't be further refactored since i18n doesn't allow for $variables
	$texture_data = array(
		array(
			'setting_id' => 'background_texture_header_show',
			'label'      => esc_html__( 'Show a texture in the header?', 'period-pro' ),
			'type'       => 'show'
		),
		array(
			'setting_id' => 'background_texture_header',
			'label'      => esc_html__( 'Choose a texture for the header:', 'period-pro' ),
			'type'       => 'textures'
		),
		array(
			'setting_id' => 'background_texture_main_show',
			'label'      => esc_html__( 'Show a texture in the body?', 'period-pro' ),
			'type'       => 'show'
		),
		array(
			'setting_id' => 'background_texture_main',
			'label'      => esc_html__( 'Choose a texture for the body:', 'period-pro' ),
			'type'       => 'textures'
		)
	);

	return $texture_data;
}


function ct_period_pro_body_classes( $classes ) {

	if ( get_theme_mod( 'background_image_header' ) ) {
		$classes[] = 'site-header-image';
	}

	return $classes;
}
add_action( 'body_class', 'ct_period_pro_body_classes', 20 );