<?php
/**
 * Login / Registration Form — Buyer-facing My Account page.
 *
 * Overrides WooCommerce default to provide a tabbed, branded login
 * and registration experience for customers.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_customer_login_form' );

$enable_registration = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
?>

<div class="sc-account-wrapper">

	<!-- Header -->
	<div class="sc-account-header">
		<h2>🏍 MotoShop Parts</h2>
		<p><?php esc_html_e( 'Quality motor parts delivered to your door — COD accepted', 'swiftcart-cod' ); ?></p>
	</div>

	<?php if ( $enable_registration ) : ?>
	<!-- Tab Switcher -->
	<div class="sc-account-tabs">
		<button class="sc-account-tab active" data-target="sc-login-tab" type="button">
			<?php esc_html_e( 'Login', 'swiftcart-cod' ); ?>
		</button>
		<button class="sc-account-tab" data-target="sc-register-tab" type="button">
			<?php esc_html_e( 'Create Account', 'swiftcart-cod' ); ?>
		</button>
	</div>
	<?php endif; ?>

	<div class="sc-account-body">

		<!-- ─── Login Tab ──────────────────────────────────────────────── -->
		<div id="sc-login-tab" class="sc-tab-panel active">

			<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'Email or Username', 'swiftcart-cod' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e( 'Password', 'swiftcart-cod' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
				</p>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<p class="form-row" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme" style="margin:0;">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
						<span><?php esc_html_e( 'Remember me', 'swiftcart-cod' ); ?></span>
					</label>
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="font-size:0.85rem;color:#518123;">
						<?php esc_html_e( 'Forgot password?', 'swiftcart-cod' ); ?>
					</a>
				</p>

				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Log In', 'swiftcart-cod' ); ?>
				</button>

				<?php do_action( 'woocommerce_login_form_end' ); ?>

			</form>

			<?php if ( $enable_registration ) : ?>
			<div class="sc-divider"><?php esc_html_e( 'or', 'swiftcart-cod' ); ?></div>
			<p style="text-align:center;margin:0;">
				<button type="button" class="sc-switch-to-register" style="background:none;border:none;color:#518123;font-weight:600;cursor:pointer;font-size:0.95rem;">
					<?php esc_html_e( 'Create a new account →', 'swiftcart-cod' ); ?>
				</button>
			</p>
			<?php endif; ?>

		</div>

		<?php if ( $enable_registration ) : ?>
		<!-- ─── Register Tab ───────────────────────────────────────────── -->
		<div id="sc-register-tab" class="sc-tab-panel">

			<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
				</p>
				<?php endif; ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" />
				</p>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
				</p>
				<?php else : ?>
				<p style="font-size:0.85rem;color:#888;"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<p class="woocommerce-form-row form-row">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
						<?php esc_html_e( 'Create Account', 'swiftcart-cod' ); ?>
					</button>
				</p>

				<?php do_action( 'woocommerce_register_form_end' ); ?>

			</form>

			<div class="sc-divider"><?php esc_html_e( 'or', 'swiftcart-cod' ); ?></div>
			<p style="text-align:center;margin:0;">
				<button type="button" class="sc-switch-to-login" style="background:none;border:none;color:#518123;font-weight:600;cursor:pointer;font-size:0.95rem;">
					<?php esc_html_e( '← Back to Login', 'swiftcart-cod' ); ?>
				</button>
			</p>

		</div>
		<?php endif; ?>

	</div><!-- .sc-account-body -->

</div><!-- .sc-account-wrapper -->

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var tabs   = document.querySelectorAll('.sc-account-tab');
	var panels = document.querySelectorAll('.sc-tab-panel');

	function switchTab(targetId) {
		tabs.forEach(function (t) { t.classList.remove('active'); });
		panels.forEach(function (p) { p.classList.remove('active'); });
		var activeTab = document.querySelector('[data-target="' + targetId + '"]');
		if (activeTab) activeTab.classList.add('active');
		var activePanel = document.getElementById(targetId);
		if (activePanel) activePanel.classList.add('active');
	}

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			switchTab(this.getAttribute('data-target'));
		});
	});

	// "Create a new account" link
	var switchToReg = document.querySelector('.sc-switch-to-register');
	if (switchToReg) {
		switchToReg.addEventListener('click', function () { switchTab('sc-register-tab'); });
	}

	// "Back to Login" link
	var switchToLogin = document.querySelector('.sc-switch-to-login');
	if (switchToLogin) {
		switchToLogin.addEventListener('click', function () { switchTab('sc-login-tab'); });
	}

	// Auto-switch to register tab if URL has #register
	if (window.location.hash === '#register') {
		switchTab('sc-register-tab');
	}
});
</script>
