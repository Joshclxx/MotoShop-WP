<?php
/**
 * Reset password form — Branded card UI.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>

<div class="sc-account-wrapper">

	<!-- Header -->
	<div class="sc-account-header">
		<h2>🏍 MotoShop Parts</h2>
		<p><?php esc_html_e( 'Create a new password', 'swiftcart-cod' ); ?></p>
	</div>

	<div class="sc-account-body">

		<div class="sc-tab-panel active">

			<p class="sc-lost-pw__intro">
				<?php echo apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Enter a new password below.', 'woocommerce' ) ); // phpcs:ignore ?>
			</p>

			<form method="post" class="woocommerce-ResetPassword lost_reset_password" novalidate>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password_1"><?php esc_html_e( 'New Password', 'swiftcart-cod' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" required aria-required="true" />
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password_2"><?php esc_html_e( 'Confirm New Password', 'swiftcart-cod' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true" />
				</p>

				<p class="sc-show-password">
					<label><input type="checkbox" class="sc-show-pw-checkbox" data-fields="password_1,password_2" /> <?php esc_html_e( 'Show password', 'swiftcart-cod' ); ?></label>
				</p>

				<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
				<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />
				<input type="hidden" name="wc_reset_password" value="true" />

				<?php do_action( 'woocommerce_resetpassword_form' ); ?>

				<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

				<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
					<?php esc_html_e( 'Save Password', 'swiftcart-cod' ); ?>
				</button>

				<p class="sc-lost-pw__back">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">&larr; <?php esc_html_e( 'Back to Login', 'swiftcart-cod' ); ?></a>
				</p>

			</form>

		</div>

	</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.sc-show-pw-checkbox').forEach(function (cb) {
		cb.addEventListener('change', function () {
			var fields = this.getAttribute('data-fields').split(',');
			var type = this.checked ? 'text' : 'password';
			fields.forEach(function (id) {
				var input = document.getElementById(id.trim());
				if (input) input.type = type;
			});
		});
	});
});
</script>

<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
