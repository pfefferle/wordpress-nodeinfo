<?php
function nodeinfo_get_active_users( $duration = '1 month ago' ) {
	// get all distinct authors that have published a post in the last 30 days
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

	// get all distinct ID from $posts
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
 * Get the masked WordPress version to only show the major and minor version.
 *
 * @return string The masked version.
 */
function nodeinfo_get_masked_version() {
	// only show the major and minor version
	$version = get_bloginfo( 'version' );
	// strip the RC or beta part
	$version = preg_replace( '/-.*$/', '', $version );
	$version = explode( '.', $version );
	$version = array_slice( $version, 0, 2 );

	return implode( '.', $version );
}
