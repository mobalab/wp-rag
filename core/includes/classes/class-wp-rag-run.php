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
		add_action( 'wp_enqueue_scripts', array( WPRAG()->frontend, 'enqueue_scripts_and_styles' ), 20 );
		add_action( 'wp_ajax_nopriv_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'wp_ajax_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'plugins_loaded', array( $this, 'add_wp_webhooks_integrations' ), 9 );
		add_filter( 'wpwhpro/admin/settings/menu_data', array( $this, 'add_main_settings_tabs' ), 20 );
		add_action( 'wpwhpro/admin/settings/menu/place_content', array( $this, 'add_main_settings_content' ), 20 );

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'wp_ajax_nopriv_wp_rag_verify_site', array( $this, 'verify_site_endpoint' ) );

		add_action( 'wp_ajax_wp_rag_process_chat',  array( WPRAG()->frontend, 'process_chat' ) );
		add_action( 'wp_ajax_nopriv_wp_rag_process_chat',  array( WPRAG()->frontend, 'process_chat' ) );

		add_shortcode( 'wp_rag_chat' , array( WPRAG()->frontend, 'shortcode' ) );
		// Render the chat window after the footer.
		add_action( 'wp_footer', array( WPRAG()->frontend, 'show_chat_window' ) );
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

	/**
	 * @param $tabs
	 *
	 * @return void
	 */
	public function add_admin_menu( $tabs ) {
		add_menu_page(
			'WP RAG',
			'WP RAG',
			'manage_options',
			'wp-rag-main',
			array( WPRAG()->pages['main'], 'render_main_page' ),
			'dashicons-admin-generic',
			100
		);

		add_submenu_page(
			'wp-rag-main',
			'WP RAG Settings', // Page title
			'Settings', // Title on the left menu
			'manage_options', // Capability
			'wp-rag-settings', // Menu slug
			array( WPRAG()->pages['settings'], 'settings_page_content' ) // Callback function
		);
	}

	public function admin_notices() {
		settings_errors( 'wp_rag_messages' );
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
	 * Asks the API to verify the site.
	 *
	 * Use this method when the site is registered, but not verified for some reason (e.g. network issue etc.).
	 *
	 * @param $site_id ID of the site to verify
	 *
	 * @return bool
	 */
	private function start_site_verification( $site_id ): bool {
		$api_path = "/api/sites/$site_id/verify";
		$data     = array();
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
			// Starting the verification process succeeded, which doesn't necessarily mean the site is verified.
			$auth_data                      = WPRAG()->helpers->get_auth_data();
			$auth_data['free_api_key']      = $response['response']['free_api_key'];
			$auth_data['verification_code'] = $response['response']['verification_code'];
			WPRAG()->helpers->save_auth_data( $auth_data );

			return true;
		}
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
			$this->start_site_verification( $auth_data['site_id'] );

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
		if ( isset( $_POST['wp_rag_import_submit'] ) ) {
			WPRAG()->pages['main']->handle_import_form_submission();
		}
		if ( isset( $_POST['wp_rag_embed_submit'] ) ) {
			WPRAG()->pages['main']->handle_embed_form_submission();
		}
		if ( isset( $_POST['wp_rag_query_submit'] ) ) {
			WPRAG()->pages['main']->handle_query_form_submission();
		}

		register_setting(
			'wp_rag_options',
			'wp_rag_options',
			array(
				'sanitize_callback' => array( $this, 'save_config_api' ),
			),
		);
		WPRAG()->pages['settings']->add_auth_section_and_fields();
		WPRAG()->pages['settings']->add_config_section_and_fields();
	}
}
