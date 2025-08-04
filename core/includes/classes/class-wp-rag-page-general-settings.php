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

	/**
	 * Executed before saving the options.
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function save_config_api( $input ) {
		$sanitized_input = sanitize_post( $input, 'db' );

		// Check if premium API key was submitted.
		$premium_api_key = '';
		if ( isset( $_POST['wp_rag_auth_data']['premium_api_key'] ) ) {
			$premium_api_key = sanitize_text_field( $_POST['wp_rag_auth_data']['premium_api_key'] );
		}

		$auth_data = WPRAG()->helpers->get_auth_data();
		if ( empty( $auth_data['site_id'] ) ) {
			// New site registration.
			if ( ! empty( $premium_api_key ) ) {
				// Register with premium API key.
				$result = WPRAG()->helpers->register_site( $premium_api_key );
				if ( ! $result ) {
					// Registration failed, don't proceed.
					return get_option( self::OPTION_NAME );
				}
			} else {
				// Register without premium API key (free).
				WPRAG()->helpers->register_site();
			}
			WPRAG()->helpers->accept_terms_pp();

			return get_option( self::OPTION_NAME );
		} elseif ( empty( $auth_data['verified_at'] ) ) {
			// The site isn't verified yet.
			WPRAG()->helpers->start_site_verification( $auth_data['site_id'] );

			return get_option( self::OPTION_NAME );
		} else {
			// Site is already registered and verified.
			$api_path = '/config';

			$response = WPRAG()->helpers->call_api_for_site( $api_path, 'PUT', $sanitized_input );

			if ( 200 !== $response['httpCode'] ) {
				$messages = Wp_Rag_AdminMessages::get_instance();
				$messages->add_message(
					'API call failed.',
					$response,
					'error'
				);
				return get_option( self::OPTION_NAME );
			}

			// Handle premium API key update for existing sites.
			if ( ! empty( $premium_api_key ) ) {
				$update_result = WPRAG()->helpers->update_site_premium_key( $auth_data['site_id'], $premium_api_key );
				if ( ! $update_result ) {
					// Premium key update failed, but config was saved.
					return get_option( self::OPTION_NAME );
				}
			}

			// Pass to the default action.
			return $sanitized_input;
		}
	}

	public function page_content() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_rag_options' );
				do_settings_sections( 'wp-rag-general-settings' );
				if ( WPRAG()->helpers->is_verified() ) {
					submit_button( __( 'Save Settings' ) );
				} else {
					submit_button(
						__( 'Register' ),
						'primary',
						'submit',
						true,
						WPRAG()->helpers->has_agreed_terms_pp() ? array() : array( 'disabled' => 'true' )
					);
				}
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

	public function add_terms_pp_section_and_fields() {
		add_settings_section(
			'wp_rag_terms_pp_section',
			'',
			array( $this, 'terms_pp_section_callback' ),
			'wp-rag-general-settings'
		);

		add_settings_field(
			'wp_rag_agree_terms_pp',
			'', // The label is included in the checkbox, so this should be blank.
			array( $this, 'agree_terms_pp_callback' ),
			'wp-rag-general-settings',
			'wp_rag_terms_pp_section'
		);
	}

	function registration_section_callback() {
		if ( ! WPRAG()->helpers->is_verified() ) {
			if ( WPRAG()->helpers->get_auth_data( 'site_id' ) ) {
				echo 'Now, waiting for site verification to be completed. Usually, it takes less than a minute.';
			} else {
				$site_url   = get_site_url();
				$parsed_url = wp_parse_url( $site_url );

				if ( isset( $parsed_url['host'] ) && strstr( $parsed_url['host'], '.' ) === false ) {
					echo "❌ The free version doesn't support WordPress installations on private networks.";
				} else {
					echo 'If you have a premium API key, enter it in the API key field to skip verification. Leave it blank for free registration.';
				}
			}
		} else if ( WPRAG()->helpers->get_auth_data( 'premium_api_key' ) ) {
			echo '✅ Premium API key is active.';
			$expires_at = WPRAG()->helpers->get_auth_data( 'premium_api_key_expires_at' );
			$expires_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $expires_at ) );
			echo ' Expires on: ' . esc_html( $expires_date );
		}
	}

	function config_section_callback() {
		echo 'Configure your plugin settings here.';
	}

	function premium_api_key_field_render() {
		$options = get_option( 'wp_rag_auth_data' );
		?>
		<input type="text" name="wp_rag_auth_data[premium_api_key]" value="<?php echo esc_attr( $options['premium_api_key'] ?? '' ); ?>">
		<?php
			if ( WPRAG()->helpers->get_auth_data( 'premium_api_key' ) ) {
				echo 'Enter a new premium API key to update it.';
			}
	}

	function wordpress_user_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[wordpress_username]"
				value="<?php echo esc_attr( $options['wordpress_username'] ?? '' ); ?>"
			<?php WPRAG()->form->disabled_unless_verified(); ?>
		/>
		<?php
	}

	function wordpress_password_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[wordpress_password]"
				value="<?php echo esc_attr( $options['wordpress_password'] ?? '' ); ?>"
			<?php WPRAG()->form->disabled_unless_verified(); ?>
		/>
		<?php
	}

	public function terms_pp_section_callback() {
		// Empty for separator.
	}

	public function agree_terms_pp_callback() {
		$options = get_option( Wp_Rag::OPTION_NAME_FOR_TERMS_PP );
		if ( $options && isset( $options['agreed'] ) && $options['agreed'] ) {
			return;
		}

		?>
		<label for="wp_rag_agree_terms">
			<input type="checkbox" id="wp_rag_agree_terms_pp" name="wp_rag_agree_terms_pp" value="1" required
					onchange="jQuery('#submit').prop('disabled', !this.checked);"
			>
			<?php
			printf(
				__( 'I agree to the <a href="%s" target="_blank">Terms of Service and Privacy Policy</a>', 'wp-rag' ),
				esc_url( 'https://services.mobalab.net/wp-rag/terms-privacy.html' ),
			);
			?>
		</label>
		<?php
	}
}
