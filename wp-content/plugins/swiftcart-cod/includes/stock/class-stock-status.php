<?php
/**
 * Product stock status labels.
 *
 * Decouples the customer-facing stock label (In Stock / Low Stock / Out of Stock)
 * from WooCommerce's exact quantity management. Warehouse staff set labels via
 * a dedicated metabox — no quantity numbers are ever shown to customers.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Stock;

defined( 'ABSPATH' ) || exit;

/**
 * Class Stock_Status
 *
 * @since 1.0.0
 */
class Stock_Status {

	/**
	 * Valid status slugs.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	const STATUSES = array( 'in_stock', 'low_stock', 'out_of_stock' );

	/**
	 * Post meta key for the custom stock label.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const META_KEY = '_sc_stock_status';

	/**
	 * Attach hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		// Admin metabox.
		add_action( 'add_meta_boxes',        array( $this, 'add_metabox' ) );
		add_action( 'save_post_product',     array( $this, 'save_metabox' ) );

		// Storefront badge on product loops and single pages.
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'render_loop_badge' ), 5 );
		add_action( 'woocommerce_single_product_summary',     array( $this, 'render_single_badge' ), 15 );

		// Replace WooCommerce's built-in availability text (hides exact quantity phrases).
		add_filter( 'woocommerce_get_availability', array( $this, 'filter_availability' ), 10, 2 );

		// Admin product list column.
		add_filter( 'manage_product_posts_columns',       array( $this, 'add_list_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_list_column' ), 10, 2 );
	}

	/**
	 * Register the SwiftCart Stock Status metabox on the product edit screen.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_metabox(): void {
		add_meta_box(
			'sc_stock_status',
			__( 'SwiftCart Stock Status', 'swiftcart-cod' ),
			array( $this, 'render_metabox' ),
			'product',
			'side',
			'high'
		);
	}

	/**
	 * Render the stock status metabox HTML.
	 *
	 * @since  1.0.0
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'sc_stock_status_save', 'sc_stock_nonce' );

		$current = get_post_meta( $post->ID, self::META_KEY, true );
		if ( ! in_array( $current, self::STATUSES, true ) ) {
			$current = 'in_stock';
		}

		$options = array(
			'in_stock'     => array(
				'label' => __( 'In Stock', 'swiftcart-cod' ),
				'color' => '#2E7D32',
			),
			'low_stock'    => array(
				'label' => __( 'Low Stock', 'swiftcart-cod' ),
				'color' => '#F57C00',
			),
			'out_of_stock' => array(
				'label' => __( 'Out of Stock', 'swiftcart-cod' ),
				'color' => '#D32F2F',
			),
		);

		echo '<p style="font-size:12px;color:#555;margin-bottom:10px;">';
		echo esc_html__( 'Sets the label shown to customers. No quantity numbers are displayed.', 'swiftcart-cod' );
		echo '</p>';

		foreach ( $options as $slug => $opts ) {
			$checked = checked( $current, $slug, false );
			printf(
				'<label style="display:block;margin-bottom:8px;cursor:pointer;">'
				. '<input type="radio" name="sc_stock_status" value="%s" %s style="margin-right:6px;">'
				. '<strong style="color:%s;">%s</strong>'
				. '</label>',
				esc_attr( $slug ),
				$checked, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_attr( $opts['color'] ),
				esc_html( $opts['label'] )
			);
		}
	}

	/**
	 * Save the stock status metabox value.
	 *
	 * @since  1.0.0
	 * @param  int $post_id Post ID being saved.
	 * @return void
	 */
	public function save_metabox( int $post_id ): void {
		if ( ! isset( $_POST['sc_stock_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sc_stock_nonce'] ) ), 'sc_stock_status_save' )
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$status = sanitize_text_field( wp_unslash( $_POST['sc_stock_status'] ?? 'in_stock' ) );
		if ( ! in_array( $status, self::STATUSES, true ) ) {
			$status = 'in_stock';
		}

		update_post_meta( $post_id, self::META_KEY, $status );
	}

	/**
	 * Render the stock badge in the product loop (shop/category page).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_loop_badge(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		echo $this->get_badge_html( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the stock badge on the single product page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_single_badge(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		echo $this->get_badge_html( $product->get_id(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Override WooCommerce availability text to prevent quantity exposure.
	 *
	 * @since  1.0.0
	 * @param  array<string,string> $availability Availability array (class, availability).
	 * @param  \WC_Product          $product      Current product.
	 * @return array<string,string>
	 */
	public function filter_availability( array $availability, \WC_Product $product ): array {
		$status = $this->get_status( $product->get_id() );

		$map = array(
			'in_stock'     => array(
				'availability' => __( 'In Stock', 'swiftcart-cod' ),
				'class'        => 'in-stock',
			),
			'low_stock'    => array(
				'availability' => __( 'Low Stock — Order Soon', 'swiftcart-cod' ),
				'class'        => 'low-stock',
			),
			'out_of_stock' => array(
				'availability' => __( 'Out of Stock', 'swiftcart-cod' ),
				'class'        => 'out-of-stock',
			),
		);

		return $map[ $status ] ?? $availability;
	}

	/**
	 * Add a Stock Status column to the product admin list table.
	 *
	 * @since  1.0.0
	 * @param  array<string,string> $columns Existing columns.
	 * @return array<string,string>
	 */
	public function add_list_column( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'name' === $key ) {
				$new['sc_stock'] = __( 'Stock Status', 'swiftcart-cod' );
			}
		}

		return $new;
	}

	/**
	 * Render the custom Stock Status column value.
	 *
	 * @since  1.0.0
	 * @param  string $column  Column slug.
	 * @param  int    $post_id Post ID.
	 * @return void
	 */
	public function render_list_column( string $column, int $post_id ): void {
		if ( 'sc_stock' !== $column ) {
			return;
		}

		echo $this->get_badge_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the stock status badge HTML for a product.
	 *
	 * @since  1.0.0
	 * @param  int  $product_id  Product ID.
	 * @param  bool $large       Whether to use the large pill variant.
	 * @return string
	 */
	public function get_badge_html( int $product_id, bool $large = false ): string {
		$status = $this->get_status( $product_id );

		$map = array(
			'in_stock'     => array( 'label' => __( 'In Stock', 'swiftcart-cod' ),     'class' => 'in-stock' ),
			'low_stock'    => array( 'label' => __( 'Low Stock', 'swiftcart-cod' ),    'class' => 'low-stock' ),
			'out_of_stock' => array( 'label' => __( 'Out of Stock', 'swiftcart-cod' ), 'class' => 'out-of-stock' ),
		);

		$info  = $map[ $status ] ?? $map['out_of_stock'];
		$style = $large ? 'font-size:16px;padding:6px 16px;' : '';

		return sprintf(
			'<span class="sc-stock-badge sc-stock-badge--%s" style="%s">%s</span>',
			esc_attr( str_replace( '_', '-', $status ) ),
			esc_attr( $style ),
			esc_html( $info['label'] )
		);
	}

	/**
	 * Get the sc_stock_status meta for a product, defaulting to in_stock.
	 *
	 * @since  1.0.0
	 * @param  int $product_id Product ID.
	 * @return string
	 */
	public function get_status( int $product_id ): string {
		$status = get_post_meta( $product_id, self::META_KEY, true );

		return in_array( $status, self::STATUSES, true ) ? $status : 'in_stock';
	}
}
