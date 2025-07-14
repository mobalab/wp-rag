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

	function showUserMessage(messages, userName, message) {
		const container = $( '<div class="wp-rag-message wp-rag-message--user"></div>' );
		container.append( $(' <div class="wp-rag-message__author">').text( userName ) )
		container.append( $( '<div class="wp-rag-message__text--user">').text( message ) );
		messages.append( container );
	}

	function showBotMessage(messages, botName, message, contextPosts = null) {
		const container = $( '<div class="wp-rag-message wp-rag-message--bot"></div>' );
		container.append( $(' <div class="wp-rag-message__author--bot">').text( botName ) )
		container.append( $( '<div class="wp-rag-message__text--bot">').text( message ) );
		if (contextPosts !== null) {
			showContextLinks(container, contextPosts)
		}
		messages.append( container );
	}

	function showContextLinks(container, contextPosts) {
		if (contextPosts.length === 0) {
			return;
		}

		const relatedInfoDiv = $( '<div class="wp-rag-related"></div>' );

		const titleDiv = $( '<div class="wp-rag-related__title"></div>' );
		titleDiv.append( '<span class="wp-rag-related__icon">📖</span>' );
		titleDiv.append( '<span class="wp-rag-related__text">Related info</span>' );
		relatedInfoDiv.append( titleDiv );

		const linksDiv = $( '<div class="wp-rag-related__links"></div>' );
		contextPosts.forEach(
			post => {
				const a = $( `<a href="${post.url}" target="_blank" class="wp-rag-related__link"></a>` );
				a.append( '<span class="wp-rag-related__link-icon">🔗</span>' );
				a.append( $( '<span class="wp-rag-related__link-text"></span>' ).text( post.title ) );
				linksDiv.append(a);
			}
		)
		relatedInfoDiv.append( linksDiv );
		container.append( relatedInfoDiv );
	}

	$( document ).ready(
		function () {
			const chatWindow     = $( '#wp-rag-chat-window' );
			const chatIcon       = $( '#wp-rag-chat-icon' );
			const form           = $( '#wp-rag-chat-form' );
			const input          = $( '#wp-rag-chat-input' );
			const submitButton   = form.find( '.wp-rag-chat__submit' );
			const messages       = $( '#wp-rag-chat-messages' );
			const minimizeButton = $( '.wp-rag-chat__minimize' );

			const userName       = wpRag.chat_ui_options['user_name'] || 'You';
			const botName        = wpRag.chat_ui_options['bot_name'] || 'Bot';
			const initialMessage = wpRag.chat_ui_options['initial_message'];

			if ( initialMessage ) {
				showBotMessage(messages, botName, initialMessage);
			}

			const isMinimized = localStorage.getItem( 'wp-rag-chat-minimized' ) === 'true';
			if (isMinimized) {
				chatWindow.addClass( 'wp-rag--hidden' );
				chatIcon.removeClass( 'wp-rag--hidden' );
			}
			minimizeButton.on(
				'click',
				function () {
					chatWindow.addClass( 'wp-rag--hidden' );
					chatIcon.removeClass( 'wp-rag--hidden' );
					localStorage.setItem( 'wp-rag-chat-minimized', 'true' );
				}
			);
			chatIcon.on(
				'click',
				function () {
					chatWindow.removeClass( 'wp-rag--hidden' );
					chatIcon.addClass( 'wp-rag--hidden' );
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

					submitButton.prop( 'disabled', true ).addClass( 'wp-rag-chat__submit--loading' );

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
									showUserMessage(messages, userName, message);
									if ('yes' === wpRag.chat_ui_options['display_context_links']) {
										showBotMessage(messages, botName, response.data.answer, response.data.context_posts);
									} else {
										showBotMessage(messages, botName, response.data.answer);
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
								submitButton.prop( 'disabled', false ).removeClass( 'wp-rag-chat__submit--loading' );
							}
						}
					);
				}
			);
		}
	);
})( jQuery );
