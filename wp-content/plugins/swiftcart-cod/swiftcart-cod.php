<?php
/**
 * SwiftCart COD
 *
 * @package           SwiftCart
 * @author            Jay
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       SwiftCart COD
 * Plugin URI:        https://example.com/swiftcart-cod
 * Description:       COD-first e-commerce for Southeast Asia — Philippine address fields, 3-step checkout, blacklist management, warehouse ops, and COD reporting.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Author:            Jay
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       swiftcart-cod
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:      10.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'SWIFTCART_VERSION', '1.0.0' );
define( 'SWIFTCART_FILE',    __FILE__ );
define( 'SWIFTCART_DIR',     plugin_dir_path( __FILE__ ) );
define( 'SWIFTCART_URL',     plugin_dir_url( __FILE__ ) );

/**
 * Declare WooCommerce HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				SWIFTCART_FILE,
				true
			);
		}
	}
);

/**
 * Autoloader — must be at top level, not inside any callback.
 */
require_once SWIFTCART_DIR . 'includes/class-autoloader.php';
\SwiftCart\Autoloader::register();

/**
 * Lifecycle hooks.
 */
register_activation_hook(   SWIFTCART_FILE, array( \SwiftCart\Activator::class,   'activate'   ) );
register_deactivation_hook( SWIFTCART_FILE, array( \SwiftCart\Deactivator::class, 'deactivate' ) );
register_uninstall_hook(    SWIFTCART_FILE, array( \SwiftCart\Activator::class,   'uninstall'  ) );

/**
 * Bootstrap after all plugins (including WooCommerce) are loaded.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function (): void {
					echo '<div class="notice notice-error"><p>';
					echo esc_html__( 'SwiftCart COD requires WooCommerce to be installed and activated.', 'swiftcart-cod' );
					echo '</p></div>';
				}
			);

			return;
		}

		// Only run table creation on activation / upgrade (avoids SQLite
		// "table already exists" bug with CREATE TABLE IF NOT EXISTS).
		$installed_ver = get_option( 'swiftcart_db_version', '0' );
		if ( version_compare( $installed_ver, SWIFTCART_VERSION, '<' ) ) {
			\SwiftCart\Activator::create_tables();
			update_option( 'swiftcart_db_version', SWIFTCART_VERSION );
		}

		\SwiftCart\SwiftCart::get_instance();
	}
);
