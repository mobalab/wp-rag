<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Frontend
 *
 * This class is responsible for frontend pages.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Frontend
 * @author      Kashima, Kazuo
 * @since       0.0.1
 */
class Wp_Rag_Frontend {
	private $shortcode_used = false;

	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @return  void
	 */
	public function enqueue_scripts_and_styles() {
		wp_enqueue_style( 'wprag-frontend-styles', WPRAG_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), WPRAG_VERSION, 'all' );
		wp_enqueue_script( 'wprag-frontend-scripts', WPRAG_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array( 'jquery' ), WPRAG_VERSION, false );
		wp_localize_script(
			'wprag-frontend-scripts',
			'wpRag',
			array(
				'demo_var'       => __( 'This is some demo text coming from the backend through a variable within javascript.', 'wp-rag' ),
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'security_nonce' => wp_create_nonce( 'your-nonce-name' ),
			)
		);
	}

	/**
	 * @return string|void HTML for the chat window
	 */
	function show_chat_window() {
		// When the shortcode wasn't used, do nothing.
		if ( empty( $this->shortcode_used ) ) {
			return '';
		}
		?>
		<div id="wp-rag-chat-window" class="wp-rag-chat-window">
			<div id="wp-rag-chat-messages"></div>
			<form id="wp-rag-chat-form">
				<input type="text" id="wp-rag-chat-input" placeholder="Enter your message here...">
				<button type="submit" class="wp-rag-chat-submit">
					<span class="button-text">Send</span>
					<span class="wp-rag-spinner"></span>
				</button>
			</form>
		</div>
		<?php
	}

	public function process_chat() {
		if ( empty( $_POST['message'] ) ) {
			return;
		}
		$message  = sanitize_text_field( wp_unslash( $_POST['message'] ) );
		$data     = array( 'question' => $message );
		$response = WPRAG()->helpers->call_api_for_site( '/posts/query', 'POST', $data );

		if ( $response['httpCode'] !== 200 ) {
			wp_send_json_error( $response['response'] );
		} else {
			wp_send_json_success( $response['response'] );
		}

		wp_die();
	}

	/**
	 * @param $atts
	 *
	 * @return string|null
	 */
	public function shortcode( $atts ) {
		// Do nothing when REST API request.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return '';
		}

		// Do nothing when AJAX request.
		if ( wp_doing_ajax() ) {
			return '';
		}

		// Do nothing on a review page.
		if ( is_admin() ) {
			return '';
		}

		// This global variable indicates whether the shortcode was used or not.
		$this->shortcode_used = true;

		return '';
	}
}