<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Webhooks_Integrations_demo_integration_Triggers_demo_request_received' ) ) :

	/**
	 * Class WP_Webhooks_Integrations_demo_integration_Triggers_demo_request_received
	 *
	 * This class contains the demo_request_received trigger related logic
	 *
	 * @package		WPRAG
	 * @subpackage	Integrations/WP_Webhooks_Integrations_demo_integration_Triggers_demo_request_received
	 * @author		Kashima, Kazuo
	 * @since		0.0.1
	 */
	class WP_Webhooks_Integrations_demo_integration_Triggers_demo_request_received {

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

			$translation_ident = "trigger-demo_request_received-description";

			$parameter = array(
				'custom_construct' => array( 'short_description' => WPWHPRO()->helpers->translate( '(Mixed) The data that was sent along with the HTTP call that was made to the receivable URL.', $translation_ident ) ),
			);

			$description = WPWHPRO()->webhook->get_endpoint_description( 'trigger', array(
				'webhook_name' => 'Demo HTTP request received',
				'webhook_slug' => 'demo_request_received',
				'post_delay' => false,
				'steps' => array(
					WPWHPRO()->helpers->translate( 'Add a URL to this trigger on which you want to receive the data.', $translation_ident ),
					WPWHPRO()->helpers->translate( 'Go into the settings for your added URL and copy the receivable URL (The dynamically created URL).', $translation_ident ),
					WPWHPRO()->helpers->translate( 'Place the receivable URL into the service of your choice and start sending data.', $translation_ident ),
				),
				'tipps' => array(
					WPWHPRO()->helpers->translate( 'To receive this data on the receivable URL, please send it from an external URL to the dynamically created URL.', $translation_ident ),
					WPWHPRO()->helpers->translate( 'The receivable URL accepts content types such as JSON, form data, or XML.', $translation_ident ),
				)
			) );

			$settings = array(
				'load_default_settings' => false,
				'data' => array(
					'wpwhpro_demo_return_full_request' => array(
						'id'			=> 'wpwhpro_demo_return_full_request',
						'type'			=> 'checkbox',
						'label'			=> WPWHPRO()->helpers->translate( 'Send full request', $translation_ident ),
						'placeholder'	=> '',
						'required'		=> false,
						'description'	=> WPWHPRO()->helpers->translate( 'Send the full, validated request instead of the payload (body) data only. This gives you access to header, cookies, response type and much more.', $translation_ident )
					),
				)
			);

			return array(
				'trigger'			=> 'demo_request_received',
				'name'				=> WPWHPRO()->helpers->translate( 'Demo HTTP request received', $translation_ident ),
				'sentence'			=> WPWHPRO()->helpers->translate( 'a demo HTTP request was received', $translation_ident ),
				'parameter'			=> $parameter,
				'settings'			=> $settings,
				'returns_code'		=> $this->get_demo( array() ),
				'short_description'	=> WPWHPRO()->helpers->translate( 'This webhook fires as soon as a HTTP request was received from an external URL.', $translation_ident ),
				'description'		=> $description,
				'integration'		=> 'demo-integration', //the folder name of the integration
				'receivable_url'	=> true, //turn the trigger into a receivable trigger
				'premium'			=> false,
			);

		}

		/**
		 * The function used to execute the trigger once data 
		 * was sent to the dynamically created, receivable URL
		 * 
		 * @since	0.0.1
		 * @access	public
		 *
		 * @param	array	$return_data	The data returned to the webhook caller
		 * @param	array	$response_body	The data about the current request
		 * @param	string	$trigger_url_name	The name of the current trigger URL
		 * 
		 * @return	array	The response body
		 */
		public function execute( $return_data, $response_body, $trigger_url_name ){

			$translation_ident = "trigger-demo_request_received-description";

			if( $trigger_url_name !== null ){
				$webhooks = WPWHPRO()->webhook->get_hooks( 'trigger', 'demo_request_received', $trigger_url_name );
				if( ! empty( $webhooks ) ){
					$webhooks = array( $webhooks );
				} else {
					$return_data['msg'] = WPWHPRO()->helpers->translate( 'We could not locate a callable trigger URL.', $translation_ident );
					return $return_data;
				}
			} else {
				$webhooks = WPWHPRO()->webhook->get_hooks( 'trigger', 'demo_request_received' );
			}
			

			$payload = $response_body['content'];

			$response_data_array = array();

			foreach( $webhooks as $webhook ){

				$webhook_url_name = ( is_array($webhook) && isset( $webhook['webhook_url_name'] ) ) ? $webhook['webhook_url_name'] : null;
				$is_valid = true;

				if( isset( $webhook['settings'] ) ){
					foreach( $webhook['settings'] as $settings_name => $settings_data ){
		
					if( $settings_name === 'wpwhpro_demo_return_full_request' && ! empty( $settings_data ) ){
						$payload = $response_body;
					}
		
					}
				}

				if( $is_valid ){

					$webhook_response = WPWHPRO()->webhook->post_to_webhook( $webhook, $payload, array( 'blocking' => true ) );

					if( $webhook_url_name !== null ){
						$response_data_array[ $webhook_url_name ] = $webhook_response;
					} else {
						$response_data_array[] = $webhook_response;
					}
				}

			}

			$return_data['success']	= true;
			$return_data['data']	= ( count( $response_data_array ) > 1 ) ? $response_data_array : reset( $response_data_array );

			do_action( 'wpwhpro/webhooks/trigger_demo_request_received', $return_data, $response_body, $trigger_url_name, $response_data_array );

			return $return_data;
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
		public function get_demo( $options = array() ) {

			$data = array (
				'custom_construct' => 'The data that was sent to the receivable data URL. Or the full request array.',
			);

			return $data;
		}

	}

endif; // End if class_exists check.