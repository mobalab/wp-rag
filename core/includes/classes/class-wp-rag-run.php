<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

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
 * @package		WPRAG
 * @subpackage	Classes/Wp_Rag_Run
 * @author		Kashima, Kazuo
 * @since		0.0.1
 */
class Wp_Rag_Run{

	/**
	 * Our Wp_Rag_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 0.0.1
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	0.0.1
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'plugin_action_links_' . WPRAG_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_and_styles' ), 20 );
		add_action( 'wp_ajax_nopriv_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'wp_ajax_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'plugins_loaded', array( $this, 'add_wp_webhooks_integrations' ), 9 );
		add_filter( 'wpwhpro/admin/settings/menu_data', array( $this, 'add_main_settings_tabs' ), 20 );
		add_action( 'wpwhpro/admin/settings/menu/place_content', array( $this, 'add_main_settings_content' ), 20 );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	0.0.1
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" target="_blank title="Documentation" style="font-weight:700;">%s</a>', 'https://github.com/k4200/wp-rag', __( 'Documentation', 'wp-rag' ) );

		return $links;
	}


	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access	public
	 * @since	0.0.1
	 *
	 * @return	void
	 */
	public function enqueue_frontend_scripts_and_styles() {
		wp_enqueue_style( 'wprag-frontend-styles', WPRAG_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), WPRAG_VERSION, 'all' );
		wp_enqueue_script( 'wprag-frontend-scripts', WPRAG_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array( 'jquery' ), WPRAG_VERSION, false );
		wp_localize_script( 'wprag-frontend-scripts', 'wprag', array(
			'demo_var'   		=> __( 'This is some demo text coming from the backend through a variable within javascript.', 'wp-rag' ),
			'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
			'security_nonce'	=> wp_create_nonce( "your-nonce-name" ),
		));
	}


	/**
	 * The callback function for my_demo_ajax_call
	 *
	 * @access	public
	 * @since	0.0.1
	 *
	 * @return	void
	 */
	public function my_demo_ajax_call_callback() {
		check_ajax_referer( 'your-nonce-name', 'ajax_nonce_parameter' );

		$demo_data = isset( $_REQUEST['demo_data'] ) ? sanitize_text_field( $_REQUEST['demo_data'] ) : '';
		$response = array( 'success' => false );

		if ( ! empty( $demo_data ) ) {
			$response['success'] = true;
			$response['msg'] = __( 'The value was successfully filled.', 'wp-rag' );
		} else {
			$response['msg'] = __( 'The sent value was empty.', 'wp-rag' );
		}

		if( $response['success'] ){
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
	 * @access	public
	 * @since	0.0.1
	 *
	 * @return	void
	 */
	public function add_wp_webhooks_integrations(){

		// Abort if WP Webhooks is not active
		if( ! function_exists('WPWHPRO') ){
			return;
		}

		$custom_integrations = array();
		$folder = WPRAG_PLUGIN_DIR . 'core' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'integrations';

		try {
			$custom_integrations = WPWHPRO()->helpers->get_folders( $folder );
		} catch ( Exception $e ) {
			WPWHPRO()->helpers->log_issue( $e->getTraceAsString() );
		}

		if( ! empty( $custom_integrations ) ){
			foreach( $custom_integrations as $integration ){
				$file_path = $folder . DIRECTORY_SEPARATOR . $integration . DIRECTORY_SEPARATOR . $integration . '.php';
				WPWHPRO()->integrations->register_integration( array(
					'slug' => $integration,
					'path' => $file_path,
				) );
			}
		}
	}

	/*
	 * Add the setting tabs
	 *
	 * @access	public
	 * @since	0.0.1
	 *
	 * @param	mixed	$tabs	All available tabs
	 *
	 * @return	array	$data
	 */
	public function add_main_settings_tabs( $tabs ){

		$tabs['demo'] = WPWHPRO()->helpers->translate( 'Demo', 'admin-menu' );

		return $tabs;

	}

	/*
	 * Output the content of the tab
	 *
	 * @access	public
	 * @since	0.0.1
	 *
	 * @param	mixed	$tab	The current tab
	 *
	 * @return	void
	 */
	public function add_main_settings_content( $tab ){

		switch($tab){
			case 'demo':
				echo '<div class="wpwh-container">This is some custom text for our very own demo tab.</div>';
				break;
		}

	}

}
