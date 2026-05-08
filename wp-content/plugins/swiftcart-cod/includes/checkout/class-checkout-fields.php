<?php
/**
 * WooCommerce checkout field customisations for the Philippine address format.
 *
 * Adds: Province (before City), Barangay (after City), Landmark (required).
 * Removes: Address line 2 (replaced by Landmark), Company.
 * Adds +63 mobile prefix and "rider will call" helper text to the phone field.
 * Validates Barangay + City against delivery coverage on form submit.
 * Checks phone number against the blacklist before allowing checkout.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace SwiftCart\Checkout;

defined( 'ABSPATH' ) || exit;

/**
 * Class Checkout_Fields
 *
 * @since 1.0.0
 */
class Checkout_Fields {

	/**
	 * Attach WooCommerce hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'woocommerce_checkout_fields',         array( $this, 'modify_checkout_fields' ) );
		// Note: woocommerce_default_address_fields is intentionally NOT hooked —
		// it applies to both billing AND shipping, which causes WC to require our
		// custom fields on the shipping address too and block checkout.
		add_action( 'woocommerce_checkout_process',        array( $this, 'validate_checkout' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_fields' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'display_custom_fields_admin' ) );
		add_action( 'wp_ajax_swiftcart_get_cities',        array( $this, 'ajax_get_cities' ) );
		add_action( 'wp_ajax_nopriv_swiftcart_get_cities', array( $this, 'ajax_get_cities' ) );
		add_action( 'wp_ajax_swiftcart_get_barangays',     array( $this, 'ajax_get_barangays' ) );
		add_action( 'wp_ajax_nopriv_swiftcart_get_barangays', array( $this, 'ajax_get_barangays' ) );
		add_action( 'wp_enqueue_scripts',                  array( $this, 'enqueue_checkout_assets' ) );

		// COD always delivers to billing address — hide "ship to different address".
		add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
	}

	/**
	 * Modify checkout fields — billing section only.
	 *
	 * Adds Province, Barangay, Landmark directly to billing so they never
	 * bleed into the shipping address set (which would trigger WC required-
	 * field validation on fields the customer never sees).
	 *
	 * @since  1.0.0
	 * @param  array<string,array<string,mixed>> $fields WooCommerce checkout fields.
	 * @return array<string,array<string,mixed>>
	 */
	public function modify_checkout_fields( array $fields ): array {
		// ── Remove unused billing fields ─────────────────────────────────────
		unset( $fields['billing']['billing_company'] );
		unset( $fields['billing']['billing_address_2'] );

		// ── Phone field ───────────────────────────────────────────────────────
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['label']       = __( 'Mobile Number', 'swiftcart-cod' );
			$fields['billing']['billing_phone']['placeholder'] = '09XX XXX XXXX';
			$fields['billing']['billing_phone']['description'] = __( 'Our rider will call this number before delivery.', 'swiftcart-cod' );
			$fields['billing']['billing_phone']['priority']    = 5;
		}

		// ── Name priority ─────────────────────────────────────────────────────
		if ( isset( $fields['billing']['billing_first_name'] ) ) {
			$fields['billing']['billing_first_name']['priority'] = 10;
		}
		if ( isset( $fields['billing']['billing_last_name'] ) ) {
			$fields['billing']['billing_last_name']['priority'] = 20;
		}

		// ── Province (select) ─────────────────────────────────────────────────
		$fields['billing']['billing_province'] = array(
			'label'    => __( 'Province / Region', 'swiftcart-cod' ),
			'required' => true,
			'class'    => array( 'form-row-wide', 'sc-province-select' ),
			'type'     => 'select',
			'options'  => array_merge(
				array( '' => __( 'Select Province', 'swiftcart-cod' ) ),
				$this->get_provinces()
			),
			'priority' => 65,
		);

		// ── City label ────────────────────────────────────────────────────────
		if ( isset( $fields['billing']['billing_city'] ) ) {
			$fields['billing']['billing_city']['label']    = __( 'City / Municipality', 'swiftcart-cod' );
			$fields['billing']['billing_city']['class']    = array( 'form-row-wide', 'sc-city-select' );
			$fields['billing']['billing_city']['priority'] = 70;
		}

