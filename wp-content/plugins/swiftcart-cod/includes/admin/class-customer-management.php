<?php
/**
 * Customer management admin page.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customer_Management
 *
 * @since 1.0.0
 */
class Customer_Management {

	/**
	 * Attach admin hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
	}

	/**
	 * Register the Customers submenu page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_menu_page(): void {
		add_submenu_page(
			'swiftcart',
			__( 'Customers', 'swiftcart-cod' ),
			__( 'Customers', 'swiftcart-cod' ),
			'manage_swiftcart',
			'swiftcart-customers',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the customers page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_swiftcart' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'swiftcart-cod' ) );
		}

		$search      = sanitize_text_field( wp_unslash( $_GET['sc_search'] ?? '' ) );
		$show_bl     = isset( $_GET['sc_blacklisted'] );
		$customers   = $this->get_customers( $search, $show_bl );

		?>
		<div class="wrap sc-admin-wrap">
			<h1><?php esc_html_e( 'Customers', 'swiftcart-cod' ); ?></h1>

			<form method="get" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
				<input type="hidden" name="page" value="swiftcart-customers">
				<input type="text" name="sc_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Name or phone…', 'swiftcart-cod' ); ?>">
				<label><input type="checkbox" name="sc_blacklisted" <?php checked( $show_bl ); ?>> <?php esc_html_e( 'Blacklisted only', 'swiftcart-cod' ); ?></label>
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'swiftcart-cod' ); ?></button>
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Customer', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Mobile', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Total Orders', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Cancellation Rate', 'swiftcart-cod' ); ?></th>
						<th><?php esc_html_e( 'Blacklist', 'swiftcart-cod' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $customers as $c ) : ?>
					<?php
					$rate_color  = $c['cancel_rate'] >= 30 ? '#F57C00' : '#2E7D32';
					$bl_color    = $c['blacklisted'] ? '#D32F2F' : '#888';
					$bl_label    = $c['blacklisted'] ? __( 'Blacklisted', 'swiftcart-cod' ) : __( 'Clear', 'swiftcart-cod' );
					?>
					<tr>
						<td><?php echo esc_html( $c['name'] ); ?></td>
						<td><a href="tel:<?php echo esc_attr( $c['phone'] ); ?>"><?php echo esc_html( $c['phone'] ); ?></a></td>
						<td><?php echo esc_html( $c['total_orders'] ); ?></td>
						<td><strong style="color:<?php echo esc_attr( $rate_color ); ?>;"><?php echo esc_html( $c['cancel_rate'] ); ?>%</strong></td>
						<td><span style="color:<?php echo esc_attr( $bl_color ); ?>;"><?php echo esc_html( $bl_label ); ?></span></td>
					</tr>
				<?php endforeach; ?>
				<?php if ( empty( $customers ) ) : ?>
					<tr><td colspan="5" style="text-align:center;padding:24px;color:#666;"><?php esc_html_e( 'No customers found.', 'swiftcart-cod' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Fetch customers as aggregated order data.
	 *
	 * @since  1.0.0
	 * @param  string $search     Search term.
	 * @param  bool   $bl_only    Show blacklisted only.
	 * @return array<int,array<string,mixed>>
	 */
	private function get_customers( string $search, bool $bl_only ): array {
		$args = array( 'limit' => -1, 'return' => 'objects' );
		if ( '' !== $search ) {
			$args['billing_phone'] = $search;
		}

		$orders = wc_get_orders( $args );
		$map    = array();

		foreach ( $orders as $order ) {
			if ( ! $order instanceof \WC_Order ) continue;
			$phone = $order->get_billing_phone();
			if ( '' === $phone ) continue;

			if ( ! isset( $map[ $phone ] ) ) {
				$map[ $phone ] = array(
					'name'         => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
					'phone'        => $phone,
					'total_orders' => 0,
					'cancelled'    => 0,
					'blacklisted'  => (bool) get_post_meta( $order->get_id(), '_sc_blacklist_flag', true ),
				);
			}

			$map[ $phone ]['total_orders']++;
			if ( 'cancelled' === $order->get_status() ) {
				$map[ $phone ]['cancelled']++;
			}
		}

		$customers = array_map( static function ( array $c ) {
			$c['cancel_rate'] = $c['total_orders'] > 0
				? (int) round( ( $c['cancelled'] / $c['total_orders'] ) * 100 )
				: 0;
			unset( $c['cancelled'] );
			return $c;
		}, $map );

		if ( $bl_only ) {
			$customers = array_filter( $customers, static fn( $c ) => $c['blacklisted'] );
		}

		usort( $customers, static fn( $a, $b ) => $b['total_orders'] <=> $a['total_orders'] );

		return array_values( $customers );
	}
}
