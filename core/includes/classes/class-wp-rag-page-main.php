<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Page_Main
 *
 * This class handles rendering of the main page.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Page_Main
 * @author      Kashima, Kazuo
 * @since       0.0.1
 */
class Wp_Rag_Page_Main {

	private $response = array();

	public function enqueue_scripts_and_styles() {
		wp_enqueue_script(
			'wp-rag-admin-clipboard',
			plugins_url( 'core/includes/assets/js/admin-clipboard.js', WPRAG_PLUGIN_FILE ),
			array( 'jquery' ),
			WPRAG_VERSION,
			true
		);

		wp_enqueue_style(
			'wp-rag-admin-notices',
			plugins_url( 'core/includes/assets/css/admin-clipboard.css', WPRAG_PLUGIN_FILE ),
			array(),
			WPRAG_VERSION
		);
	}

	/**
	 * Renders the main page
	 *
	 * @return void
	 */
	public function render_main_page() {
		if ( ! WPRAG()->helpers->is_verified() ) {
			$this->render_main_page_not_verified();
			return;
		}
		$ai_options = get_option( WPRAG()->pages['ai-configuration']::OPTION_NAME );
		$result     = WPRAG()->helpers->call_api_for_site( '/posts/status' );
		$status     = 200 === $result['httpCode'] ? $result['response']
			: array(
				'post_count'      => 0,
				'embedding_count' => 0,
			);
		?>
		<div class="wrap">
			<h2>WP RAG</h2>
			<h3>Site Info</h3>
			<ul>
				<li style="display: flex;">
					Site ID: <div id="wp-rag-main-site-id"><?php echo esc_html( WPRAG()->helpers->get_auth_data( 'site_id' ) ); ?></div>
					<button class="wp-rag-copy-btn" onclick="copyToClipboard('wp-rag-main-site-id', this)">
						📋 Copy
					</button>
				</li>
				<li style="display: flex;">
					API key: <div id="wp-rag-main-api-key"><?php echo esc_html( WPRAG()->helpers->get_auth_data( 'free_api_key' ) ); ?></div>
					<button class="wp-rag-copy-btn" onclick="copyToClipboard('wp-rag-main-api-key', this)">
						📋 Copy
					</button>
				</li>
			</ul>
			<h3>System Status</h3>
			<ul>
				<li>✅: This WordPress site is verified.</li>
				<li><?php echo isset( $ai_options['openai_api_key'] ) ? '✅: OpenAI API Key is set.' : '❌: OpenAI API Key is not set.'; ?></li>
				<li><?php echo $status['post_count'] > 0 ? '✅' : '❌'; ?>: Number of the posts imported to the WP RAG API is <?php echo esc_html( $status['post_count'] ); ?>.</li>
				<li><?php echo $status['embedding_count'] > 0 ? '✅' : '❌'; ?>: Number of the created embeddings is <?php echo esc_html( $status['embedding_count'] ); ?>.</li>
			</ul>
			<h3>Operations</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wp_rag_operation_submit', 'wp_rag_nonce' ); ?>
				<input type="submit" name="wp_rag_import_submit" class="button button-primary" value="Import Posts">
				<input type="submit" name="wp_rag_embed_submit" class="button button-primary" value="Generate Embeddings">
			</form>
			<h3>Test Query</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wp_rag_query_submit', 'wp_rag_nonce' ); ?>
				<input type="text" name="wp_rag_question" />
				<input type="submit" name="wp_rag_query_submit" class="button button-primary" value="Query">
			</form>
			<?php if ( ! empty( $this->response ) ) : ?>
				<p>Question: <?php echo esc_html( wp_unslash( $_POST['wp_rag_question'] ) ); ?></p>
				<p>Answer: <?php echo esc_html( $this->response['response']['answer'] ); ?></p>
				Context posts:
				<ul>
					<?php foreach ( $this->response['response']['context_posts'] as $post ) : ?>
						<li><a href="<?php echo esc_attr( $post['url'] ); ?>" target="_blank"><?php echo esc_html( $post['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_main_page_not_verified() {
		?>
		<div class="wrap">
			<h2>WP RAG</h2>
			<div>
				<p>Please register the site on the <a href="?page=wp-rag-general-settings">General Settings</a> page.</p>
				<p>Note that this WordPress instance needs to be accessible from the WP RAG API to be verified.</p>
			</div>
		</div>
		<?php
	}


	/**
	 * @return void
	 */
	function handle_import_form_submission() {
		check_admin_referer( 'wp_rag_operation_submit', 'wp_rag_nonce' );
		$data     = array(
			'task_type' => 'ImportWordpressPosts',
			'params'    => array( 'post_type' => 'post' ),
		);
		$response = WPRAG()->helpers->call_api_for_site( '/tasks', 'POST', $data );

		if ( 202 === $response['httpCode'] ) {
			$type    = 'success';
			$message = 'Successfully launch the import task.';
		} else {
			$type    = 'error';
			$message = 'API call failed.';
		}

		$messages = Wp_Rag_AdminMessages::get_instance();
		$messages->add_message(
			$message,
			$response,
			$type
		);
	}

	/**
	 * @return void
	 */
	function handle_embed_form_submission() {
		check_admin_referer( 'wp_rag_operation_submit', 'wp_rag_nonce' );
		$data     = array(
			'task_type' => 'Embed',
			'params'    => array(),
		);
		$response = WPRAG()->helpers->call_api_for_site( '/tasks', 'POST', $data );

		if ( 202 === $response['httpCode'] ) {
			$type    = 'success';
			$message = 'Successfully launch the embed task.';
		} else {
			$type    = 'error';
			$message = 'API call failed.';
		}

		$messages = Wp_Rag_AdminMessages::get_instance();
		$messages->add_message(
			$message,
			$response,
			$type
		);
	}

	/**
	 * Handles the query  form submission, validates the nonce, processes the posted data, and calls the API.
	 *
	 * @return void
	 */
	function handle_query_form_submission() {
		check_admin_referer( 'wp_rag_query_submit', 'wp_rag_nonce' );
		if ( empty( $_POST['wp_rag_question'] ) ) {
			return;
		}
		$data     = array( 'question' => sanitize_text_field( wp_unslash( $_POST['wp_rag_question'] ) ) );
		$response = WPRAG()->helpers->call_api_for_site( '/posts/query', 'POST', $data );

		$this->response = $response;

		if ( 200 !== $response['httpCode'] ) {
			$messages = Wp_Rag_AdminMessages::get_instance();
			$messages->add_message(
				'API call failed.',
				$response,
				'error'
			);
		}
	}
}