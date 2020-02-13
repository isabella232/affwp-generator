<?php
/**
 * User Generator
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
 * Class User
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Factories\Generators
 */
class User extends Generator {

	/**
	 * Generates users.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of generated user IDs.
	 */
	protected function generate() {
		$results = array();

		// Loop through, and create users.
		for ( $i = 0; $i < $this->args['number']; $i++ ) {

			$user_id = wp_insert_user( array(
					'user_login' => affwp_generator()->random()->user_name(),
					'user_pass'  => affwp_generator()->random()->password(),
					'user_email' => affwp_generator()->random()->email(),
					'first_name' => affwp_generator()->random()->first_name(),
					'last_name'  => affwp_generator()->random()->last_name(),
				)
			);

			if ( ! is_wp_error( $user_id ) ) {
				$results[] = $user_id;
			}
		}

		// Log the generate event
		affwp_generator()->logger()->log(
			'affwp_generator_generator_event',
			'users_generated',
			'The user generator created ' . count( $results ) . ' users.',
			'',
			array( 'users' => $results, 'args' => $this->args )
		);

		// Return the array of generated user IDs.
		return $results;
	}
}