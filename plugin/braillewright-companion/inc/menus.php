<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_register_nav_menus() {

	register_nav_menus( array(
		'secondary' => esc_html__( 'Secondary', 'period-pro' )
	) );
}
add_action( 'after_setup_theme', 'ct_period_pro_register_nav_menus', 11 );

function ct_period_pro_include_secondary_menu() {
	include_once( 'menus/menu-secondary.php' );
}
add_action( 'before_header', 'ct_period_pro_include_secondary_menu', 10 );