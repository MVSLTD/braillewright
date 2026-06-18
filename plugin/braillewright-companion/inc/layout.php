<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_add_body_classes( $classes ) {

	$post_layout    		= get_theme_mod( 'layout' );
	$post_layout 				= apply_filters( 'ct_period_pro_layout_filter', $post_layout );
	$page_layout    		= get_theme_mod( 'layout_pages' );
	$page_layout    		= apply_filters( 'ct_period_pro_layout_filter', $page_layout );
	$blog_layout    		= get_theme_mod( 'layout_blog' );
	$archives_layout    = get_theme_mod( 'layout_archives' );

	if ( !empty( $post_layout ) && is_singular( 'post' ) ) {
		$classes[] = $post_layout . '-layout';
	} 
	if ( !empty( $page_layout ) && is_singular( 'page' ) ) {
		$classes[] = $page_layout . '-layout';
	}
	if ( !empty( $blog_layout ) && is_home() ) {
		$classes[] = $blog_layout . '-layout';
	}
	if ( !empty( $archives_layout ) && is_archive() ) {
		$classes[] = $archives_layout . '-layout';
	}

	return $classes;
}
add_action( 'body_class', 'ct_period_pro_add_body_classes' );

// remove the sidebar if the current layout calls for it
function ct_period_pro_remove_primary_sidebar( $sidebars_widgets ) {

	if ( is_admin() ) {
		return $sidebars_widgets;
	}
	// never remove sidebar in Customizer (so CSS can show/hide instantly)
	if ( is_customize_preview() ) {
		return $sidebars_widgets;
	}

	global $wp_query;

	// if the post object isn't set yet, return. It will be called again with it set
	if ( ! isset( $wp_query->post ) ) {
		return $sidebars_widgets;
	}

	$post_layout    		= get_theme_mod( 'layout' );
	$post_layout 				= apply_filters( 'ct_period_pro_layout_filter', $post_layout );
	$page_layout    		= get_theme_mod( 'layout_pages' );
	$page_layout    		= apply_filters( 'ct_period_pro_layout_filter', $page_layout );
	$blog_layout    		= get_theme_mod( 'layout_blog' );
	$archives_layout    = get_theme_mod( 'layout_archives' );

	// if it's a non-sidebar layout, remove the primary sidebar
	if ( in_array( $post_layout, ct_period_pro_layouts( 'no-sidebar' ) ) && is_singular( 'post' ) ) {
		$sidebars_widgets['primary'] = false;
	}
	if ( in_array( $page_layout, ct_period_pro_layouts( 'no-sidebar' ) ) && is_singular( 'page' ) ) {
		$sidebars_widgets['primary'] = false;
	}
	if ( in_array( $blog_layout, ct_period_pro_layouts( 'no-sidebar' ) ) && is_home() ) {
		$sidebars_widgets['primary'] = false;
	}
	if ( in_array( $archives_layout, ct_period_pro_layouts( 'no-sidebar' ) ) && is_archive() ) {
		$sidebars_widgets['primary'] = false;
	}

	return $sidebars_widgets;
}
add_filter( 'sidebars_widgets', 'ct_period_pro_remove_primary_sidebar' );

function ct_period_pro_layouts( $type = '' ) {

	if ( $type == 'no-sidebar' ) {
		$layouts = array(
			'narrow',
			'wide',
			'two-narrow',
			'two-wide'
		);
	} elseif ( $type == 'page-layouts' ) {
		$layouts = array(
			'default',
			'right',
			'left',
			'narrow',
			'wide'
		);
	} else {
		$layouts = array(
			'right',
			'left',
			'narrow',
			'wide',
			'two-right',
			'two-left',
			'two-narrow',
			'two-wide'
		);
	}

	return $layouts;
}