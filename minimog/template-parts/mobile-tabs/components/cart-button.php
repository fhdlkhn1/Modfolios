<?php
/**
 * Mini cart button on mobile tabs
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! Minimog_Woo::instance()->is_activated() ) {
	return;
}

global $woocommerce;
$qty = ! empty( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

$cart_badge_html = '<div class="icon-badge mini-cart-badge" data-count="' . $qty . '">' . $qty . '</div>';
?>
<a href="<?php echo isset( $woocommerce ) ? esc_url( wc_get_cart_url() ) : '/cart'; ?>" class="mini-cart__button has-badge mobile-tab-link"
	<?php if ( empty( $args['show_title'] ) ) : ?>
		aria-label="<?php esc_attr_e( 'Cart', 'minimog' ); ?>"
	<?php endif; ?>
>
	<?php echo '<div class="icon" aria-hidden="true" focusable="false" role="presentation">' . Minimog_SVG_Manager::instance()->get( 'shopping-bag' ) . $cart_badge_html . '</div>'; ?>
	<?php if ( ! empty( $args['show_title'] ) ) : ?>
		<div class="mobile-tab-link--title"><?php echo esc_html__( 'Cart', 'minimog' ); ?></div>
	<?php endif ?>
</a>
