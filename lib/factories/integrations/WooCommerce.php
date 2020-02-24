<?php
/**
 * EDD Integration
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Integrations
 */


namespace Affiliate_WP_Generator\Factories\Integrations;


use Affiliate_WP_Generator\Abstracts\Integration;
use Affiliate_WP_Generator\Factories\Order;
use Affiliate_WP_Generator\Factories\Product;
use EDD_Download;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDD
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Integrations
 */
class WooCommerce extends Integration {

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
	public function place_order( $user, $products, $date = false ) {

		$order = wc_create_order(
			array(
				'customer_id' => $user,
				'status'      => 'completed',
			),
		);

		$user = get_userdata( $user );

		// Add products
		foreach ( $products as $product ) {
			$order->add_product( wc_get_product( $product ) );
		}

		// Set the address
		$user_meta = get_user_meta( $user->ID );
		$address   = affwp_generator()->random()->address();

		$order->set_address( array(
			'first_name' => $user_meta['first_name'][0],
			'last_name'  => $user_meta['last_name'][0],
			'email'      => $user->data->user_email,
			'address_1'  => $address['street'],
			'city'       => $address['city'],
			'state'      => $address['state_abbreviation'],
			'postcode'   => $address['postcode'],
		), 'billing' );

		$order->calculate_totals();


		// Set created date.
		if ( $date instanceof \DateTime ) {
			$order->set_date_created( $date->format( 'Y-m-d H:i:s' ) );
		}

		$order_id = $order->save();

		do_action( 'woocommerce_checkout_update_order_meta', $order_id );

		return $order_id;
	}

	/**
	 * Adds a product from the integration.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The product name.
	 * @param number $price The product price.
	 * @return int|\WP_Error The generated product ID or a WP_Error object if the integration failed to setup.
	 */
	public function add_product( $name, $price ) {
		if ( ! $this->integration->plugin_is_active() ) {
			return new \WP_Error(
				'plugin_is_not_active',
				'The ' . $this->integration->get_name() . ' plugin is not active.'
			);
		}

		$product = array(
			'post_status' => "publish",
			'post_title'  => $name,
			'post_parent' => '',
			'post_type'   => "product",
			'meta_input'  => array(
				'_downloadable'  => 'no',
				'_virtual'       => 'no',
				'_manage_stock'  => 'no',
				'_price'         => $price, //price
				'_regular_price' => $price, //price
				'_featured'      => false,
			),
		);

		return wp_insert_post( $product );
	}

	/**
	 * @inheritDoc
	 */
	public function get_product( $product_id ) {
		$product = wc_get_product( $product_id );

		return new Product(
			array(
				'id'      => $product_id,
				'name'    => $product->get_name(),
				'price'   => $product->get_price(),
				'status'  => $product->get_status(),
				'created' => $product->get_date_created()->format( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_order( $order_id ) {
		$order = wc_get_order( $order_id );

		return new Order(
			array(
				'id'       => $order_id,
				'customer' => $order->get_customer_id(),
				'total'    => $order->get_total(),
				'status'   => $order->get_status(),
				'date'     => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
			)
		);
	}
}