<?php
/*
Plugin Name: AffiliateWP Generator Utility
Description: Handles data generation and other handy methods with AffiliateWP integrations.
Version: 1.0.0
Author: Sandhills Development
Text Domain: affwp_generator
Domain Path: /languages
Requires at least: 5.0
Requires PHP: 5.6
Author URI: sandhillsdev.com
*/

namespace Affiliate_WP_Generator {

	use Affiliate_WP_Generator\Abstracts\Logger;
	use Affiliate_WP_Generator\Controllers\Generators;
	use Affiliate_WP_Generator\Controllers\Integrations;
	use Affiliate_WP_Generator\Controllers\Randomizer;

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * AffiliateWP Generator Base Class
	 *
	 * @since 1.0.0
	 */
	final class Affiliate_WP_Generator {

		/**
		 * Randomizer Instance.
		 *
		 * @since 1.0.0
		 *
		 * @var Randomizer
		 */
		private $randomizer;

		/**
		 * Generators Instance.
		 *
		 * @since 1.0.0
		 *
		 * @var Generators
		 */
		private $generators;

		/**
		 * Logger Instance.
		 *
		 * @since 1.0.0
		 *
		 * @var Logger
		 */
		private $basic_logger;

		/**
		 * Integrations Instance.
		 *
		 * @since 1.0.0
		 *
		 * @var Integrations
		 */
		private $integrations;

		/**
		 * Base class instance.
		 *
		 * @since 1.0.0
		 * @var Affiliate_WP_Generator|null The one true instance of Affiliate_WP_Generator
		 */
		private static $instance = null;

		/**
		 * Fetches the Logger instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Utilities\Basic_Logger
		 */
		public function logger() {
			return $this->_get_class( 'Utilities\\Basic_Logger' );
		}

		/**
		 * Fetches the Logger instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Controllers\Integrations
		 */
		public function integration() {
			return $this->_get_class( 'Controllers\\Integrations' );
		}

		/**
		 * Fetches the Integration instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Controllers\Generators
		 */
		public function generate() {
			return $this->_get_class( 'Controllers\\Generators' );
		}

		/**
		 * Fetches the Integration instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Controllers\Randomizer
		 */
		public function random() {
			return $this->_get_class( 'Controllers\\Randomizer' );
		}

		/**
		 * Fires up the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return self|\WP_Error
		 */
		public static function init() {
			if ( ! isset( self::$instance ) ) {

				// Check if AffiliateWP is active.
				if ( ! defined( 'AFFILIATEWP_VERSION' ) ) {
					self::$instance = new \WP_Error(
						'affiliate_wp_not_active',
						__( "This plugin requires AffiliateWP to run.", 'affwp_generator' )
					);

					// Bail if AffiliateWP is not active.
					return self::$instance;
				}

				global $wp_version;
				$affwp_version          = explode( '-', AFFILIATEWP_VERSION );
				$supports_wp_version    = version_compare( $wp_version, '5.0', '>=' );
				$supports_php_version   = version_compare( phpversion(), '5.6', '>=' );
				$supports_affwp_version = version_compare( $affwp_version[0], '2.5', '>=' );
				$is_supported           = $supports_wp_version && $supports_php_version && $supports_affwp_version;
				if ( $is_supported ) {
					self::$instance = new self;
					self::$instance->_define_constants();
					// Manually load Faker autoloader
					require_once( AFFWP_GENERATOR_ROOT_DIR . 'lib/external-libraries/faker/autoload.php' );
					self::$instance->_setup_autoloader();
					self::$instance->_setup_classes();
				} else {
					$self           = new self;
					self::$instance = new \WP_Error(
						'minimum_version_not_met',
						__( "The AffiliateWP Generator plugin requires at least WordPress 5.0, PHP 5.6, and AffiliateWP 2.5.", 'affwp_generator' ),
						array(
							'current_affwp_version' => AFFILIATEWP_VERSION,
							'current_wp_version'    => $wp_version,
							'php_version'           => phpversion(),
						)
					);

					// Include commands, so we can respond with an error message on why the commands are not working.
					$self->_define_constants();
					$self->_setup_autoloader();
					$self->_setup_commands();
					add_action( 'admin_notices', array( $self, 'below_version_notice' ) );
				}
			}

			return self::$instance;
		}

		/**
		 * Fetches the specified class, and constructs the class if it hasn't been constructed yet.
		 *
		 * @since 1.0.0
		 *
		 * @param $class
		 * @return mixed
		 */
		private function _get_class( $class ) {
			$exploded_class = explode( '\\', $class );
			$variable       = strtolower( array_pop( $exploded_class ) );

			if ( ! $this->$variable ) {
				$class           = __NAMESPACE__ . '\\' . $class;
				$this->$variable = new $class;
			}

			return $this->$variable;
		}

