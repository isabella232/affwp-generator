<?php
/**
 * Integration Abstract
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */


namespace Affiliate_WP_Generator\Abstracts;


use Affiliate_WP_Generator\Factories\Order;
use Affiliate_WP_Generator\Factories\Product;
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
	 * Retrieves the product record.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id The product ID
	 * @return Product Individual product information.
	 */
	abstract public function get_product( $product_id );

	/**
	 * Retrieves the order record.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id The order ID
	 * @return Order Individual order information.
	 */
	abstract public function get_order( $order_id );

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
	public function simulate_visit( $affiliate, $campaign = '' ) {
		// Set affiliate
		add_filter( 'affwp_get_referring_affiliate_id', function () use ( $affiliate ) {
			return $affiliate;
		} );

		// Store visit in the database.
		$visit_id = affiliate_wp()->visits->add( array(
				'context'      => $this->integration->context,
				'affiliate_id' => $affiliate,
				'campaign'     => $campaign
			)
		);

		// Set the visit and affiliate cookies. This allows integrations to handle setting referrals.
		$_COOKIE[ affiliate_wp()->tracking->get_cookie_name( 'visit' ) ] = $visit_id;
		$_COOKIE[ affiliate_wp()->tracking->get_cookie_name() ]                    = $affiliate;
	}

	/**
	 * Places an order with a referral.
	 *
	 * @param int            $user      The user ID to use as the customer.
	 * @param int            $affiliate The affiliate ID to use for the referral.
	 * @param array          $products  List of product IDs to use in the order.
	 * @param string         $campaign  The campaign. Default empty.
	 * @param DateTime|false $date      The date to set the order, and referral.
	 *
	 * @return int The order ID.
	 */
	public function place_referred_order( $user, $affiliate, $products, $campaign = '', $date = false ) {
		$this->simulate_visit( $affiliate, $campaign );

		$payment_id = $this->place_order( $user, $products, $date );

		if ( false !== $date ) {
			$this->set_referral_date( $payment_id );
		}

		return $payment_id;
	}

	/**
	 * Updates the referral to the specified date.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id The order ID
	 * @return bool
	 */
	public function set_referral_date( $order_id ) {
		$referral = affiliate_wp()->referrals->get_by( 'reference', $order_id );

		if ( ! $referral ) {
			return false;
		}

		$order = $this->get_order( $order_id );

		$referral_id    = $referral->referral_id;
		$referral->date = date( 'Y-m-d H:i:s', strtotime( $order->args['date'] ) );
		$args           = (array) $referral;
		unset( $args['referral_id'] );

		return affiliate_wp()->referrals->update_referral( $referral_id, $args );
	}
}