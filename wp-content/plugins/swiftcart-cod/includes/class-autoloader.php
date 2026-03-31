<?php
/**
 * SwiftCart COD autoloader.
 *
 * Maps SwiftCart namespace prefixes to filesystem paths.
 * Filename convention: class name lowercased, underscores → hyphens, prefixed with "class-".
 * Example: SwiftCart\Checkout\Checkout_Fields → includes/checkout/class-checkout-fields.php
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 *
 * @since 1.0.0
 */
class Autoloader {

	/**
	 * Namespace-prefix → directory map (most-specific first).
	 *
	 * @since 1.0.0
	 * @var array<string,string>
	 */
	private static array $map = array();

	/**
	 * Register the SPL autoloader.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function register(): void {
		self::$map = array(
			'SwiftCart\\Checkout\\'  => SWIFTCART_DIR . 'includes/checkout/',
			'SwiftCart\\Stock\\'     => SWIFTCART_DIR . 'includes/stock/',
			'SwiftCart\\Admin\\'     => SWIFTCART_DIR . 'includes/admin/',
			'SwiftCart\\Warehouse\\' => SWIFTCART_DIR . 'includes/warehouse/',
			'SwiftCart\\'            => SWIFTCART_DIR . 'includes/',
		);

		spl_autoload_register( array( static::class, 'load' ) );
	}

	/**
	 * Load a class file.
	 *
	 * @since  1.0.0
	 * @param  string $class Fully-qualified class name.
	 * @return void
	 */
	public static function load( string $class ): void {
		foreach ( self::$map as $prefix => $base_dir ) {
			if ( ! str_starts_with( $class, $prefix ) ) {
				continue;
			}

			$relative   = substr( $class, strlen( $prefix ) );
			$parts      = explode( '\\', $relative );
			$class_name = array_pop( $parts );
			$sub_path   = implode( '/', $parts );

			$filename = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

			$path = $base_dir;
			if ( '' !== $sub_path ) {
				$path .= trailingslashit( strtolower( $sub_path ) );
			}
			$path .= $filename;

			if ( file_exists( $path ) ) {
				require_once $path;
			}

			return;
		}
	}
}