		/**
		 * Sends a notice if the WordPress or PHP version are below the minimum requirement.
		 *
		 * @since 1.0.0
		 */
		public function below_version_notice() {
			global $wp_version;

			if ( version_compare( $wp_version, '4.7', '<' ) ) {
				echo '<div class="error">
							<p>' . __( "AffiliateWP Generator plugin is not activated. The plugin requires at least WordPress 5.0 to function.", 'affwp_generator' ) . '</p>
						</div>';
			}

			if ( version_compare( phpversion(), '5.6', '<' ) ) {
				echo '<div class="error">
							<p>' . __( "AffiliateWP Generator plugin is not activated. The plugin requires at least PHP 5.6 to function.", 'affwp_generator' ) . '</p>
						</div>';
			}

			$affwp_version = explode( '-', AFFILIATEWP_VERSION );

			if ( version_compare( $affwp_version[0], '2.5', '<' ) ) {
				echo '<div class="error">
							<p>' . __( "AffiliateWP Generator plugin is not activated. The plugin requires at least AffiliateWP 2.5 to function.", 'affwp_generator' ) . '</p>
						</div>';
			}

		}

		/**
		 * Set up classes that cannot be otherwise loaded via the autoloader.
		 *
		 * This is where you can add anything that needs "registered" to WordPress,
		 * such as shortcodes, rest endpoints, blocks, and cron jobs.
		 *
		 * @since 1.0.0
		 */
		private function _setup_classes() {
			// REST Endpoints
			// new Rest\...

			// Cron Jobs
			new Cron\Purge_Logs;

			// Shortcodes
			// new Shortcodes\...

			// Widgets
			//			add_action( 'widgets_init', function() {
			//				register_widget( 'Affiliate_WP_Generator\Widgets\...' );
			//			} );

			// Commands
			$this->_setup_commands();
		}

		/**
		 * Sets up commands
		 *
		 * @since 1.0.0
		 */
		private function _setup_commands() {
			new Commands\Generate_Users;
			new Commands\Generate_Affiliates;
			new Commands\Generate_Products;
			new Commands\Generate_Orders;
			new Commands\Generate_Transactions;
		}

		/**
		 * Defines plugin-wide constants.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function _define_constants() {
			if ( ! defined( 'AFFWP_GENERATOR_URL' ) ) {
				$dir = plugin_dir_path( __FILE__ );

				// Root URL for this plugin.
				define( 'AFFWP_GENERATOR_URL', plugin_dir_url( __FILE__ ) );

				// Root directory for this plugin.
				define( 'AFFWP_GENERATOR_ROOT_DIR', $dir );

				// Root file for this plugin. Used in activation hooks.
				define( 'AFFWP_GENERATOR_ROOT_FILE', __FILE__ );

				// The template directory. Used by the template loader to determine where templates are stored.
				define( 'AFFWP_GENERATOR_TEMPLATE_DIR', AFFWP_GENERATOR_ROOT_DIR . 'templates/' );

				// The version of this plugin. Use when registering scripts and styles to bust cache.
				define( 'AFFWP_GENERATOR_VERSION', '1.0.0' );

				// The version of this plugin. Use when registering scripts and styles to bust cache.
				define( 'AFFWP_GENERATOR_CLI_BASE', 'affwp generate' );

			}
		}

		/**
		 * Registers the autoloader.
		 *
		 * @sicne 1.0.0
		 *
		 * @return bool|string
		 */
		private function _setup_autoloader() {
			try{
				spl_autoload_register( function( $class ) {
					$class = explode( '\\', $class );

					if ( __NAMESPACE__ === $class[0] ) {
						array_shift( $class );
					}

					// Faker
					$file_name = array_pop( $class );
					$directory = str_replace( '_', '-', strtolower( implode( DIRECTORY_SEPARATOR, $class ) ) );
					$file      = trailingslashit( AFFWP_GENERATOR_ROOT_DIR ) . 'lib/' . $directory . '/' . $file_name . '.php';

					if ( file_exists( $file ) ) {
						require $file;

						return true;
					}

					return false;
				} );
			}catch( \Exception $e ){
				$this->logger()->log_exception( 'autoload_failed', $e );

				return $e->getMessage();
			}

			return false;
		}
	}
}

namespace {

	use Affiliate_WP_Generator\Affiliate_WP_Generator;

	/**
	 * Fetches the instance
	 *
	 * @since 1.0.0
	 *
	 * @return Affiliate_WP_Generator|\WP_Error
	 */
	function affwp_generator() {
		return Affiliate_WP_Generator::init();
	}

	affwp_generator();
}