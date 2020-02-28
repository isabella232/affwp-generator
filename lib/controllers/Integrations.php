<?php
/**
 * Integrations Class
 * Provides a way to retrieve integrations
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Controllers
 */

namespace Affiliate_WP_Generator\Controllers;

use Affiliate_WP_Generator\Abstracts\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Integrations
 *
 * @since   1.0.0
 * @package affwp_generator\controllers
 */
class Integrations {

	/**
	 * Integrations
	 * List of supported generator integrations.
	 *
	 * @since 1.0.0
	 *
	 * @var array List of supported integration classes keyed by the integration name.
	 */
	protected $integrations = array(
		'edd'         => 'EDD',
		'rcp'         => 'RCP',
		'woocommerce' => 'WooCommerce',
	);

	/**
	 * Retrieves the specified integration class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $integration The integration name.
	 * @return Integration|\WP_Error Integration class if valid. WP_Error otherwise.
	 */
	public function get( $integration ) {

		// if the provided integration is already an integration instance, just return it.
		if ( $integration instanceof Integration ) {
			return $integration;
		}

		$errors = new \WP_Error();

		if ( false === $this->is_supported( $integration ) ) {
			$errors->add(
				'invalid_integration',
				'The specified integration was not set because the integration is not supported.',
				array( 'integration' => $integration, 'supported_integrations' => array_keys( $this->integrations ) )
			);
		}

		$integration_class      = 'Affiliate_WP_Generator\Factories\Integrations\\' . $this->integrations[ $integration ];
		$core_integration_class = affiliate_wp()->integrations->get( $integration );

		// Bubble up error if integration is not active or invalid
		if ( is_wp_error( $core_integration_class ) ) {
			return $core_integration_class;
		}

		if ( ! $core_integration_class->plugin_is_active() ) {
			$errors->add(
				'inactive_integration_plugin',
				'The specified integration cannot be used because the integration\'s plugin is inactive.',
				array( 'integration' => $integration )
			);
		}

		if ( ! $core_integration_class->is_active() ) {
			$errors->add(
				'disabled_integration',
				'The specified integration cannot be used because the integration is not enabled in AffiliateWP.',
				array( 'integration' => $integration )
			);
		}

		// Return all errors, if we have any.
		if ( $errors->has_errors() ) {
			return $errors;
		}

		return new $integration_class( $core_integration_class );
	}

	/**
	 * Returns true if the provided integration is supported by this generator.
	 *
	 * @since 1.0.0
	 *
	 * @param string $integration The integration name.
	 * @return bool True if the integration is supported, otherwise false.
	 */
	public function is_supported( $integration ) {
		return isset( $this->integrations[ $integration ] );
	}

}