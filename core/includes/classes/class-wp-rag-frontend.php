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
	 * @since 0.7.0
	 */
	public $chat_ui_options = array();

	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access  public
	 * @since   0.0.1
	 *
	 * @return  void
	 */
	public function enqueue_scripts_and_styles() {
		$this->chat_ui_options = get_option( WPRAG()->pages['chat-ui']::OPTION_NAME );

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'wprag-frontend-styles', WPRAG_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), WPRAG_VERSION, 'all' );
		wp_enqueue_script( 'wprag-frontend-scripts', WPRAG_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array( 'jquery' ), WPRAG_VERSION, false );
		wp_localize_script(
			'wprag-frontend-scripts',
			'wpRag',
			array(
				'chat_ui_options' => $this->chat_ui_options,
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'security_nonce'  => wp_create_nonce( 'your-nonce-name' ),
			)
		);
	}

	/**
	 * @return string|void HTML for the chat window
	 */
	public function show_chat_window() {
		// When the shortcode wasn't used, do nothing.
		if ( empty( $this->shortcode_used ) ) {
			return '';
		}

		$options          = get_option( WP_RAG::instance()->pages['chat-ui']::OPTION_NAME );
		$title            = ! empty( $options['window_title'] ) ? $options['window_title'] : 'Chat';
		$placeholder      = ! empty( $options['input_placeholder_text'] ) ? $options['input_placeholder_text']
			: 'Enter your message here...';
		$send_button_text = ! empty( $options['send_button_text'] ) ? $options['send_button_text'] : 'Send';
		?>
		<div id="wp-rag-chat-window" class="wp-rag-chat">
			<div class="wp-rag-chat__header">
				<span class="wp-rag-chat__title"><?php echo esc_html( $title ); ?></span>
				<?php if ( ! empty( $this->chat_ui_options['html_minimize_button'] ) ) : ?>
					<?php echo wp_kses_post( $this->chat_ui_options['html_minimize_button'] ); ?>
				<?php else : ?>
					<button type="button" id="wp-rag-chat-minimize-button" class="wp-rag-chat__minimize">
						<span class="dashicons dashicons-minus"></span>
					</button>
				<?php endif; ?>
			</div>
			<div class="wp-rag-chat__content">
				<div id="wp-rag-chat-messages" class="wp-rag-chat__messages"></div>
				<form id="wp-rag-chat-form" class="wp-rag-chat__form">
					<input type="text" id="wp-rag-chat-input" class="wp-rag-chat__input" placeholder="<?php echo esc_attr( $placeholder ); ?>">
					<?php if ( ! empty( $this->chat_ui_options['html_submit_button'] ) ) : ?>
						<?php echo wp_kses_post( $this->chat_ui_options['html_submit_button'] ); ?>
					<?php else : ?>
						<button type="submit" id="wp-rag-chat-submit-button" class="wp-rag-chat__submit">
							<span class="wp-rag-chat__submit-text"><?php echo esc_html( $send_button_text ); ?></span>
							<span class="wp-rag-chat__spinner"></span>
						</button>
					<?php endif; ?>
				</form>
			</div>
		</div>
		<?php if ( ! empty( $this->chat_ui_options['html_minimized_icon'] ) ) : ?>
			<?php echo wp_kses_post( $this->chat_ui_options['html_minimized_icon'] ); ?>
		<?php else : ?>
			<div id="wp-rag-chat-icon" class="wp-rag-chat-launcher wp-rag--hidden">
				<span class="dashicons dashicons-admin-comments"></span>
				<span class="wp-rag-chat-launcher__tooltip">Open <?php echo esc_html( $title ); ?></span>
			</div>
		<?php endif; ?>
		<?php
	}

	public function process_chat() {
		if ( empty( $_POST['message'] ) ) {
			return;
		}
		$message  = sanitize_text_field( wp_unslash( $_POST['message'] ) );
		$data     = array( 'question' => $message );
		$response = WPRAG()->helpers->call_api_for_site( '/posts/query', 'POST', $data );

		if ( 200 !== $response['httpCode'] ) {
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
	 *
	 * @since   0.0.2
	 */
	public function output_custom_css() {
		$options = get_option( WP_RAG::instance()->pages['chat-ui']::OPTION_NAME );

		if ( ! empty( $options['custom_css'] ) ) {
			echo '<style lang="text/css">' . esc_html( $options['custom_css'] ) . '</style>';
		}
	}
}