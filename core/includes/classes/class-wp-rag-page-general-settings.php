<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Page_GeneralSettings
 *
 * This class handles rendering of the general settings page.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Page_GeneralSettings
 * @author      Kashima, Kazuo
 * @since       0.0.1
 */
class Wp_Rag_Page_GeneralSettings {
	const OPTION_NAME = 'wp_rag_options';
	const REGISTRATION_OPTION_NAME = 'wp_rag_registration';

	public function enqueue_scripts_and_styles() {
		wp_enqueue_script(
			'wp-rag-admin-clipboard',
			plugins_url( 'core/includes/assets/js/admin-clipboard.js', WPRAG_PLUGIN_FILE ),
			array( 'jquery' ),
			WPRAG_VERSION,
			true
		);

		wp_enqueue_style(
			'wp-rag-admin-notices',
			plugins_url( 'core/includes/assets/css/admin-clipboard.css', WPRAG_PLUGIN_FILE ),
			array(),
			WPRAG_VERSION
		);

	}

	/**
	 * Process registration form submission
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function process_registration( $input ) {
		$premium_api_key = isset( $input['premium_api_key'] ) ? trim( $input['premium_api_key'] ) : '';

		$auth_data = WPRAG()->helpers->get_auth_data();

		if ( empty( $auth_data['site_id'] ) ) {
			// New site registration - POST to /api/sites
			if ( $premium_api_key ) {
				$result = WPRAG()->helpers->register_site( $premium_api_key );
				if ( $result ) {
					WPRAG()->helpers->update_auth_data( 'premium_api_key', $premium_api_key );
				} else {
					// Registration with premium API key failed
					// IMPORTANT: Return false to prevent WordPress from processing further
					return false;
				}
			} else {
				$result = WPRAG()->helpers->register_site();
				if ( ! $result ) {
					// Registration failed
					return false;
				}
			}
		} else {
			// Existing site - PUT to /api/sites/{id}
			if ( $premium_api_key && empty( $auth_data['premium_api_key'] ) ) {
				// Upgrade to premium
				$api_path = '/';
				$data = array(
					'url' => get_site_url(),
					'premium_api_key' => $premium_api_key
				);

				$response = WPRAG()->helpers->call_api_for_site( $api_path, 'PUT', $data );

				if ( 200 === $response['httpCode'] ) {
					WPRAG()->helpers->update_auth_data( 'premium_api_key', $premium_api_key );
				} else {
					$messages = Wp_Rag_AdminMessages::get_instance();
					$messages->add_message_with_inline_details(
						'Failed to upgrade to premium.',
						$response,
						'error'
					);
					return false;
				}
			} elseif ( empty( $auth_data['verified_at'] ) && empty( $premium_api_key ) ) {
				// Start verification for existing unverified site
				$result = WPRAG()->helpers->start_site_verification( $auth_data['site_id'] );
				if ( ! $result ) {
					// Starting verification failed
					return false;
				}
			}
		}

		// Don't save the premium_api_key in options
		return array();
	}

	/**
	 * Process configuration form submission
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function save_config_api( $input ) {
		$sanitized_input = sanitize_post( $input, 'db' );

		$auth_data = WPRAG()->helpers->get_auth_data();

		// Only process if site is verified
		if ( empty( $auth_data['site_id'] ) || empty( $auth_data['verified_at'] ) ) {
			add_settings_error(
				'wp_rag_messages',
				'wp_rag_message',
				'Site must be registered and verified before configuration.',
				'error'
			);
			return get_option( self::OPTION_NAME );
		}

		$config_data = array();

		if ( isset( $sanitized_input['wordpress_username'] ) ) {
			$config_data['wordpress_username'] = $sanitized_input['wordpress_username'];
		}

		if ( isset( $sanitized_input['wordpress_password'] ) ) {
			$config_data['wordpress_password'] = $sanitized_input['wordpress_password'];
		}

		$api_path = '/config';
		$response = WPRAG()->helpers->call_api_for_site( $api_path, 'PUT', $config_data );

		if ( 200 !== $response['httpCode'] ) {
			$messages = Wp_Rag_AdminMessages::get_instance();
			$messages->add_message_with_inline_details(
				'API call failed.',
				$response,
				'error'
			);
			return get_option( self::OPTION_NAME );
		} else {
			return $sanitized_input;
		}
	}

	public function page_content() {
		$auth_data = WPRAG()->helpers->get_auth_data();
		$is_registered = ! empty( $auth_data['site_id'] );
		$is_verified = ! empty( $auth_data['verified_at'] );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<!-- Registration Form -->
			<div class="wp-rag-settings-section">
				<h2>WP RAG Registration</h2>
				<?php $this->registration_section_callback(); ?>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'wp_rag_registration' );
					?>
					<table class="form-table">
						<tr>
							<th scope="row">API key</th>
							<td><?php $this->premium_api_key_field_render(); ?></td>
						</tr>
					</table>
					<?php
					$label = 'Register';
					if ( $is_registered && ! $is_verified ) {
						$label = 'Start Verification';
					} elseif ( $is_verified && empty( $auth_data['premium_api_key'] ) ) {
						$label = 'Upgrade to Premium';
					}

					if ( ! $is_verified || empty( $auth_data['premium_api_key'] ) ) {
						submit_button( __( $label ) );
					}
					?>
				</form>
			</div>

			<!-- Configuration Form -->
			<div class="wp-rag-settings-section">
				<h2>WP RAG Configuration</h2>
				<?php $this->config_section_callback(); ?>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'wp_rag_options' );
					?>
					<table class="form-table">
						<tr>
							<th scope="row">WordPress user</th>
							<td><?php $this->wordpress_user_field_render(); ?></td>
						</tr>
						<tr>
							<th scope="row">WordPress password</th>
							<td><?php $this->wordpress_password_field_render(); ?></td>
						</tr>
					</table>
					<?php submit_button( __( 'Submit' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	public function add_registration_section_and_fields() {
		// Registration settings are now handled separately
		// This method is kept for backward compatibility
	}

	public function add_config_section_and_fields() {
		// Configuration settings are now handled separately
		// This method is kept for backward compatibility
	}

	function registration_section_callback() {
		$auth_data = WPRAG()->helpers->get_auth_data();
		$existing_premium_key = $auth_data['premium_api_key'] ?? '';

		// Only show help text if no premium API key exists.
		if ( empty( $existing_premium_key ) ) {
			echo 'If you have an API key, fill in the API key field. If not, leave it blank.' . '<br />';
		}

		if ( ! WPRAG()->helpers->is_verified() ) {
			if ( WPRAG()->helpers->get_auth_data( 'site_id' ) ) {
				echo 'Now, waiting for site verification to be completed. Usually, it takes less than a minute.';
			} else {
				$site_url   = get_site_url();
				$parsed_url = wp_parse_url( $site_url );

				if ( isset( $parsed_url['host'] ) && strstr( $parsed_url['host'], '.' ) === false ) {
					echo "❌ The free version doesn't support WordPress installations on private networks.";
				} else {
					echo 'Please "Register" first to use the plugin.';
				}
			}
		}
	}

	function config_section_callback() {
		echo 'Configure your plugin settings here.';
	}

	function premium_api_key_field_render() {
		$auth_data = WPRAG()->helpers->get_auth_data();
		// If already have premium API key in auth_data, show it (masked).
		$existing_premium_key = $auth_data['premium_api_key'] ?? '';

		if ( $existing_premium_key ) {
			// Show full API key with copy button when premium key exists.
			?>
			<div style="display: flex; align-items: center;">
				<span id="wp-rag-premium-api-key"><?php echo esc_html( $existing_premium_key ); ?></span>
				<button type="button" class="wp-rag-copy-btn" onclick="copyToClipboard('wp-rag-premium-api-key', this)">
					📋 Copy
				</button>
			</div>
			<?php
		} else {
			// Show input field when no premium key exists.
			?>
			<input type="text" name="<?php echo self::REGISTRATION_OPTION_NAME; ?>[premium_api_key]"
				value=""
				placeholder="Enter premium API key (optional)">
			<?php
		}
	}

	function wordpress_user_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[wordpress_username]"
				value="<?php echo esc_attr( $options['wordpress_username'] ?? '' ); ?>"
			<?php WPRAG()->form->maybe_disabled(); ?>
		/>
		<?php
	}

	function wordpress_password_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[wordpress_password]"
				value="<?php echo esc_attr( $options['wordpress_password'] ?? '' ); ?>"
			<?php WPRAG()->form->maybe_disabled(); ?>
		/>
		<?php
	}
}