		// ── Barangay (text) ───────────────────────────────────────────────────
		$fields['billing']['billing_barangay'] = array(
			'label'    => __( 'Barangay', 'swiftcart-cod' ),
			'required' => true,
			'class'    => array( 'form-row-wide', 'sc-barangay-select' ),
			'type'     => 'text',
			'priority' => 75,
		);

		// ── Street address ────────────────────────────────────────────────────
		if ( isset( $fields['billing']['billing_address_1'] ) ) {
			$fields['billing']['billing_address_1']['label']       = __( 'Street Address', 'swiftcart-cod' );
			$fields['billing']['billing_address_1']['placeholder'] = __( 'House No., Street Name', 'swiftcart-cod' );
			$fields['billing']['billing_address_1']['priority']    = 80;
		}

		// ── Landmark (text, required) ─────────────────────────────────────────
		$fields['billing']['billing_landmark'] = array(
			'label'       => __( 'Landmark', 'swiftcart-cod' ),
			'placeholder' => __( 'e.g., Near 7-Eleven, beside Blue Gate', 'swiftcart-cod' ),
			'description' => __( 'Helps our rider find you faster.', 'swiftcart-cod' ),
			'required'    => true,
			'class'       => array( 'form-row-wide' ),
			'type'        => 'text',
			'priority'    => 85,
		);

		// ── Postcode ──────────────────────────────────────────────────────────
		if ( isset( $fields['billing']['billing_postcode'] ) ) {
			$fields['billing']['billing_postcode']['priority'] = 90;
		}

