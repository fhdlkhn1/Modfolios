<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_WP_Widget_Product_Stock_Filter' ) ) {
	class Minimog_WP_Widget_Product_Stock_Filter extends Minimog_WC_Widget_Base {

		public function __construct() {
			$this->widget_id          = 'minimog-wp-widget-product-stock-filter';
			$this->widget_cssclass    = 'm-widget-collapsible minimog-wp-widget-product-stock-filter minimog-wp-widget-filter';
			$this->widget_name        = sprintf( '%1$s %2$s', '[Minimog]', __( 'Product Stock Filter', 'minimog' ) );
			$this->widget_description = __( 'Shows product stock status in a widget which lets you narrow down the list of products when viewing products.', 'minimog' );
			$this->settings           = array(
				'title' => array(
					'type'  => 'text',
					'std'   => __( 'Filter by stock', 'minimog' ),
					'label' => __( 'Title', 'minimog' ),
				),
			);

			parent::__construct();
		}

		public function widget( $args, $instance ) {
			if ( ! is_shop() && ! is_product_taxonomy() ) {
				return;
			}

			if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
				if ( current_user_can( 'administrator' ) ) { // Show message for admin only.
					$this->widget_start( $args, $instance );
					echo '<p>You hide out of stock items from the catalog. So this filter is unnecessary. <br>Click <a class="link-transition-01" href="' . esc_url( admin_url( 'page=wc-settings&tab=products&section=inventory' ) ) . '">here</a> to change the setting.</p>';
					$this->widget_end( $args, $instance );
				} else {
					return;
				}
			}

			if ( ! \Minimog\Woo\Product_Query::is_main_query_has_post() ) {
				return;
			}

			$filter_name = 'stock_status';
			$selected    = isset( $_GET[ $filter_name ] ) ? wc_clean( $_GET[ $filter_name ] ) : '';

			$base_link = remove_query_arg( 'paged', $this->get_current_page_url() );

			$this->widget_start( $args, $instance );

			$options = [
				'in_stock' => __( 'In stock', 'minimog' ),
			]
			?>
			<ul class="minimog-product-highlight-filter show-display-list list-style-checkbox">
				<?php foreach ( $options as $option_value => $option_label ) : ?>
					<?php
					$is_option_set = $selected === $option_value;

					$link = $is_option_set ? remove_query_arg( $filter_name, $base_link ) : add_query_arg( $filter_name, $option_value, $base_link );
					?>
					<li class="filter-item <?php if ( $is_option_set ) : ?> chosen<?php endif; ?>">
						<a href="<?php echo esc_url( $link ); ?>"
						   class="filter-link" rel="nofollow"><?php echo esc_html( $option_label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php

			$this->widget_end( $args, $instance );
		}
	}
}
