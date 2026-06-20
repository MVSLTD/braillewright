<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_features_add_sliders_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'braillewright_features_slider',
			esc_html__( 'Featured Slider', 'braillewright' ),
			'braillewright_features_slider_callback',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'braillewright_features_add_sliders_meta_box' );

function braillewright_features_slider_callback( $post ) {

	wp_nonce_field( 'braillewright_features_slider', 'braillewright_features_slider_nonce' );

	$slider_id = get_post_meta( $post->ID, 'braillewright_features_slider_key', true );

	if ( defined( 'META_SLIDER_ACTIVE' ) ) {

		// get all the meta sliders user has made
		$sliders = get_posts( array(
			'post_type'      => 'ml-slider',
			'posts_per_page' => - 1
		) );

		// if there are no sliders, link them to the creation page
		if ( empty( $sliders ) ) {
			$link = add_query_arg( 'page', 'metaslider', admin_url( 'admin.php' ) );
			echo wp_kses_post( '<p class="slider-notice"> ' . sprintf( __( "Looks like you don't have any Sliders yet. <a href='%s' target='_blank'>Click here</a> to create your first slider.", "braillewright" ), esc_url( $link ) ) . '</p>' );
		}

		// add dropdown for selecting a slider
		echo '<div class="braillewright_features_slider_input_container">';
			echo '<label for="braillewright_features_slider_selection">';
				esc_html_e( 'Choose a slider:', 'braillewright' );
			echo '</label> ';
			echo '<select id="braillewright_features_slider_selection" name="braillewright_features_slider_selection">';
				echo '<option value="select">' . esc_html__( "Select a slider", "braillewright" ) . '</option>';
				foreach ( $sliders as $slider ) {
					$title = $slider->post_title;
					$id    = $slider->ID;
					?>
					<option value="<?php echo absint( $id ); ?>" <?php if ( $id == $slider_id ) {
						echo 'selected';
					} ?>><?php echo esc_html( $title ); ?></option>
				<?php }
			echo '</select>';
			echo '<p><em> ' . esc_html__( "Recommended slider dimensions: 2x1", "braillewright" ) . '</em></p>';
		echo '</div>';

		// Display option
		if ( $post->post_type == 'post' ) :

			$display_blog = get_post_meta( $post->ID, 'braillewright_features_slider_display_key', true );

			if ( empty( $display_blog ) ) {
				$display_blog = "post";
			}

			// add radio buttons for post vs post and blog display
			echo '<div class="braillewright_features_slider_display_container">';
				echo '<p>' . esc_html__( 'Choose where to display the slider:', 'braillewright' ) . '</p>';
				echo '<label for="braillewright_features_slider_display_post">';
					echo '<input type="radio" name="braillewright_features_slider_display" id="braillewright_features_slider_display_post" value="post" ' . checked( $display_blog, "post", false ) . '>';
					esc_html_e( 'Post', 'braillewright' );
				echo '</label> ';
				echo '<label for="braillewright_features_slider_display_blog">';
					echo '<input type="radio" name="braillewright_features_slider_display" id="braillewright_features_slider_display_blog" value="blog" ' . checked( $display_blog, "blog", false ) . '>';
					esc_html_e( 'Blog', 'braillewright' );
				echo '</label> ';
				echo '<label for="braillewright_features_slider_display_both">';
					echo '<input type="radio" name="braillewright_features_slider_display" id="braillewright_features_slider_display_both" value="both" ' . checked( $display_blog, "both", false ) . '>';
					esc_html_e( 'Post & Blog', 'braillewright' );
				echo '</label> ';
			echo '</div>';
		endif;
	} else { // if Meta Slider is NOT currently activated

		// get installed plugins
		$plugins = get_plugins();

		// if Meta Slider is installed, but not active
		if ( array_key_exists( 'ml-slider/ml-slider.php', $plugins ) ) {
			$link_plugins = admin_url( 'plugins.php' );
			echo wp_kses_post( '<p class="slider-notice">' . sprintf( __( "Please activate Meta Slider from the <a href='%s'>Plugins menu</a>.", "braillewright" ), esc_url( $link_plugins ) ) );
		} else { // if not installed and not active
			echo '<div class="braillewright_features_slider_no_slider_container">';
			$link_ml_search = add_query_arg( array(
				'tab' => 'search',
				's'   => 'metaslider'
			), admin_url( 'plugin-install.php' ) );
			echo '<p class="slider-notice">' . esc_html__( "Featured Sliders require the Meta Slider plugin.", "braillewright" );
			echo wp_kses_post( ' ' . sprintf( __( "<a href='%s'>Click here</a> to find and install Meta Slider from the Plugins menu.", "braillewright" ), esc_url( $link_ml_search ) ) . '</p>' );
			echo '</div>';
		}
	}
}

function braillewright_features_slider_save_data( $post_id ) {

	global $post;

	if ( ! isset( $_POST['braillewright_features_slider_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['braillewright_features_slider_nonce'] ) ), 'braillewright_features_slider' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* safe to save the data now. */

	if ( isset( $_POST['braillewright_features_slider_selection'] ) ) {

		$slider = absint( $_POST['braillewright_features_slider_selection'] );

		update_post_meta( $post_id, 'braillewright_features_slider_key', $slider );

		// save display option for posts only
		if ( $post->post_type == 'post' ) {

			if ( isset( $_POST['braillewright_features_slider_display'] ) ) {

				$display_blog = sanitize_text_field( wp_unslash( $_POST['braillewright_features_slider_display'] ) );

				if ( $display_blog == 'post' || $display_blog == 'blog' || $display_blog == 'both' ) {
					update_post_meta( $post_id, 'braillewright_features_slider_display_key', $display_blog );
				}
			}
		}
	}
}
add_action( 'pre_post_update', 'braillewright_features_slider_save_data' );

function braillewright_features_output_featured_slider( $featured_image ) {

	if ( defined( 'META_SLIDER_ACTIVE' ) ) {

		global $post;

		$featured_slider = get_post_meta( $post->ID, 'braillewright_features_slider_key', true );

		if ( $featured_slider ) {

			$display_blog = get_post_meta( $post->ID, 'braillewright_features_slider_display_key', true );

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
add_filter( 'braillewright_featured_image', 'braillewright_features_output_featured_slider', 20 );

// replace all purchase links with affiliate link
function braillewright_features_metaslider_hoplink( $link ) {
	return $link;
}
add_filter( 'metaslider_hoplink', 'braillewright_features_metaslider_hoplink', 10, 1 );