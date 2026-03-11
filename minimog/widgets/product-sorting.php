<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_WP_Widget_Product_Sorting' ) ) {
	class Minimog_WP_Widget_Product_Sorting extends Minimog_WC_Widget_Base {

		public function __construct() {
			$this->widget_id          = 'minimog-wp-widget-product-sorting';
			$this->widget_cssclass    = 'm-widget-collapsible minimog-wp-widget-product-sorting';
			$this->widget_name        = sprintf( '%1$s %2$s', '[Minimog]', __( 'Product Sorting', 'minimog' ) );
			$this->widget_description = __( 'Display a sorting list to sort products in your store.', 'minimog' );
			$this->settings           = array(
				'title'            => array(
					'type'  => 'text',
					'std'   => __( 'Sort by', 'minimog' ),
					'label' => __( 'Title', 'minimog' ),
				),
				'enable_collapsed' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => __( 'Collapsed ?', 'minimog' ),
				),
			);

			parent::__construct();
		}

		public function widget( $args, $instance ) {
			if ( ! woocommerce_products_will_display() ) {
				return;
			}

			$orderby = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

			$show_default_orderby = 'menu_order' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

			$catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
				'menu_order' => __( 'Default', 'minimog' ),
				'popularity' => __( 'Popularity', 'minimog' ),
				'rating'     => __( 'Average rating', 'minimog' ),
				'date'       => __( 'Newness', 'minimog' ),
				'price'      => __( 'Price: low to high', 'minimog' ),
				'price-desc' => __( 'Price: high to low', 'minimog' ),
			) );

			if ( ! $show_default_orderby ) {
				unset( $catalog_orderby_options['menu_order'] );
			}

			if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
				unset( $catalog_orderby_options['rating'] );
			}

			$this->widget_start( $args, $instance );
			?>
			<ul class="order-by">
				<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
					<?php
					$link = $this->get_current_page_url();
					$link = add_query_arg( 'orderby', $id, $link );
					?>
					<li
						<?php if ( selected( $orderby, $id, false ) ) { ?>
							class="selected-order"
						<?php } ?>
					>
						<a href="<?php echo esc_url( $link ); ?>"
						   data-order="<?php echo $id; // WPCS: XSS ok. ?>" rel="nofollow"><?php echo esc_html( $name ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
			$this->widget_end( $args, $instance );
		}
	}
}
