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
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_rag_options' );
				do_settings_sections( 'wp-rag-settings' );
				submit_button( __( 'Save Settings' ) );
				?>
			</form>
		</div>
		<?php
	}

	private function add_fields() {
		add_settings_section(
			'wp_rag_section', // Section ID
			'WP RAG Settings', // Title
			array( $this, 'section_callback' ), // Callback
			'wp-rag-settings' // Slug of the page
		);

		add_settings_field(
			'wp_rag_openai_api_key', // Field ID
			'OpenAI API key', // Title
			array( $this, 'openai_api_key_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_wordpress_username', // Field ID
			'WordPress user', // Title
			array( $this, 'wordpress_user_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_wordpress_password', // Field ID
			'WordPress password', // Title
			array( $this, 'wordpress_password_field_render' ), // callback
			'wp-rag-settings', // Page slug
			'wp_rag_section' // Section this field belongs to
		);
	}

	function call_api( $url, $method = 'GET', $data = null, $headers = array() ) {
		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array_merge(
				array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				$headers
			),
		);

		if ( null !== $data ) {
			$args['body'] = wp_json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'httpCode' => 0,
				'response' => $response->get_error_message(),
			);
		}

		return array(
			'httpCode' => wp_remote_retrieve_response_code( $response ),
			'response' => json_decode( wp_remote_retrieve_body( $response ), true ),
		);
	}


	function save_config_api( $input ) {
		$sanitized_input = sanitize_post( $input, 'db' );
		$url             = 'http://rproxy/api/sites/1/config'; // TODO Fix this.

		$response = $this->call_api( $url, 'PUT', $sanitized_input );

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

	function settings_init() {
		register_setting(
			'wp_rag_options',
			'wp_rag_options',
			array(
				'sanitize_callback' => array( $this, 'save_config_api' ),
			),
		);
		$this->add_fields();
	}

	function section_callback() {
		echo 'Configure your plugin settings here.';
	}

	function openai_api_key_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[openai_api_key]" value="<?php echo esc_attr( $options['openai_api_key'] ?? '' ); ?>">
		<?php
	}

	function wordpress_user_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[wordpress_username]" value="<?php echo esc_attr( $options['wordpress_username'] ?? '' ); ?>">
		<?php
	}

	function wordpress_password_field_render() {
		$options = get_option( 'wp_rag_options' );
		?>
		<input type="text" name="wp_rag_options[wordpress_password]" value="<?php echo esc_attr( $options['wordpress_password'] ?? '' ); ?>">
		<?php
	}
}
