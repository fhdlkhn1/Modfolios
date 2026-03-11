<?php
/**
 * Search button open search popup on mobile tabs
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" class="page-open-popup-search mobile-tab-link"
	<?php if ( empty( $args['show_title'] ) ) : ?>
		aria-label="<?php esc_attr_e( 'Search', 'minimog' ); ?>"
	<?php endif; ?>
>
	<div class="icon" aria-hidden="true" focusable="false" role="presentation">
		<?php echo Minimog_SVG_Manager::instance()->get( 'search' ); ?>
	</div>
	<?php if ( ! empty( $args['show_title'] ) ) : ?>
		<div class="mobile-tab-link--title"><?php esc_html_e( 'Search', 'minimog' ); ?></div>
	<?php endif ?>
</a>
