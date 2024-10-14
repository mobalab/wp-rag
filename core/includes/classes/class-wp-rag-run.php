<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HELPER COMMENT START
 *
 * This class is used to bring your plugin to life.
 * All the other registered classed bring features which are
 * controlled and managed by this class.
 *
 * Within the add_hooks() function, you can register all of
 * your WordPress related actions and filters as followed:
 *
 * add_action( 'my_action_hook_to_call', array( $this, 'the_action_hook_callback', 10, 1 ) );
 * or
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * or
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 *
 * Once added, you can create the callback function, within this class, as followed:
 *
 * public function the_action_hook_callback( $some_variable ){}
 * or
 * public function the_filter_hook_callback( $some_variable ){}
 * or
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 *
 *
 * HELPER COMMENT END
 */

/**
 * Class Wp_Rag_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Run
 * @author      Kashima, Kazuo
 * @since       0.0.1
 */
class Wp_Rag_Run {

	/**
	 * Our Wp_Rag_Run constructor
	 * to run the plugin logic.
	 *
	 * @since 0.0.1
	 */
	function __construct() {
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WordPress HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access  private
	 * @since   0.0.1
	 * @return  void
	 */
	private function add_hooks() {

		add_action( 'plugin_action_links_' . WPRAG_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_and_styles' ), 20 );
		add_action( 'wp_ajax_nopriv_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'wp_ajax_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'plugins_loaded', array( $this, 'add_wp_webhooks_integrations' ), 9 );
		add_filter( 'wpwhpro/admin/settings/menu_data', array( $this, 'add_main_settings_tabs' ), 20 );
		add_action( 'wpwhpro/admin/settings/menu/place_content', array( $this, 'add_main_settings_content' ), 20 );

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		add_action( 'wp_ajax_nopriv_wp_rag_verify_site', array( $this, 'verify_site_endpoint' ) );
	}

	/**
	 * ######################
	 * ###
	 * #### WordPress HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	 * Adds action links to the plugin list table
	 *
	 * @access   public
	 * @since    0.0.1
	 *
	 * @param    array $links An array of plugin action links.
	 *
	 * @return   array   An array of plugin action links.
	 */
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" target="_blank title="Documentation" style="font-weight:700;">%s</a>', 'https://github.com/k4200/wp-rag', __( 'Documentation', 'wp-rag' ) );

		return $links;
	}


	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @return  void
	 */
	public function enqueue_frontend_scripts_and_styles() {
		wp_enqueue_style( 'wprag-frontend-styles', WPRAG_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), WPRAG_VERSION, 'all' );
		wp_enqueue_script( 'wprag-frontend-scripts', WPRAG_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array( 'jquery' ), WPRAG_VERSION, false );
		wp_localize_script(
			'wprag-frontend-scripts',
			'wprag',
			array(
				'demo_var'       => __( 'This is some demo text coming from the backend through a variable within javascript.', 'wp-rag' ),
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'security_nonce' => wp_create_nonce( 'your-nonce-name' ),
			)
		);
	}


	/**
	 * The callback function for my_demo_ajax_call
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @return  void
	 */
	public function my_demo_ajax_call_callback() {
		check_ajax_referer( 'your-nonce-name', 'ajax_nonce_parameter' );

		$demo_data = isset( $_REQUEST['demo_data'] ) ? sanitize_text_field( $_REQUEST['demo_data'] ) : '';
		$response  = array( 'success' => false );

		if ( ! empty( $demo_data ) ) {
			$response['success'] = true;
			$response['msg']     = __( 'The value was successfully filled.', 'wp-rag' );
		} else {
			$response['msg'] = __( 'The sent value was empty.', 'wp-rag' );
		}

		if ( $response['success'] ) {
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response );
		}

