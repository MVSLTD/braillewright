<?php

//----------------------------------------------------------------------------------
//	Include all required files
//----------------------------------------------------------------------------------
require_once(trailingslashit(get_template_directory()) . 'theme-options.php');
require_once(trailingslashit(get_template_directory()) . 'inc/customizer.php');
require_once(trailingslashit(get_template_directory()) . 'inc/last-updated-meta-box.php');
require_once(trailingslashit(get_template_directory()) . 'inc/scripts.php');
require_once(trailingslashit(get_template_directory()) . 'features/bootstrap.php');

if (! function_exists(('braillewright_set_content_width'))) {
    function braillewright_set_content_width()
    {
        if (! isset($content_width)) {
            $content_width = 891;
        }
    }
}
add_action('after_setup_theme', 'braillewright_set_content_width', 0);

if (! function_exists(('braillewright_theme_setup'))) {
    function braillewright_theme_setup()
    {
        add_theme_support('post-thumbnails');
        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption'
        ));
        add_theme_support('infinite-scroll', array(
            'container' => 'loop-container',
            'footer'    => 'overflow-container',
            'render'    => 'braillewright_infinite_scroll_render'
        ));

        // Gutenberg - wide & full images
        add_theme_support('align-wide');

        // Gutenberg - add support for editor styles
        add_theme_support('editor-styles');

        // Gutenberg - modify the font sizes
        add_theme_support('editor-font-sizes', array(
            array(
                    'name' => __('small', 'braillewright'),
                    'shortName' => __('S', 'braillewright'),
                    'size' => 12,
                    'slug' => 'small'
            ),
            array(
                    'name' => __('regular', 'braillewright'),
                    'shortName' => __('M', 'braillewright'),
                    'size' => 16,
                    'slug' => 'regular'
            ),
            array(
                    'name' => __('large', 'braillewright'),
                    'shortName' => __('L', 'braillewright'),
                    'size' => 21,
                    'slug' => 'large'
            ),
            array(
                    'name' => __('larger', 'braillewright'),
                    'shortName' => __('XL', 'braillewright'),
                    'size' => 28,
                    'slug' => 'larger'
            )
    ));

        register_nav_menus(array(
            'primary' => esc_html__('Primary', 'braillewright')
        ));

        // Add WooCommerce support
        add_theme_support('woocommerce');
        // Add support for WooCommerce image gallery features
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        load_theme_textdomain('braillewright', get_template_directory() . '/languages');
    }
}
add_action('after_setup_theme', 'braillewright_theme_setup', 10);

//-----------------------------------------------------------------------------
// Load custom stylesheet for the post editor
//-----------------------------------------------------------------------------
if (! function_exists('braillewright_add_editor_styles')) {
    function braillewright_add_editor_styles()
    {
        add_editor_style('styles/editor-style.css');
    }
}
add_action('admin_init', 'braillewright_add_editor_styles');

