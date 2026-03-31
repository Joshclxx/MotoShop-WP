<?php
/**
 * Custom database table creation and management.
 *
 * Uses dbDelta() for idempotent CREATE TABLE operations — safe to run on
 * every activation. Compatible with the SQLite mu-plugin dropin:
 *  - AUTO_INCREMENT is mapped to AUTOINCREMENT automatically.
 *  - get_charset_collate() returns an empty string under SQLite (harmless).
 *  - longtext is used for JSON columns for cross-DB portability.
 *  - varchar(20) is used instead of ENUM for the coupon type column.
 *  - DEFAULT CURRENT_TIMESTAMP on datetime columns is supported in
 *    sqlite-database-integration >= 2.x.
 *
 * dbDelta() formatting rules (non-negotiable):
 *  - Each column definition on its own line, comma-terminated.
 *  - PRIMARY KEY  (id) — exactly two spaces between KEY and the opening paren.
 *  - No trailing comma on the last line before the closing paren.
 *  - KEY / UNIQUE KEY index lines after all column definitions.
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Class DB_Tables
 *
 * @since 1.0.0
 */
class DB_Tables {

	/**
	 * Plugin DB schema version option key.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const VERSION_OPTION = 'motoparts_db_version';

	/**
	 * Create or update all custom tables via dbDelta().
	 *
	 * Idempotent — safe to call on every plugin activation. Only alters
	 * existing tables when the schema has changed (dbDelta handles this).
	 * Persists the schema version to avoid redundant future runs.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function create_tables(): void {
		global $wpdb;

		// dbDelta() lives in wp-admin — must be explicitly loaded outside admin.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$prefix          = $wpdb->prefix;
		$charset_collate = $wpdb->get_charset_collate();

		// ------------------------------------------------------------------ //
		// wp_mp_order_items
		// ------------------------------------------------------------------ //
		$sql = "CREATE TABLE {$prefix}mp_order_items (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  order_id bigint(20) unsigned NOT NULL DEFAULT 0,
  product_id bigint(20) unsigned NOT NULL DEFAULT 0,
  quantity int(11) NOT NULL DEFAULT 1,
  price decimal(10,2) NOT NULL DEFAULT 0.00,
  variant_meta longtext DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY order_id (order_id),
  KEY product_id (product_id)
) {$charset_collate};";

		dbDelta( $sql );

		// ------------------------------------------------------------------ //
		// wp_mp_vehicles
		// ------------------------------------------------------------------ //
		$sql = "CREATE TABLE {$prefix}mp_vehicles (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  year smallint(4) unsigned NOT NULL DEFAULT 0,
  make varchar(100) NOT NULL DEFAULT '',
  model varchar(100) NOT NULL DEFAULT '',
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY make_model (make, model)
) {$charset_collate};";

		dbDelta( $sql );

		// ------------------------------------------------------------------ //
		// wp_mp_product_fitments
		// ------------------------------------------------------------------ //
		$sql = "CREATE TABLE {$prefix}mp_product_fitments (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  product_id bigint(20) unsigned NOT NULL DEFAULT 0,
  vehicle_id bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY product_id (product_id),
  KEY vehicle_id (vehicle_id),
  UNIQUE KEY product_vehicle (product_id, vehicle_id)
) {$charset_collate};";

		dbDelta( $sql );

		// ------------------------------------------------------------------ //
		// wp_mp_coupons
		// ------------------------------------------------------------------ //
		$sql = "CREATE TABLE {$prefix}mp_coupons (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL DEFAULT '',
  type varchar(20) NOT NULL DEFAULT 'percentage',
  value decimal(10,2) NOT NULL DEFAULT 0.00,
  expiry datetime DEFAULT NULL,
  usage_limit int(11) unsigned NOT NULL DEFAULT 0,
  used_count int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  UNIQUE KEY code (code)
) {$charset_collate};";

		dbDelta( $sql );

		// ------------------------------------------------------------------ //
		// wp_mp_inventory_log
		// ------------------------------------------------------------------ //
		$sql = "CREATE TABLE {$prefix}mp_inventory_log (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  product_id bigint(20) unsigned NOT NULL DEFAULT 0,
  delta int(11) NOT NULL DEFAULT 0,
  reason varchar(255) NOT NULL DEFAULT '',
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY product_id (product_id)
) {$charset_collate};";

		dbDelta( $sql );

		// Persist the schema version so future activations can skip if unchanged.
		update_option( self::VERSION_OPTION, MOTOPARTS_VERSION );
	}

	/**
	 * Return a map of short names to fully-qualified table names.
	 *
	 * Used by the uninstall routine to know which tables to drop (if desired).
	 *
	 * @since  1.0.0
	 * @return array<string, string>
	 */
	public static function get_table_names(): array {
		global $wpdb;

		return array(
			'order_items'      => $wpdb->prefix . 'mp_order_items',
			'vehicles'         => $wpdb->prefix . 'mp_vehicles',
			'product_fitments' => $wpdb->prefix . 'mp_product_fitments',
			'coupons'          => $wpdb->prefix . 'mp_coupons',
			'inventory_log'    => $wpdb->prefix . 'mp_inventory_log',
		);
	}

	/**
	 * Return the currently stored DB schema version.
	 *
	 * @since  1.0.0
	 * @return string Semver string, or '0.0.0' if never set.
	 */
	public function get_db_version(): string {
		return (string) get_option( self::VERSION_OPTION, '0.0.0' );
	}
}
