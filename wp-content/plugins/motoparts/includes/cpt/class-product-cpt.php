<?php
/**
 * Product custom post type registration.
 *
 * Registers the motoparts_product CPT with a custom capability type so that
 * WordPress generates granular per-CPT capabilities (edit_motoparts_product,
 * delete_motoparts_products, etc.) instead of reusing the built-in post caps.
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts\CPT;

defined( 'ABSPATH' ) || exit;

/**
 * Class Product_CPT
 *
 * @since 1.0.0
 */
class Product_CPT {

	/**
	 * Post type slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const POST_TYPE = 'motoparts_product';

	/**
	 * Attach WP hooks.
	 *
	 * Called from MotoParts::init() during plugins_loaded. Actual CPT
	 * registration is deferred to the init hook.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_cpt' ) );
	}

	/**
	 * Register the motoparts_product post type.
	 *
	 * Also called directly from Activator::activate() (before flush_rewrite_rules)
	 * to ensure the rewrite slug is in the registry at activation time.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_cpt(): void {
		$labels = array(
			'name'                     => _x( 'Parts', 'post type general name', 'motoparts' ),
			'singular_name'            => _x( 'Part', 'post type singular name', 'motoparts' ),
			'add_new'                  => __( 'Add New', 'motoparts' ),
			'add_new_item'             => __( 'Add New Part', 'motoparts' ),
			'edit_item'                => __( 'Edit Part', 'motoparts' ),
			'new_item'                 => __( 'New Part', 'motoparts' ),
			'view_item'                => __( 'View Part', 'motoparts' ),
			'view_items'               => __( 'View Parts', 'motoparts' ),
			'search_items'             => __( 'Search Parts', 'motoparts' ),
			'not_found'                => __( 'No parts found.', 'motoparts' ),
			'not_found_in_trash'       => __( 'No parts found in Trash.', 'motoparts' ),
			'parent_item_colon'        => __( 'Parent Part:', 'motoparts' ),
			'all_items'                => __( 'All Parts', 'motoparts' ),
			'archives'                 => __( 'Part Archives', 'motoparts' ),
			'attributes'               => __( 'Part Attributes', 'motoparts' ),
			'insert_into_item'         => __( 'Insert into part', 'motoparts' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this part', 'motoparts' ),
			'featured_image'           => __( 'Part Image', 'motoparts' ),
			'set_featured_image'       => __( 'Set part image', 'motoparts' ),
			'remove_featured_image'    => __( 'Remove part image', 'motoparts' ),
			'use_featured_image'       => __( 'Use as part image', 'motoparts' ),
			'menu_name'                => _x( 'Parts', 'admin menu', 'motoparts' ),
			'filter_items_list'        => __( 'Filter parts list', 'motoparts' ),
			'filter_by_date'           => __( 'Filter by date', 'motoparts' ),
			'items_list_navigation'    => __( 'Parts list navigation', 'motoparts' ),
			'items_list'               => __( 'Parts list', 'motoparts' ),
			'item_published'           => __( 'Part published.', 'motoparts' ),
			'item_published_privately' => __( 'Part published privately.', 'motoparts' ),
			'item_reverted_to_draft'   => __( 'Part reverted to draft.', 'motoparts' ),
			'item_scheduled'           => __( 'Part scheduled.', 'motoparts' ),
			'item_updated'             => __( 'Part updated.', 'motoparts' ),
			'item_link'                => __( 'Part Link', 'motoparts' ),
			'item_link_description'    => __( 'A link to a part.', 'motoparts' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-car',
			'menu_position'       => 26,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
			'rewrite'             => array(
				'slug'       => 'parts',
				'with_front' => false,
			),
			'capability_type'     => 'motoparts_product',
			'map_meta_cap'        => true,
			'has_archive'         => 'parts',
			'query_var'           => true,
			'hierarchical'        => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
