<?php
/**
 * Deprecated class for backwards compatibility.
 *
 * @package Nodeinfo
 * @deprecated 3.0.0 Use Nodeinfo\Controller\Nodeinfo instead.
 */

/**
 * Nodeinfo_Endpoint class.
 *
 * @deprecated 3.0.0 Use Nodeinfo\Controller\Nodeinfo instead.
 */
class Nodeinfo_Endpoint extends Nodeinfo\Controller\Nodeinfo {
	/**
	 * Constructor.
	 */
	public function __construct() {
		\_doing_it_wrong(
			__CLASS__,
			\esc_html__( 'Nodeinfo_Endpoint is deprecated. Use Nodeinfo\Controller\Nodeinfo instead.', 'nodeinfo' ),
			'3.0.0'
		);
	}
}
