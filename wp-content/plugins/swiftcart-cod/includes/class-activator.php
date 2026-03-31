<?php
/**
 * Plugin activation and uninstall routines.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activator
 *
 * @since 1.0.0
 */
class Activator {

	/**
	 * Run activation tasks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function activate(): void {
		self::add_warehouse_role();
		self::set_default_options();
		self::create_tables();
		update_option( 'swiftcart_db_version', SWIFTCART_VERSION );
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 *
	 * Safe to call repeatedly — uses CREATE TABLE IF NOT EXISTS.
	 * Uses longtext/varchar only (SQLite compatible; no ENUM/JSON).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sc_blacklist (
  id bigint(20) unsigned NOT NULL auto_increment,
  phone varchar(20) NOT NULL DEFAULT '',
  reason longtext NOT NULL DEFAULT '',
  active tinyint(1) NOT NULL DEFAULT 1,
  created_by bigint(20) unsigned NOT NULL DEFAULT 0,
  created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY phone (phone),
  KEY active (active)
) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Register the warehouse_staff role.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function add_warehouse_role(): void {
		if ( null !== get_role( 'warehouse_staff' ) ) {
			return;
		}

		add_role(
			'warehouse_staff',
			__( 'Warehouse Staff', 'swiftcart-cod' ),
			array(
				'read'                        => true,
				'swiftcart_view_pick_list'    => true,
				'swiftcart_pack_orders'       => true,
				'swiftcart_dispatch_orders'   => true,
				'swiftcart_manage_returns'    => true,
				'swiftcart_edit_stock_status' => true,
			)
		);

		// Admin gets all warehouse caps too.
		$admin = get_role( 'administrator' );
		if ( $admin instanceof \WP_Role ) {
			$admin->add_cap( 'swiftcart_view_pick_list',    true );
			$admin->add_cap( 'swiftcart_pack_orders',       true );
			$admin->add_cap( 'swiftcart_dispatch_orders',   true );
			$admin->add_cap( 'swiftcart_manage_returns',    true );
			$admin->add_cap( 'swiftcart_edit_stock_status', true );
			$admin->add_cap( 'manage_swiftcart',            true );
			$admin->add_cap( 'view_swiftcart_reports',      true );
		}
	}

	/**
	 * Set default plugin options.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function set_default_options(): void {
		$defaults = array(
			'swiftcart_cod_fee'              => 20,
			'swiftcart_cutoff_hour'          => 10,  // 10 AM.
			'swiftcart_call_threshold'       => 3000, // ₱ amount for verification call.
			'swiftcart_blacklist_threshold'  => 3,    // cancellations before auto-flag.
			'swiftcart_version'              => SWIFTCART_VERSION,
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}

	/**
	 * Run uninstall cleanup.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function uninstall(): void {
		global $wpdb;

		remove_role( 'warehouse_staff' );

		// Drop custom tables.
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}sc_blacklist" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$options = array(
			'swiftcart_cod_fee',
			'swiftcart_cutoff_hour',
			'swiftcart_call_threshold',
			'swiftcart_blacklist_threshold',
			'swiftcart_version',
		);

		$options[] = 'swiftcart_db_version';

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}
}
