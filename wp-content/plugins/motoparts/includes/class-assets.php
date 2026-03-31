<?php
/**
 * Asset enqueue manager.
 *
 * Enqueues frontend and admin scripts/styles with:
 *  - motoparts-* handle prefix.
 *  - filemtime()-based version hash for reliable cache-busting.
 *  - Scripts loaded in the footer to avoid render-blocking.
 *  - wp_localize_script() to pass PHP data (nonce, AJAX URL) to JS.
 *  - Admin assets scoped to MotoParts screens only (no bloat on every page).
 *
 * @package MotoParts
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace MotoParts;

defined( 'ABSPATH' ) || exit;

/**
 * Class Assets
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Attach WP enqueue hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_frontend' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}

	/**
	 * Enqueue frontend stylesheet and script.
	 *
	 * Passes a motoParts JS object with ajaxUrl, nonce, and version so
	 * frontend JS can make authenticated AJAX requests.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_frontend(): void {
		wp_enqueue_style(
			'motoparts-frontend',
			MOTOPARTS_URL . 'assets/css/motoparts-frontend.css',
			array(),
			$this->file_version( MOTOPARTS_DIR . 'assets/css/motoparts-frontend.css' )
		);

		wp_enqueue_script(
			'motoparts-frontend',
			MOTOPARTS_URL . 'assets/js/motoparts-frontend.js',
			array( 'jquery' ),
			$this->file_version( MOTOPARTS_DIR . 'assets/js/motoparts-frontend.js' ),
			true // Load in footer.
		);

		wp_localize_script(
			'motoparts-frontend',
			'motoParts',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'motoparts_frontend' ),
				'version' => MOTOPARTS_VERSION,
			)
		);
	}

	/**
	 * Enqueue admin stylesheet and script.
	 *
	 * Scoped to MotoParts admin screens only — checks get_current_screen()
	 * to avoid loading on unrelated admin pages.
	 *
	 * @since  1.0.0
	 * @param  string $hook Current admin page hook suffix (unused; we use screen object).
	 * @return void
	 */
	public function enqueue_admin( string $hook ): void {
		$screen = get_current_screen();

		if ( null === $screen ) {
			return;
		}

		// Load on MotoParts CPT edit screens and any toplevel MotoParts page.
		$mp_post_types = array( 'motoparts_product', 'motoparts_order' );
		$is_mp_screen  = in_array( $screen->post_type, $mp_post_types, true )
			|| str_starts_with( $screen->id, 'motoparts' )
			|| str_starts_with( $screen->id, 'toplevel_page_motoparts' );

		if ( ! $is_mp_screen ) {
			return;
		}

		wp_enqueue_style(
			'motoparts-admin',
			MOTOPARTS_URL . 'assets/css/motoparts-admin.css',
			array(),
			$this->file_version( MOTOPARTS_DIR . 'assets/css/motoparts-admin.css' )
		);

		wp_enqueue_script(
			'motoparts-admin',
			MOTOPARTS_URL . 'assets/js/motoparts-admin.js',
			array( 'jquery', 'wp-util' ),
			$this->file_version( MOTOPARTS_DIR . 'assets/js/motoparts-admin.js' ),
			true // Load in footer.
		);

		wp_localize_script(
			'motoparts-admin',
			'motoPartsAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'motoparts_admin' ),
				'version' => MOTOPARTS_VERSION,
			)
		);
	}

	/**
	 * Return a cache-busting version string for an asset file.
	 *
	 * Uses filemtime() so the version updates automatically whenever the file
	 * changes. Falls back to MOTOPARTS_VERSION if the file does not exist yet
	 * (e.g. during development before assets are compiled).
	 *
	 * @since  1.0.0
	 * @param  string $path Absolute filesystem path to the asset file.
	 * @return string Version string.
	 */
	private function file_version( string $path ): string {
		return file_exists( $path ) ? (string) filemtime( $path ) : MOTOPARTS_VERSION;
	}
}
