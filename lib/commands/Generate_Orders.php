<?php
/**
 * Generate Orders CLI Command
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Commands
 */


namespace Affiliate_WP_Generator\Commands;


use Affiliate_WP_Generator\Abstracts\Command;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Generate_Orders extends Command {


	/**
	 * Generates orders.
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
	 * : How many orders to generate.
	 * ---
	 * default: 10
	 *
	 * [--users]
	 * : List of user IDs. 1 user is randomly selected as a customer for each transaction.
	 *
	 * [--affiliates]
	 * : List of affiliate IDs. 1 Affiliate is selected at random for each transaction.
	 *
	 * [--products]
	 * : List of product IDs. Products are randomly selected for each transaction.
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
	 * : The earliest possible order date for a transaction. Defaults to last month.
	 * ---
	 * default: last month
	 *
	 * [--latest-date=<string>]
	 * : The latest possible order date. Defaults to today.
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
	 *     # Generate 50 orders for Easy Digital Downloads, using a list of affiliates, users, and hard-coded products.
	 *     wp affwp generate orders edd --affiliates="$(wp affwp affiliate list --status=active --format=ids)" --products="507 508 509 510" --users="$(wp user list --format=ids --number=10)" --number=100
	 */
	public function run( $args, $assoc_args ) {
		$format_fields    = array( 'id' );
		$progress         = \WP_CLI\Utils\make_progress_bar( 'Generating orders', $assoc_args['number'] );
		$integration_name = $args[0];

		$assoc_args['products']   = explode( ' ', $assoc_args['products'] );
		$assoc_args['affiliates'] = explode( ' ', $assoc_args['affiliates'] );
		$assoc_args['users']      = explode( ' ', $assoc_args['users'] );

		$assoc_args['date_range'] = array(
			'earliest' => $assoc_args['earliest-date'],
			'latest'   => $assoc_args['latest-date'],
		);

		$assoc_args['products_per_transaction'] = array(
			'min' => $assoc_args['min-products'],
			'max' => $assoc_args['max-products'],
		);

		// Tick progress on each generated order
		add_action( 'affwp_generator_after_generated_order', function() use ( $progress ) {
			$progress->tick();
		} );

		$orders = affwp_generator()->generate()->orders( $integration_name, $assoc_args );

		$progress->finish();

		// If something went wrong, bail, and respond with WP Error messages.
		if ( is_wp_error( $orders ) ) {
			\WP_CLI::error( $this->process_error_message( $orders ) );
		}

		// If the format is not IDs, retrieve the order data.
		if ( 'ids' !== $assoc_args['format'] ) {
			$integration = affwp_generator()->integration()->get( $integration_name );
			$result      = array_map( function( $order_id ) use ( $integration ) {
				return $integration->get_order( $order_id )->args;
			}, $orders );

			$format_fields = array_keys( $result[0] );

		} else {
			$result = $orders;
		}

		$message = \WP_CLI\Utils\format_items( $assoc_args['format'], $result, $format_fields );

		\WP_CLI::line( $message );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_command_name() {
		return 'orders';
	}
}