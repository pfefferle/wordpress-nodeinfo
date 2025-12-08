<?php
/**
 * NodeInfo 2.2 Integration.
 *
 * @package Nodeinfo
 * @link https://nodeinfo.diaspora.software/protocol/2.2
 */

namespace Nodeinfo\Integration;

use function Nodeinfo\get_active_users;
use function Nodeinfo\get_masked_version;

/**
 * NodeInfo 2.2 Integration class.
 */
class Nodeinfo22 {

	/**
	 * The version identifier.
	 */
	const VERSION = '2.2';

	/**
	 * Initialize the integration.
	 */
	public static function init() {
		add_filter( 'nodeinfo_versions', array( __CLASS__, 'register_version' ) );
		add_filter( 'nodeinfo_discovery_links', array( __CLASS__, 'discovery_link' ) );
		add_filter( 'nodeinfo_schema', array( __CLASS__, 'schema' ) );
		add_filter( 'nodeinfo_data_software', array( __CLASS__, 'software' ), 10, 2 );
		add_filter( 'nodeinfo_data_services', array( __CLASS__, 'services' ), 10, 2 );
		add_filter( 'nodeinfo_data_usage', array( __CLASS__, 'usage' ), 10, 2 );
		add_filter( 'nodeinfo_data_metadata', array( __CLASS__, 'metadata' ), 10, 2 );
		add_filter( 'nodeinfo_data', array( __CLASS__, 'add_instance' ), 10, 2 );
	}

	/**
	 * Registers the version.
	 *
	 * @param array $versions The versions array.
	 * @return array The modified versions array.
	 */
	public static function register_version( $versions ) {
		$versions[] = self::VERSION;
		return $versions;
	}

	/**
	 * Adds the discovery link.
	 *
	 * @param array $links The discovery links.
	 * @return array The modified links.
	 */
	public static function discovery_link( $links ) {
		$links[] = array(
			'rel'  => 'http://nodeinfo.diaspora.software/ns/schema/' . self::VERSION,
			'href' => get_rest_url( null, '/nodeinfo/' . self::VERSION ),
		);
		return $links;
	}

	/**
	 * Adds the schema for NodeInfo 2.2.
	 *
	 * @param array $schema The schema.
	 * @return array The modified schema.
	 */
	public static function schema( $schema ) {
		// NodeInfo 2.2 schema - adds instance and activeWeek.
		$schema['properties'] = array_merge(
			$schema['properties'],
			array(
				'version'           => array(
					'description' => 'The NodeInfo schema version.',
					'type'        => 'string',
				),
				'instance'          => array(
					'description' => 'Metadata about this specific instance.',
					'type'        => 'object',
					'properties'  => array(
						'name'        => array( 'type' => 'string' ),
						'description' => array( 'type' => 'string' ),
					),
				),
				'software'          => array(
					'description' => 'Metadata about server software in use.',
					'type'        => 'object',
					'properties'  => array(
						'name'       => array( 'type' => 'string' ),
						'version'    => array( 'type' => 'string' ),
						'repository' => array(
							'type'   => 'string',
							'format' => 'uri',
						),
						'homepage'   => array(
							'type'   => 'string',
							'format' => 'uri',
						),
					),
				),
				'protocols'         => array(
					'description' => 'The protocols supported on this server.',
					'type'        => 'array',
					'items'       => array( 'type' => 'string' ),
				),
				'services'          => array(
					'description' => 'Third party sites this server can connect to.',
					'type'        => 'object',
					'properties'  => array(
						'inbound'  => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'outbound' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
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
								'total'          => array( 'type' => 'integer' ),
								'activeMonth'    => array( 'type' => 'integer' ),
								'activeHalfyear' => array( 'type' => 'integer' ),
								'activeWeek'     => array( 'type' => 'integer' ),
							),
						),
						'localPosts'    => array( 'type' => 'integer' ),
						'localComments' => array( 'type' => 'integer' ),
					),
				),
				'metadata'          => array(
					'description' => 'Free form key value pairs for software specific values.',
					'type'        => 'object',
				),
			)
		);

		return $schema;
	}

	/**
	 * Adds software information.
	 *
	 * @param array  $software The software data.
	 * @param string $version  The NodeInfo version.
	 * @return array The modified software data.
	 */
	public static function software( $software, $version ) {
		if ( self::VERSION !== $version ) {
			return $software;
		}

		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- NodeInfo spec uses lowercase.
		$software['name']       = 'wordpress';
		$software['version']    = get_masked_version();
		$software['repository'] = 'https://github.com/wordpress/wordpress';

		return $software;
	}

	/**
	 * Adds services.
	 *
	 * @param array  $services The services data.
	 * @param string $version  The NodeInfo version.
	 * @return array The modified services data.
	 */
	public static function services( $services, $version ) {
		if ( self::VERSION !== $version ) {
			return $services;
		}

		$services['inbound']  = array( 'atom1.0', 'rss2.0', 'pop3' );
		$services['outbound'] = array( 'atom1.0', 'rss2.0', 'wordpress', 'smtp' );

		return $services;
	}

	/**
	 * Adds usage statistics.
	 *
	 * @param array  $usage   The usage data.
	 * @param string $version The NodeInfo version.
	 * @return array The modified usage data.
	 */
	public static function usage( $usage, $version ) {
		if ( self::VERSION !== $version ) {
			return $usage;
		}

		$users = get_users(
			array(
				'fields'         => 'ID',
				'capability__in' => array( 'publish_posts' ),
			)
		);

		$user_count = is_array( $users ) ? count( $users ) : 1;

		$posts    = wp_count_posts();
		$comments = wp_count_comments();

		$usage['users'] = array(
			'total'          => $user_count,
			'activeMonth'    => get_active_users( '1 month ago' ),
			'activeHalfyear' => get_active_users( '6 month ago' ),
			'activeWeek'     => get_active_users( '1 week ago' ),
		);

		$usage['localPosts']    = (int) $posts->publish;
		$usage['localComments'] = (int) $comments->approved;

		return $usage;
	}

	/**
	 * Adds metadata.
	 *
	 * @param array  $metadata The metadata.
	 * @param string $version  The NodeInfo version.
	 * @return array The modified metadata.
	 */
	public static function metadata( $metadata, $version ) {
		if ( self::VERSION !== $version ) {
			return $metadata;
		}

		$metadata['nodeName']        = get_bloginfo( 'name' );
		$metadata['nodeDescription'] = get_bloginfo( 'description' );
		$metadata['nodeIcon']        = get_site_icon_url();

		return $metadata;
	}

	/**
	 * Adds instance information (new in 2.2).
	 *
	 * @param array  $nodeinfo The NodeInfo data.
	 * @param string $version  The NodeInfo version.
	 * @return array The modified NodeInfo data.
	 */
	public static function add_instance( $nodeinfo, $version ) {
		if ( self::VERSION !== $version ) {
			return $nodeinfo;
		}

		$nodeinfo['instance'] = array(
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
		);

		return $nodeinfo;
	}
}
