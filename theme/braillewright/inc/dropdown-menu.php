<?php
/**
 * Dropdown menus: the lists that open under a main menu item.
 *
 * Built for Aaron's review of 2026-09-27. Asked "Maybe we might want to consider adding the functionality to
 * the theme itself to better manage the formatting of submenu items", he chose all eight settings offered in
 * round 2026-09-27-dropdown-menus-release-A: text size, hover colors, a current-page color, colors on phones,
 * spacing, a border and shadow, contrast warnings, and one Customizer section holding them all.
 *
 * Before this the theme had two dropdown settings, both in Colors > Menus and both only from 900 pixels up:
 * the link color and the background. Everything else followed the main menu or was fixed. Measured on
 * donnajodhan.com: 32 pixel dropdown text because its main menu is 32 pixels, and a hover background the same
 * white as the dropdown, so only the text changed under the mouse.
 *
 * ⛔ Every setting starts at today's look, so an update changes nothing on any site until the owner chooses
 * (the promise in the round). Nothing is printed while a setting is at its default. The one automatic rule,
 * the hover text, changes only a pair that measures below 4.5:1, and no fleet site had one when measured.
 *
 * FOR DEVELOPERS
 *
 * - braillewright_dropdown_menu_css filters the finished CSS.
 *
 * @package Braillewright
 */

defined( 'ABSPATH' ) || exit;

