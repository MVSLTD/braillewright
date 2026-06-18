<?php
defined( 'ABSPATH' ) OR exit;

//----------------------------------------------------------------------------------
// Add the Featured Video meta box 
//----------------------------------------------------------------------------------
function ct_period_pro_add_video_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'ct_period_pro_video',
			esc_html__( 'Featured Video', 'period-pro' ),
			'ct_period_pro_video_callback',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'ct_period_pro_add_video_meta_box' );

//----------------------------------------------------------------------------------
// Output the form in the post editor
//----------------------------------------------------------------------------------
function ct_period_pro_video_callback( $post ) {

	wp_nonce_field( 'ct_period_pro_video', 'ct_period_pro_video_nonce' );

	$video_url        = get_post_meta( $post->ID, 'ct_period_pro_video_key', true );
	$youtube_logo     = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_logo', true );
	$youtube_captions = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_captions', true );
	$youtube_autoplay = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_autoplay', true );
	$youtube_loop     = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_loop', true );
	$youtube_mute     = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_mute', true );

	// video preview
	echo '<div class="ct_period_pro_video_preview_container" id="ct_period_pro_video_preview_container">';
		echo '<label for="ct_period_pro_video_url">';
			esc_html_e( 'Video Preview', 'period-pro' );
		echo '</label> ';
		if ( $video_url ) {
			if ( strpos( $video_url, 'youtube-nocookie.com' ) !== false ) {
				echo '<iframe src="'. esc_url( $video_url ) .'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
			} else {
				echo ct_period_pro_output_video( $video_url );
			}
		}
		echo '<span class="loading">' . ct_period_pro_loading_indicator_svg() . '</span>';
	echo '</div>';

	// video URL input
	echo '<div class="ct_period_pro_video_input_container">';
		echo '<label for="ct_period_pro_video_url">';
			esc_html_e( 'Add video URL:', 'period-pro' );
		echo '</label> ';
		echo '<div>';
			echo '<input type="text" class="regular-text" id="ct_period_pro_video_url" name="ct_period_pro_video_url" value="' . esc_url( $video_url ) . '" />';
			echo ct_period_pro_green_checkmark_svg();
		echo '</div>';
	echo '</div>';

	// Display option
	if ( $post->post_type == 'post' ) :

		$display_blog = get_post_meta( $post->ID, 'ct_period_pro_video_display_key', true );

		if ( empty( $display_blog ) ) {
			$display_blog = "post";
		}

		echo '<div class="ct_period_pro_video_display_container">';
			echo '<p>' . esc_html__( 'Choose where to display the video:', 'period-pro' ) . '</p>';
			echo '<label for="ct_period_pro_video_display_post">';
				echo '<input type="radio" name="ct_period_pro_video_display" id="ct_period_pro_video_display_post" value="post" ' . checked( $display_blog, "post", false ) . '>';
				esc_html_e( 'Post', 'period-pro' );
			echo '</label> ';
			echo '<label for="ct_period_pro_video_display_blog">';
				echo '<input type="radio" name="ct_period_pro_video_display" id="ct_period_pro_video_display_blog" value="blog" ' . checked( $display_blog, "blog", false ) . '>';
				esc_html_e( 'Blog', 'period-pro' );
			echo '</label> ';
			echo '<label for="ct_period_pro_video_display_both">';
				echo '<input type="radio" name="ct_period_pro_video_display" id="ct_period_pro_video_display_both" value="both" ' . checked( $display_blog, "both", false ) . '>';
				esc_html_e( 'Post & Blog', 'period-pro' );
			echo '</label> ';
		echo '</div>';
	endif;

	// Youtube options

	$class = 'hide';

	if ( strpos( $video_url, 'youtube.com' ) || strpos( $video_url, 'youtu.be' ) ) {
		$class = '';
	}

	echo '<div class="ct_period_pro_video_youtube_controls_container ' . esc_attr( $class ) . '">';
		echo '<p>' . esc_html__( 'Youtube controls', 'period-pro' ) . '</p>';
		echo '<label for="ct_period_pro_video_youtube_logo">';
			echo '<input type="checkbox" name="ct_period_pro_video_youtube_logo" id="ct_period_pro_video_youtube_logo" value="1" ' . checked( '1', $youtube_logo, false ) . '>';
			esc_html_e( 'Hide Youtube logo in control bar', 'period-pro' );
		echo '</label> ';
		echo '<label for="ct_period_pro_video_youtube_captions">';
			echo '<input type="checkbox" name="ct_period_pro_video_youtube_captions" id="ct_period_pro_video_youtube_captions" value="1" ' . checked( '1', $youtube_captions, false ) . '>';
			esc_html_e( 'Show Captions by Default', 'period-pro' );
		echo '</label> ';
		echo '<label for="ct_period_pro_video_youtube_autoplay">';
			echo '<input type="checkbox" name="ct_period_pro_video_youtube_autoplay" id="ct_period_pro_video_youtube_autoplay" value="1" ' . checked( '1', $youtube_autoplay, false ) . '>';
			esc_html_e( 'Autoplay video', 'period-pro' );
		echo '</label> ';
		echo '<label for="ct_period_pro_video_youtube_loop">';
			echo '<input type="checkbox" name="ct_period_pro_video_youtube_loop" id="ct_period_pro_video_youtube_loop" value="1" ' . checked( '1', $youtube_loop, false ) . '>';
			esc_html_e( 'Loop video', 'period-pro' );
		echo '</label> ';
		echo '<label for="ct_period_pro_video_youtube_mute">';
			echo '<input type="checkbox" name="ct_period_pro_video_youtube_mute" id="ct_period_pro_video_youtube_mute" value="1" ' . checked( '1', $youtube_mute, false ) . '>';
			esc_html_e( 'Auto-mute video', 'period-pro' );
		echo '</label> ';
	echo '</div>';
}

