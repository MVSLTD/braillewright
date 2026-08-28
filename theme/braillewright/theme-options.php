<?php

function braillewright_register_theme_page() {
	add_theme_page(
		sprintf( esc_html__( '%s Dashboard', 'braillewright' ), wp_get_theme() ),
		sprintf( esc_html__( '%s Dashboard', 'braillewright' ), wp_get_theme() ),
		'edit_theme_options',
		'braillewright-options',
		'braillewright_options_content'
	);
}
add_action( 'admin_menu', 'braillewright_register_theme_page' );

function braillewright_options_content() {
	?>
	<div id="braillewright-dashboard-wrap" class="wrap braillewright-dashboard-wrap">
		<img class="braillewright-logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/braillewright-logo.png' ); ?>" alt="<?php echo esc_attr__( 'Logo for the Braillewright WordPress Theme featuring a stylized black fountain pen with a gold nib, wrapped in a looping gold flourish with sparkles, dancing above the word Braillewright in large black serif lettering on a white background.', 'braillewright' ); ?>" style="max-width:220px;height:auto;display:block;margin:0 0 16px;">
		<h2><?php printf( esc_html__( '%s Dashboard', 'braillewright' ), esc_html( (string) wp_get_theme() ) ); ?></h2>
		<p class="braillewright-credit"><?php esc_html_e( 'Braillewright is created and maintained by Aaron Di Blasi of Mind Vault Solutions, Ltd. on behalf of Top Tech Tidbits, with engineering support from Claude Code.', 'braillewright' ); ?></p>
		<?php do_action( 'theme_options_before' ); ?>
		<div class="main">
			<?php if ( function_exists( 'braillewright_features_init' ) ) : ?>
			<div class="thanks-upgrading" style="background-image: url(<?php echo esc_url( trailingslashit( get_template_directory_uri() ) . 'assets/images/bg-texture.png' ); ?>)">
				<h3>Thanks for upgrading!</h3>
				<p>You can find the new features in the Customizer</p>
			</div>
			<?php endif; ?>
		</div>
		<div class="sidebar">
			<div class="dashboard-widget">
				<h4>More Amazing Resources</h4>
				<ul>
					<li><a href="https://github.com/MVSLTD/braillewright/issues" target="_blank" rel="noopener noreferrer">Support (GitHub Issues)</a></li>
					<li><a href="https://github.com/MVSLTD/braillewright/releases" target="_blank" rel="noopener noreferrer">Changelog (GitHub Releases)</a></li>
				</ul>
			</div>
			<div class="dashboard-widget">
				<h4>User Reviews</h4>
				<img src="<?php echo esc_url( trailingslashit( get_template_directory_uri() ) . 'assets/images/reviews.png' ); ?>" />
				<p>Braillewright is maintained in-house. See the GitHub repository for the changelog and to file issues.</p>
			</div>
			<div class="dashboard-widget">
				<h4>Reset Customizer Settings</h4>
				<p><b>Warning:</b> Clicking this buttin will erase the Braillewright theme's current settings in the Customizer.</p>
				<form method="post">
					<input type="hidden" name="braillewright_reset_customizer" value="braillewright_reset_customizer_settings"/>
					<p>
						<?php wp_nonce_field( 'braillewright_reset_customizer_nonce', 'braillewright_reset_customizer_nonce' ); ?>
						<?php submit_button( 'Reset Customizer Settings', 'delete', 'delete', false ); ?>
					</p>
				</form>
			</div>
		</div>
		<?php do_action( 'theme_options_after' ); ?>
	</div>
	<?php
}
