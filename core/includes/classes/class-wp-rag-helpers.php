<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_Helpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_Helpers
 * @author      Kashima, Kazuo
 * @since       0.0.1
 */
class Wp_Rag_Helpers {


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
	 */

	/**
	 * Output some text
	 *
	 * @param   string $text   The text to output
	 * @since   0.0.1
	 *
	 * @return  void
	 */
	public function output_text( $text = '' ) {
		echo $text;
	}

	/**
	 * HELPER COMMENT END
	 */


	/**
	 * Logs error.
	 *
	 * @param $message
	 * @param $context
	 * @return void
	 */
	public function log_error( $message, $context = array() ) {
		$formatted_message = sprintf(
			'[%s] [%s] %s',
			WPRAG_NAME,
			current_time( 'Y-m-d H:i:s' ),
			$message
		);

		if ( ! empty( $context ) ) {
			$formatted_message .= "\nContext: " . print_r( $context, true );
		}

		// @codingStandardsIgnoreLine
		error_log($formatted_message);
	}

	/**
	 * @return string e.g. "https://example.com/"
	 */
	public function get_api_url(): string {
		$env = getenv( 'WP_RAG_ENV' );

		switch ( $env ) {
			case 'local':
				return 'http://rproxy/';
			default:
				return Wp_Rag::DEFAULT_API_URL;
		}
	}

	public function call_api_for_site( $api_sub_path, $method = 'GET', $data = null, $headers = array() ) {
		$site_id      = WPRAG()->helpers->get_auth_data( 'site_id' );
		$free_api_key = WPRAG()->helpers->get_auth_data( 'free_api_key' );
		if ( empty( $site_id ) ) {
			wp_die( 'site_id is not set' );
		}
		$api_sub_path = ltrim( $api_sub_path, '/' );

		$api_path = "/api/sites/{$site_id}/{$api_sub_path}";
		$api_path = rtrim( $api_path, '/' );

		if ( ! empty( $free_api_key ) ) {
			$headers['X-Api-Key'] = $free_api_key;
		}

		return $this->call_api( $api_path, $method, $data, $headers );
	}

	/**
	 * Calls the WP RAG API
	 *
	 * @param $api_path e.g. /api/sites/1
	 * @param $method e.g. "POST", "PUT", etc.
	 * @param $data
	 * @param $headers
	 *
	 * @return array
	 */
	function call_api( $api_path, $method = 'GET', $data = null, $headers = array() ) {
		$base_url = $this->get_api_url();
		$api_path = ltrim( $api_path, '/' );
		$url      = $base_url . $api_path;

		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array_merge(
				array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				$headers
			),
			'timeout' => 15,
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

	/**
	 * Return whether the site is verified or not.
	 *
	 * Note that it only checks the DB, and doesn't check the API.
	 *
	 * @return bool True if verified, otherwise false
	 */
	public function is_verified() {
		return ! empty( $this->get_auth_data( 'verified_at' ) );
	}

	/**
	 * Registers the site on the API.
	 *
	 * @return bool
	 */
	public function register_site(): bool {
		$api_path = '/api/sites';
		$data     = array( 'url' => get_site_url() );
		$response = WPRAG()->helpers->call_api( $api_path, 'POST', $data );

		if ( 201 !== $response['httpCode'] ) {
			add_settings_error(
				'wp_rag_messages',
				'wp_rag_message',
				'API error: status=' . $response['httpCode'] . ', response=' . wp_json_encode( $response['response'] ),
				'error'
			);
			return false;
		} else {
			$auth_data                      = WPRAG()->helpers->get_auth_data();
			$auth_data['site_id']           = $response['response']['id'];
			$auth_data['free_api_key']      = $response['response']['free_api_key'];
			$auth_data['verification_code'] = $response['response']['verification_code'];
			WPRAG()->helpers->save_auth_data( $auth_data );

			// At this point, the site is registered, but not verified yet.
			return true;
		}
	}

	/**
	 * Asks the API to verify the site.
	 *
	 * Use this method when the site is registered, but not verified for some reason (e.g. network issue etc.).
	 *
	 * @param $site_id int ID of the site to verify
	 *
	 * @return bool
	 */
	public function start_site_verification( $site_id ): bool {
		$api_path = "/api/sites/$site_id/verify";
		$data     = array();
		$response = $this->call_api( $api_path, 'POST', $data );

		if ( 201 !== $response['httpCode'] ) {
			add_settings_error(
				'wp_rag_messages',
				'wp_rag_message',
				'API error: status=' . $response['httpCode'] . ', response=' . wp_json_encode( $response['response'] ),
				'error'
			);
			return false;
		} else {
			// Starting the verification process succeeded, which doesn't necessarily mean the site is verified.
			$auth_data                      = $this->get_auth_data();
			$auth_data['free_api_key']      = $response['response']['free_api_key'];
			$auth_data['verification_code'] = $response['response']['verification_code'];
			$this->save_auth_data( $auth_data );

			return true;
		}
	}

	/**
	 * Saves authentication data by serializing it and updating the specified option name.
	 *
	 * @param mixed $data The authentication data to be saved.
	 *
	 * @return void
	 */
	function save_auth_data( $data ) {
		$option_name     = Wp_Rag::OPTION_NAME_FOR_AUTH_DATA;
		$serialized_data = maybe_serialize( $data );
		update_option( $option_name, $serialized_data, 'no' );
	}

	/**
	 * Retrieves the authentication data, optionally filtered by a specific key.
	 *
	 * @param string|null $key Optional. The key to filter the authentication data. If not provided, the whole data set is returned.
	 *
	 * @return mixed The authentication data associated with the given key, or the entire data set if no key is provided.
	 */
	function get_auth_data( $key = null ) {
		$option_name     = Wp_Rag::OPTION_NAME_FOR_AUTH_DATA;
		$serialized_data = get_option( $option_name );
		if ( false === $serialized_data ) {
			return null;
		}
		$auth_data = maybe_unserialize( $serialized_data );
		if ( null === $key ) {
			return $auth_data;
		} else {
			return $auth_data[ $key ];
		}
	}

	/**
	 * Updates the authentication data with the provided key-value pair.
	 *
	 * @param string $key The key to update in the authentication data.
	 * @param mixed  $value The new value to associate with the specified key.
	 *
	 * @return void
	 */
	function update_auth_data( $key, $value ) {
		$data = $this->get_auth_data();
		if ( is_array( $data ) ) {
			$data[ $key ] = $value;
			$this->save_auth_data( $data );
		}
	}

	/**
	 * Deletes all the authentication data stored in wp_options table.
	 *
	 * @return void
	 */
	function delete_auth_data() {
		$option_name = Wp_Rag::OPTION_NAME_FOR_AUTH_DATA;
		delete_option( $option_name );
	}

	/**
	 * Deletes a specific key from the authentication data stored in wp_options table.
	 *
	 * @param string $key The key to be deleted from the authentication data.
	 *
	 * @return void
	 */
	function delete_key_from_auth_data( $key ) {
		$data = $this->get_auth_data();
		if ( is_array( $data ) ) {
			unset( $data[ $key ] );
			$this->save_auth_data( $data );
		}
	}
}
