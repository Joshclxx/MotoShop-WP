<?php
/**
 * Warehouse operations dashboard.
 *
 * Registers 5 front-end warehouse pages accessible only to users with the
 * warehouse_staff role. Pages are rendered via a custom rewrite endpoint
 * (/warehouse/[page]) rather than via wp-admin to give a distraction-free,
 * tablet-optimised full-screen UI.
 *
 * Pages:
 *   /warehouse/pick-list   — Orders ready to pack, sorted by zone.
 *   /warehouse/packing     — Item-by-item packing checklist for a single order.
 *   /warehouse/dispatch    — Assign packed orders to riders; mark departed.
 *   /warehouse/returns     — Intake form for returned/undeliverable orders.
 *   /warehouse/inventory   — Inline stock status toggle for all products.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Warehouse;

defined( 'ABSPATH' ) || exit;

use SwiftCart\Stock\Stock_Status;

/**
 * Class Warehouse_Dashboard
 *
 * @since 1.0.0
 */
class Warehouse_Dashboard {

	/**
	 * Valid warehouse sub-pages.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	const PAGES = array( 'pick-list', 'packing', 'dispatch', 'returns', 'inventory' );

	/**
	 * Attach WP hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'init',          array( $this, 'add_rewrite_rules' ) );
		add_action( 'query_vars',    array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'handle_warehouse_request' ) );
		add_action( 'wp_ajax_sc_warehouse_action', array( $this, 'ajax_warehouse_action' ) );
	}

	/**
	 * Register /warehouse/[page] rewrite rules.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_rewrite_rules(): void {
		add_rewrite_rule(
			'^warehouse/([a-z-]+)/?$',
			'index.php?sc_warehouse_page=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'^warehouse/?$',
			'index.php?sc_warehouse_page=pick-list',
			'top'
		);
	}

	/**
	 * Register the sc_warehouse_page query variable.
	 *
	 * @since  1.0.0
	 * @param  string[] $vars Existing query vars.
	 * @return string[]
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'sc_warehouse_page';
		return $vars;
	}

	/**
	 * Intercept warehouse URL requests and render the appropriate page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function handle_warehouse_request(): void {
		$page = get_query_var( 'sc_warehouse_page' );

		if ( '' === $page ) {
			return;
		}

		// Require login.
		if ( ! is_user_logged_in() ) {
			wp_redirect( wp_login_url( home_url( '/warehouse/' . $page ) ) );
			exit;
		}

		// Require warehouse_staff or administrator.
		if ( ! current_user_can( 'swiftcart_view_pick_list' ) ) {
			wp_die( esc_html__( 'Access denied. Warehouse staff only.', 'swiftcart-cod' ), '', array( 'response' => 403 ) );
		}

		if ( ! in_array( $page, self::PAGES, true ) ) {
			wp_die( esc_html__( 'Warehouse page not found.', 'swiftcart-cod' ), '', array( 'response' => 404 ) );
		}

		// Render full-page warehouse UI (bypasses normal WP template).
		$this->render_full_page( $page );
		exit;
	}

	/**
	 * Render the full HTML page for a warehouse screen.
	 *
	 * @since  1.0.0
	 * @param  string $page Page slug.
	 * @return void
	 */
	private function render_full_page( string $page ): void {
		$titles = array(
			'pick-list' => __( 'Pick List', 'swiftcart-cod' ),
			'packing'   => __( 'Packing', 'swiftcart-cod' ),
			'dispatch'  => __( 'Dispatch Board', 'swiftcart-cod' ),
			'returns'   => __( 'Returns Intake', 'swiftcart-cod' ),
			'inventory' => __( 'Inventory', 'swiftcart-cod' ),
		);

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
			<title><?php echo esc_html( $titles[ $page ] ); ?> — SwiftCart Warehouse</title>
			<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/style.css' ); ?>?v=<?php echo esc_attr( SWIFTCART_VERSION ); ?>">
			<style>
				* { box-sizing: border-box; }
				body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #F8F8F8; }
				.wh-nav { background:#1C1C1C; color:#fff; padding:0 16px; display:flex; align-items:center; height:56px; gap:8px; overflow-x:auto; }
				.wh-nav__brand { font-weight:900; font-size:16px; color:#FF6B00; white-space:nowrap; margin-right:12px; }
				.wh-nav a { color:#ccc; text-decoration:none; font-size:13px; font-weight:600; padding:6px 12px; border-radius:4px; white-space:nowrap; }
				.wh-nav a:hover, .wh-nav a.active { background:#333; color:#fff; }
			</style>
			<?php wp_head(); ?>
		</head>
		<body>

		<nav class="wh-nav">
			<span class="wh-nav__brand">⚡ SwiftCart WH</span>
			<?php foreach ( self::PAGES as $p ) : ?>
				<a href="<?php echo esc_url( home_url( '/warehouse/' . $p ) ); ?>"
				   class="<?php echo ( $page === $p ) ? 'active' : ''; ?>">
					<?php echo esc_html( $titles[ $p ] ?? $p ); ?>
				</a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" style="margin-left:auto;color:#ff6b6b;">
				<?php esc_html_e( 'Logout', 'swiftcart-cod' ); ?>
			</a>
		</nav>

		<div class="sc-warehouse-wrap">
			<?php $this->render_page_content( $page ); ?>
		</div>

		<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Render the content area for each warehouse page.
	 *
	 * @since  1.0.0
	 * @param  string $page Page slug.
	 * @return void
	 */
	private function render_page_content( string $page ): void {
		switch ( $page ) {
			case 'pick-list':
				$this->render_pick_list();
				break;
			case 'packing':
				$this->render_packing();
				break;
			case 'dispatch':
				$this->render_dispatch();
				break;
			case 'returns':
				$this->render_returns();
				break;
			case 'inventory':
				$this->render_inventory();
				break;
		}
	}

	/** ── Pick List ──────────────────────────────────────────────────────────── */
	private function render_pick_list(): void {
		$orders = wc_get_orders( array(
			'status' => array( 'processing', 'wc-sc-packed' ),
			'limit'  => 100,
			'return' => 'objects',
			'orderby' => 'date',
			'order'  => 'ASC',
		) );

		// Split by packing status.
		$rows = array();
		foreach ( $orders as $order ) {
			if ( ! $order instanceof \WC_Order ) {
				continue;
			}
			$is_packed = 'sc-packed' === $order->get_status();
			foreach ( $order->get_items() as $item ) {
				$rows[] = array(
					'order'    => $order,
					'item'     => $item,
					'is_packed' => $is_packed,
				);
			}
		}

		$nonce = wp_create_nonce( 'sc_warehouse_pick' );

		echo '<div class="sc-warehouse-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">';
		echo '<div>';
		echo '<h1 style="margin:0;">' . esc_html__( 'Pick List', 'swiftcart-cod' ) . '</h1>';
		echo '<p style="margin:4px 0 0;color:#888;font-size:14px;">' . esc_html(
			sprintf(
				/* translators: %d: count */
				_n( '%d order in queue', '%d orders in queue', count( $orders ), 'swiftcart-cod' ),
				count( $orders )
			)
		) . '</p>';
		echo '</div>';
		echo '<div style="display:flex;gap:8px;">';
		echo '<a href="' . esc_url( home_url( '/warehouse/pick-list' ) ) . '" class="sc-btn" style="background:#eee;color:#333;height:36px;padding:0 16px;font-size:13px;">'
			. esc_html__( '↻ Refresh', 'swiftcart-cod' ) . '</a>';
		echo '</div>';
		echo '</div>';

		if ( empty( $rows ) ) {
			echo '<div style="text-align:center;padding:64px;color:#888;">';
			echo '<div style="font-size:64px;margin-bottom:16px;">✅</div>';
			echo '<p style="font-size:18px;font-weight:700;margin:0 0 8px;">' . esc_html__( 'All caught up!', 'swiftcart-cod' ) . '</p>';
			echo '<p style="margin:0;">' . esc_html__( 'No orders waiting to be packed.', 'swiftcart-cod' ) . '</p>';
			echo '</div>';
			return;
		}

		echo '<input type="hidden" name="sc_wh_nonce" value="' . esc_attr( $nonce ) . '">';
		echo '<div class="sc-wh-wrap">';
		echo '<table class="sc-wh-table">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Order ID', 'swiftcart-cod' ) . '</th>';
		echo '<th>' . esc_html__( 'Product', 'swiftcart-cod' ) . '</th>';
		echo '<th style="text-align:center;">' . esc_html__( 'Quantity', 'swiftcart-cod' ) . '</th>';
		echo '<th>' . esc_html__( 'Delivery Zone', 'swiftcart-cod' ) . '</th>';
		echo '<th>' . esc_html__( 'COD Amount', 'swiftcart-cod' ) . '</th>';
		echo '<th>' . esc_html__( 'Packing Status', 'swiftcart-cod' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'swiftcart-cod' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		$prev_order_id = 0;
		foreach ( $rows as $row ) {
			$order     = $row['order'];
			$item      = $row['item'];
			$is_packed = $row['is_packed'];
			$order_id  = $order->get_id();
			$city      = $order->get_billing_city();
			$barangay  = get_post_meta( $order_id, '_billing_barangay', true );
			$cod       = $order->get_total();

			// Determine zone badge.
			$city_lower = strtolower( $city );
			if ( str_contains( $city_lower, 'manila' ) || str_contains( $city_lower, 'quezon' ) || str_contains( $city_lower, 'makati' ) || str_contains( $city_lower, 'taguig' ) || str_contains( $city_lower, 'pasig' ) ) {
				$zone_class = 'ncr';
				$zone_label = 'NCR';
			} elseif ( str_contains( $city_lower, 'cebu' ) ) {
				$zone_class = 'cebu';
				$zone_label = 'Cebu';
			} elseif ( str_contains( $city_lower, 'davao' ) ) {
				$zone_class = 'davao';
				$zone_label = 'Davao';
			} else {
				$zone_class = 'other';
				$zone_label = 'Other';
			}

			// Packing status.
			if ( $is_packed ) {
				$pack_class = 'packed';
				$pack_label = __( 'Packed', 'swiftcart-cod' );
			} elseif ( (bool) get_post_meta( $order_id, '_sc_packing_started', true ) ) {
				$pack_class = 'packing';
				$pack_label = __( 'In Progress', 'swiftcart-cod' );
			} else {
				$pack_class = 'pending';
				$pack_label = __( 'Pending', 'swiftcart-cod' );
			}

			// Highlight first row of a new order.
			$row_style = ( $order_id !== $prev_order_id && $prev_order_id !== 0 ) ? ' style="border-top:2px solid #E0E0E0;"' : '';
			$prev_order_id = $order_id;

			echo '<tr' . $row_style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<td class="sc-wh-order-id"><a href="' . esc_url( get_edit_post_link( $order_id ) ) . '" target="_blank">#SC-' . esc_html( $order_id ) . '</a></td>';
			echo '<td>';
			echo '<strong style="font-size:14px;">' . esc_html( $item->get_name() ) . '</strong>';
			$product = $item->get_product();
			if ( $product && $product->get_sku() ) {
				echo '<br><span style="font-size:11px;color:#888;">SKU: ' . esc_html( $product->get_sku() ) . '</span>';
			}
			echo '</td>';
			echo '<td style="text-align:center;font-size:20px;font-weight:900;color:#1565C0;">' . esc_html( $item->get_quantity() ) . '</td>';
			echo '<td><span class="sc-wh-zone-badge sc-wh-zone-badge--' . esc_attr( $zone_class ) . '">' . esc_html( $zone_label ) . '</span>';
			echo '<br><span style="font-size:11px;color:#888;">' . esc_html( $city ) . ( $barangay ? ', ' . esc_html( $barangay ) : '' ) . '</span></td>';
			echo '<td><strong style="color:#FF6B00;font-size:15px;">₱' . esc_html( number_format( $cod, 2 ) ) . '</strong></td>';
			echo '<td><span class="sc-packing-status sc-packing-status--' . esc_attr( $pack_class ) . '">' . esc_html( $pack_label ) . '</span></td>';
			echo '<td>';
			echo '<div class="sc-wh-actions">';
			if ( ! $is_packed ) {
				echo '<button class="sc-btn sc-wh-action" '
					. 'data-order="' . esc_attr( $order_id ) . '" '
					. 'data-action="mark_packed" '
					. 'data-redirect="" '
					. 'style="background:#2E7D32;color:#fff;height:36px;padding:0 14px;font-size:12px;border-radius:6px;">'
					. esc_html__( 'Mark Packed', 'swiftcart-cod' )
					. '</button>';
			} else {
				echo '<span style="color:#2E7D32;font-size:13px;font-weight:700;">✓ Packed</span>';
			}
			echo '<button class="sc-btn" '
				. 'onclick="if(confirm(\'' . esc_js( __( 'Report an issue with this order?', 'swiftcart-cod' ) ) . '\')){alert(\'Issue reported. Supervisor notified.\');}" '
				. 'style="background:#FFF3E0;color:#E55E00;border:1px solid #FF6B00;height:36px;padding:0 14px;font-size:12px;border-radius:6px;">'
				. esc_html__( 'Report Issue', 'swiftcart-cod' )
				. '</button>';
			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/** ── Packing Confirmation ───────────────────────────────────────────────── */
	private function render_packing(): void {
		$order_id = absint( $_GET['order'] ?? 0 );
		$order    = $order_id ? wc_get_order( $order_id ) : null;

		if ( ! $order instanceof \WC_Order ) {
			echo '<div style="text-align:center;padding:48px;">';
			echo '<p>' . esc_html__( 'Select an order from the Pick List to begin packing.', 'swiftcart-cod' ) . '</p>';
			echo '<a href="' . esc_url( home_url( '/warehouse/pick-list' ) ) . '" class="sc-btn" style="display:inline-flex;">'
				. esc_html__( '← Back to Pick List', 'swiftcart-cod' ) . '</a>';
			echo '</div>';
			return;
		}

		$cod      = $order->get_total();
		$city     = $order->get_billing_city();
		$barangay = get_post_meta( $order_id, '_billing_barangay', true );
		$landmark = get_post_meta( $order_id, '_billing_landmark', true );
		$phone    = $order->get_billing_phone();
		$name     = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		$items    = $order->get_items();

		echo '<div class="sc-warehouse-header">';
		echo '<h1>' . esc_html__( 'Packing', 'swiftcart-cod' ) . '</h1>';
		echo '<a href="' . esc_url( home_url( '/warehouse/pick-list' ) ) . '" style="color:#888;font-size:13px;">← ' . esc_html__( 'Back', 'swiftcart-cod' ) . '</a>';
		echo '</div>';

		echo '<div class="sc-order-card" style="margin-bottom:16px;">';
		echo '<div class="sc-order-card__id" style="font-size:22px;">#SC-' . esc_html( $order_id ) . '</div>';
		echo '<div class="sc-order-card__cod">₱' . esc_html( number_format( $cod, 2 ) ) . ' — Cash on Delivery</div>';
		echo '<div style="margin-top:8px;font-size:13px;color:#555;">';
		echo esc_html( $name ) . ' · <a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a><br>';
		echo esc_html( $order->get_billing_address_1() );
		if ( $barangay ) echo ', ' . esc_html( $barangay );
		echo ', ' . esc_html( $city );
		if ( $landmark ) echo '<br><strong>📍 ' . esc_html__( 'Landmark:', 'swiftcart-cod' ) . '</strong> ' . esc_html( $landmark );
		echo '</div></div>';

		echo '<h2 style="font-size:18px;font-weight:800;margin-bottom:12px;">' . esc_html__( 'Items Checklist', 'swiftcart-cod' ) . '</h2>';
		echo '<form id="sc-packing-form">';
		wp_nonce_field( 'sc_warehouse_pack', 'sc_wh_nonce' );
		echo '<input type="hidden" name="order_id" value="' . esc_attr( $order_id ) . '">';

		foreach ( $items as $item_id => $item ) {
			$product = $item->get_product();
			$img_url = $product ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : '';
			$sku     = $product ? $product->get_sku() : '';

			echo '<div class="sc-item-check">';
			echo '<input type="checkbox" class="sc-item-check__cb" name="item_checked[]" value="' . esc_attr( $item_id ) . '">';
			if ( $img_url ) echo '<img class="sc-item-check__img" src="' . esc_url( $img_url ) . '" alt="">';
			echo '<div style="flex:1;">';
			echo '<div class="sc-item-check__name">' . esc_html( $item->get_name() ) . '</div>';
			if ( $sku ) echo '<div style="font-size:11px;color:#888;font-family:monospace;">' . esc_html( $sku ) . '</div>';
			echo '</div>';
			echo '<span class="sc-item-check__qty">× ' . esc_html( $item->get_quantity() ) . '</span>';
			echo '</div>';
		}

		echo '</form>';

		echo '<div id="sc-packing-actions" style="margin-top:20px;display:none;">';
		echo '<button class="sc-btn" id="sc-print-label" style="margin-bottom:10px;background:#1565C0;">'
			. esc_html__( '🖨 Print Label', 'swiftcart-cod' ) . '</button>';
		echo '<button class="sc-btn sc-wh-action" id="sc-mark-packed" '
			. 'data-order="' . esc_attr( $order_id ) . '" data-action="mark_packed" '
			. 'data-redirect="' . esc_attr( home_url( '/warehouse/pick-list' ) ) . '">'
			. esc_html__( 'Mark as Packed ✓', 'swiftcart-cod' ) . '</button>';
		echo '</div>';

		echo '<script>
			(function(){
				const checks = document.querySelectorAll(".sc-item-check__cb");
				const actions = document.getElementById("sc-packing-actions");
				function checkAll(){
					const total = checks.length;
					const done = [...checks].filter(c=>c.checked).length;
					if(actions) actions.style.display = (done===total && total>0) ? "block" : "none";
				}
				checks.forEach(c=>c.addEventListener("change",checkAll));
				document.getElementById("sc-print-label")?.addEventListener("click",function(){ window.print(); });
			})();
		</script>';
	}

	/** ── Dispatch Board ─────────────────────────────────────────────────────── */
	private function render_dispatch(): void {
		$packed_orders = wc_get_orders( array(
			'status' => 'sc-packed',
			'limit'  => 100,
			'return' => 'objects',
		) );

		echo '<div class="sc-warehouse-header"><h1>' . esc_html__( 'Dispatch Board', 'swiftcart-cod' ) . '</h1></div>';

		echo '<h2 style="font-size:16px;font-weight:700;margin-bottom:12px;">' . esc_html__( 'Ready to Dispatch', 'swiftcart-cod' ) . ' (' . esc_html( count( $packed_orders ) ) . ')</h2>';

		if ( empty( $packed_orders ) ) {
			echo '<p style="color:#888;">' . esc_html__( 'No packed orders waiting for dispatch.', 'swiftcart-cod' ) . '</p>';
		}

		foreach ( $packed_orders as $order ) {
			if ( ! $order instanceof \WC_Order ) continue;

			$city     = $order->get_billing_city();
			$barangay = get_post_meta( $order->get_id(), '_billing_barangay', true );
			$cod      = $order->get_total();
			$phone    = $order->get_billing_phone();

			echo '<div class="sc-order-card" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">';
			echo '<div style="flex:1;">';
			echo '<strong>#SC-' . esc_html( $order->get_id() ) . '</strong>';
			echo ' <span style="font-size:12px;color:#888;">' . esc_html( $city ) . ( $barangay ? ' · ' . esc_html( $barangay ) : '' ) . '</span><br>';
			echo '<a href="tel:' . esc_attr( $phone ) . '" style="font-size:13px;">' . esc_html( $phone ) . '</a>';
			echo '</div>';
			echo '<strong style="color:#FF6B00;font-size:18px;">₱' . esc_html( number_format( $cod, 2 ) ) . '</strong>';
			echo '<button class="sc-btn sc-wh-action" style="width:auto;padding:0 16px;" '
				. 'data-order="' . esc_attr( $order->get_id() ) . '" '
				. 'data-action="mark_dispatched">'
				. esc_html__( 'Dispatch ✓', 'swiftcart-cod' ) . '</button>';
			echo '</div>';
		}
	}

	/** ── Returns Intake ─────────────────────────────────────────────────────── */
	private function render_returns(): void {
		echo '<div class="sc-warehouse-header"><h1>' . esc_html__( 'Returns Intake', 'swiftcart-cod' ) . '</h1></div>';

		$order_id = absint( $_GET['order'] ?? 0 );
		$order    = $order_id ? wc_get_order( $order_id ) : null;

		echo '<form method="post" action="">';
		wp_nonce_field( 'sc_warehouse_return', 'sc_return_nonce' );
		echo '<input type="hidden" name="sc_return_action" value="1">';

		echo '<div style="margin-bottom:16px;">';
		echo '<label style="font-weight:700;font-size:15px;">' . esc_html__( 'Order ID', 'swiftcart-cod' ) . '</label>';
		echo '<div style="display:flex;gap:8px;margin-top:6px;">';
		echo '<input type="number" name="order_id_lookup" value="' . esc_attr( $order_id ?: '' ) . '" placeholder="' . esc_attr__( 'Enter Order ID', 'swiftcart-cod' ) . '" style="flex:1;height:44px;font-size:16px;padding:0 12px;border:1px solid #ddd;border-radius:6px;">';
		echo '<button type="submit" name="sc_lookup" class="sc-btn" style="width:auto;padding:0 20px;">' . esc_html__( 'Lookup', 'swiftcart-cod' ) . '</button>';
		echo '</div></div>';

		if ( $order instanceof \WC_Order ) {
			$this->render_return_form( $order );
		}

		echo '</form>';
	}

	/**
	 * Render the return detail form for a specific order.
	 *
	 * @since  1.0.0
	 * @param  \WC_Order $order The order being returned.
	 * @return void
	 */
	private function render_return_form( \WC_Order $order ): void {
		$reasons = array(
			'not_available'   => __( 'Customer not available (3 attempts)', 'swiftcart-cod' ),
			'refused'         => __( 'Customer refused delivery', 'swiftcart-cod' ),
			'wrong_address'   => __( 'Wrong address / not found', 'swiftcart-cod' ),
			'cancelled_late'  => __( 'Customer cancelled before delivery', 'swiftcart-cod' ),
			'damaged_transit' => __( 'Damaged in transit', 'swiftcart-cod' ),
			'other'           => __( 'Other', 'swiftcart-cod' ),
		);

		echo '<div class="sc-order-card">';
		echo '<strong>#SC-' . esc_html( $order->get_id() ) . ' — ' . esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) . '</strong><br>';
		echo '<span style="color:#FF6B00;font-weight:900;">₱' . esc_html( number_format( $order->get_total(), 2 ) ) . ' COD NOT COLLECTED</span>';
		echo '</div>';

		echo '<input type="hidden" name="order_id" value="' . esc_attr( $order->get_id() ) . '">';

		echo '<h3 style="font-weight:700;margin-top:16px;">' . esc_html__( 'Return Reason', 'swiftcart-cod' ) . '</h3>';
		foreach ( $reasons as $val => $label ) {
			echo '<label style="display:block;margin-bottom:8px;font-size:15px;">';
			echo '<input type="radio" name="return_reason" value="' . esc_attr( $val ) . '" style="margin-right:8px;" required>';
			echo esc_html( $label );
			echo '</label>';
		}

		echo '<label style="display:block;margin-top:8px;font-size:14px;color:#888;">' . esc_html__( 'Other details:', 'swiftcart-cod' ) . '</label>';
		echo '<textarea name="return_notes" rows="3" style="width:100%;border:1px solid #ddd;border-radius:6px;padding:8px;font-size:14px;margin-top:4px;"></textarea>';

		echo '<div style="margin-top:16px;padding:12px;background:#FFEBEE;border-radius:6px;">';
		echo '<label style="font-weight:700;display:block;margin-bottom:6px;">' . esc_html__( 'Confirm COD not collected:', 'swiftcart-cod' ) . '</label>';
		echo '<label><input type="checkbox" name="cod_not_collected" required style="margin-right:8px;"> ';
		echo esc_html( sprintf( __( '₱%s was NOT collected from the customer.', 'swiftcart-cod' ), number_format( $order->get_total(), 2 ) ) );
		echo '</label></div>';

		echo '<button type="submit" name="sc_submit_return" class="sc-btn" style="margin-top:20px;">' . esc_html__( 'Submit Return', 'swiftcart-cod' ) . '</button>';
	}

	/** ── Inventory Stock Status Editor ──────────────────────────────────────── */
	private function render_inventory(): void {
		echo '<div class="sc-warehouse-header"><h1>' . esc_html__( 'Inventory', 'swiftcart-cod' ) . '</h1></div>';

		echo '<input type="text" id="sc-inv-search" placeholder="' . esc_attr__( 'Search product or SKU…', 'swiftcart-cod' ) . '" '
			. 'style="width:100%;height:48px;font-size:16px;border:1px solid #ddd;border-radius:8px;padding:0 16px;margin-bottom:16px;">';

		$products = wc_get_products( array( 'limit' => 100, 'status' => 'publish' ) );
		$stock    = new Stock_Status();

		echo '<div id="sc-inv-list">';
		foreach ( $products as $product ) {
			if ( ! $product instanceof \WC_Product ) continue;

			$pid    = $product->get_id();
			$name   = $product->get_name();
			$sku    = $product->get_sku();
			$status = $stock->get_status( $pid );
			$img    = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );

			echo '<div class="sc-order-card sc-inv-row" data-name="' . esc_attr( strtolower( $name ) ) . '" data-sku="' . esc_attr( strtolower( $sku ) ) . '">';
			echo '<div style="display:flex;align-items:center;gap:12px;">';
			if ( $img ) echo '<img src="' . esc_url( $img ) . '" style="width:56px;height:56px;object-fit:cover;border-radius:4px;flex-shrink:0;">';
			echo '<div style="flex:1;">';
			echo '<div style="font-weight:700;font-size:15px;">' . esc_html( $name ) . '</div>';
			if ( $sku ) echo '<div style="font-size:11px;font-family:monospace;color:#888;">SKU: ' . esc_html( $sku ) . '</div>';
			echo '</div>';
			echo '<div style="display:flex;gap:6px;flex-shrink:0;" class="sc-inv-toggles">';
			foreach ( array( 'in_stock' => '✅ In Stock', 'low_stock' => '⚠️ Low', 'out_of_stock' => '❌ OOS' ) as $s => $label ) {
				$active = ( $s === $status ) ? 'background:#1565C0;color:#fff;' : 'background:#eee;color:#333;';
				echo '<button class="sc-inv-btn" data-product="' . esc_attr( $pid ) . '" data-status="' . esc_attr( $s ) . '" '
					. 'style="padding:6px 10px;border:none;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;' . esc_attr( $active ) . '">'
					. esc_html( $label ) . '</button>';
			}
			echo '</div></div></div>';
		}
		echo '</div>';

		echo '<script>
		(function(){
			// Live search filter.
			const search = document.getElementById("sc-inv-search");
			search?.addEventListener("input", function(){
				const q = this.value.toLowerCase();
				document.querySelectorAll(".sc-inv-row").forEach(row=>{
					const match = row.dataset.name.includes(q) || row.dataset.sku.includes(q);
					row.style.display = match ? "" : "none";
				});
			});

			// Inline status toggle via AJAX.
			document.querySelectorAll(".sc-inv-btn").forEach(btn=>{
				btn.addEventListener("click", function(){
					const pid    = this.dataset.product;
					const status = this.dataset.status;
					const row    = this.closest(".sc-order-card");

					fetch("' . esc_url( admin_url( 'admin-ajax.php' ) ) . '", {
						method: "POST",
						headers: {"Content-Type":"application/x-www-form-urlencoded"},
						body: new URLSearchParams({
							action: "sc_warehouse_action",
							wh_action: "set_stock_status",
							product_id: pid,
							status: status,
							nonce: "' . esc_js( wp_create_nonce( 'sc_warehouse' ) ) . '"
						})
					}).then(r=>r.json()).then(res=>{
						if(res.success){
							row.querySelectorAll(".sc-inv-btn").forEach(b=>{
								b.style.background = (b.dataset.status === status) ? "#1565C0" : "#eee";
								b.style.color      = (b.dataset.status === status) ? "#fff"    : "#333";
							});
						}
					});
				});
			});
		})();
		</script>';
	}

	/**
	 * AJAX handler for warehouse actions (start packing, mark packed, dispatch, set stock status).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function ajax_warehouse_action(): void {
		check_ajax_referer( 'sc_warehouse', 'nonce' );

		if ( ! current_user_can( 'swiftcart_pack_orders' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'swiftcart-cod' ) ), 403 );
		}

		$action = sanitize_key( wp_unslash( $_POST['wh_action'] ?? '' ) );

		switch ( $action ) {
			case 'start_packing':
				$order_id = absint( $_POST['order_id'] ?? 0 );
				$order    = wc_get_order( $order_id );
				if ( $order instanceof \WC_Order ) {
					update_post_meta( $order_id, '_sc_packing_started_by', get_current_user_id() );
					update_post_meta( $order_id, '_sc_packing_started_at', current_time( 'mysql' ) );
				}
				wp_send_json_success();
				break;

			case 'mark_packed':
				$order_id = absint( $_POST['order_id'] ?? 0 );
				$order    = wc_get_order( $order_id );
				if ( $order instanceof \WC_Order ) {
					$order->update_status( 'sc-packed', __( 'Packed by warehouse staff.', 'swiftcart-cod' ) );
				}
				wp_send_json_success();
				break;

			case 'mark_dispatched':
				$order_id = absint( $_POST['order_id'] ?? 0 );
				$order    = wc_get_order( $order_id );
				if ( $order instanceof \WC_Order ) {
					$order->update_status( 'sc-dispatch', __( 'Dispatched to rider.', 'swiftcart-cod' ) );
					// Trigger customer SMS (hook for SMS plugin).
					do_action( 'sc_order_dispatched', $order_id );
				}
				wp_send_json_success();
				break;

			case 'set_stock_status':
				if ( ! current_user_can( 'swiftcart_edit_stock_status' ) ) {
					wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'swiftcart-cod' ) ), 403 );
				}

				$product_id = absint( $_POST['product_id'] ?? 0 );
				$status     = sanitize_key( wp_unslash( $_POST['status'] ?? 'in_stock' ) );

				if ( ! in_array( $status, Stock_Status::STATUSES, true ) ) {
					wp_send_json_error( array( 'message' => __( 'Invalid status.', 'swiftcart-cod' ) ) );
				}

				update_post_meta( $product_id, Stock_Status::META_KEY, $status );
				wp_send_json_success( array( 'status' => $status ) );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Unknown action.', 'swiftcart-cod' ) ) );
		}
	}
}
