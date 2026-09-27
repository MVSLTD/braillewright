<?php
/**
 * Breadcrumbs, built into the theme.
 *
 * A trail of links above the content that shows where a page sits in the site, for example
 * Home > News > Article title. Switched on in the Customizer under Breadcrumbs, and off by
 * default, so upgrading the theme changes nothing on a site until its owner chooses to.
 *
 * WHY THE THEME BUILDS ITS OWN TRAIL
 *
 * Until 2.0.11 the theme's only breadcrumb support was one call to Yoast SEO's
 * yoast_breadcrumb(), so a site without Yoast could not have breadcrumbs at all, and a site
 * with Yoast got them painted white. Measured on TTT staging on 2026-09-26: the white text sat
 * on the dark header band, and at phone width a long post title ran off the band onto the gray
 * page, where lines 4 to 6 measured 1.27:1. Yoast's markup is also a <p> of <span>s, with no
 * navigation landmark and no list, and its separator is read aloud.
 *
 * WordPress 7.0 added a Breadcrumbs block, but this theme supports WordPress 5.2 and later, so
 * it cannot depend on that block. The trail below follows the same rules as that block and
 * adds the parts it leaves out: the blog page on posts and post archives, the author's chosen
 * main category, and structured data for search engines.
 *
 * WHAT IT PRODUCES
 *
 * - A <nav aria-label="Breadcrumb"> landmark holding an ordered list, with the current page
 *   marked aria-current="page". This is the markup pattern the W3C ARIA Authoring Practices
 *   Guide describes for breadcrumbs.
 * - Separators in aria-hidden spans, so screen readers do not announce them.
 * - BreadcrumbList structured data built from the SAME trail, so what a visitor sees and what
 *   a search engine reads always agree. With Yoast SEO active the trail is handed to Yoast
 *   through its wpseo_breadcrumb_links filter instead: Yoast already prints a BreadcrumbList
 *   in its schema graph on every page (measured on toptechtidbits.com, 2026-09-26, reading
 *   Home > post title), and two lists that disagree would be worse than one.
 *
 * WHERE IT GOES
 *
 * On the before_main action in header.php, which fires inside the content area but BEFORE
 * the main landmark. The "Press Enter to skip to content" link therefore moves past the trail
 * the same way it moves past the menu, which is where GOV.UK places its breadcrumbs.
 *
 * FOR DEVELOPERS
 *
 * - braillewright_breadcrumbs_items   filters the finished trail (a list of label/url arrays).
 * - braillewright_breadcrumbs_display filters whether the trail shows on the current page.
 * - braillewright_breadcrumbs_post_term filters the category chosen for a post.
 * - braillewright_breadcrumbs_term_page filters the page that stands for a category (the
 *   Breadcrumb Page field on the category's edit screen, stored as term meta
 *   braillewright_breadcrumb_page).
 * - braillewright_breadcrumbs_schema  filters whether the theme prints its own structured data.
 * - To move the trail, remove braillewright_breadcrumbs_output from before_main and add it to
 *   another action, for example main_top.
 *
 * @package Braillewright
 */

defined( 'ABSPATH' ) || exit;

