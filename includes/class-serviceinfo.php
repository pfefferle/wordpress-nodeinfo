<?php
/**
 * ServiceInfo class
 *
 * @link https://git.feneas.org/feneas/serviceinfo
 */
class Serviceinfo {
	public $version = '1.0';
	public $server = array();
	public $publicRegistrations = false; // phpcs:ignore
	public $organisation = array();
	public $protocols = array();
	// phpcs:ignore
	public $externalServices = array(
		'inbound' => array(),
		'outbound' => array(),
	);
	public $metrics = array();
	public $features = array();

	public function __construct( $version = '1.0' ) {
		if ( in_array( $version, array( '1.0' ), true ) ) {
			$this->version = $version;
		}

		$this->generate_server();
		$this->generate_organisation();
		$this->generate_protocols();
		$this->generate_external_services();
		$this->generate_metrics();
		$this->generate_features();
		$this->openRegistrations = (boolean) get_option( 'users_can_register', false ); // phpcs:ignore
	}

	public function generate_server() {
		$this->server = apply_filters(
			'serviceinfo_data_server',
			array(
				'id' => home_url( '/' ),
				'name' => get_bloginfo( 'name' ),
				'software' => 'wordpress',
				'version' => get_bloginfo( 'version' ),
			),
			$this->version
		);
	}

	public function generate_organisation() {
		$this->server = apply_filters(
			'serviceinfo_data_server',
			array(
				'name' => get_bloginfo( 'name' ),
				'contact' => get_option( 'admin_email' ),
			),
			$this->version
		);
	}

	public function generate_protocols() {
		$this->protocols = apply_filters(
			'serviceinfo_data_protocols',
			array(),
			$this->version
		);
	}

	public function generate_metrics() {
		$users = count_users();
		$posts = wp_count_posts();
		$comments = wp_count_comments();

		$this->metrics = apply_filters(
			'serviceinfo_data_metrics',
			array(
				array(
					'type' => 'totalUsers',
					'value' => (int) $users['total_users'],
				),
				array(
					'type' => 'localMessages',
					'value' => (int) $posts->publish,
				),
				array(
					'type' => 'localComments',
					'value' => (int) $comments->approved,
				),
			),
			$this->version
		);
	}

	public function generate_features() {
		$this->features = apply_filters(
			'serviceinfo_data_features',
			array(),
			$this->version
		);
	}

	public function generate_external_services() {
		$external_services = $this->externalServices; // phpcs:ignore

		$external_services['inbound'] = array( 'atom1.0', 'rss2.0', 'wordpress', 'pop3' );
		$external_services['outbound'] = array( 'atom1.0', 'rss2.0', 'wordpress', 'smtp' );

		// phpcs:ignore
		$this->externalServices = apply_filters( 'serviceinfo_data_generate_external_services', $external_services, $this->version );
	}

	public function to_array() {
		return apply_filters( 'serviceinfo_data', get_object_vars( $this ), $this->version );
	}
}
