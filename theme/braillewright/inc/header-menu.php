<?php
/**
 * Header and menu options: the space between the logo and the menu, the text color of the
 * current page's menu link, and bold menu links.
 *
 * Built for Aaron's review of toptechtidbits.com on 2026-09-27. Measured there at a 1691px
 * window: 64px between the bottom of the logo and the tops of the menu's words, made of 16px
 * of empty space under the logo image (see the has-logo rules in style.css), the theme's 36px
 * under the title area (.title-container margin-bottom, 2.25em) and 12px of the menu's own
 * line spacing. And in Colors > Menus the current page's link had a background setting but no
 * text setting, so it kept the ordinary white link color on a yellow background: 1.51:1.
 *
 * Every option here is a theme setting, set per site in the Customizer (Aaron, 2026-09-27:
 * "Template-first, so all sites stay as consistent as possible"). He also asked for bold ("Also
 * no option to bold"). The bold options default to off. The current page's text defaults to automatic: it changes only where the ordinary link
 * color measures below 4.5:1 on the current page's background.
 *
 * FOR DEVELOPERS
 *
 * - braillewright_header_menu_css filters the finished CSS.
 *
 * @package Braillewright
 */

defined( 'ABSPATH' ) || exit;

//----------------------------------------------------------------------------------
//  Settings
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_header_menu_sanitize_logo_space' ) ) {
	/**
	 * Sanitize the space between the logo and the menu, in pixels, to 0 through 120.
	 *
	 * @param mixed $input The submitted value.
	 * @return int
	 */
	function braillewright_header_menu_sanitize_logo_space( $input ) {
		return min( absint( $input ), 120 );
	}
}

if ( ! function_exists( 'braillewright_header_menu_sanitize_optional_color' ) ) {
	/**
	 * Sanitize a color that may be left empty, which means "choose automatically".
	 *
	 * @param string $input The submitted value.
	 * @return string A #rrggbb color, or ''.
	 */
	function braillewright_header_menu_sanitize_optional_color( $input ) {
		$color = sanitize_hex_color( $input );
		return $color ? $color : '';
	}
}

if ( ! function_exists( 'braillewright_header_menu_customize_register' ) ) {
	/**
	 * Add the settings to the Customizer's Logo section and Colors > Menus section.
	 *
	 * Runs at priority 20, after the sections exist.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer manager.
	 */
	function braillewright_header_menu_customize_register( $wp_customize ) {
		$yes_no = array(
			'yes' => __( 'Yes', 'braillewright' ),
			'no'  => __( 'No', 'braillewright' ),
		);

		$wp_customize->add_setting(
			'logo_menu_space',
			array(
				'default'           => '48',
				'sanitize_callback' => 'braillewright_header_menu_sanitize_logo_space',
			)
		);
		$wp_customize->add_control(
			'logo_menu_space',
			array(
				'label'       => __( 'Space Above and Below the Menu, in Pixels', 'braillewright' ),
				'description' => __( 'Measured as you see it: from the bottom of the logo, or of the tagline if it sits lower, to the tops of the menu\'s words, and the same from the bottom of the menu\'s words to the page below it. On pages that show breadcrumbs, the space between the menu and the breadcrumbs comes from Margin Around the Breadcrumbs instead. Applies on screens wide enough to show the whole menu.', 'braillewright' ),
				'section'     => 'braillewright_logo_upload',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 120,
					'step' => 1,
				),
				'priority'    => 30,
			)
		);

		// Colors > Menus. Its color controls are numbered 1 to 11; priority 4 places these after
		// "Primary Menu Links Background (current)", which is also 4 but was added first.
		$wp_customize->add_setting(
			'colors_header_menu_links_current',
			array(
				'default'           => '',
				'sanitize_callback' => 'braillewright_header_menu_sanitize_optional_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'colors_header_menu_links_current',
				array(
					'label'       => __( 'Primary Menu Links (Current Page)', 'braillewright' ),
					'description' => __( 'The text of the link to the page being viewed, on the current-page background above. Leave empty to keep your link color where it is easy to read, or use black or white where it is not.', 'braillewright' ),
					'section'     => 'braillewright_features_colors_menus',
					'priority'    => 4,
				)
			)
		);

		foreach ( array(
			'menu_primary_bold_current' => array( __( 'Bold the Current Page in the Menu?', 'braillewright' ), __( 'The link to the page being viewed is drawn in heavier letters. Its width does not change, so the menu never shifts.', 'braillewright' ) ),
			'menu_primary_bold_hover'   => array( __( 'Bold Menu Links on Hover?', 'braillewright' ), __( 'A menu link is drawn in heavier letters under the mouse or keyboard focus. Its width does not change, so the menu never shifts.', 'braillewright' ) ),
		) as $id => $control ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => 'no',
					'sanitize_callback' => 'braillewright_sanitize_yes_no_settings',
				)
			);
			$wp_customize->add_control(
				$id,
				array(
					'label'       => $control[0],
					'description' => $control[1],
					'section'     => 'braillewright_features_colors_menus',
					'type'        => 'radio',
					'choices'     => $yes_no,
					'priority'    => 4,
				)
			);
		}
	}
}
add_action( 'customize_register', 'braillewright_header_menu_customize_register', 20 );

