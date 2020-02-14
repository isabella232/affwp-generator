<?php
/**
 * Integration Abstract
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */


namespace Affiliate_WP_Generator\Abstracts;


use DateTime;

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
	protected $integration;

	/**
	 * Places an order. Does not simulate a referral visit.
	 *
	 * @since 1.0.0
	 *
	 * @param int             $user     The user ID to use as the customer.
	 * @param array           $products List of product IDs to use in the order.
	 * @param \DateTime|false $date     The date to place this order.
	 *
	 * @return int The order ID.
	 */
	abstract public function place_order( $user, $products, $date = false );

	/**
	 * Adds a product from the integration.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The product name.
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
		add_filter( 'affwp_get_referring_affiliate_id', function() use ( $affiliate ) {
			return $affiliate;
		} );

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
	 * @param int             $user      The user ID to use as the customer.
	 * @param int             $affiliate The affiliate ID to use for the referral.
	 * @param array           $products  List of product IDs to use in the order.
	 * @param DateTime|false $date The date to set the order, and referral.
	 *
	 * @return int The order ID.
	 */
	public function place_referred_order( $user, $affiliate, $products, $date = false ) {
		$this->simulate_visit( $affiliate );

		$payment_id = $this->place_order( $user, $products );

		if ( false !== $date ) {
			$this->set_referral_date( $payment_id, $date );
		}

		return $payment_id;
	}

	/**
	 * Updates the referral to the specified date.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id The order ID
	 * @param DateTime $date The date to set the referral
	 * @return bool
	 */
	public function set_referral_date( $order_id, $date ) {
		$referral_id = affiliate_wp()->referrals->get_by( 'reference', $order_id );

		return affiliate_wp()->referrals->update_referral( $referral_id, array(
			'date' => $date->format('Y-m-d H:i:s'),
		) );
	}
}