jQuery( document ).ready(
	function ($) {
		$( '#wp-rag-accept-terms-pp' ).on(
			'click',
			function () {
				const $button = $( this );
				const $notice = $( '#wp-rag-terms-pp-notice' );

				$button.prop( 'disabled', true ).text( 'Processing...' );

				$.post(
					wpRag.ajax_url,
					{
						action: 'wp_rag_accept_terms_pp',
						nonce: wpRag.nonce
					}
				).done(
					function (response) {
						if (response.success) {
							$notice.fadeOut();
						} else {
							alert( wpRag.strings.error );
							$button.prop( 'disabled', false ).text( 'Accept Terms and Privacy Policy' );
						}
					}
				).fail(
					function () {
						alert( wpRag.strings.error );
						$button.prop( 'disabled', false ).text( 'Accept Terms and Privacy Policy' );
					}
				);
			}
		);
	}
);