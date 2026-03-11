<?php
/**
 * Cart goal to get free shipping
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $coupons ) ) {
	return;
}
?>
<div class="minimog-coupon-list">
	<p class="form-description"><?php esc_html_e( 'Select an available coupon below', 'minimog' ); ?></p>
	<?php foreach ( $coupons as $coupon_id => $coupon_settings ) : ?>
		<?php
		$coupon        = new \WC_Coupon( $coupon_id );
		$discount_type = $coupon->get_discount_type();


		switch ( $discount_type ) {
			case 'percent' :
				$coupon_amount_html = sprintf( esc_html__( '%s Discount', 'minimog' ), $coupon->get_amount() . '%' );
				break;
			case 'fixed_product' :
				$coupon_amount_html = sprintf( esc_html__( '%s Product Discount', 'minimog' ), wc_price( $coupon->get_amount() ) );
				break;
			default:
				$coupon_amount_html = sprintf( esc_html__( '%s Discount', 'minimog' ), wc_price( $coupon->get_amount() ) );
				break;
		}

		$date_format = get_option( 'date_format' );
		$expire      = empty( $coupon->get_date_expires() ) ? '' : $coupon->get_date_expires()->date( $date_format );
		$expire      = $expire ? __( 'Expires on: ', 'minimog' ) . $expire : __( 'Never expire', 'minimog' );
		?>
		<a href="#" data-coupon="<?php echo esc_attr( $coupon->get_code() ) ?>"
		   class="apply-coupon-link<?php if ( ! empty( $coupon_settings['active'] ) ): ?> coupon-selected<?php endif; ?><?php if ( empty( $coupon_settings['enable'] ) ): ?> coupon-disabled<?php endif; ?>">
			<div class="coupon-info">
				<div class="coupon-value"><?php echo '' . $coupon_amount_html; ?></div>
				<div class="coupon-code-wrap">
					<div class="coupon-code"><?php echo esc_html( $coupon->get_code() ); ?></div>
					<div class="coupon-expired"><?php echo esc_html( $expire ); ?></div>
				</div>
				<div class="coupon-description"><?php echo esc_html( $coupon->get_description() ); ?></div>
			</div>
			<?php if ( ! empty( $coupon_settings['messages'] ) ) : ?>
				<div class="coupon-messages"><?php echo '' . $coupon_settings['messages']; ?></div>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
