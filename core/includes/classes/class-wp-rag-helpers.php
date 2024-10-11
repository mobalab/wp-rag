<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Wp_Rag_Helpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		WPRAG
 * @subpackage	Classes/Wp_Rag_Helpers
 * @author		Kashima, Kazuo
 * @since		0.0.1
 */
class Wp_Rag_Helpers{

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * HELPER COMMENT START
	 *
	 * Within this class, you can define common functions that you are 
	 * going to use throughout the whole plugin. 
	 * 
	 * Down below you will find a demo function called output_text()
	 * To access this function from any other class, you can call it as followed:
	 * 
	 * WPRAG()->helpers->output_text( 'my text' );
	 * 
	 */
	 
	/**
	 * Output some text
	 *
	 * @param	string	$text	The text to output
	 * @since	0.0.1
	 *
	 * @return	void
	 */
	 public function output_text( $text = '' ){
		 echo $text;
	 }

	 /**
	  * HELPER COMMENT END
	  */

	/**
	 * Calls the WP RAG API
	 * @param $api_path e.g. /api/sites/1
	 * @param $method e.g. "POST", "PUT", etc.
	 * @param $data
	 * @param $headers
	 *
	 * @return array
	 */
	function call_api( $api_path, $method = 'GET', $data = null, $headers = array() ) {
		$base_url = 'http://rproxy/'; // TODO Fix this.
		$api_path = ltrim( $api_path, '/' );
		$url = $base_url . $api_path;

		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array_merge(
				array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				$headers
			),
		);

		if ( null !== $data ) {
			$args['body'] = wp_json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'httpCode' => 0,
				'response' => $response->get_error_message(),
			);
		}

		return array(
			'httpCode' => wp_remote_retrieve_response_code( $response ),
			'response' => json_decode( wp_remote_retrieve_body( $response ), true ),
		);
	}

}
