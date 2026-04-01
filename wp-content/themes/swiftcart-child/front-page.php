<?php
/**
 * Homepage template — SwiftCart COD front page.
 *
 * Sections (top to bottom):
 *   1. Sticky header with COD pill
 *   2. Announcement bar
 *   3. Hero banner
 *   4. Trust bar
 *   5. Featured categories
 *   6. Featured products
 *   7. How COD Works
 *   8. Delivery coverage teaser
 *   9. FAQ preview
 *  10. Footer
 *
 * @package SwiftCart
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ═══════════════════════════════════════════════════════
     HERO BANNER
═══════════════════════════════════════════════════════ -->
<section class="sc-hero" aria-label="<?php esc_attr_e( 'Hero', 'swiftcart-cod' ); ?>">
	<!-- ═══════════════════════════════════════════════════════
     ANNOUNCEMENT BAR
	═══════════════════════════════════════════════════════ -->
	<div class="sc-announcement-bar" role="banner" aria-label="<?php esc_attr_e( 'Promotions', 'swiftcart-cod' ); ?>">
		<div class="sc-announcement-bar__inner">
			<span class="sc-announcement-bar__item sc-announcement-bar__item--active">
				🛵 <?php esc_html_e( 'Free delivery on orders ₱500 and above', 'swiftcart-cod' ); ?>
			</span>
			<span class="sc-announcement-bar__item">
				⏰ <?php esc_html_e( 'Order before 10 AM for same-day dispatch', 'swiftcart-cod' ); ?>
			</span>
			<span class="sc-announcement-bar__item">
				💳 <?php esc_html_e( 'Pay cash on delivery — no card needed', 'swiftcart-cod' ); ?>
			</span>
		</div>
		<button class="sc-announcement-bar__close" aria-label="<?php esc_attr_e( 'Dismiss', 'swiftcart-cod' ); ?>">✕</button>
	</div>


	<div class="sc-hero__inner sc-container">
		<div class="sc-hero__content">
			<div class="sc-hero__badge">
				<span class="sc-cod-pill">💰 Cash on Delivery</span>
			</div>
			<h1 class="sc-hero__headline">
				<?php esc_html_e( 'Shop Now,', 'swiftcart-cod' ); ?><br>
				<span class="sc-hero__headline--accent"><?php esc_html_e( 'Pay When It Arrives', 'swiftcart-cod' ); ?></span>
			</h1>
			<p class="sc-hero__sub">
				<?php esc_html_e( 'Secure Cash-on-Delivery shopping with reliable local delivery', 'swiftcart-cod' ); ?>
			</p>
			<div class="sc-hero__actions">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
					<?php esc_html_e( 'Shop Now', 'swiftcart-cod' ); ?>
				</a>
				<a href="#how-cod-works" class="sc-btn sc-btn--ghost sc-btn--lg">
					<?php esc_html_e( 'How COD Works?', 'swiftcart-cod' ); ?>
				</a>
			</div>
			<p class="sc-hero__guarantee">
				🔒 <?php esc_html_e( 'No prepayment. Pay only when you receive your item.', 'swiftcart-cod' ); ?>
			</p>
		</div>
		<div class="sc-hero__visual" aria-hidden="true">
			<div class="sc-hero__image-wrap">
				<img
					src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/hero-delivery.svg' ); ?>"
					alt=""
					width="480"
					height="380"
					loading="eager"
					onerror="this.style.display='none'"
				>
				<div class="sc-hero__floating-card sc-hero__floating-card--left">
					<span class="sc-hero__fc-icon">✅</span>
					<div>
						<strong><?php esc_html_e( 'Order #4821', 'swiftcart-cod' ); ?></strong><br>
						<small><?php esc_html_e( 'Out for delivery', 'swiftcart-cod' ); ?></small>
					</div>
				</div>
				<div class="sc-hero__floating-card sc-hero__floating-card--right">
					<span class="sc-hero__fc-icon">⭐</span>
					<div>
						<strong><?php esc_html_e( '4.9 / 5', 'swiftcart-cod' ); ?></strong><br>
						<small><?php esc_html_e( '2,400+ reviews', 'swiftcart-cod' ); ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     TRUST BAR
═══════════════════════════════════════════════════════ -->
<section class="sc-trust-bar" aria-label="<?php esc_attr_e( 'Trust indicators', 'swiftcart-cod' ); ?>">
	<div class="sc-container">
		<div class="sc-trust-bar__grid">
			<div class="sc-trust-bar__item">
				<span class="sc-trust-bar__icon">💳</span>
				<div class="sc-trust-bar__text">
					<strong><?php esc_html_e( 'COD Payment', 'swiftcart-cod' ); ?></strong>
					<span><?php esc_html_e( 'Pay on delivery only', 'swiftcart-cod' ); ?></span>
				</div>
			</div>
			<div class="sc-trust-bar__item">
				<span class="sc-trust-bar__icon">✅</span>
				<div class="sc-trust-bar__text">
					<strong><?php esc_html_e( 'Verified Seller', 'swiftcart-cod' ); ?></strong>
					<span><?php esc_html_e( 'DTI registered business', 'swiftcart-cod' ); ?></span>
				</div>
			</div>
			<div class="sc-trust-bar__item">
				<span class="sc-trust-bar__icon">↩️</span>
				<div class="sc-trust-bar__text">
					<strong><?php esc_html_e( 'Easy Returns', 'swiftcart-cod' ); ?></strong>
					<span><?php esc_html_e( '7-day return policy', 'swiftcart-cod' ); ?></span>
				</div>
			</div>
			<div class="sc-trust-bar__item">
				<span class="sc-trust-bar__icon">🛡️</span>
				<div class="sc-trust-bar__text">
					<strong><?php esc_html_e( 'Money-Back', 'swiftcart-cod' ); ?></strong>
					<span><?php esc_html_e( '100% satisfaction guarantee', 'swiftcart-cod' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FEATURED CATEGORIES
═══════════════════════════════════════════════════════ -->
<section class="sc-section sc-categories" aria-label="<?php esc_attr_e( 'Shop by category', 'swiftcart-cod' ); ?>">
	<div class="sc-container">
		<div class="sc-section__header">
			<h2 class="sc-section__title"><?php esc_html_e( 'Shop by Category', 'swiftcart-cod' ); ?></h2>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-section__link">
				<?php esc_html_e( 'View All', 'swiftcart-cod' ); ?> →
			</a>
		</div>
		<div class="sc-categories__grid">
			<?php
			$category_slugs = array( 'brake-parts', 'engine-parts', 'tires-wheels', 'lights-electrical' );
			$category_defaults = array(
				array( 'icon' => '🔧', 'name' => 'Brake Parts',        'slug' => 'brake-parts'       ),
				array( 'icon' => '⚙️', 'name' => 'Engine Parts',       'slug' => 'engine-parts'      ),
				array( 'icon' => '🏍️', 'name' => 'Tires & Wheels',     'slug' => 'tires-wheels'      ),
				array( 'icon' => '💡', 'name' => 'Lights & Electrical', 'slug' => 'lights-electrical' ),
			);

			$terms = get_terms( array(
				'taxonomy'   => 'product_cat',
				'slug'       => $category_slugs,
				'hide_empty' => false,
			) );

			$term_map = array();
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_map[ $term->slug ] = $term;
				}
			}

			foreach ( $category_defaults as $cat ) :
				$term      = $term_map[ $cat['slug'] ] ?? null;
				$url       = $term ? get_term_link( $term ) : get_permalink( wc_get_page_id( 'shop' ) );
				$count     = $term ? $term->count : 0;
				$thumb_id  = $term ? get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0;
				$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
			?>
			<a href="<?php echo esc_url( is_wp_error( $url ) ? '#' : $url ); ?>" class="sc-cat-card">
				<div class="sc-cat-card__image">
					<?php if ( $thumb_url ) : ?>
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" loading="lazy">
					<?php else : ?>
						<span class="sc-cat-card__icon" aria-hidden="true"><?php echo $cat['icon']; ?></span>
					<?php endif; ?>
				</div>
				<div class="sc-cat-card__info">
					<strong class="sc-cat-card__name"><?php echo esc_html( $cat['name'] ); ?></strong>
					<?php if ( $count > 0 ) : ?>
						<span class="sc-cat-card__count">
							<?php echo esc_html( sprintf( _n( '%d item', '%d items', $count, 'swiftcart-cod' ), $count ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FEATURED PRODUCTS
═══════════════════════════════════════════════════════ -->
<section class="sc-section sc-featured-products" aria-label="<?php esc_attr_e( 'Featured products', 'swiftcart-cod' ); ?>">
	<div class="sc-container">
		<div class="sc-section__header">
			<h2 class="sc-section__title"><?php esc_html_e( 'Best Sellers', 'swiftcart-cod' ); ?></h2>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-section__link">
				<?php esc_html_e( 'View All', 'swiftcart-cod' ); ?> →
			</a>
		</div>
		<?php
		$featured_args = array(
			'post_type'      => 'product',
			'posts_per_page' => 8,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'exclude-from-catalog',
					'operator' => 'NOT IN',
				),
			),
		);
		$featured_query = new WP_Query( $featured_args );

		if ( $featured_query->have_posts() ) :
		?>
		<div class="sc-products-grid">
			<?php
			while ( $featured_query->have_posts() ) :
				$featured_query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}

				$stock_status = get_post_meta( get_the_ID(), '_sc_stock_status', true ) ?: 'in_stock';
				$price_html   = $product->get_price_html();
				$is_on_sale   = $product->is_on_sale();
				$sale_pct     = '';
				if ( $is_on_sale && $product->get_regular_price() > 0 ) {
					$saved    = ( (float) $product->get_regular_price() - (float) $product->get_sale_price() ) / (float) $product->get_regular_price() * 100;
					$sale_pct = '-' . round( $saved ) . '%';
				}
			?>
			<article class="sc-product-card" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
				<a href="<?php the_permalink(); ?>" class="sc-product-card__image-link" tabindex="-1" aria-hidden="true">
					<div class="sc-product-card__image">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<div class="sc-product-card__no-image">📦</div>
						<?php endif; ?>
						<?php if ( $is_on_sale && $sale_pct ) : ?>
							<span class="sc-product-card__sale-badge"><?php echo esc_html( $sale_pct ); ?></span>
						<?php endif; ?>
					</div>
				</a>
				<div class="sc-product-card__body">
					<div class="sc-product-card__stock">
						<?php if ( 'out_of_stock' === $stock_status ) : ?>
							<span class="sc-stock-badge sc-stock-badge--oos"><?php esc_html_e( 'Out of Stock', 'swiftcart-cod' ); ?></span>
						<?php elseif ( 'low_stock' === $stock_status ) : ?>
							<span class="sc-stock-badge sc-stock-badge--low"><?php esc_html_e( 'Low Stock', 'swiftcart-cod' ); ?></span>
						<?php else : ?>
							<span class="sc-stock-badge sc-stock-badge--in"><?php esc_html_e( 'In Stock', 'swiftcart-cod' ); ?></span>
						<?php endif; ?>
					</div>
					<h3 class="sc-product-card__name">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<div class="sc-product-card__price">
						<?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="sc-product-card__cod">
						<span class="sc-cod-tag">💳 <?php esc_html_e( 'COD Available', 'swiftcart-cod' ); ?></span>
					</div>
					<?php if ( 'out_of_stock' !== $stock_status ) : ?>
					<a href="<?php the_permalink(); ?>" class="sc-btn sc-btn--primary sc-btn--block">
						<?php esc_html_e( 'Add to Cart', 'swiftcart-cod' ); ?>
					</a>
					<?php else : ?>
					<button class="sc-btn sc-btn--ghost sc-btn--block" disabled>
						<?php esc_html_e( 'Out of Stock', 'swiftcart-cod' ); ?>
					</button>
					<?php endif; ?>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php else : ?>
		<p class="sc-empty-notice"><?php esc_html_e( 'No products found. Add products to get started.', 'swiftcart-cod' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     HOW COD WORKS
═══════════════════════════════════════════════════════ -->
<section id="how-cod-works" class="sc-section sc-how-cod" aria-label="<?php esc_attr_e( 'How COD works', 'swiftcart-cod' ); ?>">
	<div class="sc-container">
		<div class="sc-section__header sc-section__header--center">
			<h2 class="sc-section__title"><?php esc_html_e( 'How Cash on Delivery Works', 'swiftcart-cod' ); ?></h2>
			<p class="sc-section__sub"><?php esc_html_e( 'Simple, safe, and no card required.', 'swiftcart-cod' ); ?></p>
		</div>
		<div class="sc-how-cod__steps">
			<div class="sc-how-cod__step">
				<div class="sc-how-cod__step-num">1</div>
				<div class="sc-how-cod__step-icon" aria-hidden="true">🛒</div>
				<h3 class="sc-how-cod__step-title"><?php esc_html_e( 'Add to Cart', 'swiftcart-cod' ); ?></h3>
				<p class="sc-how-cod__step-desc">
					<?php esc_html_e( 'Browse our catalog and add your items. No payment needed at this stage.', 'swiftcart-cod' ); ?>
				</p>
			</div>
			<div class="sc-how-cod__connector" aria-hidden="true">→</div>
			<div class="sc-how-cod__step">
				<div class="sc-how-cod__step-num">2</div>
				<div class="sc-how-cod__step-icon" aria-hidden="true">📍</div>
				<h3 class="sc-how-cod__step-title"><?php esc_html_e( 'Enter Your Address', 'swiftcart-cod' ); ?></h3>
				<p class="sc-how-cod__step-desc">
					<?php esc_html_e( 'Tell us where to deliver. Our rider will call before arriving.', 'swiftcart-cod' ); ?>
				</p>
			</div>
			<div class="sc-how-cod__connector" aria-hidden="true">→</div>
			<div class="sc-how-cod__step">
				<div class="sc-how-cod__step-num">3</div>
				<div class="sc-how-cod__step-icon" aria-hidden="true">💰</div>
				<h3 class="sc-how-cod__step-title"><?php esc_html_e( 'Pay on Delivery', 'swiftcart-cod' ); ?></h3>
				<p class="sc-how-cod__step-desc">
					<?php esc_html_e( 'Hand cash to the rider when your package arrives. Inspect first, then pay.', 'swiftcart-cod' ); ?>
				</p>
			</div>
		</div>
		<div class="sc-how-cod__cta">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
				<?php esc_html_e( 'Start Shopping', 'swiftcart-cod' ); ?>
			</a>
		</div>
		<div class="sc-how-cod__note">
			🔒 <?php esc_html_e( 'SwiftCart COD will NEVER ask for GCash or any prepayment. If anyone does, it is a scam.', 'swiftcart-cod' ); ?>
		</div>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     DELIVERY COVERAGE TEASER
═══════════════════════════════════════════════════════ -->
<section class="sc-section sc-coverage" aria-label="<?php esc_attr_e( 'Delivery coverage', 'swiftcart-cod' ); ?>">
	<div class="sc-container">
		<div class="sc-coverage__inner">
			<div class="sc-coverage__content">
				<h2 class="sc-coverage__title">
					<?php esc_html_e( 'We Deliver to 50+ Cities Nationwide', 'swiftcart-cod' ); ?>
				</h2>
				<p class="sc-coverage__sub">
					<?php esc_html_e( 'Metro Manila, Cebu, Davao, and more. Check if your area is covered before ordering.', 'swiftcart-cod' ); ?>
				</p>
				<div class="sc-coverage__cities">
					<span class="sc-coverage__city">📍 Metro Manila</span>
					<span class="sc-coverage__city">📍 Cebu City</span>
					<span class="sc-coverage__city">📍 Davao City</span>
					<span class="sc-coverage__city">📍 Laguna</span>
					<span class="sc-coverage__city">📍 Bulacan</span>
					<span class="sc-coverage__city">📍 + many more</span>
				</div>
				<a href="<?php echo esc_url( home_url( '/delivery-coverage' ) ); ?>" class="sc-btn sc-btn--outline sc-btn--lg">
					<?php esc_html_e( 'Check Your Area →', 'swiftcart-cod' ); ?>
				</a>
			</div>
			<div class="sc-coverage__stats" aria-hidden="true">
				<div class="sc-coverage__stat">
					<span class="sc-coverage__stat-num">50+</span>
					<span class="sc-coverage__stat-label"><?php esc_html_e( 'Cities', 'swiftcart-cod' ); ?></span>
				</div>
				<div class="sc-coverage__stat">
					<span class="sc-coverage__stat-num">2,400+</span>
					<span class="sc-coverage__stat-label"><?php esc_html_e( 'Deliveries Done', 'swiftcart-cod' ); ?></span>
				</div>
				<div class="sc-coverage__stat">
					<span class="sc-coverage__stat-num">97%</span>
					<span class="sc-coverage__stat-label"><?php esc_html_e( 'Delivery Rate', 'swiftcart-cod' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FAQ PREVIEW
═══════════════════════════════════════════════════════ -->
<section class="sc-section sc-faq-preview" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'swiftcart-cod' ); ?>">
	<div class="sc-container">
		<div class="sc-section__header sc-section__header--center">
			<h2 class="sc-section__title"><?php esc_html_e( 'Frequently Asked Questions', 'swiftcart-cod' ); ?></h2>
		</div>
		<div class="sc-faq__list">
			<?php
			$faqs = array(
				array(
					'q' => __( 'Is it really cash on delivery?', 'swiftcart-cod' ),
					'a' => __( 'Yes! You pay the rider in cash when your package arrives at your door. There is absolutely no upfront payment required.', 'swiftcart-cod' ),
				),
				array(
					'q' => __( 'What happens if I\'m not home?', 'swiftcart-cod' ),
					'a' => __( 'Our rider will call you before arriving. If you miss the delivery, we will reschedule for the next available day. Two failed attempts may result in order cancellation.', 'swiftcart-cod' ),
				),
				array(
					'q' => __( 'Can I cancel my order?', 'swiftcart-cod' ),
					'a' => __( 'You can cancel before your order is packed. Repeated cancellations may affect your ability to place future orders.', 'swiftcart-cod' ),
				),
				array(
					'q' => __( 'Is there a handling fee for COD?', 'swiftcart-cod' ),
					'a' => sprintf(
						/* translators: %s: fee amount */
						__( 'A small COD handling fee of ₱%s is added to cover collection processing. This is disclosed clearly at checkout.', 'swiftcart-cod' ),
						number_format( (float) get_option( 'swiftcart_cod_fee', 20 ), 0 )
					),
				),
				array(
					'q' => __( 'What areas do you deliver to?', 'swiftcart-cod' ),
					'a' => __( 'We cover Metro Manila, Cebu, Davao, Laguna, Bulacan, Rizal, Pampanga, and more. Check the delivery coverage page for a full list.', 'swiftcart-cod' ),
				),
			);

			foreach ( $faqs as $i => $faq ) :
			?>
			<div class="sc-faq__item" data-open="<?php echo 0 === $i ? 'true' : 'false'; ?>">
				<button
					class="sc-faq__question"
					aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>"
					aria-controls="sc-faq-<?php echo esc_attr( $i ); ?>"
				>
					<?php echo esc_html( $faq['q'] ); ?>
					<span class="sc-faq__chevron" aria-hidden="true">▾</span>
				</button>
				<div
					class="sc-faq__answer"
					id="sc-faq-<?php echo esc_attr( $i ); ?>"
					role="region"
					<?php echo 0 === $i ? '' : 'hidden'; ?>
				>
					<p><?php echo esc_html( $faq['a'] ); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<div class="sc-faq__more">
			<a href="<?php echo esc_url( home_url( '/faq' ) ); ?>" class="sc-btn sc-btn--ghost">
				<?php esc_html_e( 'View All FAQs', 'swiftcart-cod' ); ?>
			</a>
		</div>
	</div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FOOTER COD REASSURANCE BANNER
