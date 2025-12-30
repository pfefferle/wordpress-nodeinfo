<?php
/**
 * Nodeinfo Class
 *
 * @package Nodeinfo
 */

namespace Nodeinfo;

use Nodeinfo\Controller\Nodeinfo as Controller_Nodeinfo;
use Nodeinfo\Controller\Nodeinfo2 as Controller_Nodeinfo2;
use Nodeinfo\Integration\Nodeinfo10;
use Nodeinfo\Integration\Nodeinfo11;
use Nodeinfo\Integration\Nodeinfo20;
use Nodeinfo\Integration\Nodeinfo21;
use Nodeinfo\Integration\Nodeinfo22;

/**
 * Nodeinfo Class
 *
 * @package Nodeinfo
 */
class Nodeinfo {
	/**
	 * Instance of the class.
	 *
	 * @var Nodeinfo
	 */
	private static $instance;

	/**
	 * Whether the class has been initialized.
	 *
	 * @var boolean
	 */
	private $initialized = false;

	/**
	 * Get the instance of the class.
	 *
	 * @return Nodeinfo
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Do not allow multiple instances of the class.
	 */
	private function __construct() {
		// Do nothing.
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		if ( $this->initialized ) {
			return;
		}

		$this->register_integrations();
		$this->register_hooks();

		if ( \is_admin() ) {
			$this->register_admin_hooks();
		}

		$this->initialized = true;
	}

	/**
	 * Register NodeInfo version integrations.
	 *
	 * These only register filters, so they can be called directly.
	 */
	public function register_integrations() {
		Nodeinfo10::init();
		Nodeinfo11::init();
		Nodeinfo20::init();
		Nodeinfo21::init();
		Nodeinfo22::init();
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		// Register REST routes.
		\add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Add WebFinger and Host-Meta discovery.
		\add_filter( 'webfinger_user_data', array( Controller_Nodeinfo::class, 'jrd' ), 10, 3 );
		\add_filter( 'webfinger_post_data', array( Controller_Nodeinfo::class, 'jrd' ), 10, 3 );
		\add_filter( 'host_meta', array( Controller_Nodeinfo::class, 'jrd' ) );

		// Add rewrite rules for well-known endpoints (only during flush).
		\add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );

		// Register deprecated filter handlers.
		\add_filter( 'nodeinfo_discovery', array( $this, 'deprecated_wellknown_nodeinfo_data' ), 99 );
	}

	/**
	 * Handles the deprecated wellknown_nodeinfo_data filter.
	 *
	 * @param array $discovery The discovery document.
	 * @return array The filtered discovery document.
	 */
	public function deprecated_wellknown_nodeinfo_data( $discovery ) {
		/**
		 * Filters the NodeInfo discovery document.
		 *
		 * @deprecated 3.0.0 Use nodeinfo_discovery instead.
		 *
		 * @param array $discovery The discovery document.
		 */
		return \apply_filters_deprecated(
			'wellknown_nodeinfo_data',
			array( $discovery ),
			'3.0.0',
			'nodeinfo_discovery'
		);
	}

	/**
	 * Register admin hooks.
	 */
	public function register_admin_hooks() {
		// Initialize Site Health checks.
		\add_action( 'admin_init', array( Health_Check::class, 'init' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		$nodeinfo_controller = new Controller_Nodeinfo();
		$nodeinfo_controller->register_routes();

		$nodeinfo2_controller = new Controller_Nodeinfo2();
		$nodeinfo2_controller->register_routes();
	}

	/**
	 * Add rewrite rules for well-known endpoints.
	 *
	 * @param array $rules The existing rewrite rules.
	 * @return array The modified rewrite rules.
	 */
	public function add_rewrite_rules( $rules ) {
		$new_rules = array(
			'^.well-known/nodeinfo'    => 'index.php?rest_route=/nodeinfo/discovery',
			'^.well-known/x-nodeinfo2' => 'index.php?rest_route=/nodeinfo2/1.0',
		);

		return \array_merge( $new_rules, $rules );
	}

	/**
	 * Handle plugin activation.
	 *
	 * Initializes the plugin and flushes rewrite rules. The rewrite_rules_array
	 * filter will add our rules during the flush.
	 */
	public static function activate() {
		self::get_instance()->init();
		\flush_rewrite_rules();
	}

	/**
	 * Handle plugin deactivation.
	 *
	 * Should be called on plugin deactivation.
	 */
	public static function deactivate() {
		\flush_rewrite_rules();
	}
}
