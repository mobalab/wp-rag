<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_PostHooks
 *
 * This class contains hooks called when a post/page is saved.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_PostHooks
 * @author      Kashima, Kazuo
 * @since       0.0.3
 */
class Wp_Rag_PostHooks {
	/**
	 * @var array wp_post_id => status string
	 */
	private $previous_status = array();

	/**
	 * Calls the endpoint for POSTing / adding the post.
	 *
	 * @param array $post_data
	 * @return void
	 */
	private function call_post_api( array $post_data ) {
		$response = WPRAG()->helpers->call_api_for_site( '/posts', 'POST', $post_data );

		if ( 201 !== $response['httpCode'] ) {
			error_log( json_encode( $response ) );
		}
	}

	/**
	 * Calls the endpoint for PUTting / updating the post.
	 *
	 * @param array $post_data
	 * @return void
	 */
	private function call_put_api( array $post_data ) {
		$response = WPRAG()->helpers->call_api_for_site( '/posts', 'PUT', $post_data );

		if ( 201 !== $response['httpCode'] ) {
			error_log( json_encode( $response ) );
		}
	}

	/**
	 * Calls the endpoint for DELETing the post.
	 *
	 * @param int $wp_post_id
	 * @return void
	 */
	private function call_delete_api( int $wp_post_id ) {
		$data     = array( 'wp_post_id' => $wp_post_id );
		$response = WPRAG()->helpers->call_api_for_site( '/posts', 'DELETE', $data );

		if ( 200 !== $response['httpCode'] ) {
			error_log( json_encode( $response ) );
		}
	}


	/**
	 * Stores the previous status of the post/page before saving it.
	 *
	 * @param $post_id
	 * @param $data
	 * @return void
	 */
	public function store_previous_status( $post_id, $data ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( $post && in_array( $post->post_type, array( 'post', 'page' ) ) ) {
			$this->previous_status[ $post_id ] = $post->post_status;
		}
	}

	/**
	 * Called when a post/page is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param bool    $update Whether updating or not (creating).
	 */
	public function handle_post_save( int $post_id, WP_Post $post, bool $update ) {
		// Do not process revisions.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Do not process autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Do not process posts of other types than "post" and "page".
		if ( ! in_array( $post->post_type, array( 'post', 'page' ) ) ) {
			return;
		}

		$new_status = $post->post_status;
		$old_status = isset( $this->previous_status[ $post_id ] )
			? $this->previous_status[ $post_id ]
			: 'new';  // previous_status doesn't exist when creating a new post.

		if ( ( 'draft' === $old_status || 'auto-draft' === $old_status ) && 'publish' === $new_status ) {
			// Draft to publish -> new post.
			$this->handle_post_create( $post );
		} elseif ( 'publish' === $old_status && 'publish' === $new_status ) {
			// Publish to publish -> updating an existing post.
			$this->handle_post_update( $post );
		} elseif ( 'publish' === $old_status && 'draft' === $new_status ) {
			// Published to draft -> deleting the post.
			$this->handle_post_remove( $post_id );
		}

		// Remove the status once the process is complete.
		unset( $this->previous_status[ $post_id ] );
	}

	/**
	 * Prepares the data for the API endpoints that handle posts.
	 *
	 * @param WP_Post $post Post data to send to the API.
	 * @return array Data for the API endpoints.
	 */
	private function prepare_post_data( WP_Post $post ) {
		return array(
			'wp_post_id'       => $post->ID,
			'title'            => $post->post_title,
			'content'          => $post->post_content,
			'post_type'        => $post->post_type,
			'posted_at'        => $post->post_date,
			'post_modified_at' => $post->post_modified,
			'url'              => get_permalink( $post ),
		);
	}

	/**
	 * Called when a post is deleted.
	 *
	 * @param int $post_id Post ID.
	 */
	public function handle_post_delete( int $post_id ) {
		// Do not process revisions.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post || ! in_array( $post->post_type, array( 'post', 'page' ) ) ) {
			return;
		}

		if ( 'publish' === $post->post_status ) {
			$this->handle_post_remove( $post_id );
		}
	}

	private function handle_post_create( WP_Post $post ) {
		$post_data = $this->prepare_post_data( $post );

		try {
			$this->call_post_api( $post_data );
		} catch ( Exception $e ) {
			WPRAG()->helpers->log_error( 'API Error (Create): ' . $e->getMessage() );
		}
	}

	private function handle_post_update( WP_Post $post ) {
		$post_data = $this->prepare_post_data( $post );

		try {
			$this->call_put_api( $post_data );
		} catch ( Exception $e ) {
			WPRAG()->helpers->log_error( 'API Error (Update): ' . $e->getMessage() );
		}
	}

	private function handle_post_remove( int $post_id ) {
		try {
			$this->call_delete_api( $post_id );
		} catch ( Exception $e ) {
			WPRAG()->helpers->log_error( 'Error (Remove): ' . $e->getMessage() );
		}
	}
}
