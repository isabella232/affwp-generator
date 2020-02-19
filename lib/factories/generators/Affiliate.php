<?php
/**
 * Affiliate Generator
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */


namespace Affiliate_WP_Generator\Factories\Generators;


use Affiliate_WP_Generator\Abstracts\Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Affiliate
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */
class Affiliate extends Generator {

	/**
	 * List of valid Affiliate arguments.
	 *
	 * @since 1.0.0
	 *
	 * @var array List of valid affiliate args.
	 */
	private $supported_args = array(
		'status',
		'date_registered',
		'rate',
		'rate_type',
		'payment_email',
		'earnings',
		'referrals',
		'visits',
		'website_url',
	);

	/**
	 * Generates affiliates
	 *
	 * @since 1.0.0
	 *
	 * @return array List of generated affiliate IDs.
	 */
	protected function generate() {
		$results  = array();
		$user_ids = affwp_generator()->generate()->users( array( 'number' => $this->args['number'] ) );

		// Extracts supported affiliate arguments for add affiliate
		$affiliate_args = array_intersect_key( $this->args, array_flip( $this->supported_args ) );

		// Loop through generated users, and create affiliates.
		foreach ( $user_ids as $user_id ) {
			// Set the user ID for this affiliate
			$affiliate_args['user_id'] = $user_id;

			// Set random values for unset params.
			$args = $this->generate_affiliate_args( $affiliate_args );

			// Create the affiliate.
			$affiliate_id = affiliate_wp()->affiliates->add( $args );

			if ( false !== $affiliate_id ) {
				$results[] = $affiliate_id;
			}

			do_action( 'affwp_generator_after_generated_affiliate', $affiliate_id, $results );
		}

		// Log the generate event
		affwp_generator()->logger()->log(
			'affwp_generator_event',
			'affiliates_generated',
			'The affiliate generator created ' . count( $results ) . ' affiliates.',
			'',
			array( 'affiliates' => $results, 'args' => $this->args, 'user_ids' => $user_ids )
		);

		// Return the array of generated affiliate IDs.
		return $results;
	}

	/**
	 * Fills-in necessary unset arguments with random values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of args to create affiliate.
	 * @return array Arguments with random fill-in values where a value wasn't already specified.
	 */
	public function generate_affiliate_args( $args ) {

		// Set a random status.
		if ( ! isset( $args['status'] ) ) {
			$args['status'] = affwp_generator()->random()->affiliate_status();
		}

		// Set random rate
		if ( ! isset( $args['rate'] ) ) {
			$args['rate'] = affwp_generator()->random()->number( 1, 100 );
		}

		// Set random rate type
		if ( ! isset( $args['rate_type'] ) ) {
			$args['rate_type'] = affwp_generator()->random()->rate_type();
		}

		// Set random payment email
		if ( ! isset( $args['payment_email'] ) ) {
			$args['payment_email'] = affwp_generator()->random()->email();
		}

		return $args;
	}
}