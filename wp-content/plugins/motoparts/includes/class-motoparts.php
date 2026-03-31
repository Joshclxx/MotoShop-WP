<?php
/**
 * Core plugin class — singleton bootstrap.
 *
 * Instantiated once on the plugins_loaded hook. Responsible for wiring
 * all sub-module registration calls so every feature attaches its WP hooks
 * at the right time.
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

/**
 * Class MotoParts
 *
 * @since 1.0.0
 */
final class MotoParts {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var static|null
	 */
	private static ?self $instance = null;

	/**
	 * Return the single instance, creating it on first call.
	 *
	 * Hooked to plugins_loaded in the entry file.
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
	 * Constructor — private to enforce singleton pattern.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Wire all sub-module hooks.
	 *
	 * Each register() call attaches WP actions/filters; no logic runs
	 * immediately. Actual execution is deferred to the appropriate hook.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function init(): void {
		// Custom post types.
		( new Product_CPT() )->register();
		( new Order_CPT() )->register();

		// Roles & capabilities (hooks for dynamic cap checks, if needed).
		( new Capabilities() )->register();

		// Asset enqueue.
		( new Assets() )->register();

		// i18n.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'motoparts',
			false,
			dirname( plugin_basename( MOTOPARTS_FILE ) ) . '/languages'
		);
	}

	/**
	 * Prevent cloning of the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}
}
