<?php
/**
 * Cron task to purge error logs.
 *
 * @since 1.0.0
 * @package Affiliate_WP_Generator\Cron
 */


namespace Affiliate_WP_Generator\Cron;


use Affiliate_WP_Generator\Abstracts\Cron_Task;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Purge_Logs
 *
 * @since 1.0.0
 * @package Affiliate_WP_Generator\Cron
 */
class Purge_Logs extends Cron_Task {

	/**
	 * Purge_Logs constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'affwp_generator_purge_logs', 'daily' );
	}

	/**
	 * @inheritDoc
	 */
	function cron_action() {
		affwp_generator()->logger()->purge( 30 );
	}
}