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
	 * Executed before saving the options.
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function save_config_api( $input ) {
		$sanitized_input = sanitize_post( $input, 'db' );
		$premium_api_key = isset( $sanitized_input['premium_api_key'] ) ? trim( $sanitized_input['premium_api_key'] ) : '';

		$auth_data = WPRAG()->helpers->get_auth_data();
		if ( empty( $auth_data['site_id'] ) ) {
			// New site registration
			if ( $premium_api_key ) {
				// Register with premium API key
				$result = WPRAG()->helpers->register_site( $premium_api_key );
				if ( $result ) {
					// Save premium API key to auth_data
					WPRAG()->helpers->update_auth_data( 'premium_api_key', $premium_api_key );
				} else {
					// Registration with premium API key failed, return current options
					return get_option( self::OPTION_NAME );
				}
			} else {
				// Register without premium API key (free)
				$result = WPRAG()->helpers->register_site();
				if ( ! $result ) {
					// Registration failed, return current options
					return get_option( self::OPTION_NAME );
				}
			}

			// Remove premium_api_key from options after processing
			unset( $sanitized_input['premium_api_key'] );
			return $sanitized_input;
		} elseif ( empty( $auth_data['verified_at'] ) ) {
			// The site isn't verified yet.
			if ( $premium_api_key ) {
				// Delete unverified site and re-register with premium API key
				WPRAG()->helpers->delete_auth_data();
				$result = WPRAG()->helpers->register_site( $premium_api_key );
				if ( $result ) {
					WPRAG()->helpers->update_auth_data( 'premium_api_key', $premium_api_key );
				} else {
					// Registration with premium API key failed, return current options
					return get_option( self::OPTION_NAME );
				}
			} else {
				// Start verification for free registration
				$result = WPRAG()->helpers->start_site_verification( $auth_data['site_id'] );
				if ( ! $result ) {
					// Starting verification failed, return current options
					return get_option( self::OPTION_NAME );
				}
			}

			// Remove premium_api_key from options after processing
			unset( $sanitized_input['premium_api_key'] );
			return $sanitized_input;
		} else {
			// Site is already verified
			$config_data = $sanitized_input;

			// Handle premium API key upgrade if provided
			if ( $premium_api_key && empty( $auth_data['premium_api_key'] ) ) {
				// Include premium_api_key in config update for upgrade
				$config_data['premium_api_key'] = $premium_api_key;
			} else {
				// Remove premium_api_key from config data if not upgrading
				unset( $config_data['premium_api_key'] );
			}

			$api_path = '/config';
			$response = WPRAG()->helpers->call_api_for_site( $api_path, 'PUT', $config_data );

			if ( 200 !== $response['httpCode'] ) {
				$messages = Wp_Rag_AdminMessages::get_instance();
				$messages->add_message(
					'API call failed.',
					$response,
					'error'
				);
				return get_option( self::OPTION_NAME );
			} else {
				// If premium API key was successfully activated, save it
				if ( $premium_api_key && isset( $response['response']['premium_api_key'] ) ) {
					WPRAG()->helpers->update_auth_data( 'premium_api_key', $premium_api_key );
				}

				// Remove premium_api_key from options after processing
				unset( $sanitized_input['premium_api_key'] );
				return $sanitized_input;
			}
		}
	}

	public function page_content() {
		$label_submit_button = WPRAG()->helpers->is_verified() ? 'Save Settings' : 'Register';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_rag_options' );
				do_settings_sections( 'wp-rag-general-settings' );
				submit_button( __( $label_submit_button ) );
				?>
			</form>
		</div>
		<?php
	}

	public function add_registration_section_and_fields() {
		add_settings_section(
			'wp_rag_registration_section', // Section ID
			'WP RAG Registration', // Title
			array( $this, 'registration_section_callback' ), // Callback
			'wp-rag-general-settings', // Page slug
		);

		add_settings_field(
			'wp_rag_premium_api_key',
			'API key',
			array( $this, 'premium_api_key_field_render' ), // callback
			'wp-rag-general-settings', // Page slug
			'wp_rag_registration_section'
		);
	}


	public function add_config_section_and_fields() {
		add_settings_section(
			'wordpress_authentication_section', // Section ID
			'WP RAG Configuration', // Title
			array( $this, 'config_section_callback' ), // Callback
			'wp-rag-general-settings' // Slug of the page
		);

		add_settings_field(
			'wp_rag_wordpress_username', // Field ID
			'WordPress user', // Title
			array( $this, 'wordpress_user_field_render' ), // callback
			'wp-rag-general-settings', // Page slug
			'wordpress_authentication_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_wordpress_password', // Field ID
			'WordPress password', // Title
			array( $this, 'wordpress_password_field_render' ), // callback
			'wp-rag-general-settings', // Page slug
			'wordpress_authentication_section' // Section this field belongs to
		);
	}

	function registration_section_callback() {
		$auth_data = WPRAG()->helpers->get_auth_data();
		$existing_premium_key = $auth_data['premium_api_key'] ?? '';

		// Only show help text if no premium API key exists
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
		$options = get_option( self::OPTION_NAME );
		$auth_data = WPRAG()->helpers->get_auth_data();
		// If already have premium API key in auth_data, show it (masked)
		$existing_premium_key = $auth_data['premium_api_key'] ?? '';

		if ( $existing_premium_key ) {
			// Show full API key with copy button when premium key exists
			?>
			<div style="display: flex; align-items: center;">
				<span id="wp-rag-premium-api-key"><?php echo esc_html( $existing_premium_key ); ?></span>
				<button type="button" class="wp-rag-copy-btn" onclick="copyToClipboard('wp-rag-premium-api-key', this)">
					📋 Copy
				</button>
			</div>
			<?php
		} else {
			// Show input field when no premium key exists
			?>
			<input type="text" name="<?php echo self::OPTION_NAME; ?>[premium_api_key]"
				value="<?php echo esc_attr( $options['premium_api_key'] ?? '' ); ?>"
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
