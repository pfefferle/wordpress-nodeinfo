<?php
/**
 * Plugin Name: NodeInfo
 * Plugin URI: https://github.com/pfefferle/wordpress-nodeinfo/
 * Description: NodeInfo is an effort to create a standardized way of exposing metadata about a server running one of the distributed social networks.
 * Version: 3.1.0
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: nodeinfo
 *
 * @package Nodeinfo
 */

defined( 'ABSPATH' ) || exit;

define( 'NODEINFO_PLUGIN_FILE', __FILE__ );
define( 'NODEINFO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Require the autoloader.
require_once NODEINFO_PLUGIN_DIR . 'includes/class-autoloader.php';

// Register the autoloader.
Nodeinfo\Autoloader::register_path( 'Nodeinfo', NODEINFO_PLUGIN_DIR . 'includes' );

// Require global functions.
require_once NODEINFO_PLUGIN_DIR . 'includes/functions.php';

// Require deprecated classes for backwards compatibility.
require_once NODEINFO_PLUGIN_DIR . 'includes/class-nodeinfo-endpoint.php';

/**
 * Plugin initialization function.
 *
 * @return Nodeinfo\Nodeinfo The plugin instance.
 */
function nodeinfo_plugin() {
	return Nodeinfo\Nodeinfo::get_instance();
}

// Initialize the plugin after all plugins are loaded.
add_action(
	'plugins_loaded',
	function () {
		nodeinfo_plugin()->init();
	}
);

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, array( Nodeinfo\Nodeinfo::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Nodeinfo\Nodeinfo::class, 'deactivate' ) );
