<?php
/**
 * NodeInfo 1.0 Integration.
 *
 * @package Nodeinfo
 * @link https://nodeinfo.diaspora.software/protocol/1.0
 */

namespace Nodeinfo\Integration;

use function Nodeinfo\get_active_users;
use function Nodeinfo\get_masked_version;

/**
 * NodeInfo 1.0 Integration class.
 */
class Nodeinfo10 {

	/**
	 * The version identifier.
	 */
	const VERSION = '1.0';

	/**
	 * Initialize the integration.
	 */
	public static function init() {
		\add_filter( 'nodeinfo_versions', array( __CLASS__, 'register_version' ) );
		\add_filter( 'nodeinfo_discovery_links', array( __CLASS__, 'discovery_link' ) );
		\add_filter( 'nodeinfo_schema', array( __CLASS__, 'schema' ) );
		\add_filter( 'nodeinfo_data_software', array( __CLASS__, 'software' ), 10, 2 );
		\add_filter( 'nodeinfo_data_protocols', array( __CLASS__, 'protocols' ), 10, 2 );
		\add_filter( 'nodeinfo_data_services', array( __CLASS__, 'services' ), 10, 2 );
		\add_filter( 'nodeinfo_data_usage', array( __CLASS__, 'usage' ), 10, 2 );
		\add_filter( 'nodeinfo_data_metadata', array( __CLASS__, 'metadata' ), 10, 2 );
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
			'href' => \get_rest_url( null, '/nodeinfo/' . self::VERSION ),
		);
		return $links;
	}

	/**
	 * Adds the schema for this version.
	 *
	 * @link https://github.com/jhass/nodeinfo/blob/main/schemas/1.0/schema.json
	 *
	 * @param array $schema The schema.
	 * @return array The modified schema.
	 */
	public static function schema( $schema ) {
		$schema['properties'] = \array_merge(
			$schema['properties'],
			array(
				'version'           => array(
					'description' => 'The NodeInfo schema version.',
					'type'        => 'string',
				),
				'software'          => array(
					'description' => 'Metadata about server software in use.',
					'type'        => 'object',
					'properties'  => array(
						'name'    => array(
							'type' => 'string',
							'enum' => array( 'diaspora', 'friendica', 'redmatrix' ),
						),
						'version' => array( 'type' => 'string' ),
					),
				),
				'protocols'         => array(
					'description' => 'The protocols supported on this server.',
					'type'        => 'object',
					'properties'  => array(
						'inbound'  => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
								'enum' => array( 'buddycloud', 'diaspora', 'friendica', 'gnusocial', 'libertree', 'mediagoblin', 'pumpio', 'redmatrix', 'smtp', 'tent' ),
							),
						),
						'outbound' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
								'enum' => array( 'buddycloud', 'diaspora', 'friendica', 'gnusocial', 'libertree', 'mediagoblin', 'pumpio', 'redmatrix', 'smtp', 'tent' ),
							),
						),
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
								'enum' => array( 'appnet', 'gnusocial', 'pumpio' ),
							),
						),
						'outbound' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
								'enum' => array( 'appnet', 'blogger', 'buddycloud', 'diaspora', 'dreamwidth', 'drupal', 'facebook', 'friendica', 'gnusocial', 'google', 'insanejournal', 'libertree', 'linkedin', 'livejournal', 'mediagoblin', 'myspace', 'pinterest', 'posterous', 'pumpio', 'redmatrix', 'smtp', 'tent', 'tumblr', 'twitter', 'wordpress', 'xmpp' ),
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
									'type'    => 'integer',
									'minimum' => 0,
								),
								'activeMonth'    => array(
									'type'    => 'integer',
									'minimum' => 0,
								),
								'activeHalfyear' => array(
									'type'    => 'integer',
									'minimum' => 0,
								),
							),
						),
						'localPosts'    => array(
							'type'    => 'integer',
							'minimum' => 0,
						),
						'localComments' => array(
							'type'    => 'integer',
							'minimum' => 0,
						),
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
		$software['name']    = 'wordpress';
		$software['version'] = get_masked_version();

		return $software;
	}

	/**
	 * Adds protocols.
	 *
	 * @param array  $protocols The protocols data.
	 * @param string $version   The NodeInfo version.
	 * @return array The modified protocols data.
	 */
	public static function protocols( $protocols, $version ) {
		if ( self::VERSION !== $version ) {
			return $protocols;
		}

		// NodeInfo 1.0 uses inbound/outbound structure.
		$protocols['inbound']  = array();
		$protocols['outbound'] = array();

		return $protocols;
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

		$services['inbound']  = array();
		$services['outbound'] = array( 'smtp' );

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

		$users = \get_users(
			array(
				'fields'         => 'ID',
				'capability__in' => array( 'publish_posts' ),
			)
		);

		$user_count = \is_array( $users ) ? \count( $users ) : 1;

		$posts    = \wp_count_posts();
		$comments = \wp_count_comments();

		$usage['users'] = array(
			'total'          => $user_count,
			'activeMonth'    => get_active_users( '1 month ago' ),
			'activeHalfyear' => get_active_users( '6 month ago' ),
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

		$metadata['nodeName']        = \get_bloginfo( 'name' );
		$metadata['nodeDescription'] = \get_bloginfo( 'description' );
		$metadata['nodeIcon']        = \get_site_icon_url();

		return $metadata;
	}
}
