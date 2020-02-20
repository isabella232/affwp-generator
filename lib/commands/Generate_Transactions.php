<?php
/**
 * Generate Transactions CLI Command
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Commands
 */


namespace Affiliate_WP_Generator\Commands;


use Affiliate_WP_Generator\Abstracts\Command;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Generate_Transactions extends Command {


	/**
	 * Generates transactions.
	 *
	 * ## OPTIONS
	 *
	 * <integration>
	 * : The name of the integration
	 * ---
	 * options:
	 *   - edd
	 *   - woocommerce
	 *   - rcp
	 *
	 * [--number=<number>]
	 * : How many transactions to generate.
	 * ---
	 * default: 10
	 *
	 * [--users=<number>]
	 * : Number of users to generate. 1 user is randomly selected as a customer for each transaction.
	 * ---
	 * default: 20
	 *
	 * [--affiliates=<number>]
	 * : Number of affiliates to generate. 1 Affiliate is selected at random for each transaction.
	 * ---
	 * default: 5
	 *
	 * [--products=<number>]
	 * : Number of products to generate. Products are randomly selected for each transaction.
	 * ---
	 * default: 10
	 *
	 * [--max-products=<number>]
	 * : Maximum number of products per transaction.
	 * ---
	 * default: 4
	 *
	 * [--min-products=<number>]
	 * : Minimum number of products per transaction.
	 * ---
	 * default: 1
	 *
	 * [--earliest-date=<string>]
	 * : The earliest possible transaction date for a transaction. Defaults to last month.
	 * ---
	 * default: last month
	 *
	 * [--latest-date=<string>]
	 * : The latest possible transaction date. Defaults to today.
	 * ---
	 * default: today
	 *
	 * [--format=<string>]
	 * : Return Format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - yaml
	 *   - json
	 *   - csv
	 *   - ids
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate 50 random transactions for Easy Digital Downloads.
	 *     wp affwp generate transactions edd --number=50
	 */
	public function run( $args, $assoc_args ) {
		$format_fields    = array( 'id' );
		$number           = $assoc_args['number'] + $assoc_args['users'] +  $assoc_args['affiliates'] + $assoc_args['products'];
		$progress         = \WP_CLI\Utils\make_progress_bar( 'Generating transactions', $number );
		$integration_name = $args[0];

		$assoc_args['date_range'] = array(
			'earliest' => $assoc_args['earliest-date'],
			'latest'   => $assoc_args['latest-date'],
		);

		$assoc_args['products_per_transaction'] = array(
			'min' => $assoc_args['min-products'],
			'max' => $assoc_args['max-products'],
		);

		// Tick progress on each generated transaction
		add_action( 'affwp_generator_after_generated_transaction', function() use ( $progress ) {
			$progress->tick();
		} );

		// Tick progress on each generated transaction
		add_action( 'affwp_generator_after_generated_user', function() use ( $progress ) {
			$progress->tick();
		} );

		// Tick progress on each generated transaction
		add_action( 'affwp_generator_after_generated_product', function() use ( $progress ) {
			$progress->tick();
		} );

		// Tick progress on each generated transaction
		add_action( 'affwp_generator_after_generated_order', function() use ( $progress ) {
			$progress->tick();
		} );

		$transactions = affwp_generator()->generate()->transactions( $integration_name, $assoc_args );

		$progress->finish();

		// If something went wrong, bail, and respond with WP Error messages.
		if ( is_wp_error( $transactions ) ) {
			\WP_CLI::error( $this->process_error_message( $transactions ) );
		}

		// If the format is not IDs, retrieve the transaction data.
		if ( 'ids' !== $assoc_args['format'] ) {
			$integration = affwp_generator()->integration()->get( $integration_name );
			$result      = array_map( function( $transaction_id ) use ( $integration ) {
				return $integration->get_order( $transaction_id )->args;
			}, $transactions['orders'] );

			$format_fields = array_keys( $result[0] );

		} else {
			$result = $transactions;
		}

		$message = \WP_CLI\Utils\format_items( $assoc_args['format'], $result, $format_fields );

		\WP_CLI::line( $message );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_command_name() {
		return 'transactions';
	}
}