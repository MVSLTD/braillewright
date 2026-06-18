<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_custom_colors_data() {

	$color_sections = array(

		/***** Base *****/

		array(
			'section_id'    => 'ct_period_pro_colors_base',
			'section_title' => esc_html__( 'Base', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements across the entire site.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_base_background',
				'setting_default' => '#ededed',
				'control_label'   => esc_html__( 'Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_content_bg',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Content Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_headings',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Headings', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_links',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_links_hover',
				'setting_default' => '#757575',
				'control_label'   => esc_html__( 'Links (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_content',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Text', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_inputs',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Inputs', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_inputs_bg',
				'setting_default' => '#f7f7f7',
				'control_label'   => esc_html__( 'Inputs Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_inputs_bg_focus',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Inputs Background (focus)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_inputs_border',
				'setting_default' => '#d4d4d4',
				'control_label'   => esc_html__( 'Inputs Border', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_buttons',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Buttons', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_buttons_hover',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Buttons (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_buttons_bg',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Buttons Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_base_buttons_bg_hover',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Buttons Background (hover)', 'period-pro' )
			)
		),
		/***** Header *****/

		array(
			'section_id'    => 'ct_period_pro_colors_header',
			'section_title' => esc_html__( 'Header', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in the Header.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_header_bg',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_site_title',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Site Title', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_site_title_hover',
				'setting_default' => '#666666',
				'control_label'   => esc_html__( 'Site Title (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_tagline',
				'setting_default' => '#d4d4d4',
				'control_label'   => esc_html__( 'Tagline', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_social_icons',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Social Icons', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_social_icons_hover',
				'setting_default' => '#d4d4d4',
				'control_label'   => esc_html__( 'Social Icons (hover)', 'period-pro' )
			)

		),
		/***** Menus *****/
		array(
			'section_id'    => 'ct_period_pro_colors_menus',
			'section_title' => esc_html__( 'Menus', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in the Primary and Secondary menu.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_header_menu_links',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Primary Menu Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_menu_links_hover',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Primary Menu Links (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_menu_links_bg_hover',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Primary Menu Links Background (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_menu_links_bg_current',
				'setting_default' => '#242424',
				'control_label'   => esc_html__( 'Primary Menu Links Background (current)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_submenu_links',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Submenu links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_submenu_bg',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Submenu Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_mobile_menu_button',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Primary Mobile Menu Button', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_secondary_menu_links',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Secondary Menu Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_secondary_menu_links_hover',
				'setting_default' => '#d4d4d4',
				'control_label'   => esc_html__( 'Secondary Menu Links (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_secondary_menu_bg',
				'setting_default' => '#242424',
				'control_label'   => esc_html__( 'Secondary Menu Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_header_secondary_menu_button',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Secondary Mobile Menu Button', 'period-pro' )
			),
		),
		/***** Post *****/
		array(
			'section_id'    => 'ct_period_pro_colors_post',
			'section_title' => esc_html__( 'Posts', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in Posts on the Blog and Post pages.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_post_title',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Title', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_title_hover',
				'setting_default' => '#757575',
				'control_label'   => esc_html__( 'Title (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_content',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Text', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_links',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_links_hover',
				'setting_default' => '#757575',
				'control_label'   => esc_html__( 'Links (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_more_button',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'More Button', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_more_button_hover',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'More Button (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_more_button_bg',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'More Button Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_more_button_bg_hover',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'More Button Background (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_more_button_border',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'More Button Border', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_more_button_border_hover',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'More Button Border (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_comments_link',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Comments Link', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_post_comments_link_hover',
				'setting_default' => '#757575',
				'control_label'   => esc_html__( 'Comments Link (hover)', 'period-pro' )
			)
		),
		/***** Comments *****/

		array(
			'section_id'    => 'ct_period_pro_colors_comments',
			'section_title' => esc_html__( 'Comments', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in the Comments.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_comments_content',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Text', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_comments_links',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_comments_links_hover',
				'setting_default' => '#757575',
				'control_label'   => esc_html__( 'Links (hover)', 'period-pro' )
			)
		),
		/***** Widgets *****/

		array(
			'section_id'    => 'ct_period_pro_colors_widgets',
			'section_title' => esc_html__( 'Widgets', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in Widgets.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_widgets_headings',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Headings', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_widgets_content',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Content', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_widgets_links',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_widgets_links_hover',
				'setting_default' => '#757575',
				'control_label'   => esc_html__( 'Links (hover)', 'period-pro' )
			)
		),
		/***** Archives *****/

		array(
			'section_id'    => 'ct_period_pro_colors_archives',
			'section_title' => esc_html__( 'Archives', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in archives (categories, blog, tags, etc).', 'period-pro' ),
			array(
				'setting_id'      => 'colors_archives_header',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Archive Header', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_archives_header_bg',
				'setting_default' => '#242424',
				'control_label'   => esc_html__( 'Archive Header Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_archives_pagination',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Pagination Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_archives_pagination_current',
				'setting_default' => '#ededed',
				'control_label'   => esc_html__( 'Pagination - Current Page Background', 'period-pro' )
			)
		),
		/***** Footer *****/

		array(
			'section_id'    => 'ct_period_pro_colors_footer',
			'section_title' => esc_html__( 'Footer', 'period-pro' ),
			'description'   => esc_html__( 'These colors affect elements in the Footer.', 'period-pro' ),
			array(
				'setting_id'      => 'colors_footer_bg',
				'setting_default' => '#242424',
				'control_label'   => esc_html__( 'Background', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_footer_content',
				'setting_default' => '#d4d4d4',
				'control_label'   => esc_html__( 'Text', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_footer_links',
				'setting_default' => '#ffffff',
				'control_label'   => esc_html__( 'Links', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_footer_links_hover',
				'setting_default' => '#d4d4d4',
				'control_label'   => esc_html__( 'Links (hover)', 'period-pro' )
			),
			array(
				'setting_id'      => 'colors_footer_widgets_bg',
				'setting_default' => '#333333',
				'control_label'   => esc_html__( 'Widget Area Background', 'period-pro' )
			)
		)
	);

	return $color_sections;
}

// output the css
function ct_period_pro_custom_colors_css() {

	// get the data
	$color_sections = ct_period_pro_custom_colors_data();

	// set array
	$custom_css = '';

	// for each section
	foreach ( $color_sections as $section ) {

		// for each setting
		foreach ( $section as $setting ) {

			// error checking
			if ( is_array( $setting ) ) {

				// get the color value
				$value = get_theme_mod( $setting['setting_id'] );

				// if not empty and not equal to default value
				if ( $value && $value !== $setting['setting_default'] ) {
					// output the css

					/***** Base *****/

					if ( $setting['setting_id'] == 'colors_base_background' ) {
						$custom_css .= "body, #overflow-container {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_content_bg' ) {
						$custom_css .= ".entry > article,
										.comments-number,
										li.comment > article,
										li.pingback > article,
										.comment-respond,
										.comments-closed,
										.comment-pagination,
										.pagination,
										.search-bottom,
										.sidebar-primary .widget,
										.main .sidebar-before-main-content {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_headings' ) {
						$custom_css .= "h1, h2, h3, h4, h5, h6 {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_links' ) {
						$custom_css .= "a, a:link, a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_links_hover' ) {
						$custom_css .= "a:hover, a:active, a:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_content' ) {
						$custom_css .= "body {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_inputs' ) {
						$custom_css .= "input:not([type='submit']),
						                textarea {color: $value !important;}";
					} elseif ( $setting['setting_id'] == 'colors_base_inputs_bg' ) {
						$custom_css .= "input:not([type='submit']),
						                textarea {background: $value !important;}";
					} elseif ( $setting['setting_id'] == 'colors_base_inputs_bg_focus' ) {
						$custom_css .= "input:not([type='submit']):focus,
						                textarea:focus {background: $value !important;}";
					} elseif ( $setting['setting_id'] == 'colors_base_inputs_border' ) {
						$custom_css .= "input:not([type='submit']),
						                textarea {border-color: $value !important;}";
					} elseif ( $setting['setting_id'] == 'colors_base_buttons' ) {
						$custom_css .= "input[type='submit'] {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_buttons_hover' ) {
						$custom_css .= "input[type='submit']:hover,
						                input[type='submit']:active,
						                input[type='submit']:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_buttons_bg' ) {
						$custom_css .= "input[type='submit'] {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_base_buttons_bg_hover' ) {
						$custom_css .= "input[type='submit']:hover,
						                input[type='submit']:active,
						                input[type='submit']:focus {background: $value;}";
					}
					/***** Header *****/
					elseif ( $setting['setting_id'] == 'colors_header_bg' ) {
						$custom_css .= ".site-header {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_site_title' ) {
						$custom_css .= ".site-title a,
						                .site-title a:link,
						                .site-title a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_site_title_hover' ) {
						$custom_css .= ".site-title a:hover,
						                .site-title a:active,
						                .site-title a:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_tagline' ) {
						$custom_css .= ".tagline {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_social_icons' ) {
						$custom_css .= ".social-media-icons a,
						                .social-media-icons a:link,
						                .social-media-icons a:visited,
						                .site-header .search-form-container i {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_social_icons_hover' ) {
						$custom_css .= ".social-media-icons a:hover,
						                .social-media-icons a:active,
						                .social-media-icons a:focus {color: $value;}";
					}
					/***** Menus *****/
					elseif ( $setting['setting_id'] == 'colors_header_menu_links' ) {
						$custom_css .= ".menu-primary a,
										.menu-primary a:link,
										.menu-primary a:visited,
										.menu-primary .toggle-dropdown {color: $value;}
										.menu-primary .toggle-dropdown {border-color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_menu_links_hover' ) {
						$custom_css .= ".menu-primary a:hover,
										.menu-primary a:active,
										.menu-primary a:focus,
										.menu-primary li:hover > a,
										.menu-primary .toggle-dropdown:hover,
										.menu-primary .toggle-dropdown:active,
										.menu-primary .toggle-dropdown:focus {color: $value;}
										.menu-primary .toggle-dropdown:hover,
										.menu-primary .toggle-dropdown:active,
										.menu-primary .toggle-dropdown:focus {border-color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_menu_links_bg_hover' ) {
						$custom_css .= ".menu-primary-items > li > a:hover,
										.menu-primary-items > li > a:active,
										.menu-primary-items > li > a:focus,
										.menu-primary-items > li:hover > a,
										.menu-primary-items > li.current-menu-item > a:hover,
										.menu-primary-items > li.current-menu-item:hover > a,
										.menu-unset > ul > li > a:hover,
										.menu-unset > ul > li > a:active,
										.menu-unset > ul > li > a:focus,
										.menu-unset > ul > li:hover > a,
										.menu-unset > ul > li.current_page_item > a:hover,
										.menu-unset > ul > li.current_page_item:hover > a {background: $value;}
										@media all and (min-width: 56.25em) {
											.menu-primary-items > li:hover,
											.menu-primary-items > li.current-menu-item:hover,
											.menu-unset > ul > li:hover,
											.menu-unset > ul > li.current_page_item:hover {background: $value;}
										}";
					} elseif ( $setting['setting_id'] == 'colors_header_menu_links_bg_current' ) {
						$custom_css .= ".menu-primary li.current-menu-item > a,
										.menu-primary li.current_page_item > a {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_mobile_menu_button' ) {
						$custom_css .= "#toggle-navigation svg g {fill: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_submenu_links' ) {
						$custom_css .= "@media all and (min-width: 56.25em) {
											.menu-primary ul ul a,
											.menu-primary ul ul a:link,
											.menu-primary ul ul a:visited {color: $value;}
										}";
					} elseif ( $setting['setting_id'] == 'colors_header_submenu_bg' ) {
						$custom_css .= "@media all and (min-width: 56.25em) {
											.menu-primary ul ul {background: $value;}
											.menu-primary ul ul li:hover,
											.menu-primary ul ul li:hover > a,
											.menu-primary ul ul li:active,
											.menu-primary ul ul li:active > a,
											.menu-primary ul ul a:hover,
											.menu-primary ul ul a:active,
											.menu-primary ul ul a:focus {background: $value;}
										}";
					} elseif ( $setting['setting_id'] == 'colors_header_secondary_menu_links' ) {
						$custom_css .= "#menu-secondary-items a,
						                #menu-secondary-items a:link,
						                #menu-secondary-items a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_secondary_menu_links_hover' ) {
						$custom_css .= "#menu-secondary-items a:hover,
						                #menu-secondary-items a:active,
						                #menu-secondary-items a:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_secondary_menu_bg' ) {
						$custom_css .= ".overflow-container .menu-secondary-container,
										#menu-secondary-items ul {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_header_secondary_menu_button' ) {
						$custom_css .= "#toggle-secondary-navigation {color: $value;}";
					} /***** Post *****/

					elseif ( $setting['setting_id'] == 'colors_post_title' ) {
						$custom_css .= ".entry .post-title,
						                .entry .post-title a,
						                .entry .post-title a:link,
						                .entry .post-title a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_title_hover' ) {
						$custom_css .= ".entry .post-title a:hover,
						                .entry .post-title a:active,
						                .entry .post-title a:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_content' ) {
						$custom_css .= ".entry > article {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_links' ) {
						$custom_css .= ".blog .post-content :not(.comments-link) :not(.more-link) a,
						                .archive .post-content :not(.comments-link) :not(.more-link) a:link,
						                .search .post-content :not(.comments-link) :not(.more-link) a:visited,
						                .post-byline a,
						                .post-byline a:link,
						                .post-byline a:visited,
						                .singular .post-container a,
						                .singular .post-container a:link,
						                .singular .post-container a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_links_hover' ) {
						$custom_css .= ".singular .post-container a:hover,
						                .singular .post-container a:active,
						                .singular .post-container a:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_more_button' ) {
						$custom_css .= ".entry .more-link,
						                .entry .more-link:link,
						                .entry .more-link:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_more_button_hover' ) {
						$custom_css .= ".entry .more-link:hover,
						                .entry .more-link:active,
						                .entry .more-link:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_more_button_bg' ) {
						$custom_css .= ".entry .more-link,
						                .entry .more-link:link,
						                .entry .more-link:visited {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_more_button_bg_hover' ) {
						$custom_css .= ".entry .more-link:hover,
										.entry .more-link:active,
										.entry .more-link:focus {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_more_button_border' ) {
						$custom_css .= ".entry .more-link {border-color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_more_button_border_hover' ) {
						$custom_css .= ".entry .more-link:hover,
						                .entry .more-link:active,
						                .entry .more-link:focus {border-color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_comments_link' ) {
						$custom_css .= ".comments-link,
										.entry .comments-link a,
						                .entry .comments-link a:link,
						                .entry .comments-link a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_post_comments_link_hover' ) {
						$custom_css .= ".entry .comments-link:hover,
										.entry .comments-link a:hover,
						                .entry .comments-link a:active,
						                .entry .comments-link a:focus {color: $value;}";
					} /***** Comments *****/

					elseif ( $setting['setting_id'] == 'colors_comments_content' ) {
						$custom_css .= "li.comment,
						                li.pingback {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_comments_links' ) {
						$custom_css .= "li.comment a,
						                li.comment a:link,
						                li.comment a:visited,
					                    li.pingback a,
				                        li.pingback a:link,
			                            li.pingback a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_comments_links_hover' ) {
						$custom_css .= "li.comment a:hover,
						                li.comment a:active,
						                li.comment a:focus,
					                    li.pingback a:hover,
				                        li.pingback a:active,
			                            li.pingback a:focus  {color: $value;}";
					} /***** Widgets *****/

					elseif ( $setting['setting_id'] == 'colors_widgets_headings' ) {
						$custom_css .= ".widget h1,
										.widget h2,
										.widget h3,
										.widget h4,
										.widget h5,
										.widget h6 {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_widgets_content' ) {
						$custom_css .= ".widget {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_widgets_links' ) {
						$custom_css .= ".widget a,
						                .widget a:link,
						                .widget a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_widgets_links_hover' ) {
						$custom_css .= ".widget a:hover,
						                .widget a:active,
						                .widget a:focus {color: $value;}";
					}
					/***** Archives *****/

					elseif ( $setting['setting_id'] == 'colors_archives_header' ) {
						$custom_css .= ".archive-header {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_archives_header_bg' ) {
						$custom_css .= ".archive-header h2 {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_archives_pagination' ) {
						$custom_css .= ".pagination,
						                .pagination span,
						                .pagination a,
						                .pagination a:link,
						                .pagination a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_archives_pagination_current' ) {
						$custom_css .= ".pagination span.current,
						                .pagination a.current {background: $value;}";
					}
					/***** Footer *****/

					elseif ( $setting['setting_id'] == 'colors_footer_bg' ) {
						$custom_css .= ".site-footer, .design-credit {background: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_footer_content' ) {
						$custom_css .= ".site-footer .design-credit {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_footer_links' ) {
						$custom_css .= ".site-footer .design-credit a,
						                .site-footer .design-credit a:link,
						                .site-footer .design-credit a:visited {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_footer_links_hover' ) {
						$custom_css .= ".site-footer .design-credit a:hover,
										.site-footer .design-credit a:active,
										.site-footer .design-credit a:focus {color: $value;}";
					} elseif ( $setting['setting_id'] == 'colors_footer_widgets_bg' ) {
						$custom_css .= ".site-footer {background: $value;}";
					}
				}
			}
		}
	}

	$custom_css = ct_period_pro_sanitize_css( $custom_css );

	wp_add_inline_style( 'ct-period-style-rtl', $custom_css );
	wp_add_inline_style( 'ct-period-style', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_custom_colors_css', 99 );