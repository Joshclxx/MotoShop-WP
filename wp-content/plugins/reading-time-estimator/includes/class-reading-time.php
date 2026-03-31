<?php
/**
 * Core reading-time logic.
 *
 * @package ReadingTimeEstimator
 */

namespace ReadingTimeEstimator;

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Reading_Time
 *
 * Calculates an estimated reading time and prepends it to single post content.
 */
class Reading_Time {

	/**
	 * Average adult reading speed in words per minute.
	 *
	 * Filterable via 'rte_words_per_minute'.
	 *
	 * @var int
	 */
	private int $words_per_minute = 200;

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_filter( 'the_content', array( $this, 'prepend_reading_time' ) );
	}

	/**
	 * Prepend the reading-time banner to post content.
	 *
	 * Only runs on single posts (not pages, archives, or the_excerpt).
	 *
	 * @param string $content The original post content.
	 * @return string Modified content with reading-time banner prepended.
	 */
	public function prepend_reading_time( string $content ): string {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$minutes = $this->calculate( $content );
		$banner  = $this->render_banner( $minutes );

		return $banner . $content;
	}

	/**
	 * Calculate estimated reading time in minutes.
	 *
	 * @param string $content Raw post content (may contain HTML).
	 * @return int Estimated minutes, minimum 1.
	 */
	public function calculate( string $content ): int {
		// Strip shortcodes and HTML tags before counting.
		$plain_text  = strip_shortcodes( $content );
		$plain_text  = wp_strip_all_tags( $plain_text );
		$word_count  = str_word_count( $plain_text );

		/**
		 * Filter the words-per-minute reading speed used for the estimate.
		 *
		 * @param int $wpm Words per minute. Default 200.
		 */
		$wpm = (int) apply_filters( 'rte_words_per_minute', $this->words_per_minute );
		$wpm = max( 1, $wpm ); // Guard against zero/negative values.

		$minutes = (int) ceil( $word_count / $wpm );

		return max( 1, $minutes );
	}

	/**
	 * Render the reading-time HTML banner.
	 *
	 * @param int $minutes Estimated reading time in minutes.
	 * @return string Escaped HTML string.
	 */
	private function render_banner( int $minutes ): string {
		$label = sprintf(
			/* translators: %d: estimated reading time in minutes */
			_n(
				'%d minute read',
				'%d minute read',
				$minutes,
				'reading-time-estimator'
			),
			$minutes
		);

		/**
		 * Filter the full reading-time label string before output.
		 *
		 * @param string $label   The translated label, e.g. "5 minute read".
		 * @param int    $minutes Estimated minutes.
		 */
		$label = apply_filters( 'rte_label', $label, $minutes );

		return sprintf(
			'<p class="rte-reading-time">%s %s</p>',
			'<span class="rte-icon" aria-hidden="true">&#128214;</span>',
			esc_html( $label )
		);
	}
}
