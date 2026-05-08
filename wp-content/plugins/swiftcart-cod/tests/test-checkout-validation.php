<?php
/**
 * Tests for Checkout_Fields validation logic.
 *
 * @package SwiftCart
 * @since   1.5.0
 */

declare( strict_types=1 );

use SwiftCart\Checkout\Checkout_Fields;

/**
 * Class Test_Checkout_Validation
 *
 * Tests for the checkout field validation in Checkout_Fields::validate_checkout().
 * These tests use WooCommerce notice functions to verify validation errors.
 */
class Test_Checkout_Validation extends WP_UnitTestCase {

	/**
	 * System under test.
	 *
	 * @var Checkout_Fields
	 */
	private Checkout_Fields $sut;

	/**
	 * Set up test fixtures.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->sut = new Checkout_Fields();

		// Clear WC notices before each test.
		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}
	}

	/**
	 * Tear down after each test.
	 */
	public function tear_down(): void {
		$_POST = array();
		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}
		parent::tear_down();
	}

	/**
	 * Test that missing barangay field triggers a validation error.
	 */
	public function test_missing_barangay_triggers_error(): void {
		$_POST['billing_barangay'] = '';
		$_POST['billing_landmark'] = 'Near 7-Eleven';
		$_POST['billing_phone']    = '09171234567';

		$this->sut->validate_checkout();

		$notices = wc_get_notices( 'error' );
		$messages = array_column( $notices, 'notice' );

		$this->assertTrue(
			in_array( 'Please enter your Barangay.', $messages, true ),
			'Missing barangay should produce a validation error.'
		);
	}

	/**
	 * Test that missing landmark field triggers a validation error.
	 */
	public function test_missing_landmark_triggers_error(): void {
		$_POST['billing_barangay'] = 'San Antonio';
		$_POST['billing_landmark'] = '';
		$_POST['billing_phone']    = '09171234567';

		$this->sut->validate_checkout();

		$notices = wc_get_notices( 'error' );
		$messages = array_column( $notices, 'notice' );

		$this->assertTrue(
			in_array( 'Please enter a Landmark to help our rider find you.', $messages, true ),
			'Missing landmark should produce a validation error.'
		);
	}

	/**
	 * Test that a valid PH mobile number (09XX format) passes validation.
	 */
	public function test_valid_phone_09_format_passes(): void {
		$_POST['billing_barangay'] = 'San Antonio';
		$_POST['billing_landmark'] = 'Near 7-Eleven';
		$_POST['billing_phone']    = '09171234567';

		$this->sut->validate_checkout();

		$notices = wc_get_notices( 'error' );
		$messages = array_column( $notices, 'notice' );

		$this->assertFalse(
			in_array( 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX).', $messages, true ),
			'Valid 09XX phone should not produce a phone error.'
		);
	}

	/**
	 * Test that a valid PH mobile number (+63 format) passes validation.
	 */
	public function test_valid_phone_63_format_passes(): void {
		$_POST['billing_barangay'] = 'San Antonio';
		$_POST['billing_landmark'] = 'Near 7-Eleven';
		$_POST['billing_phone']    = '+639171234567';

		$this->sut->validate_checkout();

		$notices = wc_get_notices( 'error' );
		$messages = array_column( $notices, 'notice' );

		$this->assertFalse(
			in_array( 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX).', $messages, true ),
			'Valid +63 phone should not produce a phone error.'
		);
	}

	/**
	 * Test that an invalid phone number triggers a validation error.
	 *
	 * @dataProvider invalid_phone_provider
	 */
	public function test_invalid_phone_triggers_error( string $phone ): void {
		$_POST['billing_barangay'] = 'San Antonio';
		$_POST['billing_landmark'] = 'Near 7-Eleven';
		$_POST['billing_phone']    = $phone;

		$this->sut->validate_checkout();

		$notices = wc_get_notices( 'error' );
		$messages = array_column( $notices, 'notice' );

		$this->assertTrue(
			in_array( 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX).', $messages, true ),
			"Phone '{$phone}' should produce a validation error."
		);

		wc_clear_notices();
	}

	/**
	 * Data provider for invalid phone numbers.
	 *
	 * @return array<string,array<string>>
	 */
	public static function invalid_phone_provider(): array {
		return array(
			'too short'       => array( '0917123456' ),
			'too long'        => array( '091712345678' ),
			'wrong prefix'    => array( '12345678901' ),
			'letters mixed'   => array( '0917abc4567' ),
			'empty string'    => array( '' ),
		);
	}

	/**
	 * Test that all fields valid produces no errors.
	 */
	public function test_all_valid_produces_no_errors(): void {
		$_POST['billing_barangay'] = 'Poblacion';
		$_POST['billing_landmark'] = 'Beside blue gate';
		$_POST['billing_phone']    = '09171234567';

		$this->sut->validate_checkout();

		$notices = wc_get_notices( 'error' );

		$this->assertEmpty( $notices, 'Valid checkout data should produce no errors.' );
	}
}
