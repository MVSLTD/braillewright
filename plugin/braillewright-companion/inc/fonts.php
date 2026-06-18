<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_get_fonts() {

	$fonts_dir = PERIOD_PRO_PATH . "assets/fonts.json";
	$fonts     = file_get_contents( $fonts_dir );

	if ( is_string( $fonts ) && ! empty( $fonts ) ) {
		$fonts_object = json_decode( $fonts, true );
	} else {
		$fonts_object = '';
	}

	return $fonts_object;
}

// return the available fonts in a format the customizer can use
function ct_period_pro_prepare_fonts() {

	// get fonts array from fonts.json file
	$fonts = ct_period_pro_get_fonts();

	$font_families = array();

	if ( is_array( $fonts ) && ! empty( $fonts ) ) {

		// for each item in the file (which holds data for one font)
		foreach ( $fonts['items'] as $key => $value ) {

			// store current font family
			$item_family = $fonts['items'][ $key ]['family'];

			// store available weights
			$item_weights = $fonts['items'][ $key ]['variants'];

			// add current font family to font list with available weights
			$font_families[ $item_family ] = $item_weights;
		}
	}

	return $font_families;
}

function ct_period_pro_font_css() {

	$fonts = ct_period_pro_user_fonts();
	$css   = '';

	// Loop through all the Customizer font options
	foreach( $fonts as $element => $font ) {
		if ( $element == 'primary' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= "body, 
								input[type='text'],
								input[type='email'],
								input[type='password'],
								input[type='number'],
								input[type='search'],
								input[type='tel'],
								input[type='url'],
								input[type='submit'], 
								textarea,
								h1, h2, h3, h4, h5, h6 {";
								if ( $customFamily ) {
									$css .= "font-family: '". $font['font'] ."';";
								}
								if ( $customWeight ) {
									$css .= "font-weight: ". $font['weight'] .";";
								}
								$css .= " }";
								if ( $customWeight ) {
									// don't affect icons
									$css .= 'body i { font-weight: initial; }s';
								}
			}
		} elseif ( $element == 'heading' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= "h1, h2, h3, h4, h5, h6 {";
					if ( $customFamily ) {
						$css .= "font-family: '". $font['font'] ."';";
					}
					if ( $customWeight ) {
						$css .= "font-weight: ". $font['weight'] .";";
					}
					$css .= " }";
			}
		} elseif ( $element == 'input' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= "input[type='text'],
					input[type='email'],
					input[type='password'],
					input[type='number'],
					input[type='search'],
					input[type='tel'],
					input[type='url'],
					input[type='submit'], 
					textarea {";
					if ( $customFamily ) {
						$css .= "font-family: '". $font['font'] ."';";
					}
					if ( $customWeight ) {
						$css .= "font-weight: ". $font['weight'] .";";
					}
					$css .= " }";
			}
		} elseif ( $element == 'site-title' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".site-title {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'tagline' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".tagline {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'primary-menu' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".menu-primary {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'secondary-menu' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".menu-secondary {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'post-titles' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".post-title {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'post-byline' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".post-byline {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'post-text' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".post-content, .post-meta {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'comments' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".comments {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'widget-titles' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".widget .widget-title {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'widget-text' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".widget {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		} elseif ( $element == 'footer-text' ) {
			$customFamily = ( $font['font'] != 'default' && !empty( $font['font'] ) ) ? true : false;
			$customWeight = ( $font['weight'] != 'default' && !empty( $font['weight'] ) ) ? true : false;
			if ( $customFamily || $customWeight ) {
				$css .= ".design-credit {";
				if ( $customFamily ) {
					$css .= "font-family: '". $font['font'] ."';";
				}
				if ( $customWeight ) {
					$css .= "font-weight: ". $font['weight'] .";";
				}
				$css .= " }";
			}
		}
	}

	if ( ! empty( $css ) ) {

		$css = ct_period_pro_sanitize_css( $css );

		wp_add_inline_style( 'ct-period-style', $css );
		wp_add_inline_style( 'ct-period-style-rtl', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_font_css', 99 );

function ct_period_pro_register_new_font() {

	// Get the fonts saved in the Customizer
	$fonts 	  = ct_period_pro_user_fonts();
	$language = get_theme_mod( 'font_language' ) ? get_theme_mod( 'font_language' ) : 'latin-ext';

	// Loop through all the Customizer font options
	foreach( $fonts as $element => $font ) {
		// If user set a new font
		if ( $font['font'] != 'default' && !empty( $font['font'] ) ) {
			if ( $font['weight'] == 'default' || empty( $font['weight'] ) ) {
				$font_weight = '';
			} else {
				$font_weight = ':' . $font['weight'];
			}
			// Setup the arguments for the GF request
			$font_args = array(
				'family' => urlencode( $font['font'] . $font_weight ),
				'subset' => urlencode( $language )
			);
			// Format the GF request URL
			$font_url = add_query_arg( $font_args, '//fonts.googleapis.com/css' );
			// Register and enqueue the new stylesheet
			wp_register_style( 'ct-period-pro-'. $element .'-google-fonts', $font_url );
			wp_enqueue_style( 'ct-period-pro-'. $element .'-google-fonts' );
		}
	};
}
add_action( 'wp_enqueue_scripts', 'ct_period_pro_register_new_font', 30 );

// Get user's fonts saved in Customizer
function ct_period_pro_user_fonts() {

	$fonts = array(
		'primary' => array(
			'font' 	 => get_theme_mod( 'primary_font' ),
			'weight' => get_theme_mod( 'primary_font_weight' )
		),
		'heading' => array(
			'font' 	 => get_theme_mod( 'heading_font' ),
			'weight' => get_theme_mod( 'heading_font_weight' )
		),
		'input' => array( 
			'font' 	 => get_theme_mod( 'input_font' ),
			'weight' => get_theme_mod( 'input_font_weight' )
		),
		'site-title' => array( 
			'font' 	 => get_theme_mod( 'site_title_font' ),
			'weight' => get_theme_mod( 'site_title_font_weight' )
		),
		'tagline' => array(
			'font' 	 => get_theme_mod( 'tagline_font' ),
			'weight' => get_theme_mod( 'tagline_font_weight' )
		),
		'primary-menu' => array( 
			'font' 	 => get_theme_mod( 'primary_menu_font' ),
			'weight' => get_theme_mod( 'primary_menu_font_weight' )
		),
		'secondary-menu' => array( 
			'font' 	 => get_theme_mod( 'secondary_menu_font' ),
			'weight' => get_theme_mod( 'secondary_menu_font_weight' )
		),
		'post-titles' => array(
			'font' 	 => get_theme_mod( 'post_titles_font' ),
			'weight' => get_theme_mod( 'post_titles_font_weight' ),
		),
		'post-byline' => array(
			'font' 	 => get_theme_mod( 'post_byline_font' ),
			'weight' => get_theme_mod( 'post_byline_font_weight' ),
		),
		'post-text' => array(
			'font' 	 => get_theme_mod( 'post_text_font' ),
			'weight' => get_theme_mod( 'post_text_font_weight' )
		),
		'comments' => array(
			'font' 	 => get_theme_mod( 'comments_font' ),
			'weight' => get_theme_mod( 'comments_font_weight' )
		),
		'widget-titles' => array(
			'font' 	 => get_theme_mod( 'ct_widget_titles_font' ),
			'weight' => get_theme_mod( 'ct_widget_titles_font_weight' )
		),
		'widget-text' => array(
			'font' 	 => get_theme_mod( 'ct_widget_text_font' ),
			'weight' => get_theme_mod( 'ct_widget_text_font_weight' )
		),
		'footer-text' => array(
			'font' 	 => get_theme_mod( 'footer_text_font' ),
			'weight' => get_theme_mod( 'footer_text_font_weight' )
		)
	);

	return $fonts;
}