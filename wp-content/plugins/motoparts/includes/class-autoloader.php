<?php
/**
 * PSR-4-style class autoloader.
 *
 * Maps MotoParts namespace prefixes to filesystem paths and registers
 * an SPL autoload handler so no manual require_once calls are needed.
 *
 * Filename convention: class name lowercased, underscores replaced with
 * hyphens, prepended with "class-". Example:
 *   MotoParts\CPT\Product_CPT  →  includes/cpt/class-product-cpt.php
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 *
 * @since 1.0.0
 */
class Autoloader {

	/**
	 * Namespace-prefix → directory map.
	 *
	 * More-specific prefixes must appear before their parent prefixes so the
	 * first match wins. The catch-all MotoParts\\ entry must be last.
	 *
	 * @since 1.0.0
	 * @var array<string, string>
	 */
	private static array $map = array();

	/**
	 * Register the autoloader with the SPL stack.
	 *
	 * Called once from the plugin entry file before any namespaced class
	 * is referenced.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function register(): void {
		// Build the map here (after constants are defined).
		self::$map = array(
			'MotoParts\\CPT\\'      => MOTOPARTS_DIR . 'includes/cpt/',
			'MotoParts\\DB\\'       => MOTOPARTS_DIR . 'includes/db/',
			'MotoParts\\Admin\\'    => MOTOPARTS_DIR . 'includes/admin/',
			'MotoParts\\Frontend\\' => MOTOPARTS_DIR . 'includes/frontend/',
			'MotoParts\\REST\\'     => MOTOPARTS_DIR . 'includes/rest/',
			'MotoParts\\Ajax\\'     => MOTOPARTS_DIR . 'includes/ajax/',
			'MotoParts\\'           => MOTOPARTS_DIR . 'includes/',  // catch-all — must be last.
		);

		spl_autoload_register( array( static::class, 'load' ) );
	}

	/**
	 * Attempt to load a class file for the given fully-qualified class name.
	 *
	 * Silently returns if no matching prefix is found or the file does not
	 * exist — other registered autoloaders will get a chance to resolve it.
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

			// Strip the namespace prefix to get the relative class name.
			$relative = substr( $class, strlen( $prefix ) );

			// Convert namespace separators to directory separators.
			$relative = str_replace( '\\', '/', $relative );

			// Extract just the class name (last segment after any sub-namespace).
			$parts      = explode( '/', $relative );
			$class_name = array_pop( $parts );
			$sub_path   = implode( '/', $parts );

			// Convert class name to WPCS filename format:
			// underscores → hyphens, all lowercase, prefixed with "class-".
			$filename = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

			// Reconstruct the full path.
			$path = $base_dir;
			if ( '' !== $sub_path ) {
				$path .= trailingslashit( $sub_path );
			}
			$path .= $filename;

			if ( file_exists( $path ) ) {
				require_once $path;
			}

			return;
		}
	}
}
