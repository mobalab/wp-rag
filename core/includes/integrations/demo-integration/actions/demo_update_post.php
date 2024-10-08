<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Webhooks_Integrations_demo_integration_Actions_demo_update_post' ) ) :

	/**
	 * Class WP_Webhooks_Integrations_demo_integration_Actions_demo_update_post
	 *
	 * This class contains the demo_update_post action related logic
	 *
	 * @package		WPRAG
	 * @subpackage	Integrations/WP_Webhooks_Integrations_demo_integration_Actions_demo_update_post
	 * @author		Kashima, Kazuo
	 * @since		0.0.1
	 */
	class WP_Webhooks_Integrations_demo_integration_Actions_demo_update_post {

		/**
		 * The details and configurations about 
		 * this specific action
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @return	array	The action configuration
		 */
		public function get_details(){

			$translation_ident = "action-demo_update_post-description";

			$parameter = array(
				'post_id' => array(
					'required'			=> true,
					'label'				=> WPWHPRO()->helpers->translate( 'The post ID', $translation_ident ), 
					'short_description'	=> WPWHPRO()->helpers->translate( '(integer) The ID of the post you would like to update.', $translation_ident ),
				),
				'post_title'			=> array( 
					'label'				=> WPWHPRO()->helpers->translate( 'The new post title', $translation_ident ), 
					'short_description'	=> WPWHPRO()->helpers->translate( '(string) Set the new title for the post.', $translation_ident ),
				),
			);

			$returns = array(
				'success'	=> array( 'short_description' => WPWHPRO()->helpers->translate( '(Bool) True if the action was successful, false if not. E.g. array( \'success\' => true )', $translation_ident ) ),
				'msg'		=> array( 'short_description' => WPWHPRO()->helpers->translate( '(string) A message with more information about the current request. E.g. array( \'msg\' => "This action was successful." )', $translation_ident ) ),
				'data'		=> array( 'short_description' => WPWHPRO()->helpers->translate( '(array) Further details about the sent data.', $translation_ident ) ),
			);

			$returns_code = array (
				'success'	=> true,
				'msg'		=> 'The post was successfully updated using the demo action.',
			);

			$description = WPWHPRO()->webhook->get_endpoint_description( 'action', array(
				'webhook_name'	=> 'Demo update post',
				'webhook_slug'	=> 'demo_update_post',
				'steps'			=> array(
					WPWHPRO()->helpers->translate( 'It is also required to set the <strong>post_id</strong> argument. Please set it to the ID of the post you want to update the title for.', $translation_ident ),
				),
			) );

			return array(
				'action'			=> 'demo_update_post',
				'name'				=> WPWHPRO()->helpers->translate( 'Demo update post', $translation_ident ),
				'sentence'			=> WPWHPRO()->helpers->translate( 'update a post using the demo action', $translation_ident ),
				'parameter'			=> $parameter,
				'returns'			=> $returns,
				'returns_code'		=> $returns_code,
				'short_description'	=> WPWHPRO()->helpers->translate( 'This is a demo action that allows you to update the post title of a post.', $translation_ident ),
				'description'		=> $description,
				'integration'		=> 'demo-integration',
				'premium'			=> false,
			);

		}

		/**
		 * The function used to execute the action once data 
		 * was sent to the webhook action URL
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @param	array	$return_data	The data returned to the webhook caller
		 * @param	array	$response_body	The data about the current request
		 * 
		 * @return	array	The response body
		 */
		public function execute( $return_data, $response_body ){

			$return_args = array(
				'success' => false,
				'msg' => '',
			);

			$post_id	 = intval( WPWHPRO()->helpers->validate_request_value( $response_body['content'], 'post_id' ) );
			$post_title	 = WPWHPRO()->helpers->validate_request_value( $response_body['content'], 'post_title' );
			
			if( empty( $post_id ) ){
				$return_args['msg'] = WPWHPRO()->helpers->translate( "Please set the post_id argument.", 'action-demo_update_post-failure' );
				return $return_args;
			}

			$args = array(
				'ID' => $post_id,
				'post_title' => $post_title,
			);

			$check = wp_update_post( $args );

			if( ! empty( $check ) && ! is_wp_error( $check ) ){
				$return_args['msg']		= WPWHPRO()->helpers->translate( "The post was successfully updated using the demo action.", 'action-demo_update_post-success' );
				$return_args['success']	= true;
			} else {
				$return_args['msg']		= WPWHPRO()->helpers->translate( "An error occured while updating the post using the demo request.", 'action-demo_update_post-success' );
			}

			return $return_args;
	
		}

	}

endif; // End if class_exists check.