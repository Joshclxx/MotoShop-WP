<?php
/**
 * PHPUnit bootstrap for SwiftCart COD plugin tests.
 *
 * Loads the WordPress test library and the plugin under test.
 * Requires the WP test suite installed via `wp scaffold plugin-tests`.
 *
 * @package SwiftCart
 * @since   1.5.0
 */

// Attempt to locate the WordPress test library.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "WordPress test library not found at {$_tests_dir}.\n";
	echo "To install, run:\n";
	echo "  bash bin/install-wp-tests.sh wordpress_test root '' localhost latest\n";
	echo "Or set WP_TESTS_DIR to the path of the wordpress-tests-lib directory.\n";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin under test.
 */
tests_add_filter( 'muplugins_loaded', static function (): void {
	// Load WooCommerce first if available.
	$wc_path = dirname( __DIR__, 2 ) . '/woocommerce/woocommerce.php';
	if ( file_exists( $wc_path ) ) {
		require $wc_path;
	}

	// Load our plugin.
	require dirname( __DIR__ ) . '/swiftcart-cod.php';
} );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
