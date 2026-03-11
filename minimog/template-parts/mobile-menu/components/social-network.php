<?php
/**
 * Social network buttons on mobile menu
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

$defaults = array(
	'style' => 'icons',
);

$args       = wp_parse_args( $args, $defaults );
?>
	<div class="mobile-menu-social-networks<?php if ( ! empty( $args['style'] ) ) : echo esc_attr( " style-{$args['style']}" ); endif?>">
		<div class="inner">
			<?php
			$args = [
				'tooltip_enable' => false
			];

			Minimog_Templates::social_icons( $args );
			?>
		</div>
	</div>
<?php
