<?php
/**
 * Custom role capabilities management.
 *
 * Assigns MotoParts-specific capabilities to WordPress roles on plugin
 * activation and removes them on uninstall. Capabilities are intentionally
 * left in place on deactivation so users retain permissions while the plugin
 * is temporarily disabled.
 *
 * CPT-generated caps for motoparts_product and motoparts_order are also
 * assigned here because WordPress only auto-generates them when map_meta_cap
 * is true, but it does NOT auto-assign them to any role — that is our job.
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Capabilities
 *
 * @since 1.0.0
 */
class Capabilities {

	/**
	 * Attach WP hooks.
	 *
	 * No dynamic hooks are needed for Milestone 1 — caps are assigned
	 * statically during activation. This method is a placeholder so the
	 * class follows the same register() contract as other modules.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		// No runtime hooks for Milestone 1.
	}

	/**
	 * Assign all MotoParts capabilities to the appropriate roles.
	 *
	 * Called from Activator::activate(). Creates the customer role if it does
	 * not exist (plain WordPress installs ship only subscriber, contributor,
	 * author, editor, and administrator).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_caps(): void {
		// Administrator — full access.
		$admin = get_role( 'administrator' );
		if ( $admin instanceof \WP_Role ) {
			foreach ( $this->get_admin_caps() as $cap ) {
				$admin->add_cap( $cap, true );
			}
		}

		// Customer role — create if absent.
		$customer = get_role( 'customer' );
		if ( ! $customer instanceof \WP_Role ) {
			add_role(
				'customer',
				__( 'Customer', 'motoparts' ),
				array()
			);
			$customer = get_role( 'customer' );
		}

		if ( $customer instanceof \WP_Role ) {
			foreach ( $this->get_customer_caps() as $cap ) {
				$customer->add_cap( $cap, true );
			}
		}

		// Subscriber — browse-only access.
		$subscriber = get_role( 'subscriber' );
		if ( $subscriber instanceof \WP_Role ) {
			foreach ( $this->get_public_caps() as $cap ) {
				$subscriber->add_cap( $cap, true );
			}
		}
	}

	/**
	 * Remove all MotoParts capabilities from all roles.
	 *
	 * Called from Activator::uninstall(). Also removes the customer role if
	 * it was created by this plugin (identified by having no other caps beyond
	 * the MotoParts set).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function remove_caps(): void {
		$all_caps = array_merge(
			$this->get_admin_caps(),
			$this->get_customer_caps(),
			$this->get_public_caps()
		);

		$role_slugs = array( 'administrator', 'customer', 'subscriber' );

		foreach ( $role_slugs as $slug ) {
			$role = get_role( $slug );
			if ( ! $role instanceof \WP_Role ) {
				continue;
			}

			foreach ( $all_caps as $cap ) {
				$role->remove_cap( $cap );
			}
		}

		// Remove the customer role only if it is now empty (we created it).
		$customer = get_role( 'customer' );
		if ( $customer instanceof \WP_Role && empty( $customer->capabilities ) ) {
			remove_role( 'customer' );
		}
	}

	/**
	 * Return all capabilities to grant to the administrator role.
	 *
	 * Includes the 6 custom MotoParts caps plus the full set of WordPress-
	 * generated CPT primitive caps for both motoparts_product and
	 * motoparts_order (required because we use a custom capability_type).
	 *
	 * @since  1.0.0
	 * @return string[]
	 */
	private function get_admin_caps(): array {
		return array_merge(
			array(
				// Custom MotoParts capabilities.
				'manage_motoparts_products',
				'manage_motoparts_orders',
				'view_motoparts_reports',
				'motoparts_checkout',
				'view_motoparts_account',
				'browse_motoparts_shop',
			),
			$this->get_cpt_primitive_caps( 'motoparts_product' ),
			$this->get_cpt_primitive_caps( 'motoparts_order' )
		);
	}

	/**
	 * Return capabilities to grant to the customer role.
	 *
	 * @since  1.0.0
	 * @return string[]
	 */
	private function get_customer_caps(): array {
		return array(
			'motoparts_checkout',
			'view_motoparts_account',
			'browse_motoparts_shop',
		);
	}

	/**
	 * Return capabilities to grant to the public (subscriber/guest) role.
	 *
	 * @since  1.0.0
	 * @return string[]
	 */
	private function get_public_caps(): array {
		return array(
			'browse_motoparts_shop',
		);
	}

	/**
	 * Build the full set of WordPress primitive caps for a custom CPT.
	 *
	 * WordPress generates these cap names automatically from the capability_type
	 * string but does not assign them to any role — that must be done manually.
	 *
	 * @since  1.0.0
	 * @param  string $type CPT capability_type value (e.g. 'motoparts_product').
	 * @return string[]
	 */
	private function get_cpt_primitive_caps( string $type ): array {
		$plural = $type . 's';

		return array(
			"edit_{$type}",
			"read_{$type}",
			"delete_{$type}",
			"edit_{$plural}",
			"edit_others_{$plural}",
			"publish_{$plural}",
			"read_private_{$plural}",
			"delete_{$plural}",
			"delete_others_{$plural}",
			"delete_private_{$plural}",
			"delete_published_{$plural}",
			"edit_private_{$plural}",
			"edit_published_{$plural}",
		);
	}
}
