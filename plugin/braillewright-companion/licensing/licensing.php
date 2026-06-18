<?php

// add input for saving and activating license to Period Dashboard
function ct_period_pro_add_licensing_form() {
	$license 		= get_option( 'ct_period_pro_license_key' );
	$status 		= get_option( 'ct_period_pro_license_key_status' );
	$expires 		= get_option( 'ct_period_pro_license_key_expires' );
	$date_class = 'not-expired';
	// If there's a saved license
	if ( false !== $license ) {
		// Check if the license has now expired and add expired class if it has
		if ( !empty($expires) && $expires != 'lifetime' ) {
			if ( new DateTime( $expires ) < new DateTime() ) {
				$date_class = 'expired';
			}
		}
	}
	$updates_url = admin_url( 'update-core.php' );
	?>
	<div class="license-container">
		<form method="post" action="options.php">
			<?php settings_fields('ct_period_pro_plugin_license'); ?>
			<h3><?php esc_html_e('Get updates for Period Pro', 'period-pro'); ?></h3>
			<?php if( $status !== false && $status == 'valid' ) : ?>
				<p><?php printf( __('Your license key has been activated! You can now update Period Pro automatically through the <a href="%s">Updates</a> menu.', 'period-pro'), esc_url( $updates_url ) ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e('Activate a license key to receive Period Pro updates', 'period-pro'); ?>.</p>
			<?php endif; ?>
			<table class="form-table">
				<tbody>
				<tr valign="top" class="license-key">
					<th scope="row" valign="top">
						<?php esc_html_e('License Key','period-pro'); ?>
					</th>
					<td>
						<?php if( $status !== false && $status == 'valid' ) : ?>
							<span class="checkmark dashicons-before dashicons-yes-alt"></span>
						<?php endif;?>
						<input id="ct_period_pro_license_key" name="ct_period_pro_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" placeholder="c39b426c0c9bcd6a41f5a450e513de10" />
					</td>
				</tr>
				<tr valign="top" class="activate-deactivate">
					<?php if( $status !== false && $status == 'valid' ) { ?>
						<th scope="row" valign="top">
							<?php esc_html_e('Deactivate License Key','period-pro'); ?>
						</th>
						<td>
							<input type="submit" class="button-secondary" name="ct_period_pro_license_deactivate" value="<?php esc_html_e('Deactivate License','period-pro'); ?>"/>
						</td>
					<?php } else { ?>
						<th scope="row" valign="top">
							<?php esc_html_e('Activate License Key','period-pro'); ?>
						</th>
						<td>
							<input type="submit" class="button-secondary" name="ct_period_pro_license_activate" value="<?php esc_html_e('Activate License','period-pro'); ?>"/>
						</td>
					<?php } ?>
				</tr>
				<?php if( false !== $status ) { ?>
					<tr valign="top" class="expiration">
						<th scope="row" valign="top">
							<?php esc_html_e('Expiration Date','period-pro'); ?>
						</th>
						<td>
							<p class="expires <?php echo esc_attr( $date_class ); ?>">
								<span><?php echo mysql2date( 'F j, Y', $expires ) ; ?></span>
								<?php if ( $date_class == 'expired' ) {
									$url = 'https://www.competethemes.com/checkout/?edd_license_key=' . esc_attr( $license ) . '&download_id=26574'; ?>
									<span><a target="_blank" href="<?php echo esc_url( $url ); ?>"> <?php esc_html_e('Renew license','period-pro'); ?></a></span>
								<?php } ?>
								<input type="submit" class="button-secondary" name="ct_period_pro_update_license_expiration" value="&#8635; Refresh date"/>
							</p>
							
						</td>
					</tr>
				<?php } ?>	
				<?php wp_nonce_field( 'ct_period_pro_nonce', 'ct_period_pro_nonce' ); ?>
				</tbody>
			</table>
		</form>
	</div>
<?php
}
add_action('theme_options_before', 'ct_period_pro_add_licensing_form');

// register the new option for storing the license key
function ct_period_pro_register_option() {
	// creates our settings in the options table
	register_setting('ct_period_pro_plugin_license', 'ct_period_pro_license_key', 'ct_period_pro_sanitize_license' );
}
add_action('admin_init', 'ct_period_pro_register_option');

/***** Running the Updater *****/

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'EDD_SL_STORE_URL', 'https://www.competethemes.com' );

// the name of your product. This should match the download name in EDD exactly
define( 'EDD_SL_ITEM_NAME', 'Period Pro' );