//----------------------------------------------------------------------------------
//  Settings
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_dropdown_menu_sanitize_font_size' ) ) {
	/**
	 * Sanitize the dropdown text size: empty (the main menu's size) or 10 through 48 pixels.
	 *
	 * @param mixed $input The submitted value.
	 * @return string
	 */
	function braillewright_dropdown_menu_sanitize_font_size( $input ) {
		if ( '' === $input || null === $input ) {
			return '';
		}
		$size = absint( $input );
		return $size ? (string) max( 10, min( $size, 48 ) ) : '';
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_sanitize_spacing' ) ) {
	/**
	 * Sanitize a spacing value, in pixels, to 0 through 40.
	 *
	 * @param mixed $input The submitted value.
	 * @return int
	 */
	function braillewright_dropdown_menu_sanitize_spacing( $input ) {
		return min( absint( $input ), 40 );
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_sanitize_optional_color' ) ) {
	/**
	 * Sanitize a color that may be left empty, which keeps today's look.
	 *
	 * @param string $input The submitted value.
	 * @return string A #rrggbb color, or ''.
	 */
	function braillewright_dropdown_menu_sanitize_optional_color( $input ) {
		$color = sanitize_hex_color( $input );
		return $color ? $color : '';
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_sanitize_border' ) ) {
	/**
	 * Sanitize the border choice.
	 *
	 * @param string $input The submitted value.
	 * @return string 'none' or 'line'.
	 */
	function braillewright_dropdown_menu_sanitize_border( $input ) {
		return 'line' === $input ? 'line' : 'none';
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_sanitize_shadow' ) ) {
	/**
	 * Sanitize the shadow choice.
	 *
	 * @param string $input The submitted value.
	 * @return string 'none' or 'soft'.
	 */
	function braillewright_dropdown_menu_sanitize_shadow( $input ) {
		return 'soft' === $input ? 'soft' : 'none';
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_customize_register' ) ) {
	/**
	 * Add the Dropdown Menus section, its settings, and move the two existing dropdown colors into it.
	 *
	 * Runs at priority 30, after Colors > Menus has registered those two controls (priority 11). The
	 * settings keep their ids, so every saved value stays exactly as it was.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer manager.
	 */
	function braillewright_dropdown_menu_customize_register( $wp_customize ) {
		$section = 'braillewright_dropdown_menus';

		$wp_customize->add_section(
			$section,
			array(
				'title'       => __( 'Dropdown Menus', 'braillewright' ),
				'priority'    => 57,
				'description' => __( 'The lists that open under a main menu item. Every setting starts at the theme\'s usual look, so nothing changes until you choose.', 'braillewright' ),
			)
		);

		foreach ( array(
			'colors_header_submenu_links' => array( __( 'Dropdown Link Color', 'braillewright' ), 10 ),
			'colors_header_submenu_bg'    => array( __( 'Dropdown Background', 'braillewright' ), 20 ),
		) as $id => $moved ) {
			$control = $wp_customize->get_control( $id );
			if ( $control ) {
				$control->section  = $section;
				$control->label    = $moved[0];
				$control->priority = $moved[1];
			}
		}

		$wp_customize->add_setting(
			'dropdown_font_size',
			array(
				'default'           => '',
				'sanitize_callback' => 'braillewright_dropdown_menu_sanitize_font_size',
			)
		);
		$wp_customize->add_control(
			'dropdown_font_size',
			array(
				'label'       => __( 'Dropdown Text Size, in Pixels', 'braillewright' ),
				'description' => __( 'Leave empty to use the main menu\'s text size, as before.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 48,
					'step' => 1,
				),
				'priority'    => 5,
			)
		);

		foreach ( array(
			'dropdown_hover_text'    => array( __( 'Dropdown Link Color on Hover', 'braillewright' ), __( 'The text of the item under the mouse or keyboard focus. Leave empty to keep your main menu\'s hover color where it is easy to read on the hover background, or use black or white where it is not.', 'braillewright' ), 30 ),
			'dropdown_hover_bg'      => array( __( 'Dropdown Background on Hover', 'braillewright' ), __( 'Behind the item under the mouse or keyboard focus. Leave empty to keep the dropdown background.', 'braillewright' ), 40 ),
			'dropdown_current_color' => array( __( 'Dropdown Link Color (Current Page)', 'braillewright' ), __( 'The text of the item for the page being viewed. Leave empty to keep the dropdown link color. Heavier letters for the current page are set by Bold the Current Page in the Menu.', 'braillewright' ), 50 ),
			'dropdown_border_color'  => array( __( 'Dropdown Border Color', 'braillewright' ), __( 'Used when Dropdown Border is a line. Leave empty to use the dropdown link color.', 'braillewright' ), 90 ),
		) as $id => $control ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => '',
					'sanitize_callback' => 'braillewright_dropdown_menu_sanitize_optional_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					$id,
					array(
						'label'       => $control[0],
						'description' => $control[1],
						'section'     => $section,
						'priority'    => $control[2],
					)
				)
			);
		}

		$wp_customize->add_setting(
			'dropdown_colors_phones',
			array(
				'default'           => 'no',
				'sanitize_callback' => 'braillewright_sanitize_yes_no_settings',
			)
		);
		$wp_customize->add_control(
			'dropdown_colors_phones',
			array(
				'label'       => __( 'Use the Dropdown Colors on Phones Too?', 'braillewright' ),
				'description' => __( 'On narrow screens the menu opens as a list, and its dropdown items look like main menu items. Choose Yes to give them the dropdown colors above.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'radio',
				'choices'     => array(
					'yes' => __( 'Yes', 'braillewright' ),
					'no'  => __( 'No', 'braillewright' ),
				),
				'priority'    => 60,
			)
		);

		foreach ( array(
			'dropdown_item_spacing' => array( __( 'Space Between Dropdown Items, in Pixels', 'braillewright' ), __( 'Also the space above the first item and below the last.', 'braillewright' ), '6', 70 ),
			'dropdown_box_padding'  => array( __( 'Space Inside the Dropdown Box, in Pixels', 'braillewright' ), __( 'Between the edge of the dropdown and its items.', 'braillewright' ), '0', 75 ),
		) as $id => $control ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $control[2],
					'sanitize_callback' => 'braillewright_dropdown_menu_sanitize_spacing',
				)
			);
			$wp_customize->add_control(
				$id,
				array(
					'label'       => $control[0],
					'description' => $control[1],
					'section'     => $section,
					'type'        => 'number',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					),
					'priority'    => $control[3],
				)
			);
		}

		$wp_customize->add_setting(
			'dropdown_border',
			array(
				'default'           => 'none',
				'sanitize_callback' => 'braillewright_dropdown_menu_sanitize_border',
			)
		);
		$wp_customize->add_control(
			'dropdown_border',
			array(
				'label'    => __( 'Dropdown Border', 'braillewright' ),
				'section'  => $section,
				'type'     => 'radio',
				'choices'  => array(
					'none' => __( 'None', 'braillewright' ),
					'line' => __( 'A Thin Line', 'braillewright' ),
				),
				'priority' => 80,
			)
		);

		$wp_customize->add_setting(
			'dropdown_shadow',
			array(
				'default'           => 'none',
				'sanitize_callback' => 'braillewright_dropdown_menu_sanitize_shadow',
			)
		);
		$wp_customize->add_control(
			'dropdown_shadow',
			array(
				'label'    => __( 'Dropdown Shadow', 'braillewright' ),
				'section'  => $section,
				'type'     => 'radio',
				'choices'  => array(
					'none' => __( 'None', 'braillewright' ),
					'soft' => __( 'A Soft Shadow', 'braillewright' ),
				),
				'priority' => 95,
			)
		);
	}
}
add_action( 'customize_register', 'braillewright_dropdown_menu_customize_register', 30 );

