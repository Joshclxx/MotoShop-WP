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
 * Remove WooCommerce default shop loop hooks (for classic templates).
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
 * Inject "Buy Now" button into WooCommerce Block-rendered product grids.
 *
 * Since WC Blocks bypass content-product.php on block themes, we inject
 * the dual CTA (Buy Now + Add to Cart) layout via client-side JS that
 * runs after WC Blocks render.
 *
 * @since 1.7.0
 */
add_action( 'wp_footer', static function (): void {
	if ( is_admin() ) {
		return;
	}
	// Only inject on shop/archive/category pages.
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product_taxonomy() ) {
		return;
	}
	$checkout_url = wc_get_checkout_url();
	?>
	<script>
	(function () {
		'use strict';

		function injectBuyNowButtons() {
			// Target WC Block product cards that don't already have our Buy Now button.
			var cards = document.querySelectorAll(
				'.wc-block-grid__product:not([data-sc-dual-cta]), li.product:not([data-sc-dual-cta])'
			);

			cards.forEach(function (card) {
				card.setAttribute('data-sc-dual-cta', '1');

				// Find the existing Add to Cart button container.
				var btnWrap = card.querySelector('.wc-block-components-product-button, .wp-block-button');
				var addBtn  = card.querySelector('.add_to_cart_button, .wc-block-components-product-button__button');
				if (!addBtn) return;

				// Get product permalink for Buy Now link.
				var productLink = card.querySelector('a[href*="/product/"]');
				if (!productLink) return;
				var permalink = productLink.getAttribute('href');

				// Get product ID from the add-to-cart button.
				var productId = addBtn.getAttribute('data-product_id') ||
					addBtn.closest('[data-product_id]')?.getAttribute('data-product_id') || '';

				// Build the Buy Now URL.
				var buyNowUrl = permalink;
				if (productId) {
					buyNowUrl = permalink + (permalink.indexOf('?') > -1 ? '&' : '?') +
						'add-to-cart=' + productId + '&buy-now=1';
				}

				// Create dual CTA wrapper.
				var actionsDiv = document.createElement('div');
				actionsDiv.className = 'sc-product-card__actions';

				// Buy Now button.
				var buyBtn = document.createElement('a');
				buyBtn.href = buyNowUrl;
				buyBtn.className = 'sc-btn--buy-now';
				buyBtn.textContent = 'Buy Now';

				// Restyle the existing Add to Cart button.
				addBtn.classList.add('sc-btn--add-to-cart-block');

				// Insert our dual CTA.
				actionsDiv.appendChild(buyBtn);

				// Clone the Add to Cart button into our wrapper.
				var addBtnClone = addBtn.cloneNode(true);
				addBtnClone.className = 'sc-btn--add-to-cart add_to_cart_button';
				if (productId) {
					addBtnClone.setAttribute('data-product_id', productId);
					addBtnClone.setAttribute('data-quantity', '1');
				}
				addBtnClone.textContent = 'Add to Cart';
				actionsDiv.appendChild(addBtnClone);

				// Copy AJAX behavior: clicking clone triggers the original button.
				addBtnClone.addEventListener('click', function (e) {
					e.preventDefault();
					addBtn.click();
				});

				// Hide original button container and append our CTA.
				if (btnWrap) {
					btnWrap.style.display = 'none';
				}
				card.appendChild(actionsDiv);
			});
		}

		// Run after DOM is ready and WC Blocks have rendered.
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function () {
				setTimeout(injectBuyNowButtons, 200);
			});
		} else {
			setTimeout(injectBuyNowButtons, 200);
		}

		// Re-run when WC Blocks dynamically update (e.g., sorting, filtering).
		new MutationObserver(function () {
			setTimeout(injectBuyNowButtons, 100);
		}).observe(document.body, { childList: true, subtree: true });
	})();
	</script>
	<?php
}, 25 );

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
	echo '<span class="sc-product-cod-badge__sub">' . esc_html__( 'No prepayment required. Pay when delivered.', 'swiftcart-cod' ) . '</span>';
	echo '</div>';
	echo '</div>';

	// Delivery estimate.
	$is_before_cutoff = (int) current_time( 'G' ) < $cutoff_hour;
	$dispatch_day     = $is_before_cutoff ? __( 'today', 'swiftcart-cod' ) : __( 'tomorrow', 'swiftcart-cod' );
	$delivery_start   = wp_date( 'M j', strtotime( '+2 days' ) );
	$delivery_end     = wp_date( 'M j', strtotime( '+4 days' ) );

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

	// Force store currency to Philippine Peso (₱).
	update_option( 'woocommerce_currency', 'PHP' );
	update_option( 'woocommerce_currency_pos', 'left' );
}, 20 );

