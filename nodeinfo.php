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
 */

/**
 * Initialize plugin
 */
function nodeinfo_init() {
	require_once __DIR__ . '/includes/class-nodeinfo-endpoint.php';
	require_once __DIR__ . '/includes/functions.php';

	// Configure the REST API route
	add_action( 'rest_api_init', array( 'Nodeinfo_Endpoint', 'register_routes' ) );

	// Add Webmention and Host-Meta discovery
	add_filter( 'webfinger_user_data', array( 'Nodeinfo_Endpoint', 'render_jrd' ), 10, 3 );
	add_filter( 'webfinger_post_data', array( 'Nodeinfo_Endpoint', 'render_jrd' ), 10, 3 );
	add_filter( 'host_meta', array( 'Nodeinfo_Endpoint', 'render_jrd' ) );
}
add_action( 'init', 'nodeinfo_init', 9 );

/**
 * Plugin Version Number.
 */
function nodeinfo_version() {
	$meta = nodeinfo_get_plugin_meta( array( 'Version' => 'Version' ) );

	return $meta['Version'];
}

/**
 * Add rewrite rules
 */
function nodeinfo_add_rewrite_rules() {
	add_rewrite_rule( '^.well-known/nodeinfo', 'index.php?rest_route=/nodeinfo/discovery', 'top' );
	add_rewrite_rule( '^.well-known/x-nodeinfo2', 'index.php?rest_route=/nodeinfo2/1.0', 'top' );
}
add_action( 'init', 'nodeinfo_add_rewrite_rules', 1 );

/**
 * `get_plugin_data` wrapper
 *
 * @return array the plugin metadata array
 */
function nodeinfo_get_plugin_meta( $default_headers = array() ) {
	if ( ! $default_headers ) {
		$default_headers = array(
			'Name'        => 'Plugin Name',
			'PluginURI'   => 'Plugin URI',
			'Version'     => 'Version',
			'Description' => 'Description',
			'Author'      => 'Author',
			'AuthorURI'   => 'Author URI',
			'TextDomain'  => 'Text Domain',
			'DomainPath'  => 'Domain Path',
			'Network'     => 'Network',
			'RequiresWP'  => 'Requires at least',
			'RequiresPHP' => 'Requires PHP',
			'UpdateURI'   => 'Update URI',
		);
	}

	return get_file_data( __FILE__, $default_headers, 'plugin' );
}

/**
 * Flush rewrite rules;
 */
function nodeinfo_flush_rewrite_rules() {
	nodeinfo_add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'nodeinfo_flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
