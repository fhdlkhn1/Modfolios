<?php
/**
 * The Template for displaying all store products.
 *
 * @package WCfM Markeplace Views Store/products
 *
 * For edit coping this to yourtheme/wcfm/store
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $avia_config;

$counter = 0;
$products_per_page = 10;

wc_set_loop_prop( 'is_filtered', true );

// Enfold Theme Compatibility
if( $avia_config && is_array( $avia_config ) ) {
	$avia_config['overview'] = true;
}

// Check if we're on the main products tab (not exclusive tab)
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'products';

// Current page
$paged = max( 1, get_query_var( 'paged', 1 ) );

// Custom query for main products tab to exclude exclusive products
if ( $current_tab === 'products' ) {
    // Get exclusive product IDs to exclude
    $exclusive_product_ids = get_posts( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'author'         => $store_user->get_id(),
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_is_exclusive_product',
                'value'   => 'yes',
                'compare' => '=',
            ),
        ),
    ));

    // Create custom query excluding exclusive products
    $products_query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'author'         => $store_user->get_id(),
        'post__not_in'   => $exclusive_product_ids,
        'posts_per_page' => $products_per_page,
        'paged'          => $paged,
        'tax_query'      => array(                   // Excludes ALL subscriptions
            array(
                'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => array( 'subscription', 'variable-subscription', 'subscription_variation' ),
                'operator' => 'NOT IN',
            ),
        ),
    ) );

    $total_products = $products_query->found_posts;
    $max_pages      = $products_query->max_num_pages;
} else {
    // For other tabs, use the default query
    global $wp_query;
    $products_query = $wp_query;
    $total_products = $wp_query->found_posts;
    $max_pages      = $wp_query->max_num_pages;
}
?>

<?php do_action( 'wcfmmp_store_before_products', $store_user->get_id() ); ?>

<div class="" id="products">
	<div class="product_area">
	  <div id="products-wrapper" class="products-wrapper">

			<?php do_action( 'wcfmmp_before_store_product', $store_user->get_id(), $store_info ); ?>

			<?php if ( $current_tab === 'products' ? $products_query->have_posts() : woocommerce_product_loop() ) { ?>

				<?php do_action( 'wcfmmp_woocommerce_before_shop_loop_before', $store_user->get_id(), $store_info ); ?>
				<?php do_action( 'woocommerce_before_shop_loop' ); ?>
				<?php do_action( 'wcfmmp_woocommerce_before_shop_loop_after', $store_user->get_id(), $store_info ); ?>

				<?php do_action( 'flatsome_category_title_alt'); // Flatsome Catalog support ?>
				<?php do_action( 'wcfmmp_before_store_product_loop', $store_user->get_id(), $store_info ); ?>

				<?php woocommerce_product_loop_start(); ?>

					<?php if ( $current_tab === 'products' ? $products_query->have_posts() : wc_get_loop_prop( 'total' ) ) { ?>

						<?php do_action( 'wcfmmp_after_store_product_loop_start', $store_user->get_id(), $store_info ); ?>

						<?php
                        if ( $current_tab === 'products' ) {
                            while ( $products_query->have_posts() ) {
                                $products_query->the_post();
                        ?>
                                <?php do_action( 'wcfmmp_store_product_loop_in_before', $store_user->get_id(), $store_info, $counter ); ?>
                                <?php wc_get_template_part( 'content', 'product' ); ?>
                                <?php do_action( 'wcfmmp_store_product_loop_in_after', $store_user->get_id(), $store_info, $counter ); ?>
                                <?php $counter++; ?>
                        <?php
                            }
                        } else {
                            while ( have_posts() ) { the_post();
                        ?>
                                <?php do_action( 'wcfmmp_store_product_loop_in_before', $store_user->get_id(), $store_info, $counter ); ?>
                                <?php wc_get_template_part( 'content', 'product' ); ?>
                                <?php do_action( 'wcfmmp_store_product_loop_in_after', $store_user->get_id(), $store_info, $counter ); ?>
                                <?php $counter++; ?>
                        <?php
                            }
                        }
                        ?>

						<?php do_action( 'wcfmmp_before_store_product_loop_end', $store_user->get_id(), $store_info ); ?>

					<?php } ?>

				<?php if( function_exists( 'listify_php_compat_notice') ) { ?>
					</div>
				<?php } else { ?>
					<?php woocommerce_product_loop_end(); ?>
				<?php } ?>

				<?php do_action( 'wcfmmp_after_store_product_loop', $store_user->get_id(), $store_info ); ?>

				<?php do_action( 'wcfmmp_woocommerce_after_shop_loop_before', $store_user->get_id(), $store_info ); ?>
				<?php // Skip default woocommerce_after_shop_loop — we handle pagination below ?>
				<?php do_action( 'wcfmmp_woocommerce_after_shop_loop_after', $store_user->get_id(), $store_info ); ?>

				<?php //wcfmmp_content_nav( 'nav-below' ); ?>

			<?php } else { ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php } ?>

			<?php do_action( 'wcfmmp_after_store_product', $store_user->get_id(), $store_info ); ?>

		</div><!-- .products-wrapper -->
	</div><!-- #products -->
</div><!-- .product_area -->

<?php if ( $current_tab === 'products' && $max_pages > 1 ) : ?>
<div class="wcfm-store-load-more-wrap" id="wcfm-store-load-more-wrap">
	<button type="button"
		class="wcfm-store-load-more-btn"
		id="wcfm-store-load-more"
		data-vendor="<?php echo esc_attr( $store_user->get_id() ); ?>"
		data-page="1"
		data-max="<?php echo esc_attr( $max_pages ); ?>"
		data-per-page="<?php echo esc_attr( $products_per_page ); ?>" style="background: var(--color-accent) !important;">
		<span class="button-text"><?php esc_html_e( 'Load more', 'minimog' ); ?></span>
		<span class="button-spinner" style="display:none;">
			Loading <svg width="18" height="18" viewBox="0 0 24 24" style="margin-left: 12px;" xmlns="http://www.w3.org/2000/svg"><style>.spinner{animation:rotate 1s linear infinite;transform-origin: center;transform-box: fill-box;}@keyframes rotate{100%{transform:rotate(360deg)}}</style><circle class="spinner" cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/></svg>
		</span>
	</button>
</div>

<style>
.wcfm-store-load-more-wrap {
	text-align: center;
	padding: 30px 0 10px;
}
.wcfm-store-load-more-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 12px 36px;
	background: #00C4AA;
	color: #fff;
	font-size: 15px;
	font-weight: 600;
	border: none;
	border-radius: 50px;
	cursor: pointer;
	transition: background 0.2s ease, transform 0.2s ease;
	box-shadow: 0 4px 12px rgba(0, 196, 170, 0.3);
}
.wcfm-store-load-more-btn:hover {
	background: #00b39b;
	transform: translateY(-2px);
	box-shadow: 0 6px 16px rgba(0, 196, 170, 0.4);
}
.wcfm-store-load-more-btn:disabled {
	opacity: 0.6;
	cursor: not-allowed;
	transform: none;
}
</style>

<script>
(function($) {
	var $btn = $('#wcfm-store-load-more');
	if ( ! $btn.length ) return;

	$btn.on('click', function() {
		var currentPage = parseInt( $btn.attr('data-page'), 10 );
		var maxPages    = parseInt( $btn.attr('data-max'), 10 );
		var nextPage    = currentPage + 1;

		if ( nextPage > maxPages ) return;

		$btn.prop('disabled', true);
		$btn.find('.button-text').hide();
		$btn.find('.button-spinner').show();

		$.ajax({
			url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
			type: 'POST',
			data: {
				action: 'wcfm_store_load_more_products',
				vendor_id: $btn.attr('data-vendor'),
				page: nextPage,
				per_page: $btn.attr('data-per-page'),
				nonce: '<?php echo wp_create_nonce( 'wcfm_store_load_more' ); ?>'
			},
			success: function( res ) {
				if ( res.success && res.data.html ) {
					// Minimog uses div.minimog-grid inside div.minimog-grid-wrapper
					var $grid = $('#products-wrapper').find('.minimog-grid');
					if ( ! $grid.length ) {
						// Fallback for other themes using ul.products
						$grid = $('#products-wrapper').find('ul.products, .products');
					}

					$grid.append( res.data.html );

					// Trigger WooCommerce/theme events for lazy-loading images etc.
					$(document.body).trigger('post-load');

					$btn.attr('data-page', nextPage);

					if ( nextPage >= maxPages ) {
						$('#wcfm-store-load-more-wrap').remove();
					}
				} else {
					$('#wcfm-store-load-more-wrap').remove();
				}
			},
			error: function() {
				$btn.find('.button-text').text('<?php echo esc_js( __( 'Error — try again', 'minimog' ) ); ?>');
			},
			complete: function() {
				$btn.prop('disabled', false);
				$btn.find('.button-spinner').hide();
				$btn.find('.button-text').show();
			}
		});
	});
})(jQuery);
</script>
<?php endif; ?>

<?php
// Reset post data if we used custom query
if ( $current_tab === 'products' && isset( $products_query ) ) {
    wp_reset_postdata();
}
?>

<?php do_action( 'wcfmmp_store_after_products', $store_user->get_id() ); ?>