/**
 * Ensure WooCommerce always returns PHP (Philippine Peso) as the active currency.
 *
 * @since 1.6.0
 */
add_filter( 'woocommerce_currency', static fn() => 'PHP' );
add_filter( 'woocommerce_currency_symbol', static fn() => '₱' );

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
 * Remove "Downloads" from My Account navigation (no digital products).
 */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	unset( $items['downloads'] );
	return $items;
} );

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
	if ( is_admin() || get_query_var( 'ms_admin_page' ) ) {
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
	echo '</nav>';
	echo '</div>';

	// Centre column — logo.
	echo '<div class="sc-sticky-header__centre">';
	echo '<a href="' . esc_url( $home_url ) . '" class="sc-site-logo" aria-label="' . esc_attr__( 'MotoShop Parts — Home', 'swiftcart-cod' ) . '">';
	echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr__( 'MotoShop Parts', 'swiftcart-cod' ) . '" width="170" height="45" loading="eager" />';
	echo '</a>';
	echo '</div>';

	// Right column — cart icon + role-based account links.
	echo '<div class="sc-sticky-header__right">';

	// UX-006: Cart icon with live count.
	$cart_count = ( function_exists( 'WC' ) && WC()->cart instanceof WC_Cart ) ? WC()->cart->get_cart_contents_count() : 0;
	$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#';
	$acct_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#';
	echo '<a href="' . esc_url( $cart_url ) . '" class="sc-header-cart" aria-label="' . esc_attr__( 'Shopping cart', 'swiftcart-cod' ) . '">';
	echo '<span class="sc-header-cart__icon" aria-hidden="true">🛒</span>';
	echo '<span class="sc-header-cart__count" data-count="' . esc_attr( $cart_count ) . '">' . esc_html( $cart_count ) . '</span>';
	echo '</a>';

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
		// Logout moved to My Account sidebar only.
	} else {
		echo '<a href="' . esc_url( $acct_url ) . '" class="sc-header-account">Login / Register</a>';
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

/* ═══════════════════════════════════════════════════════════════════════════
 * UX-012 — Asset Health Check
 *
 * Validates that critical image assets exist. Shows admin notice if missing.
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Check for required theme image assets and warn if missing.
 *
 * @since 1.5.0
 */
add_action( 'admin_init', static function (): void {
	$required_assets = array(
		'assets/images/hero-delivery.svg' => 'Hero banner image',
		'assets/images/logo-3-card.svg'   => 'Site logo image',
	);

	$missing = array();
	$theme_dir = get_stylesheet_directory();

	foreach ( $required_assets as $path => $label ) {
		if ( ! file_exists( $theme_dir . '/' . $path ) ) {
			$missing[] = sprintf( '<code>%s</code> (%s)', esc_html( $path ), esc_html( $label ) );
		}
	}

	if ( ! empty( $missing ) ) {
		add_action( 'admin_notices', static function () use ( $missing ): void {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>MoTo Shop:</strong> Missing theme assets &mdash; ';
			echo implode( ', ', $missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '. The homepage may display with missing visuals.';
			echo '</p></div>';
		} );
	}
} );

/* ═══════════════════════════════════════════════════════════════════════════
 * Confirm Password — Server-side Validation
 *
 * Validates that password and password_confirm fields match on registration.
 * ═══════════════════════════════════════════════════════════════════════════ */

add_filter( 'woocommerce_process_registration_errors', static function ( WP_Error $errors, string $username, string $password, string $email ): WP_Error {
	$confirm = isset( $_POST['password_confirm'] ) ? wp_unslash( $_POST['password_confirm'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
	if ( $password !== $confirm ) {
		$errors->add( 'password_mismatch', __( '<strong>Error:</strong> Passwords do not match.', 'swiftcart-cod' ) );
	}
	return $errors;
}, 10, 4 );

/* ═══════════════════════════════════════════════════════════════════════════
 * Admin Login Redirect
 *
 * Redirect administrators and shop managers to the MoTo Shop Dashboard
 * instead of the WooCommerce My Account page.
 * Also redirects admins who navigate directly to /my-account/.
 * ═══════════════════════════════════════════════════════════════════════════ */

add_filter( 'woocommerce_login_redirect', static function ( string $redirect, WP_User $user ): string {
	if ( in_array( 'administrator', (array) $user->roles, true ) || in_array( 'shop_manager', (array) $user->roles, true ) ) {
		return home_url( '/motoshop-admin/' );
	}
	return home_url( '/' );
}, 99, 2 );

add_filter( 'woocommerce_registration_redirect', static function ( $redirect ): string {
	return home_url( '/' );
}, 99 );

// Force-redirect admin/shop_manager away from /my-account/ to MoTo Shop Dashboard.
add_action( 'template_redirect', static function (): void {
	if ( ! is_account_page() || ! is_user_logged_in() ) {
		return;
	}
	if ( current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' ) ) {
		wp_safe_redirect( home_url( '/motoshop-admin/' ) );
		exit;
	}
} );

/* ═══════════════════════════════════════════════════════════════════════════
 * Guest Login-Gate Modal
 *
 * Shows a branded modal when non-logged-in users try to:
 *  - Add to cart
 *  - Visit cart / checkout / my-account pages
 *  - Interact with any WooCommerce protected action
 * ═══════════════════════════════════════════════════════════════════════════ */

add_action( 'wp_footer', static function (): void {
	if ( is_user_logged_in() ) {
		return;
	}

	$login_url = wc_get_page_permalink( 'myaccount' );
	?>
	<!-- Login Gate Modal -->
	<div id="sc-login-gate" class="sc-login-gate" hidden>
		<div class="sc-login-gate__backdrop"></div>
		<div class="sc-login-gate__card">
			<button type="button" class="sc-login-gate__close" aria-label="<?php esc_attr_e( 'Close', 'swiftcart-cod' ); ?>">&times;</button>
			<div class="sc-login-gate__icon">🔒</div>
			<h3 class="sc-login-gate__title"><?php esc_html_e( 'Login Required', 'swiftcart-cod' ); ?></h3>
			<p class="sc-login-gate__message"><?php esc_html_e( 'Please log in or create an account to continue shopping.', 'swiftcart-cod' ); ?></p>
			<a href="<?php echo esc_url( $login_url ); ?>" class="sc-login-gate__btn sc-login-gate__btn--primary">
				<?php esc_html_e( 'Log In', 'swiftcart-cod' ); ?>
			</a>
			<a href="<?php echo esc_url( $login_url . '#register' ); ?>" class="sc-login-gate__btn sc-login-gate__btn--secondary">
				<?php esc_html_e( 'Create Account', 'swiftcart-cod' ); ?>
			</a>
		</div>
	</div>

	<script>
	(function () {
		var gate     = document.getElementById('sc-login-gate');
		var backdrop = gate.querySelector('.sc-login-gate__backdrop');
		var closeBtn = gate.querySelector('.sc-login-gate__close');

		function showGate(e) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			gate.hidden = false;
			document.body.style.overflow = 'hidden';
		}

		function hideGate() {
			gate.hidden = true;
			document.body.style.overflow = '';
		}

		closeBtn.addEventListener('click', hideGate);
		backdrop.addEventListener('click', hideGate);
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !gate.hidden) hideGate();
		});

		// Selectors for protected actions
		var protectedSelectors = [
			'.add_to_cart_button',
			'.single_add_to_cart_button',
			'a[href*="add-to-cart="]',
			'a[href*="buy-now="]',
			'a[href*="/cart/"]',
			'a[href*="/checkout/"]',
			'a[href*="/product/"]',
			'a[href*="/product-category/"]',
			'a[href*="/shop/"]',
			'.woocommerce-LoopProduct-link',
			'.wc-block-grid__product-link',
			'.sc-hero-cta[href*="shop"]'
		].join(',');

		// Use event delegation on body for dynamic content (AJAX-loaded products)
		document.body.addEventListener('click', function (e) {
			// Don't intercept clicks inside the modal itself (Login / Create Account buttons)
			if (e.target.closest('#sc-login-gate')) return;

			var target = e.target.closest(protectedSelectors);
			if (target) {
				showGate(e);
				return false;
			}
		}, true); // Use capture phase to beat WC AJAX handlers

		// Also block WooCommerce AJAX add-to-cart by removing the ajax class
		document.querySelectorAll('.add_to_cart_button.ajax_add_to_cart').forEach(function (btn) {
			btn.classList.remove('ajax_add_to_cart');
		});

		// Observe for dynamically loaded products (infinite scroll, AJAX filters)
		new MutationObserver(function (mutations) {
			mutations.forEach(function (m) {
				m.addedNodes.forEach(function (node) {
					if (node.nodeType === 1) {
						node.querySelectorAll && node.querySelectorAll('.add_to_cart_button.ajax_add_to_cart').forEach(function (btn) {
							btn.classList.remove('ajax_add_to_cart');
						});
					}
				});
			});
		}).observe(document.body, { childList: true, subtree: true });
	})();
	</script>
	<?php
} );
