<?php
/**
 * Single Product
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories
 */


namespace Affiliate_WP_Generator\Factories;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Product
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories
 */
class Product {

	public function __construct( $args ) {
		$defaults   = array(
			'id'      => 0,
			'name'    => '',
			'price'   => 0,
			'status'  => '',
			'created' => '',
		);
		$this->args = wp_parse_args( $args, $defaults );
	}

}