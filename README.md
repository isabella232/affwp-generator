# AffiliateWP Integration Utilities

This plugin is intended to help make it easier to test and develop using AffiliateWP integrations.
Currently, this utility works with three integrations:

* Easy Digital Downloads
* Restrict Content Pro
* WooCommerce

## Setup

This plugin uses a composer library, [Faker](https://github.com/fzaninotto/Faker), to help generate random data.
You may need to have [Composer](https://getcomposer.org/) installed on your computer to use this plugin.

After downloading this plugin, navigate to this directory and run `composer install` to get started.

After that, it's as simple as activating the plugin.

## Generator Examples

All generators can be found in the `Generators` controller, and can be accessed using `affwp_generator()->generate()`.

### Generate Entire Transactions From Scratch

If you just want to generate some referrals using _real_ orders, simply do this:

```php
<?php
// Generates 100 transactions with referrals using Easy Digital Downloads.
affwp_generator()->generate()->transactions( 'edd' );
```

If you wanted to be a bit more picky about how much data is generated, you can do this:

```php
<?php
// Generates 10 transactions, 5 customers, 1 affiliate, and 3 products with referrals using Easy Digital Downloads.
affwp_generator()->generate()->transactions( 'edd', array(
  'number'                   => 10, // Generate 10 orders
  'users'                    => 5, // Generate 5 users to randomly select for orders
  'affiliates'               => 1, // Generate 1 affiliate to refer in each order
  'products'                 => 10, // Generate 10 products to randomly select in each order.
  'products_per_transaction' => array(
    'min' => 2, // Minimum of 2 products per order
    'max' => 10, // Maximum of 10 products per order
  ),
  'date_range'              => array(
    'earliest' => 'last_year', // Generate orders and referrals as old as 1 full year.
    'latest'   => 'today',     // Generate orders and referrals as late as today.
  )
) );
```

Behind the scenes, this will generate customers, affiliates, products, orders, views, and referrals all at one time.

### Generate Orders Using Existing Data

If you want to generate orders using _existing_ data, you can use the `orders` method:

```php
<?php

// Get all user IDs
$users = new WP_User_Query( array( 'fields' => 'ids' ) );

// Retrieve all affiliate IDs
$affiliates = affiliate_wp()->affiliates->get_affiliates( array( 'fields' => 'ids', 'number' => -1 ) );

// Retrieve all EDD product IDs 
$products = new WP_Query( array( 'post_type' => 'download', 'fields' => 'ids', 'posts_per_page' => -1 ) );

// Generate 10 orders using the provided datasets.
$order_ids = affwp_generator()->generate()->orders( 'edd', array(
	'number'     => 10,
	'users'      => $users->get_results(),
	'affiliates' => $affiliates,
	'products'   => $products->posts,
) );
```

### Generate Orders Using A Mix of Existing and Nonexisting Data

If you want to generate orders using both _existing_ and _nonexisting_ data,
you can use the `orders` method with other generators:

```php
<?php

// Generate 10 new users
$users = affwp_generator()->generate()->users( array( 'number' => 10 ) );

// Generate 10 active affiliates
$affiliates = affwp_generator()->generate()->affiliates( array( 'number' => 10, 'status' => 'active' ) );

// Generate EDD Products 
$products = affwp_generator()->generate()->products( 'edd', array( 'number' => 10 ) );

// Generate 10 orders using the provided datasets.
$order_ids = affwp_generator()->generate()->orders( 'edd', array(
	'number'     => 10,
	'users'      => $users,
	'affiliates' => $affiliates,
	'products'   => $products,
) );
```

## Getting Random Values

Most values have wrapper methods to handle randomly generated things used in this plugin. All random generator items can
be found in the `Ranzomizer` controller, and can be accessed using `affwp_generator()->random()`.

Example:

```php
<?php
// Selects a random affiliate status.
$status = affwp_generator()->random()->affiliate_status();
```


```php
<?php
// Generates a random price between 25 cents and 3 dollars.
$price = affwp_generator()->random()->price( .25, 3 );
```

This plugin comes packaged with the [Faker](https://github.com/fzaninotto/Faker) library. Any method
within this library can be accessed using the `faker()` getter method, like so:

```php
<?php
// Generates a random TLD
affwp_generator()->random()->faker()->tld;
```

For a complete list of the _insane_ number of things the Faker library can generate, check the [Faker](https://github.com/fzaninotto/Faker) docs

## CLI

All of the different generators work with the cli, as well. All of the commands can be seen in  the `lib/commands` directory. use the `--help` flag for a lot more info on each of these.

### Commands
`wp affwp generate transactions` - Generates users, affiliates, products, and orders based on specified quantities

```bash
# Generate 50 random orders for Easy Digital Downloads.
# Will also generate new affiliates, users, products.
wp affwp generate transactions edd --number=50
``` 

```bash
# Generate 50 random orders for Easy Digital Downloads.
# Will generate 10 affiliates, and the default users, and products.
wp affwp generate transactions edd --number=50 --affiliates=10
``` 

`wp affwp generate products` - Generate products.

```bash
# Generate 50 random products for Easy Digital Downloads.
wp affwp generate products edd --number=50
```

`wp affwp generate affiliates` - Generate affiliates.

```bash
# Generate 50 random affiliates
wp affwp generate affiliates --number=50
```

`wp affwp generate users` - Generate users.

```bash
# Generate 50 random users
wp affwp generate users --number=50
```

`wp affwp generate orders` - Generates orders using an integration, affiliate IDs, order IDs, and user IDs.

If you need to be more-specific than a simple transaction, you can pass commands directly into the `orders` command.
The example below would generate 100 orders, using 10 random active affiliates, a hard-coded list of products, and
a list of 10 users from the database.
```bash
# generate 100 orders, using 10 random active affiliates, a hard-coded list of products, and a list of 10 users from the database.
wp affwp generate orders edd --affiliates="$(wp affwp affiliate list --status=active --format=ids)" --products="507 508 509 510" --users="$(wp user list --format=ids --number=10)" --number=100
```

You can also generate random items with more specificity than transactions. The example below generates a single order using 1 active affiliate.
```bash
wp affwp generate orders edd --number=1 --users="$(wp affwp generate users --format=ids)" --affiliates="$(wp affwp generate affiliates --number=1 --status=active --format=ids)" --products="$(wp affwp generate products edd --format=ids)"
```