if ( ! function_exists( 'braillewright_header_menu_mods_to_remove' ) ) {
	/**
	 * Include these settings in the theme's "reset Customizer settings" action.
	 *
	 * @param string[] $mods_array Theme mods the reset removes.
	 * @return string[]
	 */
	function braillewright_header_menu_mods_to_remove( $mods_array ) {
		return array_merge( $mods_array, array( 'logo_menu_space', 'colors_header_menu_links_current', 'menu_primary_bold_current', 'menu_primary_bold_hover' ) );
	}
}
add_filter( 'braillewright_mods_to_remove', 'braillewright_header_menu_mods_to_remove' );

//----------------------------------------------------------------------------------
//  The look
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_header_menu_cap_offset' ) ) {
	/**
	 * How far below the top of a menu line its capital letters start, in pixels.
	 *
	 * Menu links are 1.715 times their font size tall (style.css), and in Roboto the capitals
	 * start about 0.48 of the font size below the top of such a line: half the extra line height
	 * plus the space above the capitals. Measured on 2026-09-27 at a 1691px window: 12px on
	 * toptechtidbits.com (24px menu), 15px on the five sites with a 32px menu, 10px on
	 * drkirkadams.com (22px). This gives 12, 15 and 11.
	 *
	 * @param int $font_size The menu's font size in pixels.
	 * @return int
	 */
	function braillewright_header_menu_cap_offset( $font_size ) {
		return (int) round( 0.48 * $font_size );
	}
}

if ( ! function_exists( 'braillewright_header_menu_current_color' ) ) {
	/**
	 * The text color for the current page's menu link, or '' to leave the ordinary link color.
	 *
	 * A chosen color is used as chosen. Left empty, the ordinary menu link color stays wherever
	 * it measures at least 4.5:1 on the current page's background, so a site that already reads
	 * well does not change; otherwise black or white, whichever contrasts more.
	 *
	 * @return string
	 */
	function braillewright_header_menu_current_color() {
		$chosen = braillewright_header_menu_sanitize_optional_color( get_theme_mod( 'colors_header_menu_links_current', '' ) );
		if ( '' !== $chosen ) {
			return $chosen;
		}

		$link       = sanitize_hex_color( get_theme_mod( 'colors_header_menu_links', '#ffffff' ) );
		$background = sanitize_hex_color( get_theme_mod( 'colors_header_menu_links_bg_current', '#242424' ) );
		$link       = $link ? $link : '#ffffff';
		$background = $background ? $background : '#242424';

		if ( braillewright_breadcrumbs_contrast( $link, $background ) >= 4.5 ) {
			return '';
		}

		return ( braillewright_breadcrumbs_contrast( '#000000', $background ) >= braillewright_breadcrumbs_contrast( '#ffffff', $background ) ) ? '#000000' : '#ffffff';
	}
}

if ( ! function_exists( 'braillewright_header_menu_bold' ) ) {
	/**
	 * Which bold options are switched on.
	 *
	 * @return array Keys 'current' and 'hover', both bool.
	 */
	function braillewright_header_menu_bold() {
		return array(
			'current' => 'yes' === get_theme_mod( 'menu_primary_bold_current', 'no' ),
			'hover'   => 'yes' === get_theme_mod( 'menu_primary_bold_hover', 'no' ),
		);
	}
}

