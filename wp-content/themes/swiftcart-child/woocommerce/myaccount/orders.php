<?php
/**
 * Orders — My Account page.
 *
 * Overrides WooCommerce default to add visual status timeline and
 * SwiftCart COD branded order cards.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

/**
 * Returns the pipeline step index (0-based) for a given WC status string.
 *
 * @param string $status WC order status (without 'wc-' prefix).
 * @return int
 */
function sc_get_timeline_step( string $status ): int {
	$map = array(
		'pending'    => 0,
		'processing' => 1,
		'sc-packed'  => 2,
		'sc-dispatch' => 3,
		'completed'  => 4,
		'on-hold'    => 1,
	);
	return $map[ $status ] ?? 0;
}

$timeline_steps = array(
	array( 'icon' => '📋', 'label' => __( 'Order Placed', 'swiftcart-cod' ) ),
	array( 'icon' => '✅', 'label' => __( 'Verified', 'swiftcart-cod' ) ),
	array( 'icon' => '📦', 'label' => __( 'Packed', 'swiftcart-cod' ) ),
	array( 'icon' => '🛵', 'label' => __( 'Out for Delivery', 'swiftcart-cod' ) ),
	array( 'icon' => '🏠', 'label' => __( 'Delivered', 'swiftcart-cod' ) ),
);

$status_labels = array(
	'pending'     => __( 'Pending', 'swiftcart-cod' ),
	'processing'  => __( 'Confirmed', 'swiftcart-cod' ),
	'sc-packed'   => __( 'Packed', 'swiftcart-cod' ),
	'sc-dispatch' => __( 'Out for Delivery', 'swiftcart-cod' ),
	'completed'   => __( 'Delivered', 'swiftcart-cod' ),
	'cancelled'   => __( 'Cancelled', 'swiftcart-cod' ),
	'refunded'    => __( 'Refunded', 'swiftcart-cod' ),
	'on-hold'     => __( 'On Hold', 'swiftcart-cod' ),
);

if ( $has_orders ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
?>
<div class="sc-orders-list">

	<?php
	$status_filter_map = array(
		'pending'     => 'sc-status--pending',
		'processing'  => 'sc-status--confirmed',
		'sc-packed'   => 'sc-status--packed',
		'sc-dispatch' => 'sc-status--dispatched',
		'completed'   => 'sc-status--delivered',
		'cancelled'   => 'sc-status--cancelled',
		'refunded'    => 'sc-status--returned',
		'on-hold'     => 'sc-status--pending',
	);

	foreach ( $customer_orders->orders as $customer_order ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$order  = wc_get_order( $customer_order );
		if ( ! $order ) {
			continue;
		}

		$order_id     = $order->get_id();
		$status       = $order->get_status();
		$status_class = $status_filter_map[ $status ] ?? 'sc-status--pending';
		$status_label = $status_labels[ $status ] ?? ucfirst( $status );
		$active_step  = sc_get_timeline_step( $status );
		$is_cancelled = in_array( $status, array( 'cancelled', 'refunded' ), true );
		$items        = $order->get_items();
		$item_count   = count( $items );
		$first_item   = $item_count > 0 ? current( $items ) : null;
	?>
	<div class="sc-order-card">

		<div class="sc-order-card__header">
			<div>
				<div class="sc-order-card__id">
					<?php
					printf(
						/* translators: %s: order ID */
						esc_html__( 'Order #%s', 'swiftcart-cod' ),
						esc_html( $order_id )
					);
					?>
				</div>
				<div class="sc-order-card__date">
					<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
				</div>
			</div>
		<div class="sc-order-card__right">
				<div class="sc-order-card__total">
					<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
				</div>
				<span class="sc-status <?php echo esc_attr( $status_class ); ?>">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</div>
		</div>

		<?php if ( $first_item ) : ?>
		<div class="sc-order-card__items-summary">
			<?php
			echo esc_html( $first_item->get_name() );
			if ( $item_count > 1 ) {
				printf(
					/* translators: %d: count */
					esc_html( _n( ' + %d other item', ' + %d other items', $item_count - 1, 'swiftcart-cod' ) ),
					absint( $item_count - 1 )
				);
			}
			?>
		</div>
		<?php endif; ?>

		<?php if ( ! $is_cancelled ) : ?>
		<div class="sc-order-timeline" aria-label="<?php esc_attr_e( 'Order progress', 'swiftcart-cod' ); ?>">
			<?php foreach ( $timeline_steps as $idx => $step ) :
				if ( $idx < $active_step ) {
					$state = 'done';
				} elseif ( $idx === $active_step ) {
					$state = 'active';
				} else {
					$state = '';
				}
			?>
			<div class="sc-timeline-step sc-timeline-step--<?php echo esc_attr( $state ); ?>" aria-label="<?php echo esc_attr( $step['label'] ); ?>">
				<div class="sc-timeline-dot">
					<?php if ( 'done' === $state ) : ?>✓<?php elseif ( 'active' === $state ) : echo esc_html( $step['icon'] ); else : echo esc_html( $idx + 1 ); endif; ?>
				</div>
				<div class="sc-timeline-label"><?php echo esc_html( $step['label'] ); ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php else : ?>
		<div class="sc-order-cancelled-notice">
			<?php esc_html_e( 'This order has been cancelled.', 'swiftcart-cod' ); ?>
		</div>
		<?php endif; ?>

		<div class="sc-order-card__actions">
			<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="sc-btn sc-btn--ghost sc-btn--sm">
				<?php esc_html_e( 'View Details', 'swiftcart-cod' ); ?>
			</a>
			<?php if ( $order->needs_payment() ) : ?>
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="sc-btn sc-btn--primary sc-btn--sm">
					<?php esc_html_e( 'Pay Now', 'swiftcart-cod' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $order->has_status( array( 'pending', 'on-hold' ) ) ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cancel_order', 'true', $order->get_cancel_order_url_raw() ), 'woocommerce-cancel_order' ) ); ?>"
				   class="sc-btn sc-btn--sm sc-btn--cancel"
				   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to cancel this order?', 'swiftcart-cod' ); ?>')">
					<?php esc_html_e( 'Cancel', 'swiftcart-cod' ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>
	<?php endforeach; ?>

</div>

<?php if ( 1 < $customer_orders->max_num_pages ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
<div class="sc-orders-pagination">
	<?php if ( 1 !== (int) $current_page ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
		<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>" class="sc-btn sc-btn--ghost sc-btn--sm">← <?php esc_html_e( 'Previous', 'swiftcart-cod' ); ?></a>
	<?php else : ?>
		<span></span>
	<?php endif; ?>
	<?php if ( (int) $current_page < (int) $customer_orders->max_num_pages ) : ?>
		<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>" class="sc-btn sc-btn--ghost sc-btn--sm"><?php esc_html_e( 'Next', 'swiftcart-cod' ); ?> →</a>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php else : ?>
<div class="sc-orders-empty">
	<div class="sc-orders-empty__icon">📦</div>
	<p class="sc-orders-empty__title"><?php esc_html_e( 'No orders yet', 'swiftcart-cod' ); ?></p>
	<p class="sc-orders-empty__desc"><?php esc_html_e( 'Your orders will appear here once you start shopping.', 'swiftcart-cod' ); ?></p>
	<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-btn sc-btn--primary">
		<?php esc_html_e( 'Start Shopping', 'swiftcart-cod' ); ?>
	</a>
</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
