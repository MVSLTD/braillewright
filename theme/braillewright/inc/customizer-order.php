<?php
/**
 * The Customizer's top-level list, in alphabetical order.
 *
 * Aaron, 2026-09-27, looking at the list on drkirkadams.com: "I would also like to alphabetize the customization
 * menu ... I don't know why they're in this order, but they would make much more sense in alphabetical order by
 * first letter." Until 2.0.18 the order was whatever each part of the theme and WordPress had asked for: Period
 * Pro's sections first (Layout, Colors, Fonts ...), then Site Identity, then the theme's own, then Menus,
 * Widgets and Additional CSS.
 *
 * Every entry of the top-level list is sorted by its title, the theme's and WordPress's alike, and so is anything
 * a plugin adds there. The active theme stays at the top, where WordPress puts it. Entries inside a panel keep
 * their own order.
 *
 * @package Braillewright
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'braillewright_customizer_sort_key' ) ) {
	/**
	 * The text a Customizer title is sorted by: no markup, no entities, no accents.
	 *
	 * @param string $title The panel or section title.
	 * @return string
	 */
	function braillewright_customizer_sort_key( $title ) {
		return remove_accents( trim( html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES, 'UTF-8' ) ) );
	}
}

if ( ! function_exists( 'braillewright_customizer_alphabetical_order' ) ) {
	/**
	 * Give every top-level panel and section a priority that follows its title from A to Z.
	 *
	 * Runs on customize_controls_init at priority 9, just before WordPress sorts the list by priority
	 * (WP_Customize_Manager::prepare_controls, priority 10), so everything registered on customize_register,
	 * whatever its priority and whoever registered it, is included. The active theme ("themes", priority 0) is
	 * left where it is. Two entries with the same title are kept apart by putting the panel first, then by id,
	 * so the order is the same on every load.
	 *
	 * ⚠️ strcasecmp, not strnatcasecmp: the natural comparison skips spaces, so it read "Font Sizes" as
	 * "FontSizes" and put it after "Fonts" (measured on TTT staging). Here a space comes before every letter,
	 * as in Windows File Explorer: "Font Sizes", then "Fonts".
	 *
	 * @param WP_Customize_Manager|null $wp_customize The Customizer manager (the global one when not passed).
	 */
	function braillewright_customizer_alphabetical_order( $wp_customize = null ) {
		if ( ! $wp_customize instanceof WP_Customize_Manager ) {
			$wp_customize = isset( $GLOBALS['wp_customize'] ) ? $GLOBALS['wp_customize'] : null;
		}
		if ( ! $wp_customize instanceof WP_Customize_Manager ) {
			return;
		}

		$entries = array();
		foreach ( $wp_customize->panels() as $id => $panel ) {
			if ( 'themes' === $id ) {
				continue;
			}
			$entries[] = array( braillewright_customizer_sort_key( $panel->title ), 0, (string) $id, $panel );
		}
		foreach ( $wp_customize->sections() as $id => $section ) {
			if ( '' !== (string) $section->panel ) {
				continue;
			}
			$entries[] = array( braillewright_customizer_sort_key( $section->title ), 1, (string) $id, $section );
		}

		usort(
			$entries,
			function ( $a, $b ) {
				$by_title = strcasecmp( $a[0], $b[0] );
				if ( 0 !== $by_title ) {
					return $by_title;
				}
				return 0 !== $a[1] - $b[1] ? $a[1] - $b[1] : strcmp( $a[2], $b[2] );
			}
		);

		foreach ( $entries as $index => $entry ) {
			$entry[3]->priority = 10 * ( $index + 1 );
		}
	}
}
add_action( 'customize_controls_init', 'braillewright_customizer_alphabetical_order', 9 );
