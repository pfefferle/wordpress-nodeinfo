<?php
/**
 * Plugin Name: NodeInfo
 * Plugin URI: https://github.com/pfefferle/wordpress-nodeinfo/
 * Description: A better way to tell the world when your blog is updated.
 * Version: 1.0.0
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
	// Configure the REST API route
	require_once dirname( __FILE__ ) . '/includes/class-nodeinfo-endpoint.php';
	add_action( 'rest_api_init', array( 'Nodeinfo_Endpoint', 'register_routes' ) );
}
add_action( 'plugins_loaded', 'nodeinfo_init' );
