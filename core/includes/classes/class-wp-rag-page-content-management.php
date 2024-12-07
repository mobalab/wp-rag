<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Page_ContentManagement
 *
 * This class handles rendering of the content management section
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Page_ContentManagement
 * @author      Kashima, Kazuo
 * @since       0.0.2
 */
class Wp_Rag_Page_ContentManagement {
	public function page_content() {
		$result      = WPRAG()->helpers->call_api_for_site( '/posts/status' );
		$post_status = 200 === $result['httpCode'] ? $result['response']
			: array(
				'post_count'      => 0,
				'embedding_count' => 0,
			);
		$result      = WPRAG()->helpers->call_api_for_site( '/tasks/status' );
		$task_status = 200 === $result['httpCode'] ? $result['response']
			: array(
				'last_import_task' => null,
				'last_embed_task'  => null,
			);

		?>
		<div class="wrap">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<form method="post" action="">
				<h2>Post Sync Controls</h2>
				<?php
				settings_fields( 'wp_rag_options' );

				echo '<table class="form-table" role="presentation">';
				do_settings_fields( 'wp-rag-content-management', 'import_posts_section' );
				echo '</table>';

				submit_button( __( 'Import Posts' ), 'primary', 'wp_rag_import_submit' );

				echo '<table class="form-table" role="presentation">';
				do_settings_fields( 'wp-rag-content-management', 'generate_embeddings_section' );
				echo '</table>';

				submit_button( __( 'Generate Embeddings' ), 'primary', 'wp_rag_generate_submit' );
				?>
			</form>
			<hr />

			<h2>Content Status</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						Number of the posts imported to the WP RAG API
					</th>
					<td>
						<?php echo esc_html( $post_status['post_count'] ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						Number of the created embeddings
					</th>
					<td>
						<?php echo esc_html( $post_status['embedding_count'] ); ?>
					</td>
				</tr>
			</table>

			<h2>Task Status</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th>Name</th>
					<th>Params</th>
					<th>Result</th>
					<th>Datetime</th>
				</tr>
				<tr>
					<th scope="row">
						Last successful "Import WordPress posts"
					</th>
					<td>
						<?php
						if ( ! empty( $task_status['last_import_task'] ) ) {
							echo esc_html( wp_json_encode( $task_status['last_import_task']['params'] ) );
						}
						?>
					</td>
					<td>
						<?php
						if ( ! empty( $task_status['last_import_task'] ) ) {
							echo esc_html( wp_json_encode( $task_status['last_import_task']['result'] ) );
						}
						?>
					</td>
					<td>
						<?php
						if ( ! empty( $task_status['last_import_task'] ) ) {
							echo esc_html( $task_status['last_import_task']['updated_at'] );
						}
						?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						Last successful "Embed"
					</th>
					<td>
						<?php
						if ( ! empty( $task_status['last_embed_task'] ) ) {
							echo esc_html( wp_json_encode( $task_status['last_embed_task']['params'] ) );
						}
						?>
					</td>
					<td>
						<?php
						if ( ! empty( $task_status['last_embed_task'] ) ) {
							echo esc_html( wp_json_encode( $task_status['last_embed_task']['result'] ) );
						}
						?>
					</td>
					<td>
						<?php
						if ( ! empty( $task_status['last_embed_task'] ) ) {
							echo esc_html( $task_status['last_embed_task']['updated_at'] );
						}
						?>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	public function add_import_posts_section_and_fields() {
		add_settings_section(
			'import_posts_section',
			'Import posts', // Not used.
			null,
			'wp-rag-content-management'
		);

		add_settings_field(
			'import_from',
			'From date:',
			array( $this, 'import_from_field_render' ),
			'wp-rag-content-management',
			'import_posts_section'
		);

		add_settings_field(
			'import_type',
			'Import type',
			array( $this, 'import_type_field_render' ),
			'wp-rag-content-management',
			'import_posts_section'
		);
	}


	public function import_from_field_render() {
		?>
		<input type="date" name="wp_rag_import_from" value="" />
		<?php
	}

	public function import_type_field_render() {
		?>
		<input id="wp_rag_import_type_post" type="radio" name="wp_rag_import_type" value="post" />
		<label for="wp_rag_import_type_post">
			Post
		</label>
		<input id="wp_rag_import_type_page" type="radio" name="wp_rag_import_type" value="page" />
		<label for="wp_rag_import_type_page">
			Page
		</label>
		<?php
	}

	public function add_generate_embeddings_section_and_fields() {
		add_settings_section(
			'generate_embeddings_section',
			'Embed', // Not used.
			null,
			'wp-rag-content-management'
		);

		add_settings_field(
			'generate_from',
			'From date:',
			array( $this, 'generate_from_field_render' ),
			'wp-rag-content-management',
			'generate_embeddings_section'
		);
	}

	public function generate_from_field_render() {
		?>
		<input type="date" name="wp_rag_generate_from" value="" />
		<?php
	}

	/**
	 * @return void
	 */
	function handle_import_form_submission() {
		check_admin_referer( 'wp_rag_options-options' );
		$post_type = isset( $_POST['wp_rag_import_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_rag_import_type'] ) ) : 'post';
		$params    = array( 'post_type' => $post_type );
		if ( ! empty( $_POST['wp_rag_import_from'] ) ) {
			// <input type="date" /> sends a date of ISO 8601 format.
			$timezone = wp_timezone();
			$date_str = sanitize_text_field( wp_unslash( $_POST['wp_rag_import_from'] ) );
			$date     = new DateTime( $date_str, $timezone );

			$params['modified_after'] = $date->format( 'Y-m-d\TH:i:s' );
		}

		$data     = array(
			'task_type' => 'ImportWordpressPosts',
			'params'    => $params,
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
	function handle_generate_form_submission() {
		check_admin_referer( 'wp_rag_options-options' );

		$params = array();
		if ( ! empty( $_POST['wp_rag_generate_from'] ) ) {
			// <input type="date" /> sends a date of ISO 8601 format.
			$timezone = wp_timezone();
			$date_str = sanitize_text_field( wp_unslash( $_POST['wp_rag_generate_from'] ) );
			$date     = new DateTime( $date_str, $timezone );

			$params['modified_after'] = $date->format( 'Y-m-d\TH:i:s' );
		}

		$data     = array(
			'task_type' => 'Embed',
			'params'    => $params,
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
}