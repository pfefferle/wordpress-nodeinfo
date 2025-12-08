<?php
/**
 * Plugin Name: NodeInfo
 * Plugin URI: https://github.com/pfefferle/wordpress-nodeinfo/
 * Description: NodeInfo is an effort to create a standardized way of exposing metadata about a server running one of the distributed social networks.
 * Version: 2.3.1
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: nodeinfo
 * Domain Path: /languages
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

/**
 * Initialize the plugin.
 */
function nodeinfo_init() {
	// Initialize NodeInfo version integrations.
	Nodeinfo\Integration\Nodeinfo10::init();
	Nodeinfo\Integration\Nodeinfo11::init();
	Nodeinfo\Integration\Nodeinfo20::init();
	Nodeinfo\Integration\Nodeinfo21::init();
	Nodeinfo\Integration\Nodeinfo22::init();

	// Register REST routes.
	add_action( 'rest_api_init', 'nodeinfo_register_routes' );

	// Add WebFinger and Host-Meta discovery.
	add_filter( 'webfinger_user_data', array( Nodeinfo\Controller\Nodeinfo::class, 'jrd' ), 10, 3 );
	add_filter( 'webfinger_post_data', array( Nodeinfo\Controller\Nodeinfo::class, 'jrd' ), 10, 3 );
	add_filter( 'host_meta', array( Nodeinfo\Controller\Nodeinfo::class, 'jrd' ) );
}
add_action( 'init', 'nodeinfo_init', 9 );

/**
 * Register REST API routes.
 */
function nodeinfo_register_routes() {
	$nodeinfo_controller = new Nodeinfo\Controller\Nodeinfo();
	$nodeinfo_controller->register_routes();

	$nodeinfo2_controller = new Nodeinfo\Controller\Nodeinfo2();
	$nodeinfo2_controller->register_routes();
}

/**
 * Add rewrite rules for well-known endpoints.
 */
function nodeinfo_add_rewrite_rules() {
	add_rewrite_rule( '^.well-known/nodeinfo', 'index.php?rest_route=/nodeinfo/discovery', 'top' );
	add_rewrite_rule( '^.well-known/x-nodeinfo2', 'index.php?rest_route=/nodeinfo2/1.0', 'top' );
}
add_action( 'init', 'nodeinfo_add_rewrite_rules', 1 );

/**
 * Flush rewrite rules on activation.
 */
function nodeinfo_activate() {
	nodeinfo_add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'nodeinfo_activate' );

/**
 * Flush rewrite rules on deactivation.
 */
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
