<?php
/**
 * Generate Users CLI Command
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Commands
 */


namespace Affiliate_WP_Generator\Commands;


use Affiliate_WP_Generator\Abstracts\Command;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Generate_Users extends Command {

	/**
	 * @inheritDoc
	 */
	protected function get_command_name() {
		return 'users';
	}

	/**
	 * Generates users.
	 *
	 * ## OPTIONS
	 *
	 * [--number=<number>]
	 * : How many users to generate.
	 * ---
	 * default: 10
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
	 *     # Generate 50 random users
	 *     wp affwp generate users --number=50
	 */
	public function run( $args, $assoc_args ) {

		$format_fields = array( 'id' );
		$progress      = \WP_CLI\Utils\make_progress_bar( 'Generating users', $assoc_args['number'] );

		// Tick progress on each generated user
		add_action( 'affwp_generator_after_generated_user', function() use ( $progress ) {
			$progress->tick();
		} );

		$users = affwp_generator()->generate()->users( array( 'number' => $assoc_args['number'] ) );

		$progress->finish();

		// If something went wrong, bail, and respond with WP Error messages.
		if ( is_wp_error( $users ) ) {
			\WP_CLI::error( $this->process_error_message( $users ) );
		}

		// If the format is not IDs, retrieve the user data.
		if ( 'ids' !== $assoc_args['format'] ) {
			$result = array_map( function( $user_id ) {
				$user = get_user_by( 'id', $user_id );

				return array(
					'name'  => $user->data->display_name,
					'login' => $user->data->user_login,
					'ID'    => $user->data->ID,
				);
			}, $users );

			$format_fields = array_keys( $result[0] );

		} else {
			$result = $users;
		}

		$message = \WP_CLI\Utils\format_items( $assoc_args['format'], $result, $format_fields );

		\WP_CLI::success( $message );
	}
}