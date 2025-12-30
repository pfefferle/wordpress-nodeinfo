<?php
/**
 * Test Nodeinfo main class.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Tests;

/**
 * Test class for the main Nodeinfo class.
 *
 * @coversDefaultClass \Nodeinfo\Nodeinfo
 */
class Test_Nodeinfo extends \WP_UnitTestCase {

	/**
	 * Test that get_instance returns same instance (singleton).
	 *
	 * @covers ::get_instance
	 */
	public function test_singleton_pattern() {
		$instance1 = \Nodeinfo\Nodeinfo::get_instance();
		$instance2 = \Nodeinfo\Nodeinfo::get_instance();

		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test that get_instance returns Nodeinfo instance.
	 *
	 * @covers ::get_instance
	 */
	public function test_get_instance_returns_nodeinfo() {
		$instance = \Nodeinfo\Nodeinfo::get_instance();

		$this->assertInstanceOf( \Nodeinfo\Nodeinfo::class, $instance );
	}

	/**
	 * Test that REST routes are registered.
	 *
	 * @covers ::register_routes
	 */
	public function test_rest_routes_registered() {
		global $wp_rest_server;

		// Save original state.
		$original_server = $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();
		do_action( 'rest_api_init' );

		$routes = $wp_rest_server->get_routes();

		$this->assertArrayHasKey( '/nodeinfo/discovery', $routes );
		$this->assertArrayHasKey( '/nodeinfo2/(?P<version>\\d\\.\\d)', $routes );

		// Restore original state.
		$wp_rest_server = $original_server;
	}

	/**
	 * Test that integrations register nodeinfo_versions filter.
	 *
	 * @covers ::register_integrations
	 */
	public function test_integrations_register_versions() {
		$versions = apply_filters( 'nodeinfo_versions', array() );

		$this->assertContains( '1.0', $versions );
		$this->assertContains( '1.1', $versions );
		$this->assertContains( '2.0', $versions );
		$this->assertContains( '2.1', $versions );
		$this->assertContains( '2.2', $versions );
	}

	/**
	 * Test that rewrite rules are added.
	 *
	 * @covers ::add_rewrite_rules
	 */
	public function test_rewrite_rules_added() {
		global $wp_rewrite;

		// Save original permalink structure.
		$original_structure = $wp_rewrite->permalink_structure;

		// Enable permalinks for testing.
		$wp_rewrite->set_permalink_structure( '/%postname%/' );

		// Add rewrite rules.
		\Nodeinfo\Nodeinfo::get_instance()->add_rewrite_rules();
		$wp_rewrite->flush_rules();

		$rules = $wp_rewrite->wp_rewrite_rules();

		// Ensure rules is an array.
		$this->assertIsArray( $rules );
		$this->assertArrayHasKey( '^.well-known/nodeinfo', $rules );
		$this->assertArrayHasKey( '^.well-known/x-nodeinfo2', $rules );

		// Restore original permalink structure.
		$wp_rewrite->set_permalink_structure( $original_structure );
		$wp_rewrite->flush_rules();
	}

	/**
	 * Test deprecated wellknown_nodeinfo_data filter triggers deprecation.
	 *
	 * @covers ::deprecated_wellknown_nodeinfo_data
	 */
	public function test_deprecated_filter_triggers_notice() {
		// Add a callback to the deprecated filter.
		add_filter(
			'wellknown_nodeinfo_data',
			function ( $data ) {
				$data['test'] = 'value';
				return $data;
			}
		);

		// Expect a deprecation notice.
		$this->setExpectedDeprecated( 'wellknown_nodeinfo_data' );

		// Trigger the filter chain.
		$discovery = apply_filters( 'nodeinfo_discovery', array( 'links' => array() ) );

		// Verify the deprecated filter was applied.
		$this->assertArrayHasKey( 'test', $discovery );
		$this->assertEquals( 'value', $discovery['test'] );
	}

	/**
	 * Test WebFinger filter is registered.
	 *
	 * @covers ::register_hooks
	 */
	public function test_webfinger_filter_registered() {
		$this->assertTrue( has_filter( 'webfinger_user_data' ) !== false );
		$this->assertTrue( has_filter( 'webfinger_post_data' ) !== false );
		$this->assertTrue( has_filter( 'host_meta' ) !== false );
	}

	/**
	 * Test admin hooks can be registered.
	 *
	 * @covers ::register_admin_hooks
	 */
	public function test_admin_hooks_registered() {
		// Call register_admin_hooks directly to test it works.
		\Nodeinfo\Nodeinfo::get_instance()->register_admin_hooks();

		$this->assertNotFalse( has_action( 'admin_init' ) );
	}
}
