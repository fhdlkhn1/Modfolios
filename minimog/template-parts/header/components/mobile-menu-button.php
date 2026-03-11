<?php
/**
 * Mobile menu toggle button on header
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

$menu_settings = [
	'direction' => $args['direction'],
	'animation' => $args['animation'],
];
?>
<div id="page-open-mobile-menu" class="header-icon page-open-mobile-menu <?php echo esc_attr( "style-" . $args['style'] ); ?>"
     data-menu-settings="<?php echo esc_attr( wp_json_encode( $menu_settings ) ); ?>">
	<div class="icon">
		<?php echo Minimog_SVG_Manager::instance()->get( 'bars' ); ?>
	</div>
</div>
