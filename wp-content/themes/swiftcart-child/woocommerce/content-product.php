<?php
/**
 * Product card template for shop loops.
 *
 * Overrides WooCommerce default to provide a branded card layout with
 * dual CTA buttons (Buy Now + Add to Cart), stock badges, and COD pill.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$product_id   = $product->get_id();
$permalink    = $product->get_permalink();
$title        = $product->get_name();
$price_html   = $product->get_price_html();
$image        = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'sc-product-card__img' ) );
$stock_status = get_post_meta( $product_id, '_sc_stock_status', true ) ?: 'in_stock';
$is_on_sale   = $product->is_on_sale();
$is_in_stock  = $product->is_in_stock();

// Stock badge map.
$badge_map = array(
	'in_stock'     => array( 'class' => 'in-stock',     'label' => __( 'In Stock', 'swiftcart-cod' ) ),
	'low_stock'    => array( 'class' => 'low-stock',    'label' => __( 'Low Stock', 'swiftcart-cod' ) ),
	'out_of_stock' => array( 'class' => 'out-of-stock', 'label' => __( 'Out of Stock', 'swiftcart-cod' ) ),
);
$badge = $badge_map[ $stock_status ] ?? $badge_map['in_stock'];

// Add-to-cart URL.
$atc_url = $product->add_to_cart_url();
$buy_now_url = add_query_arg( array( 'add-to-cart' => $product_id, 'buy-now' => '1' ), $permalink );
?>
<li <?php wc_product_class( 'sc-product-card', $product ); ?>>

	<!-- Image -->
	<a href="<?php echo esc_url( $permalink ); ?>" class="sc-product-card__image-wrap">
		<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( $is_on_sale ) : ?>
			<span class="sc-product-card__badge" style="background:#D32F2F;color:#fff;">
				<?php esc_html_e( 'Sale', 'swiftcart-cod' ); ?>
			</span>
		<?php endif; ?>
	</a>

	<!-- Body -->
	<div class="sc-product-card__body">

		<!-- Stock Badge -->
		<span class="sc-stock-badge sc-stock-badge--<?php echo esc_attr( $badge['class'] ); ?>">
			<?php echo esc_html( $badge['label'] ); ?>
		</span>

		<!-- Title -->
		<a href="<?php echo esc_url( $permalink ); ?>" class="sc-product-card__name">
			<?php echo esc_html( $title ); ?>
		</a>

		<!-- Price -->
		<div class="sc-product-card__price">
			<?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<!-- COD Badge -->
		<span class="sc-cod-badge" style="font-size:11px;padding:3px 8px;">COD</span>

		<!-- Dual CTA Buttons -->
		<?php if ( $is_in_stock ) : ?>
		<div class="sc-product-card__actions">
			<a href="<?php echo esc_url( $buy_now_url ); ?>" class="sc-btn--buy-now">
				<?php esc_html_e( 'Buy Now', 'swiftcart-cod' ); ?>
			</a>
			<?php if ( $product->is_type( 'simple' ) ) : ?>
				<a href="<?php echo esc_url( $atc_url ); ?>"
				   data-quantity="1"
				   data-product_id="<?php echo esc_attr( $product_id ); ?>"
				   class="sc-btn--add-to-cart add_to_cart_button ajax_add_to_cart"
				   aria-label="<?php echo esc_attr( sprintf( __( 'Add "%s" to cart', 'swiftcart-cod' ), $title ) ); ?>">
					<?php esc_html_e( 'Add to Cart', 'swiftcart-cod' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="sc-btn--add-to-cart">
					<?php esc_html_e( 'Select Options', 'swiftcart-cod' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php else : ?>
		<div class="sc-product-card__actions" style="grid-template-columns:1fr;">
			<a href="<?php echo esc_url( $permalink ); ?>" class="sc-btn--add-to-cart" style="grid-column:1/-1;">
				<?php esc_html_e( 'View Product', 'swiftcart-cod' ); ?>
			</a>
		</div>
		<?php endif; ?>

	</div>

</li>
