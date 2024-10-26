<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HELPER COMMENT START
 *
 * This is the main class that is responsible for registering
 * the core functions, including the files and setting up all features.
 *
 * To add a new class, here's what you need to do:
 * 1. Add your new class within the following folder: core/includes/classes
 * 2. Create a new variable you want to assign the class to (as e.g. public $helpers)
 * 3. Assign the class within the instance() function ( as e.g. self::$instance->helpers = new Wp_Rag_Helpers();)
 * 4. Register the class you added to core/includes/classes within the includes() function
 *
 * HELPER COMMENT END
 */

if ( ! class_exists( 'Wp_Rag' ) ) :

	/**
	 * Main Wp_Rag Class.
	 *
	 * @package     WPRAG
	 * @subpackage  Classes/Wp_Rag
	 * @since       0.0.1
	 * @author      Kashima, Kazuo
	 */
	final class Wp_Rag {

		const OPTION_NAME_FOR_AUTH_DATA = 'wp_rag_auth_data';

		const DEFAULT_API_URL = 'https://wp-rag.mobalab.net/';

		/**
		 * The real instance
		 *
		 * @access  private
		 * @since   0.0.1
		 * @var     object|Wp_Rag
		 */
		private static $instance;

		/**
		 * WPRAG helpers object.
		 *
		 * @access  public
		 * @since   0.0.1
		 * @var     object|Wp_Rag_Helpers
		 */
		public $helpers;

		/**
		 * WPRAG settings object.
		 *
		 * @access  public
		 * @since   0.0.1
		 * @var     object|Wp_Rag_Settings
		 */
		public $settings;

		/**
		 * @access  public
		 * @since   0.0.1
		 * @var     array
		 */
		public $pages;

		/**
		 * WPRAG frontend object.
		 *
		 * @access  public
		 * @since   0.0.1
		 * @var     object|Wp_Rag_Frontend
		 */
		public $frontend;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access  public
		 * @since   0.0.1
		 * @return  void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'wp-rag' ), '0.0.1' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access  public
		 * @since   0.0.1
		 * @return  void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'wp-rag' ), '0.0.1' );
		}

		/**
		 * Main Wp_Rag Instance.
		 *
		 * Insures that only one instance of Wp_Rag exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access      public
		 * @since       0.0.1
		 * @static
		 * @return      object|Wp_Rag   The one true Wp_Rag
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Wp_Rag ) ) {
				self::$instance = new Wp_Rag();
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers  = new Wp_Rag_Helpers();
				self::$instance->settings = new Wp_Rag_Settings();
				self::$instance->pages    = array(
					'main'             => new Wp_Rag_Page_Main(),
					'general-settings' => new Wp_Rag_Page_GeneralSettings(),
					'ai-configuration' => new Wp_Rag_Page_AiConfiguration(),
				);
				self::$instance->frontend = new Wp_Rag_Frontend();

				// Fire the plugin logic
				new Wp_Rag_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'WPRAG/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   0.0.1
		 * @return  void
		 */
		private function includes() {
			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-helpers.php';
			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-settings.php';

			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-main.php';
			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-general-settings.php';
			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-ai-configuration.php';

			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-frontend.php';

			require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   0.0.1
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   0.0.1
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'wp-rag', false, dirname( plugin_basename( WPRAG_PLUGIN_FILE ) ) . '/languages/' );
		}
	}

endif; // End if class_exists check.
