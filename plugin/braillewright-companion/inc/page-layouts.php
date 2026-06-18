<?php
defined( 'ABSPATH' ) OR exit;

function ct_period_pro_add_post_layout_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'ct_period_pro_post_layout',
			esc_html__( 'Layout', 'period-pro' ),
			'ct_period_pro_post_layout_callback',
			$screen,
			'side'
		);
	}
}
add_action( 'add_meta_boxes', 'ct_period_pro_add_post_layout_meta_box' );

function ct_period_pro_post_layout_callback( $post ) {

	wp_nonce_field( 'ct_period_pro_post_layout', 'ct_period_pro_post_layout_nonce' );

	$layout = get_post_meta( $post->ID, 'ct_period_pro_post_layout_key', true );
	?>
	<p>
		<select name="period-pro-post-layout" id="period-pro-post-layout" style="box-sizing: border-box; width: 100%;">
			<option value="default"><?php esc_html_e( 'Use layout set in Customizer', 'period-pro' ); ?></option>
			<option value="right" <?php if ( $layout == 'right' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Right sidebar', 'period-pro' ); ?>
			</option>
			<option value="left" <?php if ( $layout == 'left' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Left sidebar', 'period-pro' ); ?>
			</option>
			<option value="narrow" <?php if ( $layout == 'narrow' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Narrow', 'period-pro' ); ?>
			</option>
			<option value="wide" <?php if ( $layout == 'wide' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Wide', 'period-pro' ); ?>
			</option>
		</select>
	</p> <?php
}

function ct_period_pro_post_layout_save_data( $post_id ) {

	if ( ! isset( $_POST['ct_period_pro_post_layout_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['ct_period_pro_post_layout_nonce'], 'ct_period_pro_post_layout' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* it's safe to save the data now. */

	if ( isset( $_POST['period-pro-post-layout'] ) ) {

		$layout = $_POST['period-pro-post-layout'];

		if ( in_array( $layout, ct_period_pro_layouts( 'page-layouts' ) ) ) {
			update_post_meta( $post_id, 'ct_period_pro_post_layout_key', $layout );
		}
	}
}
add_action( 'pre_post_update', 'ct_period_pro_post_layout_save_data' );

function ct_period_pro_filter_layout( $layout ) {

	if ( is_singular() ) {

		global $post;

		$page_layout = get_post_meta( $post->ID, 'ct_period_pro_post_layout_key', true );

		if ( ! empty( $page_layout ) && $page_layout != 'default' ) {
			$layout = $page_layout;
		}
	}

	return $layout;
}
add_filter( 'ct_period_pro_layout_filter', 'ct_period_pro_filter_layout' );