<?php
/**
 * WooCommerce product archive template.
 *
 * Forces the classic WooCommerce product loop (instead of Gutenberg blocks)
 * so that our content-product.php template override is used for product cards.
 *
 * @package SwiftCart
 * @since   1.3.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="sc-shop-page sc-container">

	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<h1 class="sc-shop-page__title"><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>

	<?php
	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );
	?>

	<?php if ( woocommerce_product_loop() ) : ?>

		<?php
		/**
		 * Hook: woocommerce_before_shop_loop.
		 *
		 * @hooked woocommerce_output_all_notices - 10
		 * @hooked woocommerce_result_count - 20
		 * @hooked woocommerce_catalog_ordering - 30
		 */
		do_action( 'woocommerce_before_shop_loop' );
		?>

		<?php woocommerce_product_loop_start(); ?>

		<?php
		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		}
		?>

		<?php woocommerce_product_loop_end(); ?>

		<?php
		/**
		 * Hook: woocommerce_after_shop_loop.
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_shop_loop' );
		?>

	<?php else : ?>

		<?php
		/**
		 * Hook: woocommerce_no_products_found.
		 *
		 * @hooked wc_no_products_found - 10
		 */
		do_action( 'woocommerce_no_products_found' );
		?>

	<?php endif; ?>

</div>

<?php
get_footer();
