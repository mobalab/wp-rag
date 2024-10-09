=== WP RAG ===
Author URI: https://github.com/k4200
Plugin URI: https://github.com/k4200/wp-rag
Donate link: 
Contributors: 
Tags: rag, ai
Requires at least: 6.6.0
Tested up to: 6.6.2
Requires PHP: 
Stable tag: 0.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A WordPress plugin for building RAG

== Description ==

This plugin enables to build a RAG system based on the WordPress posts and pages.

Once it's enabled, an external server that its author owns retrieves posts and pages
using the WorePress API, and the server calculates embeddings and stores them to
the vector database on it.

It can also show a chat dialog on the site.
When a user (regardless of whether he is a guest or a WordPress user) enters a text,
it sends it the external server. Then, the server calculates embeddings, searches for
similar documents in the database, sends the user-entered text and the similar docs to
generative AI, and finally returns the answer to the plugin.

Currently, only OpenAI Embeddings API and OpenAI API are supported, so you need an
API key to get this plugin working.

== Frequently Asked Questions ==

= Is the plugin free? =

Yes, it's free for now, but we're thinking of switching to a freemium model.

= Can I use the plugin for password-protected WordPress site? =

No, at this moment. Authentication will be implemented very soon.

= Can I use the plugin for a WordPress site in a private network? =

No, at this moment. It will be handled in the near future.

== Installation ==

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add new`
3. Search for `WP RAG` and click 'Install Now' or click on the `upload` link to upload `wp-rag.zip`
4. Click on `Activate plugin`

== Changelog ==

= 0.0.1: October 8, 2024 =
* Birthday of WP RAG