if ( ! function_exists( 'braillewright_header_menu_css' ) ) {
	/**
	 * The CSS for the logo spacing, the current page's link and the bold options.
	 *
	 * The space above the menu: .title-container's margin-bottom (only from 900px, where the theme sets it and
	 * the menu lies flat) becomes the chosen space less the menu's own line spacing above its
	 * capitals, using the menu's tablet font size from 900 to 999px and its desktop size from
	 * 1000px, the widths the theme switches them at (features/inc/font-sizes.php).
	 *
	 * The current page's text rule steps aside while the item is hovered or focused, so the
	 * hover colors still apply to it, as they already do to its background.
	 *
	 * @return string
	 */
	function braillewright_header_menu_css() {
		$space   = braillewright_header_menu_sanitize_logo_space( get_theme_mod( 'logo_menu_space', 48 ) );
		$tablet  = absint( get_theme_mod( 'menu_primary_font_size_tablet', 14 ) );
		$desktop = absint( get_theme_mod( 'menu_primary_font_size_desktop', 14 ) );
		$tablet  = $tablet ? $tablet : 14;
		$desktop = $desktop ? $desktop : 14;

		$css  = '@media all and (min-width:900px) and (max-width:999px){.title-container{margin-bottom:' . max( 0, $space - braillewright_header_menu_cap_offset( $tablet ) ) . 'px;}}';
		$css .= '@media all and (min-width:1000px){.title-container{margin-bottom:' . max( 0, $space - braillewright_header_menu_cap_offset( $desktop ) ) . 'px;}}';

		// The same space BELOW the menu, on every page without a breadcrumb trail (Aaron, 2026-09-27,
		// on the home page: "there's an extreme amount of space below the menu ... tie that space ...
		// so that whatever it is, it's always equal"). Measured on toptechtidbits.com's home page with
		// 24 above: 38px from the bottom of the menu's words to the first post, the menu line's 8px
		// under its letters plus the theme's 30px .menu-primary-container margin. Measured the way the
		// breadcrumbs measure the space above their box (braillewright_breadcrumbs_menu_gap), so a page
		// with a trail and a page without one are spaced by the same rule. Pages WITH a trail keep the
		// breadcrumbs' own rule (.has-breadcrumbs .menu-primary-container, inc/breadcrumbs.php).
		$css .= '@media all and (min-width:900px) and (max-width:999px){body:not(.has-breadcrumbs) .menu-primary-container{margin-bottom:' . max( 0, $space - braillewright_breadcrumbs_menu_gap( $tablet ) ) . 'px;}}';
		$css .= '@media all and (min-width:1000px){body:not(.has-breadcrumbs) .menu-primary-container{margin-bottom:' . max( 0, $space - braillewright_breadcrumbs_menu_gap( $desktop ) ) . 'px;}}';

		$current = braillewright_header_menu_current_color();
		if ( '' !== $current ) {
			$css .= '.menu-primary li.current-menu-item:not(:hover) > a:not(:focus),.menu-primary li.current_page_item:not(:hover) > a:not(:focus){color:' . $current . ';}';
		}

		// Bold is drawn as a thicker outline on each letter, NOT a heavier font-weight. A heavier
		// weight makes the word wider, so a link turning bold under the mouse would push the rest
		// of its row along and could rewrap the menu; the current page's link would do the same
		// from page to page. Keeping room with a hidden bold copy was tried first and dropped: the
		// only places to put it are the ::before and ::after slots, which the theme already uses
		// for the dropdown arrows on items with submenus (::after left to right, ::before right to
		// left, style.css).
		$bold   = braillewright_header_menu_bold();
		$stroke = '-webkit-text-stroke:0.04em currentColor;';
		if ( $bold['current'] ) {
			$css .= '.menu-primary li.current-menu-item > a,.menu-primary li.current_page_item > a{' . $stroke . '}';
		}
		if ( $bold['hover'] ) {
			$css .= '.menu-primary a:hover,.menu-primary a:focus,.menu-primary li:hover > a{' . $stroke . '}';
		}

		return (string) apply_filters( 'braillewright_header_menu_css', $css );
	}
}

if ( ! function_exists( 'braillewright_header_menu_inline_css' ) ) {
	/**
	 * Attach the CSS after the Customizer colors (priority 99), so it wins.
	 */
	function braillewright_header_menu_inline_css() {
		wp_add_inline_style( braillewright_customizer_style_handle(), braillewright_sanitize_css( braillewright_header_menu_css() ) );
	}
}
add_action( 'wp_enqueue_scripts', 'braillewright_header_menu_inline_css', 100 );

if ( ! function_exists( 'braillewright_header_menu_customizer_scripts' ) ) {
	/**
	 * The contrast warnings shown under the menu's current-page and hover colors.
	 */
	function braillewright_header_menu_customizer_scripts() {
		wp_enqueue_script( 'braillewright-menu-customizer', get_template_directory_uri() . '/js/menu-customizer.js', array( 'customize-controls' ), braillewright_asset_version( get_template_directory() . '/js/menu-customizer.js' ), true );
		wp_localize_script(
			'braillewright-menu-customizer',
			'braillewrightMenu',
			array(
				/* translators: %s: a contrast ratio such as 3.2. */
				'warning' => __( 'This color measures %s to 1 against its menu background. Text needs at least 4.5 to 1.', 'braillewright' ),
			)
		);
	}
}
add_action( 'customize_controls_enqueue_scripts', 'braillewright_header_menu_customizer_scripts' );
