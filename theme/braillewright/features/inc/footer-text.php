<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_features_filter_footer_text( $footer_text ) {

	$custom_text = get_theme_mod( 'footer_text' );

	if ( $custom_text ) {
		$footer_text = $custom_text;
	}

	return $footer_text;
}
add_filter( 'braillewright_footer_text', 'braillewright_features_filter_footer_text', 99 );