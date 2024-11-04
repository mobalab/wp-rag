<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

define( 'WPRAG_NAME', 'WP RAG' );
define( 'WPRAG_PLUGIN_FILE', __FILE__ );
define( 'WPRAG_PLUGIN_BASE', plugin_basename( WPRAG_PLUGIN_FILE ) );
define( 'WPRAG_PLUGIN_DIR', plugin_dir_path( WPRAG_PLUGIN_FILE ) );

require_once WPRAG_PLUGIN_DIR . 'core/class-wp-rag.php';
require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-helpers.php';
require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-main.php';
require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-general-settings.php';
require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-ai-configuration.php';
require_once WPRAG_PLUGIN_DIR . 'core/includes/classes/class-wp-rag-page-chat-ui.php';

function WPRAG() {
	return Wp_Rag::instance();
}

if ( WPRAG()->helpers->is_verified() ) {
	WPRAG()->helpers->call_api_for_site( '', 'DELETE' );
}

$option_names = array(
	WPRAG()::OPTION_NAME_FOR_AUTH_DATA,
	WPRAG()->pages['general-settings']::OPTION_NAME,
	WPRAG()->pages['ai-configuration']::OPTION_NAME,
	WPRAG()->pages['chat-ui']::OPTION_NAME,
);
foreach ( $option_names as $option_name ) {
	delete_option( $option_name );
}
