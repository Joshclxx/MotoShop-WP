<?php
/**
 * Plugin Name:       Reading Time Estimator
 * Plugin URI:        https://example.com/reading-time-estimator
 * Description:       Displays an estimated reading time at the top of every single post.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       reading-time-estimator
 * Domain Path:       /languages
 *
 * @package ReadingTimeEstimator
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RTE_VERSION', '1.0.0' );
define( 'RTE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RTE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once RTE_PLUGIN_DIR . 'includes/class-reading-time.php';

/**
 * Initialise the plugin.
 */
function rte_init(): void {
	$plugin = new ReadingTimeEstimator\Reading_Time();
	$plugin->register_hooks();
}
add_action( 'plugins_loaded', 'rte_init' );

/**
 * Enqueue front-end stylesheet on single posts only.
 */
function rte_enqueue_styles(): void {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_style(
		'reading-time-estimator',
		RTE_PLUGIN_URL . 'assets/css/reading-time.css',
		array(),
		RTE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'rte_enqueue_styles' );