═══════════════════════════════════════════════════════ -->
<div class="sc-reassurance-banner">
	<div class="sc-container">
		<p>
			🚨 <strong><?php esc_html_e( 'Anti-Scam Notice:', 'swiftcart-cod' ); ?></strong>
			<?php esc_html_e( 'SwiftCart COD will NEVER ask for GCash, bank transfers, or any prepayment before delivery. Report scams to our official Facebook page.', 'swiftcart-cod' ); ?>
		</p>
	</div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SITE FOOTER
═══════════════════════════════════════════════════════ -->
<footer class="sc-footer">
	<div class="sc-container">
		<div class="sc-footer__grid">
			<!-- Brand Column -->
			<div class="sc-footer__brand">
				<h3 class="sc-footer__brand-title">🏍 MotoShop Parts</h3>
				<p class="sc-footer__brand-desc">
					<?php esc_html_e( 'Your trusted source for quality motorcycle parts in the Philippines. Cash on Delivery — no prepayment needed.', 'swiftcart-cod' ); ?>
				</p>
				<div class="sc-footer__social">
					<a href="#" class="sc-footer__social-link" aria-label="Facebook">📘</a>
					<a href="#" class="sc-footer__social-link" aria-label="Instagram">📸</a>
					<a href="#" class="sc-footer__social-link" aria-label="TikTok">🎵</a>
				</div>
			</div>

			<!-- Quick Links -->
			<div>
				<h4 class="sc-footer__col-title"><?php esc_html_e( 'Shop', 'swiftcart-cod' ); ?></h4>
				<ul class="sc-footer__links">
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All Products', 'swiftcart-cod' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/engine-parts/' ) ); ?>"><?php esc_html_e( 'Engine Parts', 'swiftcart-cod' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/brake-systems/' ) ); ?>"><?php esc_html_e( 'Brake Systems', 'swiftcart-cod' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/electrical/' ) ); ?>"><?php esc_html_e( 'Electrical', 'swiftcart-cod' ); ?></a></li>
				</ul>
			</div>

			<!-- Customer Support -->
			<div>
				<h4 class="sc-footer__col-title"><?php esc_html_e( 'Support', 'swiftcart-cod' ); ?></h4>
				<ul class="sc-footer__links">
					<li><a href="#how-cod-works"><?php esc_html_e( 'How COD Works', 'swiftcart-cod' ); ?></a></li>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Track My Order', 'swiftcart-cod' ); ?></a></li>
					<li><a href="#faq"><?php esc_html_e( 'FAQs', 'swiftcart-cod' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/return-policy/' ) ); ?>"><?php esc_html_e( 'Return Policy', 'swiftcart-cod' ); ?></a></li>
				</ul>
			</div>

			<!-- Contact -->
			<div>
				<h4 class="sc-footer__col-title"><?php esc_html_e( 'Contact Us', 'swiftcart-cod' ); ?></h4>
				<ul class="sc-footer__links">
					<li>📞 0917-123-4567</li>
					<li>📧 support@motoshopparts.ph</li>
					<li>📍 <?php esc_html_e( 'Metro Manila, Philippines', 'swiftcart-cod' ); ?></li>
					<li>⏰ <?php esc_html_e( 'Mon–Sat, 8AM–6PM', 'swiftcart-cod' ); ?></li>
				</ul>
			</div>
		</div>

		<!-- Bottom Bar -->
		<div class="sc-footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> MotoShop Parts. <?php esc_html_e( 'All rights reserved.', 'swiftcart-cod' ); ?></span>
			<div class="sc-footer__badges">
				<span class="sc-footer__badge">💳 COD Accepted</span>
				<span class="sc-footer__badge">↩ 7-Day Returns</span>
				<span class="sc-footer__badge">🛵 Nationwide Delivery</span>
			</div>
		</div>
	</div>
</footer>

<?php get_footer(); ?>

<script>
/* Homepage JS — Announcement bar + FAQ accordion + Scroll reveals */
( function () {
	'use strict';

	/* ── Announcement bar dismiss ──────────────────────── */
	var bar    = document.querySelector( '.sc-announcement-bar' );
	var closer = document.querySelector( '.sc-announcement-bar__close' );
	if ( bar && closer ) {
		if ( sessionStorage.getItem( 'sc_ann_closed' ) ) {
			bar.style.display = 'none';
		}
		closer.addEventListener( 'click', function () {
			bar.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
			bar.style.opacity    = '0';
			bar.style.transform  = 'translateY(-100%)';
			setTimeout( function () {
				bar.style.display = 'none';
			}, 300 );
			sessionStorage.setItem( 'sc_ann_closed', '1' );
		} );

		/* Rotate with fade transition (items use CSS opacity/transform) */
		var items = bar.querySelectorAll( '.sc-announcement-bar__item' );
		if ( items.length > 1 ) {
			var current = 0;
			setInterval( function () {
				items[ current ].classList.remove( 'sc-announcement-bar__item--active' );
				current = ( current + 1 ) % items.length;
				items[ current ].classList.add( 'sc-announcement-bar__item--active' );
			}, 4000 );
		}
	}

	/* ── FAQ accordion (smooth slide) ─────────────────── */
	document.querySelectorAll( '.sc-faq__question' ).forEach( function ( btn ) {
		var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
		var item  = btn.closest( '.sc-faq__item' );

		/* Initialize: first item expanded, rest collapsed */
		if ( btn.getAttribute( 'aria-expanded' ) === 'true' && panel ) {
			panel.removeAttribute( 'hidden' );
			panel.setAttribute( 'data-visible', 'true' );
			if ( item ) { item.setAttribute( 'data-open', 'true' ); }
		} else if ( panel ) {
			panel.removeAttribute( 'hidden' );
			panel.setAttribute( 'data-visible', 'false' );
			if ( item ) { item.setAttribute( 'data-open', 'false' ); }
		}

		btn.addEventListener( 'click', function () {
			var expanded = this.getAttribute( 'aria-expanded' ) === 'true';

			/* Close all */
			document.querySelectorAll( '.sc-faq__question' ).forEach( function ( b ) {
				b.setAttribute( 'aria-expanded', 'false' );
				var p = document.getElementById( b.getAttribute( 'aria-controls' ) );
				var i = b.closest( '.sc-faq__item' );
				if ( p ) { p.setAttribute( 'data-visible', 'false' ); }
				if ( i ) { i.setAttribute( 'data-open', 'false' ); }
			} );

			/* Open clicked (toggle) */
			if ( ! expanded ) {
				this.setAttribute( 'aria-expanded', 'true' );
				if ( panel ) { panel.setAttribute( 'data-visible', 'true' ); }
				if ( item ) { item.setAttribute( 'data-open', 'true' ); }
			}
		} );
	} );

	/* ── Scroll-triggered section reveals ─────────────── */
	var revealSections = document.querySelectorAll(
		'.sc-trust-bar, .sc-categories, .sc-featured-products, .sc-how-cod, .sc-coverage, .sc-faq-preview'
	);

	if ( 'IntersectionObserver' in window && revealSections.length ) {
		revealSections.forEach( function ( section ) {
			section.classList.add( 'sc-reveal' );
		} );

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'sc-visible' );
					observer.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' } );

		revealSections.forEach( function ( section ) {
			observer.observe( section );
		} );
	}
} )();
</script>
