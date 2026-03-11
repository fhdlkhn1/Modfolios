<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_WP_Widget_Product_Price_Filter' ) ) {
	class Minimog_WP_Widget_Product_Price_Filter extends Minimog_WC_Widget_Base {

		public function __construct() {
			$this->widget_id          = 'minimog-wp-widget-product-price-filter';
			$this->widget_cssclass    = 'm-widget-collapsible minimog-wp-widget-product-price-filter minimog-wp-widget-filter';
			$this->widget_name        = sprintf( '%1$s %2$s', '[Minimog]', __( 'Product Price Filter', 'minimog' ) );
			$this->widget_description = __( 'Display a price range list to filter products in your store.', 'minimog' );
			$this->settings           = array(
				'title'            => array(
					'type'  => 'text',
					'std'   => __( 'Price Filter', 'minimog' ),
					'label' => __( 'Title', 'minimog' ),
				),
				'display_type'     => array(
					'type'    => 'select',
					'std'     => 'list',
					'label'   => __( 'Display type', 'minimog' ),
					'options' => array(
						'list'   => __( 'List', 'minimog' ),
						'inline' => __( 'Inline', 'minimog' ),
					),
				),
				'list_style'       => array(
					'type'    => 'select',
					'std'     => 'normal',
					'label'   => __( 'List Style', 'minimog' ),
					'options' => array(
						'normal' => __( 'Normal List', 'minimog' ),
						'radio'  => __( 'Radio List', 'minimog' ),
					),
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
			if ( ! is_shop() && ! is_product_taxonomy() ) {
				return;
			}

			// If there are not posts and we're not filtering, hide the widget.
			if ( ! \Minimog\Woo\Product_Query::is_main_query_has_post() && ! isset( $_GET['min_price'] ) && ! isset( $_GET['max_price'] ) ) { // WPCS: input var ok, CSRF ok.
				return;
			}

			$step = max( intval( apply_filters( 'minimog/widget/product_price_filter/total_steps', 5 ) ), 1 );

			// Find min and max price in current result set.
			$prices    = $this->get_filtered_price();
			$min_price = $prices->min_price;
			$max_price = $prices->max_price;

			// Check to see if we should add taxes to the prices if store are excl tax but display incl.
			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

			if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
				$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
				$tax_rates = WC_Tax::get_rates( $tax_class );

				if ( $tax_rates ) {
					$min_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
					$max_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
				}
			}

			/**
			 * Use the same filter WC to compatible with 3rd plugins like multi currency plugin.
			 */
			$min_price = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $min_price / $step ) * $step );
			$max_price = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step );

			// If both min and max are equal, we don't need a filter.
			if ( $min_price === $max_price ) {
				return;
			}

			$current_min_price = isset( $_GET['min_price'] ) ? floor( floatval( wp_unslash( $_GET['min_price'] ) ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
			$current_max_price = isset( $_GET['max_price'] ) ? ceil( floatval( wp_unslash( $_GET['max_price'] ) ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

			$this->widget_start( $args, $instance );

			$links = $this->generate_price_links( $min_price, $max_price, $current_min_price, $current_max_price, $step );

			$display_type = $this->get_value( $instance, 'display_type' );
			$list_style   = $this->get_value( $instance, 'list_style' );

			$class = ' show-display-' . $display_type;
			$class .= ' list-style-' . $list_style;

			if ( ! empty( $links ) ) {
				?>
				<ul class="minimog-product-price-filter single-choice <?php echo esc_attr( $class ); ?>">
					<?php foreach ( $links as $link ) : ?>
						<li class="<?php echo esc_attr( $link['item_class'] ); ?>">
							<a href="<?php echo esc_url( $link['href'] ); ?>"
							   class="filter-link" rel="nofollow"><?php echo '' . $link['title']; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php
			}
			$this->widget_end( $args, $instance );
		}

		private function generate_price_links( $min_price, $max_price, $current_min_price, $current_max_price, $step ) {
			$links         = array();
			$base_link     = $this->get_current_page_url();
			$link_no_price = remove_query_arg( [ 'min_price', 'max_price' ], $base_link );

			$need_more = false;

			$step_value = $max_price / $step;

			if ( $step_value < 10 ) {
				$step_value = 10;
			}

			$step_value = round( $step_value, - 1 );

			// Link to all prices.
			$all_link = array(
				'href'       => $link_no_price,
				'title'      => __( 'All', 'minimog' ),
				'item_class' => '',
			);

			if ( ( empty( $current_min_price ) && empty( $current_max_price ) ) || ( $current_min_price === $min_price && $current_max_price === $max_price ) ) {
				$all_link['item_class'] = 'chosen';
			}

			$links[] = $all_link;

			for ( $i = 0; $i < $step; $i ++ ) {

				$step_class = '';

				$step_min = $step_value * $i;

				$step_max = $step_value * ( $i + 1 );

				if ( $step_max > $max_price ) {
					$need_more = true;
					$i ++;
					break;
				}

				$href = add_query_arg( 'min_price', $step_min, $base_link );
				$href = add_query_arg( 'max_price', $step_max, $href );
				$href = add_query_arg( 'filtering', '1', $href );

				$step_title = wc_format_price_range( $step_min, $step_max );

				if ( ! empty( $current_min_price ) && ! empty( $current_max_price ) && ( $current_min_price >= $step_min && $current_max_price <= $step_max ) || ( $i == 0 && ! empty( $current_max_price ) && $current_max_price <= $step_max ) ) {
					$step_class = 'chosen';
				}

				$count = $this->get_filtered_product_count( $step_min, $step_max );

				// Skip link that has no product price in.
				if ( 0 >= $count ) {
					continue;
				}

				$links[] = array(
					'href'       => $href,
					'title'      => $step_title,
					'item_class' => $step_class,
					'count'      => $count,
				);
			}

			if ( $max_price > $step_max ) {
				$need_more = true;
				$step_min  = $step_value * $i;
			}

			if ( $need_more ) {

				$step_class = $href = '';

				$href = add_query_arg( 'min_price', $step_min, $base_link );
				$href = add_query_arg( 'max_price', $max_price, $href );
				$href = add_query_arg( 'filtering', '1', $href );

				$step_title = wc_price( $step_min ) . ' +';

				if ( $current_min_price >= $step_min && $current_max_price <= $max_price ) {
					$step_class = 'chosen';
				}

				$count = $this->get_filtered_product_count( $step_min, $max_price );

				// Skip link that has no product price in.
				if ( $count > 0 ) {
					$links[] = array(
						'href'       => $href,
						'title'      => $step_title,
						'item_class' => $step_class,
						'count'      => $count,
					);
				}
			}

			return $links;
		}

		/**
		 * @return array
		 */
		protected function get_filtered_price() {
			global $wpdb;

			$args       = WC()->query->get_main_query()->query_vars;
			$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
			$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

			if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
				$tax_query[] = WC()->query->get_main_tax_query();
			}

			foreach ( $meta_query + $tax_query as $key => $query ) {
				if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
					unset( $meta_query[ $key ] );
				}
			}

			$meta_query = new WP_Meta_Query( $meta_query );
			$tax_query  = new WP_Tax_Query( $tax_query );
			$search     = WC_Query::get_main_search_query_sql();

			$meta_query_sql   = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
			$tax_query_sql    = $tax_query->get_sql( $wpdb->posts, 'ID' );
			$search_query_sql = $search ? ' AND ' . $search : '';

			$sql = "
			SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
			FROM {$wpdb->wc_product_meta_lookup}
			WHERE product_id IN (
				SELECT ID FROM {$wpdb->posts}
				" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
				WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
				AND {$wpdb->posts}.post_status = 'publish'
				" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . '
			)';

			return $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
		}

		/**
		 * Count products after other filters have occurred by adjusting the main query.
		 *
		 * @param float $current_min_price
		 * @param float $current_max_price
		 *
		 * @return int
		 */
		protected function get_filtered_product_count( $current_min_price, $current_max_price ) {
			global $wpdb;

			$tax_query  = \Minimog\Woo\Product_Query::get_main_tax_query();
			$meta_query = \Minimog\Woo\Product_Query::get_main_meta_query();

			$meta_query      = new WP_Meta_Query( $meta_query );
			$tax_query       = new WP_Tax_Query( $tax_query );
			$meta_query_sql  = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
			$tax_query_sql   = $tax_query->get_sql( $wpdb->posts, 'ID' );
			$price_query_sql = \Minimog\Woo\Product_Query::instance()->get_main_price_query_sql( $current_min_price, $current_max_price );

			$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
			$sql .= $tax_query_sql['join'] . $meta_query_sql['join'] . $price_query_sql['join'];
			$sql .= " WHERE {$wpdb->posts}.post_type = 'product' AND {$wpdb->posts}.post_status = 'publish' ";
			$sql .= $tax_query_sql['where'] . $meta_query_sql['where'] . $price_query_sql['where'];

			$search = \Minimog\Woo\Product_Query::get_main_search_query_sql();
			if ( $search ) {
				$sql .= ' AND ' . $search;
			}

			return absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.
		}
	}
}
