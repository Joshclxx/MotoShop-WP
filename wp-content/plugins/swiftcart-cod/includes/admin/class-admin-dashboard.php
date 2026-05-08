<?php
/**
 * MoTo Shop Admin Dashboard.
 *
 * Branded overview page with KPIs, quick actions, recent orders,
 * low stock alerts, and top sellers.
 *
 * @package SwiftCart
 * @since   1.1.0
 */

declare( strict_types=1 );

namespace SwiftCart\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin_Dashboard
 *
 * @since 1.1.0
 */
class Admin_Dashboard {

	/** @var int Low-stock threshold. */
	private const LOW_STOCK_THRESHOLD = 5;

	/**
	 * Attach hooks.
	 *
	 * @since  1.1.0
	 * @return void
	 */
	public function register(): void {
		// Priority 5 → registers the parent menu BEFORE other SwiftCart submenus.
		add_action( 'admin_menu', array( $this, 'register_menu_page' ), 5 );
	}

	/**
	 * Register the SwiftCart parent menu with Dashboard as the landing page.
	 *
	 * @since  1.1.0
	 * @return void
	 */
	public function register_menu_page(): void {
		// Create the parent menu — Dashboard is the default page.
		add_menu_page(
			__( 'SwiftCart', 'swiftcart-cod' ),
			__( 'SwiftCart', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart-dashboard',
			array( $this, 'render_page' ),
			'dashicons-cart',
			55
		);

		// First submenu replaces the auto-generated parent submenu label.
		add_submenu_page(
			'swiftcart-dashboard',
			__( 'Dashboard', 'swiftcart-cod' ),
			__( 'Dashboard', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart-dashboard',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the Admin Dashboard page.
	 *
	 * @since  1.1.0
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_swiftcart' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'swiftcart-cod' ) );
		}

		$user = wp_get_current_user();
		$kpis = $this->get_dashboard_kpis();
		$recent_orders   = $this->get_recent_orders();
		$low_stock       = $this->get_low_stock_products();
		$top_sellers     = $this->get_top_sellers();

		?>
		<div class="wrap sc-admin-wrap sc-dashboard">

			<!-- ── Branded Header ── -->
			<div class="sc-dash-header">
				<div class="sc-dash-header__left">
					<h1 class="sc-dash-header__title">🏍️ MoTo Shop HQ</h1>
					<p class="sc-dash-header__sub">
						<?php
						printf(
							/* translators: %s: admin display name */
							esc_html__( 'Welcome back, %s', 'swiftcart-cod' ),
							esc_html( $user->display_name )
						);
						?>
						· <?php echo esc_html( wp_date( 'l, F j, Y' ) ); ?>
					</p>
				</div>
			</div>

			<!-- ── KPI Widgets ── -->
			<div class="sc-widgets-row">
				<div class="sc-widget sc-widget--orange">
					<div class="sc-widget__icon">📦</div>
					<div class="sc-widget__value"><?php echo esc_html( (string) $kpis['today_orders'] ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( "Today's Orders", 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub"><?php esc_html_e( 'Orders placed today', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-widget sc-widget--green">
					<div class="sc-widget__icon">💰</div>
					<div class="sc-widget__value">₱<?php echo esc_html( number_format( $kpis['today_revenue'], 0 ) ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( "Today's Revenue", 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub"><?php esc_html_e( 'Total sales today', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-widget sc-widget--amber">
					<div class="sc-widget__icon">⏳</div>
					<div class="sc-widget__value"><?php echo esc_html( (string) $kpis['pending_cod'] ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( 'Pending COD', 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub"><?php esc_html_e( 'Awaiting delivery', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-widget sc-widget--red">
					<div class="sc-widget__icon">⚠️</div>
					<div class="sc-widget__value"><?php echo esc_html( (string) $kpis['low_stock'] ); ?></div>
					<div class="sc-widget__label"><?php esc_html_e( 'Low Stock Items', 'swiftcart-cod' ); ?></div>
					<div class="sc-widget__sub">
						<?php
						printf(
							/* translators: %d: stock threshold */
							esc_html__( 'Stock ≤ %d units', 'swiftcart-cod' ),
							self::LOW_STOCK_THRESHOLD
						);
						?>
					</div>
				</div>
			</div>

			<!-- ── Quick Actions ── -->
			<div class="sc-dash-section">
				<h2 class="sc-dash-section__title"><?php esc_html_e( 'Quick Actions', 'swiftcart-cod' ); ?></h2>
				<div class="sc-dash-actions-grid">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="sc-dash-action">
						<span class="sc-dash-action__icon">➕</span>
						<span class="sc-dash-action__label"><?php esc_html_e( 'Add Product', 'swiftcart-cod' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=swiftcart-orders' ) ); ?>" class="sc-dash-action">
						<span class="sc-dash-action__icon">📦</span>
						<span class="sc-dash-action__label"><?php esc_html_e( 'Manage Orders', 'swiftcart-cod' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=swiftcart-reports' ) ); ?>" class="sc-dash-action">
						<span class="sc-dash-action__icon">📊</span>
						<span class="sc-dash-action__label"><?php esc_html_e( 'COD Reports', 'swiftcart-cod' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=swiftcart-customers' ) ); ?>" class="sc-dash-action">
						<span class="sc-dash-action__icon">👥</span>
						<span class="sc-dash-action__label"><?php esc_html_e( 'Customers', 'swiftcart-cod' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=warehouse' ) ); ?>" class="sc-dash-action">
						<span class="sc-dash-action__icon">🏭</span>
						<span class="sc-dash-action__label"><?php esc_html_e( 'Warehouse', 'swiftcart-cod' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>" class="sc-dash-action">
						<span class="sc-dash-action__icon">⚙️</span>
						<span class="sc-dash-action__label"><?php esc_html_e( 'Settings', 'swiftcart-cod' ); ?></span>
					</a>
				</div>
			</div>

			<!-- ── Two-Column Layout: Recent Orders + Low Stock ── -->
			<div class="sc-dash-columns">

				<!-- Recent Orders -->
				<div class="sc-dash-section">
					<h2 class="sc-dash-section__title">
						<?php esc_html_e( 'Recent Orders', 'swiftcart-cod' ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=swiftcart-orders' ) ); ?>" class="sc-dash-section__link"><?php esc_html_e( 'View All →', 'swiftcart-cod' ); ?></a>
					</h2>
					<table class="wp-list-table widefat fixed striped sc-dash-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Order', 'swiftcart-cod' ); ?></th>
								<th><?php esc_html_e( 'Customer', 'swiftcart-cod' ); ?></th>
								<th><?php esc_html_e( 'Total', 'swiftcart-cod' ); ?></th>
								<th><?php esc_html_e( 'Status', 'swiftcart-cod' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $recent_orders ) ) : ?>
								<tr class="sc-empty-row"><td colspan="4"><?php esc_html_e( 'No recent orders.', 'swiftcart-cod' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $recent_orders as $order ) : ?>
									<?php
									$status_map = array(
										'pending'     => 'pending',
										'processing'  => 'confirmed',
										'sc-packed'   => 'packed',
										'sc-dispatch' => 'dispatched',
										'completed'   => 'delivered',
										'cancelled'   => 'cancelled',
										'sc-returned' => 'returned',
									);
									$status_class = $status_map[ $order->get_status() ] ?? 'pending';
									?>
									<tr>
										<td>
											<a href="<?php echo esc_url( get_edit_post_link( $order->get_id() ) ); ?>">
												<strong>#SC-<?php echo esc_html( (string) $order->get_id() ); ?></strong>
											</a><br>
											<span class="sc-order-date"><?php echo esc_html( $order->get_date_created()?->date( 'M d, H:i' ) ?? '' ); ?></span>
										</td>
										<td><?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ); ?></td>
										<td><strong>₱<?php echo esc_html( number_format( (float) $order->get_total(), 2 ) ); ?></strong></td>
										<td><span class="sc-status sc-status--<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $status_class ) ) ); ?></span></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<!-- Low Stock Alerts -->
				<div class="sc-dash-section">
					<h2 class="sc-dash-section__title">
						<?php esc_html_e( 'Low Stock Alerts', 'swiftcart-cod' ); ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>" class="sc-dash-section__link"><?php esc_html_e( 'All Products →', 'swiftcart-cod' ); ?></a>
					</h2>
					<table class="wp-list-table widefat fixed striped sc-dash-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Product', 'swiftcart-cod' ); ?></th>
								<th><?php esc_html_e( 'Stock', 'swiftcart-cod' ); ?></th>
								<th><?php esc_html_e( 'Action', 'swiftcart-cod' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $low_stock ) ) : ?>
								<tr class="sc-empty-row"><td colspan="3"><?php esc_html_e( 'All products are well stocked. 👍', 'swiftcart-cod' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $low_stock as $product ) : ?>
									<?php
									$stock_qty = (int) $product->get_stock_quantity();
									$stock_class = 0 === $stock_qty ? 'sc-stock--out' : 'sc-stock--low';
									?>
									<tr>
										<td><?php echo esc_html( $product->get_name() ); ?></td>
										<td><strong class="<?php echo esc_attr( $stock_class ); ?>"><?php echo esc_html( (string) $stock_qty ); ?></strong></td>
										<td><a href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'swiftcart-cod' ); ?></a></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

			</div>

			<!-- ── Top Selling Products ── -->
			<div class="sc-dash-section">
				<h2 class="sc-dash-section__title"><?php esc_html_e( 'Top Selling Products', 'swiftcart-cod' ); ?></h2>
				<table class="wp-list-table widefat fixed striped sc-dash-table">
					<thead>
						<tr>
							<th><?php esc_html_e( '#', 'swiftcart-cod' ); ?></th>
							<th><?php esc_html_e( 'Product', 'swiftcart-cod' ); ?></th>
							<th><?php esc_html_e( 'Units Sold', 'swiftcart-cod' ); ?></th>
							<th><?php esc_html_e( 'Price', 'swiftcart-cod' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $top_sellers ) ) : ?>
							<tr class="sc-empty-row"><td colspan="4"><?php esc_html_e( 'No sales data yet.', 'swiftcart-cod' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $top_sellers as $i => $product ) : ?>
								<tr>
									<td><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
									<td><a href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></td>
									<td><strong><?php echo esc_html( (string) $product->get_total_sales() ); ?></strong></td>
									<td>₱<?php echo esc_html( number_format( (float) $product->get_price(), 2 ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

		</div>
		<?php
	}

	/**
	 * Get KPI data for the dashboard widgets.
	 *
	 * @since  1.1.0
	 * @return array<string,int|float>
	 */
	private function get_dashboard_kpis(): array {
		$today_start = wp_date( 'Y-m-d 00:00:00' );

		// Today's orders.
		$today_orders = wc_get_orders( array(
			'date_created' => '>=' . strtotime( $today_start ),
			'limit'        => -1,
			'return'       => 'objects',
		) );

		$today_count   = 0;
		$today_revenue = 0.0;

		foreach ( $today_orders as $o ) {
			if ( ! $o instanceof \WC_Order ) {
				continue;
			}
			$today_count++;
			if ( ! in_array( $o->get_status(), array( 'cancelled', 'sc-returned', 'failed' ), true ) ) {
				$today_revenue += (float) $o->get_total();
			}
		}

		// Pending COD — orders not yet delivered.
		$pending_statuses = array( 'pending', 'processing', 'sc-packed', 'sc-dispatch' );
		$pending_count    = count( wc_get_orders( array(
			'status' => $pending_statuses,
			'limit'  => -1,
			'return' => 'ids',
		) ) );

		// Low stock products.
		$low_stock_count = $this->count_low_stock_products();

		return array(
			'today_orders'  => $today_count,
			'today_revenue' => $today_revenue,
			'pending_cod'   => $pending_count,
			'low_stock'     => $low_stock_count,
		);
	}

	/**
	 * Get the 5 most recent orders.
	 *
	 * @since  1.1.0
	 * @return \WC_Order[]
	 */
	private function get_recent_orders(): array {
		$orders = wc_get_orders( array(
			'limit'   => 5,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		) );

		return array_filter( $orders, static fn( $o ) => $o instanceof \WC_Order );
	}

	/**
	 * Get products with stock at or below threshold.
	 *
	 * @since  1.1.0
	 * @return \WC_Product[]
	 */
	private function get_low_stock_products(): array {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_manage_stock',
					'value'   => 'yes',
					'compare' => '=',
				),
				array(
					'key'     => '_stock',
					'value'   => self::LOW_STOCK_THRESHOLD,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
			),
			'orderby'  => 'meta_value_num',
			'meta_key' => '_stock',
			'order'    => 'ASC',
		);

		$query    = new \WP_Query( $args );
		$products = array();

		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			if ( $product instanceof \WC_Product ) {
				$products[] = $product;
			}
		}

		return $products;
	}

	/**
	 * Count low-stock products (for KPI widget).
	 *
	 * @since  1.1.0
	 * @return int
	 */
	private function count_low_stock_products(): int {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_manage_stock',
					'value'   => 'yes',
					'compare' => '=',
				),
				array(
					'key'     => '_stock',
					'value'   => self::LOW_STOCK_THRESHOLD,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
			),
		);

		$query = new \WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get top 5 selling products by total_sales meta.
	 *
	 * @since  1.1.0
	 * @return \WC_Product[]
	 */
	private function get_top_sellers(): array {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'meta_key'       => 'total_sales',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => 'total_sales',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		);

		$query    = new \WP_Query( $args );
		$products = array();

		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			if ( $product instanceof \WC_Product ) {
				$products[] = $product;
			}
		}

		return $products;
	}
}