if ( ! function_exists( 'braillewright_dropdown_menu_mods_to_remove' ) ) {
	/**
	 * Include these settings in the theme's "reset Customizer settings" action.
	 *
	 * @param string[] $mods_array Theme mods the reset removes.
	 * @return string[]
	 */
	function braillewright_dropdown_menu_mods_to_remove( $mods_array ) {
		return array_merge( $mods_array, array( 'dropdown_font_size', 'dropdown_hover_text', 'dropdown_hover_bg', 'dropdown_current_color', 'dropdown_colors_phones', 'dropdown_item_spacing', 'dropdown_box_padding', 'dropdown_border', 'dropdown_border_color', 'dropdown_shadow' ) );
	}
}
add_filter( 'braillewright_mods_to_remove', 'braillewright_dropdown_menu_mods_to_remove' );

//----------------------------------------------------------------------------------
//  The look
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_dropdown_menu_colors' ) ) {
	/**
	 * The dropdown's colors as the page will show them.
	 *
	 * 'link' and 'background' are the two older settings (defaults from style.css). 'hover_text' is '' when
	 * today's hover text stays; otherwise the chosen color, or black or white when today's measures below
	 * 4.5:1 on the hover background. 'hover_background' is '' when it stays the dropdown background.
	 *
	 * @return array
	 */
	function braillewright_dropdown_menu_colors() {
		$link       = sanitize_hex_color( get_theme_mod( 'colors_header_submenu_links', '#333333' ) );
		$background = sanitize_hex_color( get_theme_mod( 'colors_header_submenu_bg', '#ffffff' ) );
		$link       = $link ? $link : '#333333';
		$background = $background ? $background : '#ffffff';

		$hover_background = braillewright_dropdown_menu_sanitize_optional_color( get_theme_mod( 'dropdown_hover_bg', '' ) );
		$hover_bg_seen    = '' !== $hover_background ? $hover_background : $background;

		$hover_text = braillewright_dropdown_menu_sanitize_optional_color( get_theme_mod( 'dropdown_hover_text', '' ) );
		if ( '' === $hover_text ) {
			// Today the dropdown's hover text is the main menu's hover color (features/inc/colors.php).
			$today = sanitize_hex_color( get_theme_mod( 'colors_header_menu_links_hover', '#333333' ) );
			$today = $today ? $today : '#333333';
			if ( braillewright_breadcrumbs_contrast( $today, $hover_bg_seen ) < 4.5 ) {
				$hover_text = ( braillewright_breadcrumbs_contrast( '#000000', $hover_bg_seen ) >= braillewright_breadcrumbs_contrast( '#ffffff', $hover_bg_seen ) ) ? '#000000' : '#ffffff';
			}
		}

		return array(
			'link'             => $link,
			'background'       => $background,
			'hover_text'       => $hover_text,
			'hover_background' => $hover_background,
			'current'          => braillewright_dropdown_menu_sanitize_optional_color( get_theme_mod( 'dropdown_current_color', '' ) ),
		);
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_css' ) ) {
	/**
	 * The CSS for the dropdown settings. Empty while every setting is at its default.
	 *
	 * The color rules apply from 900 pixels, where the dropdown is a box under its item, like the two older
	 * settings in features/inc/colors.php; with "Use the Dropdown Colors on Phones Too?" they apply at every
	 * width, and the older two are added below 900 pixels as well. Spacing, border and shadow shape the box,
	 * so they apply from 900 pixels only. The current page's rule matches the main menu's current-page rule
	 * (inc/header-menu.php) in how it steps aside on hover and focus, and outranks it.
	 *
	 * @return string
	 */
	function braillewright_dropdown_menu_css() {
		$colors = braillewright_dropdown_menu_colors();
		$phones = 'yes' === get_theme_mod( 'dropdown_colors_phones', 'no' );
		$wide   = '@media all and (min-width:56.25em){';
		$narrow = '@media all and (max-width:56.1875em){';

		$css = '';

		$size = braillewright_dropdown_menu_sanitize_font_size( get_theme_mod( 'dropdown_font_size', '' ) );
		if ( '' !== $size ) {
			$css .= '.menu-primary ul ul a{font-size:' . $size . 'px;}';
		}

		$color_rules = '';
		if ( '' !== $colors['hover_background'] ) {
			$color_rules .= '.menu-primary ul ul li:hover > a,.menu-primary ul ul a:hover,.menu-primary ul ul a:active,.menu-primary ul ul a:focus{background:' . $colors['hover_background'] . ';}';
		}
		if ( '' !== $colors['hover_text'] ) {
			$color_rules .= '.menu-primary ul ul li:hover > a,.menu-primary ul ul a:hover,.menu-primary ul ul a:active,.menu-primary ul ul a:focus{color:' . $colors['hover_text'] . ';}';
		}
		if ( '' !== $colors['current'] ) {
			$color_rules .= '.menu-primary ul ul li.current-menu-item:not(:hover) > a:not(:focus),.menu-primary ul ul li.current_page_item:not(:hover) > a:not(:focus){color:' . $colors['current'] . ';}';
		}
		if ( $phones ) {
			// Below 900 pixels, the two older colors (features/inc/colors.php stops at 900) and, as there, the
			// dropdown background under a hovered item unless a hover background is chosen.
			$css .= $narrow . '.menu-primary ul ul{background:' . $colors['background'] . ';}.menu-primary ul ul a,.menu-primary ul ul a:link,.menu-primary ul ul a:visited{color:' . $colors['link'] . ';}';
			if ( '' === $colors['hover_background'] ) {
				$css .= '.menu-primary ul ul li:hover > a,.menu-primary ul ul a:hover,.menu-primary ul ul a:active,.menu-primary ul ul a:focus{background:' . $colors['background'] . ';}';
			}
			$css .= '}' . $color_rules;
		} elseif ( '' !== $color_rules ) {
			$css .= $wide . $color_rules . '}';
		}

		$box     = '';
		$spacing = braillewright_dropdown_menu_sanitize_spacing( get_theme_mod( 'dropdown_item_spacing', 6 ) );
		if ( 6 !== $spacing ) {
			$box .= '.menu-primary ul ul li{margin-bottom:' . $spacing . 'px;}.menu-primary ul ul li:first-child{margin-top:' . $spacing . 'px;}.menu-primary ul ul li:last-child{margin-bottom:' . $spacing . 'px;}';
		}
		$padding = braillewright_dropdown_menu_sanitize_spacing( get_theme_mod( 'dropdown_box_padding', 0 ) );
		if ( $padding ) {
			$box .= '.menu-primary ul ul{padding:' . $padding . 'px;}';
		}
		if ( 'line' === braillewright_dropdown_menu_sanitize_border( get_theme_mod( 'dropdown_border', 'none' ) ) ) {
			$border = braillewright_dropdown_menu_sanitize_optional_color( get_theme_mod( 'dropdown_border_color', '' ) );
			$box   .= '.menu-primary ul ul{border:1px solid ' . ( '' !== $border ? $border : $colors['link'] ) . ';}';
		}
		if ( 'soft' === braillewright_dropdown_menu_sanitize_shadow( get_theme_mod( 'dropdown_shadow', 'none' ) ) ) {
			$box .= '.menu-primary ul ul{box-shadow:0 4px 12px rgba(0,0,0,0.25);}';
		}
		if ( '' !== $box ) {
			$css .= $wide . $box . '}';
		}

		return (string) apply_filters( 'braillewright_dropdown_menu_css', $css );
	}
}

