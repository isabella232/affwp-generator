<?php
/**
 * Transaction Generator
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
 * Class Transaction
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */
class Transaction extends Integration_Generator {

	/**
	 * Generates transactions.
	 *
	 * @since 1.0.0
	 *
	 * @return array|\WP_Error List of generated IDs, keyed by their type.
	 */
	protected function generate() {

		// Generate Customers
		$users = affwp_generator()->generate()->users( $this->args['users'] );

		// Generate Affiliates
		$affiliates = affwp_generator()->generate()->affiliates( $this->args['affiliates'] );

		// Generate Products
		$products = affwp_generator()->generate()->products( $this->integration, $this->args['products'] );

		// Bail if something went wrong.
		if ( is_wp_error( $users ) || is_wp_error( $affiliates ) || is_wp_error( $products ) ) {
			// Log the generate event
			return affwp_generator()->logger()->log(
				'affwp_generator_error',
				'transactions_generation_failed',
				'The transactions generator failed. One or more generators have errors.',
				'',
				compact( $users, $affiliates, $products )
			);
		}

		// Generate Orders
		$orders = affwp_generator()->generate()->orders( $this->integration, array(
			'number'                   => $this->args['number'],
			'users'                    => $users,
			'affiliates'               => $affiliates,
			'products'                 => $products,
			'products_per_transaction' => $this->args['products_per_transaction'],
			'date_range'               => array(
				'earliest' => $this->args['date_range']['earliest'],
				'latest'   => $this->args['date_range']['latest'],
			),
		) );

		$result = compact( 'users', 'affiliates', 'products', 'orders' );

		// Log an error if orders did not generate.
		if ( is_wp_error( $orders ) ) {
			// Log the generate event
			$result = affwp_generator()->logger()->log(
				'affwp_generator_error',
				'transactions_generate_order_failed',
				'The transactions generator failed. Orders were not created.',
				'',
				$result
			);
		} else {
			// Log the successful generate event
			affwp_generator()->logger()->log(
				'affwp_generator_generator_event',
				'transactions_generated',
				sprintf( "The transactions generator created %s transactions.", count( $orders ) ),
				'',
				$result
			);
		}

		return $result;
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
			'users'                    => 20,
			'affiliates'               => 5,
			'products'                 => 10,
			'products_per_transaction' => array(
				'max' => 4,
				'min' => 1,
			),
			'date_range'               => array(
				'earliest' => 'last month',
				'latest'   => 'today',
			),
		);
		$args     = wp_parse_args( $args, $defaults );

		// Construct affiliates args
		if ( ! is_array( $args['affiliates'] ) ) {
			$args['affiliates'] = array(
				'number' => (int) $args['affiliates'],
			);
		}

		// Construct users args
		if ( ! is_array( $args['users'] ) ) {
			$args['users'] = array(
				'number' => (int) $args['users'],
			);
		}

		// Construct products args
		if ( ! is_array( $args['products'] ) ) {
			$args['products'] = array(
				'number' => (int) $args['products'],
			);
		}

		// Add an error if the number argument is less than 1
		if ( $args['number'] < 1 ) {
			$this->errors->add(
				'invalid_transaction_number_arg',
				'The number argument must be greater than 1 for transactions.'
			);
		}

		// Add an error if the users argument is less than 1
		if ( $args['users']['number'] < 1 ) {
			$this->errors->add(
				'invalid_transaction_users_arg',
				'The users argument must be greater than 1 for transactions.'
			);
		}

		// Add an error if the affiliates argument is less than 1
		if ( $args['affiliates']['number'] < 1 ) {
			$this->errors->add(
				'invalid_affiliates_users_arg',
				'The affiliates argument must be greater than 1 for transactions.'
			);
		}

		// Add an error if the products argument is less than 1
		if ( $args['products']['number'] < 1 ) {
			$this->errors->add(
				'invalid_transaction_products_arg',
				'The products argument must be greater than 1 for transactions.'
			);
		}

		// Construct products per transaction array
		if ( ! is_array( $args['products_per_transaction'] ) ) {
			$args['products_per_transaction'] = array(
				'max' => (int) $args['products_per_transaction'],
				'min' => (int) $args['products_per_transaction'],
			);
		}

		// Set date range, if provided
		if ( ! is_array( $args['date_range'] ) ) {
			$args['date_range'] = array(
				'earliest' => $args['date_range'],
				'latest'   => $args['date_range'],
			);
		}
		$args['date_range']               = wp_parse_args( $args['date_range'], $defaults['date_range'] );
		$args['products_per_transaction'] = wp_parse_args( $args['products_per_transaction'], $defaults['products_per_transaction'] );

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

		// Add an error if the products per transaction max argument is less than the max argument.
		if ( $args['products_per_transaction']['max'] < $args['products_per_transaction']['min'] ) {
			$this->errors->add(
				'products_per_transaction_max_is_smaller_than_min',
				'The products per transaction max argument must be greater than the min argument.'
			);
		}

		// Add an error if the products per transaction max argument is less than the max argument.
		if ( $args['products_per_transaction']['max'] > $args['products'] ) {
			$this->errors->add(
				'products_per_transaction_max_is_larger_than_products',
				'The products per transaction max argument cannot be greater than the products.'
			);
		}

		// Add an error if the products per transaction min argument is greater than the products.
		if ( $args['products_per_transaction']['min'] > $args['products'] ) {
			$this->errors->add(
				'products_per_transaction_min_is_larger_than_products',
				'The products per transaction min argument cannot be greater than the products.'
			);
		}

		// Validate date range values are valid
		if ( ! isset( $args['date_range']['earliest'] ) || ! isset( $args['date_range']['latest'] ) ) {
			$this->errors->add(
				'malformed_date_range_arg',
				'The date range arg must either be a single date value, or an array containing earliest and latest values.'
			);
		}

		$earliest_date = $latest_date = 0;

		// Test to ensure earliest date is valid
		try{
			$earliest_date = new \DateTime( $args['date_range']['earliest'] );
			$earliest_date = $earliest_date->getTimestamp();
		}catch( \Exception $e ){
			$this->errors->add(
				'malformed_earliest_date_range_arg',
				'The earliest date range arg failed to create a valid DateTime object.',
				array( 'error_info' => $e )
			);
		}

		// Test to ensure latest date is valid
		try{
			$latest_date = new \DateTime( $args['date_range']['latest'] );
			$latest_date = $latest_date->getTimestamp();
		}catch( \Exception $e ){
			$this->errors->add(
				'malformed_latest_date_range_arg',
				'The latest date range arg failed to create a valid DateTime object.',
				array( 'error_info' => $e )
			);
		}

		if ( $earliest_date > $latest_date ) {
			$this->errors->add(
				'earliest_date_newer_than_latest_date',
				'The latest date arg must be newer than the earliest date.'
			);
		}

		return $args;
	}
}