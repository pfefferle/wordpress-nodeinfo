<?php

class Nodeinfo_Endpoint {
	/**
	 * Register the Route.
	 */
	public static function register_routes() {
		register_rest_route(
			'nodeinfo', '/discovery', array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( 'Nodeinfo_Endpoint', 'discovery' ),
				)
			)
		);

		register_rest_route(
			'nodeinfo', '/1.0', array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( 'Nodeinfo_Endpoint', 'info_1x' ),
					'args' => array('version' => '1.0'),
				)
			)
		);

		register_rest_route(
			'nodeinfo', '/1.1', array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( 'Nodeinfo_Endpoint', 'info_1x' ),
					'args' => array('version' => '1.1'),
				),
			)
		);

		register_rest_route(
			'nodeinfo', '/2.0', array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( 'Nodeinfo_Endpoint', 'info_20' ),
				),
			)
		);
	}

	public static function discovery( WP_REST_Request $request ) {
		$discovery = array();
		$discovery['links'] = array(
			array(
				'rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.0',
				'href' => get_rest_url( null, '/nodeinfo/2.0' )
			),
			array(
				'rel' => 'http://nodeinfo.diaspora.software/ns/schema/1.1',
				'href' => get_rest_url( null, '/nodeinfo/1.1' )
			),
			array(
				'rel' => 'http://nodeinfo.diaspora.software/ns/schema/1.0',
				'href' => get_rest_url( null, '/nodeinfo/1.0' )
			),
		);

		$discovery = apply_filters( 'wellknown_nodeinfo_data', $discovery );

		// Create the response object
		$response = new WP_REST_Response( $discovery );
		$response->header( 'Content-Type', 'application/json; profile=http://nodeinfo.diaspora.software' );

		return $response;
	}

	public static function info_1x( WP_REST_Request $request ) {
		$attributes = $request->get_attributes();

		$nodeinfo = self::get_default_object();

		$nodeinfo['version'] = $attributes['args']['version'];
		$nodeinfo['protocols'] = array(
			'inbound' => array(),
			'outbound' => array(
				'smtp'
			),
		);
		$nodeinfo['services'] = array(
			'inbound' => array(),
			'outbound' => array(
				'wordpress',
				'smtp'
			),
		);

		$nodeinfo = apply_filters( 'nodeinfo_1x_data', $nodeinfo );

		// Create the response object
		$response = new WP_REST_Response( $nodeinfo );

		return $response;
	}

	public static function info_20( WP_REST_Request $request ) {
		$nodeinfo = self::get_default_object();

		$nodeinfo['version'] = '2.0';
		$nodeinfo['protocols'] = array(

		);
		$nodeinfo['services'] = array(
			'inbound' => array(
				'atom1.0',
				'rss2.0',
				'wordpress',
				'pop3',
			),
			'outbound' => array(
				'atom1.0',
				'rss2.0',
				'smtp',
			),
		);

		$nodeinfo = apply_filters( 'nodeinfo_20_data', $nodeinfo );

		// Create the response object
		$response = new WP_REST_Response( $nodeinfo );

		return $response;
	}

	public static function get_default_object() {
		$nodeinfo['version'] = '0';

		$nodeinfo['software'] = array(
			'name' => 'wordpress',
			'version' => get_bloginfo( 'version' ),
		);

		$users = count_users();
		$posts = wp_count_posts();
		$comments = wp_count_comments();

		$nodeinfo['usage'] = array(
			'users' => array(
				'total' => (int) $users['total_users'],
			),
			'localPosts' => (int) $posts->publish,
			'localComments' => (int) $comments->approved,
		);

		$nodeinfo['openRegistrations'] = (boolean) get_option( 'users_can_register', false );

		return $nodeinfo;
	}
}
