<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>
	<div class="woocommerce-tabs woocommerce-main-tabs">
		<div class="<?php echo Minimog\Woo\Single_Product::instance()->page_content_container_class(); ?>">
			<div class="minimog-tabs minimog-tabs--horizontal minimog-tabs--title-graphic-align-center">
				<div class="minimog-tabs__header-wrap">
					<div class="minimog-tabs__header-inner">
						<div class="minimog-tabs__header" role="tablist">
							<?php $loop_count = 0; ?>
							<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
								<?php $loop_count++; ?>
								<div class="<?php echo "tab-title {$key}_tab"; ?><?php if ( 1 === $loop_count ) : ?> active<?php endif; ?>"
								     data-tab="<?php echo $loop_count; // WPCS: XSS ok. ?>"
								     id="tab-title-<?php echo esc_attr( $key ); ?>"
								     role="tab"
								     aria-selected="<?php echo 1 === $loop_count ? 'true' : 'false'; ?>"
								     tabindex="<?php echo 1 === $loop_count ? '0' : '-1'; ?>"
								     aria-controls="tab-content-<?php echo esc_attr( $key ); ?>"
								>
									<span class="tab-title__text">
										<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="minimog-tabs__content">
					<?php $loop_count = 0; ?>
					<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
						<?php $loop_count++; ?>
						<div class="<?php echo "tab-content tab-content-{$key}"; ?><?php if ( 1 === $loop_count ) : ?> active<?php endif; ?>"
						     data-tab="<?php echo $loop_count; // WPCS: XSS ok. ?>"
						     id="tab-content-<?php echo esc_attr( $key ); ?>"
						     role="tabpanel"
						     tabindex="0"
						     aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>"
							<?php echo 1 === $loop_count ? '' : ' hidden'; ?>
							 aria-expanded="<?php echo 1 === $loop_count ? 'true' : 'false'; ?>"
						>
							<div class="tab-content-wrapper">
								<?php
								if ( isset( $product_tab['callback'] ) ) {
									call_user_func( $product_tab['callback'], $key, $product_tab );
								}
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php do_action( 'woocommerce_product_after_tabs' ); ?>
		</div>
	</div>
<?php endif; ?>
