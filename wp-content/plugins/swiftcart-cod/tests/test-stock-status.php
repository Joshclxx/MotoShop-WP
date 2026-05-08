<?php
/**
 * Tests for Stock_Status logic.
 *
 * @package SwiftCart
 * @since   1.5.0
 */

declare( strict_types=1 );

use SwiftCart\Stock\Stock_Status;

/**
 * Class Test_Stock_Status
 *
 * Unit tests for the Stock_Status product label system.
 */
class Test_Stock_Status extends WP_UnitTestCase {

	/**
	 * System under test.
	 *
	 * @var Stock_Status
	 */
	private Stock_Status $sut;

	/**
	 * Set up test fixtures.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->sut = new Stock_Status();
	}

	/**
	 * Test that all valid status slugs are accepted.
	 *
	 * @dataProvider valid_status_provider
	 */
	public function test_valid_statuses_accepted( string $status ): void {
		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );
		update_post_meta( $product_id, Stock_Status::META_KEY, $status );

		$result = $this->sut->get_status( $product_id );

		$this->assertSame( $status, $result, "Status '{$status}' should be returned as-is." );
	}

	/**
	 * Data provider for valid stock statuses.
	 *
	 * @return array<string,array<string>>
	 */
	public static function valid_status_provider(): array {
		return array(
			'in_stock'     => array( 'in_stock' ),
			'low_stock'    => array( 'low_stock' ),
			'out_of_stock' => array( 'out_of_stock' ),
		);
	}

	/**
	 * Test that invalid status values default to 'in_stock'.
	 *
	 * @dataProvider invalid_status_provider
	 */
	public function test_invalid_status_defaults_to_in_stock( string $status ): void {
		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );
		update_post_meta( $product_id, Stock_Status::META_KEY, $status );

		$result = $this->sut->get_status( $product_id );

		$this->assertSame( 'in_stock', $result, "Invalid status '{$status}' should default to 'in_stock'." );
	}

	/**
	 * Data provider for invalid stock statuses.
	 *
	 * @return array<string,array<string>>
	 */
	public static function invalid_status_provider(): array {
		return array(
			'empty string'    => array( '' ),
			'arbitrary value' => array( 'discontinued' ),
			'sql injection'   => array( "'; DROP TABLE wp_posts; --" ),
			'numeric'         => array( '42' ),
		);
	}

	/**
	 * Test that a product without any stock meta defaults to 'in_stock'.
	 */
	public function test_no_meta_defaults_to_in_stock(): void {
		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );

		$result = $this->sut->get_status( $product_id );

		$this->assertSame( 'in_stock', $result, 'Product without stock meta should default to in_stock.' );
	}

	/**
	 * Test that the badge HTML contains the correct CSS class for each status.
	 *
	 * @dataProvider valid_status_provider
	 */
	public function test_badge_html_contains_correct_class( string $status ): void {
		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );
		update_post_meta( $product_id, Stock_Status::META_KEY, $status );

		$html = $this->sut->get_badge_html( $product_id );
		$expected_class = 'sc-stock-badge--' . str_replace( '_', '-', $status );

		$this->assertStringContainsString(
			$expected_class,
			$html,
			"Badge HTML should contain class '{$expected_class}'."
		);
	}

	/**
	 * Test that availability text is overridden for all statuses.
	 *
	 * @dataProvider availability_provider
	 */
	public function test_availability_text_overridden( string $status, string $expected_text ): void {
		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );
		update_post_meta( $product_id, Stock_Status::META_KEY, $status );

		$product = new \WC_Product_Simple( $product_id );
		$result  = $this->sut->filter_availability(
			array( 'availability' => '', 'class' => '' ),
			$product
		);

		$this->assertSame(
			$expected_text,
			$result['availability'],
			"Availability text for '{$status}' should be '{$expected_text}'."
		);
	}

	/**
	 * Data provider for availability text expectations.
	 *
	 * @return array<string,array<string>>
	 */
	public static function availability_provider(): array {
		return array(
			'in_stock'     => array( 'in_stock', 'In Stock' ),
			'low_stock'    => array( 'low_stock', 'Low Stock — Order Soon' ),
			'out_of_stock' => array( 'out_of_stock', 'Out of Stock' ),
		);
	}
}
