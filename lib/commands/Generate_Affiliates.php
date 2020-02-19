<?php
/**
 * Generate Affiliates CLI Command
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Commands
 */


namespace Affiliate_WP_Generator\Commands;


use Affiliate_WP_Generator\Abstracts\Command;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Generate_Affiliates extends Command {

	/**
	 * Generates affiliates.
	 *
	 * ## OPTIONS
	 *
	 * [--number=<number>]
	 * : How many affiliates to generate.
	 * ---
	 * default: 10
	 *
	 * [--status=<string>]
	 * : The affiliate status. Defaults to a random valid status.
	 *
	 * [--date_registered=<string>]
	 * : A date to use for affiliates. Defaults to today's date.
	 *
	 * [--rate=<number>]
	 * : Affiliate-specific referral rate. Default random rate between 1-100.
	 *
	 * [--rate_type=<string>]
	 * : Affiliate-specific rate type. Defaults to a random valid rate type.
	 *
	 * [--payment_email=<string>]
	 * : Payment email for affiliates. Defaults to a random fake email.
	 *
	 * [--earnings=<number>]
	 * : Affiliate earnings. Default 0.
	 *
	 * [--referrals=<number>]
	 * : Number of affiliate referrals. Default 0.
	 *
	 * [--visits=<number>]
	 * : Number of affiliate visits. Default 0.
	 *
	 * [--website_url=<string>]
	 *  : The affiliate's website URL. Is not set by default.
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
	 *     # Generate 50 random affiliates
	 *     wp affwp generate affiliates --number=50
	 */
	public function run( $args, $assoc_args ) {

		$format_fields = array( 'id' );
		$progress      = \WP_CLI\Utils\make_progress_bar( 'Generating affiliates', $assoc_args['number'] );

		// Tick progress on each generated affiliate
		add_action( 'affwp_generator_after_generated_affiliate', function() use ( $progress ) {
			$progress->tick();
		} );

		$affiliates = affwp_generator()->generate()->affiliates( $assoc_args );

		$progress->finish();

		// If something went wrong, bail, and respond with WP Error messages.
		if ( is_wp_error( $affiliates ) ) {
			\WP_CLI::error( $this->process_error_message( $affiliates ) );
		}

		// If the format is not IDs, retrieve the affiliate data.
		if ( 'ids' !== $assoc_args['format'] ) {
			$result = array_map( function( $affiliate_id ) {
				$affiliate = affwp_get_affiliate( $affiliate_id );
				$user      = get_user_by( 'id', $affiliate->user_id );

				return array(
					'name'      => $user->data->display_name,
					'login'     => $user->data->user_login,
					'user_id'   => $user->data->ID,
					'rate'      => $affiliate->rate,
					'rate_type' => $affiliate->rate_type,
					'status'    => $affiliate->status,
				);
			}, $affiliates );

			$format_fields = array_keys( $result[0] );

		} else {
			$result = $affiliates;
		}

		$message = \WP_CLI\Utils\format_items( $assoc_args['format'], $result, $format_fields );

		\WP_CLI::success( $message );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_command_name() {
		return 'affiliates';
	}
}