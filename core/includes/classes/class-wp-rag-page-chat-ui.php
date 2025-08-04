<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Page_ChatUI
 *
 * This class handles rendering of the chat UI
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Page_ChatUI
 * @author      Kashima, Kazuo
 * @since       0.0.2
 */
class Wp_Rag_Page_ChatUI {
	const OPTION_NAME = 'wp_rag_chat_ui';

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * @since 0.0.4
	 */
	public function enqueue_admin_styles( $hook ) {
		wp_add_inline_style(
			'wp-admin',
			'.wrap.wp-rag-settings h3 {
				font-size: 1.2em;
				margin: 1em 0 1em;
			}
			/* Space between sections */
			.wrap.wp-rag-settings .form-table {
				/* margin-left: 1em; */
			}
			/* Margin above the first h3 */
			.wrap.wp-rag-settings h2 + h3 {
				margin-top: 1em;
			}'
		);
	}

	public function page_content() {
		?>
		<div class="wrap wp-rag-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_rag_options' );
				do_settings_sections( 'wp-rag-chat-ui' );
				submit_button( __( 'Submit' ) );
				?>
			</form>
		</div>
		<?php
	}

	public function add_appearance_section_and_fields() {
		$section_id = 'appearance_section';
		add_settings_section(
			$section_id,
			'Appearance',
			array( $this, 'appearance_section_callback' ),
			'wp-rag-chat-ui',
			array(
				'after_section' => '<hr />',
			)
		);

		add_settings_field(
			'custom_css',
			'Custom CSS',
			array( $this, 'custom_css_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);
	}

	public function appearance_section_callback() {
		echo '';
	}

	public function custom_css_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<textarea name="<?php echo self::OPTION_NAME; ?>[custom_css]" rows="5" cols="50" style="resize: both;"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
			><?php echo esc_textarea( $options['custom_css'] ?? '' ); ?></textarea>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function add_windows_settings_section_and_fields() {
		$section_id = 'windows_settings_section';
		add_settings_section(
			$section_id,
			'Labels & Messages', // This is the first "subsection", so show the title of the parent section.
			array( $this, 'windows_settings_section_callback' ),
			'wp-rag-chat-ui'
		);

		add_settings_field(
			'initial_message',
			'Initial Message',
			array( $this, 'initial_message_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);

		add_settings_field(
			'window_title',
			'Window Title',
			array( $this, 'window_title_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);
	}

	/**
	 * @since 0.0.4
	 */
	public function windows_settings_section_callback() {
		echo '<h3>Windows Settings</h3>';
	}

	public function initial_message_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[initial_message]"
				value="<?php echo esc_attr( $options['initial_message'] ?? '' ); ?>"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function window_title_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[window_title]"
				value="<?php echo esc_attr( $options['window_title'] ?? '' ); ?>"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function add_input_and_button_labels_section_and_fields() {
		$section_id = 'input_and_button_labels_section';
		add_settings_section(
			$section_id,
			'', // Show nothing here, but in the callback with <h3>.
			array( $this, 'input_and_button_labels_section_callback' ),
			'wp-rag-chat-ui'
		);

		add_settings_field(
			'input_placeholder_text',
			'Input Placeholder Text',
			array( $this, 'input_placeholder_text_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);

		add_settings_field(
			'send_button_text',
			'Send Button Text',
			array( $this, 'send_button_text_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);
	}

	/**
	 * @since 0.0.4
	 */
	public function input_and_button_labels_section_callback() {
		echo '<h3>Input & Button Labels</h3>';
	}

	/**
	 * @since 0.0.4
	 */
	public function input_placeholder_text_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[input_placeholder_text]"
				value="<?php echo esc_attr( $options['input_placeholder_text'] ?? '' ); ?>"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function send_button_text_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[send_button_text]"
				value="<?php echo esc_attr( $options['send_button_text'] ?? '' ); ?>"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function add_participant_names_section_and_fields() {
		$section_id = 'participant_names_section';
		add_settings_section(
			$section_id,
			'', // Show nothing here, but in the callback with <h3>.
			array( $this, 'participant_names_section_callback' ),
			'wp-rag-chat-ui',
			array(
				'after_section' => '<hr />',
			)
		);

		add_settings_field(
			'bot_name',
			'Bot Name',
			array( $this, 'bot_name_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);

		add_settings_field(
			'user_name',
			'User Name',
			array( $this, 'user_name_field_render' ),
			'wp-rag-chat-ui',
			$section_id
		);
	}

	/**
	 * @since 0.0.4
	 */
	public function participant_names_section_callback() {
		echo '<h3>Participant Names</h3>';
	}

	/**
	 * @since 0.0.4
	 */
	public function bot_name_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[bot_name]"
				value="<?php echo esc_attr( $options['bot_name'] ?? '' ); ?>"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
		/>
		<?php
	}

	/**
	 * @since 0.0.4
	 */
	public function user_name_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[user_name]"
				value="<?php echo esc_attr( $options['user_name'] ?? '' ); ?>"
			<?php
			WPRAG()->form->disabled_unless_verified()
			?>
		/>
		<?php
	}

	public function add_display_options_section_and_fields() {
		add_settings_section(
			'display_options_section',
			'Display options',
			array( $this, 'display_options_section_callback' ),
			'wp-rag-chat-ui'
		);

		add_settings_field(
			'display_context_links',
			'Display context links',
			array( $this, 'display_context_links_field_render' ),
			'wp-rag-chat-ui',
			'display_options_section'
		);
	}

	function display_options_section_callback() {
		echo '';
	}

	function display_context_links_field_render() {
		$options = get_option( self::OPTION_NAME );
		$value   = $options['display_context_links'] ?? 'no';
		?>
		<input type="radio" name="<?php echo self::OPTION_NAME; ?>[display_context_links]" value="no"
				<?php
				if ( 'no' === $value ) {
					echo 'checked="checked"';
				}
				WPRAG()->form->disabled_unless_verified();
				?>

		/>No
		<input type="radio" name="<?php echo self::OPTION_NAME; ?>[display_context_links]" value="yes"
			<?php
			if ( 'yes' === $value ) {
				echo 'checked="checked"';
			}
			WPRAG()->form->disabled_unless_verified();
			?>

		/>Yes
		<?php
	}
}