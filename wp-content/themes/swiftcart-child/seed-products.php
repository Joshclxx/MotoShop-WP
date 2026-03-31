<?php
/**
 * MotoShop Parts Product Seeder
 *
 * Run via: php wp-content/themes/swiftcart-child/seed-products.php
 *
 * Creates motor parts categories and 8 products with images.
 * Safe to re-run — skips products that already exist.
 */

/* ── Bootstrap WordPress ──────────────────────────── */
$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	echo "Error: Cannot find wp-load.php at {$wp_load}\n";
	exit( 1 );
}
require_once $wp_load;

if ( ! class_exists( 'WooCommerce' ) ) {
	echo "Error: WooCommerce is not active.\n";
	exit( 1 );
}

echo "=== MotoShop Parts Product Seeder ===\n\n";

/* ── Categories ───────────────────────────────────── */
$categories = array(
	array( 'name' => 'Brake Parts',         'slug' => 'brake-parts',         'icon' => '🔧' ),
	array( 'name' => 'Engine Parts',         'slug' => 'engine-parts',        'icon' => '⚙️' ),
	array( 'name' => 'Tires & Wheels',       'slug' => 'tires-wheels',        'icon' => '🏍️' ),
	array( 'name' => 'Lights & Electrical',  'slug' => 'lights-electrical',   'icon' => '💡' ),
);

$cat_ids = array();
foreach ( $categories as $cat ) {
	$existing = get_term_by( 'slug', $cat['slug'], 'product_cat' );
	if ( $existing ) {
		$cat_ids[ $cat['slug'] ] = $existing->term_id;
		echo "  Category '{$cat['name']}' already exists (ID: {$existing->term_id})\n";
	} else {
		$result = wp_insert_term( $cat['name'], 'product_cat', array(
			'slug' => $cat['slug'],
		) );
		if ( is_wp_error( $result ) ) {
			echo "  Error creating category '{$cat['name']}': {$result->get_error_message()}\n";
			continue;
		}
		$cat_ids[ $cat['slug'] ] = $result['term_id'];
		echo "  Created category '{$cat['name']}' (ID: {$result['term_id']})\n";
	}
}

echo "\n";

/* ── Products ─────────────────────────────────────── */
$upload_dir = wp_upload_dir();
$upload_path = $upload_dir['basedir'] . '/2026/03';
$upload_url  = $upload_dir['baseurl'] . '/2026/03';

