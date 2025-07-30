<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Page_AiConfiguration
 *
 * This class handles rendering of the AI configuration
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Page_AiConfiguration
 * @author      Kashima, Kazuo
 * @since       0.0.1
 */
class Wp_Rag_Page_AiConfiguration {
	const OPTION_NAME = 'wp_rag_ai_options';

	/**
	 * Enqueue scripts for AI configuration page
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'wp-rag-ai-configuration',
			plugins_url( 'core/includes/assets/js/ai-configuration.js', WPRAG_PLUGIN_FILE ),
			array( 'jquery' ),
			WPRAG_VERSION,
			true
		);
	}

	private function construct_request_for_api( $sanitized_input ) {
		$request = array(
			'openai_api_key'      => $sanitized_input['openai_api_key'],
			'embedding_model_id'  => $sanitized_input['embedding_model_id'] ?? null,
			'generation_model_id' => $sanitized_input['generation_model_id'] ?? null,
			'ai_settings'         => array(
				'generation' => array(
					'prompt' => $sanitized_input['generation']['prompt'],
				),
			),
		);
		if ( '' !== $sanitized_input['search']['number_of_documents'] || '' !== $sanitized_input['search']['score_threshold'] ) {
			$request['ai_settings']['search'] = array();
		}
		if ( '' !== $sanitized_input['search']['number_of_documents'] ) {
			$request['ai_settings']['search']['k'] = (int) $sanitized_input['search']['number_of_documents'];
		}
		if ( '' !== $sanitized_input['search']['score_threshold'] ) {
			$request['ai_settings']['search']['score_threshold'] = (float) $sanitized_input['search']['score_threshold'];
		}

		return $request;
	}


	function save_config_api( $input ) {
		$sanitized_input = sanitize_post( $input, 'db' );

		$auth_data = WPRAG()->helpers->get_auth_data();
		if ( empty( $auth_data['site_id'] ) || empty( $auth_data['verified_at'] ) ) {
			return get_option( self::OPTION_NAME );
		} else {
			$api_path = '/config';

			$post_data = $this->construct_request_for_api( $sanitized_input );
			$response  = WPRAG()->helpers->call_api_for_site( $api_path, 'PUT', $post_data );

			if ( 200 !== $response['httpCode'] ) {
				$messages = Wp_Rag_AdminMessages::get_instance();
				$messages->add_message(
					'API call failed.',
					$response,
					'error'
				);
				return get_option( self::OPTION_NAME );
			} else {
				// Pass to the default action.
				return $sanitized_input;
			}
		}
	}

	public function page_content() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_rag_options' );
				do_settings_sections( 'wp-rag-ai-configuration' );
				submit_button( __( 'Submit' ) );
				?>
			</form>
		</div>
		<?php
	}

	public function add_api_keys_section_and_fields() {
		add_settings_section(
			'api_keys_section', // Section ID
			'API Keys', // Title
			array( $this, 'api_keys_section_callback' ), // Callback
			'wp-rag-ai-configuration' // Slug of the page
		);

		add_settings_field(
			'wp_rag_openai_api_key', // Field ID
			'OpenAI API key', // Title
			array( $this, 'openai_api_key_field_render' ), // callback
			'wp-rag-ai-configuration', // Page slug
			'api_keys_section' // Section this field belongs to
		);

		// TODO Add Claude API key.
	}


	function api_keys_section_callback() {
		echo 'Set API keys to use AI models.';
	}

	public function openai_api_key_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[openai_api_key]"
				value="<?php echo esc_attr( $options['openai_api_key'] ?? '' ); ?>"
			<?php WPRAG()->form->maybe_disabled(); ?>
		/>
		<?php
	}

	public function claude_api_key_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[claude_api_key]"
				value="<?php echo esc_attr( $options['claude_api_key'] ?? '' ); ?>"
			<?php WPRAG()->form->maybe_disabled(); ?>
		/>
		<?php
	}

	public function add_model_selection_section_and_fields() {
		add_settings_section(
			'model_selection_section', // Section ID
			'AI Model (Premium Feature)', // Title
			array( $this, 'model_selection_section_callback' ), // Callback
			'wp-rag-ai-configuration' // Slug of the page
		);

		add_settings_field(
			'wp_rag_embedding_model', // Field ID
			'Embedding model', // Title
			array( $this, 'embedding_model_field_render' ), // callback
			'wp-rag-ai-configuration', // Page slug
			'model_selection_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_generation_model', // Field ID
			'Generation model', // Title
			array( $this, 'generation_model_field_render' ), // callback
			'wp-rag-ai-configuration', // Page slug
			'model_selection_section' // Section this field belongs to
		);
	}

	function model_selection_section_callback() {
		echo 'Select AI models.';
	}

	function embedding_model_field_render() {
		$options       = get_option( self::OPTION_NAME );
		$current_value = $options['embedding_model_id'] ?? 'openai-text-embedding-3-large';

		// Get embedding count
		$embedding_count = 0;
		$auth_data       = WPRAG()->helpers->get_auth_data();
		if ( ! empty( $auth_data['site_id'] ) && ! empty( $auth_data['verified_at'] ) ) {
			$result = WPRAG()->helpers->call_api_for_site( '/posts/status' );
			if ( 200 === $result['httpCode'] && isset( $result['response']['embedding_count'] ) ) {
				$embedding_count = $result['response']['embedding_count'];
			}
		}
		?>
		<select name="<?php echo self::OPTION_NAME; ?>[embedding_model_id]"<?php WPRAG()->form->maybe_disabled(); ?>>
			<option value="openai-text-embedding-3-large" <?php selected( $current_value, 'openai-text-embedding-3-large' ); ?>>OpenAI text-embedding-3-large</option>
			<option value="openai-text-embedding-3-small" <?php selected( $current_value, 'openai-text-embedding-3-small' ); ?>>OpenAI text-embedding-3-small</option>
		</select>
		<?php if ( $embedding_count > 0 ) : ?>
		<div id="embedding-model-change-warning" class="notice notice-warning inline" style="display: none; margin-top: 10px;">
			<p><strong>Notice:</strong> Changing the embedding model will require re-indexing of existing posts. Depending on the number of posts, this synchronization process may take some time.</p>
		</div>
		<?php endif; ?>
		<?php
	}

	function generation_model_field_render() {
		$options       = get_option( self::OPTION_NAME );
		$current_value = $options['generation_model_id'] ?? 'openai-gpt-4o';
		?>
		<select name="<?php echo self::OPTION_NAME; ?>[generation_model_id]"<?php WPRAG()->form->maybe_disabled(); ?>>
			<option value="openai-gpt-4o" <?php selected( $current_value, 'openai-gpt-4o' ); ?>>OpenAI gpt-4o</option>
			<option value="openai-gpt-4o-mini" <?php selected( $current_value, 'openai-gpt-4o-mini' ); ?>>OpenAI gpt-4o-mini</option>
			<option value="openai-o1-preview" <?php selected( $current_value, 'openai-o1-preview' ); ?>>OpenAI o1-preview</option>
		</select>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function add_search_parameters_section_and_fields() {
		add_settings_section(
			'search_parameters_section', // Section ID
			'Search Parameters (Premium Feature)', // Title
			array( $this, 'search_parameters_section_callback' ), // Callback
			'wp-rag-ai-configuration' // Slug of the page
		);

		add_settings_field(
			'wp_rag_number_of_documents', // Field ID
			'Number of Documents (k)', // Title
			array( $this, 'number_of_documents_field_render' ), // callback
			'wp-rag-ai-configuration', // Page slug
			'search_parameters_section' // Section this field belongs to
		);

		add_settings_field(
			'wp_rag_similarity_threshold', // Field ID
			'Similarity Threshold', // Title
			array( $this, 'score_threshold_field_render' ), // callback
			'wp-rag-ai-configuration', // Page slug
			'search_parameters_section' // Section this field belongs to
		);
	}

	/**
	 * @since 0.0.4
	 */
	public function search_parameters_section_callback() {
		echo '';
	}