		die();
	}

	/**
	 * ####################
	 * ### WP Webhooks
	 * ####################
	 */

	/*
	 * Register dynamically all integrations
	 * The integrations are available within core/includes/integrations.
	 * A new folder is considered a new integration.
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @return  void
	 */
	public function add_wp_webhooks_integrations() {

		// Abort if WP Webhooks is not active
		if ( ! function_exists( 'WPWHPRO' ) ) {
			return;
		}

		$custom_integrations = array();
		$folder              = WPRAG_PLUGIN_DIR . 'core' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'integrations';

		try {
			$custom_integrations = WPWHPRO()->helpers->get_folders( $folder );
		} catch ( Exception $e ) {
			WPWHPRO()->helpers->log_issue( $e->getTraceAsString() );
		}

		if ( ! empty( $custom_integrations ) ) {
			foreach ( $custom_integrations as $integration ) {
				$file_path = $folder . DIRECTORY_SEPARATOR . $integration . DIRECTORY_SEPARATOR . $integration . '.php';
				WPWHPRO()->integrations->register_integration(
					array(
						'slug' => $integration,
						'path' => $file_path,
					)
				);
			}
		}
	}

	/*
	 * Add the setting tabs
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @param   mixed   $tabs   All available tabs
	 *
	 * @return  array   $data
	 */
	public function add_main_settings_tabs( $tabs ) {

		$tabs['demo'] = WPWHPRO()->helpers->translate( 'Demo', 'admin-menu' );

		return $tabs;
	}

	/*
	 * Output the content of the tab
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @param   mixed   $tab    The current tab
	 *
	 * @return  void
	 */
	public function add_main_settings_content( $tab ) {

		switch ( $tab ) {
			case 'demo':
				echo '<div class="wpwh-container">This is some custom text for our very own demo tab.</div>';
				break;
		}
	}

	public function add_admin_menu( $tabs ) {
		add_options_page(
			'WP RAG Settings', // Page title
			'WP RAG', // Title on the left menu
			'manage_options', // Capability
			'wp-rag-settings', // Menu slug
			array( $this, 'settings_page_content' ) // Callback function
		);
	}

	function settings_page_content() {
		$label_submit_button = $this->is_verified() ? 'Save Settings' : 'Register';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_rag_options' );
				do_settings_sections( 'wp-rag-settings' );
				submit_button( __( $label_submit_button ) );
				?>
			</form>
		</div>
		<?php
	}

	private function add_auth_section_and_fields() {
		add_settings_section(
			'wp_rag_auth_section', // Section ID
			'WP RAG Registration', // Title
			array( $this, 'auth_section_callback' ), // Callback
			'wp-rag-settings', // Page slug
		);

		add_settings_field(
			'wp_rag_paid_api_key',
			'API key',
			array( $this, 'paid_api_key_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_auth_section'
		);
	}


	private function add_config_section_and_fields() {
		add_settings_section(
			'wp_rag_config_section', // Section ID
			'WP RAG Configuration', // Title
			array( $this, 'config_section_callback' ), // Callback
			'wp-rag-settings' // Slug of the page
		);

		add_settings_field(
			'wp_rag_openai_api_key', // Field ID
			'OpenAI API key', // Title
			array( $this, 'openai_api_key_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_config_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_wordpress_username', // Field ID
			'WordPress user', // Title
			array( $this, 'wordpress_user_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_config_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_wordpress_password', // Field ID
			'WordPress password', // Title
			array( $this, 'wordpress_password_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_config_section' // Section this field belongs to
		);
	}

	/**
	 * Registers the site on the API.
	 *
	 * @return bool
	 */
	private function register_site(): bool {
		$api_path = '/api/sites';
		$data     = array( 'url' => get_site_url() );
		$response = WPRAG()->helpers->call_api( $api_path, 'POST', $data );

		if ( 201 !== $response['httpCode'] ) {
			add_settings_error(
				'wp_rag_messages',
				'wp_rag_message',
				'API error: status=' . $response['httpCode'] . ', response=' . wp_json_encode( $response['response'] ),
				'error'
			);
			return false;
		} else {
			$auth_data                      = WPRAG()->helpers->get_auth_data();
			$auth_data['site_id']           = $response['response']['id'];
			$auth_data['free_api_key']      = $response['response']['free_api_key'];
			$auth_data['verification_code'] = $response['response']['verification_code'];
			WPRAG()->helpers->save_auth_data( $auth_data );

			// At this point, the site is registered, but not verified yet.
			return true;
		}
	}

	/**
	 * Return whether the site is verified or not.
	 *
	 * Note that it only checks the DB, and doesn't check the API.
	 *
	 * @return bool True if verified, otherwise false
	 */
	private function is_verified() {
		return ! empty( WPRAG()->helpers->get_auth_data( 'verified_at' ) );
	}

	/**
	 * Endpoint to verify the site.
	 *
	 * @return void
	 */
	public function verify_site_endpoint() {
		$received_code = wp_unslash( $_GET['code'] ?? '' );
		$stored_code   = WPRAG()->helpers->get_auth_data( 'verification_code' );

		if ( $received_code === $stored_code ) {
			// WPRAG()->helpers->delete_key_from_auth_data( 'verification_code' );
			WPRAG()->helpers->update_auth_data( 'verified_at', date( 'Y-m-d H:i:s' ) );

			ob_start();
			status_header( 204 );
			ob_end_clean();
			// Without this exit, this endpoint would return 200.
			exit;
		} else {
			wp_die( 'Invalid verification code' );
		}
		wp_die();
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

		$auth_data = WPRAG()->helpers->get_auth_data();
		if ( empty( $auth_data['site_id'] ) ) {
			$this->register_site();

			return get_option( 'wp_rag_options' );
		} elseif ( empty( $auth_data['verified_at'] ) ) {
			// The site isn't verified yet.
			// TODO Check the expiration datetime of the verification code.
			return get_option( 'wp_rag_options' );
		} else {
			$api_path = "/api/sites/{$auth_data['site_id']}/config";

			$response = WPRAG()->helpers->call_api( $api_path, 'PUT', $sanitized_input );

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

	function settings_init() {
		register_setting(
			'wp_rag_options',
			'wp_rag_options',
			array(
				'sanitize_callback' => array( $this, 'save_config_api' ),
			),
		);
		$this->add_auth_section_and_fields();
		$this->add_config_section_and_fields();
	}

	function auth_section_callback() {
		echo 'If you have an API key, fill in the API key field. If not, leave it blank.' . '<br />';
		if ( ! $this->is_verified() ) {
			if ( WPRAG()->helpers->get_auth_data( 'site_id' ) ) {
				echo 'Now, waiting for site verification to be completed.';
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

	function openai_api_key_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[openai_api_key]"
				value="<?php echo esc_attr( $options['openai_api_key'] ?? '' ); ?>"
			<?php
			if ( ! $this->is_verified() ) {
				echo 'disabled';
			}
			?>
		/>
		<?php
	}

	function wordpress_user_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[wordpress_username]"
				value="<?php echo esc_attr( $options['wordpress_username'] ?? '' ); ?>"
			<?php
			if ( ! $this->is_verified() ) {
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
			if ( ! $this->is_verified() ) {
				echo 'disabled';
			}
			?>
		/>
		<?php
	}
}
