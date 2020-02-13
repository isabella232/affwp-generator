<?php
/**
 * Randomizer Class
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Controllers
 */


namespace Affiliate_WP_Generator\Controllers;


use Faker\Factory as Faker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Randomizer
 * Generates Random Values
 *
 * @since   1.0.0
 * @package Affiliate_WP_Generator\Controllers
 */
class Randomizer {

	/**
	 * Faker library instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Faker
	 */
	protected static $faker;

	/**
	 * Getter method for the faker instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \Faker\Generator The faker instance
	 */
	public function faker() {
		if ( ! self::$faker instanceof Faker ) {
			self::$faker = Faker::create();
		}

		return self::$faker;
	}

	/**
	 * Generates a random username.
	 *
	 * @since 1.0.0
	 *
	 * @return string A randomly generated username.
	 */
	public function user_name() {
		return $this->faker()->userName;
	}

	/**
	 * Generates a random first name.
	 *
	 * @since 1.0.0
	 *
	 * @return string A randomly generated first name.
	 */
	public function first_name() {
		return $this->faker()->firstName;
	}

	/**
	 * Generates a random last name.
	 *
	 * @since 1.0.0
	 *
	 * @return string A randomly generated last name.
	 */
	public function last_name() {
		return $this->faker()->lastName;
	}

	/**
	 * Generates a random product name.
	 *
	 * @since 1.0.0
	 *
	 * @return string A randomly generated product name.
	 */
	public function product_name() {
		return $this->faker()->bs;
	}

	/**
	 * Selects a random item from an array of items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Pool of items to select a random item from.
	 * @return mixed A single randomly selected item.
	 */
	public function array_item( array $items ) {
		return $this->faker()->randomElement( $items );
	}

	/**
	 * Selects a collection of random items from an array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items  Pool of items to select a random item from.
	 * @param int   $number Number of items to retrieve.
	 * @return array A collection of randomly selected items.
	 */
	public function array_subset( array $items, $number ) {
		return $this->faker()->randomElements( $items, $number );
	}

	/**
	 * Generates a random number.
	 *
	 * @since 1.0.0
	 *
	 * @param int $min The minimum number.
	 * @param int $max The maximum number.
	 * @return int A random integer between the two provided values.
	 */
	public function number( $min, $max ) {
		return $this->faker()->numberBetween( $min, $max );
	}

	/**
	 * Generates a random price.
	 *
	 * @since 1.0.0
	 *
	 * @param float $min The minimum price.
	 * @param float $max The maximum price.
	 * @return float A random price between the two provided values.
	 */
	public function price( $min, $max ) {
		return $this->faker()->randomFloat( 2, $min, $max );
	}

	/**
	 * Generates a random email.
	 *
	 * @since 1.0.0
	 *
	 * @return string A randomly generated email.
	 */
	public function email() {
		return $this->faker()->safeEmail;
	}

	/**
	 * Generates a random password.
	 *
	 * @since 1.0.0
	 *
	 * @return string A randomly generated password.
	 */
	public function password() {
		return wp_generate_password( 30 );
	}

	/**
	 * Selects a random rate type.
	 *
	 * @since 1.0.0
	 *
	 * @return string a single randomly selected rate type.
	 */
	public function rate_type() {
		$rate_types = array_keys( affwp_get_affiliate_rate_types() );

		return $this->array_item( $rate_types );
	}

	/**
	 * Selects a random affiliate status.
	 *
	 * @since 1.0.0
	 *
	 * @return string a single randomly selected affiliate status.
	 */
	public function affiliate_status() {
		$statuses = array_keys( affwp_get_affiliate_statuses() );

		return $this->array_item( $statuses );
	}

	/**
	 * Selects a random referral status.
	 *
	 * @since 1.0.0
	 *
	 * @return string a single randomly selected referral status.
	 */
	public function referral_status() {
		$statuses = array_keys( affwp_get_referral_statuses() );

		return $this->array_item( $statuses );
	}

	/**
	 * Selects a random date in-between specified dates.
	 *
	 * @since 1.0.0
	 *
	 * @param string $earliest_date The earliest possible date.
	 * @param string $latest_date   The latest possible date. Defaults to today's date.
	 * @return \DateTime A randomly-selected DateTime object in-between the specified dates.
	 */
	public function date( $earliest_date, $latest_date = 'now' ) {
		return $this->faker()->dateTimeBetween( $earliest_date, $latest_date );
	}
}