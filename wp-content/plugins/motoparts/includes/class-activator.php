<?php
/**
 * Plugin activation and uninstall routines.
 *
 * Called by register_activation_hook() and register_uninstall_hook() from
 * the plugin entry file. Static methods are required by register_uninstall_hook.
 *
 * NOTE: plugins_loaded has NOT fired when these run. The autoloader must be
 * registered at the top level of motoparts.php (which it is) so all
 * classes are resolvable here.
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts;

defined( 'ABSPATH' ) || exit;

use MotoParts\Admin\Capabilities;
use MotoParts\CPT\Order_CPT;
use MotoParts\CPT\Product_CPT;
use MotoParts\DB\DB_Tables;

/**
 * Class Activator
 *
 * @since 1.0.0
 */
class Activator {

	/**
	 * Run all activation tasks.
	 *
	 * Registers CPTs so rewrite rules exist before flush, creates DB tables,
	 * assigns capabilities, seeds taxonomy terms, then flushes rewrite rules.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function activate(): void {
		// Register CPTs so their rewrite slugs are in the registry before flush.
		( new Product_CPT() )->register_cpt();
		( new Order_CPT() )->register_cpt();

		// Create custom DB tables.
		( new DB_Tables() )->create_tables();

		// Assign capabilities to roles.
		( new Capabilities() )->add_caps();

		// Seed default order status taxonomy terms.
		Order_CPT::seed_default_statuses();

		// Flush rewrite rules after CPT slugs are registered.
		flush_rewrite_rules();
	}

	/**
	 * Run uninstall cleanup.
	 *
	 * Removes all custom capabilities from roles and deletes plugin options.
	 * DB tables are intentionally left in place to preserve customer data.
	 * Add table drops here only if you are certain data loss is acceptable.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function uninstall(): void {
		( new Capabilities() )->remove_caps();

		delete_option( 'motoparts_db_version' );
	}
}
