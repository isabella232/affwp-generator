<?php
/**
 * Cron Task Abstraction
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */

namespace Affiliate_WP_Generator\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cron_Task
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Abstracts
 */
abstract class Cron_Task {

	/**
	 * How often the cron task should recur. See wp_get_schedules() for accepted values.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $frequency = 'hourly';

	/**
	 * The name of this event.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $event;

	/**
	 * List of registered events.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $registered_events = array();

	/**
	 * The cron action that will fire on the scheduled time.
	 *
	 * @since 1.0.0
	 */
	abstract function cron_action();

	/**
	 * Cron_Task constructor.
	 *
	 * @param string $event     The name of this event.
	 * @param string $frequency How often the cron task should recur. See wp_get_schedules() for accepted values.
	 */
	public function __construct( $event, $frequency = 'hourly' ) {
		if ( ! isset( self::$registered_events[ $this->event ] ) ) {
			$this->event     = 'affwp_generator\sessions\\' . $event;
			$this->frequency = $frequency;

			// Registers this cron job to activate when the plugin is activated.
			register_activation_hook( AFFWP_GENERATOR_ROOT_FILE, [ $this, 'activate' ] );

			// Registers the action that fires when the cron job runs
			add_action( $this->event, [ $this, 'cron_action' ] );

			// Adds the job to the registry.
			self::$registered_events[ $this->event ] = $this->frequency;
		} else {
			affwp_generator()->logger()->log(
				'',
				'',
				__( 'A cron event was not registered because an event of the same name has already been registered.' ),
				'',
				array( 'event' => $event, 'frequency' => $frequency )
			);
		}
	}

	/**
	 * Activates the cron task on plugin activation
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// If this event is not scheduled, schedule it.
		if ( ! wp_next_scheduled( $this->event ) ) {
			wp_schedule_event( time(), $this->frequency, $this->event );
		}
	}
}