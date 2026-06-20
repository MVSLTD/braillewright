<?php
defined( 'ABSPATH' ) OR exit;

/********** Register Widget Areas **********/

function braillewright_features_register_widget_areas() {

	// After post content
	register_sidebar( array(
		'name'          => esc_html__( 'After Post Content', 'braillewright' ),
		'id'            => 'after-post',
		'description'   => esc_html__( 'Widgets in this area will be shown after the post content', 'braillewright' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	// After page content
	register_sidebar( array(
		'name'          => esc_html__( 'After Page Content', 'braillewright' ),
		'id'            => 'after-page',
		'description'   => esc_html__( 'Widgets in this area will be shown after the page content', 'braillewright' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	// Before main content
	register_sidebar( array(
		'name'          => esc_html__( 'Before Main Content', 'braillewright' ),
		'id'            => 'before-main',
		'description'   => esc_html__( 'Widgets in this area will be shown after the site title and above the posts', 'braillewright' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	// Footer
	register_sidebar( array(
		'name'          => esc_html__( 'Footer', 'braillewright' ),
		'id'            => 'footer',
		'description'   => esc_html__( 'Widgets in this area will be shown in the footer', 'braillewright' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
}
add_action( 'widgets_init', 'braillewright_features_register_widget_areas' );

/********** Add Widget Areas to Front-end **********/

// After Post Content
function braillewright_features_after_post_content_widgets() {
	include( 'widget-areas/after-post-content.php' );
}
add_action( 'post_after', 'braillewright_features_after_post_content_widgets' );

// After Page Content
function braillewright_features_after_page_content_widgets() {
	include( 'widget-areas/after-page-content.php' );
}
add_action( 'page_after', 'braillewright_features_after_page_content_widgets' );

// Before Main Content
function braillewright_features_before_main_content_widgets() {
	include( 'widget-areas/before-main-content.php' );
}
add_action( 'after_archive_header', 'braillewright_features_before_main_content_widgets' );

// Footer
function braillewright_features_footer_widgets() {
	include( 'widget-areas/footer.php' );
}
add_action( 'footer_top', 'braillewright_features_footer_widgets' );