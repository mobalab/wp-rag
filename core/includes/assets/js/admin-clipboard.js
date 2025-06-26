/**
 * Copies the text of the given element.
 *
 * @param {string} elementId
 * @param {*} button
 */
async function copyToClipboard(elementId, button) {
	try {
		const element = document.getElementById( elementId );
		const text    = element.textContent;

		await navigator.clipboard.writeText( text );

		// Change the button to the "copied" status.
		const originalText = button.innerHTML;
		button.innerHTML   = '📋 Copied';
		button.classList.add( 'copied' );

		// Put it back to the original after 2 seconds.
		setTimeout(
			() => {
				button.innerHTML = originalText;
				button.classList.remove( 'copied' );
			},
			2000
		);

	} catch (err) {
		console.error( 'Failed to copy the value to the clipboard:', err );
		alert( 'Failed to copy the value to the clipboard. Please copy it manually.' );
	}
}