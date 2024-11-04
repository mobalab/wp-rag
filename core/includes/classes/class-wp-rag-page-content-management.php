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
}