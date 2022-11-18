<?php
/**
 * Plugin Name: NodeInfo
 * Plugin URI: https://github.com/pfefferle/wordpress-nodeinfo/
 * Description: NodeInfo is an effort to create a standardized way of exposing metadata about a server running one of the distributed social networks.
 * Version: 1.0.7
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: nodeinfo
 * Domain Path: /languages
 */

/**
 * Initialize plugin
 */
function nodeinfo_init() {
	require_once dirname( __FILE__ ) . '/includes/class-nodeinfo-endpoint.php';

	// Configure the REST API route
	add_action( 'rest_api_init', array( 'Nodeinfo_Endpoint', 'register_routes' ) );

	// Add Webmention and Host-Meta discovery
	add_filter( 'webfinger_user_data', array( 'Nodeinfo_Endpoint', 'render_jrd' ), 10, 3 );
	add_filter( 'webfinger_post_data', array( 'Nodeinfo_Endpoint', 'render_jrd' ), 10, 3 );
	add_filter( 'host_meta', array( 'Nodeinfo_Endpoint', 'render_jrd' ) );
}
add_action( 'plugins_loaded', 'nodeinfo_init' );

/**
 * Add rewrite rules
 */
function nodeinfo_add_rewrite_rules() {
	add_rewrite_rule( '^.well-known/nodeinfo', 'index.php?rest_route=/nodeinfo/discovery', 'top' );
	add_rewrite_rule( '^.well-known/x-nodeinfo2', 'index.php?rest_route=/nodeinfo2/1.0', 'top' );
}
add_action( 'init', 'nodeinfo_add_rewrite_rules', 1 );

/**
 * Flush rewrite rules;
 */
function nodeinfo_flush_rewrite_rules() {
	nodeinfo_add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'nodeinfo_flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
