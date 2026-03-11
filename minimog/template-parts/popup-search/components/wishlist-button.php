<?php
/**
 * Wishlist button on search popup
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
switch ( Minimog::setting( 'header_icons_style' ) ) :
	case 'icon-set-02':
		$icon_key = 'heart' === $icon_type ? 'heart-light' : 'star-light';
		break;
	case 'icon-set-03':
		$icon_key = 'heart' === $icon_type ? 'phr-heart' : 'phr-star';
		break;
	case 'icon-set-04':
		$icon_key = 'heart' === $icon_type ? 'heart-solid' : 'star-solid';
		break;
	case 'icon-set-05':
		$icon_key = 'heart' === $icon_type ? 'phb-heart' : 'phb-star';
		break;
	default :
		$icon_key = 'heart' === $icon_type ? 'heart' : 'star';
		break;
endswitch;
?>
<a href="<?php echo esc_url( WPCleverWoosw::get_url() ) ?>"
   class="popup-search-icon has-badge wishlist-link hint--bounce hint--bottom" aria-label="<?php esc_attr_e( 'Wishlist', 'minimog' ); ?>">
	<div class="icon">
		<?php echo Minimog_SVG_Manager::instance()->get( $icon_key ); ?>
		<span class="icon-badge"><?php echo esc_html( WPCleverWoosw::get_count() ); ?></span>
	</div>
</a>
