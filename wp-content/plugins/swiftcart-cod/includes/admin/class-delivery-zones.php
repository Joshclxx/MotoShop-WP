<?php
/**
 * Delivery zones management admin page (stub for Phase 2).
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Delivery_Zones
 *
 * @since 1.0.0
 */
class Delivery_Zones {

	/**
	 * Attach hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
	}

	/**
	 * Register the Delivery Zones submenu page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_menu_page(): void {
		add_submenu_page(
			'swiftcart-dashboard',
			__( 'Delivery Zones', 'swiftcart-cod' ),
			__( 'Delivery Zones', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart-zones',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the Delivery Zones page (placeholder).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_swiftcart' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'swiftcart-cod' ) );
		}

		echo '<div class="wrap"><h1>' . esc_html__( 'Delivery Zones', 'swiftcart-cod' ) . '</h1>';
		echo '<p>' . esc_html__( 'Delivery zone management will be implemented in Phase 2. Currently using WooCommerce built-in shipping zones.', 'swiftcart-cod' ) . '</p>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) . '" class="button button-primary">';
		echo esc_html__( 'Configure WooCommerce Shipping Zones →', 'swiftcart-cod' );
		echo '</a></div>';
	}
}
