<?php
/**
 * SwiftCart COD core singleton.
 *
 * Bootstraps all sub-modules after WooCommerce is confirmed active.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart;

defined( 'ABSPATH' ) || exit;

use SwiftCart\Admin\Admin_Dashboard;
use SwiftCart\Admin\Orders_Dashboard;
use SwiftCart\Admin\Customer_Management;
use SwiftCart\Admin\COD_Reports;
use SwiftCart\Admin\Delivery_Zones;
use SwiftCart\Admin\Product_Stock_Meta;
use SwiftCart\Admin\MotoShop_Panel;
use SwiftCart\Checkout\Checkout_Fields;
use SwiftCart\Checkout\Checkout_Steps;
use SwiftCart\Stock\Stock_Status;
use SwiftCart\Warehouse\Warehouse_Dashboard;

/**
 * Class SwiftCart
 *
 * @since 1.0.0
 */
final class SwiftCart {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var static|null
	 */
	private static ?self $instance = null;

	/**
	 * Return the singleton, creating it on first call.
	 *
	 * @since  1.0.0
	 * @return static
	 */
	public static function get_instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Wire all sub-module hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function init(): void {
		// Checkout customisations.
		( new Checkout_Fields() )->register();
		( new Checkout_Steps() )->register();

		// Stock status labels.
		( new Stock_Status() )->register();

		// Admin pages.
		( new Admin_Dashboard() )->register();
		( new Orders_Dashboard() )->register();
		( new Customer_Management() )->register();
		( new COD_Reports() )->register();
		( new Delivery_Zones() )->register();
		( new Product_Stock_Meta() )->register();

		// Custom admin panel (front-end).
		( new MotoShop_Panel() )->register();

		// Warehouse ops.
		( new Warehouse_Dashboard() )->register();

		// i18n.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'swiftcart-cod',
			false,
			dirname( plugin_basename( SWIFTCART_FILE ) ) . '/languages'
		);
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}
}
