<?php
/**
 * Plugin deactivation routines.
 *
 * Capabilities are intentionally left in place on deactivation — users must
 * not lose permissions simply because the plugin is temporarily disabled.
 * Capabilities are removed only on full uninstall via Activator::uninstall().
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts;

defined( 'ABSPATH' ) || exit;

/**
 * Class Deactivator
 *
 * @since 1.0.0
 */
class Deactivator {

	/**
	 * Run deactivation tasks.
	 *
	 * Flushes rewrite rules so the CPT slugs no longer occupy the rewrite table
	 * while the plugin is inactive.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