// the name of the settings page for the license input to be displayed
define( 'EDD_SL_LICENSE_PAGE', 'period-options' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( PERIOD_PRO_PATH . 'licensing/EDD_SL_Plugin_Updater.php' );
}

function ct_period_pro_plugin_updater() 
{
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}
	// retrieve our license key from the DB
	$license_key = trim( get_option( 'ct_period_pro_license_key' ) );
	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( EDD_SL_STORE_URL, PERIOD_PRO_FILE, array(
			'version'   => '1.16',        // current version number
			'license'   => $license_key,    // license key (used get_option above to retrieve from DB)
			'item_name' => EDD_SL_ITEM_NAME,    // name of this plugin
			'author'    => 'Compete Themes',  // author of this plugin
			'beta'			=> false
		)
	);
}
add_action( 'admin_init', 'ct_period_pro_plugin_updater', 0 );

/***** Licensing functions *****/

/***********************************************
 * Gets rid of the local license status option
 * when adding a new one
 ***********************************************/

function ct_period_pro_sanitize_license( $new ) {
	$old = get_option( 'ct_period_pro_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'ct_period_pro_license_key_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

/***********************************************
 * Illustrates how to activate a license key.
 ***********************************************/

function ct_period_pro_activate_license() {

	if( isset( $_POST['ct_period_pro_license_activate'] ) ) {

		if( ! check_admin_referer( 'ct_period_pro_nonce', 'ct_period_pro_nonce' ) )
			return; // get out if we didn't click the Activate button

		if ( isset( $_POST['ct_period_pro_license_key'] ) ) {
			$license = trim( $_POST['ct_period_pro_license_key'] );		
		} else {
			$license = trim( get_option( 'ct_period_pro_license_key' ) );
		}
	
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( EDD_SL_ITEM_NAME ),
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SL_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'period-pro' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$url = 'https://www.competethemes.com/checkout/?edd_license_key=' . esc_attr( $license ) . '&download_id=26574';
						$message = sprintf(
							__( 'Your license key expired on %1$s. <a target="_blank" href=%2$s>Please renew here</a>.', 'period-pro' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ),
							$url
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.', 'period-pro' );
						break;

					case 'missing' :

						$message = __( 'Please enter a license key.', 'period-pro' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.', 'period-pro' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'period-pro' ), EDD_SL_ITEM_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.', 'period-pro' );
						break;

					default :

						$message = __( 'An error occurred, please try again.', 'period-pro' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'themes.php?page=' . EDD_SL_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'ct_period_pro_license_key', $license );
		update_option( 'ct_period_pro_license_key_status', $license_data->license );
		update_option( 'ct_period_pro_license_key_expires', $license_data->expires );
		wp_redirect( admin_url( 'themes.php?page=' . EDD_SL_LICENSE_PAGE ) );
		exit();
	}
}
add_action('admin_init', 'ct_period_pro_activate_license');

/***********************************************
 * Illustrates how to deactivate a license key.
 * This will decrease the site count
 ***********************************************/

function ct_period_pro_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['ct_period_pro_license_deactivate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'ct_period_pro_nonce', 'ct_period_pro_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'ct_period_pro_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_SL_ITEM_NAME ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SL_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'period-pro' );
			}

			$base_url = admin_url( 'themes.php?page=' . EDD_SL_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' || $license_data->license == 'failed' ) {
			delete_option( 'ct_period_pro_license_key_status' );
		}

		wp_redirect( admin_url( 'themes.php?page=' . EDD_SL_LICENSE_PAGE ) );
		exit();
	}
}
add_action('admin_init', 'ct_period_pro_deactivate_license');

function ct_period_pro_update_license_expiration( $manual ) {

	if ( $manual || isset( $_POST['ct_period_pro_update_license_expiration'] ) ) {

		// get out if we didn't click the button
		if ( ! check_admin_referer( 'ct_period_pro_nonce', 'ct_period_pro_nonce' ) ) return; 

		global $wp_version;

		$license = trim( get_option( 'ct_period_pro_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( EDD_SL_ITEM_NAME ),
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SL_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// ["expires"]=> string(19) "2030-01-27 23:59:59"
		update_option( 'ct_period_pro_license_key_expires', $license_data->expires );
	}
}
add_action('admin_init', 'ct_period_pro_update_license_expiration');

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function ct_period_pro_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;
		}
	}
}
add_action( 'admin_notices', 'ct_period_pro_admin_notices' );