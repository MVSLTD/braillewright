<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_features_fi_size_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'braillewright_features_fi_size',
			esc_html__( 'Featured Image Size', 'braillewright' ),
			'braillewright_features_fi_size_callback',
			$screen,
			'side'
		);
	}
}
add_action( 'add_meta_boxes', 'braillewright_features_fi_size_meta_box' );

function braillewright_features_fi_size_callback( $post ) {

	wp_nonce_field( 'braillewright_features_fi_size', 'braillewright_features_fi_size_nonce' );

	$ratio = get_post_meta( $post->ID, 'braillewright_features_fi_size_key', true );
	?>
	<p>
		<select name="braillewright-pro-fi-size" id="braillewright-pro-fi-size" style="box-sizing: border-box; width: 100%;">
			<option value="default"><?php esc_html_e( 'Use size set in Customizer', 'braillewright' ); ?></option>
			<option value="2-1" <?php if ( $ratio == '2-1' ) {
				echo 'selected';
			} ?>>2:1
			</option>
			<option value="1-2" <?php if ( $ratio == '1-2' ) {
				echo 'selected';
			} ?>>1:2
			</option>
			<option value="16-9" <?php if ( $ratio == '16-9' ) {
				echo 'selected';
			} ?>>16:9
			</option>
			<option value="9-16" <?php if ( $ratio == '9-16' ) {
				echo 'selected';
			} ?>>9:16
			</option>
			<option value="3-2" <?php if ( $ratio == '3-2' ) {
				echo 'selected';
			} ?>>3:2
			</option>
			<option value="2-3" <?php if ( $ratio == '2-3' ) {
				echo 'selected';
			} ?>>2:3
			</option>
			<option value="4-3" <?php if ( $ratio == '4-3' ) {
				echo 'selected';
			} ?>>4:3
			</option>
			<option value="3-4" <?php if ( $ratio == '3-4' ) {
				echo 'selected';
			} ?>>3:4
			</option>
			<option value="5-4" <?php if ( $ratio == '5-4' ) {
				echo 'selected';
			} ?>>5:4
			</option>
			<option value="4-5" <?php if ( $ratio == '4-5' ) {
				echo 'selected';
			} ?>>4:5
			</option>
			<option value="1-1" <?php if ( $ratio == '1-1' ) {
				echo 'selected';
			} ?>>1:1
			</option>
			<option value="natural" <?php if ( $ratio == 'natural' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Natural Dimensions', 'braillewright' ); ?></option>
		</select>
	</p> <?php
}

function braillewright_features_fi_size_save_data( $post_id ) {

	if ( ! isset( $_POST['braillewright_features_fi_size_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['braillewright_features_fi_size_nonce'] ) ), 'braillewright_features_fi_size' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* it's safe to save the data now. */

	if ( isset( $_POST['braillewright-pro-fi-size'] ) ) {

		$ratio = sanitize_text_field( wp_unslash( $_POST['braillewright-pro-fi-size'] ) );

		if ( in_array( $ratio, braillewright_features_fi_size_array() ) ) {
			update_post_meta( $post_id, 'braillewright_features_fi_size_key', $ratio );
		}
	}
}
add_action( 'pre_post_update', 'braillewright_features_fi_size_save_data' );

function braillewright_features_featured_image_post_class( $classes ) {

	$size = get_theme_mod( 'featured_image_size' );
	$size = apply_filters( 'braillewright_features_fi_size_filter', $size );

	if ( ! empty( $size ) ) {
		$classes[] = 'ratio-' . esc_attr( $size );
	}

	return $classes;
}
add_filter( 'post_class', 'braillewright_features_featured_image_post_class' );

function braillewright_features_filter_fi_size( $size ) {

	global $post;

	$page_size = get_post_meta( $post->ID, 'braillewright_features_fi_size_key', true );

	if ( ! empty( $page_size ) && $page_size != 'default' ) {
		$size = $page_size;
	}

	return $size;
}
add_filter( 'braillewright_features_fi_size_filter', 'braillewright_features_filter_fi_size' );

function braillewright_features_fi_size_array() {
	$sizes = array(
		'default',
		'2-1',
		'1-2',
		'16-9',
		'9-16',
		'3-2',
		'2-3',
		'4-3',
		'3-4',
		'5-4',
		'4-5',
		'1-1',
		'natural'
	);

	return $sizes;
}