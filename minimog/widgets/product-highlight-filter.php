<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_WP_Widget_Product_Highlight_Filter' ) ) {
	class Minimog_WP_Widget_Product_Highlight_Filter extends Minimog_WC_Widget_Base {

		public function __construct() {
			$this->widget_id          = 'minimog-wp-widget-product-highlight-filter';
			$this->widget_cssclass    = 'm-widget-collapsible minimog-wp-widget-product-highlight-filter minimog-wp-widget-filter';
			$this->widget_name        = sprintf( '%1$s %2$s', '[Minimog]', __( 'Product Highlight Filter', 'minimog' ) );
			$this->widget_description = __( 'Shows product status in a widget which lets you narrow down the list of products when viewing products.', 'minimog' );
			$this->settings           = array(
				'title'            => array(
					'type'  => 'text',
					'std'   => __( 'Highlight', 'minimog' ),
					'label' => __( 'Title', 'minimog' ),
				),
				'display_type'     => array(
					'type'    => 'select',
					'std'     => 'list',
					'label'   => __( 'List Layout', 'minimog' ),
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

			if ( ! \Minimog\Woo\Product_Query::is_main_query_has_post() ) {
				return;
			}

			$filter_name = 'highlight_filter';
			$selected    = isset( $_GET[ $filter_name ] ) ? wc_clean( $_GET[ $filter_name ] ) : '';

			$options = Minimog_Woo::instance()->get_product_highlight_filter_options();

			$base_link = remove_query_arg( 'paged', $this->get_current_page_url() );

			$this->widget_start( $args, $instance );

			$display_type = $this->get_value( $instance, 'display_type' );
			$list_style   = $this->get_value( $instance, 'list_style' );

			$class = 'show-display-' . $display_type;
			$class .= ' list-style-' . $list_style;
			?>
			<ul class="minimog-product-highlight-filter single-choice <?php echo esc_attr( $class ); ?>">
				<?php foreach ( $options as $option_value => $option_label ) : ?>
					<?php
					if ( '' === $option_value ) {
						$link = remove_query_arg( $filter_name, $base_link );
					} else {
						$link = add_query_arg( $filter_name, $option_value, $base_link );
					}
					?>
					<li class="filter-item<?php if ( $selected === $option_value ) : ?> chosen<?php endif; ?>">
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
