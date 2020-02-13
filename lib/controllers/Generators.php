<?php
/**
 * Generator Class
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Controllers
 */


namespace Affiliate_WP_Generator\Controllers;


use Affiliate_WP_Generator\Factories\Generators\Transaction;
use Affiliate_WP_Generator\Factories\Generators\User;
use Affiliate_WP_Generator\Factories\Generators\Affiliate;
use Affiliate_WP_Generator\Factories\Generators\Product;
use Affiliate_WP_Generator\Factories\Generators\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Generator
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Controllers
 */
class Generators {

	/**
	 * Generates entire transactions, including users, affiliates, products, and orders.
	 *
	 * @since 1.0.0
	 *
	 * @param string $integration The name of the integration.
	 * @param array $args {
	 *     List of arguments to run this generator.
	 *     @type int   $number     Optional. The number of transactions to generate. Default 100
	 *     @type int   $users      Optional. Number of users to generate. 1 user is randomly selected as a customer
	 *                             for each transaction. Default 20.
	 *     @type int   $affiliates Optional. Number of affiliates to generate. 1 Affiliate is selected at random
	 *                             for each transaction. Default 5.
	 *     @type int   $products   Optional. Number of products to generate. Products are randomly selected
	 *                             for each transaction. Default 10.
	 *     @type array $products_per_transaction {
	 *         Optional. The min/max number of items to use in each transaction.
	 *         @type int $min The minimum value. Default 1.
	 *         @type int $max The maximum value. Default 4.
	 *     }
	 * }
	 * @return array|\WP_Error Array of generated IDs, keyed by the type, or a WP_Error object.
	 */
	public function transactions( $integration, $args = array() ) {
		$generator = new Transaction( $integration, $args );

		return $generator->run();
	}

	/**
	 * Generates users.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     List of arguments to run this generator.
	 *     @type int $number Optional. The number of users to generate. Default 10.
	 * }
	 * @return array|\WP_Error Array of generated user IDs, or a WP_Error object.
	 */
	public function users( $args = array() ) {
		$generator = new User( $args );

		return $generator->run();
	}

	/**
	 * Generates affiliates.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     List of arguments to run this generator.
	 *     @type int    $number          Optional. The number of affiliates to generate. Default 10.
	 *     @type string $status          Optional. The affiliate status. Defaults to a random valid status.
	 *     @type string $date_registered Optional. A date to use for affiliates. Defaults to today's date.
	 *     @type string $rate            Optional. Affiliate-specific referral rate. Default random rate between 1-100.
	 *     @type string $rate_type       Optional. Affiliate-specific rate type. Defaults to a random valid rate type.
	 *     @type string $payment_email   Optional. Payment email for affiliates. Defaults to a random fake email.
	 *     @type int    $earnings        Optional. Affiliate earnings. Default 0.
	 *     @type int    $referrals       Optional. Number of affiliate referrals. Default 0.
	 *     @type int    $visits          Optional. Number of affiliate visits. Default 0.
	 *     @type string $website_url     Optional. The affiliate's website URL. Is not set by default.
	 * }
	 * @return array|\WP_Error Array of generated affiliate IDs, or a WP_Error object.
	 */
	public function affiliates( $args = array() ) {
		$generator = new Affiliate( $args );

		return $generator->run();
	}

	/**
	 * Generates products.
	 *
	 * @since 1.0.0
	 *
	 * @param string $integration The name of the integration.
	 * @param array $args {
	 *     List of arguments to run this generator.
	 *     @type int   $number     Optional. The number of products to generate. Default 10.
	 *     @type int   $min_price  Optional. Minimum Price for generated products. Default 0.
	 *     @type int   $max_price  Optional. Minimum Price for generated products. Default 100.
	 * }
	 * @return array|\WP_Error Array of generated product IDs. or a WP_Error object.
	 */
	public function products( $integration, $args = array() ) {
		$generator = new Product( $integration, $args );

		return $generator->run();
	}

	/**
	 * Generates orders.
	 *
	 * @since 1.0.0
	 *
	 * @param string $integration The name of the integration.
	 * @param array $args {
	 *     List of arguments to run this generator.
	 *     @type int   $number     Optional. The number of orders to generate. Default 100.
	 *     @type array $users      Array of user IDs. 1 user is randomly selected as a customer for each transaction.
	 *     @type array $affiliates Array of affiliate IDs. 1 Affiliate is selected at random for each transaction.
	 *     @type array $products   Array of product IDs. Products are randomly selected for each transaction.
	 *     @type array $products_per_transaction {
	 *         Optional. The min/max number of items to use in each transaction.
	 *         @type int $min The minimum value. Default 1.
	 *         @type int $max The maximum value. Default 4.
	 *     }
	 * }
	 * @return array|\WP_Error Array of generated order IDs, or a WP_Error object.
	 */
	public function orders( $integration, $args = array() ) {
		$generator = new Order( $integration, $args );

		return $generator->run();
	}

}