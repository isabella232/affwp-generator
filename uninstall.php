<?php
/**
 * Uninstall actions
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Destroy Error Log
if ( affwp_generator()->logger() instanceof Affiliate_WP_Generator\Utilities\Basic_Logger ) {
	affwp_generator()->logger()->wipe();
}