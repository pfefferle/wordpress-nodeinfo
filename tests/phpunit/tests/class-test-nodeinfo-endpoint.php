<?php
/**
 * Test NodeInfo REST Endpoint.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Tests;

/**
 * Test class for the NodeInfo REST endpoint.
 *
 * @coversDefaultClass \Nodeinfo\Controller\Nodeinfo
 */
class Test_Nodeinfo_Endpoint extends \WP_UnitTestCase {

	/**
	 * Test REST Server.
	 *
	 * @var \WP_REST_Server
	 */
	protected $server;

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();

		global $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();
		$this->server   = $wp_rest_server;

		do_action( 'rest_api_init' );
	}

	/**
	 * Tear down the test.
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tear_down();
	}

	/**
	 * Test that the discovery endpoint is registered.
	 *
	 * @covers ::register_routes
	 */
	public function test_discovery_endpoint_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/nodeinfo/discovery', $routes );
	}

	/**
	 * Test that the versioned endpoint is registered.
	 *
	 * @covers ::register_routes
	 */
	public function test_versioned_endpoint_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/nodeinfo/(?P<version>\\d\\.\\d)', $routes );
	}

	/**
	 * Test the discovery endpoint response.
	 *
	 * @covers ::get_discovery
	 */
	public function test_discovery_endpoint_response() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/discovery' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'links', $data );
		$this->assertIsArray( $data['links'] );
	}

	/**
	 * Test that all NodeInfo versions are in discovery links.
	 *
	 * @covers ::get_discovery
	 */
	public function test_discovery_contains_all_versions() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/discovery' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$versions = array( '1.0', '1.1', '2.0', '2.1' );
		$links    = $data['links'];

		foreach ( $versions as $version ) {
			$found = false;
			foreach ( $links as $link ) {
				if ( strpos( $link['rel'], $version ) !== false ) {
					$found = true;
					break;
				}
			}
			$this->assertTrue( $found, "Version {$version} not found in discovery links" );
		}
	}

	/**
	 * Test the NodeInfo 2.0 endpoint response.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo_20_endpoint() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( '2.0', $data['version'] );
		$this->assertArrayHasKey( 'software', $data );
		$this->assertArrayHasKey( 'protocols', $data );
		$this->assertArrayHasKey( 'services', $data );
		$this->assertArrayHasKey( 'openRegistrations', $data );
		$this->assertArrayHasKey( 'usage', $data );
		$this->assertArrayHasKey( 'metadata', $data );
	}

	/**
	 * Test the NodeInfo 2.1 endpoint response.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo_21_endpoint() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.1' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( '2.1', $data['version'] );
		$this->assertArrayHasKey( 'software', $data );
		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- NodeInfo spec uses lowercase.
		$this->assertEquals( 'wordpress', $data['software']['name'] );
	}

	/**
	 * Test the NodeInfo 1.0 endpoint response.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo_10_endpoint() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( '1.0', $data['version'] );
		$this->assertArrayHasKey( 'protocols', $data );
		// NodeInfo 1.0 uses inbound/outbound for protocols.
		$this->assertArrayHasKey( 'inbound', $data['protocols'] );
		$this->assertArrayHasKey( 'outbound', $data['protocols'] );
	}

	/**
	 * Test invalid version returns error.
	 *
	 * @covers ::get_item
	 */
	public function test_invalid_version_returns_error() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/9.9' );
		$response = $this->server->dispatch( $request );

		// Returns 400 (bad request) because enum validation fails.
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test software name is lowercase per NodeInfo spec.
	 *
	 * @covers \Nodeinfo\Integration\Nodeinfo20::software
	 */
	public function test_software_name() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- NodeInfo spec uses lowercase.
		$this->assertEquals( 'wordpress', $data['software']['name'] );
	}

	/**
	 * Test services structure.
	 *
	 * @covers \Nodeinfo\Integration\Nodeinfo20::services
	 */
	public function test_services_structure() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'inbound', $data['services'] );
		$this->assertArrayHasKey( 'outbound', $data['services'] );
		$this->assertIsArray( $data['services']['inbound'] );
		$this->assertIsArray( $data['services']['outbound'] );
	}

	/**
	 * Test usage statistics structure.
	 *
	 * @covers \Nodeinfo\Integration\Nodeinfo20::usage
	 */
	public function test_usage_structure() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'users', $data['usage'] );
		$this->assertArrayHasKey( 'total', $data['usage']['users'] );
		$this->assertArrayHasKey( 'activeMonth', $data['usage']['users'] );
		$this->assertArrayHasKey( 'activeHalfyear', $data['usage']['users'] );
		$this->assertArrayHasKey( 'localPosts', $data['usage'] );
		$this->assertArrayHasKey( 'localComments', $data['usage'] );
	}

	/**
	 * Test metadata contains node info.
	 *
	 * @covers \Nodeinfo\Integration\Nodeinfo20::metadata
	 */
	public function test_metadata_structure() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'nodeName', $data['metadata'] );
		$this->assertArrayHasKey( 'nodeDescription', $data['metadata'] );
	}

	/**
	 * Test openRegistrations reflects users_can_register option.
	 *
	 * @covers ::get_item
	 */
	public function test_open_registrations() {
		update_option( 'users_can_register', '1' );

		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertTrue( $data['openRegistrations'] );

		update_option( 'users_can_register', '0' );

		$request  = new \WP_REST_Request( 'GET', '/nodeinfo/2.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertFalse( $data['openRegistrations'] );
	}
}
