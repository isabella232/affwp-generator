<?php
/**
 * Code Coverage For Basic_Logger
 *
 * @package Affiliate_WP_Generator\Utilities
 */

/**
 * @covers Affiliate_WP_Generator\Utilities\Basic_Logger
 */
class Basic_Logger_Test extends WP_UnitTestCase {

	public function setUp() {
		// Clear the log
		affwp_generator()->logger()->wipe();
		affwp_generator()->logger()->reset_events();
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::log
	 */
	public function test_log_logs_the_error_if_type_is_valid() {
		affwp_generator()->logger()->log(
			'affwp_generator_api_event',
			'test_event',
			'Test Event',
			1,
			[ 'data_test' => 'data' ]
		);
		affwp_generator()->logger()->log(
			'affwp_generator_api_event',
			'test_event',
			'Test Event',
			1,
			[ 'data_test' => 'data' ]
		);

		$events = affwp_generator()->logger()->events();

		$this->assertCount( 2, $events['affwp_generator_api_event'] );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::log
	 */
	public function test_log_returns_wp_error_object() {
		$event = affwp_generator()->logger()->log( 'affwp_generator_api_event', '', '' );

		$this->assertInstanceOf( '\WP_Error', $event );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::file
	 */
	public function test_get_log_file_creates_log_file_path_if_type_is_valid() {
		affwp_generator()->logger()->file( 'affwp_generator_api_event' );

		$this->assertFileExists( affwp_generator()->logger()->path( 'affwp_generator_api_event' ) );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::file
	 */
	public function test_get_log_file_returns_log_file_path_if_type_is_valid() {
		$path = affwp_generator()->logger()->file( 'affwp_generator_api_event' );

		$this->assertSame( affwp_generator()->logger()->path( 'affwp_generator_api_event' ), $path );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::file
	 */
	public function test_get_log_file_returns_error_if_type_is_invalid() {
		$file = affwp_generator()->logger()->file( 'invalid_event_type' );

		$this->assertInstanceOf( '\WP_Error', $file );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::path
	 */
	public function test_get_log_path_returns_error_if_type_is_invalid() {
		$file = affwp_generator()->logger()->path( 'invalid_event_type' );

		$this->assertInstanceOf( '\WP_Error', $file );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::events
	 */
	public function test_events_returns_array_of_event_types() {
		affwp_generator()->logger()->log(
			'affwp_generator_api_event',
			'test_event',
			'Test Event',
			1,
			[ 'data_test' => 'data' ]
		);

		$events = array_keys( affwp_generator()->logger()->events() );

		$this->assertSame( $events, [ 'affwp_generator_error', 'affwp_generator_api_event' ] );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::reset_events
	 */
	public function test_reset_events_clears_request_events() {
		affwp_generator()->logger()->log( 'affwp_generator_api_event', 'test_event', 'Test Event' );
		affwp_generator()->logger()->log( 'affwp_generator_error', 'test_error', 'Test Event' );

		affwp_generator()->logger()->reset_events();
		$events = affwp_generator()->logger()->events();
		$test   = array_reduce( $events, 'array_merge', array() );

		$this->assertCount( 0, $test );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::log_events
	 */
	public function test_log_events_should_write_events_to_log_file() {
		affwp_generator()->logger()->log( 'affwp_generator_api_event', 'test_event', 'Test Event' );
		affwp_generator()->logger()->log( 'affwp_generator_error', 'test_error', 'Test Event' );

		affwp_generator()->logger()->log_events();

		$files = affwp_generator()->logger()->files();
		$test  = array(
			affwp_generator()->logger()->path( 'affwp_generator_api_event' ),
			affwp_generator()->logger()->path( 'affwp_generator_error' ),
		);

		$this->assertSame( $files, $test );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::clear_log
	 */
	public function test_clear_log_should_clear_log_files_for_event_type() {
		affwp_generator()->logger()->log( 'affwp_generator_api_event', 'test_event', 'Test Event' );

		affwp_generator()->logger()->log_events();
		affwp_generator()->logger()->clear( 'affwp_generator_api_event' );

		$this->assertFileNotExists( affwp_generator()->logger()->path( 'affwp_generator_api_event' ) );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::wipe_log
	 */
	public function test_wipe_should_clear_all_log_files() {
		affwp_generator()->logger()->log( 'affwp_generator_api_event', 'test_event', 'Test Event' );
		affwp_generator()->logger()->log( 'affwp_generator_error', 'test_error', 'Test Event' );

		affwp_generator()->logger()->log_events();
		affwp_generator()->logger()->wipe();

		$this->assertEmpty( affwp_generator()->logger()->files() );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::parse_file
	 */
	public function test_parse_file_should_return_log_file_info_with_path() {
		$path   = affwp_generator()->logger()->path( 'affwp_generator_api_event' );
		$parsed = affwp_generator()->logger()->parse_file( $path );
		$test   = array(
			'type' => 'affwp_generator_api_event',
			'date' => date( 'M-d-Y', strtotime( 'today' ) ),
			'path' => $path,
		);

		$this->assertSame( $parsed, $test );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::parse_file
	 */
	public function test_parse_file_should_return_log_file_info_with_file_name_only() {
		$date   = date( 'M-d-Y', strtotime( 'today' ) );
		$file   = 'affwp_generator-api-event-log__' . $date . '.log';
		$parsed = affwp_generator()->logger()->parse_file( $file );
		$path   = affwp_generator()->logger()->path( 'affwp_generator_api_event', $date );

		$test = array( 'type' => 'affwp_generator_api_event', 'date' => $date, 'path' => $path );

		$this->assertSame( $parsed, $test );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::parse_file
	 */
	public function test_parse_file_should_return_error_if_type_is_invalid() {
		$date   = date( 'M-d-Y', strtotime( 'today' ) );
		$parsed = affwp_generator()->logger()->parse_file( 'invalid-type__' . $date . '.log' );

		$this->assertInstanceOf( '\WP_Error', $parsed );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::parse_file
	 */
	public function test_parse_file_should_return_error_if_file_is_not_a_log() {
		$date   = date( 'M-d-Y', strtotime( 'today' ) );
		$parsed = affwp_generator()->logger()->parse_file( 'affwp_generator-api-event-log__' . $date );

		$this->assertInstanceOf( '\WP_Error', $parsed );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::parse_file
	 */
	public function test_parse_file_should_return_error_if_name_is_malformed() {
		$parsed = affwp_generator()->logger()->parse_file( 'invalid-type_and_such.log' );

		$this->assertInstanceOf( '\WP_Error', $parsed );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::purge
	 */
	public function test_purge_should_purge_files_older_than_specified_date() {
		// Write an old file of the specified type to the system.
		$test = array( affwp_generator()->logger()->file( 'affwp_generator_error', 'today - 2 days' ) );
		// Purge log
		$purged = affwp_generator()->logger()->purge( 1 );

		$this->assertSame( $test, $purged );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::purge
	 */
	public function test_purge_should_not_purge_files_newer_than_specified_date() {
		$test = array();
		// Write an old file of the specified type to the system.
		$test[] = affwp_generator()->logger()->file( 'affwp_generator_error', 'today - 2 days' );
		$test[] = affwp_generator()->logger()->file( 'affwp_generator_error', 'today - 3 days' );
		affwp_generator()->logger()->file( 'affwp_generator_error', 'yesterday' );

		// Purge log
		$purged = affwp_generator()->logger()->purge( 1 );

		$this->assertSame( asort( $test ), asort( $purged ) );
	}

	/**
	 * @covers \Affiliate_WP_Generator\Utilities\Basic_Logger::purge
	 */
	public function test_purge_should_return_wp_error_if_purge_is_negative_number() {
		$this->assertInstanceOf( '\WP_Error', affwp_generator()->logger()->purge( -1 ) );
	}
}