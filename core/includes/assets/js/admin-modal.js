jQuery(
	function ($) {
		$( '.wp-rag-show-details' ).on(
			'click',
			function (e) {
				e.preventDefault();
				const details = $( this ).data( 'details' );
				$( '#wp-rag-modal pre' ).text( JSON.stringify( details, null, 2 ) );
				$( '#wp-rag-modal' ).show();
			}
		);

		$( '.wp-rag-modal-close' ).on(
			'click',
			function () {
				$( '#wp-rag-modal' ).hide();
			}
		);

		// Close the model when outside is clicked.
		$( document ).on(
			'click',
			function (e) {
				if ($( e.target ).hasClass( 'wp-rag-modal' )) {
					$( '#wp-rag-modal' ).hide();
				}
			}
		);
	}
);