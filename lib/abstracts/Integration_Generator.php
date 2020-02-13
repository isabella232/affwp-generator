<?php
/**
 * Abstraction for Generators
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */


namespace Affiliate_WP_Generator\Abstracts;


use Affiliate_WP_Generator\Abstracts\Integration;

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
abstract class Integration_Generator extends Generator {
	/**
	 * @var Integration|\WP_Error
	 */
	protected $integration;

	/**
	 * Generator constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 */
	public function __construct( $integration, $args = array() ) {
		parent::__construct( $args );
		$this->integration = affwp_generator()->integration()->get( $integration );

		// Bubble error if integration is invalid.
		if ( is_wp_error( $integration ) ) {
			$this->errors->add(
				$this->integration->get_error_code(),
				$this->integration->get_error_message(),
				$this->integration->get_error_data()
			);
		}
	}

}