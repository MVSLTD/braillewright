<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_features_add_post_layout_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'braillewright_features_post_layout',
			esc_html__( 'Layout', 'braillewright' ),
			'braillewright_features_post_layout_callback',
			$screen,
			'side'
		);
	}
}
add_action( 'add_meta_boxes', 'braillewright_features_add_post_layout_meta_box' );

function braillewright_features_post_layout_callback( $post ) {

	wp_nonce_field( 'braillewright_features_post_layout', 'braillewright_features_post_layout_nonce' );

	$layout = get_post_meta( $post->ID, 'braillewright_features_post_layout_key', true );
	?>
	<p>
		<select name="braillewright-pro-post-layout" id="braillewright-pro-post-layout" style="box-sizing: border-box; width: 100%;">
			<option value="default"><?php esc_html_e( 'Use layout set in Customizer', 'braillewright' ); ?></option>
			<option value="right" <?php if ( $layout == 'right' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Right sidebar', 'braillewright' ); ?>
			</option>
			<option value="left" <?php if ( $layout == 'left' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Left sidebar', 'braillewright' ); ?>
			</option>
			<option value="narrow" <?php if ( $layout == 'narrow' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Narrow', 'braillewright' ); ?>
			</option>
			<option value="wide" <?php if ( $layout == 'wide' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Wide', 'braillewright' ); ?>
			</option>
		</select>
	</p> <?php
}

function braillewright_features_post_layout_save_data( $post_id ) {

	if ( ! isset( $_POST['braillewright_features_post_layout_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['braillewright_features_post_layout_nonce'] ) ), 'braillewright_features_post_layout' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* it's safe to save the data now. */

	if ( isset( $_POST['braillewright-pro-post-layout'] ) ) {

		$layout = sanitize_text_field( wp_unslash( $_POST['braillewright-pro-post-layout'] ) );

		if ( in_array( $layout, braillewright_features_layouts( 'page-layouts' ) ) ) {
			update_post_meta( $post_id, 'braillewright_features_post_layout_key', $layout );
		}
	}
}
add_action( 'pre_post_update', 'braillewright_features_post_layout_save_data' );

function braillewright_features_filter_layout( $layout ) {

	if ( is_singular() ) {

		global $post;

		$page_layout = get_post_meta( $post->ID, 'braillewright_features_post_layout_key', true );

		if ( ! empty( $page_layout ) && $page_layout != 'default' ) {
			$layout = $page_layout;
		}
	}

	return $layout;
}
add_filter( 'braillewright_features_layout_filter', 'braillewright_features_filter_layout' );