<?php
/**
 * Cart countdown
 */

defined( 'ABSPATH' ) || exit;

$length          = intval( Minimog::setting( 'shopping_cart_countdown_length' ) );
$message         = pll__( Minimog::setting( 'shopping_cart_countdown_message' ) );
$expired_message = pll__( Minimog::setting( 'shopping_cart_countdown_expired_message' ) );
$is_loop            = ! empty( Minimog::setting( 'shopping_cart_countdown_loop_enable' ) );

$formatter          = [
	'day'    => _x( 'd', 'time abbreviations', 'minimog' ),
	'hour'   => _x( 'h', 'time abbreviations', 'minimog' ),
	'minute' => _x( 'm', 'time abbreviations', 'minimog' ),
	'second' => _x( 's', 'time abbreviations', 'minimog' ),
];
$timer              = sprintf( '<div class="timer">00%1$s 00%2$s</div>', $formatter['minute'], $formatter['second'] );
$message            = str_replace( '{timer}', $timer, $message );
$html_message       = str_replace( '{fire}', '&#128293;', $message );
$js_message         = str_replace( '{fire}', '\ud83d\udd25', $message );
$countdown_settings = [
	'loop'            => $is_loop,
	'length'          => $length,
	'message'         => $js_message,
	'expired_message' => $expired_message,
	'formatter'       => $formatter,
];
?>
<div class="cart-countdown-timer"
     data-countdown="<?php echo esc_attr( wp_json_encode( $countdown_settings ) ); ?>">
	<div class="inner">
		<div class="cart-countdown-message"><?php echo '' . $html_message; ?></div>
	</div>
</div>
