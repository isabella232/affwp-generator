<?php
/**
 * Generate Products CLI Command
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Commands
 */


namespace Affiliate_WP_Generator\Commands;


use Affiliate_WP_Generator\Abstracts\Command;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Generate_Products extends Command {


	/**
	 * Generates products.
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
	 * : How many products to generate.
	 * ---
	 * default: 10
	 *
	 * [--min_price=<number>]
	 * : minimum price to use for products.
	 * ---
	 * default: 0
	 *
	 * [--max_price=<number>]
	 * : maximum price to use for products.
	 * ---
	 * default: 100
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
	 *     # Generate 50 random products for Easy Digital Downloads.
	 *     wp affwp generate products edd --number=50
	 */
	public function run( $args, $assoc_args ) {
		$format_fields    = array( 'id' );
		$progress         = \WP_CLI\Utils\make_progress_bar( 'Generating products', $assoc_args['number'] );
		$integration_name = $args[0];

		// Tick progress on each generated product
		add_action( 'affwp_generator_after_generated_product', function() use ( $progress ) {
			$progress->tick();
		} );

		$products = affwp_generator()->generate()->products( $integration_name, $assoc_args );

		$progress->finish();

		// If something went wrong, bail, and respond with WP Error messages.
		if ( is_wp_error( $products ) ) {
			\WP_CLI::error( $this->process_error_message( $products ) );
		}

		// If the format is not IDs, retrieve the product data.
		if ( 'ids' !== $assoc_args['format'] ) {
			$integration = affwp_generator()->integration()->get( $integration_name );
			$result      = array_map( function( $product_id ) use ( $integration ) {
				return $integration->get_product( $product_id )->args;
			}, $products );

			$format_fields = array_keys( $result[0] );

		} else {
			$result = $products;
		}

		$message = \WP_CLI\Utils\format_items( $assoc_args['format'], $result, $format_fields );

		\WP_CLI::line( $message );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_command_name() {
		return 'products';
	}
}