<?php
/**
 * Product bulk stock status actions in the admin list table.
 *
 * Adds "Set to In Stock", "Set to Low Stock", "Set to Out of Stock"
 * bulk actions to the WooCommerce Products admin screen.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Admin;

defined( 'ABSPATH' ) || exit;

use SwiftCart\Stock\Stock_Status;

/**
 * Class Product_Stock_Meta
 *
 * @since 1.0.0
 */
class Product_Stock_Meta {

	/**
	 * Attach hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'bulk_actions-edit-product',        array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-product', array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices',                    array( $this, 'bulk_action_admin_notice' ) );
	}

	/**
	 * Add SwiftCart stock status bulk actions to the Products list.
	 *
	 * @since  1.0.0
	 * @param  array<string,string> $actions Existing bulk actions.
	 * @return array<string,string>
	 */
	public function add_bulk_actions( array $actions ): array {
		$actions['sc_set_in_stock']     = __( 'SwiftCart: Set to In Stock', 'swiftcart-cod' );
		$actions['sc_set_low_stock']    = __( 'SwiftCart: Set to Low Stock', 'swiftcart-cod' );
		$actions['sc_set_out_of_stock'] = __( 'SwiftCart: Set to Out of Stock', 'swiftcart-cod' );

		return $actions;
	}

	/**
	 * Handle SwiftCart bulk stock status actions.
	 *
	 * @since  1.0.0
	 * @param  string   $redirect_url Redirect URL after bulk action.
	 * @param  string   $action       Action slug.
	 * @param  int[]    $post_ids     Selected post IDs.
	 * @return string
	 */
	public function handle_bulk_actions( string $redirect_url, string $action, array $post_ids ): string {
		$map = array(
			'sc_set_in_stock'     => 'in_stock',
			'sc_set_low_stock'    => 'low_stock',
			'sc_set_out_of_stock' => 'out_of_stock',
		);

		if ( ! isset( $map[ $action ] ) ) {
			return $redirect_url;
		}

		$status = $map[ $action ];

		foreach ( $post_ids as $id ) {
			update_post_meta( absint( $id ), Stock_Status::META_KEY, $status );
		}

		$redirect_url = add_query_arg( array(
			'sc_bulk_updated' => count( $post_ids ),
			'sc_bulk_status'  => $status,
		), $redirect_url );

		return $redirect_url;
	}

	/**
	 * Display an admin notice after bulk stock status update.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function bulk_action_admin_notice(): void {
		if ( ! isset( $_GET['sc_bulk_updated'] ) ) {
			return;
		}

		$count  = absint( $_GET['sc_bulk_updated'] );
		$status = sanitize_text_field( wp_unslash( $_GET['sc_bulk_status'] ?? '' ) );

		$labels = array(
			'in_stock'     => __( 'In Stock', 'swiftcart-cod' ),
			'low_stock'    => __( 'Low Stock', 'swiftcart-cod' ),
			'out_of_stock' => __( 'Out of Stock', 'swiftcart-cod' ),
		);

		$label = $labels[ $status ] ?? $status;

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: number of products, 2: status label */
					_n(
						'%1$d product updated to %2$s.',
						'%1$d products updated to %2$s.',
						$count,
						'swiftcart-cod'
					),
					$count,
					$label
				)
			)
		);
	}
}
