<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_output_header_image() {

	$header_image_type = get_theme_mod( 'header_image_type' );
	$header_image  		 = get_theme_mod( 'header_image_upload' );
	$header_video  		 = get_theme_mod( 'header_image_video' );
	$homepage_only 		 = get_theme_mod( 'header_image_homepage' );
	$link 				 		 = get_theme_mod( 'header_image_link' );

	if ( $homepage_only == 'yes' && ! is_front_page() ) {
		return;
	}
	if ( $header_image && $header_image_type != 'video' ) {
		$image_id = attachment_url_to_postid($header_image);
		$alt_text = false;
		if ($image_id) {
			$alt_text = get_post_meta($image_id , '_wp_attachment_image_alt', true);
		}
		
		if ($alt_text) {
			echo '<span id="header-image" class="header-image" 
					style="background-image: url(\'' . esc_url( $header_image ) . '\')" 
					role="img"
					aria-label="'. esc_attr($alt_text) .'"
				  >';
		} else {
			echo '<span id="header-image" class="header-image" 
				style="background-image: url(\'' . esc_url( $header_image ) . '\')" 
			>';
		}
			if ( $link != '' ) {
				echo '<a href="'. esc_url( $link ) .'">'. esc_html__( "Visit Page", "period-pro" ) .'</a>';
			}
		echo '</span>';
	} elseif ( $header_video && $header_image_type == 'video' ) {
		$filetype = wp_check_filetype( $header_video );
		$video_type = $filetype['type'] == 'video/mp4' ? 'self-hosted' : 'external';
		echo '<span id="header-image" class="header-image video '. esc_attr($video_type) .'">';
		if ($filetype['type'] == 'video/mp4') {
			echo do_shortcode('[video mp4=' . esc_url($header_video) . ' loop="on" autoplay="on" muted="true"]');
		} else {
			echo wp_oembed_get( esc_url( $header_video ) );
		}
		echo '</span>';
	}
}
add_action( 'body_top', 'ct_period_pro_output_header_image' );

function ct_period_pro_header_image_css() {

	$header_image 		 = get_theme_mod( 'header_image_upload' );
	$header_video  		 = get_theme_mod( 'header_image_video' );

	if (empty($header_image) && empty($header_video)) {
		return;
	}

	$header_image_type = get_theme_mod( 'header_image_type' );
	$height_type = get_theme_mod( 'header_image_height_type' );
	$height      = get_theme_mod( 'header_image_height' );

	if ( empty( $height ) ) {
		$height = 20;
	}

	if ($header_image_type == 'video') {
		$filetype = wp_check_filetype( $header_video );
		if ($filetype['type'] == 'video/mp4') {
			$custom_css = "#header-image { padding-bottom: " . $height . "%;}";	
		} else {
			return;
		}
	} elseif ( $height_type == 'fixed' ) {
		$custom_css = "#header-image { height: " . $height * 5 . "px; padding-bottom: 0; }";
	} else {
		$custom_css = "#header-image { padding-bottom: $height%; }";
	}

	$custom_css = ct_period_pro_sanitize_css( $custom_css );

	wp_add_inline_style( 'ct-period-style', $custom_css );
	wp_add_inline_style( 'ct-period-style-rtl', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_header_image_css', 99 );

//----------------------------------------------------------------------------------
// Transition old yes/no header home link option to new custom URL option
//----------------------------------------------------------------------------------
function ct_period_pro_set_header_image_link() {
	if ( get_option( 'ct_period_pro_header_image_link_check' ) != 'yes' ) {
		if ( get_theme_mod( 'header_image_link_home' ) == 'yes' ) {
			set_theme_mod( 'header_image_link', esc_url(site_url()) );
		}
		update_option( 'ct_period_pro_header_image_link_check', 'yes' );
	}
}
add_action( 'admin_init', 'ct_period_pro_set_header_image_link' );