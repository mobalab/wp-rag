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

	/**
	 * Executed before saving the options.
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function save_config_api( $input ) {
		$sanitized_input = sanitize_post( $input, 'db' );

		$auth_data = WPRAG()->helpers->get_auth_data();
		if ( empty( $auth_data['site_id'] ) ) {
			$this->register_site();

			return get_option( 'wp_rag_options' );
		} elseif ( empty( $auth_data['verified_at'] ) ) {
			// The site isn't verified yet.
			$this->start_site_verification( $auth_data['site_id'] );

			return get_option( 'wp_rag_options' );
		} else {
			$api_path = '/config';

			$response = WPRAG()->helpers->call_api_for_site( $api_path, 'PUT', $sanitized_input );

			if ( 200 !== $response['httpCode'] ) {
				add_settings_error(
					'wp_rag_messages',
					'wp_rag_message',
					'API error: status=' . $response['httpCode'] . ', response=' . wp_json_encode( $response['response'] ),
					'error'
				);
				return get_option( 'wp_rag_options' );
			} else {
				// Pass to the default action.
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

	public function add_auth_section_and_fields() {
		add_settings_section(
			'wp_rag_auth_section', // Section ID
			'WP RAG Registration', // Title
			array( $this, 'auth_section_callback' ), // Callback
			'wp-rag-general-settings', // Page slug
		);

		add_settings_field(
			'wp_rag_paid_api_key',
			'API key',
			array( $this, 'paid_api_key_field_render' ), // callback
			'wp-rag-general-settings', // Page slug
			'wp_rag_auth_section'
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

	function auth_section_callback() {
		echo 'If you have an API key, fill in the API key field. If not, leave it blank.' . '<br />';
		if ( ! WPRAG()->helpers->is_verified() ) {
			if ( WPRAG()->helpers->get_auth_data( 'site_id' ) ) {
				echo 'Now, waiting for site verification to be completed. Usually, it takes less than a minute.';
			} else {
				echo 'Please "Register" first to use the plugin.';
			}
		}
	}

	function config_section_callback() {
		echo 'Configure your plugin settings here.';
	}

	function paid_api_key_field_render() {
		$options = get_option( 'wp_rag_auth_data' );
		?>
		<input type="text" name="wp_rag_auth_data[paid_api_key]" value="<?php echo esc_attr( $options['paid_api_key'] ?? '' ); ?>">
		<?php
	}

	function wordpress_user_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[wordpress_username]"
				value="<?php echo esc_attr( $options['wordpress_username'] ?? '' ); ?>"
			<?php
			if ( ! WPRAG()->helpers->is_verified() ) {
				echo 'disabled';
			}
			?>
		/>
		<?php
	}

	function wordpress_password_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[wordpress_password]"
				value="<?php echo esc_attr( $options['wordpress_password'] ?? '' ); ?>"
			<?php
			if ( ! WPRAG()->helpers->is_verified() ) {
				echo 'disabled';
			}
			?>
		/>
		<?php
	}
}
