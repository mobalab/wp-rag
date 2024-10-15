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

(function( $ ) {

	"use strict";

    $(document).ready( function() {
        $('#wp-rag-chat-form').on('submit', function (e) {
            e.preventDefault();
            var message = $('#wp-rag-chat-input').val();

            if (message.trim() === '') return;

            $.ajax({
                url: wpRag.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_rag_process_chat',
                    message: message
                },
                success: function (response) {
                    if (response.success) {
                        $('#wp-rag-chat-messages').append('<p><strong>You:</strong> ' + message + '</p>');
                        $('#wp-rag-chat-messages').append('<p><strong>Bot:</strong> ' + response.data.answer + '</p>');
                    } else {
                        $('#wp-rag-chat-messages').append('<p><strong>Error:</strong> ' + response.data + '</p>');
                    }
                    $('#wp-rag-chat-input').val('');
                },
                error: function () {
                    $('#wp-rag-chat-messages').append('<p><strong>Error:</strong> Unable to process your request.</p>');
                }
            });
        });
    });
})( jQuery );
