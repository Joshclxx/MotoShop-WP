<?php
/**
 * COD financial reports admin page.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class COD_Reports
 *
 * @since 1.0.0
 */
class COD_Reports {

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
	 * Register the COD Reports submenu page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_menu_page(): void {
		add_submenu_page(
			'swiftcart-dashboard',
			__( 'COD Reports', 'swiftcart-cod' ),
			__( 'COD Reports', 'swiftcart-cod' ),
			'view_swiftcart_reports',
			'swiftcart-reports',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the COD Reports page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'view_swiftcart_reports' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'swiftcart-cod' ) );
		}

		$range  = sanitize_text_field( wp_unslash( $_GET['sc_range'] ?? 'today' ) );
		$dates  = $this->get_date_range( $range );
		$stats  = $this->get_stats( $dates['start'], $dates['end'] );

		$collection_rate = ( $stats['collected'] + $stats['failed'] + $stats['returned'] ) > 0
			? round( ( $stats['collected'] / ( $stats['collected'] + $stats['failed'] + $stats['returned'] ) ) * 100, 1 )
			: 0;

		?>
		<div class="wrap sc-admin-wrap">
			<h1><?php esc_html_e( 'COD Reports', 'swiftcart-cod' ); ?></h1>

			<!-- Date range tabs -->
			<div class="sc-report-tabs">
				<?php foreach ( array( 'today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month' ) as $key => $label ) : ?>
					<a href="?page=swiftcart-reports&sc_range=<?php echo esc_attr( $key ); ?>"
					   class="button <?php echo ( $range === $key ) ? 'button-primary' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</div>

			<!-- KPI Cards -->
			<div class="sc-kpi-row">
				<div class="sc-kpi-card sc-kpi-card--blue">
					<div class="sc-kpi-card__value"><?php echo esc_html( $stats['total_orders'] ); ?></div>
					<div class="sc-kpi-card__label"><?php esc_html_e( 'Total Orders', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-kpi-card sc-kpi-card--green">
					<div class="sc-kpi-card__value">₱<?php echo esc_html( number_format( $stats['cod_collected_amount'], 2 ) ); ?></div>
					<div class="sc-kpi-card__label"><?php esc_html_e( 'COD Collected', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-kpi-card sc-kpi-card--amber">
					<div class="sc-kpi-card__value"><?php echo esc_html( $stats['pending'] ); ?></div>
					<div class="sc-kpi-card__label"><?php esc_html_e( 'Pending', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-kpi-card sc-kpi-card--red">
					<div class="sc-kpi-card__value"><?php echo esc_html( $stats['failed'] ); ?></div>
					<div class="sc-kpi-card__label"><?php esc_html_e( 'Failed', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-kpi-card">
					<div class="sc-kpi-card__value"><?php echo esc_html( $stats['returned'] ); ?></div>
					<div class="sc-kpi-card__label"><?php esc_html_e( 'Returned', 'swiftcart-cod' ); ?></div>
				</div>
				<div class="sc-kpi-card sc-kpi-card--<?php echo esc_attr( $collection_rate >= 80 ? 'green' : 'amber' ); ?>">
					<div class="sc-kpi-card__value"><?php echo esc_html( $collection_rate ); ?>%</div>
					<div class="sc-kpi-card__label"><?php esc_html_e( 'Collection Rate', 'swiftcart-cod' ); ?></div>
				</div>
			</div>

			<p class="sc-report-note">
				<?php esc_html_e( 'Collection Rate = Collected ÷ (Collected + Failed + Returned)', 'swiftcart-cod' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Resolve start/end timestamps for a named date range.
	 *
	 * @since  1.0.0
	 * @param  string $range Range key.
	 * @return array<string,int>
	 */
	private function get_date_range( string $range ): array {
		$now = time();

		$ranges = array(
			'today'      => array( strtotime( 'today midnight' ), $now ),
			'yesterday'  => array( strtotime( 'yesterday midnight' ), strtotime( 'today midnight' ) - 1 ),
			'this_week'  => array( strtotime( 'monday this week midnight' ), $now ),
			'this_month' => array( strtotime( 'first day of this month midnight' ), $now ),
		);

		return array(
			'start' => $ranges[ $range ][0] ?? $ranges['today'][0],
			'end'   => $ranges[ $range ][1] ?? $now,
		);
	}

	/**
	 * Aggregate order stats for a date range.
	 *
	 * @since  1.0.0
	 * @param  int $start Unix timestamp start.
	 * @param  int $end   Unix timestamp end.
	 * @return array<string,int|float>
	 */
	private function get_stats( int $start, int $end ): array {
		$orders = wc_get_orders( array(
			'date_created' => $start . '...' . $end,
			'limit'        => -1,
			'return'       => 'objects',
		) );

		$stats = array(
			'total_orders'        => count( $orders ),
			'collected'           => 0,
			'cod_collected_amount' => 0.0,
			'pending'             => 0,
			'failed'              => 0,
			'returned'            => 0,
		);

		foreach ( $orders as $order ) {
			if ( ! $order instanceof \WC_Order ) continue;
			$s = $order->get_status();
			if ( 'completed' === $s ) {
				$stats['collected']++;
				$stats['cod_collected_amount'] += (float) $order->get_total();
			} elseif ( in_array( $s, array( 'pending', 'processing', 'sc-packed', 'sc-dispatch' ), true ) ) {
				$stats['pending']++;
			} elseif ( 'cancelled' === $s ) {
				$stats['failed']++;
			} elseif ( 'sc-returned' === $s ) {
				$stats['returned']++;
			}
		}

		return $stats;
	}
}
