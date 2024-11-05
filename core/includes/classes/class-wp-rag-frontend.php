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
		$chat_ui_options = get_option( WPRAG()->pages['chat-ui']::OPTION_NAME );

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'wprag-frontend-styles', WPRAG_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), WPRAG_VERSION, 'all' );
		wp_enqueue_script( 'wprag-frontend-scripts', WPRAG_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array( 'jquery' ), WPRAG_VERSION, false );
		wp_localize_script(
			'wprag-frontend-scripts',
			'wpRag',
			array(
				'chat_ui_options' => $chat_ui_options,
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'security_nonce'  => wp_create_nonce( 'your-nonce-name' ),
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
			<div class="wp-rag-chat-header">
				<span class="wp-rag-chat-title">Chat</span>
				<button type="button" class="wp-rag-chat-minimize">
					<span class="dashicons dashicons-minus"></span>
				</button>
			</div>
			<div class="wp-rag-chat-content">
				<div id="wp-rag-chat-messages"></div>
				<form id="wp-rag-chat-form">
					<input type="text" id="wp-rag-chat-input" placeholder="Enter your message here...">
					<button type="submit" class="wp-rag-chat-submit">
						<span class="button-text">Send</span>
						<span class="wp-rag-spinner"></span>
					</button>
				</form>
			</div>
		</div>
		<div id="wp-rag-chat-icon" class="wp-rag-chat-icon hidden">
			<span class="dashicons dashicons-admin-comments"></span>
			<span class="wp-rag-chat-icon-tooltip">Open Chat</span>
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
			wp_send_json_error( $response['response'], $response['httpCode'] );
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

	/**
	 * Outputs the custom CSS.
	 */
	public function output_custom_css() {
		$options = get_option( WP_RAG::instance()->pages['chat-ui']::OPTION_NAME );

		if ( ! empty( $options['custom_css'] ) ) {
			echo '<style type="text/css">' . esc_html( $options['custom_css'] ) . '</style>';
		}
	}
}