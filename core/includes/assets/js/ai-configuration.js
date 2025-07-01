(function ($) {
	'use strict';

	$( document ).ready(
		function () {
			var $embeddingModelSelect = $( 'select[name="wp_rag_ai_options[embedding_model]"]' );
			var originalValue         = $embeddingModelSelect.val();

			// Get the warning message element that already exists in the HTML.
			var $warningMessage = $( '#embedding-model-change-warning' );

			// Only set up the change handler if the warning element exists.
			if ($warningMessage.length > 0) {
				// Show/hide warning on model change.
				$embeddingModelSelect.on(
					'change',
					function () {
						if ($( this ).val() !== originalValue) {
							$warningMessage.slideDown();
						} else {
							$warningMessage.slideUp();
						}
					}
				);
			}

			// Since WordPress admin forms don't typically use AJAX,
			// we'll reset the original value on page load.
			// The warning will automatically be hidden via CSS on page load.
		}
	);

})( jQuery );
