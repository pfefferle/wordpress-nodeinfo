<?php
/**
 * Nodeinfo2 class
 *
 * @link https://github.com/jaywink/nodeinfo2
 */
class Nodeinfo2 {
	public $version           = '1.0';
	public $server            = array();
	public $usage             = array();
	public $openRegistrations = false; // phpcs:ignore
	public $services          = array(
		'inbound'  => array(),
		'outbound' => array(),
	);
	public $protocols         = array();
	public $metadata          = array();

	public function __construct( $version = '1.0' ) {
		if ( in_array( $version, array( '1.0' ), true ) ) {
			$this->version = $version;
		}

		$this->generate_server();
		$this->generate_usage();
		$this->generate_protocols();
		$this->generate_services();
		$this->generate_metadata();
		$this->openRegistrations = (boolean) get_option( 'users_can_register', false ); // phpcs:ignore
	}

	public function generate_usage() {
		$users = get_users(
			array(
				'capability__in' => array( 'publish_posts' ),
			)
		);

		if ( is_array( $users ) ) {
			$users = count( $users );
		} else {
			$users = 1;
		}

		$posts    = wp_count_posts();
		$comments = wp_count_comments();

		$this->usage = apply_filters(
			'nodeinfo2_data_usage',
			array(
				'users'         => array(
					'total'          => $users,
					'activeMonth'    => nodeinfo_get_active_users( '1 month ago' ),
					'activeHalfyear' => nodeinfo_get_active_users( '6 month ago' ),
				),
				'localPosts'    => (int) $posts->publish,
				'localComments' => (int) $comments->approved,
			),
			$this->version
		);
	}

	public function generate_server() {
		$this->server = apply_filters(
			'nodeinfo2_data_server',
			array(
				'baseUrl'  => home_url( '/' ),
				'name'     => get_bloginfo( 'name' ),
				'software' => 'wordpress',
				'version'  => nodeinfo_get_masked_version(),
			),
			$this->version
		);
	}

	public function generate_protocols() {
		$this->protocols = apply_filters( 'nodeinfo2_data_protocols', $this->protocols, $this->version );
	}

	public function generate_services() {
		$services = $this->services;

		$services['inbound']  = array( 'atom1.0', 'rss2.0', 'wordpress', 'pop3' );
		$services['outbound'] = array( 'atom1.0', 'rss2.0', 'wordpress', 'smtp' );

		$this->services = apply_filters( 'nodeinfo2_data_services', $services, $this->version );
	}

	public function generate_metadata() {
		$metadata = $this->metadata;

		$metadata['generator'] = array(
			'name'       => 'NodeInfo WordPress-Plugin',
			'version'    => nodeinfo_version(),
			'repository' => 'https://github.com/pfefferle/wordpress-nodeinfo/',
		);

		$metadata['nodeName']        = \get_bloginfo( 'name' );
		$metadata['nodeDescription'] = \get_bloginfo( 'description' );
		$metadata['nodeIcon']        = \get_site_icon_url();

		$this->metadata = apply_filters( 'nodeinfo2_data_metadata', $metadata, $this->version );
	}

	public function to_array() {
		return apply_filters( 'nodeinfo2_data', get_object_vars( $this ), $this->version );
	}
}
