<?php
declare( strict_types=1 );
namespace SwiftCart\Admin;
defined( 'ABSPATH' ) || exit;

class MotoShop_Panel {
	const PAGES = array( 'dashboard', 'orders', 'products', 'customers', 'reports' );
	private const LOW_STOCK = 5;

	public function register(): void {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'handle_request' ) );
	}

	public function add_rewrite_rules(): void {
		add_rewrite_rule( '^motoshop-admin/([a-z-]+)/?$', 'index.php?ms_admin_page=$matches[1]', 'top' );
		add_rewrite_rule( '^motoshop-admin/?$', 'index.php?ms_admin_page=dashboard', 'top' );
	}

	public function add_query_vars( array $vars ): array {
		$vars[] = 'ms_admin_page';
		return $vars;
	}

	public function handle_request(): void {
		$page = get_query_var( 'ms_admin_page' );
		if ( '' === $page ) return;
		if ( ! is_user_logged_in() ) { wp_redirect( wp_login_url( home_url( '/motoshop-admin/' . $page ) ) ); exit; }
		if ( ! current_user_can( 'manage_swiftcart' ) ) { wp_die( 'Access denied.', '', array( 'response' => 403 ) ); }
		if ( ! in_array( $page, self::PAGES, true ) ) { wp_die( 'Page not found.', '', array( 'response' => 404 ) ); }
		show_admin_bar( false );

		// Strip theme and WC front-end styles so only panel.css loads.
		add_action( 'wp_enqueue_scripts', function() {
			wp_dequeue_style( 'swiftcart-child-style' );
			wp_dequeue_style( 'swiftcart-parent-style' );
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wc-blocks-style' );
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-smallscreen' );
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'global-styles' );
		}, 999 );

		$this->render_shell( $page );
		exit;
	}

	private function render_shell( string $page ): void {
		$user = wp_get_current_user();
		$initials = strtoupper( mb_substr( $user->display_name, 0, 2 ) );
		$titles = array( 'dashboard' => 'Dashboard', 'orders' => 'Orders', 'products' => 'Products', 'customers' => 'Customers', 'reports' => 'COD Reports' );
		$icons  = array( 'dashboard' => '📊', 'orders' => '📦', 'products' => '🏍️', 'customers' => '👥', 'reports' => '💰' );
		?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $titles[ $page ] ); ?> — MoTo Shop Admin</title>
<link rel="stylesheet" href="<?php echo esc_url( SWIFTCART_URL . 'assets/css/panel.css' ); ?>?v=<?php echo esc_attr( (string) filemtime( SWIFTCART_DIR . 'assets/css/panel.css' ) ); ?>">
<?php wp_head(); ?>
</head>
<body>
<div class="ms-app">
<aside class="ms-sidebar">
	<div class="ms-sidebar__brand">
		<div class="ms-sidebar__logo"><span>🏍️</span> MoTo Shop</div>
		<div class="ms-sidebar__tagline">Admin Panel</div>
	</div>
	<nav class="ms-sidebar__nav">
		<?php foreach ( self::PAGES as $p ) : ?>
		<a href="<?php echo esc_url( home_url( '/motoshop-admin/' . $p ) ); ?>" class="ms-sidebar__link <?php echo $page === $p ? 'active' : ''; ?>">
			<span class="ms-sidebar__link-icon"><?php echo esc_html( $icons[ $p ] ); ?></span>
			<?php echo esc_html( $titles[ $p ] ); ?>
		</a>
		<?php endforeach; ?>
		<hr class="ms-sidebar__divider">
		<a href="<?php echo esc_url( admin_url() ); ?>" class="ms-sidebar__link"><span class="ms-sidebar__link-icon">⚙️</span> WP Admin</a>
	</nav>
	<div class="ms-sidebar__footer">
		<div class="ms-sidebar__user">
			<div class="ms-sidebar__avatar"><?php echo esc_html( $initials ); ?></div>
			<div class="ms-sidebar__user-info">
				<div class="ms-sidebar__username"><?php echo esc_html( $user->display_name ); ?></div>
				<div class="ms-sidebar__role">Administrator</div>
			</div>
		</div>
		<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="ms-sidebar__logout">Sign Out →</a>
	</div>
</aside>
<main class="ms-main">
	<?php $this->render_content( $page ); ?>
