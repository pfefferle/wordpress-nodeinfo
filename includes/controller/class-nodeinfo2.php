<?php
/**
 * NodeInfo2 REST Controller.
 *
 * @package Nodeinfo
 * @link https://github.com/jaywink/nodeinfo2
 */

namespace Nodeinfo\Controller;

use function Nodeinfo\get_active_users;
use function Nodeinfo\get_masked_version;

/**
 * NodeInfo2 REST Controller class.
 *
 * Handles NodeInfo2 endpoints (version 1.0).
 */
class Nodeinfo2 extends \WP_REST_Controller {

	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'nodeinfo2';

	/**
	 * Register the routes.
	 */
	public function register_routes() {
		\register_rest_route(
			$this->namespace,
			'/(?P<version>\d\.\d)',
			array(
				'args'   => array(
					'version' => array(
						'description' => 'The NodeInfo2 schema version.',
						'type'        => 'string',
						'enum'        => array( '1.0' ),
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
	 * Retrieves NodeInfo2 data.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response The response object.
	 */
	public function get_item( $request ) {
		$version = $request->get_param( 'version' );

		$users = \get_users(
			array(
				'capability__in' => array( 'publish_posts' ),
				'fields'         => 'ID',
			)
		);

		$user_count = \is_array( $users ) ? \count( $users ) : 1;

		$posts    = \wp_count_posts();
		$comments = \wp_count_comments();

		$nodeinfo2 = array(
			'version'           => $version,
			'server'            => \apply_filters(
				'nodeinfo2_data_server',
				array(
					'baseUrl'  => \home_url( '/' ),
					'name'     => \get_bloginfo( 'name' ),
					'software' => 'wordpress',
					'version'  => get_masked_version(),
				),
				$version
			),
			'protocols'         => \apply_filters( 'nodeinfo2_data_protocols', array(), $version ),
			'services'          => \apply_filters(
				'nodeinfo2_data_services',
				array(
					'inbound'  => array( 'atom1.0', 'rss2.0', 'pop3' ),
					'outbound' => array( 'atom1.0', 'rss2.0', 'wordpress', 'smtp' ),
				),
				$version
			),
			'openRegistrations' => (bool) \get_option( 'users_can_register', false ),
			'usage'             => \apply_filters(
				'nodeinfo2_data_usage',
				array(
					'users'         => array(
						'total'          => $user_count,
						'activeMonth'    => get_active_users( '1 month ago' ),
						'activeHalfyear' => get_active_users( '6 month ago' ),
					),
					'localPosts'    => (int) $posts->publish,
					'localComments' => (int) $comments->approved,
				),
				$version
			),
			'metadata'          => \apply_filters(
				'nodeinfo2_data_metadata',
				array(
					'nodeName'        => \get_bloginfo( 'name' ),
					'nodeDescription' => \get_bloginfo( 'description' ),
					'nodeIcon'        => \get_site_icon_url(),
				),
				$version
			),
		);

		/**
		 * Filters the complete NodeInfo2 response.
		 *
		 * @param array  $nodeinfo2 The NodeInfo2 data.
		 * @param string $version   The NodeInfo2 version.
		 */
		$nodeinfo2 = \apply_filters( 'nodeinfo2_data', $nodeinfo2, $version );

		return new \WP_REST_Response( $nodeinfo2 );
	}

	/**
	 * Retrieves the NodeInfo2 schema.
	 *
	 * @return array The schema data.
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'nodeinfo2',
			'type'       => 'object',
			'properties' => array(
				'version'           => array(
					'description' => 'The NodeInfo2 schema version.',
					'type'        => 'string',
					'enum'        => array( '1.0' ),
				),
				'server'            => array(
					'description' => 'Metadata about the server.',
					'type'        => 'object',
					'properties'  => array(
						'baseUrl'  => array(
							'type'   => 'string',
							'format' => 'uri',
						),
						'name'     => array(
							'type' => 'string',
						),
						'software' => array(
							'type' => 'string',
						),
						'version'  => array(
							'type' => 'string',
						),
					),
				),
				'protocols'         => array(
					'description' => 'The protocols supported on this server.',
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
				),
				'services'          => array(
					'description' => 'Third party sites this server can connect to.',
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
					'description' => 'Whether this server allows open self-registration.',
					'type'        => 'boolean',
				),
				'usage'             => array(
					'description' => 'Usage statistics for this server.',
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
					'description' => 'Free form key value pairs for software specific values.',
					'type'        => 'object',
				),
			),
		);
	}
}