//----------------------------------------------------------------------------------
//  Settings
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_breadcrumbs_separators' ) ) {
	/**
	 * The separators a site owner can choose from, keyed by the value stored in the theme mod.
	 *
	 * Directional characters are flagged so a right-to-left site can mirror them; a slash or a
	 * pipe must not be mirrored, because a mirrored slash reads as a backslash.
	 *
	 * @return array[] Each entry: 'char', 'label' and 'directional'.
	 */
	function braillewright_breadcrumbs_separators() {
		return array(
			'chevron'        => array(
				'char'        => '›',
				'label'       => __( 'Chevron', 'braillewright' ) . ' ›',
				'directional' => true,
			),
			'slash'          => array(
				'char'        => '/',
				'label'       => __( 'Slash', 'braillewright' ) . ' /',
				'directional' => false,
			),
			'double-chevron' => array(
				'char'        => '»',
				'label'       => __( 'Double Chevron', 'braillewright' ) . ' »',
				'directional' => true,
			),
			'arrow'          => array(
				'char'        => '→',
				'label'       => __( 'Arrow', 'braillewright' ) . ' →',
				'directional' => true,
			),
			'pipe'           => array(
				'char'        => '|',
				'label'       => __( 'Vertical Bar', 'braillewright' ) . ' |',
				'directional' => false,
			),
		);
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_sanitize_separator' ) ) {
	/**
	 * Sanitize the separator setting to one of the known keys.
	 *
	 * @param string $input The submitted value.
	 * @return string A known separator key, or the default.
	 */
	function braillewright_breadcrumbs_sanitize_separator( $input ) {
		return array_key_exists( $input, braillewright_breadcrumbs_separators() ) ? $input : 'chevron';
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_backgrounds' ) ) {
	/**
	 * The background choices, keyed by the value stored in the theme mod.
	 *
	 * ⛔ There is deliberately no "transparent" choice. The trail sits on the lower edge of the
	 * header band, and before 2.0.11 transparent text there wrapped off the band onto the page at
	 * 1.27:1 on a phone. "Same Color as the Header" gives the same look with a background of
	 * its own, so a wrapped line still has the header color behind it.
	 *
	 * @return string[]
	 */
	function braillewright_breadcrumbs_backgrounds() {
		return array(
			'box'    => __( 'A Box Like the Theme\'s Other Boxes', 'braillewright' ),
			'header' => __( 'Same Color as the Header', 'braillewright' ),
			'page'   => __( 'Same Color as the Page Background', 'braillewright' ),
			'custom' => __( 'A Color I Choose', 'braillewright' ),
		);
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_sanitize_background' ) ) {
	/**
	 * Sanitize the background choice to one of the known keys.
	 *
	 * @param string $input The submitted value.
	 * @return string
	 */
	function braillewright_breadcrumbs_sanitize_background( $input ) {
		return array_key_exists( $input, braillewright_breadcrumbs_backgrounds() ) ? $input : 'box';
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_sanitize_font_size' ) ) {
	/**
	 * Sanitize the text size, in pixels, to 12 through 32.
	 *
	 * @param mixed $input The submitted value.
	 * @return int
	 */
	function braillewright_breadcrumbs_sanitize_font_size( $input ) {
		$size = absint( $input );
		return $size ? min( max( $size, 12 ), 32 ) : 16;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_sanitize_spacing' ) ) {
	/**
	 * Sanitize the margin around the trail, in pixels, to 0 through 48.
	 *
	 * @param mixed $input The submitted value.
	 * @return int
	 */
	function braillewright_breadcrumbs_sanitize_spacing( $input ) {
		return min( absint( $input ), 48 );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_sanitize_optional_color' ) ) {
	/**
	 * Sanitize a color that may be left empty, which means "choose automatically".
	 *
	 * @param string $input The submitted value.
	 * @return string A #rrggbb color, or ''.
	 */
	function braillewright_breadcrumbs_sanitize_optional_color( $input ) {
		$color = sanitize_hex_color( $input );
		return $color ? $color : '';
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_custom_background_active' ) ) {
	/**
	 * Show the background color picker only when "A Color I Choose" is selected.
	 *
	 * @return bool
	 */
	function braillewright_breadcrumbs_custom_background_active() {
		return 'custom' === get_theme_mod( 'breadcrumbs_background', 'box' );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_customize_register' ) ) {
	/**
	 * Add the Breadcrumbs section and every breadcrumb setting to the Customizer.
	 *
	 * Everything lives in this one section, colors included, because Aaron asked for "a place
	 * ... where I can customize specifically the font size and the font color" (2026-09-26).
	 * The color pickers start empty, which means "choose a color that contrasts with the
	 * background"; js/breadcrumbs-customizer.js warns inside the Customizer when a chosen color
	 * falls below 4.5:1.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer manager.
	 */
	function braillewright_breadcrumbs_customize_register( $wp_customize ) {
		$section = 'braillewright_breadcrumbs';

		$wp_customize->add_section(
			$section,
			array(
				'title'       => __( 'Breadcrumbs', 'braillewright' ),
				'priority'    => 56,
				'description' => __( 'A trail of links above the content that shows where each page sits in your site, for example Home, then News, then the article. It is never shown on the front page. Every breadcrumb setting is here: the background, the text size and colors, the margin and the separator.', 'braillewright' ),
			)
		);

		$yes_no = array(
			'yes' => __( 'Yes', 'braillewright' ),
			'no'  => __( 'No', 'braillewright' ),
		);

		$wp_customize->add_setting(
			'breadcrumbs',
			array(
				'default'           => 'no',
				'sanitize_callback' => 'braillewright_sanitize_yes_no_settings',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs',
			array(
				'label'    => __( 'Show Breadcrumbs?', 'braillewright' ),
				'section'  => $section,
				'type'     => 'radio',
				'choices'  => $yes_no,
				'priority' => 10,
			)
		);

		$wp_customize->add_setting(
			'breadcrumbs_background',
			array(
				'default'           => 'box',
				'sanitize_callback' => 'braillewright_breadcrumbs_sanitize_background',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_background',
			array(
				'label'       => __( 'Background', 'braillewright' ),
				'description' => __( 'The header and page colors follow whatever you set under Colors.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'radio',
				'choices'     => braillewright_breadcrumbs_backgrounds(),
				'priority'    => 20,
			)
		);

		$wp_customize->add_setting(
			'breadcrumbs_bg_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'breadcrumbs_bg_color',
				array(
					'label'           => __( 'Background Color', 'braillewright' ),
					'section'         => $section,
					'priority'        => 25,
					'active_callback' => 'braillewright_breadcrumbs_custom_background_active',
				)
			)
		);

		$wp_customize->add_setting(
			'breadcrumbs_font_size',
			array(
				'default'           => '16',
				'sanitize_callback' => 'braillewright_breadcrumbs_sanitize_font_size',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_font_size',
			array(
				'label'       => __( 'Text Size, in Pixels', 'braillewright' ),
				'description' => __( 'From 12 to 32. The theme\'s body text is 16.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 12,
					'max'  => 32,
					'step' => 1,
				),
				'priority'    => 30,
			)
		);

		$colors = array(
			'breadcrumbs_text_color'       => array( __( 'Text Color', 'braillewright' ), __( 'The current page and the separators. Leave empty to pick a color that contrasts with the background.', 'braillewright' ), 40 ),
			'breadcrumbs_link_color'       => array( __( 'Link Color', 'braillewright' ), __( 'Leave empty to use your site\'s link color on a light background, or white on a dark one.', 'braillewright' ), 50 ),
			'breadcrumbs_link_hover_color' => array( __( 'Link Color on Hover', 'braillewright' ), __( 'The color a link turns when the mouse is over it. Leave empty to use your site\'s hover color on a light background.', 'braillewright' ), 60 ),
		);
		foreach ( $colors as $id => $control ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => '',
					'sanitize_callback' => 'braillewright_breadcrumbs_sanitize_optional_color',
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
			'breadcrumbs_spacing',
			array(
				'default'           => '12',
				'sanitize_callback' => 'braillewright_breadcrumbs_sanitize_spacing',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_spacing',
			array(
				'label'       => __( 'Margin Around the Breadcrumbs, in Pixels', 'braillewright' ),
				'description' => __( 'The space between the words and the edge of their box, the same on all four sides as you see it, and the space between the box and the menu above it and the content below it.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 48,
					'step' => 1,
				),
				'priority'    => 70,
			)
		);

		$choices = array();
		foreach ( braillewright_breadcrumbs_separators() as $key => $separator ) {
			$choices[ $key ] = $separator['label'];
		}
		$wp_customize->add_setting(
			'breadcrumbs_separator',
			array(
				'default'           => 'chevron',
				'sanitize_callback' => 'braillewright_breadcrumbs_sanitize_separator',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_separator',
			array(
				'label'       => __( 'Separator Between Links', 'braillewright' ),
				'description' => __( 'Screen readers do not announce the separator, whichever you choose.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'radio',
				'choices'     => $choices,
				'priority'    => 80,
			)
		);

		$wp_customize->add_setting(
			'breadcrumbs_home_label',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_home_label',
			array(
				'label'       => __( 'Text of the First Link', 'braillewright' ),
				'description' => __( 'Leave empty to use "Home".', 'braillewright' ),
				'section'     => $section,
				'type'        => 'text',
				'priority'    => 90,
			)
		);

		$wp_customize->add_setting(
			'breadcrumbs_show_current',
			array(
				'default'           => 'yes',
				'sanitize_callback' => 'braillewright_sanitize_yes_no_settings',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_show_current',
			array(
				'label'       => __( 'Show the Current Page\'s Title at the End?', 'braillewright' ),
				'description' => __( 'It is marked as the current page for screen readers. A very long trail is cut off on screen after three lines; screen readers still read all of it.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'radio',
				'choices'     => $yes_no,
				'priority'    => 100,
			)
		);

		$wp_customize->add_setting(
			'breadcrumbs_strip_emoji',
			array(
				'default'           => 'yes',
				'sanitize_callback' => 'braillewright_sanitize_yes_no_settings',
			)
		);
		$wp_customize->add_control(
			'breadcrumbs_strip_emoji',
			array(
				'label'       => __( 'Leave Emoji Out of the Breadcrumbs?', 'braillewright' ),
				'description' => __( 'Screen readers read every emoji aloud, so a link titled "Newsletters" followed by a newspaper emoji is announced as "Newsletters newspaper". Your page titles themselves are not changed.', 'braillewright' ),
				'section'     => $section,
				'type'        => 'radio',
				'choices'     => $yes_no,
				'priority'    => 110,
			)
		);
	}
}
add_action( 'customize_register', 'braillewright_breadcrumbs_customize_register' );

if ( ! function_exists( 'braillewright_breadcrumbs_mods_to_remove' ) ) {
	/**
	 * Include the breadcrumb settings in the theme's "reset Customizer settings" action.
	 *
	 * @param string[] $mods_array Theme mods the reset removes.
	 * @return string[]
	 */
	function braillewright_breadcrumbs_mods_to_remove( $mods_array ) {
		return array_merge(
			$mods_array,
			array(
				'breadcrumbs',
				'breadcrumbs_background',
				'breadcrumbs_bg_color',
				'breadcrumbs_font_size',
				'breadcrumbs_text_color',
				'breadcrumbs_link_color',
				'breadcrumbs_link_hover_color',
				'breadcrumbs_spacing',
				'breadcrumbs_separator',
				'breadcrumbs_home_label',
				'breadcrumbs_show_current',
				'breadcrumbs_strip_emoji',
			)
		);
	}
}
add_filter( 'braillewright_mods_to_remove', 'braillewright_breadcrumbs_mods_to_remove' );

//----------------------------------------------------------------------------------
//  Breadcrumb Page: a page that stands for a category in the trail
//----------------------------------------------------------------------------------

/*
 * Why this exists: on toptechtidbits.com newsletters are filed in a category named "Newsletter",
 * while the page visitors know, the one in the menu with the year archives under it, is a page
 * named "Newsletters". The trail took its middle link from the category, so it read "Home >
 * Newsletter > issue" and led to the bare category list (Aaron, 2026-09-27: "It does not say
 * newsletters, and the name of the page is newsletters with an S"). The same site has three
 * more categories whose section page is a separate page in the menu: JAWS Power Tip, News and
 * Publisher Updates. A site owner picks the page on the category's own edit screen; nothing
 * changes until they do.
 */

if ( ! function_exists( 'braillewright_breadcrumbs_page_taxonomies' ) ) {
	/**
	 * The taxonomies whose terms can be given a Breadcrumb Page: every public hierarchical one,
	 * because those are the ones braillewright_breadcrumbs_post_term() puts in a post's trail.
	 *
	 * @return string[]
	 */
	function braillewright_breadcrumbs_page_taxonomies() {
		return array_values(
			get_taxonomies(
				array(
					'public'       => true,
					'hierarchical' => true,
				)
			)
		);
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_term_page' ) ) {
	/**
	 * The published page chosen to stand for a term in the trail, if any.
	 *
	 * @param WP_Term $term The term.
	 * @return WP_Post|null
	 */
	function braillewright_breadcrumbs_term_page( $term ) {
		$page_id = absint( get_term_meta( $term->term_id, 'braillewright_breadcrumb_page', true ) );
		$page    = $page_id ? get_post( $page_id ) : null;
		if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
			$page = null;
		}
		$page = apply_filters( 'braillewright_breadcrumbs_term_page', $page, $term );

		return ( $page instanceof WP_Post ) ? $page : null;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_term_page_field' ) ) {
	/**
	 * The Breadcrumb Page field on a category's edit screen.
	 *
	 * @param WP_Term $term The term being edited.
	 */
	function braillewright_breadcrumbs_term_page_field( $term ) {
		?>
		<tr class="form-field term-braillewright-breadcrumb-page-wrap">
			<th scope="row"><label for="braillewright-breadcrumb-page"><?php esc_html_e( 'Breadcrumb Page', 'braillewright' ); ?></label></th>
			<td>
				<?php
				wp_nonce_field( 'braillewright_breadcrumb_page', 'braillewright_breadcrumb_page_nonce' );
				wp_dropdown_pages(
					array(
						'name'              => 'braillewright_breadcrumb_page',
						'id'                => 'braillewright-breadcrumb-page',
						'selected'          => absint( get_term_meta( $term->term_id, 'braillewright_breadcrumb_page', true ) ),
						'show_option_none'  => esc_html__( 'None: show the category itself', 'braillewright' ),
						'option_none_value' => '0',
					)
				);
				?>
				<p class="description"><?php esc_html_e( 'When breadcrumbs are switched on, posts in this category show this page in their trail, linked, instead of the category. Choose it when a page, not the list of posts, is where visitors go for this section: for example a Newsletters page for a Newsletter category.', 'braillewright' ); ?></p>
			</td>
		</tr>
		<?php
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_save_term_page' ) ) {
	/**
	 * Save the Breadcrumb Page chosen on a category's edit screen.
	 *
	 * @param int $term_id The term being saved.
	 */
	function braillewright_breadcrumbs_save_term_page( $term_id ) {
		if ( ! isset( $_POST['braillewright_breadcrumb_page_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['braillewright_breadcrumb_page_nonce'] ) ), 'braillewright_breadcrumb_page' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}
		$page_id = isset( $_POST['braillewright_breadcrumb_page'] ) ? absint( wp_unslash( $_POST['braillewright_breadcrumb_page'] ) ) : 0;
		if ( $page_id && 'page' === get_post_type( $page_id ) ) {
			update_term_meta( $term_id, 'braillewright_breadcrumb_page', $page_id );
		} else {
			delete_term_meta( $term_id, 'braillewright_breadcrumb_page' );
		}
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_term_page_hooks' ) ) {
	/**
	 * Put the field on the edit screen of every taxonomy that can use it. On admin_init, after
	 * plugins have registered their taxonomies on init.
	 */
	function braillewright_breadcrumbs_term_page_hooks() {
		foreach ( braillewright_breadcrumbs_page_taxonomies() as $taxonomy ) {
			add_action( "{$taxonomy}_edit_form_fields", 'braillewright_breadcrumbs_term_page_field' );
			add_action( "edited_{$taxonomy}", 'braillewright_breadcrumbs_save_term_page' );
		}
	}
}
add_action( 'admin_init', 'braillewright_breadcrumbs_term_page_hooks' );

//----------------------------------------------------------------------------------
//  Colors, size and spacing
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_breadcrumbs_luminance' ) ) {
	/**
	 * WCAG 2.x relative luminance of a #rgb or #rrggbb color.
	 *
	 * @param string $hex The color.
	 * @return float 0 (black) to 1 (white).
	 */
	function braillewright_breadcrumbs_luminance( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
			return 0.0;
		}
		$channels = array();
		foreach ( array( 0, 2, 4 ) as $offset ) {
			$value      = hexdec( substr( $hex, $offset, 2 ) ) / 255;
			$channels[] = ( $value <= 0.03928 ) ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
		}
		return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_contrast' ) ) {
	/**
	 * WCAG 2.x contrast ratio between two colors.
	 *
	 * @param string $a A color.
	 * @param string $b Another color.
	 * @return float 1 to 21.
	 */
	function braillewright_breadcrumbs_contrast( $a, $b ) {
		$la = braillewright_breadcrumbs_luminance( $a );
		$lb = braillewright_breadcrumbs_luminance( $b );
		return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_menu_gap' ) ) {
	/**
	 * The space the menu's own last line already leaves under its text, in pixels.
	 *
	 * The menu links are Roboto with line-height 1.715 (style.css) and 2px of bottom padding.
	 * Roboto's letters fill about 1.172 of their font size, so the line leaves
	 * (1.715 - 1.172) / 2 of the font size below the letters, plus the padding. Measured on TTT
	 * staging on 2026-09-26 with the 32px desktop menu: 11px between the text and the bottom of
	 * the menu; this gives 10.7. The margin under the menu is reduced by this much, so the space
	 * a reader SEES above the trail equals the space below it.
	 *
	 * @param int $font_size The menu's font size in pixels.
	 * @return int
	 */
	function braillewright_breadcrumbs_menu_gap( $font_size ) {
		return (int) round( ( 1.715 - 1.172 ) / 2 * $font_size + 2 );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_style' ) ) {
	/**
	 * The resolved look of the trail: background, text size, colors and spacing.
	 *
	 * An empty color setting means "automatic": the text is #333333 when that reaches 4.5:1 on
	 * the background and white otherwise; links use the site's link color on a light
	 * background when it reaches 4.5:1, white on a dark one; hover uses the site's hover color,
	 * or a light gray on a dark background. A color the site owner chose is always used as
	 * chosen: the Customizer warns about low contrast, the front end never overrides a choice.
	 *
	 * @return array
	 */
	function braillewright_breadcrumbs_style() {
		$choice = braillewright_breadcrumbs_sanitize_background( get_theme_mod( 'breadcrumbs_background', 'box' ) );

		if ( 'header' === $choice ) {
			$background = get_theme_mod( 'colors_header_bg', '#333333' );
		} elseif ( 'page' === $choice ) {
			$background = get_theme_mod( 'colors_base_background', '#ededed' );
		} elseif ( 'custom' === $choice ) {
			$background = get_theme_mod( 'breadcrumbs_bg_color', '#ffffff' );
		} else {
			$background = get_theme_mod( 'colors_base_content_bg', '#ffffff' );
		}
		$background = sanitize_hex_color( $background );
		if ( ! $background ) {
			$background = '#ffffff';
		}

		$dark      = '#333333';
		$light     = '#ffffff';
		$auto_text = $dark;
		if ( braillewright_breadcrumbs_contrast( $dark, $background ) < 4.5 && braillewright_breadcrumbs_contrast( $light, $background ) > braillewright_breadcrumbs_contrast( $dark, $background ) ) {
			$auto_text = $light;
		}
		$light_background = ( $dark === $auto_text );

		if ( $light_background ) {
			$site_link  = sanitize_hex_color( get_theme_mod( 'colors_base_links', '#333333' ) );
			$site_hover = sanitize_hex_color( get_theme_mod( 'colors_base_links_hover', '#757575' ) );
			$auto_link  = ( $site_link && braillewright_breadcrumbs_contrast( $site_link, $background ) >= 4.5 ) ? $site_link : $auto_text;
			$auto_hover = ( $site_hover && braillewright_breadcrumbs_contrast( $site_hover, $background ) >= 4.5 ) ? $site_hover : $auto_link;
		} else {
			$auto_link  = $light;
			$auto_hover = ( braillewright_breadcrumbs_contrast( '#d4d4d4', $background ) >= 4.5 ) ? '#d4d4d4' : $light;
		}

		$text  = braillewright_breadcrumbs_sanitize_optional_color( get_theme_mod( 'breadcrumbs_text_color', '' ) );
		$link  = braillewright_breadcrumbs_sanitize_optional_color( get_theme_mod( 'breadcrumbs_link_color', '' ) );
		$hover = braillewright_breadcrumbs_sanitize_optional_color( get_theme_mod( 'breadcrumbs_link_hover_color', '' ) );

		$spacing = braillewright_breadcrumbs_sanitize_spacing( get_theme_mod( 'breadcrumbs_spacing', 12 ) );
		$tablet  = absint( get_theme_mod( 'menu_primary_font_size_tablet', 14 ) );
		$desktop = absint( get_theme_mod( 'menu_primary_font_size_desktop', 14 ) );

		return array(
			'background'          => $background,
			'box'                 => ( 'box' === $choice ),
			'text'                => '' !== $text ? $text : $auto_text,
			'link'                => '' !== $link ? $link : $auto_link,
			'hover'               => '' !== $hover ? $hover : $auto_hover,
			'font_size'           => braillewright_breadcrumbs_sanitize_font_size( get_theme_mod( 'breadcrumbs_font_size', 16 ) ),
			'spacing'             => $spacing,
			'menu_margin_tablet'  => max( 0, $spacing - braillewright_breadcrumbs_menu_gap( $tablet ? $tablet : 14 ) ),
			'menu_margin_desktop' => max( 0, $spacing - braillewright_breadcrumbs_menu_gap( $desktop ? $desktop : 14 ) ),
		);
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_css' ) ) {
	/**
	 * The CSS for the resolved look, including the equal space above and below.
	 *
	 * The margin setting sets the bar's own padding, the same on all four sides (Aaron,
	 * 2026-09-26: "can we make sure that the top margin, left margin, right margin ... are
	 * equal?" - the sides were 1.5em, 48px at 32px), as well as the gaps outside
	 * it. Equal padding is not equal SPACE: each line of text is 1.5 times the text size tall,
	 * so the line itself adds room above and below the letters. Measured on TTT staging at 32px
	 * with a 10px setting: 22px above the capital letters, 23px below the baseline, 10px at the
	 * sides (Aaron, asking a second time: "the left margin and the right margin are much shorter
	 * than the top margin and the bottom margin"). style.css pulls the list up and down into
	 * that extra line space (.breadcrumbs-list, margin-block), so all four look the same. It used to set only the gaps, while the padding stayed 0.625em, so at 32px there were
	 * 20px above and below the words that the setting could not change (Aaron, 2026-09-26: "the
	 * space above and below in pixels does not seem to change anything once I've changed the
	 * text to 32 pixels"). On the header-color background the bar blends into the header, so
	 * that padding IS the space a reader sees.
	 *
	 * Space ABOVE the trail comes from the header, not the trail: 30px under the menu button on
	 * a phone (.toggle-navigation margin 1.875em) and 30px under the menu from 900px up
	 * (.menu-primary-container margin 1.875em). Measured on TTT staging on 2026-09-26: 40px
	 * visible above against 12px below on a desktop, and 30px against 12px on a phone. Both
	 * are set here, only on pages that print a trail (body class has-breadcrumbs). The menu's
	 * font size is set separately for 800-999px and 1000px and up, and the menu sits flat from
	 * 900px, so there are two desktop rules.
	 *
	 * @return string
	 */
	function braillewright_breadcrumbs_css() {
		$s   = braillewright_breadcrumbs_style();
		$css = '.breadcrumbs,#breadcrumbs{font-size:' . $s['font_size'] . 'px;background:' . $s['background'] . ';color:' . $s['text'] . ';padding:' . $s['spacing'] . 'px;margin-bottom:' . $s['spacing'] . 'px;' . ( $s['box'] ? '' : 'box-shadow:none;' ) . '}';

		$css .= '.breadcrumbs a,.breadcrumbs a:link,.breadcrumbs a:visited,#breadcrumbs a,#breadcrumbs a:link,#breadcrumbs a:visited{color:' . $s['link'] . ';}';
		$css .= '.breadcrumbs a:hover,.breadcrumbs a:active,.breadcrumbs a:focus,#breadcrumbs a:hover,#breadcrumbs a:active,#breadcrumbs a:focus{color:' . $s['hover'] . ';}';
		$css .= '.has-breadcrumbs .toggle-navigation{margin-bottom:' . $s['spacing'] . 'px;}';
		$css .= '@media all and (min-width:900px) and (max-width:999px){.has-breadcrumbs .menu-primary-container{margin-bottom:' . $s['menu_margin_tablet'] . 'px;}}';
		$css .= '@media all and (min-width:1000px){.has-breadcrumbs .menu-primary-container{margin-bottom:' . $s['menu_margin_desktop'] . 'px;}}';

		return $css;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_inline_css' ) ) {
	/**
	 * Attach the breadcrumb CSS after the Customizer colors (priority 99), so it wins.
	 */
	function braillewright_breadcrumbs_inline_css() {
		if ( braillewright_breadcrumbs_enabled() ) {
			wp_add_inline_style( braillewright_customizer_style_handle(), braillewright_sanitize_css( braillewright_breadcrumbs_css() ) );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'braillewright_breadcrumbs_inline_css', 100 );

if ( ! function_exists( 'braillewright_breadcrumbs_body_class' ) ) {
	/**
	 * Add has-breadcrumbs to the body on pages that print a trail, for the spacing rules.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	function braillewright_breadcrumbs_body_class( $classes ) {
		if ( braillewright_breadcrumbs_should_display() && braillewright_breadcrumbs_get_items() ) {
			$classes[] = 'has-breadcrumbs';
		}
		return $classes;
	}
}
add_filter( 'body_class', 'braillewright_breadcrumbs_body_class' );

if ( ! function_exists( 'braillewright_breadcrumbs_customizer_scripts' ) ) {
	/**
	 * The contrast warnings shown beside the breadcrumb color pickers.
	 */
	function braillewright_breadcrumbs_customizer_scripts() {
		wp_enqueue_script( 'braillewright-breadcrumbs-customizer', get_template_directory_uri() . '/js/breadcrumbs-customizer.js', array( 'customize-controls' ), braillewright_asset_version( get_template_directory() . '/js/breadcrumbs-customizer.js' ), true );
		wp_localize_script(
			'braillewright-breadcrumbs-customizer',
			'braillewrightBreadcrumbs',
			array(
				/* translators: %s: a contrast ratio such as 3.2. */
				'warning' => __( 'This color measures %s to 1 against the breadcrumb background. Text needs at least 4.5 to 1.', 'braillewright' ),
			)
		);
	}
}
add_action( 'customize_controls_enqueue_scripts', 'braillewright_breadcrumbs_customizer_scripts' );

//----------------------------------------------------------------------------------
//  When to show
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_breadcrumbs_enabled' ) ) {
	/**
	 * Whether the site owner has switched breadcrumbs on.
	 *
	 * @return bool
	 */
	function braillewright_breadcrumbs_enabled() {
		return 'yes' === get_theme_mod( 'breadcrumbs', 'no' );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_should_display' ) ) {
	/**
	 * Whether the trail belongs on the current page.
	 *
	 * Never on the first page of a STATIC front page (Settings, Reading, "A static page"), where it
	 * could only ever say "Home" (Yoast SEO prints exactly that, as one unlinked word, measured on
	 * TTT staging). When the front page is the list of latest posts it shows "Home > Blog"
	 * (Aaron, 2026-09-27: "when your blog is the main page, you still have a breadcrumb at the top
	 * that says 'Home > Blog'"). Never on the landing-page templates, which are standalone pages
	 * by design, and never on bbPress pages, which print their own trail.
	 *
	 * @return bool
	 */
	function braillewright_breadcrumbs_should_display() {
		$display = braillewright_breadcrumbs_enabled();

		if ( $display ) {
			// Only on a page being viewed. An admin screen, an AJAX call or a REST request (Yoast's
			// "head" endpoint builds schema for any URL through REST) has no trail of its own.
			if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				$display = false;
			} elseif ( is_feed() || is_embed() ) {
				$display = false;
			} elseif ( is_front_page() && ! is_home() && ! is_paged() && (int) get_query_var( 'page' ) < 2 ) {
				$display = false;
			} elseif ( is_page_template( array( 'templates/landing-page.php', 'templates/landing-page-header.php' ) ) ) {
				$display = false;
			} elseif ( function_exists( 'is_bbpress' ) && is_bbpress() ) {
				$display = false;
			}
		}

		return (bool) apply_filters( 'braillewright_breadcrumbs_display', $display );
	}
}

//----------------------------------------------------------------------------------
//  Building the trail
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_breadcrumbs_label' ) ) {
	/**
	 * Turn a title into plain breadcrumb text.
	 *
	 * Titles arrive with HTML entities (get_the_title() texturizes a dash into &#8211;) and can
	 * carry tags. Both are decoded to plain text here and escaped once, at output.
	 *
	 * Emoji are left out when the site owner asks; see braillewright_breadcrumbs_emoji_pattern().
	 *
	 * @param string $text A title or label.
	 * @return string Plain text, never empty unless the input was.
	 */
	function braillewright_breadcrumbs_label( $text ) {
		$text  = wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES, get_bloginfo( 'charset' ) ) );
		$plain = trim( (string) preg_replace( '/\s+/u', ' ', $text ) );

		if ( 'yes' === get_theme_mod( 'breadcrumbs_strip_emoji', 'yes' ) ) {
			$stripped = preg_replace( braillewright_breadcrumbs_emoji_pattern(), '', $plain );
			// Keep the title when removing emoji would leave nothing, or when the text is not
			// valid UTF-8 (preg_replace then returns null).
			if ( is_string( $stripped ) ) {
				$stripped = trim( (string) preg_replace( '/\s+/u', ' ', $stripped ) );
				if ( '' !== $stripped ) {
					$plain = $stripped;
				}
			}
		}

		return $plain;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_emoji_pattern' ) ) {
	/**
	 * The regular expression that finds emoji in a breadcrumb label.
	 *
	 * Written as plain code-point ranges, NOT Unicode properties such as \p{Emoji_Presentation}:
	 * PCRE2 only learned those in 10.40, and on an older build the pattern fails to compile and
	 * PHP prints a warning on every page. Ranges compile everywhere.
	 *
	 * - The Basic Multilingual Plane list is every character PCRE2 10.42 (Unicode 15) classes as
	 *   Emoji_Presentation below U+10000, generated by testing each code point on TTT staging on
	 *   2026-09-26, not typed from memory. No emoji has been added there in years.
	 * - U+1F000 to U+1FAFF is taken whole rather than character by character, so emoji added
	 *   to Unicode after 15 are caught too. Those blocks hold only symbols and pictographs.
	 * - A symbol followed by U+FE0F, the "show this as emoji" selector, goes with it. That is
	 *   how a heart or a keyboard becomes an emoji.
	 * - Joiners, selectors, the keycap mark and flag tag characters go on their own.
	 *
	 * ⚠️ The property Extended_Pictographic is deliberately not used: tried on the same server,
	 * it also removed the copyright and trademark signs, turning "(c) 2026 TM" into "2026".
	 * Here a copyright sign stays unless it is followed by U+FE0F.
	 *
	 * @return string
	 */
	function braillewright_breadcrumbs_emoji_pattern() {
		$bmp = '\x{231A}-\x{231B}\x{23E9}-\x{23EC}\x{23F0}\x{23F3}\x{25FD}-\x{25FE}\x{2614}-\x{2615}\x{2648}-\x{2653}\x{267F}\x{2693}\x{26A1}\x{26AA}-\x{26AB}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26CE}\x{26D4}\x{26EA}\x{26F2}-\x{26F3}\x{26F5}\x{26FA}\x{26FD}\x{2705}\x{270A}-\x{270B}\x{2728}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2795}-\x{2797}\x{27B0}\x{27BF}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}';

		return '/[^\p{L}\p{N}\s](?=\x{FE0F})|[' . $bmp . '\x{1F000}-\x{1FAFF}\x{E0020}-\x{E007F}\x{200D}\x{FE0E}\x{FE0F}\x{20E3}]/u';
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_item' ) ) {
	/**
	 * One step of the trail.
	 *
	 * @param string $label The text.
	 * @param string $url   The link, or '' for the current page.
	 * @return array
	 */
	function braillewright_breadcrumbs_item( $label, $url = '' ) {
		return array(
			'label' => braillewright_breadcrumbs_label( $label ),
			'url'   => (string) $url,
		);
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_blog_item' ) ) {
	/**
	 * The blog page, when the site shows a static front page and a separate posts page.
	 *
	 * On a site whose front page IS the blog, "Home" already means the blog, so this returns
	 * nothing and the trail does not repeat itself.
	 *
	 * @return array[] Zero or one items.
	 */
	function braillewright_breadcrumbs_blog_item() {
		$page_for_posts = (int) get_option( 'page_for_posts' );

		if ( 'page' !== get_option( 'show_on_front' ) || ! $page_for_posts || 'publish' !== get_post_status( $page_for_posts ) ) {
			return array();
		}

		return array( braillewright_breadcrumbs_item( get_the_title( $page_for_posts ), get_permalink( $page_for_posts ) ) );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_page_items' ) ) {
	/**
	 * A page and its published parent pages, top-most first, every one a link. The front page is
	 * left out: "Home" already stands for it.
	 *
	 * @param WP_Post $page The page.
	 * @return array[]
	 */
	function braillewright_breadcrumbs_page_items( $page ) {
		$items = array();
		$front = (int) get_option( 'page_on_front' );

		foreach ( array_reverse( get_post_ancestors( $page ) ) as $ancestor_id ) {
			if ( (int) $ancestor_id !== $front && 'publish' === get_post_status( $ancestor_id ) ) {
				$items[] = braillewright_breadcrumbs_item( get_the_title( $ancestor_id ), get_permalink( $ancestor_id ) );
			}
		}
		if ( (int) $page->ID !== $front ) {
			$items[] = braillewright_breadcrumbs_item( get_the_title( $page ), get_permalink( $page ) );
		}

		return $items;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_term_chain' ) ) {
	/**
	 * A term's parents and then the term itself, top-most first.
	 *
	 * @param WP_Term $term The term.
	 * @return WP_Term[]
	 */
	function braillewright_breadcrumbs_term_chain( $term ) {
		$chain = array();
		foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $term->taxonomy );
			if ( $ancestor instanceof WP_Term ) {
				$chain[] = $ancestor;
			}
		}
		$chain[] = $term;

		return $chain;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_chain_page' ) ) {
	/**
	 * The deepest term in a chain that has a Breadcrumb Page, and that page.
	 *
	 * That page then stands for the term AND every term above it: it has its own place in the site,
	 * given by its own parent pages. The term that IS the current page (a category archive) is
	 * never replaced, only the terms above it.
	 *
	 * @param WP_Term[] $chain       Terms, top-most first.
	 * @param bool      $link_itself Whether the last term is a link (false on its own archive).
	 * @return array|null array( index in the chain, WP_Post ), or null.
	 */
	function braillewright_breadcrumbs_chain_page( $chain, $link_itself = true ) {
		for ( $i = count( $chain ) - ( $link_itself ? 1 : 2 ); $i >= 0; $i-- ) {
			$page = braillewright_breadcrumbs_term_page( $chain[ $i ] );
			if ( $page ) {
				return array( $i, $page );
			}
		}

		return null;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_term_items' ) ) {
	/**
	 * A term and its parents, top-most first, with any Breadcrumb Page standing in for them.
	 *
	 * @param WP_Term $term        The term.
	 * @param bool    $link_itself Whether the term itself is a link (false when it IS the current page).
	 * @return array[]
	 */
	function braillewright_breadcrumbs_term_items( $term, $link_itself = true ) {
		$chain  = braillewright_breadcrumbs_term_chain( $term );
		$last   = count( $chain ) - 1;
		$mapped = braillewright_breadcrumbs_chain_page( $chain, $link_itself );
		$items  = $mapped ? braillewright_breadcrumbs_page_items( $mapped[1] ) : array();
		$start  = $mapped ? $mapped[0] + 1 : 0;

		for ( $i = $start; $i <= $last; $i++ ) {
			if ( $i < $last ) {
				$link = get_term_link( $chain[ $i ] );
				if ( ! is_wp_error( $link ) ) {
					$items[] = braillewright_breadcrumbs_item( $chain[ $i ]->name, $link );
				}
			} else {
				$link    = $link_itself ? get_term_link( $term ) : '';
				$items[] = braillewright_breadcrumbs_item( $term->name, is_wp_error( $link ) ? '' : $link );
			}
		}

		return $items;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_post_term' ) ) {
	/**
	 * The term a post is filed under, for the middle of its trail.
	 *
	 * 1. The main category the author picked, when an SEO plugin stored one. Yoast SEO and
	 *    Rank Math both keep it in post meta, which is read directly, so neither plugin is
	 *    needed for the trail to work: without one, step 2 decides. Measured on TTT staging on
	 *    2026-09-26: all 107 posts filed in two categories have "news" as that choice.
	 * 2. Otherwise, the deepest category (a child category puts its parents in the trail too),
	 *    then the one holding the most posts, then alphabetical order. On those same 107 posts,
	 *    "fewest posts" picked the other category every time, which is why it is "most".
	 * 3. The default category ("Uncategorized") only when it is the post's only category, and
	 *    then not at all: "Uncategorized" is not a place anyone navigates to.
	 *
	 * For other post types, the first public hierarchical taxonomy with terms on the post is
	 * used, for example WooCommerce product categories.
	 *
	 * @param WP_Post $post The post.
	 * @return WP_Term|null
	 */
	function braillewright_breadcrumbs_post_term( $post ) {
		$taxonomy = '';
		if ( 'post' === $post->post_type ) {
			$taxonomy = 'category';
		} else {
			foreach ( get_object_taxonomies( $post->post_type, 'objects' ) as $candidate ) {
				if ( $candidate->public && $candidate->hierarchical && 'post_format' !== $candidate->name ) {
					$candidate_terms = get_the_terms( $post, $candidate->name );
					if ( ! empty( $candidate_terms ) && ! is_wp_error( $candidate_terms ) ) {
						$taxonomy = $candidate->name;
						break;
					}
				}
			}
		}

		$chosen  = null;
		$terms   = $taxonomy ? get_the_terms( $post, $taxonomy ) : false;
		$default = ( 'category' === $taxonomy ) ? (int) get_option( 'default_category' ) : 0;

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$by_id = array();
			foreach ( $terms as $term ) {
				$by_id[ (int) $term->term_id ] = $term;
			}

			// A stored main category that is the default category is skipped too, so a post filed
			// only under "Uncategorized" never shows it, whichever plugin stored the choice.
			foreach ( array( '_yoast_wpseo_primary_' . $taxonomy, 'rank_math_primary_' . $taxonomy ) as $meta_key ) {
				$primary = (int) get_post_meta( $post->ID, $meta_key, true );
				if ( $primary && $primary !== $default && isset( $by_id[ $primary ] ) ) {
					$chosen = $by_id[ $primary ];
					break;
				}
			}

			if ( null === $chosen ) {
				$pool = array();
				foreach ( $by_id as $term_id => $term ) {
					if ( $term_id !== $default ) {
						$pool[] = array(
							'term'  => $term,
							'depth' => count( get_ancestors( $term_id, $taxonomy, 'taxonomy' ) ),
						);
					}
				}
				usort(
					$pool,
					function ( $a, $b ) {
						if ( $a['depth'] !== $b['depth'] ) {
							return $b['depth'] - $a['depth'];
						}
						if ( $a['term']->count !== $b['term']->count ) {
							return $b['term']->count - $a['term']->count;
						}
						return strcmp( $a['term']->name, $b['term']->name );
					}
				);
				$chosen = $pool ? $pool[0]['term'] : null;
			}
		}

		$chosen = apply_filters( 'braillewright_breadcrumbs_post_term', $chosen, $post, $taxonomy );

		return ( $chosen instanceof WP_Term ) ? $chosen : null;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_singular_items' ) ) {
	/**
	 * Everything between "Home" and the title of a single post, page or attachment.
	 *
	 * @param WP_Post $post The post.
	 * @return array[]
	 */
	function braillewright_breadcrumbs_singular_items( $post ) {
		$items = array();

		// An attachment sits under the post it was uploaded to.
		if ( 'attachment' === $post->post_type ) {
			$parent = $post->post_parent ? get_post( $post->post_parent ) : null;
			if ( $parent instanceof WP_Post && 'publish' === $parent->post_status ) {
				$items   = braillewright_breadcrumbs_singular_items( $parent );
				$items[] = braillewright_breadcrumbs_item( get_the_title( $parent ), get_permalink( $parent ) );
			}
			return $items;
		}

		$term = is_post_type_hierarchical( $post->post_type ) ? null : braillewright_breadcrumbs_post_term( $post );

		if ( 'post' === $post->post_type ) {
			// Not when the post's category has a Breadcrumb Page: that page's own parents say where
			// it sits, and "Home > Blog > Newsletters" would invent a place the site does not have.
			if ( ! $term || ! braillewright_breadcrumbs_chain_page( braillewright_breadcrumbs_term_chain( $term ) ) ) {
				$items = braillewright_breadcrumbs_blog_item();
			}
		} else {
			// A post type with its own archive, such as WooCommerce products and the Shop page.
			$archive     = get_post_type_archive_link( $post->post_type );
			$type_object = get_post_type_object( $post->post_type );
			if ( $archive && $type_object && untrailingslashit( $archive ) !== untrailingslashit( home_url() ) ) {
				$items[] = braillewright_breadcrumbs_item( $type_object->labels->name, $archive );
			}
		}

		if ( is_post_type_hierarchical( $post->post_type ) ) {
			$front = (int) get_option( 'page_on_front' );
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor_id ) {
				// "Home" already stands for the front page; do not list it twice.
				if ( (int) $ancestor_id !== $front && 'publish' === get_post_status( $ancestor_id ) ) {
					$items[] = braillewright_breadcrumbs_item( get_the_title( $ancestor_id ), get_permalink( $ancestor_id ) );
				}
			}
		} elseif ( $term ) {
			$items = array_merge( $items, braillewright_breadcrumbs_term_items( $term ) );
		}

		return $items;
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_page_number_item' ) ) {
	/**
	 * "Page 2" and so on, for the later pages of an archive or of a split post.
	 *
	 * @param int $number The page number.
	 * @return array
	 */
	function braillewright_breadcrumbs_page_number_item( $number ) {
		/* translators: %s: page number. */
		return braillewright_breadcrumbs_item( sprintf( __( 'Page %s', 'braillewright' ), number_format_i18n( $number ) ) );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_get_items' ) ) {
	/**
	 * The trail for the current page, "Home" first and the current page last.
	 *
	 * Every item is an array with 'label' (plain text) and 'url' ('' for the current page).
	 * Built once per request: the structured data in the head and the list in the page read
	 * the same result, so they cannot disagree.
	 *
	 * @return array[] An empty array when there is no trail to show.
	 */
	function braillewright_breadcrumbs_get_items() {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$home_label = trim( (string) get_theme_mod( 'breadcrumbs_home_label', '' ) );
		$items      = array( braillewright_breadcrumbs_item( '' !== $home_label ? $home_label : __( 'Home', 'braillewright' ), home_url( '/' ) ) );
		$paged      = max( (int) get_query_var( 'paged' ), 1 );
		$current    = array();

		if ( is_front_page() && is_home() ) {
			// The front page IS the list of latest posts: "Home > Blog", and on its later pages
			// "Home > Blog > Page 2" with Blog linking back to the first page, the same shape as a
			// separate blog page. "Blog" can be renamed with the braillewright_breadcrumbs_front_blog_label filter.
			$blog = braillewright_breadcrumbs_item( apply_filters( 'braillewright_breadcrumbs_front_blog_label', __( 'Blog', 'braillewright' ) ) );
			if ( $paged > 1 ) {
				$blog['url'] = get_pagenum_link( 1, false );
				$current     = array( $blog, braillewright_breadcrumbs_page_number_item( $paged ) );
			} else {
				$current = array( $blog );
			}
		} elseif ( is_front_page() ) {
			// A static front page: reached only on page 2 and later; see should_display().
			$current = array( braillewright_breadcrumbs_page_number_item( max( $paged, (int) get_query_var( 'page' ) ) ) );
		} elseif ( is_home() ) {
			// The blog page IS the current page here, so it must not link to itself. Measured on
			// the first test run on TTT staging, which listed it as a link.
			$current = braillewright_breadcrumbs_blog_item();
			if ( $current ) {
				$current[0]['url'] = '';
			}
		} elseif ( is_singular() ) {
			$queried = get_queried_object();
			if ( $queried instanceof WP_Post ) {
				$items   = array_merge( $items, braillewright_breadcrumbs_singular_items( $queried ) );
				$current = array( braillewright_breadcrumbs_item( get_the_title( $queried ) ) );
				$page    = (int) get_query_var( 'page' );
				if ( $page > 1 ) {
					$current   = array( braillewright_breadcrumbs_item( get_the_title( $queried ), get_permalink( $queried ) ) );
					$current[] = braillewright_breadcrumbs_page_number_item( $page );
				}
			}
		} elseif ( is_search() ) {
			/* translators: %s: the words the visitor searched for. */
			$current = array( braillewright_breadcrumbs_item( sprintf( __( 'Search results for "%s"', 'braillewright' ), wp_trim_words( get_search_query( false ), 10 ) ) ) );
		} elseif ( is_404() ) {
			$current = array( braillewright_breadcrumbs_item( __( 'Page not found', 'braillewright' ) ) );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$taxonomy = get_taxonomy( $term->taxonomy );
				$mapped   = ! is_tag() && braillewright_breadcrumbs_chain_page( braillewright_breadcrumbs_term_chain( $term ), false );
				if ( $taxonomy && in_array( 'post', (array) $taxonomy->object_type, true ) && ! $mapped ) {
					$items = array_merge( $items, braillewright_breadcrumbs_blog_item() );
				}
				if ( is_tag() ) {
					/* translators: %s: tag name. */
					$term_items = array( braillewright_breadcrumbs_item( sprintf( __( 'Tag: %s', 'braillewright' ), $term->name ) ) );
				} else {
					$term_items = braillewright_breadcrumbs_term_items( $term, false );
				}
				$current = array( array_pop( $term_items ) );
				$items   = array_merge( $items, $term_items );
			}
		} elseif ( is_author() ) {
			$items  = array_merge( $items, braillewright_breadcrumbs_blog_item() );
			$author = get_queried_object();
			if ( $author instanceof WP_User ) {
				/* translators: %s: author name. */
				$current = array( braillewright_breadcrumbs_item( sprintf( __( 'Author: %s', 'braillewright' ), $author->display_name ) ) );
			}
		} elseif ( is_date() ) {
			$items = array_merge( $items, braillewright_breadcrumbs_blog_item() );
			$year  = (int) get_query_var( 'year' );
			$month = (int) get_query_var( 'monthnum' );
			$day   = (int) get_query_var( 'day' );
			if ( ! $year && get_query_var( 'm' ) ) {
				$m     = (string) get_query_var( 'm' );
				$year  = (int) substr( $m, 0, 4 );
				$month = (int) substr( $m, 4, 2 );
				$day   = (int) substr( $m, 6, 2 );
			}
			if ( $year ) {
				$parts = array( braillewright_breadcrumbs_item( (string) $year, get_year_link( $year ) ) );
				if ( $month ) {
					$parts[] = braillewright_breadcrumbs_item( date_i18n( 'F', mktime( 0, 0, 0, $month, 1, $year ) ), get_month_link( $year, $month ) );
					if ( $day ) {
						$parts[] = braillewright_breadcrumbs_item( number_format_i18n( $day ), get_day_link( $year, $month, $day ) );
					}
				}
				$last        = array_pop( $parts );
				$last['url'] = '';
				$current     = array( $last );
				$items       = array_merge( $items, $parts );
			}
		} elseif ( is_post_type_archive() ) {
			$current = array( braillewright_breadcrumbs_item( post_type_archive_title( '', false ) ) );
		}

		// On page 2 and later of a listing, the listing itself becomes a link back to its first
		// page, and "Page N" is the current page.
		// get_pagenum_link() escapes by default; the unescaped form is asked for because the URL is
		// escaped once at output, and escaping twice turns "&" into "&#038;#038;".
		if ( $paged > 1 && ! is_front_page() && ! is_singular() && ! empty( $current ) ) {
			$listing        = array_pop( $current );
			$listing['url'] = get_pagenum_link( 1, false );
			$current[]      = $listing;
			$current[]      = braillewright_breadcrumbs_page_number_item( $paged );
		}

		$items = array_merge( $items, $current );

		// Anything unrecognised (a plugin's own page type, say) gets no trail rather than a
		// lone "Home" that goes nowhere.
		if ( count( $items ) < 2 ) {
			$items = array();
		}

		$items = apply_filters( 'braillewright_breadcrumbs_items', $items );
		$cache = is_array( $items ) ? array_values( $items ) : array();

		return $cache;
	}
}

//----------------------------------------------------------------------------------
//  Output
//----------------------------------------------------------------------------------

if ( ! function_exists( 'braillewright_breadcrumbs_output' ) ) {
	/**
	 * Print the trail. Hooked to before_main in header.php.
	 */
	function braillewright_breadcrumbs_output() {
		if ( ! braillewright_breadcrumbs_should_display() ) {
			return;
		}

		$items = braillewright_breadcrumbs_get_items();
		if ( empty( $items ) ) {
			return;
		}

		if ( 'no' === get_theme_mod( 'breadcrumbs_show_current', 'yes' ) ) {
			array_pop( $items );
		}

		$separators = braillewright_breadcrumbs_separators();
		$key        = braillewright_breadcrumbs_sanitize_separator( get_theme_mod( 'breadcrumbs_separator', 'chevron' ) );
		$separator  = $separators[ $key ];
		$sep_class  = 'breadcrumbs-separator' . ( $separator['directional'] ? ' breadcrumbs-separator-directional' : '' );
		$last       = count( $items ) - 1;
		$list       = '';

		foreach ( $items as $index => $item ) {
			$is_last = ( $index === $last );

			if ( '' !== $item['url'] ) {
				$content = '<a class="breadcrumbs-link" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
			} else {
				$content = '<span class="breadcrumbs-current" aria-current="page">' . esc_html( $item['label'] ) . '</span>';
			}

			if ( ! $is_last ) {
				$content .= '<span class="' . esc_attr( $sep_class ) . '" aria-hidden="true">' . esc_html( $separator['char'] ) . '</span>';
			}

			$list .= '<li class="breadcrumbs-item">' . $content . '</li>';
		}

		printf(
			'<nav class="breadcrumbs" aria-label="%1$s"><ol class="breadcrumbs-list" role="list">%2$s</ol></nav>',
			esc_attr__( 'Breadcrumb', 'braillewright' ),
			$list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled above; every URL passed through esc_url(), every label through esc_html(), the class through esc_attr().
		);
	}
}
add_action( 'before_main', 'braillewright_breadcrumbs_output' );

if ( ! function_exists( 'braillewright_breadcrumbs_seo_plugin_owns_schema' ) ) {
	/**
	 * Whether an SEO plugin already prints breadcrumb structured data, so the theme must not.
	 *
	 * Yoast SEO is handled separately: it receives the theme's trail through its own filter.
	 *
	 * @return bool
	 */
	function braillewright_breadcrumbs_seo_plugin_owns_schema() {
		return defined( 'WPSEO_VERSION' )
			|| class_exists( 'RankMath' )
			|| function_exists( 'aioseo' )
			|| defined( 'SEOPRESS_VERSION' )
			|| function_exists( 'the_seo_framework' );
	}
}

if ( ! function_exists( 'braillewright_breadcrumbs_schema_output' ) ) {
	/**
	 * Print BreadcrumbList structured data for the current page, from the same trail the
	 * visitor sees.
	 *
	 * The current page is listed last without a URL, as Google's breadcrumb documentation
	 * allows. JSON_HEX_TAG turns < and > into < and >, so no title can close the
	 * script element early.
	 *
	 * Not printed on search results or on the "page not found" page, which search engines do
	 * not list; Yoast SEO leaves its own out of the 404 page for the same reason.
	 */
	function braillewright_breadcrumbs_schema_output() {
		$print = braillewright_breadcrumbs_should_display()
			&& ! is_search()
			&& ! is_404()
			&& ! braillewright_breadcrumbs_seo_plugin_owns_schema();

		if ( ! apply_filters( 'braillewright_breadcrumbs_schema', $print ) ) {
			return;
		}

		$items = braillewright_breadcrumbs_get_items();
		if ( empty( $items ) ) {
			return;
		}

		$elements = array();
		foreach ( $items as $index => $item ) {
			$element = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $item['label'],
			);
			if ( '' !== $item['url'] ) {
				$element['item'] = $item['url'];
			}
			$elements[] = $element;
		}

		$json = wp_json_encode(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $elements,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
		);

		if ( $json ) {
			echo '<script type="application/ld+json" class="braillewright-breadcrumbs-schema">' . $json . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON built by wp_json_encode() with JSON_HEX_TAG, so it cannot contain < or >.
		}
	}
}
add_action( 'wp_head', 'braillewright_breadcrumbs_schema_output', 20 );

if ( ! function_exists( 'braillewright_breadcrumbs_yoast_links' ) ) {
	/**
	 * Give Yoast SEO the theme's trail, so the BreadcrumbList in Yoast's schema graph matches
	 * the one on the page.
	 *
	 * Yoast builds that list through the wpseo_breadcrumb_links filter (src/generators/
	 * breadcrumbs-generator.php in Yoast SEO 28.5), and drops the whole list if any crumb lacks
	 * a 'url' or a 'text' key, so every crumb carries both. It removes the URL from the last
	 * crumb itself.
	 *
	 * Only while the theme's breadcrumbs are on and shown on this page; otherwise Yoast's own
	 * trail is left exactly as it was.
	 *
	 * @param array $crumbs Yoast's crumbs.
	 * @return array
	 */
	function braillewright_breadcrumbs_yoast_links( $crumbs ) {
		if ( ! braillewright_breadcrumbs_should_display() ) {
			return $crumbs;
		}

		$items = braillewright_breadcrumbs_get_items();
		if ( empty( $items ) ) {
			return $crumbs;
		}

		$mapped = array();
		foreach ( $items as $item ) {
			$mapped[] = array(
				'text' => $item['label'],
				'url'  => $item['url'],
			);
		}

		return $mapped;
	}
}
add_filter( 'wpseo_breadcrumb_links', 'braillewright_breadcrumbs_yoast_links', 20 );