</main>
</div>
<?php wp_footer(); ?>
</body>
</html><?php
	}

	private function render_content( string $page ): void {
		switch ( $page ) {
			case 'dashboard': $this->render_dashboard(); break;
			case 'orders': $this->render_orders(); break;
			case 'products': $this->render_products(); break;
			case 'customers': $this->render_customers(); break;
			case 'reports': $this->render_reports(); break;
		}
	}

	private function render_dashboard(): void {
		$kpis = $this->get_kpis();
		$recent = $this->get_recent_orders();
		$low = $this->get_low_stock();
		?>
		<div class="ms-hero">
			<div class="ms-hero__title">Welcome back, <?php echo esc_html( wp_get_current_user()->display_name ); ?> 👋</div>
			<div class="ms-hero__sub"><?php echo esc_html( wp_date( 'l, F j, Y' ) ); ?></div>
		</div>
		<div class="ms-kpi-row">
			<div class="ms-kpi ms-kpi--orange"><div class="ms-kpi__icon">📦</div><div class="ms-kpi__value"><?php echo esc_html( $kpis['today_orders'] ); ?></div><div class="ms-kpi__label">Today's Orders</div></div>
			<div class="ms-kpi ms-kpi--green"><div class="ms-kpi__icon">💰</div><div class="ms-kpi__value">₱<?php echo esc_html( number_format( $kpis['today_revenue'], 0 ) ); ?></div><div class="ms-kpi__label">Revenue Today</div></div>
			<div class="ms-kpi ms-kpi--amber"><div class="ms-kpi__icon">⏳</div><div class="ms-kpi__value"><?php echo esc_html( $kpis['pending'] ); ?></div><div class="ms-kpi__label">Pending COD</div></div>
			<div class="ms-kpi ms-kpi--red"><div class="ms-kpi__icon">⚠️</div><div class="ms-kpi__value"><?php echo esc_html( $kpis['low_stock'] ); ?></div><div class="ms-kpi__label">Low Stock</div></div>
		</div>
		<div class="ms-card"><div class="ms-card__header"><div class="ms-card__title">Quick Actions</div></div>
			<div class="ms-actions-grid">
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="ms-action"><span class="ms-action__icon">➕</span><span class="ms-action__label">Add Product</span></a>
				<a href="<?php echo esc_url( home_url( '/motoshop-admin/orders' ) ); ?>" class="ms-action"><span class="ms-action__icon">📦</span><span class="ms-action__label">Orders</span></a>
				<a href="<?php echo esc_url( home_url( '/motoshop-admin/reports' ) ); ?>" class="ms-action"><span class="ms-action__icon">📊</span><span class="ms-action__label">Reports</span></a>
				<a href="<?php echo esc_url( home_url( '/motoshop-admin/customers' ) ); ?>" class="ms-action"><span class="ms-action__icon">👥</span><span class="ms-action__label">Customers</span></a>
				<a href="<?php echo esc_url( home_url( '/warehouse/' ) ); ?>" class="ms-action"><span class="ms-action__icon">🏭</span><span class="ms-action__label">Warehouse</span></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>" class="ms-action"><span class="ms-action__icon">⚙️</span><span class="ms-action__label">Settings</span></a>
			</div>
		</div>
		<div class="ms-grid-2">
			<div class="ms-card"><div class="ms-card__header"><div class="ms-card__title">Recent Orders</div><a href="<?php echo esc_url( home_url( '/motoshop-admin/orders' ) ); ?>" class="ms-card__action">View All →</a></div>
				<table class="ms-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>
				<?php if ( empty( $recent ) ) : ?><tr class="ms-table--empty"><td colspan="4">No recent orders.</td></tr>
				<?php else : foreach ( $recent as $o ) :
					$sm = array( 'pending'=>'pending','processing'=>'confirmed','sc-packed'=>'packed','sc-dispatch'=>'dispatched','completed'=>'delivered','cancelled'=>'cancelled','sc-returned'=>'returned' );
					$sc = $sm[ $o->get_status() ] ?? 'pending'; ?>
				<tr><td><strong>#SC-<?php echo esc_html( $o->get_id() ); ?></strong><br><span style="font-size:11px;color:#999"><?php echo esc_html( $o->get_date_created()?->date('M d, H:i') ?? '' ); ?></span></td>
				<td><?php echo esc_html( trim( $o->get_billing_first_name().' '.$o->get_billing_last_name() ) ); ?></td>
				<td><strong>₱<?php echo esc_html( number_format( (float) $o->get_total(), 2 ) ); ?></strong></td>
				<td><span class="ms-badge ms-badge--<?php echo esc_attr( $sc ); ?>"><?php echo esc_html( ucfirst( $sc ) ); ?></span></td></tr>
				<?php endforeach; endif; ?></tbody></table>
			</div>
			<div class="ms-card"><div class="ms-card__header"><div class="ms-card__title">Low Stock Alerts</div><a href="<?php echo esc_url( home_url( '/motoshop-admin/products' ) ); ?>" class="ms-card__action">All Products →</a></div>
				<table class="ms-table"><thead><tr><th>Product</th><th>Stock</th><th>Action</th></tr></thead><tbody>
				<?php if ( empty( $low ) ) : ?><tr class="ms-table--empty"><td colspan="3">All products well stocked 👍</td></tr>
				<?php else : foreach ( $low as $p ) : $sq = (int) $p->get_stock_quantity(); ?>
				<tr><td><?php echo esc_html( $p->get_name() ); ?></td><td><strong class="<?php echo $sq === 0 ? 'ms-stock--out' : 'ms-stock--low'; ?>"><?php echo esc_html( $sq ); ?></strong></td>
				<td><a href="<?php echo esc_url( get_edit_post_link( $p->get_id() ) ); ?>" class="ms-btn ms-btn--secondary ms-btn--sm">Edit</a></td></tr>
				<?php endforeach; endif; ?></tbody></table>
			</div>
		</div>
		<?php
	}

	private function render_orders(): void {
		$status_filter = sanitize_text_field( wp_unslash( $_GET['status'] ?? '' ) );
		$args = array( 'limit' => 50, 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects' );
		if ( $status_filter ) $args['status'] = $status_filter;
		$orders = wc_get_orders( $args );
		$statuses = array( '' => 'All', 'pending' => 'Pending', 'processing' => 'Confirmed', 'sc-packed' => 'Packed', 'sc-dispatch' => 'Dispatched', 'completed' => 'Delivered', 'cancelled' => 'Cancelled' );
		$sm = array( 'pending'=>'pending','processing'=>'confirmed','sc-packed'=>'packed','sc-dispatch'=>'dispatched','completed'=>'delivered','cancelled'=>'cancelled','sc-returned'=>'returned' );
		?>
		<div class="ms-page-header"><div class="ms-page-header__title">Orders</div><div class="ms-page-header__sub">Manage your COD orders</div></div>
		<div class="ms-tabs">
			<?php foreach ( $statuses as $k => $l ) : $url = home_url( '/motoshop-admin/orders' ) . ( $k ? '?status=' . $k : '' ); ?>
			<a href="<?php echo esc_url( $url ); ?>" class="ms-tab <?php echo $status_filter === $k ? 'active' : ''; ?>"><?php echo esc_html( $l ); ?></a>
			<?php endforeach; ?>
		</div>
		<div class="ms-card">
		<table class="ms-table"><thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody>
		<?php if ( empty( $orders ) ) : ?><tr class="ms-table--empty"><td colspan="6">No orders found.</td></tr>
		<?php else : foreach ( $orders as $o ) : if ( ! $o instanceof \WC_Order ) continue; $sc = $sm[ $o->get_status() ] ?? 'pending'; ?>
		<tr><td><a href="<?php echo esc_url( get_edit_post_link( $o->get_id() ) ); ?>"><strong>#SC-<?php echo esc_html( $o->get_id() ); ?></strong></a></td>
		<td><?php echo esc_html( trim( $o->get_billing_first_name().' '.$o->get_billing_last_name() ) ); ?></td>
		<td><a href="tel:<?php echo esc_attr( $o->get_billing_phone() ); ?>"><?php echo esc_html( $o->get_billing_phone() ); ?></a></td>
		<td><strong>₱<?php echo esc_html( number_format( (float) $o->get_total(), 2 ) ); ?></strong></td>
		<td><span class="ms-badge ms-badge--<?php echo esc_attr( $sc ); ?>"><?php echo esc_html( ucfirst( $sc ) ); ?></span></td>
		<td style="font-size:12px;color:#999"><?php echo esc_html( $o->get_date_created()?->date('M d, Y H:i') ?? '' ); ?></td></tr>
		<?php endforeach; endif; ?></tbody></table>
		</div>
		<?php
	}

	private function render_products(): void {
		$products = wc_get_products( array( 'limit' => 100, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<div class="ms-page-header"><div class="ms-page-header__title">Products</div>
			<div class="ms-page-header__sub"><?php echo esc_html( count( $products ) ); ?> products · <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>">+ Add New</a></div>
		</div>
		<div class="ms-product-grid">
		<?php foreach ( $products as $p ) : if ( ! $p instanceof \WC_Product ) continue;
			$img = wp_get_attachment_image_url( $p->get_image_id(), 'medium' ) ?: SWIFTCART_URL . 'assets/img/placeholder.png';
			$stock = $p->get_stock_quantity();
			$manages = $p->get_manage_stock();
			if ( $manages && $stock !== null ) { $sc = $stock <= 0 ? 'ms-stock--out' : ( $stock <= self::LOW_STOCK ? 'ms-stock--low' : 'ms-stock--ok' ); $sl = $stock . ' in stock'; }
			else { $sc = 'ms-stock--ok'; $sl = $p->is_in_stock() ? 'In stock' : 'Out of stock'; }
		?>
		<div class="ms-product-card">
			<img src="<?php echo esc_url( $img ); ?>" alt="" class="ms-product-card__img">
			<div class="ms-product-card__body">
				<div class="ms-product-card__name"><?php echo esc_html( $p->get_name() ); ?></div>
				<div class="ms-product-card__price">₱<?php echo esc_html( number_format( (float) $p->get_price(), 2 ) ); ?></div>
				<div class="ms-product-card__meta">
					<span class="ms-product-card__stock <?php echo esc_attr( $sc ); ?>"><?php echo esc_html( $sl ); ?></span>
					<a href="<?php echo esc_url( get_edit_post_link( $p->get_id() ) ); ?>" class="ms-btn ms-btn--secondary ms-btn--sm">Edit</a>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
		</div>
		<?php
	}

	private function render_customers(): void {
		$orders = wc_get_orders( array( 'limit' => -1, 'return' => 'objects' ) );
		$map = array();
		foreach ( $orders as $o ) {
			if ( ! $o instanceof \WC_Order ) continue;
			$ph = $o->get_billing_phone(); if ( ! $ph ) continue;
			if ( ! isset( $map[$ph] ) ) $map[$ph] = array( 'name' => trim($o->get_billing_first_name().' '.$o->get_billing_last_name()), 'phone' => $ph, 'total' => 0, 'cancelled' => 0, 'bl' => (bool) $o->get_meta('_sc_blacklist_flag') );
			$map[$ph]['total']++;
			if ( 'cancelled' === $o->get_status() ) $map[$ph]['cancelled']++;
		}
		usort( $map, fn($a,$b) => $b['total'] <=> $a['total'] );
		?>
		<div class="ms-page-header"><div class="ms-page-header__title">Customers</div><div class="ms-page-header__sub"><?php echo esc_html( count($map) ); ?> customers</div></div>
		<div class="ms-card">
		<table class="ms-table"><thead><tr><th>Customer</th><th>Phone</th><th>Orders</th><th>Cancel Rate</th><th>Blacklist</th></tr></thead><tbody>
		<?php if ( empty( $map ) ) : ?><tr class="ms-table--empty"><td colspan="5">No customer data yet.</td></tr>
		<?php else : foreach ( $map as $c ) : $rate = $c['total'] > 0 ? (int) round(($c['cancelled']/$c['total'])*100) : 0; $rc = $rate >= 30 ? 'ms-rate--bad' : ($rate >= 15 ? 'ms-rate--warn' : 'ms-rate--ok'); ?>
		<tr><td><strong><?php echo esc_html($c['name']); ?></strong></td>
		<td><a href="tel:<?php echo esc_attr($c['phone']); ?>"><?php echo esc_html($c['phone']); ?></a></td>
		<td><?php echo esc_html($c['total']); ?></td>
		<td><strong class="<?php echo esc_attr($rc); ?>"><?php echo esc_html($rate); ?>%</strong></td>
		<td><span class="<?php echo $c['bl'] ? 'ms-bl--yes' : 'ms-bl--no'; ?>"><?php echo $c['bl'] ? 'Blacklisted' : 'Clear'; ?></span></td></tr>
		<?php endforeach; endif; ?></tbody></table>
		</div>
		<?php
	}

	private function render_reports(): void {
		$range = sanitize_text_field( wp_unslash( $_GET['range'] ?? 'today' ) );
		$ranges = array( 'today' => array( strtotime('today midnight'), time() ), 'yesterday' => array( strtotime('yesterday midnight'), strtotime('today midnight')-1 ), 'this_week' => array( strtotime('monday this week midnight'), time() ), 'this_month' => array( strtotime('first day of this month midnight'), time() ) );
		$dates = $ranges[$range] ?? $ranges['today'];
		$orders = wc_get_orders( array( 'date_created' => $dates[0].'...'.$dates[1], 'limit' => -1, 'return' => 'objects' ) );
		$s = array( 'total' => count($orders), 'collected' => 0, 'collected_amt' => 0.0, 'pending' => 0, 'failed' => 0, 'returned' => 0 );
		foreach ( $orders as $o ) { if ( ! $o instanceof \WC_Order ) continue; $st = $o->get_status();
			if ( 'completed' === $st ) { $s['collected']++; $s['collected_amt'] += (float) $o->get_total(); }
			elseif ( in_array($st, array('pending','processing','sc-packed','sc-dispatch'), true) ) $s['pending']++;
			elseif ( 'cancelled' === $st ) $s['failed']++;
			elseif ( 'sc-returned' === $st ) $s['returned']++;
		}
		$cr = ($s['collected']+$s['failed']+$s['returned']) > 0 ? round($s['collected']/($s['collected']+$s['failed']+$s['returned'])*100,1) : 0;
		$tabs = array( 'today'=>'Today', 'yesterday'=>'Yesterday', 'this_week'=>'This Week', 'this_month'=>'This Month' );
		?>
		<div class="ms-page-header"><div class="ms-page-header__title">COD Reports</div><div class="ms-page-header__sub">Financial overview</div></div>
		<div class="ms-tabs"><?php foreach ($tabs as $k=>$l): ?><a href="<?php echo esc_url(home_url('/motoshop-admin/reports?range='.$k)); ?>" class="ms-tab <?php echo $range===$k?'active':''; ?>"><?php echo esc_html($l); ?></a><?php endforeach; ?></div>
		<div class="ms-kpi-row">
			<div class="ms-kpi ms-kpi--orange"><div class="ms-kpi__value"><?php echo esc_html($s['total']); ?></div><div class="ms-kpi__label">Total Orders</div></div>
			<div class="ms-kpi ms-kpi--green"><div class="ms-kpi__value">₱<?php echo esc_html(number_format($s['collected_amt'],2)); ?></div><div class="ms-kpi__label">COD Collected</div></div>
			<div class="ms-kpi ms-kpi--amber"><div class="ms-kpi__value"><?php echo esc_html($s['pending']); ?></div><div class="ms-kpi__label">Pending</div></div>
			<div class="ms-kpi ms-kpi--red"><div class="ms-kpi__value"><?php echo esc_html($s['failed']); ?></div><div class="ms-kpi__label">Failed</div></div>
			<div class="ms-kpi"><div class="ms-kpi__value"><?php echo esc_html($s['returned']); ?></div><div class="ms-kpi__label">Returned</div></div>
			<div class="ms-kpi ms-kpi--<?php echo $cr>=80?'green':'amber'; ?>"><div class="ms-kpi__value"><?php echo esc_html($cr); ?>%</div><div class="ms-kpi__label">Collection Rate</div></div>
		</div>
		<div class="ms-card"><p style="color:#999;font-size:13px">Collection Rate = Collected ÷ (Collected + Failed + Returned)</p></div>
		<?php
	}

	// ── Data helpers ──
	private function get_kpis(): array {
		$today = wc_get_orders( array( 'date_created' => '>='.strtotime( wp_date('Y-m-d 00:00:00') ), 'limit' => -1, 'return' => 'objects' ) );
		$cnt = 0; $rev = 0.0;
		foreach ( $today as $o ) { if (!$o instanceof \WC_Order) continue; $cnt++;
			if ( !in_array($o->get_status(), array('cancelled','sc-returned','failed'), true) ) $rev += (float)$o->get_total(); }
		$pending = count( wc_get_orders( array( 'status' => array('pending','processing','sc-packed','sc-dispatch'), 'limit' => -1, 'return' => 'ids' ) ) );
		$lq = new \WP_Query( array( 'post_type'=>'product','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids',
			'meta_query'=>array('relation'=>'AND', array('key'=>'_manage_stock','value'=>'yes'), array('key'=>'_stock','value'=>self::LOW_STOCK,'compare'=>'<=','type'=>'NUMERIC') ) ) );
		return array( 'today_orders'=>$cnt, 'today_revenue'=>$rev, 'pending'=>$pending, 'low_stock'=>$lq->found_posts );
	}

	private function get_recent_orders(): array {
		return array_filter( wc_get_orders( array( 'limit'=>5, 'orderby'=>'date', 'order'=>'DESC', 'return'=>'objects' ) ), fn($o)=>$o instanceof \WC_Order );
	}

	private function get_low_stock(): array {
		$q = new \WP_Query( array( 'post_type'=>'product','post_status'=>'publish','posts_per_page'=>10,
			'meta_query'=>array('relation'=>'AND', array('key'=>'_manage_stock','value'=>'yes'), array('key'=>'_stock','value'=>self::LOW_STOCK,'compare'=>'<=','type'=>'NUMERIC') ),
			'orderby'=>'meta_value_num','meta_key'=>'_stock','order'=>'ASC' ) );
		$r = array();
		foreach ( $q->posts as $p ) { $pr = wc_get_product($p->ID); if ($pr) $r[] = $pr; }
		return $r;
	}
}
