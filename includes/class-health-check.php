<?php
/**
 * Site Health checks for NodeInfo.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo;

/**
 * Health_Check class.
 *
 * Adds Site Health checks to verify NodeInfo endpoints are accessible.
 */
class Health_Check {

	/**
	 * Initialize the health checks.
	 */
	public static function init() {
		\add_filter( 'site_status_tests', array( __CLASS__, 'register_tests' ) );
	}

	/**
	 * Register Site Health tests.
	 *
	 * @param array $tests The Site Health tests.
	 * @return array The modified tests.
	 */
	public static function register_tests( $tests ) {
		$tests['direct']['nodeinfo_wellknown'] = array(
			'label' => \__( 'NodeInfo Well-Known Endpoint', 'nodeinfo' ),
			'test'  => array( __CLASS__, 'test_wellknown_endpoint' ),
		);

		$tests['direct']['nodeinfo_endpoint'] = array(
			'label' => \__( 'NodeInfo REST Endpoint', 'nodeinfo' ),
			'test'  => array( __CLASS__, 'test_nodeinfo_endpoint' ),
		);

		return $tests;
	}

	/**
	 * Test if the .well-known/nodeinfo endpoint is accessible.
	 *
	 * @return array The test result.
	 */
	public static function test_wellknown_endpoint() {
		$result = array(
			'label'       => \__( 'NodeInfo discovery is working', 'nodeinfo' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => \__( 'Fediverse', 'nodeinfo' ),
				'color' => 'green',
			),
			'description' => \sprintf(
				'<p>%s</p>',
				\__( 'The NodeInfo discovery endpoint is accessible and other Fediverse servers can find information about your site.', 'nodeinfo' )
			),
			'actions'     => '',
			'test'        => 'nodeinfo_wellknown',
		);

		$url      = \home_url( '/.well-known/nodeinfo' );
		$response = \wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		if ( \is_wp_error( $response ) ) {
			$result['status']      = 'critical';
			$result['label']       = \__( 'NodeInfo discovery endpoint is not accessible', 'nodeinfo' );
			$result['description'] = \sprintf(
				'<p>%s</p><p>%s</p>',
				\__( 'The NodeInfo discovery endpoint could not be reached. Other Fediverse servers may not be able to discover your site.', 'nodeinfo' ),
				\sprintf(
					/* translators: %s: Error message */
					\__( 'Error: %s', 'nodeinfo' ),
					$response->get_error_message()
				)
			);
			$result['badge']['color'] = 'red';

			return $result;
		}

		$status_code = \wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			$result['status']      = 'critical';
			$result['label']       = \__( 'NodeInfo discovery endpoint returned an error', 'nodeinfo' );
			$result['description'] = \sprintf(
				'<p>%s</p><p>%s</p>',
				\__( 'The NodeInfo discovery endpoint returned an unexpected status code. This may indicate a server configuration issue.', 'nodeinfo' ),
				\sprintf(
					/* translators: %d: HTTP status code */
					\__( 'HTTP Status: %d', 'nodeinfo' ),
					$status_code
				)
			);
			$result['badge']['color'] = 'red';

			return $result;
		}

		$body = \wp_remote_retrieve_body( $response );
		$data = \json_decode( $body, true );

		if ( empty( $data['links'] ) ) {
			$result['status']         = 'recommended';
			$result['label']          = \__( 'NodeInfo discovery returns incomplete data', 'nodeinfo' );
			$result['description']    = \sprintf(
				'<p>%s</p>',
				\__( 'The NodeInfo discovery endpoint is accessible but does not contain the expected links. This may indicate a plugin conflict or configuration issue.', 'nodeinfo' )
			);
			$result['badge']['color'] = 'orange';

			return $result;
		}

		$result['actions'] = \sprintf(
			'<p><a href="%s" target="_blank">%s</a></p>',
			\esc_url( $url ),
			\__( 'View NodeInfo discovery document', 'nodeinfo' )
		);

		return $result;
	}

	/**
	 * Test if a NodeInfo REST endpoint is accessible.
	 *
	 * @return array The test result.
	 */
	public static function test_nodeinfo_endpoint() {
		$result = array(
			'label'       => \__( 'NodeInfo endpoint is working', 'nodeinfo' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => \__( 'Fediverse', 'nodeinfo' ),
				'color' => 'green',
			),
			'description' => \sprintf(
				'<p>%s</p>',
				\__( 'The NodeInfo REST endpoint returns valid data about your site.', 'nodeinfo' )
			),
			'actions'     => '',
			'test'        => 'nodeinfo_endpoint',
		);

		// Test the latest version (2.2).
		$url      = \get_rest_url( null, '/nodeinfo/2.2' );
		$response = \wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		if ( \is_wp_error( $response ) ) {
			$result['status']      = 'critical';
			$result['label']       = \__( 'NodeInfo REST endpoint is not accessible', 'nodeinfo' );
			$result['description'] = \sprintf(
				'<p>%s</p><p>%s</p>',
				\__( 'The NodeInfo REST endpoint could not be reached. This may indicate that the REST API is disabled or blocked.', 'nodeinfo' ),
				\sprintf(
					/* translators: %s: Error message */
					\__( 'Error: %s', 'nodeinfo' ),
					$response->get_error_message()
				)
			);
			$result['badge']['color'] = 'red';

			return $result;
		}

		$status_code = \wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			$result['status']      = 'critical';
			$result['label']       = \__( 'NodeInfo REST endpoint returned an error', 'nodeinfo' );
			$result['description'] = \sprintf(
				'<p>%s</p><p>%s</p>',
				\__( 'The NodeInfo REST endpoint returned an unexpected status code.', 'nodeinfo' ),
				\sprintf(
					/* translators: %d: HTTP status code */
					\__( 'HTTP Status: %d', 'nodeinfo' ),
					$status_code
				)
			);
			$result['badge']['color'] = 'red';

			return $result;
		}

		$body = \wp_remote_retrieve_body( $response );
		$data = \json_decode( $body, true );

		if ( empty( $data['software']['name'] ) || empty( $data['version'] ) ) {
			$result['status']         = 'recommended';
			$result['label']          = \__( 'NodeInfo endpoint returns incomplete data', 'nodeinfo' );
			$result['description']    = \sprintf(
				'<p>%s</p>',
				\__( 'The NodeInfo endpoint is accessible but does not contain all expected fields.', 'nodeinfo' )
			);
			$result['badge']['color'] = 'orange';

			return $result;
		}

		$result['actions'] = \sprintf(
			'<p><a href="%s" target="_blank">%s</a></p>',
			\esc_url( $url ),
			\__( 'View NodeInfo 2.2 endpoint', 'nodeinfo' )
		);

		return $result;
	}
}
