<?php
/**
 * Shop link on mobile tabs
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! Minimog_Woo::instance()->is_activated() ) {
	return;
}
$link_url     = get_permalink( wc_get_page_id( 'shop' ) );
?>
<a href="<?php echo esc_url( $link_url ) ?>" class="mobile-tab-link"
	<?php if ( empty( $args['show_title'] ) ) : ?>
		aria-label="<?php esc_attr_e( 'Shop', 'minimog' ); ?>"
	<?php endif; ?>
>
	<div class="icon" aria-hidden="true" focusable="false" role="presentation">
		<?php echo Minimog_SVG_Manager::instance()->get( 'grid' ); ?>
	</div>
	<?php if ( ! empty( $args['show_title'] ) ) : ?>
		<div class="mobile-tab-link--title"><?php esc_html_e( 'Shop','minimog' ); ?></div>
	<?php endif ?>
</a>
