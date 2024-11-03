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

	public function page_content() {
		?>
		<div class="wrap">
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
		add_settings_section(
			'appearance_section',
			'Appearance',
			array( $this, 'appearance_section_callback' ),
			'wp-rag-chat-ui'
		);

		add_settings_field(
			'initial_message',
			'Initial message',
			array( $this, 'initial_message_field_render' ),
			'wp-rag-chat-ui',
			'appearance_section'
		);

		add_settings_field(
			'custom_css',
			'Custom CSS',
			array( $this, 'custom_css_field_render' ),
			'wp-rag-chat-ui',
			'appearance_section'
		);
	}


	function appearance_section_callback() {
		echo '';
	}

	public function initial_message_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<input type="text" name="<?php echo self::OPTION_NAME; ?>[initial_message]"
				value="<?php echo esc_attr( $options['initial_message'] ?? '' ); ?>"
			<?php
			if ( ! WPRAG()->helpers->is_verified() ) {
				echo 'disabled';
			}
			?>
		/>
		<?php
	}

	public function custom_css_field_render() {
		$options = get_option( self::OPTION_NAME );
		?>
		<textarea name="<?php echo self::OPTION_NAME; ?>[custom_css]"
			<?php
			if ( ! WPRAG()->helpers->is_verified() ) {
				echo 'disabled';
			}
			?>
			><?php echo esc_textarea( $options['custom_css'] ?? '' ); ?></textarea>
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
		$value   = $options['display_context_links'] ?? "no";
		?>
		<input type="radio" name="<?php echo self::OPTION_NAME; ?>[display_context_links]" value="no"
				<?php
				if ( "no" === $value ) {
					echo 'checked="checked"';}
				?>
				/>No
		<input type="radio" name="<?php echo self::OPTION_NAME; ?>[display_context_links]" value="yes"
			<?php
			if ( "yes" === $value ) {
				echo 'checked="checked"';}
			?>
			/>Yes
		<?php
	}
}