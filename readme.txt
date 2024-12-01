=== WP RAG ===
Author URI: https://github.com/k4200
Plugin URI: https://github.com/mobalab/wp-rag
Donate link: 
Contributors: 
Tags: rag, ai
Requires at least: 6.6.0
Tested up to: 6.6.2
Requires PHP: 
Stable tag: trunk
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

Not currently, but support for private networks will be added in the near future.

== Installation ==

Please refer to our Installation and Setup Guide:
https://github.com/mobalab/wp-rag/wiki/Installation-and-Setup-Guide

== Changelog ==

= 0.0.1: October 8, 2024 =
* Birthday of WP RAG

= 0.0.2: November 6, 2024 =
* Enable to specify "from" date for importing posts and generating embeddings
* Customizable UI
* A bit more user-friendly admin pages

= 0.0.3: November 17, 2024 =
* Save/delete it on the API when saving/deleting a Post

= 0.0.4: December 2, 2024 =
* More customizable UI
* Make some AI settings customizable