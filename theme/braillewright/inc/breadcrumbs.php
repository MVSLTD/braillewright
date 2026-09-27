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
 * on the dark header band, and at phone width a long post title ran off the band onto the grey
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
				'label'       => __( 'Double chevron', 'braillewright' ) . ' »',
				'directional' => true,
			),
			'arrow'          => array(
				'char'        => '→',
				'label'       => __( 'Arrow', 'braillewright' ) . ' →',
				'directional' => true,
			),
			'pipe'           => array(
				'char'        => '|',
				'label'       => __( 'Vertical bar', 'braillewright' ) . ' |',
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

if ( ! function_exists( 'braillewright_breadcrumbs_customize_register' ) ) {
	/**
	 * Add the Breadcrumbs section and its settings to the Customizer.
	 *
	 * Colours live with every other colour, under Colors > Breadcrumbs, and come from
	 * braillewright_features_custom_colors_data() in features/inc/colors.php.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer manager.
	 */
	function braillewright_breadcrumbs_customize_register( $wp_customize ) {

		$wp_customize->add_section(
			'braillewright_breadcrumbs',
			array(
				'title'       => __( 'Breadcrumbs', 'braillewright' ),
				'priority'    => 56,
				'description' => __( 'A trail of links above the content that shows where each page sits in your site, for example Home, then News, then the article. It is never shown on the front page. Change its colors under Colors, then Breadcrumbs.', 'braillewright' ),
			)
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
				'label'    => __( 'Show breadcrumbs?', 'braillewright' ),
				'section'  => 'braillewright_breadcrumbs',
				'settings' => 'breadcrumbs',
				'type'     => 'radio',
				'choices'  => array(
					'yes' => __( 'Yes', 'braillewright' ),
					'no'  => __( 'No', 'braillewright' ),
				),
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
				'label'       => __( 'Separator between links', 'braillewright' ),
				'description' => __( 'Screen readers do not announce the separator, whichever you choose.', 'braillewright' ),
				'section'     => 'braillewright_breadcrumbs',
				'settings'    => 'breadcrumbs_separator',
				'type'        => 'radio',
				'choices'     => $choices,
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
				'label'       => __( 'Text of the first link', 'braillewright' ),
				'description' => __( 'Leave empty to use "Home".', 'braillewright' ),
				'section'     => 'braillewright_breadcrumbs',
				'settings'    => 'breadcrumbs_home_label',
				'type'        => 'text',
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
				'label'       => __( 'End the trail with the title of the current page?', 'braillewright' ),
				'description' => __( 'It is marked as the current page for screen readers, and long titles are shortened on screen to two lines.', 'braillewright' ),
				'section'     => 'braillewright_breadcrumbs',
				'settings'    => 'breadcrumbs_show_current',
				'type'        => 'radio',
				'choices'     => array(
					'yes' => __( 'Yes', 'braillewright' ),
					'no'  => __( 'No', 'braillewright' ),
				),
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
				'label'       => __( 'Leave emoji out of the breadcrumbs?', 'braillewright' ),
				'description' => __( 'Screen readers read every emoji aloud, so a link titled "Newsletters" followed by a newspaper emoji is announced as "Newsletters newspaper". Your page titles themselves are not changed.', 'braillewright' ),
				'section'     => 'braillewright_breadcrumbs',
				'settings'    => 'breadcrumbs_strip_emoji',
				'type'        => 'radio',
				'choices'     => array(
					'yes' => __( 'Yes', 'braillewright' ),
					'no'  => __( 'No', 'braillewright' ),
				),
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
			array( 'breadcrumbs', 'breadcrumbs_separator', 'breadcrumbs_home_label', 'breadcrumbs_show_current', 'breadcrumbs_strip_emoji' )
		);
	}
}
add_filter( 'braillewright_mods_to_remove', 'braillewright_breadcrumbs_mods_to_remove' );

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
	 * Never on the front page, where it could only ever say "Home" (Yoast SEO prints exactly
	 * that, as one unlinked word, measured on TTT staging), except on its second and later
	 * pages. Never on the landing-page templates, which are standalone pages by design, and
	 * never on bbPress pages, which print their own trail.
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
			} elseif ( is_front_page() && ! is_paged() && (int) get_query_var( 'page' ) < 2 ) {
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

if ( ! function_exists( 'braillewright_breadcrumbs_term_items' ) ) {
	/**
	 * A term and its parents, top-most first.
	 *
	 * @param WP_Term $term        The term.
	 * @param bool    $link_itself Whether the term itself is a link (false when it IS the current page).
	 * @return array[]
	 */
	function braillewright_breadcrumbs_term_items( $term, $link_itself = true ) {
		$items = array();

		foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $term->taxonomy );
			if ( $ancestor instanceof WP_Term ) {
				$link = get_term_link( $ancestor );
				if ( ! is_wp_error( $link ) ) {
					$items[] = braillewright_breadcrumbs_item( $ancestor->name, $link );
				}
			}
		}

		$link    = $link_itself ? get_term_link( $term ) : '';
		$items[] = braillewright_breadcrumbs_item( $term->name, is_wp_error( $link ) ? '' : $link );

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

		if ( 'post' === $post->post_type ) {
			$items = braillewright_breadcrumbs_blog_item();
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
		} else {
			$term = braillewright_breadcrumbs_post_term( $post );
			if ( $term ) {
				$items = array_merge( $items, braillewright_breadcrumbs_term_items( $term ) );
			}
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

		if ( is_front_page() ) {
			// Reached only on page 2 and later of the front page; see should_display().
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
				if ( $taxonomy && in_array( 'post', (array) $taxonomy->object_type, true ) ) {
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
			'<nav class="breadcrumbs" aria-label="%1$s"><ol class="breadcrumbs-list">%2$s</ol></nav>',
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
