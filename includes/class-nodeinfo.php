<?php
/**
 * Nodeinfo class
 *
 * @link https://github.com/jhass/nodeinfo
 */
class Nodeinfo {
	public $version = '2.0';
	public $software = array();
	public $usage = array();
	public $openRegistrations = false; // phpcs:ignore
	public $services = array(
		'inbound' => array(),
		'outbound' => array(),
	);
	public $protocols = array();
	public $metadata;

	public function __construct( $version = '2.0' ) {
		if ( in_array( $version, array( '1.0', '1.1', '2.0' ) ) ) {
			$this->version = $version;
		}

		$this->generate_software();
		$this->generate_usage();
		$this->generate_protocols();
		$this->generate_services();
		$this->generate_metadata();
		$this->openRegistrations = (boolean) get_option( 'users_can_register', false ); // phpcs:ignore
	}

	public function generate_usage() {
		$users = count_users();
		$posts = wp_count_posts();
		$comments = wp_count_comments();

		$this->usage = apply_filters( 'nodeinfo_data_usage', array(
			'users' => array(
				'total' => (int) $users['total_users'],
			),
			'localPosts' => (int) $posts->publish,
			'localComments' => (int) $comments->approved,
		), $this->version );
	}

	public function generate_software() {
		$this->software = apply_filters( 'nodeinfo_data_software', array(
			'name' => 'wordpress',
			'version' => get_bloginfo( 'version' ),
		), $this->version );
	}

	public function generate_protocols() {
		$protocols = $this->protocols;

		if ( '2.0' == $this->version ) {
			$protocols = array();
		} else {
			$protocols['inbound'] = array( 'smtp' );
			$protocols['outbound'] = array( 'smtp' );
		}

		$this->protocols = apply_filters( 'nodeinfo_data_protocols', $protocols, $this->version );
	}

	public function generate_services() {
		$services = $this->services;

		if ( '2.0' == $this->version ) {
			$services['inbound'] = array( 'atom1.0', 'rss2.0', 'pop3' );
			$services['outbound'] = array( 'atom1.0', 'rss2.0', 'wordpress', 'smtp' );
		} else {
			$services['outbound'] = array( ' smtp' );
		}

		$this->services = apply_filters( 'nodeinfo_data_services', $services, $this->version );
	}

	public function generate_metadata() {
		$this->metadata = apply_filters( 'nodeinfo_data_metadata', new stdClass, $this->version );
	}

	public function to_array() {
		return apply_filters( 'nodeinfo_data', get_object_vars( $this ), $this->version );
	}
}
