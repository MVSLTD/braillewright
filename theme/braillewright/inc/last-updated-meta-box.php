<?php
defined( 'ABSPATH' ) OR exit;

function braillewright_last_updated_meta_box() {

	$screens = array( 'post' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'braillewright_last_updated',
			esc_html__( 'Last Updated Date', 'braillewright' ),
			'braillewright_last_updated_callback',
			$screen,
			'side'
		);
	}
}
add_action( 'add_meta_boxes', 'braillewright_last_updated_meta_box' );

function braillewright_last_updated_callback( $post ) {

  wp_nonce_field( 'braillewright_last_updated', 'braillewright_last_updated_nonce' );
  $display = get_post_meta( $post->ID, 'braillewright_last_updated', true );

  ?>
	<p>
		<select name="braillewright-last-updated" id="braillewright-last-updated" style="box-sizing: border-box; width: 100%;">
			<option value="default"><?php esc_html_e( 'Use Customizer setting', 'braillewright' ); ?></option>
			<option value="yes" <?php if ( $display == 'yes' ) {
				echo 'selected';
			} ?>><?php esc_html_e( 'Show the date', 'braillewright' ); ?>
			</option>
			<option value="no" <?php if ( $display == 'no' ) {
				echo 'selected';
			} ?>><?php esc_html_e( "Don't show the date", 'braillewright' ); ?>
			</option>
		</select>
	</p> <?php
}
function braillewright_last_updated_save_data( $post_id ) {

	global $post;

	if ( ! isset( $_POST['braillewright_last_updated_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['braillewright_last_updated_nonce'] ) ), 'braillewright_last_updated' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* it's safe to save the data now. */

	if ( isset( $_POST['braillewright-last-updated'] ) ) {

    $display = sanitize_text_field( wp_unslash( $_POST['braillewright-last-updated'] ) );
    $accepted_values = array('default', 'yes', 'no');

		if ( in_array( $display, $accepted_values ) ) {
			update_post_meta( $post_id, 'braillewright_last_updated', $display );
		}
	}
}
add_action( 'pre_post_update', 'braillewright_last_updated_save_data' );