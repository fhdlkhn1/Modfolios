<?php
/**
 * Mobile tabs navigation
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.3.0
 */

defined( 'ABSPATH' ) || exit;

$show_title = Minimog::setting( 'mobile_tabs_title_enable' );
$wrap_class = 'page-mobile-tabs';

if ( $show_title ) {
	$wrap_class .= ' page-mobile-tabs__has-title';
}
?>
<div id="page-mobile-tabs" class="<?php echo $wrap_class; ?>">
	<div class="tabs">
		<?php do_action( 'minimog/mobile-tabs/before' ); ?>

		<?php foreach ( $args['tab_items'] as $tab_item => $is_active ): ?>
			<?php
			if ( empty( $is_active ) ) {
				continue;
			}

			minimog_load_template( 'mobile-tabs/components/' . $tab_item . '-button', null, [
				'show_title' => $show_title,
			] );
			?>
		<?php endforeach; ?>

		<?php do_action( 'minimog/mobile-tabs/after' ); ?>
	</div>
</div>
