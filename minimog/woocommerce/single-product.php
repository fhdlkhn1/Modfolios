<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see           https://woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package       WooCommerce\Templates
 * @version       1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$product_summary_container = Minimog_Woo::instance()->get_product_setting( 'single_product_summary_layout' );
?>
	<div id="page-content" class="page-content">
		<div class="entry-product-page-content">
			<div class="<?php echo Minimog_Site_Layout::instance()->get_container_class( $product_summary_container ); // WPCS: XSS ok. ?>">
				<div class="row">

					<?php Minimog_Sidebar::instance()->render( 'left' ); ?>

					<div class="page-main-content">
						<?php while ( have_posts() ) : the_post(); ?>

							<?php wc_get_template_part( 'content', 'single-product' ); ?>

						<?php endwhile; ?>
					</div>

					<?php Minimog_Sidebar::instance()->render( 'right' ); ?>

				</div>
			</div>
		</div>
		<?php do_action( 'woocommerce_after_single_product' ); ?>
	</div>
<?php
get_footer( 'shop' );

