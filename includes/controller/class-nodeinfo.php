<?php
/**
 * NodeInfo REST Controller.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Controller;

/**
 * NodeInfo REST Controller class.
 *
 * Handles NodeInfo discovery and versioned endpoints.
 * Versions are registered dynamically via filters.
 */
class Nodeinfo extends \WP_REST_Controller {

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
		\register_rest_route(
			$this->namespace,
			'/discovery',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_discovery' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		$versions = $this->get_versions();

		if ( empty( $versions ) ) {
			return;
		}

		\register_rest_route(
			$this->namespace,
			'/(?P<version>\d\.\d)',
			array(
				'args'   => array(
					'version' => array(
						'description' => 'The NodeInfo schema version.',
						'type'        => 'string',
						'enum'        => $versions,
						'required'    => true,
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Gets registered NodeInfo versions.
	 *
	 * @return array List of version strings.
	 */
	protected function get_versions() {
		/**
		 * Filters the list of supported NodeInfo versions.
		 *
		 * @param array $versions List of version strings (e.g., '2.0', '2.1').
		 */
		return \apply_filters( 'nodeinfo_versions', array() );
	}

	/**
	 * Retrieves the discovery document.
	 *
	 * @return \WP_REST_Response The response object.
	 */
	public function get_discovery() {
		$links = array();

		/**
		 * Filters the NodeInfo discovery links.
		 *
		 * @param array $links The discovery links.
		 */
		$links = \apply_filters( 'nodeinfo_discovery_links', $links );

		$discovery = array( 'links' => $links );

		/**
		 * Filters the NodeInfo discovery document.
		 *
		 * @param array $discovery The discovery document.
		 */
		$discovery = \apply_filters( 'nodeinfo_discovery', $discovery );

		$response = new \WP_REST_Response( $discovery );
		$response->header( 'Content-Type', 'application/json; profile=http://nodeinfo.diaspora.software' );

		return $response;
	}

	/**
	 * Retrieves NodeInfo for a specific version.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response The response object.
	 */
	public function get_item( $request ) {
		$version = $request->get_param( 'version' );

		$nodeinfo = array(
			'version'           => $version,
			'software'          => \apply_filters( 'nodeinfo_data_software', array(), $version ),
			'protocols'         => \apply_filters( 'nodeinfo_data_protocols', array(), $version ),
			'services'          => \apply_filters(
				'nodeinfo_data_services',
				array(
					'inbound'  => array(),
					'outbound' => array(),
				),
				$version
			),
			'openRegistrations' => (bool) \get_option( 'users_can_register', false ),
			'usage'             => \apply_filters( 'nodeinfo_data_usage', array(), $version ),
			'metadata'          => \apply_filters( 'nodeinfo_data_metadata', array(), $version ),
		);

		/**
		 * Filters the complete NodeInfo response.
		 *
		 * @param array  $nodeinfo The NodeInfo data.
		 * @param string $version  The NodeInfo version.
		 */
		$nodeinfo = \apply_filters( 'nodeinfo_data', $nodeinfo, $version );

		return new \WP_REST_Response( $nodeinfo );
	}

	/**
	 * Retrieves the NodeInfo schema.
	 *
	 * @return array The schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'nodeinfo',
			'type'       => 'object',
			'properties' => array(),
		);

		/**
		 * Filters the NodeInfo schema.
		 *
		 * @param array $schema The schema data.
		 */
		return \apply_filters( 'nodeinfo_schema', $schema );
	}

	/**
	 * Adds NodeInfo discovery links to JRD documents.
	 *
	 * Translates the nodeinfo_discovery_links filter to JRD format
	 * for WebFinger and Host-Meta discovery.
	 *
	 * @param array $jrd The JRD document.
	 * @return array The modified JRD document.
	 */
	public static function jrd( $jrd ) {
		if ( ! isset( $jrd['links'] ) ) {
			$jrd['links'] = array();
		}

		/**
		 * Filters the NodeInfo discovery links.
		 *
		 * @param array $links The discovery links.
		 */
		$links = \apply_filters( 'nodeinfo_discovery_links', array() );

		$jrd['links'] = \array_merge( $jrd['links'], $links );

		return $jrd;
	}
}
