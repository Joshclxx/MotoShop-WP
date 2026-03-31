<?php
/**
 * MotoParts
 *
 * @package           MotoParts
 * @author            Jay
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       MotoParts
 * Plugin URI:        https://example.com/motoparts
 * Description:       Automotive parts e-commerce system — catalogue, orders, cart, checkout, and inventory management.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Author:            Jay
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       motoparts
 * Domain Path:       /languages
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'MOTOPARTS_VERSION', '1.0.0' );
define( 'MOTOPARTS_FILE',    __FILE__ );
define( 'MOTOPARTS_DIR',     plugin_dir_path( __FILE__ ) );
define( 'MOTOPARTS_URL',     plugin_dir_url( __FILE__ ) );

/**
 * Autoloader — must be required at top level (not inside any callback)
 * so classes are resolvable during activation hooks and before plugins_loaded.
 */
require_once MOTOPARTS_DIR . 'includes/class-autoloader.php';
\MotoParts\Autoloader::register();

/**
 * Lifecycle hooks.
 */
register_activation_hook(   MOTOPARTS_FILE, array( \MotoParts\Activator::class,   'activate'   ) );
register_deactivation_hook( MOTOPARTS_FILE, array( \MotoParts\Deactivator::class, 'deactivate' ) );
register_uninstall_hook(    MOTOPARTS_FILE, array( \MotoParts\Activator::class,   'uninstall'  ) );

/**
 * Bootstrap the plugin singleton after all plugins are loaded.
 */
add_action( 'plugins_loaded', array( \MotoParts\MotoParts::class, 'get_instance' ) );
