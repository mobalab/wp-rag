<?php
/**
 * WP RAG
 *
 * @package       WPRAG
 * @author        Kashima, Kazuo
 * @license       gplv3
 * @version       0.0.2
 *
 * @wordpress-plugin
 * Plugin Name:   WP RAG
 * Plugin URI:    https://github.com/mobalab/wp-rag
 * Description:   A WordPress plugin for building RAG
 * Version:       0.0.2
 * Author:        Kashima, Kazuo
 * Author URI:    https://github.com/k4200
 * Text Domain:   wp-rag
 * Domain Path:   /languages
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with WP RAG. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HELPER COMMENT START
 *
 * This file contains the main information about the plugin.
 * It is used to register all components necessary to run the plugin.
 *
 * The comment above contains all information about the plugin
 * that are used by WordPress to differenciate the plugin and register it properly.
 * It also contains further PHPDocs parameter for a better documentation
 *
 * The function WPRAG() is the main function that you will be able to
 * use throughout your plugin to extend the logic. Further information
 * about that is available within the sub classes.
 *
 * HELPER COMMENT END
 */

// Plugin name
define( 'WPRAG_NAME', 'WP RAG' );

// Plugin version
define( 'WPRAG_VERSION', '0.1.0' );

// Plugin Root File
define( 'WPRAG_PLUGIN_FILE', __FILE__ );

// Plugin base
define( 'WPRAG_PLUGIN_BASE', plugin_basename( WPRAG_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WPRAG_PLUGIN_DIR', plugin_dir_path( WPRAG_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WPRAG_PLUGIN_URL', plugin_dir_url( WPRAG_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once WPRAG_PLUGIN_DIR . 'core/class-wp-rag.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Kashima, Kazuo
 * @since   0.0.1
 * @return  object|Wp_Rag
 */
function WPRAG() {
	return Wp_Rag::instance();
}

WPRAG();
