<?php
/**
 * Plugin deactivation routines.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart;

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
	 * @since  1.0.0
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
