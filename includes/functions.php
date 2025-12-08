<?php
/**
 * Helper functions for NodeInfo.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo;

/**
 * Gets the count of active users within a duration.
 *
 * @param string $duration The duration to check (e.g., '1 month ago').
 * @return int The number of active users.
 */
function get_active_users( $duration = '1 month ago' ) {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'orderby'        => 'post_count',
			'order'          => 'DESC',
			'posts_per_page' => 4,
			'date_query'     => array(
				array(
					'after' => $duration,
				),
			),
		)
	);

	if ( ! $posts ) {
		return 0;
	}

	return count(
		array_unique(
			wp_list_pluck(
				$posts,
				'post_author'
			)
		)
	);
}

/**
 * Gets the masked WordPress version (major.minor only).
 *
 * @return string The masked version.
 */
function get_masked_version() {
	$version = get_bloginfo( 'version' );
	// Strip RC/beta suffixes.
	$version = preg_replace( '/-.*$/', '', $version );
	$version = explode( '.', $version );
	$version = array_slice( $version, 0, 2 );

	return implode( '.', $version );
}

/**
 * Gets the plugin version.
 *
 * @return string The plugin version.
 */
function get_plugin_version() {
	$meta = get_plugin_meta( array( 'Version' => 'Version' ) );

	return $meta['Version'];
}

/**
 * Gets plugin metadata.
 *
 * @param array $default_headers Optional headers to retrieve.
 * @return array The plugin metadata.
 */
function get_plugin_meta( $default_headers = array() ) {
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

	return \get_file_data( NODEINFO_PLUGIN_FILE, $default_headers, 'plugin' );
}
