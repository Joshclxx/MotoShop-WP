<?php
/**
 * My Account Dashboard — MotoShop Parts
 *
 * Custom dashboard with welcome card, quick-action grid, and recent orders.
 * Replaces the default WooCommerce plain-text greeting.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user         = wp_get_current_user();
$display_name = $user->display_name ?: $user->user_login;
$first_name   = $user->first_name ?: $display_name;
$member_since = date_i18n( 'F Y', strtotime( $user->user_registered ) );
$avatar_url   = get_avatar_url( $user->ID, array( 'size' => 96 ) );

// Recent orders (last 3).
$recent_orders = wc_get_orders( array(
	'customer' => $user->ID,
	'limit'    => 3,
	'orderby'  => 'date',
	'order'    => 'DESC',
	'status'   => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-sc-packed', 'wc-sc-dispatch' ),
) );

$status_labels = array(
	'pending'     => __( 'Pending', 'swiftcart-cod' ),
	'processing'  => __( 'Confirmed', 'swiftcart-cod' ),
	'sc-packed'   => __( 'Packed', 'swiftcart-cod' ),
	'sc-dispatch' => __( 'Out for Delivery', 'swiftcart-cod' ),
	'completed'   => __( 'Delivered', 'swiftcart-cod' ),
	'cancelled'   => __( 'Cancelled', 'swiftcart-cod' ),
	'on-hold'     => __( 'On Hold', 'swiftcart-cod' ),
);

$status_icons = array(
	'pending'     => '⏳',
	'processing'  => '✅',
	'sc-packed'   => '📦',
	'sc-dispatch' => '🛵',
	'completed'   => '🏠',
	'cancelled'   => '❌',
	'on-hold'     => '⏸',
);
?>

<!-- ═══ Welcome Card ═══ -->
<div class="sc-dash-welcome">

	<div class="sc-dash-welcome__info">
		<h2 class="sc-dash-welcome__greeting">
			<?php
			printf(
				/* translators: %s: customer first name */
				esc_html__( 'Welcome back, %s!', 'swiftcart-cod' ),
				'<span>' . esc_html( $first_name ) . '</span>'
			);
			?>
		</h2>
		<p class="sc-dash-welcome__meta">
			<?php
			printf(
				/* translators: %s: member since date */
				esc_html__( 'Member since %s', 'swiftcart-cod' ),
				esc_html( $member_since )
			);
			?>
			<span class="sc-dash-welcome__separator">·</span>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>">
				<?php esc_html_e( 'Edit Profile', 'swiftcart-cod' ); ?>
			</a>
		</p>
	</div>

</div>

<!-- ═══ Quick Actions Grid ═══ -->
<div class="sc-dash-actions">
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="sc-dash-action-card">
		<span class="sc-dash-action-card__label"><?php esc_html_e( 'My Orders', 'swiftcart-cod' ); ?></span>
		<span class="sc-dash-action-card__desc"><?php esc_html_e( 'Track, return, or buy again', 'swiftcart-cod' ); ?></span>
	</a>
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>" class="sc-dash-action-card">
		<span class="sc-dash-action-card__label"><?php esc_html_e( 'Addresses', 'swiftcart-cod' ); ?></span>
		<span class="sc-dash-action-card__desc"><?php esc_html_e( 'Shipping & billing info', 'swiftcart-cod' ); ?></span>
	</a>
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>" class="sc-dash-action-card">
		<span class="sc-dash-action-card__label"><?php esc_html_e( 'Account Details', 'swiftcart-cod' ); ?></span>
		<span class="sc-dash-action-card__desc"><?php esc_html_e( 'Name, email & password', 'swiftcart-cod' ); ?></span>
	</a>
	<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-dash-action-card sc-dash-action-card--cta">
		<span class="sc-dash-action-card__label"><?php esc_html_e( 'Continue Shopping', 'swiftcart-cod' ); ?></span>
		<span class="sc-dash-action-card__desc"><?php esc_html_e( 'Browse motorcycle parts', 'swiftcart-cod' ); ?></span>
	</a>
</div>

<!-- ═══ Recent Orders ═══ -->
<div class="sc-dash-recent">
	<div class="sc-dash-recent__header">
		<h3 class="sc-dash-recent__title">
			<?php esc_html_e( 'Recent Orders', 'swiftcart-cod' ); ?>
		</h3>
		<?php if ( ! empty( $recent_orders ) ) : ?>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="sc-dash-recent__view-all">
				<?php esc_html_e( 'View All →', 'swiftcart-cod' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $recent_orders ) ) : ?>
		<div class="sc-dash-recent__list">
			<?php foreach ( $recent_orders as $order ) :
				$order_id     = $order->get_id();
				$status       = $order->get_status();
				$status_label = $status_labels[ $status ] ?? ucfirst( $status );
				$status_icon  = $status_icons[ $status ] ?? '📋';
				$items        = $order->get_items();
				$item_count   = count( $items );
				$first_item   = $item_count > 0 ? current( $items ) : null;
			?>
				<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="sc-dash-recent-order">

					<div class="sc-dash-recent-order__info">
						<div class="sc-dash-recent-order__id">
							<?php
							printf(
								/* translators: %s: order ID */
								esc_html__( 'Order #%s', 'swiftcart-cod' ),
								esc_html( $order_id )
							);
							?>
						</div>
						<div class="sc-dash-recent-order__items">
							<?php
							if ( $first_item ) {
								echo esc_html( $first_item->get_name() );
								if ( $item_count > 1 ) {
									printf(
										/* translators: %d: count */
										esc_html( _n( ' + %d other', ' + %d others', $item_count - 1, 'swiftcart-cod' ) ),
										absint( $item_count - 1 )
									);
								}
							}
							?>
						</div>
					</div>
					<div class="sc-dash-recent-order__right">
						<div class="sc-dash-recent-order__total">
							<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
						</div>
						<div class="sc-dash-recent-order__status sc-dash-recent-order__status--<?php echo esc_attr( $status ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="sc-dash-recent__empty">

			<p class="sc-dash-recent__empty-title"><?php esc_html_e( 'No orders yet', 'swiftcart-cod' ); ?></p>
			<p class="sc-dash-recent__empty-desc"><?php esc_html_e( 'Start browsing quality motorcycle parts — COD accepted!', 'swiftcart-cod' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-btn sc-btn--primary">
				<?php esc_html_e( 'Shop Now', 'swiftcart-cod' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
