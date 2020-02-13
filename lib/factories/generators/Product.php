<?php
/**
 * Product Generator
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
 * Class Product
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */
class Product extends Integration_Generator {

	/**
	 * Generates products.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of generated product IDs.
	 */
	protected function generate() {
		$results = array();

		// Loop through, and create products.
		for ( $i = 0; $i < $this->args['number']; $i++ ) {
			$product_id = $this->integration->add_product(
				affwp_generator()->random()->product_name(),
				affwp_generator()->random()->price( $this->args['min_price'], $this->args['max_price'] )
			);

			if ( ! is_wp_error( $product_id ) ) {
				$results[] = $product_id;
			}
		}

		// Log the generate event
		affwp_generator()->logger()->log(
			'affwp_generator_generator_event',
			'products_generated',
			sprintf( "The product generator created %s products.", count( $results ) ),
			'',
			array( 'products' => $results, 'args' => $this->args )
		);

		// Return the array of generated product IDs.
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
		$args              = parent::process_args( $args );
		$args['min_price'] = isset( $args['min_price'] ) ? $args['min_price'] : 0;
		$args['max_price'] = isset( $args['max_price'] ) ? $args['max_price'] : 100;

		if ( $args['max_price'] < 0 ) {
			$this->errors->add(
				'affwp_generator_max_price_invalid',
				'The provide maximum price is too low. The minimum possible price is zero.',
				array( 'max_price' => $args['max_price'] )
			);
		}

		if ( $args['min_price'] < 0 ) {
			$this->errors->add(
				'affwp_generator_min_price_invalid',
				'The provide minimum price is too low. The minimum possible price is zero.',
				array( 'min_price' => $args['min_price'] )
			);
		}

		return $args;
	}
}