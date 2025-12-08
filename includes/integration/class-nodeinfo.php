<?php
/**
 * NodeInfo Integration.
 *
 * Adds WordPress-specific data to NodeInfo responses (versions 1.0, 1.1, 2.0, 2.1).
 *
 * @package Nodeinfo
 */

namespace Nodeinfo\Integration;

use function Nodeinfo\get_active_users;
use function Nodeinfo\get_masked_version;

/**
 * NodeInfo Integration class.
 */
class Nodeinfo {

	/**
	 * Initialize the integration.
	 */
	public static function init() {
		add_filter( 'nodeinfo_data_software', array( __CLASS__, 'software' ), 10, 2 );
		add_filter( 'nodeinfo_data_usage', array( __CLASS__, 'usage' ), 10, 2 );
		add_filter( 'nodeinfo_data_protocols', array( __CLASS__, 'protocols' ), 10, 2 );
		add_filter( 'nodeinfo_data_services', array( __CLASS__, 'services' ), 10, 2 );
		add_filter( 'nodeinfo_data_metadata', array( __CLASS__, 'metadata' ), 10, 2 );
	}

	/**
	 * Adds software information.
	 *
	 * @param array  $software The software data.
	 * @param string $version  The NodeInfo version.
	 * @return array The modified software data.
	 */
	public static function software( $software, $version ) {
		$software['name']    = 'wordpress';
		$software['version'] = get_masked_version();

		if ( '2.1' === $version ) {
			$software['repository'] = 'https://github.com/wordpress/wordpress';
		}

		return $software;
	}

	/**
	 * Adds usage statistics.
	 *
	 * @param array  $usage   The usage data.
	 * @param string $version The NodeInfo version.
	 * @return array The modified usage data.
	 */
	public static function usage( $usage, $version ) {
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
		);

		$usage['localPosts']    = (int) $posts->publish;
		$usage['localComments'] = (int) $comments->approved;

		return $usage;
	}

	/**
	 * Adds protocols.
	 *
	 * @param array  $protocols The protocols data.
	 * @param string $version   The NodeInfo version.
	 * @return array The modified protocols data.
	 */
	public static function protocols( $protocols, $version ) {
		// NodeInfo 1.x uses inbound/outbound structure for protocols.
		if ( version_compare( $version, '2.0', '<' ) ) {
			$protocols['inbound']  = array( 'smtp' );
			$protocols['outbound'] = array( 'smtp' );
		}

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
		if ( version_compare( $version, '2.0', '>=' ) ) {
			$services['inbound']  = array( 'atom1.0', 'rss2.0', 'pop3' );
			$services['outbound'] = array( 'atom1.0', 'rss2.0', 'wordpress', 'smtp' );
		} else {
			$services['outbound'] = array( 'smtp' );
		}

		return $services;
	}

	/**
	 * Adds metadata.
	 *
	 * @param array  $metadata The metadata.
	 * @param string $version  The NodeInfo version.
	 * @return array The modified metadata.
	 */
	public static function metadata( $metadata, $version ) {
		$metadata['nodeName']        = get_bloginfo( 'name' );
		$metadata['nodeDescription'] = get_bloginfo( 'description' );
		$metadata['nodeIcon']        = get_site_icon_url();

		return $metadata;
	}
}
