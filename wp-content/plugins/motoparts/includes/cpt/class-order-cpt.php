<?php
/**
 * Order custom post type and status taxonomy registration.
 *
 * Orders are internal records — not publicly browsable — so the CPT is
 * registered with public:false. A custom taxonomy (motoparts_order_status)
 * is used for order workflow states instead of post statuses, giving us
 * a clean admin UI with filterable columns.
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts\CPT;

defined( 'ABSPATH' ) || exit;

/**
 * Class Order_CPT
 *
 * @since 1.0.0
 */
class Order_CPT {

	/**
	 * Post type slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const POST_TYPE = 'motoparts_order';

	/**
	 * Taxonomy slug for order statuses.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TAXONOMY = 'motoparts_order_status';

	/**
	 * Default order status terms to seed on activation.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	const DEFAULT_STATUSES = array( 'pending', 'processing', 'completed', 'cancelled', 'refunded' );

	/**
	 * Attach WP hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_status_taxonomy' ) );
	}

	/**
	 * Register the motoparts_order post type.
	 *
	 * Also called directly from Activator::activate() to ensure rewrites are
	 * registered before flush_rewrite_rules() runs.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_cpt(): void {
		$labels = array(
			'name'                     => _x( 'Orders', 'post type general name', 'motoparts' ),
			'singular_name'            => _x( 'Order', 'post type singular name', 'motoparts' ),
			'add_new'                  => __( 'Add New', 'motoparts' ),
			'add_new_item'             => __( 'Add New Order', 'motoparts' ),
			'edit_item'                => __( 'Edit Order', 'motoparts' ),
			'new_item'                 => __( 'New Order', 'motoparts' ),
			'view_item'                => __( 'View Order', 'motoparts' ),
			'view_items'               => __( 'View Orders', 'motoparts' ),
			'search_items'             => __( 'Search Orders', 'motoparts' ),
			'not_found'                => __( 'No orders found.', 'motoparts' ),
			'not_found_in_trash'       => __( 'No orders found in Trash.', 'motoparts' ),
			'all_items'                => __( 'All Orders', 'motoparts' ),
			'archives'                 => __( 'Order Archives', 'motoparts' ),
			'attributes'               => __( 'Order Attributes', 'motoparts' ),
			'insert_into_item'         => __( 'Insert into order', 'motoparts' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this order', 'motoparts' ),
			'menu_name'                => _x( 'Orders', 'admin menu', 'motoparts' ),
			'filter_items_list'        => __( 'Filter orders list', 'motoparts' ),
			'filter_by_date'           => __( 'Filter by date', 'motoparts' ),
			'items_list_navigation'    => __( 'Orders list navigation', 'motoparts' ),
			'items_list'               => __( 'Orders list', 'motoparts' ),
			'item_published'           => __( 'Order published.', 'motoparts' ),
			'item_published_privately' => __( 'Order published privately.', 'motoparts' ),
			'item_reverted_to_draft'   => __( 'Order reverted to draft.', 'motoparts' ),
			'item_scheduled'           => __( 'Order scheduled.', 'motoparts' ),
			'item_updated'             => __( 'Order updated.', 'motoparts' ),
			'item_link'                => __( 'Order Link', 'motoparts' ),
			'item_link_description'    => __( 'A link to an order.', 'motoparts' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-clipboard',
			'menu_position'      => 27,
			'supports'           => array( 'title', 'custom-fields' ),
			'rewrite'            => false,
			'capability_type'    => 'motoparts_order',
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'query_var'          => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register the order status taxonomy.
	 *
	 * Internal taxonomy — not public, not REST-exposed — used purely for
	 * filtering and status display in the admin list table.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_status_taxonomy(): void {
		$labels = array(
			'name'          => _x( 'Order Statuses', 'taxonomy general name', 'motoparts' ),
			'singular_name' => _x( 'Order Status', 'taxonomy singular name', 'motoparts' ),
			'all_items'     => __( 'All Statuses', 'motoparts' ),
			'edit_item'     => __( 'Edit Status', 'motoparts' ),
			'update_item'   => __( 'Update Status', 'motoparts' ),
			'add_new_item'  => __( 'Add New Status', 'motoparts' ),
			'new_item_name' => __( 'New Status Name', 'motoparts' ),
			'menu_name'     => __( 'Statuses', 'motoparts' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => false,
			'query_var'         => false,
		);

		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE ), $args );
	}

	/**
	 * Seed default order status taxonomy terms.
	 *
	 * Called from Activator::activate(). Skips terms that already exist so
	 * this is safe to call on re-activation.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function seed_default_statuses(): void {
		foreach ( self::DEFAULT_STATUSES as $slug ) {
			if ( ! term_exists( $slug, self::TAXONOMY ) ) {
				wp_insert_term(
					ucfirst( $slug ),
					self::TAXONOMY,
					array( 'slug' => $slug )
				);
			}
		}
	}
}