//----------------------------------------------------------------------------------
// Ajax callback
//----------------------------------------------------------------------------------
function ct_period_pro_add_oembed_callback() {

	global $wpdb, $post;  // $wpdb - access to the database

	// get the video url passed from the JS (validate user input right away)
	$video_url = esc_url_raw( $_POST['videoURL'] );

	if ( strpos( $video_url, 'youtube-nocookie.com' ) !== false ) {
		$video = '<iframe src="'. esc_url( $video_url ) .'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
	} else {
		$video = ct_period_pro_output_video( $video_url );
	}

	$response = $video ? $video : '';

	echo $response;

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_add_oembed', 'ct_period_pro_add_oembed_callback' );

//----------------------------------------------------------------------------------
// Output the video in the back-end
//----------------------------------------------------------------------------------
function ct_period_pro_output_video( $video_url ) {
	
	if ( $video_url ) {
		$filetype = wp_check_filetype( $video_url );
		$filetype = $filetype['type'];
		if ( $filetype == 'audio/mpeg' ) {
			return do_shortcode('[audio mp3=' . $video_url . ']');
		} else if ( $filetype == 'video/mp4' ) {
			return do_shortcode('[video mp4=' . $video_url . ']');
		} else {
			return wp_oembed_get( esc_url( $video_url ) );
		}
	}
}

function ct_period_pro_video_save_data( $post_id ) {

	global $post;

	if ( ! isset( $_POST['ct_period_pro_video_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['ct_period_pro_video_nonce'], 'ct_period_pro_video' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* safe to save the data now. */

	if ( isset( $_POST['ct_period_pro_video_url'] ) ) {

		$video_url = esc_url_raw( $_POST['ct_period_pro_video_url'] );

		update_post_meta( $post_id, 'ct_period_pro_video_key', $video_url );

		// save display option for posts only
		if ( $post->post_type == 'post' ) {

			if ( isset( $_POST['ct_period_pro_video_display'] ) ) {

				$display_blog = esc_attr( $_POST['ct_period_pro_video_display'] );

				if ( $display_blog == 'post' || $display_blog == 'blog' || $display_blog == 'both' ) {
					update_post_meta( $post_id, 'ct_period_pro_video_display_key', $display_blog );
				}
			}
		}
	}

	$youtube_IDs = array(
		'ct_period_pro_video_youtube_logo',
		'ct_period_pro_video_youtube_captions',
		'ct_period_pro_video_youtube_autoplay',
		'ct_period_pro_video_youtube_loop',
		'ct_period_pro_video_youtube_mute'
	);

	foreach ( $youtube_IDs as $youtube_option ) {

		if ( ! isset( $_POST[ $youtube_option ] ) ) {
			$_POST[ $youtube_option ] = '0';
		}
		$youtube_option_data = $_POST[ $youtube_option ];

		if ( $youtube_option_data == '1' || $youtube_option_data == '0' ) {
			update_post_meta( $post_id, $youtube_option, $youtube_option_data );
		}
	}
}
add_action( 'pre_post_update', 'ct_period_pro_video_save_data' );

// green checkmark icon used in Post Video input
function ct_period_pro_green_checkmark_svg() {

	$svg = '<svg width="12px" height="13px" viewBox="0 0 12 13" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<desc>green checkmark icon</desc>
				<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				    <path d="M12.0000143,5.99999404 C12.0000143,2.68749009 9.3125111,-1.31130219e-05 6.00000715,-1.31130219e-05 C2.6875032,-1.31130219e-05 0,2.68749009 0,5.99999404 C0,9.31249799 2.6875032,12.0000012 6.00000715,12.0000012 C9.3125111,12.0000012 12.0000143,9.31249799 12.0000143,5.99999404 Z M10.031262,4.73436753 C10.031262,4.86718019 9.9843869,4.99218034 9.89063679,5.08593045 L5.64844423,9.32812301 C5.55469412,9.42187312 5.42188146,9.47656068 5.28906881,9.47656068 C5.16406866,9.47656068 5.031256,9.42187312 4.93750589,9.32812301 L2.10937751,6.49999464 C2.0156274,6.40624452 1.96875235,6.28124437 1.96875235,6.14843172 C1.96875235,6.01561906 2.0156274,5.8828064 2.10937751,5.78905629 L2.82031586,5.08593045 C2.91406597,4.99218034 3.03906612,4.93749277 3.17187878,4.93749277 C3.30469144,4.93749277 3.42969159,4.99218034 3.5234417,5.08593045 L5.28906881,6.85155755 L8.4765726,3.67186626 C8.57032272,3.57811615 8.69532287,3.52342859 8.82813552,3.52342859 C8.96094818,3.52342859 9.08594833,3.57811615 9.17969844,3.67186626 L9.89063679,4.3749921 C9.9843869,4.46874221 10.031262,4.60155487 10.031262,4.73436753 Z" fill="#43C591"></path>
				</g>
			</svg>';

	return $svg;
}

// loading indicator used in Post Video input
function ct_period_pro_loading_indicator_svg() {

	$svg = '<svg width="47px" height="50px" viewBox="0 0 47 50" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			    <desc>loading icon</desc>
			    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			        <path d="M14.9464464,39.2142788 C14.9464464,36.8035617 12.9877387,34.8749879 10.6071555,34.8749879 C8.19643834,34.8749879 6.26786461,36.8035617 6.26786461,39.2142788 C6.26786461,41.624996 8.19643834,43.5535697 10.6071555,43.5535697 C12.9877387,43.5535697 14.9464464,41.624996 14.9464464,39.2142788 Z M27.9643191,45 C27.9643191,42.8604885 26.2466831,41.1428525 24.1071716,41.1428525 C21.9676601,41.1428525 20.2500241,42.8604885 20.2500241,45 C20.2500241,47.1395115 21.9676601,48.8571475 24.1071716,48.8571475 C26.2466831,48.8571475 27.9643191,47.1395115 27.9643191,45 Z M9.64286864,25.7142627 C9.64286864,23.0624738 7.47322319,20.8928284 4.82143432,20.8928284 C2.16964544,20.8928284 0,23.0624738 0,25.7142627 C0,28.3660516 2.16964544,30.535697 4.82143432,30.535697 C7.47322319,30.535697 9.64286864,28.3660516 9.64286864,25.7142627 Z M40.9821917,39.2142788 C40.9821917,37.345973 39.4754935,35.8392748 37.6071877,35.8392748 C35.7388819,35.8392748 34.2321837,37.345973 34.2321837,39.2142788 C34.2321837,41.0825846 35.7388819,42.5892828 37.6071877,42.5892828 C39.4754935,42.5892828 40.9821917,41.0825846 40.9821917,39.2142788 Z M15.9107333,12.2142466 C15.9107333,9.29125207 13.5301501,6.91066888 10.6071555,6.91066888 C7.68416095,6.91066888 5.30357775,9.29125207 5.30357775,12.2142466 C5.30357775,15.1372412 7.68416095,17.5178244 10.6071555,17.5178244 C13.5301501,17.5178244 15.9107333,15.1372412 15.9107333,12.2142466 Z M29.8928928,6.42852545 C29.8928928,3.23432521 27.3013718,0.642804265 24.1071716,0.642804265 C20.9129714,0.642804265 18.3214504,3.23432521 18.3214504,6.42852545 C18.3214504,9.62272568 20.9129714,12.2142466 24.1071716,12.2142466 C27.3013718,12.2142466 29.8928928,9.62272568 29.8928928,6.42852545 Z M46.2857695,25.7142627 C46.2857695,24.1171626 44.990009,22.8214021 43.3929089,22.8214021 C41.7958088,22.8214021 40.5000483,24.1171626 40.5000483,25.7142627 C40.5000483,27.3113628 41.7958088,28.6071233 43.3929089,28.6071233 C44.990009,28.6071233 46.2857695,27.3113628 46.2857695,25.7142627 Z M40.0179048,12.2142466 C40.0179048,10.8883522 38.9330821,9.80352947 37.6071877,9.80352947 C36.2812933,9.80352947 35.1964705,10.8883522 35.1964705,12.2142466 C35.1964705,13.5401411 36.2812933,14.6249638 37.6071877,14.6249638 C38.9330821,14.6249638 40.0179048,13.5401411 40.0179048,12.2142466 Z" fill="#FFFFFF"></path>
			    </g>
			</svg>';

	return $svg;
}

function ct_period_pro_output_featured_video( $featured_image ) {

	global $post;
	$featured_video = get_post_meta( $post->ID, 'ct_period_pro_video_key', true );

	if ( $featured_video ) {

		$display_blog = get_post_meta( $post->ID, 'ct_period_pro_video_display_key', true );

		if (
			( is_singular() && ( $display_blog == 'post' || $display_blog == 'both' ) )
			|| ( ( is_home() || is_archive() || is_search() ) && ( $display_blog == 'blog' || $display_blog == 'both' ) )
			|| is_singular( 'page' )
		) {
			if ( strpos( $featured_video, 'youtube-nocookie.com' ) !== false ) {
				$featured_image = '<div class="featured-video"><iframe src="'. esc_url( $featured_video ) .'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>';
			} else {
				$featured_image = '<div class="featured-video">' . ct_period_pro_output_video( $featured_video ) . '</div>';
			}
			// If AMP plugin is active and viewing an AMP page
			if ( function_exists('amp_init') ) {
				if ( is_amp_endpoint() ) {
					// Add filter to remove the Featured Image
					add_filter( 'amp_post_template_data', 'ct_period_pro_amp_remove_featured_image' );
				}
			}
		}
	}

	return $featured_image;
}
add_filter( 'ct_period_featured_image', 'ct_period_pro_output_featured_video' );

function ct_period_pro_featured_video_amp( $content ) {
	// If AMP plugin is active and viewing an AMP page
	if ( function_exists('amp_init') ) {
		if ( is_amp_endpoint() ) {
			// Add the Featured Video to the top of the content
			global $post;
			$featured_video = ct_period_pro_output_featured_video('');
			$content = $featured_video . $content;	
		}
	}
	return $content;
}
add_filter( 'the_content', 'ct_period_pro_featured_video_amp', 1 );

function ct_period_pro_amp_remove_featured_image( $data ) {
    $data['featured_image'] = false;
    return $data;
}

function ct_period_pro_add_youtube_parameters( $html, $url, $args ) {

	global $post;

	if ( ! empty( $post ) ) {
		$featured_video = get_post_meta( $post->ID, 'ct_period_pro_video_key', true );
	}

	if ( ! empty( $featured_video ) ) {

		// only run filter on the featured video
		if ( $url == $featured_video ) {

			// only add parameters if featured vid is a youtube vid
			if ( strpos( $featured_video, 'youtube.com' ) || strpos( $featured_video, 'youtu.be' ) ) {

				$youtube_logo    = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_logo', true );
				$youtube_captions = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_captions', true );
				$youtube_autoplay = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_autoplay', true );
				$youtube_loop     = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_loop', true );
				$youtube_mute     = get_post_meta( $post->ID, 'ct_period_pro_video_youtube_mute', true );

				$youtube_parameters = array(
					'modestbranding' => $youtube_logo,
					'cc_load_policy' => $youtube_captions,
					'autoplay'       => $youtube_autoplay,
					'loop'           => $youtube_loop,
					'mute'           => $youtube_mute
				);

				if ( $youtube_loop == 1 ) {
					$video_id = explode( 'v=', $featured_video );
					$video_id = $video_id[1];
					$youtube_parameters['playlist'] = $video_id;
				}

				$args       = is_array( $args ) ? array_merge( $args, $youtube_parameters ) : $youtube_parameters;
				$parameters = http_build_query( $args );
				$html       = str_replace( '?feature=oembed', '?feature=oembed&' . $parameters, $html );
			}
		}
	}

	return $html;
}
add_filter( 'oembed_result', 'ct_period_pro_add_youtube_parameters', 10, 3 );

wp_oembed_add_provider( '/https?:\/\/(.+)?(wistia.com|wi.st)\/(medias|embed)\/.*/', 'http://fast.wistia.com/oembed', true);