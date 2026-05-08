<?php
/**
 * Lost password confirmation — Branded card UI.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;

wc_print_notice( esc_html__( 'Password reset email has been sent.', 'woocommerce' ) );
?>

<?php do_action( 'woocommerce_before_lost_password_confirmation_message' ); ?>

<div class="sc-account-wrapper">

	<!-- Header -->
	<div class="sc-account-header">
		<h2>🏍 MotoShop Parts</h2>
		<p><?php esc_html_e( 'Check your email', 'swiftcart-cod' ); ?></p>
	</div>

	<div class="sc-account-body">

		<div class="sc-tab-panel active">

			<div class="sc-lost-pw__confirmation">
				<div class="sc-lost-pw__icon">✉️</div>
				<p class="sc-lost-pw__intro">
					<?php echo esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', esc_html__( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'woocommerce' ) ) ); ?>
				</p>
			</div>

			<p class="sc-lost-pw__back">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">&larr; <?php esc_html_e( 'Back to Login', 'swiftcart-cod' ); ?></a>
			</p>

		</div>

	</div>

</div>

<?php do_action( 'woocommerce_after_lost_password_confirmation_message' ); ?>
