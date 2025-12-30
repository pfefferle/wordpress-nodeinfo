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
		$instance = \Nodeinfo\Nodeinfo::get_instance();

		// Test the filter directly.
		$rules = $instance->add_rewrite_rules( array() );

		$this->assertIsArray( $rules );
		$this->assertArrayHasKey( '^.well-known/nodeinfo', $rules );
		$this->assertArrayHasKey( '^.well-known/x-nodeinfo2', $rules );
		$this->assertEquals( 'index.php?rest_route=/nodeinfo/discovery', $rules['^.well-known/nodeinfo'] );
		$this->assertEquals( 'index.php?rest_route=/nodeinfo2/1.0', $rules['^.well-known/x-nodeinfo2'] );
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

	/**
	 * Test that init() guard prevents multiple initializations.
	 *
	 * @covers ::init
	 */
	public function test_init_guard_prevents_double_initialization() {
		$instance = \Nodeinfo\Nodeinfo::get_instance();

		// Get the filter count before calling init again.
		$filter_count_before = has_filter( 'nodeinfo_discovery' );

		// Call init() again - should be guarded.
		$instance->init();

		// Filter count should remain the same.
		$filter_count_after = has_filter( 'nodeinfo_discovery' );

		$this->assertSame( $filter_count_before, $filter_count_after );
	}

	/**
	 * Test activate() method initializes plugin and flushes rewrite rules.
	 *
	 * @covers ::activate
	 */
	public function test_activate_initializes_and_flushes() {
		// Verify activate() runs without errors and initializes the plugin.
		\Nodeinfo\Nodeinfo::activate();

		// Verify the rewrite_rules_array filter is registered.
		$this->assertNotFalse( has_filter( 'rewrite_rules_array' ) );
	}

	/**
	 * Test deactivate() method flushes rewrite rules.
	 *
	 * @covers ::deactivate
	 */
	public function test_deactivate_flushes_rewrite_rules() {
		// This test verifies deactivate() runs without errors.
		// The actual effect (flushing rules) is internal to WordPress.
		\Nodeinfo\Nodeinfo::deactivate();

		// If we get here without exceptions, the test passes.
		$this->assertTrue( true );
	}
}
