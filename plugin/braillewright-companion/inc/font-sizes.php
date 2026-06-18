<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_font_size_css() {
  $css = '';

  $site_title_mobile  = get_theme_mod('site_title_font_size_mobile');
  $site_title_tablet  = get_theme_mod('site_title_font_size_tablet');
  $site_title_desktop = get_theme_mod('site_title_font_size_desktop');

  $tagline_mobile  = get_theme_mod('tagline_font_size_mobile');
  $tagline_tablet  = get_theme_mod('tagline_font_size_tablet');
  $tagline_desktop = get_theme_mod('tagline_font_size_desktop');

  $menu_items_mobile  = get_theme_mod('menu_primary_font_size_mobile');
  $menu_items_tablet  = get_theme_mod('menu_primary_font_size_tablet');
  $menu_items_desktop = get_theme_mod('menu_primary_font_size_desktop');

  $secondary_menu_items_mobile  = get_theme_mod('menu_secondary_font_size_mobile');
  $secondary_menu_items_tablet  = get_theme_mod('menu_secondary_font_size_tablet');
  $secondary_menu_items_desktop = get_theme_mod('menu_secondary_font_size_desktop');

  $post_titles_mobile  = get_theme_mod('post_title_font_size_mobile');
  $post_titles_tablet  = get_theme_mod('post_title_font_size_tablet');
  $post_titles_desktop = get_theme_mod('post_title_font_size_desktop');

  $post_byline_mobile  = get_theme_mod('post_byline_font_size_mobile');
  $post_byline_tablet  = get_theme_mod('post_byline_font_size_tablet');
  $post_byline_desktop = get_theme_mod('post_byline_font_size_desktop');

  $post_text_mobile  = get_theme_mod('post_text_font_size_mobile');
  $post_text_tablet  = get_theme_mod('post_text_font_size_tablet');
  $post_text_desktop = get_theme_mod('post_text_font_size_desktop');

  $comments_mobile  = get_theme_mod('comments_font_size_mobile');
  $comments_tablet  = get_theme_mod('comments_font_size_tablet');
  $comments_desktop = get_theme_mod('comments_font_size_desktop');

  $widget_titles_mobile  = get_theme_mod('ct_widget_titles_font_size_mobile');
  $widget_titles_tablet  = get_theme_mod('ct_widget_titles_font_size_tablet');
  $widget_titles_desktop = get_theme_mod('ct_widget_titles_font_size_desktop');

  $widget_text_mobile  = get_theme_mod('ct_widget_text_font_size_mobile');
  $widget_text_tablet  = get_theme_mod('ct_widget_text_font_size_tablet');
  $widget_text_desktop = get_theme_mod('ct_widget_text_font_size_desktop');

  $footer_text_mobile  = get_theme_mod('footer_text_font_size_mobile');
  $footer_text_tablet  = get_theme_mod('footer_text_font_size_tablet');
  $footer_text_desktop = get_theme_mod('footer_text_font_size_desktop');

  // Site Title
  if ( $site_title_mobile != '' && $site_title_mobile != '21' ) {
    $css .= "@media all and (max-width: 599px) {
              .site-title { 
                font-size: " . $site_title_mobile . "px;
              }
            }";
  }
  if ( $site_title_tablet != '' && $site_title_tablet != '21' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
              .site-title { 
                font-size: " . $site_title_tablet . "px;
              }
            }";
  }
  if ( $site_title_desktop != '' && $site_title_desktop != '21' ) {
    $css .= "@media all and (min-width: 900px) {
              .site-title { 
                font-size: " . $site_title_desktop . "px;
              }
            }";
  }

  // Tagline
  if ( $tagline_mobile != '' && $tagline_mobile != '14' ) {
    $css .= "@media all and (max-width: 599px) {
              .tagline { 
                font-size: " .$tagline_mobile . "px;
              }
            }";
  }
  if ( $tagline_tablet != '' && $tagline_tablet != '14' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
              .tagline { 
                font-size: " . $tagline_tablet . "px;
              }
            }";
  }
  if ( $tagline_desktop != '' && $tagline_desktop != '14' ) {
    $css .= "@media all and (min-width: 900px) {
              .tagline { 
                font-size: " . $tagline_desktop . "px;
              }
            }";
  }

  // Primary Menu Items
  if ( $menu_items_mobile != '' && $menu_items_mobile != '14' ) {
    $css .= "@media all and (max-width: 799px) {
              .menu-primary a { 
                font-size: " . $menu_items_mobile . "px;
              }
            }";
  }
  if ( $menu_items_tablet != '' && $menu_items_tablet != '14' ) {
    $css .= "@media all and (min-width: 800px) and (max-width: 999px) {
              .menu-primary a { 
                font-size: " . $menu_items_tablet . "px;
              }
            }";
  }
  if ( $menu_items_desktop != '' && $menu_items_desktop != '14' ) {
    $css .= "@media all and (min-width: 1000px) {
              .menu-primary a { 
                font-size: " . $menu_items_desktop . "px;
              }
            }";
  }

  // Secondary Menu Items
  if ( $secondary_menu_items_mobile != '' && $secondary_menu_items_mobile != '12' ) {
    $css .= "@media all and (max-width: 799px) {
              .menu-secondary .menu-secondary-items a { 
                font-size: " . $secondary_menu_items_mobile . "px;
              }
            }";
  }
  if ( $secondary_menu_items_tablet != '' && $secondary_menu_items_tablet != '12' ) {
    $css .= "@media all and (min-width: 800px) and (max-width: 899px) {
              .menu-secondary .menu-secondary-items a { 
                font-size: " . $secondary_menu_items_tablet . "px;
              }
            }";
  }
  if ( $secondary_menu_items_desktop != '' && $secondary_menu_items_desktop != '12' ) {
    $css .= "@media all and (min-width: 900px) {
              .menu-secondary .menu-secondary-items a { 
                font-size: " . $secondary_menu_items_desktop . "px;
              }
            }";
  }

  // Post Titles
  if ( $post_titles_mobile != '' && $post_titles_mobile != '18' ) {
    $css .= "@media all and (max-width: 599px) {
              .post-header .post-title { 
                font-size: " . $post_titles_mobile . "px;
              }
            }";
  }
  if ( $post_titles_tablet != '' && $post_titles_tablet != '21' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
            .post-header .post-title { 
                font-size: " . $post_titles_tablet . "px;
              }
            }";
  }
  if ( $post_titles_desktop != '' && $post_titles_desktop != '28' ) {
    $css .= "@media all and (min-width: 900px) {
            .post-header .post-title { 
                font-size: " . $post_titles_desktop . "px;
              }
            }";
  }

  // Post Byline
  if ( $post_byline_mobile != '' && $post_byline_mobile != '12' ) {
    $css .= "@media all and (max-width: 599px) {
              .post-byline { 
                font-size: " . $post_byline_mobile . "px;
              }
            }";
  }
  if ( $post_byline_tablet != '' && $post_byline_tablet != '12' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
              .post-byline { 
                font-size: " . $post_byline_tablet . "px;
              }
            }";
  }
  if ( $post_byline_desktop != '' && $post_byline_desktop != '12' ) {
    $css .= "@media all and (min-width: 900px) {
              .post-byline { 
                font-size: " . $post_byline_desktop . "px;
              }
            }";
  }

  // Post Text
  if ( $post_text_mobile != '' && $post_text_mobile != '16' ) {
    $css .= "@media all and (max-width: 599px) {
              .post-content { 
                font-size: " . $post_text_mobile . "px;
              }
            }";
  }
  if ( $post_text_tablet != '' && $post_text_tablet != '16' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
              .post-content { 
                font-size: " . $post_text_tablet . "px;
              }
            }";
  }
  if ( $post_text_desktop != '' && $post_text_desktop != '16' ) {
    $css .= "@media all and (min-width: 900px) {
              .post-content { 
                font-size: " . $post_text_desktop . "px;
              }
            }";
  }

  // Comments
  if ( $comments_mobile != '' && $comments_mobile != '14' ) {
    $css .= "@media all and (max-width: 599px) {
              .comments { 
                font-size: " . $comments_mobile . "px;
              }
            }";
  }
  if ( $comments_tablet != '' && $comments_tablet != '14' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 799px) {
              .comments { 
                font-size: " . $comments_tablet . "px;
              }
            }";
  }
  if ( $comments_desktop != '' && $comments_desktop != '14' ) {
    $css .= "@media all and (min-width: 800px) {
              .comments { 
                font-size: " . $comments_desktop . "px;
              }
            }";
  }

  // Widget Titles
  if ( $widget_titles_mobile != '' && $widget_titles_mobile != '16' ) {
    $css .= "@media all and (max-width: 599px) {
              .sidebar-primary .widget .widget-title { 
                font-size: " . $widget_titles_mobile . "px;
              }
            }";
  }
  if ( $widget_titles_tablet != '' && $widget_titles_tablet != '16' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
              .sidebar-primary .widget .widget-title { 
                font-size: " . $widget_titles_tablet . "px;
              }
            }";
  }
  if ( $widget_titles_desktop != '' && $widget_titles_desktop != '16' ) {
    $css .= "@media all and (min-width: 900px) {
              .sidebar-primary .widget .widget-title { 
                font-size: " . $widget_titles_desktop . "px;
              }
            }";
  }

  // Widget Text
  if ( $widget_text_mobile != '' && $widget_text_mobile != '14' ) {
    $css .= "@media all and (max-width: 599px) {
              .sidebar-primary .widget > * { 
                font-size: " . $widget_text_mobile . "px;
              }
            }";
  }
  if ( $widget_text_tablet != '' && $widget_text_tablet != '14' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 899px) {
              .sidebar-primary .widget > * { 
                font-size: " . $widget_text_tablet . "px;
              }
            }";
  }
  if ( $widget_text_desktop != '' && $widget_text_desktop != '14' ) {
    $css .= "@media all and (min-width: 900px) {
            .sidebar-primary .widget > * { 
                font-size: " . $widget_text_desktop . "px;
              }
            }";
  }

  // Footer Text
  if ( $footer_text_mobile != '' && $footer_text_mobile != '14' ) {
    $css .= "@media all and (max-width: 599px) {
              .site-footer .design-credit { 
                font-size: " . $footer_text_mobile . "px;
              }
            }";
  }
  if ( $footer_text_tablet != '' && $footer_text_tablet != '14' ) {
    $css .= "@media all and (min-width: 600px) and (max-width: 799px) {
              .site-footer .design-credit { 
                font-size: " . $footer_text_tablet . "px;
              }
            }";
  }
  if ( $footer_text_desktop != '' && $footer_text_desktop != '14' ) {
    $css .= "@media all and (min-width: 800px) {
              .site-footer .design-credit { 
                font-size: " . $footer_text_desktop . "px;
              }
            }";
  }

  if ( ! empty( $css ) ) {

    $css = ct_period_pro_sanitize_css( $css );

    wp_add_inline_style( 'ct-period-style', $css );
    wp_add_inline_style( 'ct-period-style-rtl', $css );
  }
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_font_size_css', 99 );