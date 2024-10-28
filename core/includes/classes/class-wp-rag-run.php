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
	public function __construct() {
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
		add_action( 'plugins_loaded', array( $this, 'add_wp_webhooks_integrations' ), 9 );

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'wp_ajax_nopriv_wp_rag_verify_site', array( $this, 'verify_site_endpoint' ) );

		add_action( 'wp_ajax_wp_rag_process_chat', array( WPRAG()->frontend, 'process_chat' ) );
		add_action( 'wp_ajax_nopriv_wp_rag_process_chat', array( WPRAG()->frontend, 'process_chat' ) );

		add_shortcode( 'wp_rag_chat', array( WPRAG()->frontend, 'shortcode' ) );
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
			'WP RAG General Settings', // Page title
			'General Settings', // Title on the left menu
			'manage_options', // Capability
			'wp-rag-general-settings', // Menu slug
			array( WPRAG()->pages['general-settings'], 'page_content' ) // Callback function
		);

		add_submenu_page(
			'wp-rag-main',
			'WP RAG AI Configuration', // Page title
			'AI Configuration', // Title on the left menu
			'manage_options', // Capability
			'wp-rag-ai-configuration', // Menu slug
			array( WPRAG()->pages['ai-configuration'], 'page_content' ) // Callback function
		);
	}

	public function admin_notices() {
		settings_errors( 'wp_rag_messages' );
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
	 * Initializes the admin pages.
	 *
	 * @return void
	 */
	function settings_init() {
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if (isset( $_POST['_wp_http_referer'] )) {
			$_wp_http_referer = sanitize_text_field( $_POST['_wp_http_referer'] );
			$referer_page     = wp_unslash( $_wp_http_referer );
			$referer_query    = parse_url( $referer_page, PHP_URL_QUERY );
			parse_str( $referer_query, $params );
			$referer_page = $params['page'];
		} else {
			$referer_page = null;
		}

		if ( 'wp-rag-main' === $current_page || 'wp-rag-main' === $referer_page ) {
			// TODO Check nonce.
			$cls = WPRAG()->pages['main'];

			if ( isset( $_POST['wp_rag_import_submit'] ) ) {
				$cls->handle_import_form_submission();
			}
			if ( isset( $_POST['wp_rag_embed_submit'] ) ) {
				$cls->handle_embed_form_submission();
			}
			if ( isset( $_POST['wp_rag_query_submit'] ) ) {
				$cls->handle_query_form_submission();
			}
		} elseif ( 'wp-rag-general-settings' === $current_page || 'wp-rag-general-settings' === $referer_page ) {
			$cls = WPRAG()->pages['general-settings'];

			register_setting(
				'wp_rag_options',
				'wp_rag_options', // This is for General Settings.
				array(
					'sanitize_callback' => array( $cls, 'save_config_api' ),
				),
			);

			$cls->add_auth_section_and_fields();
			$cls->add_config_section_and_fields();
		} elseif ( 'wp-rag-ai-configuration' === $current_page || 'wp-rag-ai-configuration' === $referer_page ) {
			$cls = WPRAG()->pages['ai-configuration'];

			register_setting(
				'wp_rag_options',
				$cls::OPTION_NAME,
				array(
					'sanitize_callback' => array( $cls, 'save_config_api' ),
				),
			);

			$cls->add_api_keys_section_and_fields();
			$cls->add_model_selection_section_and_fields();
		}
	}
}