$products = array(
	array(
		'name'         => 'Performance Brake Pad Set (Front & Rear)',
		'slug'         => 'performance-brake-pad-set',
		'price'        => '450',
		'regular'      => '599',
		'sku'          => 'MTP-BRK-001',
		'category'     => 'brake-parts',
		'description'  => 'High-performance organic brake pads for reliable stopping power. Includes front and rear sets, pre-chamfered for noise-free operation. Compatible with Honda, Yamaha, Suzuki, and Kawasaki motorcycles.',
		'short_desc'   => 'Front & rear organic brake pads with heat-resistant compound.',
		'image'        => 'brake-pads.png',
		'stock'        => 25,
	),
	array(
		'name'         => 'NGK Iridium IX Spark Plugs (Set of 4)',
		'slug'         => 'ngk-iridium-spark-plugs',
		'price'        => '320',
		'regular'      => '380',
		'sku'          => 'MTP-ENG-001',
		'category'     => 'engine-parts',
		'description'  => 'Premium NGK Iridium IX spark plugs with ultra-fine 0.6mm center electrode. Delivers superior ignitability, improved throttle response, and better fuel efficiency. Set of 4 plugs.',
		'short_desc'   => 'Iridium IX plugs for superior ignition and fuel efficiency.',
		'image'        => 'spark-plugs.png',
		'stock'        => 40,
	),
	array(
		'name'         => 'OEM Engine Oil Filter',
		'slug'         => 'oem-engine-oil-filter',
		'price'        => '120',
		'regular'      => '',
		'sku'          => 'MTP-ENG-002',
		'category'     => 'engine-parts',
		'description'  => 'OEM-grade motorcycle engine oil filter with anti-drainback valve and silicone gasket. Ensures clean oil circulation and optimal engine protection. Fits most 150cc-400cc models.',
		'short_desc'   => 'OEM-quality oil filter for 150cc - 400cc models.',
		'image'        => 'oil-filter.png',
		'stock'        => 60,
	),
	array(
		'name'         => 'H4 LED Headlight Bulb with Cooling Fan',
		'slug'         => 'h4-led-headlight-bulb',
		'price'        => '580',
		'regular'      => '750',
		'sku'          => 'MTP-LIT-001',
		'category'     => 'lights-electrical',
		'description'  => 'Ultra-bright H4 LED headlight bulb producing 6,000 lumens in a 6000K daylight white beam. Built-in turbo cooling fan prevents heat damage. Plug-and-play installation for most motorcycles.',
		'short_desc'   => '6000LM LED bulb with cooling fan, 6000K daylight output.',
		'image'        => 'led-headlight.png',
		'stock'        => 18,
	),
	array(
		'name'         => 'Gold O-Ring Chain & Sprocket Kit',
		'slug'         => 'gold-chain-sprocket-kit',
		'price'        => '1350',
		'regular'      => '1680',
		'sku'          => 'MTP-DRV-001',
		'category'     => 'engine-parts',
		'description'  => 'Complete drive kit: 428 gold O-ring chain (120 links) with hardened steel front and rear sprockets. O-ring sealed rollers for extended chain life. Includes master link.',
		'short_desc'   => '428 gold O-ring chain + front/rear sprocket set.',
		'image'        => 'chain-sprocket.png',
		'stock'        => 12,
	),
	array(
		'name'         => 'CNC Aluminum Side Mirrors (Pair)',
		'slug'         => 'cnc-aluminum-side-mirrors',
		'price'        => '280',
		'regular'      => '350',
		'sku'          => 'MTP-ACC-001',
		'category'     => 'lights-electrical',
		'description'  => 'Premium CNC-machined aluminum side mirrors with convex glass for a wide viewing angle. Universal 10mm threading, foldable arms. Anti-vibration rubber mounts included.',
		'short_desc'   => 'CNC aluminum mirrors with convex glass, universal 10mm.',
		'image'        => 'side-mirrors.png',
		'stock'        => 30,
	),
	array(
		'name'         => 'MotoMax 10W-40 Synthetic Engine Oil (1L)',
		'slug'         => 'motomax-10w40-engine-oil',
		'price'        => '199',
		'regular'      => '',
		'sku'          => 'MTP-OIL-001',
		'category'     => 'engine-parts',
		'description'  => 'Full synthetic 10W-40 motorcycle engine oil formulated for JASO MA2 wet-clutch compatibility. Superior thermal stability and anti-wear protection for 4-stroke engines.',
		'short_desc'   => 'Full synthetic 10W-40, JASO MA2 compliant.',
		'image'        => 'engine-oil.png',
		'stock'        => 50,
	),
	array(
		'name'         => 'Sport Touring Motorcycle Tire (120/70-17)',
		'slug'         => 'sport-touring-tire-120-70-17',
		'price'        => '2100',
		'regular'      => '2450',
		'sku'          => 'MTP-TIR-001',
		'category'     => 'tires-wheels',
		'description'  => 'Sport touring radial tire with dual-compound tread for optimal grip in wet and dry conditions. Size 120/70 ZR17, tubeless. DOT and ECE approved.',
		'short_desc'   => 'Dual-compound 120/70-17 radial tire, wet & dry grip.',
		'image'        => 'motorcycle-tire.png',
		'stock'        => 8,
	),
);

foreach ( $products as $product_data ) {
	/* Skip if product already exists */
	$existing = get_page_by_path( $product_data['slug'], OBJECT, 'product' );
	if ( $existing ) {
		echo "  Product '{$product_data['name']}' already exists — skipping.\n";
		continue;
	}

	/* Create the product */
	$product = new WC_Product_Simple();
	$product->set_name( $product_data['name'] );
	$product->set_slug( $product_data['slug'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( $product_data['description'] );
	$product->set_short_description( $product_data['short_desc'] );
	$product->set_sku( $product_data['sku'] );
	$product->set_price( $product_data['price'] );
	$product->set_regular_price( $product_data['regular'] ?: $product_data['price'] );
	if ( $product_data['regular'] ) {
		$product->set_sale_price( $product_data['price'] );
	}
	$product->set_manage_stock( true );
	$product->set_stock_quantity( $product_data['stock'] );
	$product->set_stock_status( 'instock' );

	/* Assign category */
	if ( isset( $cat_ids[ $product_data['category'] ] ) ) {
		$product->set_category_ids( array( $cat_ids[ $product_data['category'] ] ) );
	}

	/* Attach image */
	$image_path = $upload_path . '/' . $product_data['image'];
	if ( file_exists( $image_path ) ) {
		$attachment_id = create_attachment( $image_path, $upload_url . '/' . $product_data['image'], $product_data['name'] );
		if ( $attachment_id ) {
			$product->set_image_id( $attachment_id );
		}
	} else {
		echo "  Warning: Image not found at {$image_path}\n";
	}

	$product_id = $product->save();
	echo "  Created: '{$product_data['name']}' (ID: {$product_id})\n";
}

echo "\n=== Seeder complete! ===\n";

/* ── Helper: Create WP attachment from file ──────── */
function create_attachment( $file_path, $file_url, $title ) {
	$filetype = wp_check_filetype( basename( $file_path ) );
	$attachment = array(
		'guid'           => $file_url,
		'post_mime_type' => $filetype['type'],
		'post_title'     => sanitize_file_name( $title ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $file_path );
	if ( is_wp_error( $attach_id ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
}
