<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_add_sliders_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'ct_period_pro_slider',
			esc_html__( 'Featured Slider', 'period-pro' ),
			'ct_period_pro_slider_callback',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'ct_period_pro_add_sliders_meta_box' );

function ct_period_pro_slider_callback( $post ) {

	wp_nonce_field( 'ct_period_pro_slider', 'ct_period_pro_slider_nonce' );

	$slider_id = get_post_meta( $post->ID, 'ct_period_pro_slider_key', true );

	if ( defined( 'META_SLIDER_ACTIVE' ) ) {

		// get all the meta sliders user has made
		$sliders = get_posts( array(
			'post_type'      => 'ml-slider',
			'posts_per_page' => - 1
		) );

		// if there are no sliders, link them to the creation page
		if ( empty( $sliders ) ) {
			$link = add_query_arg( 'page', 'metaslider', admin_url( 'admin.php' ) );
			echo '<p class="slider-notice"> ' . sprintf( __( "Looks like you don't have any Sliders yet. <a href='%s' target='_blank'>Click here</a> to create your first slider.", "period-pro" ), esc_url( $link ) ) . '</p>';
		}

		// add dropdown for selecting a slider
		echo '<div class="ct_period_pro_slider_input_container">';
			echo '<label for="ct_period_pro_slider_selection">';
				esc_html_e( 'Choose a slider:', 'period-pro' );
			echo '</label> ';
			echo '<select id="ct_period_pro_slider_selection" name="ct_period_pro_slider_selection">';
				echo '<option value="select">' . esc_html__( "Select a slider", "period-pro" ) . '</option>';
				foreach ( $sliders as $slider ) {
					$title = $slider->post_title;
					$id    = $slider->ID;
					?>
					<option value="<?php echo absint( $id ); ?>" <?php if ( $id == $slider_id ) {
						echo 'selected';
					} ?>><?php echo sanitize_text_field( $title ); ?></option>
				<?php }
			echo '</select>';
			echo '<p><em> ' . esc_html__( "Recommended slider dimensions: 2x1", "period-pro" ) . '</em></p>';
		echo '</div>';

		// Display option
		if ( $post->post_type == 'post' ) :

			$display_blog = get_post_meta( $post->ID, 'ct_period_pro_slider_display_key', true );

			if ( empty( $display_blog ) ) {
				$display_blog = "post";
			}

			// add radio buttons for post vs post and blog display
			echo '<div class="ct_period_pro_slider_display_container">';
				echo '<p>' . esc_html__( 'Choose where to display the slider:', 'period-pro' ) . '</p>';
				echo '<label for="ct_period_pro_slider_display_post">';
					echo '<input type="radio" name="ct_period_pro_slider_display" id="ct_period_pro_slider_display_post" value="post" ' . checked( $display_blog, "post", false ) . '>';
					esc_html_e( 'Post', 'period-pro' );
				echo '</label> ';
				echo '<label for="ct_period_pro_slider_display_blog">';
					echo '<input type="radio" name="ct_period_pro_slider_display" id="ct_period_pro_slider_display_blog" value="blog" ' . checked( $display_blog, "blog", false ) . '>';
					esc_html_e( 'Blog', 'period-pro' );
				echo '</label> ';
				echo '<label for="ct_period_pro_slider_display_both">';
					echo '<input type="radio" name="ct_period_pro_slider_display" id="ct_period_pro_slider_display_both" value="both" ' . checked( $display_blog, "both", false ) . '>';
					esc_html_e( 'Post & Blog', 'period-pro' );
				echo '</label> ';
			echo '</div>';
		endif;
	} else { // if Meta Slider is NOT currently activated

		// get installed plugins
		$plugins = get_plugins();

		// if Meta Slider is installed, but not active
		if ( array_key_exists( 'ml-slider/ml-slider.php', $plugins ) ) {
			$link_plugins = admin_url( 'plugins.php' );
			echo '<p class="slider-notice">' . sprintf( __( "Please activate Meta Slider from the <a href='%s'>Plugins menu</a>.", "period-pro" ), esc_url( $link_plugins ) );
		} else { // if not installed and not active
			echo '<div class="ct_period_pro_slider_no_slider_container">';
			$link_ml_search = add_query_arg( array(
				'tab' => 'search',
				's'   => 'metaslider'
			), admin_url( 'plugin-install.php' ) );
			echo '<p class="slider-notice">' . esc_html__( "Featured Sliders require the Meta Slider plugin.", "period-pro" );
			echo ' ' . sprintf( __( "<a href='%s'>Click here</a> to find and install Meta Slider from the Plugins menu.", "period-pro" ), esc_url( $link_ml_search ) ) . '</p>';
			echo '</div>';
		}
	}
}

function ct_period_pro_slider_save_data( $post_id ) {

	global $post;

	if ( ! isset( $_POST['ct_period_pro_slider_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['ct_period_pro_slider_nonce'], 'ct_period_pro_slider' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* safe to save the data now. */

	if ( isset( $_POST['ct_period_pro_slider_selection'] ) ) {

		$slider = absint( $_POST['ct_period_pro_slider_selection'] );

		update_post_meta( $post_id, 'ct_period_pro_slider_key', $slider );

		// save display option for posts only
		if ( $post->post_type == 'post' ) {

			if ( isset( $_POST['ct_period_pro_slider_display'] ) ) {

				$display_blog = $_POST['ct_period_pro_slider_display'];

				if ( $display_blog == 'post' || $display_blog == 'blog' || $display_blog == 'both' ) {
					update_post_meta( $post_id, 'ct_period_pro_slider_display_key', $display_blog );
				}
			}
		}
	}
}
add_action( 'pre_post_update', 'ct_period_pro_slider_save_data' );

function ct_period_pro_output_featured_slider( $featured_image ) {

	if ( defined( 'META_SLIDER_ACTIVE' ) ) {

		global $post;

		$featured_slider = get_post_meta( $post->ID, 'ct_period_pro_slider_key', true );

		if ( $featured_slider ) {

			$display_blog = get_post_meta( $post->ID, 'ct_period_pro_slider_display_key', true );

			if (
				( is_singular() && ( $display_blog == 'post' || $display_blog == 'both' ) )
				|| ( ( is_home() || is_archive() || is_search() ) && ( $display_blog == 'blog' || $display_blog == 'both' ) )
				|| is_singular( 'page' )
			) {

				$featured_image = '<div class="featured-slider featured-image">';

				// output shortcode using ID => [metaslider id=1927]
				$featured_image .= do_shortcode( '[metaslider id=' . absint( $featured_slider ) . ']' );

				$featured_image .= '</div>';
			}
		}
	}

	return $featured_image;
}
add_filter( 'ct_period_featured_image', 'ct_period_pro_output_featured_slider', 20 );

// replace all purchase links with affiliate link
function ct_period_pro_metaslider_hoplink( $link ) {
	return "http://link.competethemes.com/meta-slider-period-pro";
}
add_filter( 'metaslider_hoplink', 'ct_period_pro_metaslider_hoplink', 10, 1 );