	/**
	 * @since 0.0.4
	 */
	public function number_of_documents_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="number" name="<?php echo self::OPTION_NAME; ?>[search][number_of_documents]"
				value="<?php echo esc_attr( $options['search']['number_of_documents'] ?? '' ); ?>"
				min="1" max="8"
			<?php WPRAG()->form->maybe_disabled(); ?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function score_threshold_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="number" name="<?php echo self::OPTION_NAME; ?>[search][score_threshold]"
				value="<?php echo esc_attr( $options['search']['score_threshold'] ?? '' ); ?>"
				min="0" max="1" step="0.01"
			<?php WPRAG()->form->maybe_disabled(); ?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function add_generation_parameters_section_and_fields() {
		add_settings_section(
			'generation_parameters_section', // Section ID
			'Generation Parameters (Premium Feature)', // Title
			array( $this, 'generation_parameters_section_callback' ), // Callback
			'wp-rag-ai-configuration' // Slug of the page
		);

		add_settings_field(
			'wp_rag_prompt', // Field ID
			'Prompt', // Title
			array( $this, 'prompt_field_render' ), // callback
			'wp-rag-ai-configuration', // Page slug
			'generation_parameters_section' // Section this field belongs to
		);
	}

	/**
	 * @since 0.0.4
	 */
	public function generation_parameters_section_callback() {
		echo '';
	}

	/**
	 * @since 0.0.4
	 */
	public function prompt_field_render() {
		$options = get_option( self::OPTION_NAME );
		$example = "Please provide an answer based on the following context only.\n\nContext:";
		?>
		<textarea name="<?php echo self::OPTION_NAME; ?>[generation][prompt]" rows="10" class="large-text code"
			<?php WPRAG()->form->maybe_disabled(); ?>
			><?php echo esc_textarea( $options['generation']['prompt'] ?? '' ); ?></textarea>

		<p class="description">Enter your prompt template. The context will be automatically appended after this prompt.</p>

		<div class="wp-rag-example">
			<h4>Example Prompt</h4>
			<pre class="wp-rag-code-preview"><?php echo esc_html( $example ); ?></pre>
		</div>
		<?php
	}
}
