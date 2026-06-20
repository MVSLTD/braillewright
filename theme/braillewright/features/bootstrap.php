<?php
/*
Braillewright built-in feature modules — layouts, colors, fonts, header image,
display controls, featured sliders/videos, widget areas, and more. Formerly the
companion Braillewright plugin, now merged into the Braillewright theme.

Period Pro WordPress Plugin, Copyright 2025 Compete Themes
Forked from Period Pro 1.16; modifications Copyright 2026 Aaron Di Blasi, GNU GPL v2 or later.
Distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) OR exit;

// Asset locations for the merged-in feature modules (now a theme subdirectory).
if ( ! defined( 'BRAILLEWRIGHT_FEATURES_PATH' ) ) {
	define( 'BRAILLEWRIGHT_FEATURES_PATH', trailingslashit( get_template_directory() ) . 'features/' );
}
if ( ! defined( 'BRAILLEWRIGHT_FEATURES_URL' ) ) {
	define( 'BRAILLEWRIGHT_FEATURES_URL', trailingslashit( get_template_directory_uri() ) . 'features/' );
}

require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/colors.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/customizer.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/featured-videos.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/scripts.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/featured-sliders.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/featured-image-size.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/header-image.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/fonts.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/font-sizes.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/widget-areas.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/background.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/display-controls.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/footer-text.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/layout.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/page-layouts.php' );
require_once( BRAILLEWRIGHT_FEATURES_PATH . 'inc/menus.php' );

// Detect the optional MetaSlider plugin for the featured-sliders feature. Kept as
// a named function because the theme dashboard uses function_exists() on it to
// confirm the feature set is present. Formerly hooked on plugins_loaded; the theme
// loads after all plugins, so it runs on after_setup_theme instead.
function braillewright_features_init() {
	if ( class_exists( 'MetaSliderPlugin' ) ) {
		define( 'META_SLIDER_ACTIVE', true );
	}
}
add_action( 'after_setup_theme', 'braillewright_features_init' );

function braillewright_features_mods_to_remove( $mods_array ) {

	$pro_mods = array(
		'header_image_upload',
		'header_image_homepage',
		'header_image_link_home',
		'header_image_height_type',
		'header_image_height',
		'background_image_header',
		'background_image_main',
		'background_texture_header_show',
		'background_texture_header',
		'background_texture_main_show',
		'background_texture_main',
		'primary_font',
		'primary_font_weight',
		'site_title_font',
		'site_title_font_weight',
		'featured_image_size',
		'display_site_title',
		'display_primary_menu',
		'display_post_title',
		'display_more_link',
		'display_comments_link',
		'display_post_categories',
		'display_post_tags',
		'display_post_nav',
		'display_comment_count',
		'display_comment_date',
		'display_footer',
		'footer_text'
	);

	$color_sections = braillewright_features_custom_colors_data();

	foreach ( $color_sections as $section ) {

		foreach ( $section as $setting ) {

			if ( is_array( $setting ) ) {
				$pro_mods[] = $setting['setting_id'];
			}
		}
	}

	$mods_array = array_merge( $mods_array, $pro_mods );

	return $mods_array;
}
add_action( 'braillewright_mods_to_remove', 'braillewright_features_mods_to_remove' );

function braillewright_features_sanitize_css( $css ) {
	$css = wp_kses( $css, array( '\'', '\"' ) );
	$css = str_replace( '&gt;', '>', $css );

	return $css;
}
