<?php
/**
 * EDD Integration
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Integrations
 */


namespace Affiliate_WP_Generator\Factories\Integrations;


use Affiliate_WP_Generator\Abstracts\Integration;
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
class EDD extends Integration {

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
	public function place_order( $user, $downloads, $date = false ) {

		$total           = 0;
		$user            = get_userdata( $user );
		$user_meta       = get_user_meta( $user->ID );
		$final_downloads = $cart_details = array();


		foreach ( $downloads as $key => $download ) {
			$options  = array();
			$download = new EDD_Download( $download );

			// Set Price
			if ( edd_has_variable_prices( $download->ID ) ) {

				$prices        = edd_get_variable_prices( $download->ID );
				$item_price_id = array_rand( $prices );

				$item_price          = $prices[ $item_price_id ]['amount'];
				$options['price_id'] = $item_price_id;

			} else {
				$item_price = edd_get_download_price( $download->ID );
			}

			$item_number = array(
				'id'       => $download->ID,
				'quantity' => 1,
				'options'  => $options,
			);

			$cart_details[ $key ] = array(
				'name'        => $download->post_title,
				'id'          => $download->ID,
				'item_number' => $item_number,
				'item_price'  => edd_sanitize_amount( $item_price ),
				'subtotal'    => edd_sanitize_amount( $item_price ),
				'price'       => edd_sanitize_amount( $item_price ),
				'quantity'    => 1,
				'discount'    => 0,
				'tax'         => edd_calculate_tax( $item_price ),
			);

			$final_downloads[ $key ] = $item_number;

			$total += $item_price;
		}

		$purchase_data = array(
			'price'        => edd_sanitize_amount( $total ),
			'tax'          => edd_calculate_tax( $total ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email'   => $user->data->user_email,
			'user_info'    => array(
				'id'         => $user->ID,
				'email'      => $user->data->user_email,
				'first_name' => $user_meta['first_name'][0],
				'last_name'  => $user_meta['last_name'][0],
				'discount'   => 'none',
			),
			'currency'     => edd_get_currency(),
			'downloads'    => $final_downloads,
			'cart_details' => $cart_details,
			'status'       => 'pending',
		);

		if ( $date instanceof \DateTime ) {
			$purchase_data['date'] = $date->format( 'Y-m-d G:i:s' );
		}

		$payment_id = edd_insert_payment( $purchase_data );
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );
		edd_update_payment_status( $payment_id, 'complete' );

		return $payment_id;
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

		$download = new EDD_Download();

		$download->create( array(
			'post_title'  => $name,
			'post_status' => 'publish',
			'meta_input'  => array(
				'edd_price' => $price,
			),
		) );

		return $download->ID;
	}

}