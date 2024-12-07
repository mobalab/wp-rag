<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_AdminMessages
 *
 * This class provides functions that show messages with details on a modal.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_AdminMessages
 * @author      Kashima, Kazuo
 * @since       0.1.0
 */
class Wp_Rag_AdminMessages {

	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_footer', array( $this, 'render_modal_template' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Loads the script and style files
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'wp-rag-admin-notices',
			plugins_url( 'core/includes/assets/js/admin-modal.js', WPRAG_PLUGIN_FILE ),
			array( 'jquery' ),
			WPRAG_VERSION,
			true
		);

		wp_enqueue_style(
			'wp-rag-admin-notices',
			plugins_url( 'core/includes/assets/css/admin-modal.css', WPRAG_PLUGIN_FILE ),
			array(),
			WPRAG_VERSION
		);
	}

	/**
	 * Outputs the modal template HTML.
	 */
	public function render_modal_template() {
		?>
		<div id="wp-rag-modal" style="display:none;" class="wp-rag-modal">
			<div class="wp-rag-modal-content">
				<div class="wp-rag-modal-header">
					<h2 class="wp-rag-modal-title">Details</h2>
					<span class="wp-rag-modal-close dashicons dashicons-no-alt"></span>
				</div>
				<div class="wp-rag-modal-body">
					<pre></pre>
				</div>
				<div class="wp-rag-modal-footer">
					<button type="button" class="button wp-rag-modal-close">Close</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Shows a message with the button for a model for the detailed info.
	 *
	 * @param string $message Message.
	 * @param array  $details Detailed info.
	 * @param string $type Either one of 'error', 'warning', 'success' or 'info'.
	 * @param string $setting_id Passed to the 1st argument of add_settings_error.
	 */
	public function add_message( $message, $details = array(), $type = 'info', $setting_id = 'wp_rag_messages' ) {
		$encoded_details = esc_attr( wp_json_encode( $details ) );
		$detail_button   = ! empty( $details )
			? ' &gt; <a href="#" class="wp-rag-show-details" data-details="' . $encoded_details . '">Details</a>'
			: '';

		add_settings_error(
			$setting_id,
			'wp_rag_' . uniqid(),
			$message . $detail_button,
			'error' === $type ? 'error' : 'updated'
		);
	}
}
