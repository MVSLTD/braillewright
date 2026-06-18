<?php
defined( 'ABSPATH' ) OR exit;

add_action( 'customize_register', 'ct_period_pro_add_customizer_content', 11 );

function ct_period_pro_add_customizer_content( $wp_customize ) {

	// if Live Previewing another theme, don't do anything
	$period_section = $wp_customize->get_section('ct_period_logo_upload');

	if ( empty( $period_section ) ) {
		return;
	}

	//----------------------------------------------------------------------------------
  //	Remove Adds
  //----------------------------------------------------------------------------------

	$wp_customize->get_section( 'period_layout' )->description = __( 'Layout can be overridden for any post or page.', 'period-pro' );
	$wp_customize->get_section( 'period_layout_pages' )->description = __( 'Layout can be overridden for any post or page.', 'period-pro' );
	$wp_customize->get_section( 'period_layout_blog' )->description = '';
	$wp_customize->get_section( 'period_layout_archives' )->description = '';
	$wp_customize->get_panel( 'ct_period_layout_panel' )->priority = 1;
	$wp_customize->get_section( 'period_display' )->description = '';

	//----------------------------------------------------------------------------------
  //	Reorder existing sections
  //----------------------------------------------------------------------------------

	$wp_customize->get_section( 'title_tagline' )->priority = 10;

	// check if exists in case user has no pages
	if ( is_object( $wp_customize->get_section( 'static_front_page' ) ) ) {
		$wp_customize->get_section( 'static_front_page' )->priority = 11;
	}

	//----------------------------------------------------------------------------------
  //	Add Panels
  //----------------------------------------------------------------------------------

	// Add panel for colors
	if ( method_exists( 'WP_Customize_Manager', 'add_panel' ) ) {

		$wp_customize->add_panel( 'ct_period_pro_colors_panel', array(
			'priority'    => 2,
			'title'       => __( 'Colors', 'period-pro' ),
			'description' => __( 'Change any color on your site', 'period-pro' )
		) );
		$wp_customize->add_panel( 'ct_period_pro_fonts_panel', array(
			'priority'    => 3,
			'title'       => __( 'Fonts', 'period-pro' ),
			'description' => __( 'Change the fonts on your site', 'period-pro' )
		) );
		$wp_customize->add_panel( 'ct_period_pro_font_sizes_panel', array(
			'priority'    => 4,
			'title'       => __( 'Font Sizes', 'period-pro' ),
			'description' => __( 'Change the size of text on your site', 'period-pro' )
		) );
		$wp_customize->add_panel( 'ct_period_pro_background_panel', array(
			'priority'    => 6,
			'title'       => __( 'Background', 'period-pro' ),
			'description' => __( 'Change your background using images & textures', 'period-pro' )
		) );
		$wp_customize->add_panel( 'ct_period_pro_show_hide_panel', array(
			'priority'    => 8,
			'title'       => __( 'Show/Hide Elements', 'period-pro' ),
			'description' => __( 'Choose which elements you want to display on the site.', 'period-pro' )
		) );
	}

	//----------------------------------------------------------------------------------
  //	Font Sizes
  //----------------------------------------------------------------------------------

	/***** Site Title *****/

	$wp_customize->add_section( 'ct_period_pro_site_title_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Site Title', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'site_title_font_size_mobile', array(
		'default'						=> '21',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'site_title_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 21px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_site_title_font_size',
		'settings' 		=> 'site_title_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'site_title_font_size_tablet', array(
		'default'						=> '21',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'site_title_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 21px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_site_title_font_size',
		'settings' 		=> 'site_title_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'site_title_font_size_desktop', array(
		'default'						=> '21',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'site_title_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 21px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_site_title_font_size',
		'settings' 		=> 'site_title_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Tagline *****/
	
	$wp_customize->add_section( 'ct_period_pro_tagline_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Tagline', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'tagline_font_size_mobile', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'tagline_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_tagline_font_size',
		'settings' 		=> 'tagline_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'tagline_font_size_tablet', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'tagline_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_tagline_font_size',
		'settings' 		=> 'tagline_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'tagline_font_size_desktop', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'tagline_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_tagline_font_size',
		'settings' 		=> 'tagline_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Primary Menu Items *****/
	
	$wp_customize->add_section( 'ct_period_pro_menu_primary_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Primary Menu Items', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'menu_primary_font_size_mobile', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'menu_primary_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_menu_primary_font_size',
		'settings' 		=> 'menu_primary_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'menu_primary_font_size_tablet', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'menu_primary_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_menu_primary_font_size',
		'settings' 		=> 'menu_primary_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'menu_primary_font_size_desktop', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'menu_primary_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_menu_primary_font_size',
		'settings' 		=> 'menu_primary_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Secondary Menu Items *****/
	
	$wp_customize->add_section( 'ct_period_pro_menu_secondary_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Secondary Menu Items', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'menu_secondary_font_size_mobile', array(
		'default'						=> '12',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'menu_secondary_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 12px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_menu_secondary_font_size',
		'settings' 		=> 'menu_secondary_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'menu_secondary_font_size_tablet', array(
		'default'						=> '12',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'menu_secondary_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 12px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_menu_secondary_font_size',
		'settings' 		=> 'menu_secondary_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'menu_secondary_font_size_desktop', array(
		'default'						=> '12',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'menu_secondary_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 12px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_menu_secondary_font_size',
		'settings' 		=> 'menu_secondary_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Post Titles *****/
	
	$wp_customize->add_section( 'ct_period_pro_post_title_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Post Titles', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'post_title_font_size_mobile', array(
		'default'						=> '18',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'post_title_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 18px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_title_font_size',
		'settings' 		=> 'post_title_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'post_title_font_size_tablet', array(
		'default'						=> '21',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'post_title_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 21px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_title_font_size',
		'settings' 		=> 'post_title_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'post_title_font_size_desktop', array(
		'default'						=> '28',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'post_title_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 28px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_title_font_size',
		'settings' 		=> 'post_title_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Post Byline *****/
	
	$wp_customize->add_section( 'ct_period_pro_post_byline_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Post Byline', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'post_byline_font_size_mobile', array(
		'default'						=> '12',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'post_byline_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 12px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_byline_font_size',
		'settings' 		=> 'post_byline_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'post_byline_font_size_tablet', array(
		'default'						=> '12',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'post_byline_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 12px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_byline_font_size',
		'settings' 		=> 'post_byline_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'post_byline_font_size_desktop', array(
		'default'						=> '12',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'post_byline_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 12px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_byline_font_size',
		'settings' 		=> 'post_byline_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Post Text *****/
	
	$wp_customize->add_section( 'ct_period_pro_post_text_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Post Text', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'post_text_font_size_mobile', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'post_text_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_text_font_size',
		'settings' 		=> 'post_text_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'post_text_font_size_tablet', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'post_text_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_text_font_size',
		'settings' 		=> 'post_text_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'post_text_font_size_desktop', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'post_text_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_post_text_font_size',
		'settings' 		=> 'post_text_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Comments *****/
	
	$wp_customize->add_section( 'ct_period_pro_comments_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Comments', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'comments_font_size_mobile', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'comments_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_comments_font_size',
		'settings' 		=> 'comments_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'comments_font_size_tablet', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'comments_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_comments_font_size',
		'settings' 		=> 'comments_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'comments_font_size_desktop', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'comments_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_comments_font_size',
		'settings' 		=> 'comments_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Widget Titles *****/
	
	$wp_customize->add_section( 'ct_period_pro_widget_titles_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Widget Titles', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'ct_widget_titles_font_size_mobile', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'ct_widget_titles_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_widget_titles_font_size',
		'settings' 		=> 'ct_widget_titles_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'ct_widget_titles_font_size_tablet', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'ct_widget_titles_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_widget_titles_font_size',
		'settings' 		=> 'ct_widget_titles_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'ct_widget_titles_font_size_desktop', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'ct_widget_titles_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_widget_titles_font_size',
		'settings' 		=> 'ct_widget_titles_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Widget Text *****/
	
	$wp_customize->add_section( 'ct_period_pro_widget_text_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Widget Text', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'ct_widget_text_font_size_mobile', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'ct_widget_text_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_widget_text_font_size',
		'settings' 		=> 'ct_widget_text_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'ct_widget_text_font_size_tablet', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'ct_widget_text_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_widget_text_font_size',
		'settings' 		=> 'ct_widget_text_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'ct_widget_text_font_size_desktop', array(
		'default'						=> '14',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'ct_widget_text_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 14px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_widget_text_font_size',
		'settings' 		=> 'ct_widget_text_font_size_desktop',
		'type'     		=> 'number'
	) );

	/***** Footer Text *****/
	
	$wp_customize->add_section( 'ct_period_pro_footer_text_font_size', array(
		'panel' => 'ct_period_pro_font_sizes_panel',
		'title' => __( 'Footer Text', 'period-pro' )
	) );
	// Setting - Mobile
	$wp_customize->add_setting( 'footer_text_font_size_mobile', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Mobile
	$wp_customize->add_control( 'footer_text_font_size_mobile', array(
		'label'    		=> __( 'Mobile', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_footer_text_font_size',
		'settings' 		=> 'footer_text_font_size_mobile',
		'type'    		=> 'number'
	) );
	// Setting - Tablet
	$wp_customize->add_setting( 'footer_text_font_size_tablet', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Tablet
	$wp_customize->add_control( 'footer_text_font_size_tablet', array(
		'label'    		=> __( 'Tablet', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_footer_text_font_size',
		'settings' 		=> 'footer_text_font_size_tablet',
		'type'     		=> 'number'
	) );
	// Setting - Desktop
	$wp_customize->add_setting( 'footer_text_font_size_desktop', array(
		'default'						=> '16',
		'sanitize_callback' => 'absint'
	) );
	// Control - Desktop
	$wp_customize->add_control( 'footer_text_font_size_desktop', array(
		'label'    		=> __( 'Desktop', 'period-pro' ),
		'description' => __( 'Default is 16px', 'period-pro' ),
		'section'  		=> 'ct_period_pro_footer_text_font_size',
		'settings' 		=> 'footer_text_font_size_desktop',
		'type'     		=> 'number'
	) );

	//----------------------------------------------------------------------------------
  //	Header Image
  //----------------------------------------------------------------------------------

	// section
	$wp_customize->add_section( 'ct_period_pro_header_image', array(
		'title'    => __( 'Header Image', 'period-pro' ),
		'priority' => 5
	) );
	// setting - image or video
	$wp_customize->add_setting( 'header_image_type', array(
		'default'           => 'image',
		'sanitize_callback' => 'ct_period_pro_sanitize_header_image_type'
	) );
	// control - image or video
	$wp_customize->add_control( 'header_image_type', array(
		'label'    => __( 'Display image or video?', 'period-pro' ),
		'section'  => 'ct_period_pro_header_image',
		'settings' => 'header_image_type',
		'type'     => 'radio',
		'choices'  => array(
			'image'  => __( 'Image', 'period-pro' ),
			'video'  => __( 'Video', 'period-pro' )
		)
	) );
	// setting - upload
	$wp_customize->add_setting( 'header_image_upload', array(
		'sanitize_callback' => 'esc_url_raw'
	) );
	// control - upload
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'header_image_upload', array(
			'label'    => __( 'Upload an image', 'period-pro' ),
			'section'  => 'ct_period_pro_header_image',
			'settings' => 'header_image_upload',
		)
	) );
	// setting - video
	$wp_customize->add_setting( 'header_image_video', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url'
	) );
	// control - video
	$wp_customize->add_control( 'header_image_video', array(
		'label'    => __( 'Enter a video URL', 'period-pro' ),
		'section'  => 'ct_period_pro_header_image',
		'settings' => 'header_image_video',
		'type'     => 'url'
	) );
	// setting - homepage only
	$wp_customize->add_setting( 'header_image_homepage', array(
		'default'           => 'no',
		'sanitize_callback' => 'ct_period_pro_sanitize_yes_no_settings'
	) );
	// control - homepage only
	$wp_customize->add_control( 'header_image_homepage', array(
		'label'    => __( 'Only display on homepage?', 'period-pro' ),
		'section'  => 'ct_period_pro_header_image',
		'settings' => 'header_image_homepage',
		'type'     => 'radio',
		'choices'  => array(
			'yes' => __( 'Yes', 'period-pro' ),
			'no'  => __( 'No', 'period-pro' )
		)
	) );
	// setting - link home
	$wp_customize->add_setting( 'header_image_link', array(
		'sanitize_callback' => 'esc_url'
	) );
	// control - link home
	$wp_customize->add_control( 'header_image_link', array(
		'label'    => __( 'Header image link', 'period-pro' ),
		'section'  => 'ct_period_pro_header_image',
		'settings' => 'header_image_link',
		'type'     => 'url'
	) );
	// setting - height type
	$wp_customize->add_setting( 'header_image_height_type', array(
		'default'           => 'responsive',
		'sanitize_callback' => 'ct_period_pro_sanitize_header_image_height_type'
	) );
	// control - height type
	$wp_customize->add_control( 'header_image_height_type', array(
		'label'    => __( 'Responsive or Fixed height?', 'period-pro' ),
		'section'  => 'ct_period_pro_header_image',
		'settings' => 'header_image_height_type',
		'type'     => 'radio',
		'choices'  => array(
			'responsive' => __( 'Responsive', 'period-pro' ),
			'fixed'      => __( 'Fixed', 'period-pro' )
		)
	) );
	// setting - height
	$wp_customize->add_setting( 'header_image_height', array(
		'default'           => '20',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage'
	) );
	// control - height
	$wp_customize->add_control( 'header_image_height', array(
		'label'       => __( 'Adjust the height', 'period-pro' ),
		'section'     => 'ct_period_pro_header_image',
		'settings'    => 'header_image_height',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 5,
			'max'  => 100,
			'step' => 1
		)
	) );

	//----------------------------------------------------------------------------------
  //	Custom Colors
  //----------------------------------------------------------------------------------

	$color_sections = ct_period_pro_custom_colors_data();

	// set priority (in case user is < 4.0, set below widgets)
	// panel is 42
	$priority = 120;

	// sections
	foreach ( $color_sections as $section ) {

		// add section
		$wp_customize->add_section( $section['section_id'], array(
			'priority'    => $priority,
			'title'       => $section['section_title'],
			'description' => $section['description'],
			'panel'       => 'ct_period_pro_colors_panel'
		) );

		$priority ++;

		/* Add Settings & Controls */

		$control_priority = 1;

		foreach ( $section as $setting ) {

			if ( is_array( $setting ) ) {

				$wp_customize->add_setting( $setting['setting_id'], array(
					'default'           => $setting['setting_default'],
					'sanitize_callback' => 'sanitize_hex_color'
				) );

				$wp_customize->add_control( new WP_Customize_Color_Control(
					$wp_customize, $setting['setting_id'], array(
						'label'    => $setting['control_label'],
						'section'  => $section['section_id'],
						'settings' => $setting['setting_id'],
						'priority' => $control_priority
					)
				) );

				$control_priority ++;
			}
		}
	}

	//----------------------------------------------------------------------------------
  //	Layouts
  //----------------------------------------------------------------------------------

	// add more choices to existing controls
	$layouts = array(
		'right'      => __( 'Right sidebar', 'period' ),
		'left'       => __( 'Left sidebar', 'period' ),
		'narrow'     => __( 'No sidebar - Narrow', 'period' ),
		'wide'       => __( 'No sidebar - Wide', 'period' ),
		'two-right'  => __( 'Two column - Right sidebar', 'period' ),
		'two-left'   => __( 'Two column - Left sidebar', 'period' ),
		'two-narrow' => __( 'Two column - No Sidebar - Narrow', 'period' ),
		'two-wide'   => __( 'Two column - No Sidebar - Wide', 'period' )
	);
	$wp_customize->get_control( 'layout' )->choices = $layouts;
	$wp_customize->get_control( 'layout_pages' )->choices = $layouts;
	$wp_customize->get_control( 'layout_blog' )->choices = $layouts;
	$wp_customize->get_control( 'layout_archives' )->choices = $layouts;

	//----------------------------------------------------------------------------------
  //	Background Images
  //----------------------------------------------------------------------------------

	// section
	$wp_customize->add_section( 'ct_period_pro_background_image', array(
		'title'    => __( 'Background Images', 'period-pro' ),
		'priority' => 55,
		'panel'    => 'ct_period_pro_background_panel'
	) );
	// setting - header
	$wp_customize->add_setting( 'background_image_header', array(
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage'
	) );
	// control - header
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'background_image_header', array(
			'label'    => __( 'Header', 'period-pro' ),
			'section'  => 'ct_period_pro_background_image',
			'settings' => 'background_image_header'
		)
	) );
	// setting - main
	$wp_customize->add_setting( 'background_image_main', array(
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage'
	) );
	// control - main
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'background_image_main', array(
			'label'    => __( 'Body', 'period-pro' ),
			'section'  => 'ct_period_pro_background_image',
			'settings' => 'background_image_main'
		)
	) );

	//----------------------------------------------------------------------------------
  //	Background Textures
  //----------------------------------------------------------------------------------

	// section
	$wp_customize->add_section( 'ct_period_pro_background_texture', array(
		'title'    => __( 'Background Textures', 'period-pro' ),
		'priority' => 56,
		'panel'    => 'ct_period_pro_background_panel'
	) );

	$settings = ct_period_pro_background_textures_data();

	foreach ( $settings as $setting ) {

		if ( $setting['type'] == 'show' ) {

			$wp_customize->add_setting( $setting['setting_id'], array(
				'default'           => 'no',
				'sanitize_callback' => 'ct_period_pro_sanitize_yes_no_settings',
				'transport'         => 'postMessage'
			) );

			$wp_customize->add_control( $setting['setting_id'], array(
				'label'    => $setting['label'],
				'section'  => 'ct_period_pro_background_texture',
				'settings' => $setting['setting_id'],
				'type'     => 'radio',
				'choices'  => array(
					'yes' => __( 'Yes', 'period-pro' ),
					'no'  => __( 'No', 'period-pro' )
				)
			) );
		} else {

			$wp_customize->add_setting( $setting['setting_id'], array(
				'sanitize_callback' => 'ct_period_pro_sanitize_textures',
				'transport'         => 'postMessage'
			) );
			// control - header textures
			$wp_customize->add_control( $setting['setting_id'], array(
				'label'    => $setting['label'],
				'section'  => 'ct_period_pro_background_texture',
				'settings' => $setting['setting_id'],
				'type'     => 'radio',
				'choices'  => ct_period_pro_textures_array()
			) );
		}
	}

	//----------------------------------------------------------------------------------
	//	Custom Fonts
	//----------------------------------------------------------------------------------

	$fonts['default'] = 'Default';
	$font_data = ct_period_pro_prepare_fonts();

	foreach ( $font_data as $font => $weights ) {
		$fonts[ $font ] = $font;
	}

	$font_weights = array(
		'default' => __( 'Default', 'period-pro' ),
		'100' => __( 'Thin', 'period-pro' ),
		'200' => __( 'Extra-light', 'period-pro' ),
		'300' => __( 'Light', 'period-pro' ),
		'400' => __( 'Regular', 'period-pro' ),
		'500' => __( 'Medium', 'period-pro' ),
		'600' => __( 'Semi-Bold', 'period-pro' ),
		'700' => __( 'Bold', 'period-pro' ),
		'800' => __( 'Extra-Bold', 'period-pro' ),
		'900' => __( 'Ultra-Bold', 'period-pro' )
	);

	// section
	$wp_customize->add_section( 'ct_period_pro_fonts', array(
		'title'       => __( 'Global', 'period-pro' ),
		'description' => sprintf( __( '<i>All fonts can be previewed at <a href="%s" target="_blank">Google Fonts</a>.</i>', 'period-pro' ), 'https://fonts.google.com/' ),
		'panel'		  => 'ct_period_pro_fonts_panel'
	) );
	// setting - language
	$wp_customize->add_setting( 'font_language', array(
		'default'           => 'latin-ext',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_language'
	) );
	// control - language
	$wp_customize->add_control( 'font_language', array(
		'type'        => 'select',
		'label'       => __( 'Character set', 'period-pro' ),
		'description' => __( 'Change the character set if your language does not use the Latin alphabet.', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts',
		'setting'     => 'font_language',
		'choices'     => array(
			'arabic' 			  => 'Arabic',
			'bengali' 			  => 'Bengali',
			'chinese-hong-kong'   => 'Chinese (Hong Kong)',
			'chinese-simplified'  => 'Chinese (Simplified)',
			'chinese-traditional' => 'Chinese (Traditional)',
			'cyrillic' 			  => 'Cyrillic',
			'cyrillic-ext' 		  => 'Cyrillic Extended',
			'devanagari' 		  => 'Devanagari',
			'greek' 			  => 'Greek',
			'greek-ext' 		  => 'Greek Extended',
			'gujarati' 			  => 'Gujarati',
			'gurmukhi' 			  => 'Gurmukhi',
			'hebrew' 			  => 'Hebrew',
			'japanese' 			  => 'Japanese',
			'kannade' 			  => 'Kannada',
			'khmer' 			  => 'Khmer',
			'korean' 			  => 'Korean',
			'latin' 			  => 'Latin',
			'latin-ext' 		  => 'Latin Extended',
			'malayalam' 		  => 'Malayalam',
			'myanmar' 			  => 'Myanmar',
			'oriya' 			  => 'Oriya',
			'sinhala' 			  => 'Sinhala',
			'tamil' 			  => 'Tamil',
			'telugu' 			  => 'Telugu',
			'thai' 				  => 'Thai',
			'vietnamese' 		  => 'Vietnamese'
		)
	) );
	// setting - primary font family
	$wp_customize->add_setting( 'primary_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - primary font family
	$wp_customize->add_control( 'primary_font', array(
		'type'        => 'select',
		'label'       => __( 'Primary Font', 'period-pro' ),
		'description' => __( 'Changes font everywhere. Overridable with other font settings.', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts',
		'setting'     => 'primary_font',
		'choices'     => $fonts
	) );
	// setting - primary font weight
	$wp_customize->add_setting( 'primary_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - primary font weight
	$wp_customize->add_control( 'primary_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Primary Font Weight', 'period-pro' ),
		'section' => 'ct_period_pro_fonts',
		'setting' => 'primary_font_weight',
		'choices' => $font_weights
	) );
	// setting - headings font family
	$wp_customize->add_setting( 'heading_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - headings font family
	$wp_customize->add_control( 'heading_font', array(
		'type'        => 'select',
		'label'       => __( 'Headings', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts',
		'setting'     => 'heading_font',
		'choices'     => $fonts
	) );
	// setting - headings font weight
	$wp_customize->add_setting( 'heading_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - headings font weight
	$wp_customize->add_control( 'heading_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Headings (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts',
		'setting' => 'heading_font_weight',
		'choices' => $font_weights
	) );
	// setting - inputs font family
	$wp_customize->add_setting( 'input_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - inputs font family
	$wp_customize->add_control( 'input_font', array(
		'type'        => 'select',
		'label'       => __( 'Buttons & Inputs', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts',
		'setting'     => 'input_font',
		'choices'     => $fonts
	) );
	// setting - inputs font weight
	$wp_customize->add_setting( 'input_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - inputs font weight
	$wp_customize->add_control( 'input_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Buttons & Inputs (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts',
		'setting' => 'input_font_weight',
		'choices' => $font_weights
	) );

	// section
	$wp_customize->add_section( 'ct_period_pro_fonts_advanced', array(
		'title'       => __( 'Individual Elements', 'period-pro' ),
		'description' => sprintf( __( '<i>All fonts can be previewed at <a href="%s" target="_blank">Google Fonts</a>.</i>', 'period-pro' ), 'https://fonts.google.com/' ),
		'panel'		  => 'ct_period_pro_fonts_panel'
	) );

	/***** Individual Elements *****/

	// setting - site title font family
	$wp_customize->add_setting( 'site_title_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - site title font family
	$wp_customize->add_control( 'site_title_font', array(
		'type'        => 'select',
		'label'       => __( 'Site Title', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'site_title_font',
		'choices'     => $fonts
	) );
	// setting - site title font weight
	$wp_customize->add_setting( 'site_title_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - site title font weight
	$wp_customize->add_control( 'site_title_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Site Title (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'site_title_font_weight',
		'choices' => $font_weights
	) );
	// setting - tagline font family
	$wp_customize->add_setting( 'tagline_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - tagline font family
	$wp_customize->add_control( 'tagline_font', array(
		'type'        => 'select',
		'label'       => __( 'Tagline', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'tagline_font',
		'choices'     => $fonts
	) );
	// setting - tagline font weight
	$wp_customize->add_setting( 'tagline_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - tagline font weight
	$wp_customize->add_control( 'tagline_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Tagline (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'tagline_font_weight',
		'choices' => $font_weights
	) );
	// setting - primary menu font family
	$wp_customize->add_setting( 'primary_menu_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - primary menu font family
	$wp_customize->add_control( 'primary_menu_font', array(
		'type'        => 'select',
		'label'       => __( 'Primary Menu', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'primary_menu_font',
		'choices'     => $fonts
	) );
	// setting - primary menu font weight
	$wp_customize->add_setting( 'primary_menu_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - primary menu font weight
	$wp_customize->add_control( 'primary_menu_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Primary Menu (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'primary_menu_font_weight',
		'choices' => $font_weights
	) );
	// setting - secondary menu font family
	$wp_customize->add_setting( 'secondary_menu_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - secondary menu font family
	$wp_customize->add_control( 'secondary_menu_font', array(
		'type'        => 'select',
		'label'       => __( 'Secondary Menu', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'secondary_menu_font',
		'choices'     => $fonts
	) );
	// setting - secondary menu font weight
	$wp_customize->add_setting( 'secondary_menu_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - secondary menu font weight
	$wp_customize->add_control( 'secondary_menu_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Secondary Menu (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'secondary_menu_font_weight',
		'choices' => $font_weights
	) );
	// setting - post titles font family
	$wp_customize->add_setting( 'post_titles_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - post titles font family
	$wp_customize->add_control( 'post_titles_font', array(
		'type'        => 'select',
		'label'       => __( 'Post Titles', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'post_titles_font',
		'choices'     => $fonts
	) );
	// setting - post titles font weight
	$wp_customize->add_setting( 'post_titles_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - post titles font weight
	$wp_customize->add_control( 'post_titles_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Post Titles (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'post_titles_font_weight',
		'choices' => $font_weights
	) );
	// setting - post byline font family
	$wp_customize->add_setting( 'post_byline_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - post byline font family
	$wp_customize->add_control( 'post_byline_font', array(
		'type'        => 'select',
		'label'       => __( 'Post Byline', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'post_byline_font',
		'choices'     => $fonts
	) );
	// setting - post byline font weight
	$wp_customize->add_setting( 'post_byline_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - post byline font weight
	$wp_customize->add_control( 'post_byline_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Post Byline (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'post_byline_font_weight',
		'choices' => $font_weights
	) );
	// setting - post text font family
	$wp_customize->add_setting( 'post_text_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - post text font family
	$wp_customize->add_control( 'post_text_font', array(
		'type'        => 'select',
		'label'       => __( 'Post Text', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'post_text_font',
		'choices'     => $fonts
	) );
	// setting - post text font weight
	$wp_customize->add_setting( 'post_text_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - post text font weight
	$wp_customize->add_control( 'post_text_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Post Text (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'post_text_font_weight',
		'choices' => $font_weights
	) );
	// setting - comments font family
	$wp_customize->add_setting( 'comments_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - comments font family
	$wp_customize->add_control( 'comments_font', array(
		'type'        => 'select',
		'label'       => __( 'Comments', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'comments_font',
		'choices'     => $fonts
	) );
	// setting - comments font weight
	$wp_customize->add_setting( 'comments_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - comments font weight
	$wp_customize->add_control( 'comments_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Comments (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'comments_font_weight',
		'choices' => $font_weights
	) );
	// setting - widget titles font family
	$wp_customize->add_setting( 'ct_widget_titles_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - widget titles font family
	$wp_customize->add_control( 'ct_widget_titles_font', array(
		'type'        => 'select',
		'label'       => __( 'Widget Titles', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'ct_widget_titles_font',
		'choices'     => $fonts
	) );
	// setting - widget titles font weight
	$wp_customize->add_setting( 'ct_widget_titles_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - widget titles font weight
	$wp_customize->add_control( 'ct_widget_titles_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Widget Titles (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'ct_widget_titles_font_weight',
		'choices' => $font_weights
	) );
	// setting - widget text font family
	$wp_customize->add_setting( 'ct_widget_text_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - widget text font family
	$wp_customize->add_control( 'ct_widget_text_font', array(
		'type'        => 'select',
		'label'       => __( 'Widget Text', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'ct_widget_text_font',
		'choices'     => $fonts
	) );
	// setting - widget text font weight
	$wp_customize->add_setting( 'ct_widget_text_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - widget text font weight
	$wp_customize->add_control( 'ct_widget_text_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Widget Text (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'ct_widget_text_font_weight',
		'choices' => $font_weights
	) );
	// setting - footer text font family
	$wp_customize->add_setting( 'footer_text_font', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_family'
	) );
	// control - footer text font family
	$wp_customize->add_control( 'footer_text_font', array(
		'type'        => 'select',
		'label'       => __( 'Footer Text', 'period-pro' ),
		'section'     => 'ct_period_pro_fonts_advanced',
		'setting'     => 'footer_text_font',
		'choices'     => $fonts
	) );
	// setting - footer text font weight
	$wp_customize->add_setting( 'footer_text_font_weight', array(
		'default'           => 'default',
		'sanitize_callback' => 'ct_period_pro_sanitize_font_weight'
	) );
	// control - footer text font weight
	$wp_customize->add_control( 'footer_text_font_weight', array(
		'type'    => 'select',
		'label'   => __( 'Footer Text (Font weight)', 'period-pro' ),
		'section' => 'ct_period_pro_fonts_advanced',
		'setting' => 'footer_text_font_weight',
		'choices' => $font_weights
	) );

	//----------------------------------------------------------------------------------
  //	Featured Image Sizes
  //----------------------------------------------------------------------------------

	// section
	$wp_customize->add_section( 'ct_period_pro_featured_image_size', array(
		'title'    => __( 'Featured Image Size', 'period-pro' ),
		'priority' => 7
	) );
	// setting
	$wp_customize->add_setting( 'featured_image_size', array(
		'default'           => '2-1',
		'sanitize_callback' => 'ct_period_pro_sanitize_featured_image_size'
	) );
	// control
	$wp_customize->add_control( 'featured_image_size', array(
		'label'       => __( 'Aspect ratio for all Featured Images', 'period-pro' ),
		'description' => __( 'Size can be overridden in Post editor.', 'period-pro' ),
		'section'     => 'ct_period_pro_featured_image_size',
		'settings'    => 'featured_image_size',
		'type'        => 'select',
		'choices'     => array(
			'2-1'     => '2:1',
			'1-2'     => '1:2',
			'16-9'    => '16:9',
			'9-16'    => '9:16',
			'3-2'     => '3:2',
			'2-3'     => '2:3',
			'4-3'     => '4:3',
			'3-4'     => '3:4',
			'5-4'     => '5:4',
			'4-5'     => '4:5',
			'1-1'     => '1:1',
			'natural' => __( 'Natural Dimensions', 'period-pro' )
		)
	) );

	//----------------------------------------------------------------------------------
  //	Show/Hide Elements
  //----------------------------------------------------------------------------------
	
	/***** Header *****/

	$wp_customize->add_section( 'ct_period_pro_show_hide_header', array(
		'title' => __( 'Header', 'period-pro' ),
		'panel'	=> 'ct_period_pro_show_hide_panel'
	) );
	// setting - site title
	$wp_customize->add_setting( 'display_site_title', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - site title
	$wp_customize->add_control( 'display_site_title', array(
		'type'    => 'radio',
		'label'   => __( 'Site title', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_header',
		'setting' => 'display_site_title',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - tagline
	$wp_customize->add_setting( 'display_tagline', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - tagline
	$wp_customize->add_control( 'display_tagline', array(
		'type'    => 'radio',
		'label'   => __( 'Tagline', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_header',
		'setting' => 'display_tagline',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - primary menu
	$wp_customize->add_setting( 'display_primary_menu', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - primary menu
	$wp_customize->add_control( 'display_primary_menu', array(
		'type'    => 'radio',
		'label'   => __( 'Primary Menu', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_header',
		'setting' => 'display_primary_menu',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );

	/***** Post *****/

	$wp_customize->add_section( 'ct_period_pro_show_hide_post', array(
		'title' => __( 'Posts', 'period-pro' ),
		'panel'	=> 'ct_period_pro_show_hide_panel'
	) );
	// setting - post title
	$wp_customize->add_setting( 'display_post_title', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - post title
	$wp_customize->add_control( 'display_post_title', array(
		'type'     => 'radio',
		'label'    => __( 'Post title', 'period-pro' ),
		'section'  => 'ct_period_pro_show_hide_post',
		'setting'  => 'display_post_title',
		'priority' => 1,
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - Featured Image
	$wp_customize->add_setting( 'display_post_featured_image', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - Featured Image
	$wp_customize->add_control( 'display_post_featured_image', array(
		'type'     => 'radio',
		'label'    => __( 'Featured Image', 'period-pro' ),
		'section'  => 'ct_period_pro_show_hide_post',
		'setting'  => 'display_post_featured_image',
		'priority' => 1,
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );

	// Move post author name in byline to this new section (setting in Period)
	$wp_customize->get_control( 'display_post_author' )->section = 'ct_period_pro_show_hide_post';
	$wp_customize->get_control( 'display_post_author' )->priority = 2;

	// Move post date in byline to this new section (setting in Period)
	$wp_customize->get_control( 'display_post_date' )->section = 'ct_period_pro_show_hide_post';
	$wp_customize->get_control( 'display_post_date' )->priority = 3;

	// setting - post categories
	$wp_customize->add_setting( 'display_post_categories', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - post categories
	$wp_customize->add_control( 'display_post_categories', array(
		'type'    => 'radio',
		'label'   => __( 'Post categories', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_post',
		'setting' => 'display_post_categories',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - post tags
	$wp_customize->add_setting( 'display_post_tags', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - post tags
	$wp_customize->add_control( 'display_post_tags', array(
		'type'    => 'radio',
		'label'   => __( 'Post tags', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_post',
		'setting' => 'display_post_tags',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - post nav
	$wp_customize->add_setting( 'display_post_nav', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - post nav
	$wp_customize->add_control( 'display_post_nav', array(
		'type'    => 'radio',
		'label'   => __( 'Previous/Next post links', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_post',
		'setting' => 'display_post_nav',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - comment count
	$wp_customize->add_setting( 'display_comment_count', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - comment count
	$wp_customize->add_control( 'display_comment_count', array(
		'type'    => 'radio',
		'label'   => __( 'Comment count', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_post',
		'setting' => 'display_comment_count',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - comment date
	$wp_customize->add_setting( 'display_comment_date', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - comment count
	$wp_customize->add_control( 'display_comment_date', array(
		'type'    => 'radio',
		'label'   => __( 'Comment date', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_post',
		'setting' => 'display_comment_date',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );

	/***** Blog & Archives *****/

	$wp_customize->add_section( 'ct_period_pro_show_hide_blog_archives', array(
		'title' => __( 'Blog & Archives', 'period-pro' ),
		'panel'	=> 'ct_period_pro_show_hide_panel'
	) );
	// setting - Featured Images
	$wp_customize->add_setting( 'display_featured_images', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - Featured Images
	$wp_customize->add_control( 'display_featured_images', array(
		'type'    => 'radio',
		'label'   => __( 'Featured Images', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_blog_archives',
		'setting' => 'display_featured_images',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - more link
	$wp_customize->add_setting( 'display_more_link', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - more link
	$wp_customize->add_control( 'display_more_link', array(
		'type'    => 'radio',
		'label'   => __( '"Continue reading" button', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_blog_archives',
		'setting' => 'display_more_link',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - comments link
	$wp_customize->add_setting( 'display_comments_link', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - comments link
	$wp_customize->add_control( 'display_comments_link', array(
		'type'    => 'radio',
		'label'   => __( 'Comments link', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_blog_archives',
		'setting' => 'display_comments_link',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );

	/***** Archives *****/

	$wp_customize->add_section( 'ct_period_pro_show_hide_archives', array(
		'title' => __( 'Archives', 'period-pro' ),
		'panel'	=> 'ct_period_pro_show_hide_panel'
	) );
	// setting - archive title
	$wp_customize->add_setting( 'display_archive_title', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - archive title
	$wp_customize->add_control( 'display_archive_title', array(
		'type'    => 'radio',
		'label'   => __( 'Archive Title', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_archives',
		'setting' => 'display_archive_title',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );
	// setting - archive description
	$wp_customize->add_setting( 'display_archive_description', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - archive description
	$wp_customize->add_control( 'display_archive_description', array(
		'type'    => 'radio',
		'label'   => __( 'Archive Description', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_archives',
		'setting' => 'display_archive_description',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );

	/***** Footer *****/

	$wp_customize->add_section( 'ct_period_pro_show_hide_footer', array(
		'title' => __( 'Footer', 'period-pro' ),
		'panel'	=> 'ct_period_pro_show_hide_panel'
	) );

	// setting - footer
	$wp_customize->add_setting( 'display_footer', array(
		'default'           => 'show',
		'sanitize_callback' => 'ct_period_pro_sanitize_show_hide',
		'transport'         => 'postMessage'
	) );
	// control - footer
	$wp_customize->add_control( 'display_footer', array(
		'type'    => 'radio',
		'label'   => __( 'Footer', 'period-pro' ),
		'section' => 'ct_period_pro_show_hide_footer',
		'setting' => 'display_footer',
		'choices' => array(
			'show' => __( 'Show', 'period-pro' ),
			'hide' => __( 'Hide', 'period-pro' )
		)
	) );

	//----------------------------------------------------------------------------------
  //	Footer Text
  //----------------------------------------------------------------------------------

	// section
	$wp_customize->add_section( 'ct_period_pro_footer_text', array(
		'title'    => __( 'Footer Text', 'period-pro' ),
		'priority' => 9
	) );
	// setting
	$wp_customize->add_setting( 'footer_text', array(
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage'
	) );
	// control
	$wp_customize->add_control( 'footer_text', array(
		'label'    => __( 'Edit the text in your footer', 'period-pro' ),
		'section'  => 'ct_period_pro_footer_text',
		'settings' => 'footer_text',
		'type'     => 'textarea'
	) );
}

//----------------------------------------------------------------------------------
//	Sanitization Functions
//----------------------------------------------------------------------------------

// sanitize yes/no settings
function ct_period_pro_sanitize_yes_no_settings( $input ) {

	$valid = array(
		'yes' => __( 'Yes', 'period-pro' ),
		'no'  => __( 'No', 'period-pro' )
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_header_image_height_type( $input ) {

	$valid = array(
		'responsive' => __( 'Responsive', 'period-pro' ),
		'fixed'      => __( 'Fixed', 'period-pro' )
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_font_family( $input ) {

	$fonts['default'] = 'Default';
	$font_data = ct_period_pro_prepare_fonts();
	foreach ( $font_data as $font => $weights ) {
		$fonts[ $font ] = $font;
	}

	return array_key_exists( $input, $fonts ) ? $input : '';
}

function ct_period_pro_sanitize_font_weight( $input ) {

	$valid = array(
		'default' => __( 'Default', 'period-pro' ),
		'100' => __( 'Thin', 'period-pro' ),
		'200' => __( 'Extra-light', 'period-pro' ),
		'300' => __( 'Light', 'period-pro' ),
		'400' => __( 'Regular', 'period-pro' ),
		'500' => __( 'Medium', 'period-pro' ),
		'600' => __( 'Semi-Bold', 'period-pro' ),
		'700' => __( 'Bold', 'period-pro' ),
		'800' => __( 'Extra-Bold', 'period-pro' ),
		'900' => __( 'Ultra-Bold', 'period-pro' )
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_checkbox( $input ) {
	if ( $input == 1 ) {
		return 1;
	} else {
		return '';
	}
}

function ct_period_pro_sanitize_layout( $input ) {

	$valid = array(
		'two-column'    => __( 'Two columns', 'period-pro' ),
		'one-column'    => __( 'One column', 'period-pro' ),
		'right-sidebar' => __( 'Right sidebar', 'period-pro' ),
		'left-sidebar'  => __( 'Left sidebar', 'period-pro' ),
		'two-right'     => __( 'Two columns - Right sidebar', 'period-pro' ),
		'two-left'      => __( 'Two columns - Left sidebar', 'period-pro' ),
		'three-column'  => __( 'Three columns', 'period-pro' )
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_textures( $input ) {

	$valid = ct_period_pro_textures_array();

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_show_hide( $input ) {

	$valid = array(
		'show' => __( 'Show', 'period-pro' ),
		'hide' => __( 'Hide', 'period-pro' )
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_featured_image_size( $input ) {

	$valid = array(
		'2-1'     => '2:1',
		'1-2'     => '1:2',
		'16-9'    => '16:9',
		'9-16'    => '9:16',
		'3-2'     => '3:2',
		'2-3'     => '2:3',
		'4-3'     => '4:3',
		'3-4'     => '3:4',
		'5-4'     => '5:4',
		'4-5'     => '4:5',
		'1-1'     => '1:1',
		'natural' => __( 'Natural Dimensions', 'period-pro' )
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_header_image_type( $input ) {
	$valid = array(
		'image'  => __( 'Image', 'period-pro' ),
		'video'  => __( 'Video', 'period-pro' )
	);
	return array_key_exists( $input, $valid ) ? $input : '';
}

function ct_period_pro_sanitize_font_language( $input ) {

	$valid = array(
		'arabic' 			  => 'Arabic',
		'bengali' 			  => 'Bengali',
		'chinese-hong-kong'   => 'Chinese (Hong Kong)',
		'chinese-simplified'  => 'Chinese (Simplified)',
		'chinese-traditional' => 'Chinese (Traditional)',
		'cyrillic' 			  => 'Cyrillic',
		'cyrillic-ext' 		  => 'Cyrillic Extended',
		'devanagari' 		  => 'Devanagari',
		'greek' 			  => 'Greek',
		'greek-ext' 		  => 'Greek Extended',
		'gujarati' 			  => 'Gujarati',
		'gurmukhi' 			  => 'Gurmukhi',
		'hebrew' 			  => 'Hebrew',
		'japanese' 			  => 'Japanese',
		'kannade' 			  => 'Kannada',
		'khmer' 			  => 'Khmer',
		'korean' 			  => 'Korean',
		'latin' 			  => 'Latin',
		'latin-ext' 		  => 'Latin Extended',
		'malayalam' 		  => 'Malayalam',
		'myanmar' 			  => 'Myanmar',
		'oriya' 			  => 'Oriya',
		'sinhala' 			  => 'Sinhala',
		'tamil' 			  => 'Tamil',
		'telugu' 			  => 'Telugu',
		'thai' 				  => 'Thai',
		'vietnamese' 		  => 'Vietnamese'
	);

	return array_key_exists( $input, $valid ) ? $input : '';
}

//----------------------------------------------------------------------------------
//	Remove Customizer ad
//----------------------------------------------------------------------------------

function ct_period_pro_remove_customizer_ad( $content ) {
	return '';
}
add_filter( 'ct_period_customizer_ad', 'ct_period_pro_remove_customizer_ad' );