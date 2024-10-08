<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Webhooks_Integrations_demo_integration_Triggers_demo_update_user' ) ) :

	/**
	 * Class WP_Webhooks_Integrations_demo_integration_Triggers_demo_update_user
	 *
	 * This class contains the demo_update_user trigger related logic
	 *
	 * @package		WPRAG
	 * @subpackage	Integrations/WP_Webhooks_Integrations_demo_integration_Triggers_demo_update_user
	 * @author		Kashima, Kazuo
	 * @since		0.0.1
	 */
	class WP_Webhooks_Integrations_demo_integration_Triggers_demo_update_user {

		/**
		 * Register the callbacks that are used to 
		 * fire the webhook trigger
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @return	array	The event callbacks
		 */
		public function get_callbacks(){

			return array(
				array(
					'type' => 'action',
					'hook' => 'profile_update',
					'callback' => array( $this, 'wpwh_trigger_user_update' ),
					'priority' => 10,
					'arguments' => 2,
					'delayed' => true,
				),
			);

		}

		/**
		 * The details and configurations about 
		 * this specific trigger
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @return	array	The trigger configuration
		 */
		public function get_details(){

			$translation_ident = "trigger-update-user-description";

			$parameter = array(
				'user_object'	=> array( 'short_description' => WPWHPRO()->helpers->translate( 'The request will send the full user object as an array. Please see https://codex.wordpress.org/Class_Reference/WP_User for more details.', $translation_ident ) ),
				'user_meta'		=> array( 'short_description' => WPWHPRO()->helpers->translate( 'The user meta is also pushed to the user object. You will find it on the first layer of the object as well. ', $translation_ident ) ),
				'user_old_data'	=> array( 'short_description' => WPWHPRO()->helpers->translate( 'This is the object with the previous user object as an array. You can recheck your data on it as well.', $translation_ident ) ),
			);

			$description = WPWHPRO()->webhook->get_endpoint_description( 'trigger', array(
				'webhook_name'	=> 'Demo user updated',
				'webhook_slug'	=> 'demo_update_user',
				'post_delay'	=> true,
				'trigger_hooks'	=> array(
					array( 
						'hook'	=> 'profile_update',
						'url'	=> 'https://developer.wordpress.org/reference/hooks/profile_update/',
					),
				)
			) );

			return array(
				'trigger'			=> 'demo_update_user',
				'name'				=> WPWHPRO()->helpers->translate( 'Demo user updated', $translation_ident ),
				'sentence'			=> WPWHPRO()->helpers->translate( 'a user was updated', $translation_ident ),
				'parameter'			=> $parameter,
				'returns_code'		=> $this->get_demo( array() ),
				'short_description'	=> WPWHPRO()->helpers->translate( 'This webhook is a demo webhook and fires as soon as a user updates his profile.', $translation_ident ),
				'description'		=> $description,
				'callback'			=> 'test_user_update',
				'integration'		=> 'demo-integration', //the folder name of the integration
				'premium'			=> false,
			);

		}

		/**
		 * The callback used to fire the webhook trigger.
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @return	array	The trigger configuration
		 */
		public function wpwh_trigger_user_update( $user_id, $old_data ){
			$webhooks	= WPWHPRO()->webhook->get_hooks( 'trigger', 'demo_update_user' );
			$user_data	= (array) get_user_by( 'id', $user_id );

			$user_data['user_meta']		= get_user_meta( $user_id );
			$user_data['user_old_data']	= $old_data;
			$response_data				= array();

			foreach( $webhooks as $webhook ){

				$webhook_url_name = ( is_array($webhook) && isset( $webhook['webhook_url_name'] ) ) ? $webhook['webhook_url_name'] : null;

				if( $webhook_url_name !== null ){
					$response_data[ $webhook_url_name ] = WPWHPRO()->webhook->post_to_webhook( $webhook, $user_data );
				} else {
					$response_data[] = WPWHPRO()->webhook->post_to_webhook( $webhook, $user_data );
				}

			}

			do_action( 'wpwhpro/webhooks/demo_trigger_user_update', $user_id, $user_data, $response_data );
		}

		/**
		 * The demo data that is assigned to the trigger.
		 * This will be used to send a static demo request.
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @return	array	The demo data
		 */
		public function get_demo( $options = array() ){

			$data = array (
				'data' =>
					array (
						'ID' => '1',
						'user_login' => 'admin',
						'user_pass' => '$P$BVbptZxEcZV2yeLyYeN.O4ZeG8225d.',
						'user_nicename' => 'admin',
						'user_email' => 'admin@wp-webhooks.test',
						'user_url' => '',
						'user_registered' => '2018-11-06 14:19:18',
						'user_activation_key' => '',
						'user_status' => '0',
						'display_name' => 'admin',
					),
				'ID' => 1,
				'caps' =>
					array (
						'administrator' => true,
					),
				'cap_key' => 'wp_capabilities',
				'roles' =>
					array (
						0 => 'administrator',
					),
				'allcaps' =>
					array (
						'switch_themes' => true,
						'edit_themes' => true,
						'activate_plugins' => true,
						'edit_plugins' => true,
						'edit_users' => true,
						'edit_files' => true,
						'manage_options' => true,
						'moderate_comments' => true,
						'manage_categories' => true,
						'manage_links' => true,
						'upload_files' => true,
						'import' => true,
						'unfiltered_html' => true,
						'edit_posts' => true,
						'edit_others_posts' => true,
						'edit_published_posts' => true,
						'publish_posts' => true,
						'edit_pages' => true,
						'read' => true,
						'level_10' => true,
						'level_9' => true,
						'level_8' => true,
						'level_7' => true,
						'level_6' => true,
						'level_5' => true,
						'level_4' => true,
						'level_3' => true,
						'level_2' => true,
						'level_1' => true,
						'level_0' => true,
						'edit_others_pages' => true,
						'edit_published_pages' => true,
						'publish_pages' => true,
						'delete_pages' => true,
						'delete_others_pages' => true,
						'delete_published_pages' => true,
						'delete_posts' => true,
						'delete_others_posts' => true,
						'delete_published_posts' => true,
						'delete_private_posts' => true,
						'edit_private_posts' => true,
						'read_private_posts' => true,
						'delete_private_pages' => true,
						'edit_private_pages' => true,
						'read_private_pages' => true,
						'delete_users' => true,
						'create_users' => true,
						'unfiltered_upload' => true,
						'edit_dashboard' => true,
						'update_plugins' => true,
						'delete_plugins' => true,
						'install_plugins' => true,
						'update_themes' => true,
						'install_themes' => true,
						'update_core' => true,
						'list_users' => true,
						'remove_users' => true,
						'promote_users' => true,
						'edit_theme_options' => true,
						'delete_themes' => true,
						'export' => true,
						'administrator' => true,
					),
				'filter' => NULL,
				'user_meta' => array (
					'nickname' =>
						array (
							0 => 'admin',
						),
					'first_name' =>
						array (
							0 => 'Jon',
						),
					'last_name' =>
						array (
							0 => 'Doe',
						),
					'description' =>
						array (
							0 => 'My descriptio ',
						),
					'rich_editing' =>
						array (
							0 => 'true',
						),
					'syntax_highlighting' =>
						array (
							0 => 'true',
						),
					'comment_shortcuts' =>
						array (
							0 => 'false',
						),
					'admin_color' =>
						array (
							0 => 'fresh',
						),
					'use_ssl' =>
						array (
							0 => '0',
						),
					'show_admin_bar_front' =>
						array (
							0 => 'true',
						),
					'locale' =>
						array (
							0 => '',
						),
					'wp_capabilities' =>
						array (
							0 => 'a:1:{s:13:"administrator";b:1;}',
						),
					'wp_user_level' =>
						array (
							0 => '10',
						),
					'dismissed_wp_pointers' =>
						array (
							0 => 'wp111_privacy',
						),
					'show_welcome_panel' =>
						array (
							0 => '1',
						),
					'session_tokens' =>
						array (
							0 => 'a:1:{}',
						),
					'wp_dashboard_quick_press_last_post_id' =>
						array (
							0 => '4',
						),
					'community-events-location' =>
						array (
							0 => 'a:1:{s:2:"ip";s:9:"127.0.0.0";}',
						),
					'show_try_gutenberg_panel' =>
						array (
							0 => '0',
						),
				)
			);

			$data['user_old_data'] = array();

			return $data;
		}

	}

endif; // End if class_exists check.