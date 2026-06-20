<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_features_register_nav_menus() {

	register_nav_menus( array(
		'secondary' => esc_html__( 'Secondary', 'braillewright' )
	) );
}
add_action( 'after_setup_theme', 'braillewright_features_register_nav_menus', 11 );

function braillewright_features_include_secondary_menu() {
	include_once( 'menus/menu-secondary.php' );
}
add_action( 'before_header', 'braillewright_features_include_secondary_menu', 10 );