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
	 * Static cache for cancel rate memoization (ISS-012).
	 *
	 * @var array<string,int>
	 */
	private static array $cancel_rate_cache = [];

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
		add_submenu_page(
			'swiftcart-dashboard',
			__( 'Orders', 'swiftcart-cod' ),
			__( 'Orders', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart-orders',
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
		// Global admin theme — loads on ALL wp-admin pages.
		wp_enqueue_style(
			'motoshop-admin-theme',
			SWIFTCART_URL . 'assets/css/admin-theme.css',
			array(),
			(string) filemtime( SWIFTCART_DIR . 'assets/css/admin-theme.css' )
		);

		// SwiftCart-specific assets — only on plugin pages.
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

		// Verify nonce when filter params are present (ISS-021).
		if ( isset( $_GET['sc_status'] ) || isset( $_GET['sc_search'] ) || isset( $_GET['sc_paged'] ) ) {
			if ( ! isset( $_GET['_sc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_sc_nonce'] ) ), 'sc_orders_filter' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'swiftcart-cod' ) );
			}
		}

		$kpis      = $this->get_kpis();
		$paged     = max( 1, absint( $_GET['sc_paged'] ?? 1 ) );
		$per_page  = 50;
		$orders    = $this->get_orders( $paged, $per_page );
		$total     = $this->count_orders();
		$max_pages = max( 1, (int) ceil( $total / $per_page ) );

		?>
		<div class="wrap sc-admin-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'SwiftCart Orders', 'swiftcart-cod' ); ?></h1>
			<p class="sc-date-sub"><?php echo esc_html( wp_date( 'l, F j, Y' ) ); ?></p>

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
						<th class="sc-checkbox-col"><input type="checkbox" id="sc-select-all"></th>
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
					<tr class="sc-empty-row"><td colspan="9"><?php esc_html_e( 'No orders found.', 'swiftcart-cod' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $max_pages > 1 ) : ?>
			<div class="sc-pagination" style="display:flex;gap:8px;align-items:center;margin-top:16px;">
				<?php if ( $paged > 1 ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'sc_paged', $paged - 1 ), 'sc_orders_filter', '_sc_nonce' ) ); ?>" class="button"><?php esc_html_e( '← Previous', 'swiftcart-cod' ); ?></a>
				<?php endif; ?>
				<span class="sc-date-sub">
					<?php printf( esc_html__( 'Page %1$d of %2$d', 'swiftcart-cod' ), $paged, $max_pages ); ?>
				</span>
				<?php if ( $paged < $max_pages ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'sc_paged', $paged + 1 ), 'sc_orders_filter', '_sc_nonce' ) ); ?>" class="button"><?php esc_html_e( 'Next →', 'swiftcart-cod' ); ?></a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
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

		echo '<div class="sc-filters">';

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
		wp_nonce_field( 'sc_orders_filter', '_sc_nonce' );
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
		$barangay    = $order->get_meta( '_billing_barangay' );
		$total       = $order->get_total();
		$status      = $order->get_status();
		$is_flagged  = (bool) $order->get_meta( '_sc_blacklist_flag' );
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

		$row_class = $is_flagged ? ' class="sc-row--flagged"' : '';

		echo '<tr' . $row_class . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<td><input type="checkbox" name="sc_order_ids[]" value="' . esc_attr( $order_id ) . '"></td>';

		echo '<td><a href="' . esc_url( get_edit_post_link( $order_id ) ) . '"><strong>#SC-' . esc_html( $order_id ) . '</strong></a><br>'
			. '<span class="sc-order-date">' . esc_html( $order->get_date_created()?->date( 'M d, Y H:i' ) ?? '' ) . '</span></td>';

		echo '<td>' . esc_html( $customer );
		if ( $is_flagged ) {
			echo ' <span title="' . esc_attr__( 'Blacklisted', 'swiftcart-cod' ) . '" class="sc-blacklist-icon">🚫</span>';
		}
		echo '</td>';

		echo '<td><a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a></td>';
		echo '<td>' . esc_html( $city ) . ( $barangay ? '<br><span class="sc-barangay-sub">' . esc_html( $barangay ) . '</span>' : '' ) . '</td>';
		echo '<td><strong>₱' . esc_html( number_format( (float) $total, 2 ) ) . '</strong></td>';
		echo '<td><span class="sc-status sc-status--' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( str_replace( '-', ' ', $status_class ) ) ) . '</span></td>';
		echo '<td><span class="sc-risk-badge sc-risk-badge--' . esc_attr( $risk_class ) . '">' . esc_html( $risk_label ) . '</span>';
		if ( $cancel_rate > 0 ) {
			echo '<br><span class="sc-cancel-rate">' . esc_html( sprintf( '%d%% cancel rate', $cancel_rate ) ) . '</span>';
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
				. 'class="button sc-action-btn sc-cancel-btn">' . esc_html__( 'Cancel', 'swiftcart-cod' ) . '</button>';
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
		$order    = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'swiftcart-cod' ) ), 404 );
		}

		$flagged  = (bool) $order->get_meta( '_sc_blacklist_flag' );

		$order->update_meta_data( '_sc_blacklist_flag', ! $flagged );
		$order->save_meta_data();

		wp_send_json_success( array( 'flagged' => ! $flagged ) );
	}

	/**
	 * Fetch today's KPI data for the 4-widget dashboard row.
	 *
	 * @since  1.0.0
	 * @return array<string,int|float>
	 */
	private function get_kpis(): array {
		$today_start = wp_date( 'Y-m-d 00:00:00' );

		// Today's orders — load as objects to iterate (limited to today).
		$today_orders = wc_get_orders( array(
			'date_created' => '>=' . strtotime( $today_start ),
			'limit'        => -1,
			'return'       => 'objects',
		) );

		$today_total   = 0;
		$cod_expected   = 0.0;
		$return_req     = 0;

		foreach ( $today_orders as $o ) {
			if ( ! $o instanceof \WC_Order ) {
				continue;
			}

			$today_total++;
			$s = $o->get_status();

			if ( in_array( $s, array( 'pending', 'processing', 'sc-packed', 'sc-dispatch' ), true ) ) {
				$cod_expected += (float) $o->get_total();
			}

			if ( in_array( $s, array( 'cancelled', 'sc-returned' ), true ) ) {
				$return_req++;
			}
		}

		// All-time stats — count queries only, no object loading (ISS-013).
		$delivered_total = count( wc_get_orders( array( 'status' => 'completed', 'limit' => -1, 'return' => 'ids' ) ) );
		$all_time_total  = count( wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) ) );
		$delivery_rate   = $all_time_total > 0 ? round( ( $delivered_total / $all_time_total ) * 100 ) : 0;

		return array(
			'today_total'     => $today_total,
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
	 * @param  int $paged    Current page number.
	 * @param  int $per_page Orders per page.
	 * @return \WC_Order[]
	 */
	private function get_orders( int $paged = 1, int $per_page = 50 ): array {
		$args = array(
			'limit'   => $per_page,
			'paged'   => $paged,
			'return'  => 'objects',
			'orderby' => 'date',
			'order'   => 'DESC',
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
	 * Count total orders for current filters (pagination support).
	 *
	 * @since  1.5.0
	 * @return int
	 */
	private function count_orders(): int {
		$args = array(
			'limit'  => -1,
			'return' => 'ids',
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

		return count( wc_get_orders( $args ) );
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

		// ISS-012: Memoize per-request to avoid N+1 queries.
		if ( isset( self::$cancel_rate_cache[ $phone ] ) ) {
			return self::$cancel_rate_cache[ $phone ];
		}

		$all = wc_get_orders( array(
			'billing_phone' => $phone,
			'limit'         => -1,
			'return'        => 'ids',
		) );

		if ( empty( $all ) ) {
			self::$cancel_rate_cache[ $phone ] = 0;
			return 0;
		}

		$cancelled = wc_get_orders( array(
			'billing_phone' => $phone,
			'status'        => 'cancelled',
			'limit'         => -1,
			'return'        => 'ids',
		) );

		$rate = (int) round( ( count( $cancelled ) / count( $all ) ) * 100 );
		self::$cancel_rate_cache[ $phone ] = $rate;

		return $rate;
	}
}
