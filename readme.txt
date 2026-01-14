=== WP RAG ===
Author URI: https://github.com/mobalab
Plugin URI: https://github.com/mobalab/wp-rag
Donate link: 
Contributors: 
Tags: rag, ai
Requires at least: 6.6.0
Tested up to: 6.6.2
Requires PHP: 
Stable tag: 0.8.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A WordPress plugin for building RAG

== Description ==

This plugin allows you to build a RAG (Retrieval-Augmented Generation) system using your WordPress posts and pages.

Once enabled, our external server retrieves your posts and pages via the WordPress API,
calculates embeddings, and stores them in its vector database.

The plugin can display a chat dialog on your site. When a user (whether a guest or
a WordPress user) enters a query, it is sent to our external server. The server then
calculates embeddings, searches for relevant documents in the database, sends both
the user query and the retrieved documents to a generative AI, and returns the
generated response to the plugin.

Currently, the plugin only supports OpenAI's Embedding API and Chat API, so you'll
need an OpenAI API key to use this plugin.

== Frequently Asked Questions ==

= Is the plugin free? =

Yes, it's currently free, but we plan to transition to a freemium model in the future.

= Can I use the plugin for password-protected WordPress site? =

Not at the moment, but authentication support will be implemented very soon.

= Can I use the plugin for a WordPress site in a private network? =

Yes, private network support is now available for Premium users as of version 0.5.0.

== Installation ==

Please refer to our Installation and Setup Guide:
https://github.com/mobalab/wp-rag/wiki/Installation-and-Setup-Guide

== Changelog ==

= 0.7.0: November 3rd, 2025 =
* Add support for GPT-5
* Fix PHP warnings

= 0.6.0: September 10th, 2025 =
* Disable some features for free users

= 0.5.0: July 29, 2025 =
* Add premium API key functionality
* Add support for private networks (Premium users only)
* Fix empty value handling in configuration fields
* Fix deprecation warnings

= 0.4.1: July 22, 2025 =
* Fix bugs related to the notice for the Terms and Privacy Policy
* Update README

= 0.4.0: July 16, 2025 =
* Require users to agree to the Terms and Privacy Policy
* Polish the UI of the chat window (The DOM structure has been changed)
* Minor UI change on an admin page

= 0.3.0: July 12, 2025 =
* Enable to configure embedding / generation models
* Extend timeout for API calls from 15 to 30 seconds

= 0.2.0: July 1, 2025 =
* Show the site ID and API key on the main page
* Fix a minor CSS issue

= 0.1.0: December 8, 2024 =
* Improve messages
* Fix a bug

= 0.0.4: December 2, 2024 =
* More customizable UI
* Make some AI settings customizable

= 0.0.3: November 17, 2024 =
* Save/delete it on the API when saving/deleting a Post

= 0.0.2: November 6, 2024 =
* Enable to specify "from" date for importing posts and generating embeddings
* Customizable UI
* A bit more user-friendly admin pages

= 0.0.1: October 8, 2024 =
* Birthday of WP RAG