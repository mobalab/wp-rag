<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_TermsPPNotice
 *
 * This class provides functions for the notice of the terms and privacy policy
 *
 * @package    WPRAG
 * @subpackage Classes/Wp_Rag_TermsPPNotice
 * @author     Mobalab, KK
 * @since      0.4.0
 */
class Wp_Rag_TermsPPNotice {

	const OPTION_NAME = 'wp_rag_terms_pp_notice';


	/**
	 * Add hooks necessary to display the notice for terms and privacy policy
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'admin_notices', array( $this, 'show_terms_pp_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_wp_rag_accept_terms_pp', array( $this, 'accept_terms_pp' ) );
	}//end add_hooks()


	/**
	 * Loads the script and style files
	 */
	public function enqueue_scripts() {
		$options = get_option( self::OPTION_NAME );
		if ( $options && isset( $options['agreed'] ) && $options['agreed'] ) {
			return;
		}

		wp_enqueue_script(
			'wp-rag-terms-pp-notices',
			plugins_url( 'core/includes/assets/js/terms-pp-notices.js', WPRAG_PLUGIN_FILE ),
			array( 'jquery' ),
			WPRAG_VERSION,
			true
		);

		wp_localize_script(
			'wp-rag-terms-pp-notices',
			'wpRag',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp_rag_accept_terms_pp' ),
				'strings'  => array(
					'error'   => __( 'An error occurred. Please try again.', 'wp-rag' ),
					'success' => __( 'Terms accepted successfully.', 'wp-rag' ),
				),
			)
		);
	}//end enqueue_scripts()


	/**
	 * Shows the notice for the terms and privacy policy if the user hasn't agreed to them yet
	 *
	 * @return void
	 */
	public function show_terms_pp_notice() {
		$options = get_option( self::OPTION_NAME );
		if ( $options && isset( $options['agreed'] ) && $options['agreed'] ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible" id="wp-rag-terms-pp-notice">
			<p>
				<strong><?php esc_html_e( 'Important Update:', 'wp-rag' ); ?></strong>
				<?php esc_html_e( 'We have updated our Terms of Service and Privacy Policy. Please review and accept them to continue using WP RAG plugin.', 'wp-rag' ); ?>
			</p>
			<p>
				<a href="https://services.mobalab.net/wp-rag/terms-privacy.html" target="_blank" class="button"><?php esc_html_e( 'View Terms of Service and Privacy Policy', 'wp-rag' ); ?></a>
				<button type="button" class="button button-primary" id="wp-rag-accept-terms-pp"><?php esc_html_e( 'Accept Terms and Privacy Policy', 'wp-rag' ); ?></button>
			</p>
		</div>
		<?php
	}//end show_terms_pp_notice()


	/**
	 * An Ajax function to be called when the user accepts the terms and privacy policy
	 *
	 * @return void
	 */
	public function accept_terms_pp() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wp_rag_accept_terms_pp' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$options = array(
			'agreed'    => true,
			'agreed_at' => current_time( 'mysql' ),
		);
		update_option( self::OPTION_NAME, $options );

		wp_send_json_success();
	}//end accept_terms_pp()
}//end class