<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wp_Rag_FormHelpers
 *
 * This class contains repetitive functions for forms that
 * are used globally within the plugin.
 *
 * @package     WPRAG
 * @subpackage  Classes/Wp_Rag_FormHelpers
 * @author      Kashima, Kazuo
 * @since       0.0.2
 */
class Wp_Rag_FormHelpers {

	/**
	 * Returns 'disabled' if forms should be disabled, otherwise ''.
	 *
	 * @param string $output The method echos the returned value if 'yes'.
	 * @return string
	 */
	public function maybe_disabled( string $output = 'yes' ): string {
		$disabled = ! WPRAG()->helpers->is_verified() ? 'disabled' : '';

		if ( 'yes' === $output ) {
			echo esc_attr( $disabled );
		}

		return $disabled;
	}
}
