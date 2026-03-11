<?php
/**
 * Loop Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
$product_url = get_permalink($product->get_id());

$aria_describedby = isset( $args['aria-describedby_text'] ) ? sprintf( 'aria-describedby="woocommerce_loop_add_to_cart_link_describedby_%s"', esc_attr( $product->get_id() ) ) : '';
?>
<div class="product-action hint--bounce hint--top woocommerce_loop_add_to_cart_wrap test"
     data-hint="<?php echo esc_attr( $product->add_to_cart_text() ); ?>">
	<?php
// 	echo apply_filters( 'woocommerce_loop_add_to_cart_link',
// 		sprintf( '<a href="%s" %s data-quantity="%s" class="%s" %s><span>%s</span></a>',
// 			esc_url( $product->add_to_cart_url() ),
// 			$aria_describedby,
// 			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
// 			esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
// 			isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
// 			esc_html( $product->add_to_cart_text() )
// 		),
// 		$product,
// 		$args
// 	);
	?>
	
	<a href="<?php echo esc_url($product_url); ?>" target="_blank"
	   class="button view-product-btn"
	   aria-label="<?php echo esc_attr(sprintf(__('View details for %s', 'dukandar'), $product->get_name())); ?>">
<!-- 		<span><?php //esc_html_e('View Details', 'dukandar'); ?></span> -->
		<span><i class="fas fa-eye"></i></span>
	</a>

	<?php if ( isset( $args['aria-describedby_text'] ) ) : ?>
		<span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr( $product->get_id() ); ?>" class="screen-reader-text">
		<?php if ( ! empty( $args['aria-describedby_text'] ) ) : ?>
			<?php echo esc_html( $args['aria-describedby_text'] ); ?>
		<?php endif ?>
	</span>
	<?php endif; ?>
</div>