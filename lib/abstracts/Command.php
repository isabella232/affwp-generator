<?php
/**
 * WP CLI Command Abstraction
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */


namespace Affiliate_WP_Generator\Abstracts;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Command
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */
abstract class Command {

	/**
	 * List of registered commands.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private static $commands = array();

	/**
	 * Retrieves the command registration name
	 *
	 * @return string The name of the command.
	 */
	abstract protected function get_command_name();

	/**
	 * Retrieves the command registration arguments.
	 *
	 * @return array The arguments used when registering the command.
	 */
	protected function get_command_args() {
		return array();
	}

	/**
	 * Callback function for the specified command.
	 *
	 * @param array $args       Non-associative CLI arguments
	 * @param array $assoc_args Associative CLI arguments.
	 * @return mixed The command result.
	 */
	abstract public function run( $args, $assoc_args );


	public function process_error_message( \WP_Error $error, $include_heading = true ) {

		$message = '';

		if ( true === $include_heading ) {
			$message = "Errors were found: \n";
		}

		foreach ( $error->get_error_messages() as $error_message ) {
			$message .= $error_message . "\n";
		}

		$error_data = $error->get_error_data();

		if ( is_wp_error( $error_data['errors'] ) ) {
			$message .= $this->process_error_message( $error_data['errors'], false );
		} else {

			foreach ( $error_data['errors'] as $error_messages ) {
				foreach ( $error_messages as $error_message ) {
					$message .= " - " . $error_message[0] . "\n";
				}
			}
		}

		return $message;
	}

	/**
	 * Generates an inactive plugin message, if the generator has one.
	 *
	 * @since 1.0.0
	 */
	public function generator_inactive_message() {
		if ( is_wp_error( affwp_generator() ) ) {
			\WP_CLI::error( $this->process_error_message( affwp_generator() ) );
		}
	}

	/**
	 * Command constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Bail if WP CLI is not set up.
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		$name = $this->get_command_name();

		if ( ! in_array( $name, self::$commands ) ) {
			$name = AFFWP_GENERATOR_CLI_BASE . ' ' . $name;

			// If something went wrong when setting up the generator, retrieve the error instead.
			if ( is_wp_error( affwp_generator() ) ) {
				\WP_CLI::add_command( $name, array( $this, 'generator_inactive_message' ) );

				// Otherwise, set up the default command.
			} else {
				\WP_CLI::add_command( $name, array( $this, 'run' ), $this->get_command_args() );
			}
			self::$commands[] = $name;
		}
	}

}