<?php
/**
 * Lost password form — Branded card UI.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>

<div class="sc-account-wrapper">

	<!-- Header -->
	<div class="sc-account-header">
		<h2>🏍 MotoShop Parts</h2>
		<p><?php esc_html_e( 'Reset your password', 'swiftcart-cod' ); ?></p>
	</div>

	<div class="sc-account-body">

		<div class="sc-tab-panel active">

			<p class="sc-lost-pw__intro">
				<?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Enter your username or email address and we\'ll send you a link to reset your password.', 'swiftcart-cod' ) ); // phpcs:ignore ?>
			</p>

			<form method="post" class="woocommerce-ResetPassword lost_reset_password" novalidate>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="user_login"><?php esc_html_e( 'Email or Username', 'swiftcart-cod' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true" />
				</p>

				<?php do_action( 'woocommerce_lostpassword_form' ); ?>

				<input type="hidden" name="wc_reset_password" value="true" />
				<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

				<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
					<?php esc_html_e( 'Reset Password', 'swiftcart-cod' ); ?>
				</button>

				<p class="sc-lost-pw__back">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">&larr; <?php esc_html_e( 'Back to Login', 'swiftcart-cod' ); ?></a>
				</p>

			</form>

		</div>

	</div>

</div>

<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