if (! function_exists(('braillewright_register_widget_areas'))) {
    function braillewright_register_widget_areas()
    {
        register_sidebar(array(
            'name'          => esc_html__('Primary Sidebar', 'braillewright'),
            'id'            => 'primary',
            'description'   => esc_html__('Widgets in this area will be shown in the sidebar next to the main post content', 'braillewright'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>'
        ));
    }
}
add_action('widgets_init', 'braillewright_register_widget_areas');

if (! function_exists(('braillewright_customize_comments'))) {
    function braillewright_customize_comments($comment, $args, $depth)
    {
        $GLOBALS['comment'] = $comment;
        global $post; ?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<div class="comment-author">
				<?php
                echo get_avatar(get_comment_author_email(), 48, '', get_comment_author()); ?>
				<div class="comment-meta">
					<span class="author-name"><?php comment_author_link(); ?></span>
					<span class="comment-date"><?php comment_date(); ?></span>
				</div>
			</div>
			<div class="comment-content">
				<?php if ($comment->comment_approved == '0') : ?>
					<em><?php esc_html_e('Your comment is awaiting moderation.', 'braillewright') ?></em>
					<br/>
				<?php endif; ?>
				<?php comment_text(); ?>
			</div>
			<div class="comment-footer">
				<?php comment_reply_link(array_merge($args, array(
                    'reply_text' => esc_html_x('Reply', 'verb: reply to this comment', 'braillewright'),
                    'depth'      => $depth,
                    'max_depth'  => $args['max_depth'],
                    'before'     => '<i class="fas fa-reply"></i>'
                ))); ?>
				<?php edit_comment_link(esc_html_x('Edit', 'verb: edit this comment', 'braillewright'), '<i class="fas fa-edit"></i>'); ?>
			</div>
		</article>
		<?php
    }
}

if (! function_exists('braillewright_update_fields')) {
    function braillewright_update_fields($fields)
    {
        $commenter = wp_get_current_commenter();
        $req       = get_option('require_name_email');
        $label     = $req ? '*' : ' ' . esc_html__('(optional)', 'braillewright');
        $aria_req  = $req ? "aria-required='true'" : '';

        $fields['author'] =
            '<p class="comment-form-author">
	            <label for="author">' . esc_html_x("Name", "noun", "braillewright") . $label . '</label>
	            <input id="author" name="author" type="text" placeholder="' . esc_attr__("Jane Doe", "braillewright") . '" value="' . esc_attr($commenter['comment_author']) .
            '" size="30" ' . $aria_req . ' />
	        </p>';

        $fields['email'] =
            '<p class="comment-form-email">
	            <label for="email">' . esc_html_x("Email", "noun", "braillewright") . $label . '</label>
	            <input id="email" name="email" type="email" placeholder="' . esc_attr__("name@email.com", "braillewright") . '" value="' . esc_attr($commenter['comment_author_email']) .
            '" size="30" ' . $aria_req . ' />
	        </p>';

        $fields['url'] =
            '<p class="comment-form-url">
	            <label for="url">' . esc_html__("Website", "braillewright") . '</label>
	            <input id="url" name="url" type="url" placeholder="http://google.com" value="' . esc_attr($commenter['comment_author_url']) .
            '" size="30" />
	            </p>';

        return $fields;
    }
}
add_filter('comment_form_default_fields', 'braillewright_update_fields');

if (! function_exists('braillewright_update_comment_field')) {
    function braillewright_update_comment_field($comment_field)
    {

        // don't filter the WooCommerce review form
        if (function_exists('is_woocommerce')) {
            if (is_woocommerce()) {
                return $comment_field;
            }
        }
        
        $comment_field =
            '<p class="comment-form-comment">
	            <label for="comment">' . esc_html_x("Comment", "noun", "braillewright") . '</label>
	            <textarea required id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
	        </p>';

        return $comment_field;
    }
}
add_filter('comment_form_field_comment', 'braillewright_update_comment_field', 7);

if (! function_exists('braillewright_remove_comments_notes_after')) {
    function braillewright_remove_comments_notes_after($defaults)
    {
        $defaults['comment_notes_after'] = '';
        return $defaults;
    }
}
add_action('comment_form_defaults', 'braillewright_remove_comments_notes_after');

if (! function_exists('braillewright_filter_read_more_link')) {
    function braillewright_filter_read_more_link($custom = false)
    {
        if (is_feed()) {
            return;
        }
        global $post;
        $ismore             = strpos($post->post_content, '<!--more-->');
        $read_more_text     = get_theme_mod('read_more_text');
        $new_excerpt_length = get_theme_mod('excerpt_length');
        $excerpt_more       = ($new_excerpt_length === 0) ? '' : '&#8230;';
        $output = '';

        // add ellipsis for automatic excerpts
        if (empty($ismore) && $custom !== true) {
            $output .= $excerpt_more;
        }
        // Because i18n text cannot be stored in a variable
        if (empty($read_more_text)) {
            $output .= '<div class="more-link-wrapper"><a class="more-link" href="' . esc_url(get_permalink()) . '">' . esc_html__('Continue reading', 'braillewright') . '<span class="screen-reader-text">' . esc_html(get_the_title()) . '</span></a></div>';
        } else {
            $output .= '<div class="more-link-wrapper"><a class="more-link" href="' . esc_url(get_permalink()) . '">' . esc_html($read_more_text) . '<span class="screen-reader-text">' . esc_html(get_the_title()) . '</span></a></div>';
        }
        return $output;
    }
}
add_filter('the_content_more_link', 'braillewright_filter_read_more_link'); // more tags
add_filter('excerpt_more', 'braillewright_filter_read_more_link', 10); // automatic excerpts

// handle manual excerpts
if (! function_exists('braillewright_filter_manual_excerpts')) {
    function braillewright_filter_manual_excerpts($excerpt)
    {
        $excerpt_more = '';
        if (has_excerpt()) {
            $excerpt_more = braillewright_filter_read_more_link(true);
        }
        return $excerpt . $excerpt_more;
    }
}
add_filter('get_the_excerpt', 'braillewright_filter_manual_excerpts');

if (! function_exists('braillewright_excerpt')) {
    function braillewright_excerpt()
    {
        global $post;
        $show_full_post = get_theme_mod('full_post');
        $ismore         = strpos($post->post_content, '<!--more-->');

        if ($show_full_post === 'yes' || $ismore) {
            the_content();
        } else {
            the_excerpt();
        }
    }
}

if (! function_exists('braillewright_custom_excerpt_length')) {
    function braillewright_custom_excerpt_length($length)
    {
        $new_excerpt_length = get_theme_mod('excerpt_length');

        if (! empty($new_excerpt_length) && $new_excerpt_length != 25) {
            return $new_excerpt_length;
        } elseif ($new_excerpt_length === 0) {
            return 0;
        } else {
            return 25;
        }
    }
}
add_filter('excerpt_length', 'braillewright_custom_excerpt_length', 99);

if (! function_exists('braillewright_remove_more_link_scroll')) {
    function braillewright_remove_more_link_scroll($link)
    {
        $link = preg_replace('|#more-[0-9]+|', '', $link);
        return $link;
    }
}
add_filter('the_content_more_link', 'braillewright_remove_more_link_scroll');

// Yoast OG description has "Continue readingTitle of the Post" due to its use of get_the_excerpt(). This fixes that.
function braillewright_update_yoast_og_description($ogdesc)
{
    $read_more_text = get_theme_mod('read_more_text');
    if (empty($read_more_text)) {
        $read_more_text = esc_html__('Continue reading', 'braillewright');
    }
    $ogdesc = substr($ogdesc, 0, strpos($ogdesc, $read_more_text));

    return $ogdesc;
}
add_filter('wpseo_opengraph_desc', 'braillewright_update_yoast_og_description');

if (! function_exists('braillewright_featured_image')) {
    function braillewright_featured_image()
    {
        global $post;
        $featured_image = '';

        if (has_post_thumbnail($post->ID)) {
            if (is_singular()) {
                $featured_image = '<div class="featured-image">' . get_the_post_thumbnail($post->ID, 'full') . '</div>';
            } else {
                $featured_image = '<div class="featured-image"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . get_the_post_thumbnail($post->ID, 'full') . '</a></div>';
            }
        }

        $featured_image = apply_filters('braillewright_featured_image', $featured_image);

        if ($featured_image) {
            echo wp_kses_post( (string) $featured_image );
        }
    }
}

if (! function_exists('braillewright_social_array')) {
    function braillewright_social_array()
    {
        $social_sites = array(
            'twitter'       => 'braillewright_twitter_profile',
            'facebook'      => 'braillewright_facebook_profile',
            'instagram'     => 'braillewright_instagram_profile',
            'tiktok'     	=> 'braillewright_tiktok_profile',
            'threads'     	=> 'braillewright_threads_profile',
            'linkedin'      => 'braillewright_linkedin_profile',
            'pinterest'     => 'braillewright_pinterest_profile',
            'youtube'       => 'braillewright_youtube_profile',
            'rss'           => 'braillewright_rss_profile',
            'email'         => 'braillewright_email_profile',
            'phone'			=> 'braillewright_phone_profile',
            'email-form'    => 'braillewright_email_form_profile',
            'amazon'        => 'braillewright_amazon_profile',
            'artstation'    => 'braillewright_artstation_profile',
            'bandcamp'      => 'braillewright_bandcamp_profile',
            'behance'       => 'braillewright_behance_profile',
            'bitbucket'     => 'braillewright_bitbucket_profile',
            'codepen'       => 'braillewright_codepen_profile',
            'delicious'     => 'braillewright_delicious_profile',
            'deviantart'    => 'braillewright_deviantart_profile',
            'diaspora'      => 'braillewright_diaspora_profile',
            'digg'          => 'braillewright_digg_profile',
            'discord'    	=> 'braillewright_discord_profile',
            'dribbble'      => 'braillewright_dribbble_profile',
            'etsy'          => 'braillewright_etsy_profile',
            'flickr'        => 'braillewright_flickr_profile',
            'foursquare'    => 'braillewright_foursquare_profile',
            'github'        => 'braillewright_github_profile',
            'goodreads'		=> 'braillewright_goodreads_profile',
            'google-wallet' => 'braillewright_google_wallet_profile',
            'hacker-news'   => 'braillewright_hacker-news_profile',
            'imdb'   		=> 'braillewright_imdb_profile',
            'mastodon'      => 'braillewright_mastodon_profile',
            'medium'        => 'braillewright_medium_profile',
            'meetup'        => 'braillewright_meetup_profile',
            'mixcloud'      => 'braillewright_mixcloud_profile',
            'ok-ru'         => 'braillewright_ok_ru_profile',
            'orcid'         => 'braillewright_orcid_profile',
            'patreon'       => 'braillewright_patreon_profile',
            'paypal'        => 'braillewright_paypal_profile',
            'pocket'        => 'braillewright_pocket_profile',
            'podcast'       => 'braillewright_podcast_profile',
            'qq'            => 'braillewright_qq_profile',
            'quora'         => 'braillewright_quora_profile',
            'ravelry'       => 'braillewright_ravelry_profile',
            'reddit'        => 'braillewright_reddit_profile',
            'researchgate'  => 'braillewright_researchgate_profile',
            'skype'         => 'braillewright_skype_profile',
            'slack'         => 'braillewright_slack_profile',
            'slideshare'    => 'braillewright_slideshare_profile',
            'soundcloud'    => 'braillewright_soundcloud_profile',
            'spotify'       => 'braillewright_spotify_profile',
            'snapchat'      => 'braillewright_snapchat_profile',
            'stack-overflow' => 'braillewright_stack_overflow_profile',
            'steam'         => 'braillewright_steam_profile',
            'strava'   		=> 'braillewright_strava_profile',
            'stumbleupon'   => 'braillewright_stumbleupon_profile',
            'telegram'      => 'braillewright_telegram_profile',
            'tencent-weibo' => 'braillewright_tencent_weibo_profile',
            'tumblr'        => 'braillewright_tumblr_profile',
            'twitch'        => 'braillewright_twitch_profile',
            'untappd'       => 'braillewright_untappd_profile',
            'vimeo'         => 'braillewright_vimeo_profile',
            'vine'          => 'braillewright_vine_profile',
            'vk'            => 'braillewright_vk_profile',
            'wechat'        => 'braillewright_wechat_profile',
            'weibo'         => 'braillewright_weibo_profile',
            'whatsapp'      => 'braillewright_whatsapp_profile',
            'xing'          => 'braillewright_xing_profile',
            'yahoo'         => 'braillewright_yahoo_profile',
            'yelp'          => 'braillewright_yelp_profile',
            '500px'         => 'braillewright_500px_profile',
            'social_icon_custom_1' => 'social_icon_custom_1_profile',
            'social_icon_custom_2' => 'social_icon_custom_2_profile',
            'social_icon_custom_3' => 'social_icon_custom_3_profile'
        );

        return apply_filters('braillewright_social_array_filter', $social_sites);
    }
}

//----------------------------------------------------------------------------------
//	Output the social media icons
//----------------------------------------------------------------------------------
if (! function_exists('braillewright_social_icons_output')) {
    function braillewright_social_icons_output()
    {
        // Get the social icons array
        $social_sites = braillewright_social_array();
        // Store only icons with URLs saved
        $saved = array();

        /* Store the site name and ID if saved
        /* name: twitter
        /* id: braillewright_twitter_profile */
        foreach ($social_sites as $name => $id) {
            if (strlen(get_theme_mod($name)) > 0) {
                $saved[ $name ] = $id;
            }
        }

        // If there are any social profiles saved
        if (!empty($saved)) {
            echo "<ul class='social-media-icons'>";

            // Output list item for every saved profile
            foreach ($saved as $name => $id) {
                if ($name == 'rss') {
                    $class = 'fas fa-rss';
                } elseif ($name == 'email') {
                    $class = 'fas fa-envelope';
                } elseif ($name == 'email-form') {
                    $class = 'far fa-envelope';
                } elseif ($name == 'podcast') {
                    $class = 'fas fa-podcast';
                } elseif ($name == 'ok-ru') {
                    $class = 'fab fa-odnoklassniki';
                } elseif ($name == 'wechat') {
                    $class = 'fab fa-weixin';
                } elseif ($name == 'pocket') {
                    $class = 'fab fa-get-pocket';
                } elseif ($name == 'phone') {
                    $class = 'fas fa-phone';
                } elseif ($name == 'twitter') {
                    $class = 'fab fa-x-twitter';
                } else {
                    $class = 'fab fa-' . $name;
                }

                $url = get_theme_mod($name);
                $title = $name;

                // Escape the URL based on protocol being used
                if ($name == 'email') {
                    $href = 'mailto:' . antispambot(is_email($url));
                    $title = antispambot(is_email($url));
                } elseif ($name == 'skype') {
                    $href = esc_url($url, array( 'http', 'https', 'skype' ));
                } elseif ($name == 'phone') {
                    $href = esc_url($url, array( 'tel' ));
                    $title = esc_url($url, array( 'tel' ));
                } else {
                    $href = esc_url($url);
                }
                // Output the icon
                if ($name == 'social_icon_custom_1' || $name == 'social_icon_custom_2' || $name == 'social_icon_custom_3') { ?>
					<li>
						<a class="custom-icon" target="_blank" href="<?php echo $href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $href is pre-escaped via esc_url() above. ?>">
							<img class="icon" src="<?php echo esc_url(get_theme_mod($name .'_image')); ?>" style="width: <?php echo absint(get_theme_mod($name . '_size', '20')); ?>px;" alt="<?php echo esc_html(get_theme_mod($name . '_name'));  ?>" />
						</a>
					</li>
				<?php
                } else { ?>
					<li>
						<a class="<?php echo esc_attr($name); ?>" target="_blank" href="<?php echo $href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $href is pre-escaped per protocol branch (esc_url / mailto antispambot) above. ?>"
                            <?php if ($title == 'mastodon') {
                                echo 'rel="me"';
                            } ?>>
							<i class="<?php echo esc_attr($class); ?>" aria-hidden="true" title="<?php echo esc_attr($title); ?>"></i>
							<span class="screen-reader-text"><?php echo esc_html($title);  ?></span>
						</a>
					</li>
				<?php
                }
            }
            echo "</ul>";
        }
    }
}

/*
 * WP will apply the ".menu-primary-items" class & id to the containing <div> instead of <ul>
 * making styling difficult and confusing. Using this wrapper to add a unique class to make styling easier.
 */
if (! function_exists(('braillewright_wp_page_menu'))) {
    function braillewright_wp_page_menu()
    {
        wp_page_menu(
            array(
                "menu_class" => "menu-unset",
                "depth"      => - 1
            )
        );
    }
}

if (! function_exists(('braillewright_nav_dropdown_buttons'))) {
    function braillewright_nav_dropdown_buttons($item_output, $item, $depth, $args)
    {
        if ($args->theme_location == 'primary') {
            if (in_array('menu-item-has-children', $item->classes) || in_array('page_item_has_children', $item->classes)) {
                $item_output = str_replace($args->link_after . '</a>', $args->link_after . '</a><button class="toggle-dropdown" aria-expanded="false" name="toggle-dropdown"><span class="screen-reader-text">' . esc_html_x("open dropdown menu", "verb: open the dropdown menu", "braillewright") . '</span><span class="arrow"></span></button>', $item_output);
            }
        }

        return $item_output;
    }
}
add_filter('walker_nav_menu_start_el', 'braillewright_nav_dropdown_buttons', 10, 4);

if (! function_exists(('braillewright_sticky_post_marker'))) {
    function braillewright_sticky_post_marker()
    {
        if (is_sticky() && !is_archive() && !is_search()) {
            echo '<div class="sticky-status"><span>' . esc_html__("Featured", "braillewright") . '</span></div>';
        }
    }
}
add_action('sticky_post_status', 'braillewright_sticky_post_marker');

if (! function_exists(('braillewright_reset_customizer_options'))) {
    function braillewright_reset_customizer_options()
    {
        if (empty($_POST['braillewright_reset_customizer']) || 'braillewright_reset_customizer_settings' !== $_POST['braillewright_reset_customizer']) {
            return;
        }

        if (! isset($_POST['braillewright_reset_customizer_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['braillewright_reset_customizer_nonce'])), 'braillewright_reset_customizer_nonce')) {
            return;
        }

        if (! current_user_can('edit_theme_options')) {
            return;
        }

        $mods_array = array(
            'logo_upload',
            'search_bar',
            'layout',
            'layout_pages',
            'layout_blog',
            'layout_archives',
            'full_post',
            'excerpt_length',
            'read_more_text',
            'display_post_author',
            'display_post_date',
            'last_updated',
            'custom_css'
        );

        $social_sites = braillewright_social_array();

        // add social site settings to mods array
        foreach ($social_sites as $social_site => $value) {
            $mods_array[] = $social_site;
        }

        $mods_array = apply_filters('braillewright_mods_to_remove', $mods_array);

        foreach ($mods_array as $theme_mod) {
            remove_theme_mod($theme_mod);
        }

        $redirect = admin_url('themes.php?page=braillewright-options');
        $redirect = add_query_arg('braillewright_status', 'deleted', $redirect);

        // safely redirect
        wp_safe_redirect($redirect);
        exit;
    }
}
add_action('admin_init', 'braillewright_reset_customizer_options');

if (! function_exists(('braillewright_delete_settings_notice'))) {
    function braillewright_delete_settings_notice()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin notice after the nonce-verified reset redirect; no state change.
        if ( isset( $_GET['braillewright_status'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice; compared to a string literal, no sanitization required.
            if ( 'deleted' === $_GET['braillewright_status'] ) {
                ?>
				<div class="updated">
					<p><?php esc_html_e('Customizer settings deleted.', 'braillewright'); ?></p>
				</div>
				<?php
            }
        }
    }
}
add_action('admin_notices', 'braillewright_delete_settings_notice');

if (! function_exists(('braillewright_body_class'))) {
    function braillewright_body_class($classes)
    {
        global $post;
        $full_post 					= get_theme_mod('full_post');
        $post_layout    		= get_theme_mod('layout');
        $page_layout    		= get_theme_mod('layout_pages');
        $blog_layout    		= get_theme_mod('layout_blog');
        $archives_layout    = get_theme_mod('layout_archives');

        if ($full_post == 'yes') {
            $classes[] = 'full-post';
        }
        if (!empty($post_layout) && is_singular('post')) {
            $classes[] = $post_layout . '-sidebar';
        }
        if (!empty($page_layout) && is_singular('page')) {
            $classes[] = $page_layout . '-sidebar';
        }
        if (!empty($blog_layout) && is_home()) {
            $classes[] = $blog_layout . '-sidebar';
        }
        if (!empty($archives_layout) && is_archive()) {
            $classes[] = $archives_layout . '-sidebar';
        }

        return $classes;
    }
}
add_filter('body_class', 'braillewright_body_class');

if (! function_exists(('braillewright_post_class'))) {
    function braillewright_post_class($classes)
    {
        $classes[] = 'entry';

        return $classes;
    }
}
add_filter('post_class', 'braillewright_post_class');

if (! function_exists(('braillewright_custom_css_output'))) {
    function braillewright_custom_css_output()
    {
        if (function_exists('wp_get_custom_css')) {
            $custom_css = wp_get_custom_css();
        } else {
            $custom_css = get_theme_mod('custom_css');
        }
        $logo_size  = get_theme_mod('logo_size');

        if ($logo_size != 168 && ! empty($logo_size)) {
            $logo_size_css = '.logo {
							width: ' . $logo_size . 'px;
						  }';
            $custom_css .= $logo_size_css;
        }
        if (get_theme_mod('display_post_author') == 'hide') {
            $custom_css .= '';
        }
        if (get_theme_mod('display_post_date') == 'hide') {
            $custom_css .= '';
        }

        if (! empty($custom_css)) {
            $custom_css = braillewright_sanitize_css($custom_css);

            wp_add_inline_style('braillewright-style', $custom_css);
            wp_add_inline_style('braillewright-style-rtl', $custom_css);
        }
    }
}
add_action('wp_enqueue_scripts', 'braillewright_custom_css_output', 20);

if (! function_exists(('braillewright_svg_output'))) {
    function braillewright_svg_output($type)
    {
        $svg = '';

        if ($type == 'toggle-navigation') {
            $svg = '<svg width="36px" height="23px" viewBox="0 0 36 23" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <desc>mobile menu toggle button</desc>
				    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g transform="translate(-142.000000, -104.000000)" fill="#FFFFFF">
				            <g transform="translate(142.000000, 104.000000)">
				                <rect x="0" y="20" width="36" height="3"></rect>
				                <rect x="0" y="10" width="36" height="3"></rect>
				                <rect x="0" y="0" width="36" height="3"></rect>
				            </g>
				        </g>
				    </g>
				</svg>';
        }

        return $svg;
    }
}

if (! function_exists(('braillewright_add_meta_elements'))) {
    function braillewright_add_meta_elements()
    {
        $meta_elements = '';

        $meta_elements .= sprintf('<meta charset="%s" />' . "\n", esc_attr(get_bloginfo('charset')));
        $meta_elements .= '<meta name="viewport" content="width=device-width, initial-scale=1" />' . "\n";

        $theme    = wp_get_theme(get_template());
        $template = sprintf('<meta name="template" content="%s %s" />' . "\n", esc_attr($theme->get('Name')), esc_attr($theme->get('Version')));
        $meta_elements .= $template;

        echo $meta_elements; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- composed of literal <meta> tags + esc_attr()'d values; wp_kses_post() would strip <meta>.
    }
}
add_action('wp_head', 'braillewright_add_meta_elements', 1);

if (! function_exists(('braillewright_infinite_scroll_render'))) {
    function braillewright_infinite_scroll_render()
    {
        while (have_posts()) {
            the_post();
            get_template_part('content', 'archive');
        }
    }
}

if (! function_exists('braillewright_get_content_template')) {
    function braillewright_get_content_template()
    {

        // Get bbpress.php for all bbpress pages
        if (function_exists('is_bbpress')) {
            if (is_bbpress()) {
                get_template_part('content/bbpress');
                return;
            }
        }
        if (is_home() || is_archive()) {
            get_template_part('content-archive', get_post_type());
        } else {
            get_template_part('content', get_post_type());
        }
    }
}

// allow skype URIs to be used
if (! function_exists(('braillewright_allow_skype_protocol'))) {
    function braillewright_allow_skype_protocol($protocols)
    {
        $protocols[] = 'skype';

        return $protocols;
    }
}
add_filter('kses_allowed_protocols', 'braillewright_allow_skype_protocol');

//----------------------------------------------------------------------------------
// Add paragraph tags for author bio displayed in content/archive-header.php.
// the_archive_description includes paragraph tags for tag and category descriptions, but not the author bio.
//----------------------------------------------------------------------------------
if (! function_exists('braillewright_modify_archive_descriptions')) {
    function braillewright_modify_archive_descriptions($description)
    {
        if (is_author()) {
            $description = wpautop($description);
        }
        return $description;
    }
}
add_filter('get_the_archive_description', 'braillewright_modify_archive_descriptions');

//----------------------------------------------------------------------------------
// So existing users don't have certain templates revert to the left sidebar
//----------------------------------------------------------------------------------
function braillewright_set_default_layouts()
{
    if (get_option('braillewright_layouts_set') == '') {
        $current_layout = get_theme_mod('layout');
        set_theme_mod('layout_pages', $current_layout);
        set_theme_mod('layout_blog', $current_layout);
        set_theme_mod('layout_archives', $current_layout);
        update_option('braillewright_layouts_set', 'yes');
    }
}
add_action('after_setup_theme', 'braillewright_set_default_layouts');

//----------------------------------------------------------------------------------
// Output the markup for the optional scroll-to-top arrow
//----------------------------------------------------------------------------------
function braillewright_scroll_to_top_arrow()
{
    $setting = get_theme_mod('scroll_to_top');
    
    if ($setting == 'yes') {
        echo '<button id="scroll-to-top" class="scroll-to-top"><span class="screen-reader-text">'. esc_html__('Scroll to the top', 'braillewright') .'</span><i class="fas fa-arrow-up"></i></button>';
    }
}
add_action('body_bottom', 'braillewright_scroll_to_top_arrow');

//----------------------------------------------------------------------------------
// Output the "Last Updated" date on posts
//----------------------------------------------------------------------------------
function braillewright_output_last_updated_date()
{
    global $post;

    if (get_the_modified_date() != get_the_date()) {
        $updated_post = get_post_meta($post->ID, 'braillewright_last_updated', true);
        $updated_customizer = get_theme_mod('last_updated');
        if (
            ($updated_customizer == 'yes' && ($updated_post != 'no'))
            || $updated_post == 'yes'
            ) {
            echo '<p class="last-updated">'. esc_html__("Last updated on", "braillewright") . ' ' . esc_html( (string) get_the_modified_date() ) . ' </p>';
        }
    }
}

//----------------------------------------------------------------------------------
// Output standard post pagination
//----------------------------------------------------------------------------------
if (! function_exists(('braillewright_pagination'))) {
    function braillewright_pagination()
    {

    // Never output pagination on bbpress pages
        if (function_exists('is_bbpress')) {
            if (is_bbpress()) {
                return;
            }
        }
        // Output pagination if Jetpack not installed, otherwise check if infinite scroll is active before outputting
        if (!class_exists('Jetpack')) {
            the_posts_pagination(array(
        'prev_text' => esc_html__('Previous', 'braillewright'),
        'next_text' => esc_html__('Next', 'braillewright')
      ));
        } elseif (!Jetpack::is_module_active('infinite-scroll')) {
            the_posts_pagination(array(
        'prev_text' => esc_html__('Previous', 'braillewright'),
        'next_text' => esc_html__('Next', 'braillewright')
      ));
        }
    }
}

//----------------------------------------------------------------------------------
// Add support for Elementor headers & footers
//----------------------------------------------------------------------------------
function braillewright_register_elementor_locations($elementor_theme_manager)
{
    $elementor_theme_manager->register_location('header');
    $elementor_theme_manager->register_location('footer');
}
add_action('elementor/theme/register_locations', 'braillewright_register_elementor_locations');