if ( ! function_exists( 'braillewright_dropdown_menu_inline_css' ) ) {
	/**
	 * Attach the CSS after the Customizer colors (99) and the menu options (100), so it wins.
	 */
	function braillewright_dropdown_menu_inline_css() {
		$css = braillewright_dropdown_menu_css();
		if ( '' !== $css ) {
			wp_add_inline_style( braillewright_customizer_style_handle(), braillewright_sanitize_css( $css ) );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'braillewright_dropdown_menu_inline_css', 101 );

if ( ! function_exists( 'braillewright_dropdown_menu_customizer_scripts' ) ) {
	/**
	 * The contrast warnings shown under the dropdown colors.
	 */
	function braillewright_dropdown_menu_customizer_scripts() {
		wp_enqueue_script( 'braillewright-dropdown-customizer', get_template_directory_uri() . '/js/dropdown-customizer.js', array( 'customize-controls' ), braillewright_asset_version( get_template_directory() . '/js/dropdown-customizer.js' ), true );
		wp_localize_script(
			'braillewright-dropdown-customizer',
			'braillewrightDropdown',
			array(
				/* translators: %s: a contrast ratio such as 3.2. */
				'warning' => __( 'This color measures %s to 1 against its dropdown background. Text needs at least 4.5 to 1.', 'braillewright' ),
			)
		);
	}
}
add_action( 'customize_controls_enqueue_scripts', 'braillewright_dropdown_menu_customizer_scripts' );
