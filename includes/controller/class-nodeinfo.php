<?php
/**
 * NodeInfo REST Controller.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Controller;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;

/**
 * NodeInfo REST Controller class.
 *
 * Handles NodeInfo discovery and versioned endpoints (1.0, 1.1, 2.0, 2.1).
 */
class Nodeinfo extends WP_REST_Controller {

	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'nodeinfo';

	/**
	 * Register the routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/discovery',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_discovery' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/(?P<version>\d\.\d)',
			array(
				'args'   => array(
					'version' => array(
						'description' => __( 'The NodeInfo schema version.', 'nodeinfo' ),
						'type'        => 'string',
						'enum'        => array( '1.0', '1.1', '2.0', '2.1' ),
						'required'    => true,
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Retrieves the discovery document.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function get_discovery( $request ) {
		$discovery = array(
			'links' => array(
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
			),
		);

		/**
		 * Filters the NodeInfo discovery document.
		 *
		 * @param array $discovery The discovery document.
		 */
		$discovery = apply_filters( 'nodeinfo_discovery', $discovery );

		$response = new WP_REST_Response( $discovery );
		$response->header( 'Content-Type', 'application/json; profile=http://nodeinfo.diaspora.software' );

		return $response;
	}

	/**
	 * Retrieves NodeInfo for a specific version.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function get_item( $request ) {
		$version = $request->get_param( 'version' );

		$nodeinfo = array(
			'version'           => $version,
			'software'          => apply_filters( 'nodeinfo_data_software', array(), $version ),
			'protocols'         => apply_filters( 'nodeinfo_data_protocols', array(), $version ),
			'services'          => apply_filters(
				'nodeinfo_data_services',
				array(
					'inbound'  => array(),
					'outbound' => array(),
				),
				$version
			),
			'openRegistrations' => (bool) get_option( 'users_can_register', false ),
			'usage'             => apply_filters( 'nodeinfo_data_usage', array(), $version ),
			'metadata'          => apply_filters( 'nodeinfo_data_metadata', array(), $version ),
		);

		/**
		 * Filters the complete NodeInfo response.
		 *
		 * @param array  $nodeinfo The NodeInfo data.
		 * @param string $version  The NodeInfo version.
		 */
		$nodeinfo = apply_filters( 'nodeinfo_data', $nodeinfo, $version );

		return new WP_REST_Response( $nodeinfo );
	}

	/**
	 * Adds Host-Meta and WebFinger discovery links.
	 *
	 * @param array $jrd The JRD document.
	 * @return array The modified JRD document.
	 */
	public static function jrd( $jrd ) {
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

	/**
	 * Retrieves the NodeInfo schema.
	 *
	 * @return array The schema data.
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'nodeinfo',
			'type'       => 'object',
			'properties' => array(
				'version'           => array(
					'description' => __( 'The NodeInfo schema version.', 'nodeinfo' ),
					'type'        => 'string',
					'enum'        => array( '1.0', '1.1', '2.0', '2.1' ),
				),
				'software'          => array(
					'description' => __( 'Metadata about server software in use.', 'nodeinfo' ),
					'type'        => 'object',
					'properties'  => array(
						'name'       => array(
							'type' => 'string',
						),
						'version'    => array(
							'type' => 'string',
						),
						'homepage'   => array(
							'type'   => 'string',
							'format' => 'uri',
						),
						'repository' => array(
							'type'   => 'string',
							'format' => 'uri',
						),
					),
				),
				'protocols'         => array(
					'description' => __( 'The protocols supported on this server.', 'nodeinfo' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
				),
				'services'          => array(
					'description' => __( 'Third party sites this server can connect to.', 'nodeinfo' ),
					'type'        => 'object',
					'properties'  => array(
						'inbound'  => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
						'outbound' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
					),
				),
				'openRegistrations' => array(
					'description' => __( 'Whether this server allows open self-registration.', 'nodeinfo' ),
					'type'        => 'boolean',
				),
				'usage'             => array(
					'description' => __( 'Usage statistics for this server.', 'nodeinfo' ),
					'type'        => 'object',
					'properties'  => array(
						'users'         => array(
							'type'       => 'object',
							'properties' => array(
								'total'          => array(
									'type' => 'integer',
								),
								'activeMonth'    => array(
									'type' => 'integer',
								),
								'activeHalfyear' => array(
									'type' => 'integer',
								),
							),
						),
						'localPosts'    => array(
							'type' => 'integer',
						),
						'localComments' => array(
							'type' => 'integer',
						),
					),
				),
				'metadata'          => array(
					'description' => __( 'Free form key value pairs for software specific values.', 'nodeinfo' ),
					'type'        => 'object',
				),
			),
		);
	}
}
