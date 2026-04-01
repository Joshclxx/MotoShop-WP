<?php
/**
 * SwiftCart COD child theme functions.
 *
 * Enqueues styles, declares WooCommerce support, registers menus/widget areas,
 * and configures theme-level hooks. All business logic (checkout fields,
 * warehouse, admin) lives in the swiftcart-cod plugin.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue parent and child theme stylesheets.
 *
 * @since  1.0.0
 * @return void
 */
function swiftcart_enqueue_styles(): void {
	$parent = 'twentytwentyfive-style';

	wp_enqueue_style(
		$parent,
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( 'twentytwentyfive' )->get( 'Version' )
	);

	wp_enqueue_style(
		'swiftcart-child',
		get_stylesheet_uri(),
		array( $parent ),
		(string) filemtime( get_stylesheet_directory() . '/style.css' )
	);

	// Google Fonts — Inter.
	wp_enqueue_style(
		'swiftcart-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);

	// Comprehensive enhancement styles.
	$enh_path = get_stylesheet_directory() . '/assets/css/swiftcart-enhancements.css';
	if ( file_exists( $enh_path ) ) {
		wp_enqueue_style(
			'swiftcart-enhancements',
			get_stylesheet_directory_uri() . '/assets/css/swiftcart-enhancements.css',
			array( 'swiftcart-child' ),
			(string) filemtime( $enh_path )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'swiftcart_enqueue_styles' );

/**
 * Remove WooCommerce default shop loop hooks.
 *
 * Our custom content-product.php template handles its own image,
 * title, price, and CTA buttons. The WC defaults must be stripped
 * to prevent duplicates overlaying the custom card layout.
 *
 * @since 1.3.0
 */
add_action( 'woocommerce_init', static function (): void {
	// Remove default shop loop item elements.
	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
} );

/**
 * Force WooCommerce to use classic PHP templates on product archives.
 *
 * Twenty Twenty-Five is a block theme, so WooCommerce uses block-based
 * rendering by default. This filter tells WC to skip block templates
 * for the product archive, allowing archive-product.php + content-product.php
 * to render instead (with our dual CTA design).
 *
 * @since 1.3.0
 */
add_filter( 'woocommerce_has_block_template', '__return_false' );

/**
 * COD availability badge on single product summary.
 *
 * Note: The stock badge on single product is handled by the swiftcart-cod
 * plugin's Stock_Status module. We only inject the COD info and delivery
 * estimate here — not the stock badge (to avoid redundancy).
 *
 * @since  1.0.0
 * @return void
 */
function swiftcart_product_page_cod_info(): void {
	$cutoff_hour = (int) get_option( 'swiftcart_cutoff_hour', 10 );
	$cutoff_fmt  = sprintf( '%d:00 %s', $cutoff_hour > 12 ? $cutoff_hour - 12 : $cutoff_hour, $cutoff_hour >= 12 ? 'PM' : 'AM' );

	echo '<div class="sc-product-cod-badge">';
	echo '<span class="sc-product-cod-badge__icon">💳</span>';
	echo '<div>';
	echo '<strong>' . esc_html__( 'Cash on Delivery Available', 'swiftcart-cod' ) . '</strong><br>';
	echo '<span style="font-size:13px;color:#E55E00;">' . esc_html__( 'No prepayment required. Pay when delivered.', 'swiftcart-cod' ) . '</span>';
	echo '</div>';
	echo '</div>';

	// Delivery estimate.
	$is_before_cutoff = (int) current_time( 'G' ) < $cutoff_hour;
	$dispatch_day     = $is_before_cutoff ? __( 'today', 'swiftcart-cod' ) : __( 'tomorrow', 'swiftcart-cod' );
	$delivery_start   = date( 'M j', strtotime( '+2 days', current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	$delivery_end     = date( 'M j', strtotime( '+4 days', current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

	echo '<div class="sc-delivery-estimate">';
	echo '<div class="sc-delivery-estimate__title">🚚 ' . esc_html__( 'Delivery Estimate', 'swiftcart-cod' ) . '</div>';
	echo '<div class="sc-delivery-estimate__row">';
	echo '<span>' . esc_html__( 'Dispatch', 'swiftcart-cod' ) . '</span>';
	echo '<strong>' . esc_html( sprintf( __( 'Ships %s (order before %s)', 'swiftcart-cod' ), $dispatch_day, $cutoff_fmt ) ) . '</strong>';
	echo '</div>';
	echo '<div class="sc-delivery-estimate__row">';
	echo '<span>' . esc_html__( 'Estimated delivery', 'swiftcart-cod' ) . '</span>';
	echo '<strong>' . esc_html( $delivery_start . ' – ' . $delivery_end ) . '</strong>';
	echo '</div>';
	echo '</div>';

	echo '<div class="sc-return-policy">↩ ' . esc_html__( '7-day return policy. Inspect your item before paying.', 'swiftcart-cod' ) . '</div>';
}
add_action( 'woocommerce_single_product_summary', 'swiftcart_product_page_cod_info', 25 );

/**
 * Declare WooCommerce feature support.
 *
 * @since  1.0.0
 * @return void
 */
function swiftcart_woocommerce_setup(): void {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'swiftcart_woocommerce_setup' );

/**
 * Ensure WooCommerce registration is enabled (Bug 2 fix).
 *
 * @since 1.2.0
 */
add_action( 'after_setup_theme', static function (): void {
	update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
	update_option( 'woocommerce_registration_generate_username', 'yes' );
	update_option( 'woocommerce_registration_generate_password', 'no' );
}, 20 );

/**
 * Register navigation menus.
 *
 * @since  1.0.0
 * @return void
 */
function swiftcart_register_menus(): void {
	register_nav_menus(
		array(
			'primary'   => __( 'Primary Navigation', 'swiftcart-cod' ),
			'footer'    => __( 'Footer Navigation', 'swiftcart-cod' ),
			'warehouse' => __( 'Warehouse Navigation', 'swiftcart-cod' ),
		)
	);
}
add_action( 'init', 'swiftcart_register_menus' );

/**
 * Register widget areas.
 *
 * @since  1.0.0
 * @return void
 */
function swiftcart_register_sidebars(): void {
	register_sidebar(
		array(
			'name'          => __( 'Shop Sidebar', 'swiftcart-cod' ),
			'id'            => 'shop-sidebar',
			'description'   => __( 'Widgets for the shop and category pages.', 'swiftcart-cod' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Column 1', 'swiftcart-cod' ),
			'id'            => 'footer-1',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Column 2', 'swiftcart-cod' ),
			'id'            => 'footer-2',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);
}
add_action( 'widgets_init', 'swiftcart_register_sidebars' );

/**
 * Add body classes for SwiftCart-specific context.
 *
 * @since  1.0.0
 * @param  string[] $classes Existing body classes.
 * @return string[]
 */
function swiftcart_body_classes( array $classes ): array {
	if ( is_checkout() ) {
		$classes[] = 'sc-checkout-page';
	}

	if ( is_account_page() ) {
		$classes[] = 'sc-account-page';
	}

	if ( is_woocommerce() ) {
		$classes[] = 'sc-woo-page';
	}

	return $classes;
}
add_filter( 'body_class', 'swiftcart_body_classes' );

/**
 * Remove default WooCommerce styles (we use our own).
 *
 * @since  1.0.0
 * @param  array<string,array<string,string>> $styles Default WC styles.
 * @return array<string,array<string,string>>
 */
function swiftcart_dequeue_wc_styles( array $styles ): array {
	// Keep WooCommerce's block styles but strip the legacy CSS.
	unset( $styles['woocommerce-general'] );
	unset( $styles['woocommerce-layout'] );
	unset( $styles['woocommerce-smallscreen'] );

	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'swiftcart_dequeue_wc_styles' );

/* ═══════════════════════════════════════════════════════════════════════════
 * TASK 6 — Redirect Rules
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Redirect non-admin users away from wp-admin.
 *
 * Warehouse staff use /warehouse/* URLs and never need wp-admin.
 *
 * @since 1.0.0
 */
add_action( 'admin_init', static function (): void {
	if ( is_admin() && ! current_user_can( 'manage_woocommerce' ) && ! wp_doing_ajax() ) {
		wp_redirect( wc_get_account_endpoint_url( 'dashboard' ) );
		exit;
	}
} );

/**
 * Redirect logged-in buyers away from wp-login.php to My Account.
 *
 * WooCommerce already shows the dashboard to logged-in users on
 * the My Account page, so no template_redirect is needed there.
 *
 * @since 1.0.0
 */
add_action( 'login_init', static function (): void {
	// Never interrupt POST (login/registration form submissions).
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	// Don't redirect for logout action.
	if ( isset( $_GET['action'] ) && 'logout' === $_GET['action'] ) {
		return;
	}

	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if ( in_array( 'customer', (array) $user->roles, true ) ) {
			wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
			exit;
		}
	}
} );

/* ═══════════════════════════════════════════════════════════════════════════
 * TASK 3 — Custom Admin Login Branding
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Enqueue branded login stylesheet for staff/admin login.
 *
 * Skipped when ?context=buyer query arg is present.
 *
 * @since 1.0.0
 */
add_action( 'login_enqueue_scripts', static function (): void {
	if ( isset( $_GET['context'] ) && 'buyer' === $_GET['context'] ) {
		return;
	}

	wp_enqueue_style(
		'swiftcart-login-admin',
		get_stylesheet_directory_uri() . '/login-admin.css',
		array(),
		(string) filemtime( get_stylesheet_directory() . '/login-admin.css' )
	);
} );

add_filter( 'login_headerurl', static fn() => home_url() );
add_filter( 'login_headertext', static fn() => 'MotoShop Parts — Staff Portal' );

/**
 * Show staff/admin access label on the login page.
 *
 * @since 1.0.0
 */
add_filter( 'login_message', static function ( string $message ): string {
	if ( ! isset( $_GET['context'] ) || 'buyer' !== $_GET['context'] ) {
		$message .= '<p class="sc-login-role-label">🔒 Staff / Admin Access</p>';
	}
	return $message;
} );

/* ═══════════════════════════════════════════════════════════════════════════
 * TASK 1 — Buy Now Redirect
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Redirect to checkout when the "Buy Now" button is clicked.
 *
 * Detects ?buy-now=1 query arg added by the Buy Now button link.
 *
 * @since 1.0.0
 */
add_filter( 'woocommerce_add_to_cart_redirect', static function ( $url ) {
	// Guest → send to login instead of checkout.
	if ( ! is_user_logged_in() ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	if ( isset( $_REQUEST['buy-now'] ) && '1' === $_REQUEST['buy-now'] ) {
		return wc_get_checkout_url();
	}
	return $url;
} );

/* ═══════════════════════════════════════════════════════════════════════════
 * BUG 3 — Admin Login Redirects
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * After WooCommerce login, redirect admins/managers to wp-admin.
 *
 * @since 1.2.0
 */
add_filter( 'woocommerce_login_redirect', static function ( $redirect, $user ) {
	if ( user_can( $user, 'manage_woocommerce' ) || user_can( $user, 'manage_options' ) ) {
		return admin_url();
	}
	return $redirect;
}, 10, 2 );

/**
 * After standard WordPress login, redirect admins/managers to wp-admin.
 *
 * @since 1.2.0
 */
add_filter( 'login_redirect', static function ( $redirect_to, $requested_redirect_to, $user ) {
	if ( isset( $user->roles ) && (
		in_array( 'administrator', (array) $user->roles, true ) ||
		in_array( 'shop_manager', (array) $user->roles, true )
	) ) {
		return admin_url();
	}
	return $redirect_to;
}, 10, 3 );

/* ═══════════════════════════════════════════════════════════════════════════
 * BUG 4 — Guest Add-to-Cart Intercept
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Block guest users from adding to cart — require login first.
 *
 * @since 1.2.0
 */
add_filter( 'woocommerce_add_to_cart_validation', static function ( $passed, $product_id, $quantity ) {
	if ( ! is_user_logged_in() ) {
		wc_add_notice(
			sprintf(
				__( 'Please <a href="%s">log in or create an account</a> to add items to your cart.', 'swiftcart-cod' ),
				esc_url( wc_get_page_permalink( 'myaccount' ) )
			),
			'error'
		);
		return false;
	}
	return $passed;
}, 10, 3 );

/* ═══════════════════════════════════════════════════════════════════════════
 * TASK 5 + TASK B1 — Unified Sticky Header
 *
 * All three header elements (nav pill, logo, account bar) are output inside a
 * single <header id="sc-sticky-header"> wrapper so they form one cohesive bar.
 * A small inline script adds .scrolled when the user scrolls past 10 px,
 * triggering the dark solid-background transition in CSS.
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Output the unified sticky header and scroll-state script.
 *
 * Replaces the former separate priority-10 nav/logo hook and priority-20
 * account-bar hook. Both are now a single priority-10 emission so the DOM
 * order is: wrapper open → left (nav) → centre (logo) → right (account) → close.
 *
 * @since 1.4.0
 */
add_action( 'wp_footer', static function (): void {
	if ( is_admin() ) {
		return;
	}

	$shop_url = wc_get_page_permalink( 'shop' ) ?: home_url( '/shop/' );
	$home_url = home_url( '/' );
	$acct_url = wc_get_page_permalink( 'myaccount' );
	$logo_url = get_stylesheet_directory_uri() . '/assets/images/logo-3-card.svg';

	echo '<header id="sc-sticky-header" class="sc-sticky-header" role="banner">';

	// Left column — navigation pill.
	echo '<div class="sc-sticky-header__left">';
	echo '<nav class="sc-main-nav" aria-label="Main Navigation">';
	echo '<a href="' . esc_url( $home_url ) . '" class="sc-main-nav__link' . ( is_front_page() ? ' sc-main-nav__link--active' : '' ) . '">🏠 Home</a>';
	echo '<a href="' . esc_url( $shop_url ) . '" class="sc-main-nav__link' . ( is_shop() ? ' sc-main-nav__link--active' : '' ) . '">🛒 Shop</a>';
	echo '<a href="' . esc_url( $acct_url ) . '" class="sc-main-nav__link' . ( is_account_page() ? ' sc-main-nav__link--active' : '' ) . '">👤 Account</a>';
	echo '</nav>';
	echo '</div>';

	// Centre column — logo.
	echo '<div class="sc-sticky-header__centre">';
	echo '<a href="' . esc_url( $home_url ) . '" class="sc-site-logo" aria-label="' . esc_attr__( 'MotoShop Parts — Home', 'swiftcart-cod' ) . '">';
	echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr__( 'MotoShop Parts', 'swiftcart-cod' ) . '" width="170" height="45" loading="eager" />';
	echo '</a>';
	echo '</div>';

	// Right column — role-based account links.
	echo '<div class="sc-sticky-header__right">';
	echo '<div class="sc-header-account-bar">';
	if ( is_user_logged_in() ) {
		if ( current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' ) ) {
			echo '<a href="' . esc_url( admin_url() ) . '" class="sc-header-account sc-header-account--admin">⚙ Admin Panel</a>';
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=swiftcart' ) ) . '" class="sc-header-account sc-header-account--admin">📦 Orders</a>';
			echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=product' ) ) . '" class="sc-header-account sc-header-account--admin">🛒 Products</a>';
		}
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			echo '<a href="' . esc_url( wc_get_account_endpoint_url( 'dashboard' ) ) . '" class="sc-header-account">My Account</a>';
		}
		// All logged-in users get a logout link.
		echo '<a href="' . esc_url( wp_logout_url( home_url() ) ) . '" class="sc-header-account sc-header-account--logout">Logout</a>';
	} else {
		echo '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="sc-header-account">Login / Register</a>';
	}
	echo '</div>';
	echo '</div>';

	echo '</header>';
	?>
	<script>
	(function () {
		var header = document.getElementById('sc-sticky-header');
		if (!header) return;
		function onScroll() {
			if (window.scrollY > 10) {
				header.classList.add('scrolled');
			} else {
				header.classList.remove('scrolled');
			}
		}
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll(); // run once on load in case page starts scrolled
	}());
	</script>
	<?php
}, 10 );

/**
 * Guest JS redirect — intercept AJAX add-to-cart clicks for non-logged-in users.
 *
 * @since 1.2.0
 */
add_action( 'wp_footer', static function (): void {
	if ( is_user_logged_in() || is_admin() ) {
		return;
	}
	$login_url = esc_url( wc_get_page_permalink( 'myaccount' ) );
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.add_to_cart_button, .sc-btn--add-to-cart, .sc-btn--buy-now, [name="add-to-cart"]')
			.forEach(function(btn) {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					window.location.href = '<?php echo $login_url; ?>';
				});
			});
	});
	</script>
	<?php
}, 30 );

/* Note: TASK B1 (nav + logo injection) is now consolidated into the unified
 * sticky header hook above (TASK 5 + TASK B1). The former standalone hook
 * has been removed to prevent duplicate output. */
