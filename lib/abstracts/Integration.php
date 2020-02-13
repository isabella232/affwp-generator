<?php
/**
 * Integration Abstract
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */


namespace Affiliate_WP_Generator\Abstracts;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Integration
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */
abstract class Integration {

	/**
	 * The AffiliateWP Integration class
	 *
	 * @since 1.0.0
	 *
	 * @var \Affiliate_WP_Base|\WP_Error
	 */
	private $integration;

	/**
	 * Places an order. Does not simulate a referral visit.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user     The user ID to use as the customer.
	 * @param array $products List of product IDs to use in the order.
	 *
	 * @return int The order ID.
	 */
	abstract public function place_order( $user, $products );

	/**
	 * Adds a product from the integration.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The product name.
	 * @param number $price The product price.
	 * @return int The generated product ID.
	 */
	abstract public function add_product( $name, $price );

	/**
	 * Integration constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $integration Integration name.
	 */
	public function __construct( $integration ) {
		$this->integration = affiliate_wp()->integrations->get( $integration );
	}

	/**
	 * Simulates a visit for the specified affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate The affiliate ID to simulate a visit for.
	 */
	public function simulate_visit( $affiliate ) {

		// Store visit in the database.
		$visit_id = affiliate_wp()->visits->add( array(
				'context'      => $this->integration->context,
				'affiliate_id' => $affiliate,
			)
		);

		// Set the visit and affiliate cookies. This allows integrations to handle setting referrals.
		$_COOKIE['affwp_ref_visit_id'] = $visit_id;
		$_COOKIE['affwp_ref']          = $affiliate;
	}

	/**
	 * Places an order with a referral.
	 *
	 * @param int   $user      The user ID to use as the customer.
	 * @param int   $affiliate The affiliate ID to use for the referral.
	 * @param array $products  List of product IDs to use in the order.
	 *
	 * @return int The order ID.
	 */
	public function place_referred_order( $user, $affiliate, $products ) {
		$this->simulate_visit( $affiliate );

		$payment_id = $this->place_order( $user, $products );

		return $payment_id;
	}
}