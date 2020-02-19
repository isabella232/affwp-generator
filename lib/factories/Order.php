<?php
/**
 * Single Order.
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories
 */


namespace Affiliate_WP_Generator\Factories;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Order
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories
 */
class Order {

	public function __construct( $args ) {
		$defaults   = array(
			'id'       => '',
			'customer' => 0,
			'total'    => 0,
			'status'   => '',
		);
		$this->args = wp_parse_args( $args, $defaults );
	}

}