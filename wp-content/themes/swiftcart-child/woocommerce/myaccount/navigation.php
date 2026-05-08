<?php
/**
 * My Account navigation — MotoShop Parts
 *
 * Branded sidebar navigation with user avatar header and
 * emoji icons per menu item.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package SwiftCart
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user       = wp_get_current_user();
$avatar_url = get_avatar_url( $user->ID, array( 'size' => 80 ) );
$first_name = $user->first_name ?: $user->display_name ?: $user->user_login;



do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">

	<!-- User mini-profile -->
	<div class="sc-nav-profile">
		<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" class="sc-nav-profile__avatar" width="48" height="48" />
		<div class="sc-nav-profile__info">
			<span class="sc-nav-profile__name"><?php echo esc_html( $first_name ); ?></span>
			<span class="sc-nav-profile__role"><?php esc_html_e( 'Customer', 'swiftcart-cod' ); ?></span>
		</div>
	</div>

	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
			$is_active = wc_is_current_account_menu_item( $endpoint );
		?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