		return $fields;
	}

	/**
	 * Validate custom fields on checkout submission.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function validate_checkout(): void {
		// Barangay required.
		$barangay = sanitize_text_field( wp_unslash( $_POST['billing_barangay'] ?? '' ) );
		if ( '' === $barangay ) {
			wc_add_notice(
				__( 'Please enter your Barangay.', 'swiftcart-cod' ),
				'error'
			);
		}

		// Landmark required.
		$landmark = sanitize_text_field( wp_unslash( $_POST['billing_landmark'] ?? '' ) );
		if ( '' === $landmark ) {
			wc_add_notice(
				__( 'Please enter a Landmark to help our rider find you.', 'swiftcart-cod' ),
				'error'
			);
		}

		// Phone format: must start with 09 or +63 and be 11 digits (local format).
		$phone = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ?? '' ) );
		$phone = preg_replace( '/\D/', '', $phone );
		if ( ! preg_match( '/^(0\d{10}|63\d{10})$/', $phone ) ) {
			wc_add_notice(
				__( 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX).', 'swiftcart-cod' ),
				'error'
			);
		}

		// Blacklist check.
		if ( '' !== $phone ) {
			$raw_phone = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ?? '' ) );
			if ( $this->is_phone_blacklisted( $raw_phone ) ) {
				wc_add_notice(
					__( 'We are unable to process orders from this number at this time. Please contact support.', 'swiftcart-cod' ),
					'error'
				);
			}
		}
	}

	/**
	 * Save custom checkout fields to order meta.
	 *
	 * @since  1.0.0
	 * @param  int $order_id WooCommerce order ID.
	 * @return void
	 */
	public function save_custom_fields( int $order_id ): void {
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$fields = array(
			'billing_barangay' => '_billing_barangay',
			'billing_landmark' => '_billing_landmark',
			'billing_province' => '_billing_province',
		);

		foreach ( $fields as $post_key => $meta_key ) {
			if ( ! isset( $_POST[ $post_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				continue;
			}

			$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$order->update_meta_data( $meta_key, $value );
		}

		$order->save_meta_data();
	}

	/**
	 * Display custom address fields in the WooCommerce order admin screen.
	 *
	 * @since  1.0.0
	 * @param  \WC_Order $order Current order.
	 * @return void
	 */
	public function display_custom_fields_admin( \WC_Order $order ): void {
		$barangay = $order->get_meta( '_billing_barangay' );
		$landmark = $order->get_meta( '_billing_landmark' );
		$province = $order->get_meta( '_billing_province' );

		if ( $province ) {
			echo '<p><strong>' . esc_html__( 'Province:', 'swiftcart-cod' ) . '</strong> ' . esc_html( $province ) . '</p>';
		}

		if ( $barangay ) {
			echo '<p><strong>' . esc_html__( 'Barangay:', 'swiftcart-cod' ) . '</strong> ' . esc_html( $barangay ) . '</p>';
		}

		if ( $landmark ) {
			echo '<p><strong>' . esc_html__( 'Landmark:', 'swiftcart-cod' ) . '</strong> ' . esc_html( $landmark ) . '</p>';
		}
	}

	/**
	 * AJAX handler: return cities for a given province slug.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function ajax_get_cities(): void {
		check_ajax_referer( 'swiftcart_checkout', 'nonce' );

		$province = sanitize_text_field( wp_unslash( $_POST['province'] ?? '' ) );
		$data     = require SWIFTCART_DIR . 'data/ph-locations.php';
		$cities   = $data[ $province ] ?? array();

		wp_send_json_success( array( 'cities' => array_keys( $cities ) ) );
	}

	/**
	 * AJAX handler: return barangays for a given city.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function ajax_get_barangays(): void {
		check_ajax_referer( 'swiftcart_checkout', 'nonce' );

		$province = sanitize_text_field( wp_unslash( $_POST['province'] ?? '' ) );
		$city     = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
		$data     = require SWIFTCART_DIR . 'data/ph-locations.php';
		$barangays = $data[ $province ][ $city ] ?? array();

		wp_send_json_success( array( 'barangays' => $barangays ) );
	}

	/**
	 * Enqueue checkout-specific JS and pass data.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_checkout_assets(): void {
		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_script(
			'swiftcart-checkout',
			SWIFTCART_URL . 'assets/js/checkout.js',
			array( 'jquery', 'wc-checkout' ),
			(string) filemtime( SWIFTCART_DIR . 'assets/js/checkout.js' ),
			true
		);

		wp_localize_script(
			'swiftcart-checkout',
			'swiftcartCheckout',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'swiftcart_checkout' ),
				'cutoffHour'  => (int) get_option( 'swiftcart_cutoff_hour', 14 ),
				'codFee'      => (float) get_option( 'swiftcart_cod_fee', 20 ),
				'callThreshold' => (float) get_option( 'swiftcart_call_threshold', 3000 ),
				'i18n'        => array(
					'selectCity'     => __( 'Select City / Municipality', 'swiftcart-cod' ),
					'selectBarangay' => __( 'Enter Barangay', 'swiftcart-cod' ),
					'loading'        => __( 'Loading...', 'swiftcart-cod' ),
					'stepAddress'    => __( 'Address', 'swiftcart-cod' ),
					'stepShipping'   => __( 'Shipping', 'swiftcart-cod' ),
					'stepConfirm'    => __( 'Confirm', 'swiftcart-cod' ),
					'continue'       => __( 'Continue', 'swiftcart-cod' ),
					'back'           => __( 'Back', 'swiftcart-cod' ),
					'placeOrder'     => __( 'Place Order — Pay on Delivery', 'swiftcart-cod' ),
					'prepareAmount'  => __( 'Please prepare ₱%s in cash upon delivery.', 'swiftcart-cod' ),
					'cutoffGreen'    => __( 'Order before %s for same-day dispatch. Current time: %s', 'swiftcart-cod' ),
					'cutoffAmber'    => __( 'Cutoff in %s minutes! Order now for same-day dispatch.', 'swiftcart-cod' ),
					'verifyCall'     => __( 'A verification call will be required for this order.', 'swiftcart-cod' ),
				),
			)
		);
	}

	/**
	 * Check if a phone number is on the blacklist.
	 *
	 * @since  1.0.0
	 * @param  string $phone Phone number (raw from form).
	 * @return bool
	 */
	private function is_phone_blacklisted( string $phone ): bool {
		global $wpdb;

		$phone_clean = preg_replace( '/\D/', '', $phone );

		$blacklisted = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}sc_blacklist WHERE phone = %s AND active = 1",
				$phone_clean
			)
		);

		return (int) $blacklisted > 0;
	}

	/**
	 * Return the list of Philippine provinces.
	 *
	 * @since  1.0.0
	 * @return array<string,string> Slug => Display name.
	 */
	private function get_provinces(): array {
		$data = require SWIFTCART_DIR . 'data/ph-locations.php';

		return array_map(
			static fn( string $key ) => ucwords( str_replace( '-', ' ', $key ) ),
			array_combine( array_keys( $data ), array_keys( $data ) )
		);
	}
}
