<?php
/**
 * Test NodeInfo2 REST Endpoint.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Tests;

/**
 * Test class for the NodeInfo2 REST endpoint.
 *
 * @coversDefaultClass \Nodeinfo\Controller\Nodeinfo2
 */
class Test_Nodeinfo2_Endpoint extends \WP_UnitTestCase {

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
	 * Test that the NodeInfo2 endpoint is registered.
	 *
	 * @covers ::register_routes
	 */
	public function test_nodeinfo2_endpoint_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/nodeinfo2/1.0', $routes );
	}

	/**
	 * Test the NodeInfo2 endpoint response structure.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo2_endpoint_response() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'version', $data );
		$this->assertEquals( '1.0', $data['version'] );
		$this->assertArrayHasKey( 'server', $data );
		$this->assertArrayHasKey( 'protocols', $data );
		$this->assertArrayHasKey( 'openRegistrations', $data );
		$this->assertArrayHasKey( 'usage', $data );
	}

	/**
	 * Test NodeInfo2 server information.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo2_server_info() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'baseUrl', $data['server'] );
		$this->assertArrayHasKey( 'name', $data['server'] );
		$this->assertArrayHasKey( 'software', $data['server'] );
		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- NodeInfo2 spec uses lowercase.
		$this->assertEquals( 'wordpress', $data['server']['software'] );
	}

	/**
	 * Test NodeInfo2 usage statistics.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo2_usage() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
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
	 * Test NodeInfo2 protocols array.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo2_protocols() {
		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertIsArray( $data['protocols'] );
	}

	/**
	 * Test NodeInfo2 openRegistrations.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo2_open_registrations() {
		update_option( 'users_can_register', '1' );

		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertTrue( $data['openRegistrations'] );

		update_option( 'users_can_register', '0' );

		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertFalse( $data['openRegistrations'] );
	}

	/**
	 * Test nodeinfo2_data filter.
	 *
	 * @covers ::get_item
	 */
	public function test_nodeinfo2_data_filter() {
		add_filter(
			'nodeinfo2_data',
			function ( $data ) {
				$data['customField'] = 'test';
				return $data;
			}
		);

		$request  = new \WP_REST_Request( 'GET', '/nodeinfo2/1.0' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'customField', $data );
		$this->assertEquals( 'test', $data['customField'] );
	}
}
