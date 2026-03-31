<?php
/**
 * SwiftCart COD Admin Orders Dashboard.
 *
 * Custom WooCommerce orders view with COD-specific KPIs, filters, and actions:
 * blacklist flags, cancellation rate warnings, COD status toggle, and
 * quick-action buttons (Confirm / Print / Cancel).
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders_Dashboard
 *
 * @since 1.0.0
 */
class Orders_Dashboard {

	/**
	 * Attach admin hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu',            array( $this, 'register_menu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_sc_update_order_status', array( $this, 'ajax_update_order_status' ) );
		add_action( 'wp_ajax_sc_toggle_blacklist',    array( $this, 'ajax_toggle_blacklist' ) );
	}

	/**
	 * Register the SwiftCart admin menu group.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_menu_pages(): void {
		add_menu_page(
			__( 'SwiftCart', 'swiftcart-cod' ),
			__( 'SwiftCart', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart',
			array( $this, 'render_orders_page' ),
			'dashicons-cart',
			55
		);

		add_submenu_page(
			'swiftcart',
			__( 'Orders', 'swiftcart-cod' ),
			__( 'Orders', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart',
			array( $this, 'render_orders_page' )
		);
	}

	/**
	 * Enqueue admin stylesheet and script on SwiftCart pages.
	 *
	 * @since  1.0.0
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'swiftcart' ) && ! str_contains( $hook, 'warehouse' ) ) {
			return;
		}

		wp_enqueue_style(
			'swiftcart-admin',
			SWIFTCART_URL . 'assets/css/admin.css',
			array(),
			(string) filemtime( SWIFTCART_DIR . 'assets/css/admin.css' )
		);

		wp_enqueue_script(
			'swiftcart-admin',
			SWIFTCART_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-util' ),
			(string) filemtime( SWIFTCART_DIR . 'assets/js/admin.js' ),
			true
		);

		wp_localize_script(
			'swiftcart-admin',
			'scAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sc_admin' ),
			)
		);
	}

	/**
	 * Render the Orders Dashboard page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_orders_page(): void {
		if ( ! current_user_can( 'manage_swiftcart' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'swiftcart-cod' ) );
		}

		$kpis   = $this->get_kpis();
		$orders = $this->get_orders();

		?>
		<div class="wrap sc-admin-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'SwiftCart Orders', 'swiftcart-cod' ); ?></h1>
			<p style="color:#888;font-size:13px;margin-top:4px;"><?php echo esc_html( date( 'l, F j, Y', current_time( 'timestamp' ) ) ); ?></p>

			<!-- Widgets Row -->
			<div class="sc-widgets-row">
				<div class="sc-widget sc-widget--blue">
					<div class="sc-widget__icon">📦</div>
					<div class="sc-widget__value"><?php echo esc_html( $kpis['today_total'] ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( "Today's Orders", 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub"><?php esc_html_e( 'Orders placed today', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-widget sc-widget--orange">
					<div class="sc-widget__icon">💰</div>
					<div class="sc-widget__value">₱<?php echo esc_html( number_format( (float) $kpis['cod_expected'], 0 ) ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( 'COD Expected Revenue', 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub"><?php esc_html_e( 'Pending + out for delivery', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-widget sc-widget--green">
					<div class="sc-widget__icon">✅</div>
					<div class="sc-widget__value"><?php echo esc_html( $kpis['delivery_rate'] ); ?>%</div>
					<div class="sc-widget__label"><?php esc_html_e( 'Delivery Success Rate', 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub">
						<?php
						printf(
							/* translators: 1: delivered count, 2: total count */
							esc_html__( '%1$d of %2$d orders delivered', 'swiftcart-cod' ),
							absint( $kpis['delivered_total'] ),
							absint( $kpis['all_time_total'] )
						);
						?>
					</div>
				</div>
				<div class="sc-widget sc-widget--red">
					<div class="sc-widget__icon">↩️</div>
					<div class="sc-widget__value"><?php echo esc_html( $kpis['return_requests'] ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( 'Return Requests', 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub"><?php esc_html_e( 'Returned + failed today', 'swiftcart-cod' ); ?></div>
				</div>
			</div>

			<!-- Filters -->
			<form method="get" action="" class="sc-filter-form">
				<input type="hidden" name="page" value="swiftcart">
				<?php $this->render_filters(); ?>
			</form>

			<!-- Orders Table -->
			<table class="wp-list-table widefat fixed striped sc-orders-table">
				<thead>
					<tr>
						<th style="width:32px;"><input type="checkbox" id="sc-select-all"></th>
						<th><?php esc_html_e( 'Order', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Mobile', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'City / Barangay', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Total', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Status', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Risk Level', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'swiftcart-cod' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $orders as $order ) : ?>
					<?php $this->render_order_row( $order ); ?>
				<?php endforeach; ?>
				<?php if ( empty( $orders ) ) : ?>
					<tr><td colspan="9" style="text-align:center;padding:24px;color:#666;"><?php esc_html_e( 'No orders found.', 'swiftcart-cod' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render filter row (status, date, search).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function render_filters(): void {
		$status_filter = sanitize_text_field( wp_unslash( $_GET['sc_status'] ?? '' ) );
		$search        = sanitize_text_field( wp_unslash( $_GET['sc_search'] ?? '' ) );

		$statuses = array(
			''            => __( 'All Statuses', 'swiftcart-cod' ),
			'pending'     => __( 'Pending', 'swiftcart-cod' ),
			'processing'  => __( 'Confirmed', 'swiftcart-cod' ),
			'sc-packed'   => __( 'Packed', 'swiftcart-cod' ),
			'sc-dispatch' => __( 'Dispatched', 'swiftcart-cod' ),
			'completed'   => __( 'Delivered', 'swiftcart-cod' ),
			'cancelled'   => __( 'Cancelled', 'swiftcart-cod' ),
			'sc-returned' => __( 'Returned', 'swiftcart-cod' ),
		);

		echo '<div class="sc-filters" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">';

		echo '<select name="sc_status" onchange="this.form.submit()">';
		foreach ( $statuses as $val => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $val ),
				selected( $status_filter, $val, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( $label )
			);
		}
		echo '</select>';

		echo '<input type="text" name="sc_search" placeholder="' . esc_attr__( 'Order ID or phone…', 'swiftcart-cod' ) . '" value="' . esc_attr( $search ) . '">';
		echo '<button type="submit" class="button">' . esc_html__( 'Filter', 'swiftcart-cod' ) . '</button>';
		echo '</div>';
	}

	/**
	 * Render a single order row.
	 *
	 * @since  1.0.0
	 * @param  \WC_Order $order WooCommerce order.
	 * @return void
	 */
	private function render_order_row( \WC_Order $order ): void {
		$order_id    = $order->get_id();
		$customer    = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$phone       = $order->get_billing_phone();
		$city        = $order->get_billing_city();
		$barangay    = get_post_meta( $order_id, '_billing_barangay', true );
		$total       = $order->get_total();
		$status      = $order->get_status();
		$is_flagged  = (bool) get_post_meta( $order_id, '_sc_blacklist_flag', true );
		$cancel_rate = $this->get_customer_cancel_rate( $phone );

		$status_labels = array(
			'pending'     => 'pending',
			'processing'  => 'confirmed',
			'sc-packed'   => 'packed',
			'sc-dispatch' => 'dispatched',
			'completed'   => 'delivered',
			'cancelled'   => 'cancelled',
			'sc-returned' => 'returned',
		);

		$status_class = $status_labels[ $status ] ?? 'pending';

		// Risk level.
		if ( $is_flagged || $cancel_rate >= 50 ) {
			$risk_class = 'flagged';
			$risk_label = __( 'Flagged for Review', 'swiftcart-cod' );
		} elseif ( $cancel_rate >= 30 ) {
			$risk_class = 'watchlist';
			$risk_label = __( 'Watchlist', 'swiftcart-cod' );
		} else {
			$risk_class = 'normal';
			$risk_label = __( 'Normal', 'swiftcart-cod' );
		}

		$row_style = $is_flagged ? ' style="background:#FFEBEE;"' : '';

		echo '<tr' . $row_style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<td><input type="checkbox" name="sc_order_ids[]" value="' . esc_attr( $order_id ) . '"></td>';

		echo '<td><a href="' . esc_url( get_edit_post_link( $order_id ) ) . '"><strong>#SC-' . esc_html( $order_id ) . '</strong></a><br>'
			. '<span style="font-size:11px;color:#888;">' . esc_html( $order->get_date_created()?->date( 'M d, Y H:i' ) ?? '' ) . '</span></td>';

		echo '<td>' . esc_html( $customer );
		if ( $is_flagged ) {
			echo ' <span title="' . esc_attr__( 'Blacklisted', 'swiftcart-cod' ) . '" style="color:#D32F2F;">🚫</span>';
		}
		echo '</td>';

		echo '<td><a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a></td>';
		echo '<td>' . esc_html( $city ) . ( $barangay ? '<br><span style="font-size:11px;color:#888;">' . esc_html( $barangay ) . '</span>' : '' ) . '</td>';
		echo '<td><strong>₱' . esc_html( number_format( (float) $total, 2 ) ) . '</strong></td>';
		echo '<td><span class="sc-status sc-status--' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( str_replace( '-', ' ', $status_class ) ) ) . '</span></td>';
		echo '<td><span class="sc-risk-badge sc-risk-badge--' . esc_attr( $risk_class ) . '">' . esc_html( $risk_label ) . '</span>';
		if ( $cancel_rate > 0 ) {
			echo '<br><span style="font-size:10px;color:#888;">' . esc_html( sprintf( '%d%% cancel rate', $cancel_rate ) ) . '</span>';
		}
		echo '</td>';

		echo '<td class="sc-order-actions">';
		if ( 'pending' === $status ) {
			echo '<button class="button button-primary sc-action-btn" data-order="' . esc_attr( $order_id ) . '" data-action="processing">'
				. esc_html__( 'Confirm', 'swiftcart-cod' ) . '</button> ';
		}

		echo '<a href="' . esc_url( get_edit_post_link( $order_id ) ) . '" class="button">'
			. esc_html__( 'View', 'swiftcart-cod' ) . '</a> ';

		if ( ! in_array( $status, array( 'completed', 'cancelled', 'sc-returned' ), true ) ) {
			echo '<button class="button sc-action-btn" data-order="' . esc_attr( $order_id ) . '" data-action="cancelled" '
				. 'onclick="return confirm(\'' . esc_js( __( 'Cancel this order?', 'swiftcart-cod' ) ) . '\')" '
				. 'style="color:#D32F2F;">' . esc_html__( 'Cancel', 'swiftcart-cod' ) . '</button>';
		}
		echo '</td>';

		echo '</tr>';
	}

	/**
	 * AJAX handler: update order status inline.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function ajax_update_order_status(): void {
		check_ajax_referer( 'sc_admin', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'swiftcart-cod' ) ), 403 );
		}

		$order_id = absint( $_POST['order_id'] ?? 0 );
		$status   = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'swiftcart-cod' ) ), 404 );
		}

		$order->update_status( $status, __( 'Status updated via SwiftCart dashboard.', 'swiftcart-cod' ) );

		wp_send_json_success( array( 'status' => $order->get_status() ) );
	}

	/**
	 * AJAX handler: toggle customer blacklist flag on an order.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function ajax_toggle_blacklist(): void {
		check_ajax_referer( 'sc_admin', 'nonce' );

		if ( ! current_user_can( 'manage_swiftcart' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'swiftcart-cod' ) ), 403 );
		}

		$order_id = absint( $_POST['order_id'] ?? 0 );
		$flagged  = (bool) get_post_meta( $order_id, '_sc_blacklist_flag', true );

		update_post_meta( $order_id, '_sc_blacklist_flag', ! $flagged );

		wp_send_json_success( array( 'flagged' => ! $flagged ) );
	}

	/**
	 * Fetch today's KPI data for the 4-widget dashboard row.
	 *
	 * @since  1.0.0
	 * @return array<string,int|float>
	 */
	private function get_kpis(): array {
		$today_start = date( 'Y-m-d 00:00:00' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

		$today_ids = wc_get_orders( array(
			'date_created' => '>=' . strtotime( $today_start ),
			'limit'        => -1,
			'return'       => 'ids',
		) );

		$cod_expected  = 0.0;
		$return_req    = 0;

		foreach ( $today_ids as $id ) {
			$o = wc_get_order( $id );
			if ( ! $o instanceof \WC_Order ) {
				continue;
			}

			$s = $o->get_status();

			// COD expected = pending + out for delivery orders total.
			if ( in_array( $s, array( 'pending', 'processing', 'sc-packed', 'sc-dispatch' ), true ) ) {
				$cod_expected += (float) $o->get_total();
			}

			// Return requests = returned + cancelled today.
			if ( in_array( $s, array( 'cancelled', 'sc-returned' ), true ) ) {
				$return_req++;
			}
		}

		// All-time delivery stats.
		$all_ids = wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) );
		$delivered_total = count( wc_get_orders( array( 'status' => 'completed', 'limit' => -1, 'return' => 'ids' ) ) );
		$all_time_total  = count( $all_ids );
		$delivery_rate   = $all_time_total > 0 ? round( ( $delivered_total / $all_time_total ) * 100 ) : 0;

		return array(
			'today_total'     => count( $today_ids ),
			'cod_expected'    => $cod_expected,
			'delivery_rate'   => $delivery_rate,
			'delivered_total' => $delivered_total,
			'all_time_total'  => $all_time_total,
			'return_requests' => $return_req,
		);
	}

	/**
	 * Fetch filtered orders for the table.
	 *
	 * @since  1.0.0
	 * @return \WC_Order[]
	 */
	private function get_orders(): array {
		$args = array(
			'limit'  => 50,
			'return' => 'objects',
			'orderby' => 'date',
			'order'  => 'DESC',
		);

		$status_filter = sanitize_text_field( wp_unslash( $_GET['sc_status'] ?? '' ) );
		if ( '' !== $status_filter ) {
			$args['status'] = $status_filter;
		}

		$search = sanitize_text_field( wp_unslash( $_GET['sc_search'] ?? '' ) );
		if ( '' !== $search ) {
			if ( is_numeric( $search ) ) {
				$args['post__in'] = array( (int) $search );
			} else {
				$args['billing_phone'] = $search;
			}
		}

		$orders = wc_get_orders( $args );

		return array_filter(
			$orders,
			static fn( $o ) => $o instanceof \WC_Order
		);
	}

	/**
	 * Calculate a customer's cancellation rate by phone number.
	 *
	 * @since  1.0.0
	 * @param  string $phone Customer phone number.
	 * @return int Percentage (0–100).
	 */
	private function get_customer_cancel_rate( string $phone ): int {
		if ( '' === $phone ) {
			return 0;
		}

		$all = wc_get_orders( array(
			'billing_phone' => $phone,
			'limit'         => -1,
			'return'        => 'ids',
		) );

		if ( empty( $all ) ) {
			return 0;
		}

		$cancelled = wc_get_orders( array(
			'billing_phone' => $phone,
			'status'        => 'cancelled',
			'limit'         => -1,
			'return'        => 'ids',
		) );

		return (int) round( ( count( $cancelled ) / count( $all ) ) * 100 );
	}
}
