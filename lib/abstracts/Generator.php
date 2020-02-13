<?php
/**
 * Abstraction for Generators
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */


namespace Affiliate_WP_Generator\Abstracts;


use Faker\Factory as Faker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Generator
 *
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */
abstract class Generator {

	/**
	 * List of errors for this generator.
	 *
	 * @since 1.0.0
	 *
	 * @var \WP_Error
	 */
	protected $errors;

	/**
	 * Generator arguments.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Generates items for this class.
	 * This runs inside of the run method, and will only run if there are no errors. Arguments are sanitized.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of generated items, usually IDs.
	 */
	abstract protected function generate();

	/**
	 * Sanitizes and validates arguments. Intended to be extended by child classes if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments.
	 * @return array List of sanitized arguments.
	 */
	protected function process_args( $args ) {
		$defaults = array(
			'number' => 10,
		);
		$args     = wp_parse_args( $args, $defaults );

		// Add an error if the number argument is less than 1
		if ( $args['number'] < 1 ) {
			$this->errors->add(
				'invalid_customer_number_arg',
				'The number argument must be greater than 1 for customers.'
			);
		}

		return $args;
	}

	/**
	 * Generator constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 */
	public function __construct( $args = array() ) {
		$this->errors = new \WP_Error();
		$this->args   = $this->process_args( $args );

	}

	/**
	 * Generates items for this class, if no errors occurred during sanitization.
	 *
	 * @since 1.0.0
	 *
	 * @return array|\WP_Error List of generated items, usually IDs, or a WP_Error object.
	 */
	public function run() {
		// If something went wrong setting up this generator, log it and bail.
		if ( $this->errors->has_errors() ) {
			return affwp_generator()->logger()->log(
				'affwp_generator_error',
				'generator_has_errors',
				'A generator attempted to run, but failed because it had errors',
				__CLASS__,
				array( 'args' => $this->args, 'errors' => $this->errors )
			);
		}

		return $this->generate();
	}

}