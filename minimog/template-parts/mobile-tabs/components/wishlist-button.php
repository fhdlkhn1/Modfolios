<?php
/**
 * Wishlist button on mobile tabs
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverWoosw' ) ) {
	return;
}

$wishlist_url = WPCleverWoosw::get_url();
$count        = WPCleverWoosw::get_count();
$icon_type    = Minimog::setting( 'header_wishlist_icon_type' );
$icon_key     = 'heart' === $icon_type ? 'heart' : 'star';
?>
<a href="<?php echo esc_url( $wishlist_url ) ?>" class="mobile-tab-link has-badge wishlist-link"
	<?php if ( empty( $args['show_title'] ) ) : ?>
		aria-label="<?php esc_attr_e( 'Wishlist', 'minimog' ); ?>"
	<?php endif; ?>
>
	<div class="icon" aria-hidden="true" focusable="false" role="presentation">
		<?php echo Minimog_SVG_Manager::instance()->get( $icon_key ); ?>
		<span class="icon-badge"><?php echo esc_html( $count ); ?></span>
	</div>
	<?php if ( ! empty( $args['show_title'] ) ) : ?>
		<div class="mobile-tab-link--title"><?php esc_html_e( 'Wishlist','minimog' ); ?></div>
	<?php endif ?>
</a>
