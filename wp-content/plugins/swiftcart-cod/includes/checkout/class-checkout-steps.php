<?php
/**
 * Checkout steps — registers custom WooCommerce order statuses used by
 * the SwiftCart COD workflow, and adds a COD handling fee to the cart.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Checkout;

defined( 'ABSPATH' ) || exit;

/**
 * Class Checkout_Steps
 *
 * @since 1.0.0
 */
class Checkout_Steps {

	/**
	 * Attach hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'init',                              array( $this, 'register_order_statuses' ) );
		add_filter( 'wc_order_statuses',                 array( $this, 'add_order_statuses_to_wc' ) );
		add_action( 'woocommerce_cart_calculate_fees',   array( $this, 'add_cod_fee' ) );
		add_filter( 'woocommerce_endpoint_order-received_title', array( $this, 'order_received_title' ) );
	}

	/**
	 * Register custom post statuses for the SwiftCart order workflow.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_order_statuses(): void {
		register_post_status(
			'wc-sc-packed',
			array(
				'label'                     => _x( 'Packed', 'Order status', 'swiftcart-cod' ),
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count */
				'label_count'               => _n_noop( 'Packed <span class="count">(%s)</span>', 'Packed <span class="count">(%s)</span>', 'swiftcart-cod' ),
			)
		);

		register_post_status(
			'wc-sc-dispatch',
			array(
				'label'                     => _x( 'Dispatched', 'Order status', 'swiftcart-cod' ),
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count */
				'label_count'               => _n_noop( 'Dispatched <span class="count">(%s)</span>', 'Dispatched <span class="count">(%s)</span>', 'swiftcart-cod' ),
			)
		);

		register_post_status(
			'wc-sc-returned',
			array(
				'label'                     => _x( 'Returned', 'Order status', 'swiftcart-cod' ),
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count */
				'label_count'               => _n_noop( 'Returned <span class="count">(%s)</span>', 'Returned <span class="count">(%s)</span>', 'swiftcart-cod' ),
			)
		);
	}

	/**
	 * Add custom statuses to the WooCommerce order status list.
	 *
	 * @since  1.0.0
	 * @param  array<string,string> $statuses Existing WC statuses.
	 * @return array<string,string>
	 */
	public function add_order_statuses_to_wc( array $statuses ): array {
		$statuses['wc-sc-packed']   = _x( 'Packed', 'Order status', 'swiftcart-cod' );
		$statuses['wc-sc-dispatch'] = _x( 'Dispatched', 'Order status', 'swiftcart-cod' );
		$statuses['wc-sc-returned'] = _x( 'Returned', 'Order status', 'swiftcart-cod' );

		return $statuses;
	}

	/**
	 * Add a COD handling fee to the cart when COD is the selected payment method.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_cod_fee(): void {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$chosen = WC()->session?->get( 'chosen_payment_method' );
		if ( 'cod' !== $chosen ) {
			return;
		}

		$fee = (float) get_option( 'swiftcart_cod_fee', 20 );
		if ( $fee <= 0 ) {
			return;
		}

		WC()->cart->add_fee( __( 'COD Handling Fee', 'swiftcart-cod' ), $fee, false );
	}

	/**
	 * Customise the order-received page title.
	 *
	 * @since  1.0.0
	 * @param  string $title Default title.
	 * @return string
	 */
	public function order_received_title( string $title ): string {
		return __( 'Order Placed — Pay on Delivery', 'swiftcart-cod' );
	}
}
