<?php
/**
 * Test NodeInfo Functions.
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Tests;

use function Nodeinfo\get_active_users;
use function Nodeinfo\get_masked_version;

/**
 * Test class for NodeInfo functions.
 */
class Test_Functions extends \WP_UnitTestCase {

	/**
	 * Test get_masked_version returns a version string.
	 *
	 * @covers \Nodeinfo\get_masked_version
	 */
	public function test_get_masked_version() {
		$version = get_masked_version();

		$this->assertIsString( $version );
		$this->assertNotEmpty( $version );
	}

	/**
	 * Test get_masked_version returns major.minor format.
	 *
	 * @covers \Nodeinfo\get_masked_version
	 */
	public function test_get_masked_version_format() {
		$version = get_masked_version();

		// Should match major.minor format (e.g., "6.5").
		$this->assertMatchesRegularExpression( '/^\d+\.\d+$/', $version );
	}

	/**
	 * Test get_active_users returns an integer.
	 *
	 * @covers \Nodeinfo\get_active_users
	 */
	public function test_get_active_users_returns_integer() {
		$active_users = get_active_users( '1 month ago' );

		$this->assertIsInt( $active_users );
	}

	/**
	 * Test get_active_users with different time periods.
	 *
	 * @covers \Nodeinfo\get_active_users
	 */
	public function test_get_active_users_time_periods() {
		$month_users    = get_active_users( '1 month ago' );
		$halfyear_users = get_active_users( '6 month ago' );

		$this->assertIsInt( $month_users );
		$this->assertIsInt( $halfyear_users );
		// Halfyear should be >= month.
		$this->assertGreaterThanOrEqual( $month_users, $halfyear_users );
	}

	/**
	 * Test get_active_users with a user who published a post.
	 *
	 * @covers \Nodeinfo\get_active_users
	 */
	public function test_get_active_users_with_published_post() {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );

		self::factory()->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'publish',
				'post_date'   => gmdate( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
			)
		);

		$active_users = get_active_users( '1 month ago' );

		$this->assertGreaterThanOrEqual( 1, $active_users );
	}
}
