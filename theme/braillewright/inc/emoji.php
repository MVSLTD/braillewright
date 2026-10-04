<?php
/**
 * Emoji: visible but silent in menus, and kept out of the browser tab.
 *
 * Aaron, 2026-10-03: "I am only interested in keeping the emoji present within the menu for easy visual
 * identification ... let's get them out of the page titles. Let's get them out of the browser tabs, and they
 * will exist only in the menu as suffixes where they do, and they will not be announced to screen readers.
 * Except, of course, in page content where they might actually be useful".
 *
 * - Menu labels: each run of emoji is wrapped in <span aria-hidden="true">. It stays on screen; screen readers
 *   and braille displays skip it, and it drops out of the link's spoken name. Every menu printed by
 *   wp_nav_menu() goes through the nav_menu_item_title filter, so the main menu, its dropdowns and the phone
 *   menu are all covered. A label that is nothing but emoji is left alone, so no link loses its name.
 * - Browser tab: emoji are removed from the document title, whoever writes it (WordPress, or Yoast through
 *   pre_get_document_title), because a <title> cannot hide part of its text and screen readers announce it on
 *   every page load. A title that is nothing but emoji is left alone.
 * - Page content is not touched. The page titles themselves were cleaned in each site's data (2026-10-03,
 *   ops/emoji_titles.py), and breadcrumbs already leave emoji out (Customizer > Breadcrumbs).
 *
 * The emoji pattern is the breadcrumbs' one, braillewright_breadcrumbs_emoji_pattern() in inc/breadcrumbs.php,
 * tested on staging 2026-09-26: it keeps the copyright and trademark signs.
 *
 * @package Braillewright
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'braillewright_emoji_decode_entities' ) ) {
	/**
	 * Turn numeric HTML entities that stand for emoji (&#x1f4f0; or &#128240;) into the characters themselves,
	 * so they are found like any other emoji. Every other entity is left as it is.
	 *
	 * @param string $text Text that may hold entities.
	 * @return string
	 */
	function braillewright_emoji_decode_entities( $text ) {
		$pattern = braillewright_breadcrumbs_emoji_pattern();
		$decoded = preg_replace_callback(
			'/&#(x[0-9a-f]+|[0-9]+);/i',
			function ( $m ) use ( $pattern ) {
				$char = html_entity_decode( $m[0], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				return ( $char !== $m[0] && 1 === preg_match( $pattern, $char ) ) ? $char : $m[0];
			},
			(string) $text
		);
		return is_string( $decoded ) ? $decoded : (string) $text;
	}
}

if ( ! function_exists( 'braillewright_emoji_has_words' ) ) {
	/**
	 * Whether a label holds emoji AND something else to read once they are gone.
	 *
	 * @param string $html A label, possibly with markup.
	 * @return bool
	 */
	function braillewright_emoji_has_words( $html ) {
		$pattern = braillewright_breadcrumbs_emoji_pattern();
		$plain   = html_entity_decode( wp_strip_all_tags( braillewright_emoji_decode_entities( $html ) ), ENT_QUOTES, 'UTF-8' );
		if ( 1 !== preg_match( $pattern, $plain ) ) {
			return false;
		}
		$rest = preg_replace( $pattern, '', $plain );
		return is_string( $rest ) && '' !== trim( $rest );
	}
}

if ( ! function_exists( 'braillewright_emoji_hide_from_screen_readers' ) ) {
	/**
	 * Wrap each run of emoji in a label in <span aria-hidden="true">, outside any tag.
	 *
	 * @param string $html A menu label, possibly with markup.
	 * @return string The label, unchanged when it has no emoji, is only emoji, or is not valid UTF-8.
	 */
	function braillewright_emoji_hide_from_screen_readers( $html ) {
		$html = (string) $html;
		if ( '' === $html || ! braillewright_emoji_has_words( $html ) ) {
			return $html;
		}

		// The same pattern, repeated, so a heart and its emoji selector or a family joined by zero-width
		// joiners end up in ONE span. Its delimiters are "/" and "/u".
		$run   = '/(?:' . substr( braillewright_breadcrumbs_emoji_pattern(), 1, -2 ) . ')+/u';
		$parts = preg_split( '/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! is_array( $parts ) ) {
			return $html;
		}
		foreach ( $parts as $i => $part ) {
			if ( '' === $part || '<' === $part[0] ) {
				continue;
			}
			$wrapped = preg_replace_callback(
				$run,
				function ( $m ) {
					return '<span aria-hidden="true">' . $m[0] . '</span>';
				},
				braillewright_emoji_decode_entities( $part )
			);
			if ( is_string( $wrapped ) ) {
				$parts[ $i ] = $wrapped;
			}
		}
		return implode( '', $parts );
	}
}

if ( ! function_exists( 'braillewright_emoji_menu_item_title' ) ) {
	/**
	 * The nav_menu_item_title filter: emoji in a menu label stay visible and are hidden from screen readers.
	 *
	 * @param string $title The label as the menu prints it.
	 * @return string
	 */
	function braillewright_emoji_menu_item_title( $title ) {
		return braillewright_emoji_hide_from_screen_readers( $title );
	}
}
add_filter( 'nav_menu_item_title', 'braillewright_emoji_menu_item_title', 20 );

if ( ! function_exists( 'braillewright_emoji_strip' ) ) {
	/**
	 * Remove emoji from plain text and tidy the spaces they leave.
	 *
	 * @param string $text Plain text, such as a document title.
	 * @return string The text without emoji; unchanged when that would leave nothing or it is not valid UTF-8.
	 */
	function braillewright_emoji_strip( $text ) {
		$text = (string) $text;
		if ( '' === $text ) {
			return $text;
		}
		$stripped = preg_replace( braillewright_breadcrumbs_emoji_pattern(), '', braillewright_emoji_decode_entities( $text ) );
		if ( ! is_string( $stripped ) ) {
			return $text;
		}
		$stripped = trim( (string) preg_replace( '/ {2,}/u', ' ', $stripped ) );
		return '' === $stripped ? $text : $stripped;
	}
}

if ( ! function_exists( 'braillewright_emoji_document_title' ) ) {
	/**
	 * The pre_get_document_title filter, run last: a title written by Yoast or another plugin loses its emoji.
	 * An empty value means nobody wrote one, so WordPress builds it and document_title_parts below applies.
	 *
	 * @param string $title The title so far.
	 * @return string
	 */
	function braillewright_emoji_document_title( $title ) {
		return ( is_string( $title ) && '' !== $title ) ? braillewright_emoji_strip( $title ) : $title;
	}
}
add_filter( 'pre_get_document_title', 'braillewright_emoji_document_title', 999 );

if ( ! function_exists( 'braillewright_emoji_document_title_parts' ) ) {
	/**
	 * The document_title_parts filter: WordPress's own title (page title, site name, tagline) loses its emoji.
	 *
	 * @param array $parts The title's parts.
	 * @return array
	 */
	function braillewright_emoji_document_title_parts( $parts ) {
		if ( ! is_array( $parts ) ) {
			return $parts;
		}
		foreach ( $parts as $key => $part ) {
			if ( is_string( $part ) ) {
				$parts[ $key ] = braillewright_emoji_strip( $part );
			}
		}
		return $parts;
	}
}
add_filter( 'document_title_parts', 'braillewright_emoji_document_title_parts', 999 );
