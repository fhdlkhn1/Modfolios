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
?>
<a href="<?php echo esc_url( home_url() ) ?>" class="mobile-tab-link"
	<?php if ( empty( $args['show_title'] ) ) : ?>
		aria-label="<?php esc_attr_e( 'Home', 'minimog' ); ?>"
	<?php endif; ?>
>
	<div class="icon" aria-hidden="true" focusable="false" role="presentation">
		<?php echo Minimog_SVG_Manager::instance()->get( 'home-alt' ); ?>
	</div>
	<?php if ( ! empty( $args['show_title'] ) ) : ?>
		<div class="mobile-tab-link--title"><?php esc_html_e( 'Home', 'minimog' ); ?></div>
	<?php endif ?>
</a>
