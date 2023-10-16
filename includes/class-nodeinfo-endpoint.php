<?php
/**
 * Class handles the NodeInfo API rest endpoints
 */
class Nodeinfo_Endpoint {

	/**
	 * Register the Routes.
	 */
	public static function register_routes() {
		register_rest_route(
			'nodeinfo',
			'/discovery',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'Nodeinfo_Endpoint', 'render_discovery' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			'nodeinfo',
			'/(?P<version>[\.\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'Nodeinfo_Endpoint', 'render_nodeinfo' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'version' => array(
							'required'    => true,
							'type'        => 'string',
							'description' => __( 'The version of the NodeInfo scheme', 'nodeinfo' ),
							'enum'        => array(
								'1.0',
								'1.1',
								'2.0',
								'2.1',
							),
						),
					),
				),
			)
		);

		register_rest_route(
			'nodeinfo2',
			'/(?P<version>[\.\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'Nodeinfo_Endpoint', 'render_nodeinfo2' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'version' => array(
							'required'    => true,
							'type'        => 'string',
							'description' => __( 'The version of the NodeInfo2 scheme', 'nodeinfo' ),
							'enum'        => array(
								'1.0',
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Render the discovery file.
	 *
	 * @param  WP_REST_Request $request the request object
	 * @return WP_REST_Response         the response object
	 */
	public static function render_discovery( WP_REST_Request $request ) {
		$discovery          = array();
		$discovery['links'] = array(
			array(
				'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/2.1',
				'href' => get_rest_url( null, '/nodeinfo/2.1' ),
			),
			array(
				'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/2.0',
				'href' => get_rest_url( null, '/nodeinfo/2.0' ),
			),
			array(
				'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/1.1',
				'href' => get_rest_url( null, '/nodeinfo/1.1' ),
			),
			array(
				'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/1.0',
				'href' => get_rest_url( null, '/nodeinfo/1.0' ),
			),
		);

		$discovery = apply_filters( 'wellknown_nodeinfo_data', $discovery );

		// Create the response object
		$response = new WP_REST_Response( $discovery );
		$response->header( 'Content-Type', 'application/json; profile=http://nodeinfo.diaspora.software' );

		return $response;
	}

	/**
	 * Render the NodeInfo file.
	 *
	 * @param  WP_REST_Request $request the request object
	 * @return WP_REST_Response         the response object
	 */
	public static function render_nodeinfo( WP_REST_Request $request ) {
		require_once 'class-nodeinfo.php';

		$nodeinfo = new Nodeinfo( $request->get_param( 'version' ) );

		// Create the response object
		return new WP_REST_Response( $nodeinfo->to_array() );
	}

	/**
	 * Render the NodeInfo2 file.
	 *
	 * @param  WP_REST_Request $request the request object
	 * @return WP_REST_Response         the response object
	 */
	public static function render_nodeinfo2( WP_REST_Request $request ) {
		require_once 'class-nodeinfo2.php';

		$nodeinfo2 = new Nodeinfo2( $request->get_param( 'version' ) );

		// Create the response object
		return new WP_REST_Response( $nodeinfo2->to_array() );
	}

	/**
	 * Add Host-Meta and WebFinger discovery links
	 *
	 * @param  array $jrd the JRD file used by Host-Meta and WebFinger
	 * @return array      the extended JRD file
	 */
	public static function render_jrd( $jrd ) {
		$jrd['links'][] = array(
			'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/2.1',
			'href' => get_rest_url( null, '/nodeinfo/2.1' ),
		);

		$jrd['links'][] = array(
			'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/2.0',
			'href' => get_rest_url( null, '/nodeinfo/2.0' ),
		);

		$jrd['links'][] = array(
			'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/1.1',
			'href' => get_rest_url( null, '/nodeinfo/1.1' ),
		);

		$jrd['links'][] = array(
			'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/1.0',
			'href' => get_rest_url( null, '/nodeinfo/1.0' ),
		);

		return $jrd;
	}
}
