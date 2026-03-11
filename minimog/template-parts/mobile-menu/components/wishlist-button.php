<?php
/**
 * Wishlist button on mobile menu
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverWoosw' ) ) {
	return;
}

$icon_type = Minimog::setting( 'header_wishlist_icon_type' );
$icon_key  = 'heart' === $icon_type ? 'heart' : 'star';
?>
<a href="<?php echo esc_url( WPCleverWoosw::get_url() ) ?>"
   class="mobile-menu-wishlist-link">
	<div class="button-icon"><?php echo Minimog_SVG_Manager::instance()->get( $icon_key ); ?></div>
	<div class="button-text"><?php printf( esc_html__( 'My Wishlist (%1$s)', 'minimog' ), '<span class="count">' . WPCleverWoosw::get_count() . '</span>' ); ?></div>
</a>
