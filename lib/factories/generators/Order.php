<?php
/**
 * Order Generator
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */


namespace Affiliate_WP_Generator\Factories\Generators;


use Affiliate_WP_Generator\Abstracts\Integration_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Order
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */
class Order extends Integration_Generator {

	/**
	 * Generates orders
	 *
	 * @since 1.0.0
	 *
	 * @return array List of generated order IDs.
	 */
	protected function generate() {
		$results = array();

		// Loop through, and create orders.
		for ( $i = 0; $i < $this->args['number']; $i++ ) {
			$order_args = $this->generate_order_args();

			if ( empty( $this->args['affiliates'] ) ) {
				$order_id = $this->integration->place_order(
					$order_args['users'],
					$order_args['products']
				);
			} else {
				$order_id = $this->integration->place_referred_order(
					$order_args['users'],
					$order_args['affiliates'],
					$order_args['products']
				);
			}

			if ( ! is_wp_error( $order_id ) ) {
				$results[] = $order_id;
			}
		}

		// Log the generate event
		affwp_generator()->logger()->log(
			'affwp_generator_generator_event',
			'order_generated',
			'The order generator created ' . count( $results ) . ' order.',
			'',
			array( 'users' => $results, 'args' => $this->args )
		);

		// Return the array of generated order IDs.
		return $results;
	}

	/**
	 * Sanitizes and validates arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments.
	 * @return array List of sanitized arguments.
	 */
	public function process_args( $args ) {
		$defaults = array(
			'number'                   => 100,
			'users'                    => array(),
			'affiliates'               => array(),
			'products'                 => array(),
			'products_per_transaction' => array(
				'max' => 4,
				'min' => 1,
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Construct affiliates args
		if ( ! is_array( $args['affiliates'] ) ) {
			$args['affiliates'] = array( $args['affiliates'] );
		}

		// Construct users args
		if ( ! is_array( $args['users'] ) ) {
			$args['users'] = array( $args['users'] );
		}

		// Construct products args
		if ( ! is_array( $args['products'] ) ) {
			$args['products'] = array( $args['products'] );
		}

		// Add an error if the number argument is less than 1
		if ( $args['number'] < 1 ) {
			$this->errors->add(
				'invalid_transaction_number_arg',
				'The number argument must be greater than 1 for transactions.'
			);
		}

		// Construct products per transaction array
		if ( ! is_array( $args['products_per_transaction'] ) ) {
			$args['products_per_transaction'] = array(
				'max' => (int) $args['products_per_transaction'],
				'min' => (int) $args['products_per_transaction'],
			);
		}

		// Validate products per transaction arguments are valid.
		if ( ! isset( $args['products_per_transaction']['max'] ) || ! isset( $args['products_per_transaction']['min'] ) ) {
			$this->errors->add(
				'malformed_products_per_transaction_arg',
				'The products per transaction must either be a single integer, or an array containing a max and min value.'
			);
		}

		// Add an error if the products per transaction max argument is less than 1.
		if ( $args['products_per_transaction']['max'] < 1 ) {
			$this->errors->add(
				'invalid_transaction_products_per_transaction_max_arg',
				'The products per transaction max argument must be greater than 1 for transactions.'
			);
		}

		// Add an error if the products per transaction min argument is less than 1.
		if ( $args['products_per_transaction']['min'] < 1 ) {
			$this->errors->add(
				'invalid_transaction_products_per_transaction_min_arg',
				'The products per transaction min argument must be greater than 1 for transactions.'
			);
		}

		// Add an error if the products per transaction max argument is less than the min argument.
		if ( $args['products_per_transaction']['max'] < $args['products_per_transaction']['min'] ) {
			$this->errors->add(
				'products_per_transaction_max_is_smaller_than_min',
				'The products per transaction max argument must be greater than the min argument.'
			);
		}

		return $args;
	}

	/**
	 * Fills-in necessary unset arguments with random values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of args to create affiliate.
	 * @return array Arguments with random fill-in values where a value wasn't already specified.
	 */
	public function generate_order_args() {
		$product_range = $this->args['products_per_transaction'];
		$product_count = affwp_generator()->random()->number( $product_range['min'], $product_range['max'] );
		$users         = affwp_generator()->random()->array_item( $this->args['users'] );
		$affiliates    = affwp_generator()->random()->array_item( $this->args['affiliates'] );
		$products      = affwp_generator()->random()->array_subset( $this->args['products'], $product_count );


		return compact( 'users', 'affiliates', 'products' );
	}
}