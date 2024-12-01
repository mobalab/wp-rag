/*-------------------------
Frontend related javascript
-------------------------*/

/**
 * HELPER COMMENT START
 *
 * This file contains all of the frontend related javascript.
 * With frontend, it is meant the WordPress site that is visible for every visitor.
 *
 * Since you added the jQuery dependency within the "Add JS support" module, you see down below
 * the helper comment a function that allows you to use jQuery with the commonly known notation: $('')
 * By default, this notation is deactivated since WordPress uses the noConflict mode of jQuery
 * You can also use jQuery outside using the following notation: jQuery('')
 *
 * Here's some jQuery example code you can use to fire code once the page is loaded: $(document).ready( function(){} );
 *
 * Using the ajax example, you can send data back and forth between your frontend and the
 * backend of the website (PHP to ajax and vice-versa).
 * As seen in the example below, we use the jQuery $.ajax function to send data to the WordPress
 * callback my_demo_ajax_call, which was added within the Wp_Rag_Run class.
 * From there, we process the data and send it back to the code below, which will then display the
 * example within the console of your browser.
 *
 * You can add the localized variables in here as followed: wprag.plugin_name
 * These variables are defined within the localization function in the following file:
 * core/includes/classes/class-wp-rag-run.php
 *
 * HELPER COMMENT END
 */

(function ( $ ) {

	"use strict";

	$( document ).ready(
		function () {
			const chatWindow     = $( '#wp-rag-chat-window' );
			const chatIcon       = $( '#wp-rag-chat-icon' );
			const form           = $( '#wp-rag-chat-form' );
			const input          = $( '#wp-rag-chat-input' );
			const submitButton   = form.find( '.wp-rag-chat-submit' );
			const messages       = $( '#wp-rag-chat-messages' );
			const minimizeButton = $( '.wp-rag-chat-minimize' );

			const yourName       = wpRag.chat_ui_options['your_name'] || 'You';
			const botName        = wpRag.chat_ui_options['bot_name'] || 'Bot';
			const initialMessage = wpRag.chat_ui_options['initial_message'];

			if ( initialMessage ) {
				const paragraph = $( '<p>' );
				paragraph.append( $( '<strong>' ).text( botName + ':' ) );
				paragraph.append( ' ' ).append( $( '<span>' ).text( initialMessage ) );
				messages.append( paragraph );
			}

			const isMinimized = localStorage.getItem( 'wp-rag-chat-minimized' ) === 'true';
			if (isMinimized) {
				chatWindow.addClass( 'hidden' );
				chatIcon.removeClass( 'hidden' );
			}
			minimizeButton.on(
				'click',
				function () {
					chatWindow.addClass( 'hidden' );
					chatIcon.removeClass( 'hidden' );
					localStorage.setItem( 'wp-rag-chat-minimized', 'true' );
				}
			);
			chatIcon.on(
				'click',
				function () {
					chatWindow.removeClass( 'hidden' );
					chatIcon.addClass( 'hidden' );
					localStorage.setItem( 'wp-rag-chat-minimized', 'false' );
					input.focus();
				}
			);

			form.on(
				'submit',
				function (e) {
					e.preventDefault();
					const message = $( '#wp-rag-chat-input' ).val();

					if (message.trim() === '') {
						return;
					}

					submitButton.prop( 'disabled', true ).addClass( 'loading' );

					$.ajax(
						{
							url: wpRag.ajaxurl,
							type: 'POST',
							data: {
								action: 'wp_rag_process_chat',
								message: message
							},
							success: function (response) {
								if (response.success) {
									messages.append( '<p><strong>' + yourName + ':</strong> ' + message + '</p>' );
									messages.append( '<p><strong>' + botName + ':</strong> ' + response.data.answer + '</p>' );
									if ('yes' === wpRag.chat_ui_options['display_context_links']) {
										if (response.data.context_posts.length > 0) {
											messages.append( '<p>Related info:</p>' );
											const ul = $( '<ul></ul>' );
											response.data.context_posts.forEach(
												post => {
													const li = $( `<li><a href="${post.url}" target="_blank">${post.title}</a></li>` );
													ul.append( li );
												}
											)
											messages.append( ul );
										}
									}
								} else {
									messages.append( '<p><strong>Error:</strong> ' + response.data + '</p>' );
								}
							},
							error: function (jqXHR) {
								var errorMessage = 'Unable to process your request.';
								if ( jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
									errorMessage = jqXHR.responseJSON.data.message;
								}

								messages.append( '<p><strong>Error:</strong> ' + errorMessage + '</p>' );
							},
							complete: function () {
								input.val( '' ).focus();
								submitButton.prop( 'disabled', false ).removeClass( 'loading' );
							}
						}
					);
				}
			);
		}
	);
})( jQuery );
