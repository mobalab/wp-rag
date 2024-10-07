<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/k4200
 * @since      1.0.0
 *
 * @package    Wp_Rag
 * @subpackage Wp_Rag/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_Rag
 * @subpackage Wp_Rag/includes
 * @author     Kashima, Kazuo <k4200@kazu.tv>
 */
class Wp_Rag_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-rag',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
