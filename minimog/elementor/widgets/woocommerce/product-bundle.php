<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Product_Bundle extends Base {

	/**
	 * @var \WC_Product $product
	 */
	private $product = null;

	const PRODUCT_TYPE = 'woosb';

	public function get_name() {
		return 'tm-product-bundle';
	}

	public function get_title() {
		return __( 'Product Bundle', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-products';
	}

	public function get_keywords() {
		return [ 'bundle', 'product' ];
	}

	public function get_script_depends() {
		return [ 'minimog-widget-product-bundle' ];
	}

	public function register_controls() {
		$this->add_content_section();

		$this->add_style_section();

		$this->add_product_price_style_section();

		$this->add_order_number_style_section();

		$this->add_button_style_section();
	}

	private function add_content_section() {
		$this->start_controls_section( 'content_section', [
			'label' => __( 'Styling', 'minimog' ),
		] );

		$this->add_control( 'product_id', [
			'label'        => __( 'Choose Product', 'minimog' ),
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'label_block'  => true,
			'multiple'     => false,
			'autocomplete' => [
				'object' => Module_Query_Base::QUERY_OBJECT_POST,
				'query'  => [
					'post_type' => 'product',
					'tax_query' => [
						[
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => self::PRODUCT_TYPE,
						],
					],
				],
			],
			'render_type'  => 'template',
		] );

		$this->add_control( 'bundle_price', [
			'label'                => __( 'Bundle Price', 'minimog' ),
			'type'                 => Controls_Manager::SWITCHER,
			'label_on'             => __( 'Show', 'minimog' ),
			'label_off'            => __( 'Hide', 'minimog' ),
			'return_value'         => 'yes',
			'default'              => '',
			'selectors_dictionary' => [
				'yes' => 'display: block; !important',
				''    => 'display: none !important;',
			],
			'selectors'            => [
				'{{WRAPPER}} .woosb-total' => '{{VALUE}};',
			],
			'separator'            => 'before',
		] );

		$this->add_control( 'show_product_stock', [
			'label'        => __( 'Show Stock', 'minimog' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'minimog' ),
			'label_off'    => __( 'Hide', 'minimog' ),
			'return_value' => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'show_order_number', [
			'label'        => __( 'Show Order Number', 'minimog' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'minimog' ),
			'label_off'    => __( 'Hide', 'minimog' ),
			'return_value' => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'thumbnail_default_size', [
			'label'        => __( 'Use Default Thumbnail Size', 'minimog' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '1',
			'return_value' => '1',
			'separator'    => 'before',
		] );

		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'      => 'thumbnail',
			'default'   => 'full',
			'condition' => [
				'thumbnail_default_size!' => '1',
			],
		] );

		$this->end_controls_section();
	}

	private function add_style_section() {
		$this->start_controls_section( 'style_section', [
			'label' => __( 'Product Bundle', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'box_max_width', [
			'label'      => __( 'Max Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [
				'unit' => 'px',
			],
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'%'  => [
					'min' => 1,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 1600,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-product-bundle__product' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'box_horizontal_alignment', [
			'label'                => __( 'Horizontal Alignment', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => 'left',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .tm-product-bundle' => 'display: flex; justify-content: {{VALUE}}',
			],
		] );

		$this->add_control( 'item_heading', [
			'label'     => __( 'Item', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [],
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .woosb-product + .woosb-product' => 'margin-top: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'price_wrapper_width', [
			'label'      => __( 'Price Wrapper Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [],
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 80,
					'max' => 300,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .woosb-price' => 'width: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'product_image_width', [
			'label'      => __( 'Image Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [
				'unit' => 'px',
			],
			'size_units' => [ '%', 'px' ],
			'range'      => [
				'%'  => [
					'min' => 5,
					'max' => 50,
				],
				'px' => [
					'min' => 50,
					'max' => 500,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-thumb' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'product_title_typography',
			'label'    => __( 'Product Title Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-title',
		] );

		$this->start_controls_tabs( 'product_title_color_tabs', [
			'label' => __( 'Colors', 'minimog' ),
		] );

		$this->start_controls_tab( 'product_title_color_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'product_title_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-title' => 'color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'product_title_color_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'product_title_hover_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-title:hover' => 'color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function add_product_price_style_section() {
		$this->start_controls_section( 'product_price_style_section', [
			'label' => __( 'Product Price', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'product_price_typography',
			'label'    => __( 'Product Price Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .tm-product-bundle .woosb-price > div > .amount',
		] );

		$this->add_control( 'product_price_text_color', [
			'label'     => __( 'Original Price', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-price-ori > .amount' => 'color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'product_saved_price_typography',
			'label'    => __( 'Saved Price Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-price-saved',
		] );

		$this->add_control( 'product_price_saved_text_color', [
			'label'     => __( 'Saved Price', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .woosb-products .woosb-price-saved' => 'color: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	private function add_order_number_style_section() {
		$this->start_controls_section( 'order_number_style_section', [
			'label'     => __( 'Order Number', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'show_order_number' => 'yes',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'order_number',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .tm-product-bundle .item-order-count',
		] );

		$this->add_control( 'order_number_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .item-order-count' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'order_number_background_color', [
			'label'     => __( 'Background', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .item-order-count' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'order_number_border_color', [
			'label'     => __( 'Border', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-product-bundle .item-order-count' => 'border-color: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	private function add_button_style_section() {
		$this->start_controls_section( 'add_to_cart_button_style_section', [
			'label' => __( 'Add To Cart Button', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'button_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max'  => 200,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} form.cart' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'button_width', [
			'label'      => __( 'Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%', 'px' ],
			'range'      => [
				'%'  => [
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'max'  => 1000,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .single_add_to_cart_button' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'button_height', [
			'label'      => __( 'Height', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max'  => 200,
					'min'  => 20,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .single_add_to_cart_button' => 'height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'text',
			'selector' => '{{WRAPPER}} .single_add_to_cart_button',
		] );

		$this->start_controls_tabs( 'button_skin_tabs' );

		$this->start_controls_tab( 'button_skin_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'button_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .single_add_to_cart_button' => '--minimog-color-button-text: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .single_add_to_cart_button' => '--minimog-color-button-background: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .single_add_to_cart_button' => '--minimog-color-button-border: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_skin_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'button_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .single_add_to_cart_button:hover' => '--minimog-color-button-hover-text: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_hover_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .single_add_to_cart_button:hover' => '--minimog-color-button-hover-background: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_hover_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .single_add_to_cart_button:hover' => '--minimog-color-button-hover-border: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['product_id'] ) ) {
			return;
		}

		/**
		 * @var \WC_Product_Woosb $product
		 */
		$product = wc_get_product( $settings['product_id'] );

		if ( empty( $product ) || ! $product instanceof \WC_Product || self::PRODUCT_TYPE !== $product->get_type() ) {
			return;
		}

		$product_id = $product->get_id();

		$items = $product->get_items();

		if ( empty( $items ) ) {
			return;
		}

		$this->product = $product;

		$this->add_render_attribute( 'wrapper', [
			'class'                         => [
				'tm-product-bundle',
				'woocommerce',
			],
			'data-price_format'             => get_woocommerce_price_format(),
			'data-price_decimals'           => wc_get_price_decimals(),
			'data-price_thousand_separator' => wc_get_price_thousand_separator(),
			'data-price_decimal_separator'  => wc_get_price_decimal_separator(),
			'data-currency_symbol'          => get_woocommerce_currency_symbol(),
			'data-bundled_price'            => get_option( '_woosb_bundled_price', 'price' ),
			'data-bundled_price_from'       => get_option( '_woosb_bundled_price_from', 'sale_price' ),
			'data-save_text'                => __( 'Save', 'minimog' ),
			'data-discount_amount'          => $product->get_discount_amount(),
		] );

		$this->add_render_attribute( 'form', [
			'class'   => 'cart',
			'action'  => apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ),
			'method'  => 'post',
			'enctype' => 'multipart/form-data',
		] );

		/**
		 * @see WPCleverWoosb::add_to_cart_button()
		 */
		$ids = $product->get_ids_str();
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ) ?>>
			<div <?php wc_product_class( 'tm-product-bundle__product', $product ); ?>>

				<?php $this->show_bundled( $product ); ?>

				<form <?php $this->print_render_attribute_string( 'form' ) ?>>
					<input name="woosb_ids" class="woosb-ids woosb-ids-<?php echo esc_attr( $product->get_id() ) ?>"
					       type="hidden"
					       value="<?php echo esc_attr( $ids ) ?>"/>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>"
					        class="single_add_to_cart_button button alt">
						<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
						<span class="price"></span>
					</button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * @param \WC_Product_Woosb $product
	 */
	protected function show_bundled( $product ) {
		$settings      = $this->get_settings_for_display();
		$product_types = \Minimog\Woo\Product_Bundle::instance()->get_types();

		if ( isset( $settings['thumbnail_default_size'] ) && '1' !== $settings['thumbnail_default_size'] ) {
			$thumbnail_size = \Minimog_Image::elementor_parse_image_size( $settings );
		} else {
			$thumbnail_size = \Minimog_Woo::instance()->get_loop_product_image_size();
		}

		if ( $items = $product->get_items() ) {
			$order                 = 1;
			$product_id            = $product->get_id();
			$fixed_price           = $product->is_fixed_price();
			$has_variables         = $product->has_variables();
			$has_optional          = $product->has_optional();
			$discount_amount       = $product->get_discount_amount();
			$discount_percentage   = $product->get_discount_percentage();
			$exclude_unpurchasable = $product->exclude_unpurchasable();
			$total_limit           = get_post_meta( $product_id, 'woosb_total_limits', true ) === 'on';
			$total_min             = get_post_meta( $product_id, 'woosb_total_limits_min', true );
			$total_max             = get_post_meta( $product_id, 'woosb_total_limits_max', true );
			$whole_min             = get_post_meta( $product_id, 'woosb_limit_whole_min', true ) ? : 1;
			$whole_max             = get_post_meta( $product_id, 'woosb_limit_whole_max', true ) ? : '-1';

			do_action( 'woosb_before_wrap', $product );

			echo '<div class="woosb-wrap woosb-bundled" data-id="' . esc_attr( $product_id ) . '">';

			if ( $before_text = apply_filters( 'woosb_before_text', get_post_meta( $product_id, 'woosb_before_text', true ), $product_id ) ) {
				echo '<div class="woosb-before-text woosb-text">' . wp_kses_post( do_shortcode( $before_text ) ) . '</div>';
			}

			do_action( 'woosb_before_table', $product );

			$this->add_render_attribute( 'woosb_products', [
				'class'                      => 'woosb-products',
				'data-discount-amount'       => $discount_amount,
				'data-discount'              => $discount_percentage,
				'data-fixed-price'           => $fixed_price ? 'yes' : 'no',
				'data-price'                 => wc_get_price_to_display( $product ),
				'data-price-suffix'          => htmlentities( $product->get_price_suffix() ),
				'data-variables'             => $has_variables ? 'yes' : 'no',
				'data-optional'              => $has_optional ? 'yes' : 'no',
				'data-min'                   => $whole_min,
				'data-max'                   => $whole_max,
				'data-total-min'             => $total_limit && $total_min ? $total_min : 0,
				'data-total-max'             => $total_limit && $total_max ? $total_max : '-1',
				'data-exclude-unpurchasable' => $exclude_unpurchasable ? 'yes' : 'no',
			] );
			?>
			<div <?php $this->print_render_attribute_string( 'woosb_products' ) ?>>

				<?php
				// store global $product.
				$global_product    = $product;
				$global_product_id = $product_id;

				foreach ( $items as $key => $item ) {
					if ( ! empty( $item['id'] ) ) {
						/**
						 * @var \WC_Product_Variable $product
						 */
						$product  = wc_get_product( $item['id'] );
						$item_qty = isset( $item['qty'] ) ? (float) $item['qty'] : 1;
						$item_min = ! empty( $item['min'] ) ? (float) $item['min'] : 0;
						$item_max = ! empty( $item['max'] ) ? (float) $item['max'] : 10000;
						$optional = ! empty( $item['optional'] );

						if ( ! $product || in_array( $product->get_type(), $product_types, true ) ) {
							continue;
						}

						if ( ! apply_filters( 'woosb_item_exclude', true, $product, $global_product ) ) {
							continue;
						}

						if ( $optional ) {
							if ( ( $max_purchase = $product->get_max_purchase_quantity() ) && ( $max_purchase > 0 ) && ( $max_purchase < $item_max ) ) {
								// get_max_purchase_quantity can return -1
								$item_max = $max_purchase;
							}

							if ( $item_qty < $item_min ) {
								$item_qty = $item_min;
							}

							if ( ( $item_max > $item_min ) && ( $item_qty > $item_max ) ) {
								$item_qty = $item_max;
							}
						}

						$item_class = 'product woosb-item-product woosb-product woosb-product-type-' . $product->get_type();

						if ( $optional ) {
							$item_class .= ' woosb-product-optional';
						}

						if ( ! apply_filters( 'woosb_item_visible', true, $product, $global_product ) ) {
							$item_class .= ' woosb-product-hidden';
						}

						if ( ! $product->is_in_stock() || ! $product->has_enough_stock( $item_qty ) || ! $product->is_purchasable() ) {
							if ( ! apply_filters( 'woosb_allow_unpurchasable_qty', false ) ) {
								$item_qty = 0;
							}

							$item_class .= ' woosb-product-unpurchasable';
						}

						$quantity_input_html = '';
						if ( $optional ) {
							if ( get_post_meta( $product_id, 'woosb_optional_products', true ) === 'on' ) {
								if ( ( $product->get_backorders() === 'no' ) && ( $product->get_stock_status() !== 'onbackorder' ) && is_int( $product->get_stock_quantity() ) && ( $product->get_stock_quantity() < $item_max ) ) {
									$item_max = $product->get_stock_quantity();
								}

								if ( $product->is_sold_individually() ) {
									$item_max = 1;
								}

								ob_start();
								?>
								<div class="woosb-quantity">
									<?php
									if ( $product->is_in_stock() ) {
										woocommerce_quantity_input( array(
											'input_value' => $item_qty,
											'min_value'   => $item_min,
											'max_value'   => $item_max,
											'woosb_qty'   => array(
												'input_value' => $item_qty,
												'min_value'   => $item_min,
												'max_value'   => $item_max,
											),
											'classes'     => array( 'input-text', 'woosb-qty', 'qty', 'text' ),
											'input_name'  => 'woosb_qty_' . $order
											// compatible with WPC Product Quantity
										), $product );
									} else { ?>
										<input type="number" class="input-text qty text woosb-qty" value="0" disabled/>
									<?php } ?>
								</div>
								<?php
								$quantity_input_html = ob_get_clean();
							}
						}

						do_action( 'woosb_above_item', $product, $global_product, $order );
						$has_link = $product->is_visible() && get_option( '_woosb_bundled_link', 'yes' ) !== 'no';
						?>
						<div
							class="<?php echo esc_attr( apply_filters( 'woosb_item_class', $item_class, $product, $global_product, $order ) ); ?>"
							data-key="<?php echo esc_attr( $key ); ?>"
							data-name="<?php echo esc_attr( $product->get_name() ); ?>"
							data-id="<?php echo esc_attr( $product->is_type( 'variable' ) ? 0 : $item['id'] ); ?>"
							data-price="<?php echo esc_attr( \WPCleverWoosb_Helper()->get_price_to_display( $product ) ); ?>"
							data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
							data-qty="<?php echo esc_attr( $item_qty ); ?>"
							data-order="<?php echo esc_attr( $order ); ?>"
						>
							<?php do_action( 'woosb_before_item', $product, $global_product, $order ); ?>

							<div class="woosb-thumb-wrap">
								<?php if ( ! empty( $settings['show_order_number'] ) && 'yes' === $settings['show_order_number'] ): ?>
									<div class="item-order-count"><span><?php echo '' . $order; ?></span></div>
								<?php endif; ?>

								<?php if ( get_option( '_woosb_bundled_thumb', 'yes' ) !== 'no' ) : ?>
									<div class="woosb-thumb">
										<?php if ( $has_link ) {
											echo '<a ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link no-ajaxy" data-id="' . $item['id'] . '" data-context="woosb"' : '' ) . ' href="' . esc_url( $product->get_permalink() ) . '" ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
										} ?>
										<?php
										/**
										 * Disabled variation image changed.
										 * Because it not support properly image size.
										 * Move img out of div to make js disabled.
										 */ ?>
										<!--<div class="woosb-thumb-ori"></div>
										<div class="woosb-thumb-new"></div>-->
										<?php
										$product_image = \Minimog_Woo::instance()->get_product_image( $product, $thumbnail_size );
										echo apply_filters( 'woosb_item_thumbnail', $product_image, $product );
										?>

										<?php if ( $has_link ) {
											echo '</a>';
										} ?>
									</div><!-- /woosb-thumb -->
								<?php endif; ?>
							</div>
							<div class="woosb-product-info">
								<div class="woosb-product-main-info">
									<div class="woosb-title-wrap">
										<?php
										do_action( 'woosb_before_item_name', $product );

										echo '<h3 class="woosb-title post-title-2-rows">';

										if ( ( get_option( '_woosb_bundled_qty', 'yes' ) === 'yes' ) && ! $optional ) {
											echo apply_filters( 'woosb_item_qty', $item['qty'] . ' × ', $item['qty'], $product );
										}

										$item_name    = '';
										$product_name = apply_filters( 'woosb_item_product_name', $product->get_name(), $product );

										if ( $has_link ) {
											$item_name .= '<a ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link no-ajaxy" data-id="' . $item['id'] . '" data-context="woosb"' : '' ) . ' href="' . esc_url( $product->get_permalink() ) . '" ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
										}

										if ( $product->is_in_stock() && $product->has_enough_stock( $item_qty ) ) {
											$item_name .= $product_name;
										} else {
											$item_name .= '<s>' . $product_name . '</s>';
										}

										if ( $has_link ) {
											$item_name .= '</a>';
										}

										echo apply_filters( 'woosb_item_name', $item_name, $product, $global_product, $order );
										echo '</h3>';

										do_action( 'woosb_after_item_name', $product );

										if ( \WPCleverWoosb_Helper::get_setting( 'bundled_description', 'no' ) === 'yes' ) {
											echo '<div class="woosb-description">' . apply_filters( 'woosb_item_description', $product->get_short_description(), $product ) . '</div>';
										}
										?>

										<?php if ( ( $bundled_price = get_option( '_woosb_bundled_price', 'price' ) ) !== 'no' ) { ?>
											<div class="woosb-price">
												<div class="woosb-price-ori">
													<?php
													$ori_price = $product->get_price();
													$get_price = \WPCleverWoosb_Helper::get_price( $product );

													if ( ! $global_product->is_fixed_price() && $discount_percentage > 0 ) {
														$new_price     = true;
														$product_price = $get_price * ( 100 - (float) $discount_percentage ) / 100;
														$product_price = round( $product_price, wc_get_price_decimals() );
														$product_price = apply_filters( 'woosb_item_price_add_to_cart', $product_price, $item );
													} else {
														$new_price     = false;
														$product_price = $get_price;
													}

													switch ( $bundled_price ) {
														case 'price':
															if ( $new_price ) {
																$regular_price = wc_get_price_to_display( $product, array( 'price' => $get_price ) );
																$sale_price    = wc_get_price_to_display( $product, array( 'price' => $product_price ) );
																$_amount_saved = $regular_price - $sale_price;

																$item_price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $get_price ) ), wc_get_price_to_display( $product, array( 'price' => $product_price ) ) );
															} else {
																if ( $get_price > $ori_price ) {
																	$item_price = wc_price( \WPCleverWoosb_Helper::get_price_to_display( $product ) ) . $product->get_price_suffix();
																} else {
																	$item_price = $product->get_price_html();
																}
															}
															break;

														case 'subtotal':
															if ( $new_price ) {
																$subtotal_regular_price = wc_get_price_to_display( $product, array(
																	'price' => $get_price,
																	'qty'   => $item['qty'],
																) );
																$subtotal_sale_price    = wc_get_price_to_display( $product, array(
																	'price' => $product_price,
																	'qty'   => $item['qty'],
																) );
																$_amount_saved          = $subtotal_regular_price - $subtotal_sale_price;

																$item_price = wc_format_sale_price( wc_get_price_to_display( $product, array(
																		'price' => $get_price,
																		'qty'   => $item['qty'],
																	) ), wc_get_price_to_display( $product, array(
																		'price' => $product_price,
																		'qty'   => $item['qty'],
																	) ) ) . $product->get_price_suffix();
															} else {
																$item_price = wc_price( \WPCleverWoosb_Helper::get_price_to_display( $product, $item['qty'] ) ) . $product->get_price_suffix();
															}
															break;

														default:
															$_amount_saved = 0;
															$item_price    = $product->get_price_html();
															break;
													}

													echo apply_filters( 'woosb_item_price', $item_price, $product );

													if ( $new_price && intval( $_amount_saved ) > 0 ) {
														echo '<div class="woosb-price-saved">' . __( 'Save', 'minimog' ) . '&nbsp;' . wc_price( $_amount_saved ) . '</div>';
													}
													?>
												</div>
												<div class="woosb-price-new"></div>
												<?php do_action( 'woosb_after_item_price', $product ); ?>
											</div>
										<?php } ?>

										<?php
										echo '<div class="woosb-price-wrapper"></div>';

										do_action( 'woosb_after_item_name', $product );

										if ( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ) {
											echo '<div class="woosb-description">' . apply_filters( 'woosb_item_description', $product->get_short_description(), $product ) . '</div>';
										}
										?>
									</div>

									<div class="woosb-product-cart">
										<?php if ( $product->is_type( 'variable' ) ) { ?>
											<div class="minimog-variation-select-wrap">
												<?php
												if ( ( get_option( '_woosb_variations_selector', 'default' ) === 'wpc_radio' ) && class_exists( 'WPClever_Woovr' ) ) {
													\WPClever_Woovr::woovr_variations_form( $product );
												} else {
													echo '<div class="minimog-variation-select-wrap">';
													\Minimog_Woo::instance()->get_product_variation_dropdown_html( $product, [
														'show_label' => false,
														'show_price' => false,
													] );
													echo '</div>';

													$attributes           = $product->get_variation_attributes();
													$available_variations = $product->get_available_variations();
													$variations_json      = wp_json_encode( $available_variations );
													$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

													if ( ! empty( $attributes ) ) {
														$total_attrs = count( $attributes );
														$loop_count  = 0;

														echo '<div class="variations_form" data-product_id="' . absint( $product->get_id() ) . '" data-product_variations="' . $variations_attr . '">';
														echo '<div class="variations">';

														foreach ( $attributes as $attribute_name => $options ) {
															$loop_count ++;
															?>
															<div class="variation">
																<div class="label">
																	<?php echo wc_attribute_label( $attribute_name ); ?>
																</div>
																<div class="select">
																	<?php
																	$attr     = 'attribute_' . sanitize_title( $attribute_name );
																	$selected = isset( $_REQUEST[ $attr ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ $attr ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
																	wc_dropdown_variation_attribute_options( array(
																		'options'          => $options,
																		'attribute'        => $attribute_name,
																		'product'          => $product,
																		'selected'         => $selected,
																		'show_option_none' => wc_attribute_label( $attribute_name ),
																	) );
																	?>
																</div>
																<?php if ( $loop_count === $total_attrs ): ?>
																	<?php echo '<div class="reset">' . apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'minimog' ) . '</a>' ) . '</div>'; ?>
																<?php endif; ?>
															</div>
														<?php }
														echo '</div>';
														echo '</div>';

														if ( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ) {
															echo '<div class="woosb-variation-description"></div>';
														}
													}
												}

												do_action( 'woosb_after_item_variations', $product );
												?>
											</div>
											<?php
										}
										?>
										<?php echo '' . $quantity_input_html; ?>
										<?php if ( ! empty( $settings['show_product_stock'] ) && 'yes' === $settings['show_product_stock'] ) : ?>
											<?php echo '<div class="woosb-availability">' . wc_get_stock_html( $product ) . '</div>'; ?>
										<?php endif; ?>
									</div>
								</div>
								<?php if ( ( $bundled_price = get_option( '_woosb_bundled_price', 'price' ) ) !== 'no' ) { ?>
									<div class="woosb-price">
										<div class="woosb-price-ori">
											<?php
											$ori_price = $product->get_price();
											$get_price = \WPCleverWoosb_Helper::get_price( $product );

											if ( ! $fixed_price && $discount_percentage ) {
												$new_price     = true;
												$product_price = $get_price * ( 100 - $discount_percentage ) / 100;
											} else {
												$new_price     = false;
												$product_price = $get_price;
											}

											switch ( $bundled_price ) {
												case 'price':
													if ( $new_price ) {
														$regular_price = wc_get_price_to_display( $product, array( 'price' => $get_price ) );
														$sale_price    = wc_get_price_to_display( $product, array( 'price' => $product_price ) );
														$_amount_saved = $regular_price - $sale_price;

														$item_price = wc_price( $sale_price );

													} else {
														if ( $get_price > $ori_price ) {
															$item_price = wc_price( \WPCleverWoosb_Helper::get_price_to_display( $product ) ) . $product->get_price_suffix();
														} else {
															$item_price = $product->get_price_html();
														}
													}
													break;
												case 'subtotal':
													if ( $new_price ) {

														$subtotal_regular_price = wc_get_price_to_display( $product, array(
															'price' => $get_price,
															'qty'   => $item['qty'],
														) );

														$subtotal_sale_price = wc_get_price_to_display( $product, array(
															'price' => $product_price,
															'qty'   => $item['qty'],
														) );

														$_amount_saved = $subtotal_regular_price - $subtotal_sale_price;

														$item_price = wc_price( $subtotal_sale_price ) . $product->get_price_suffix();

													} else {
														$item_price = wc_price( \WPCleverWoosb_Helper::get_price_to_display( $product, $item['qty'] ) ) . $product->get_price_suffix();
													}

													break;
												default:
													$_amount_saved = 0;
													$item_price    = $product->get_price_html();
											}

											echo apply_filters( 'woosb_item_price', $item_price, $product );

											if ( $new_price && floatval( $_amount_saved ) > 0 ) {
												echo '<div class="woosb-price-saved">' . __( 'Save', 'minimog' ) . '&nbsp;' . wc_price( $_amount_saved ) . '</div>';
											}
											?>
										</div>
										<div class="woosb-price-new"></div>
										<?php do_action( 'woosb_after_item_price', $product ); ?>
									</div>
								<?php } ?>
							</div>
							<?php do_action( 'woosb_after_item', $product, $global_product, $order ); ?>
						</div>
						<?php
					} elseif ( ! empty( $item['text'] ) ) {
						$item_class = 'woosb-item-text';

						if ( ! empty( $item['type'] ) ) {
							$item_class .= ' woosb-item-text-type-' . $item['type'];
						}

						echo '<div class="' . esc_attr( apply_filters( 'woosb_item_text_class', $item_class, $item, $global_product, $order ) ) . '">';

						if ( empty( $item['type'] ) || ( $item['type'] === 'none' ) ) {
							echo wp_kses_post( $item['text'] );
						} else {
							echo '<' . esc_attr( $item['type'] ) . '>' . wp_kses_post( $item['text'] ) . '</' . esc_attr( $item['type'] ) . '>';
						}

						echo '</div>';
					}

					$order ++;
				}

				// Restore global $product.
				$product    = $global_product;
				$product_id = $global_product_id;
				?>
			</div>
			<?php
			if ( ! $fixed_price && ( $has_variables || $has_optional ) ) {
				echo '<div class="woosb-total woosb-text"></div>';
			}

			echo '<div class="woosb-alert woosb-text" style="display: none"></div>';

			do_action( 'woosb_after_table', $product );

			if ( $after_text = apply_filters( 'woosb_after_text', get_post_meta( $product_id, 'woosb_after_text', true ), $product_id ) ) {
				echo '<div class="woosb-after-text woosb-text">' . wp_kses_post( do_shortcode( $after_text ) ) . '</div>';
			}

			echo '</div>';

			do_action( 'woosb_after_wrap', $product );
		}
	}

	protected function save_amount( $product ) {
		if ( ! $product->is_on_sale() || 'grouped' == $product->get_type() ) {
			return;
		}

		$saved_amount = 0;

		if ( $product->get_type() == 'variable' ) {
			$available_variations = $product->get_available_variations();
			$max_saved_amount     = 0;
			$total_variations     = count( $available_variations );

			for ( $i = 0; $i < $total_variations; $i ++ ) {
				$variation_id          = $available_variations[ $i ]['variation_id'];
				$variable_product      = new \WC_Product_Variation( $variation_id );
				$regular_price         = (float) $variable_product->get_regular_price();
				$sales_price           = (float) $variable_product->get_sale_price();
				$variable_saved_amount = $regular_price && $sales_price ? ( $regular_price - $sales_price ) : 0;

				if ( $variable_saved_amount > $max_saved_amount ) {
					$max_saved_amount = $variable_saved_amount;
				}
			}

			$saved_amount = $max_saved_amount ? $max_saved_amount : $saved_amount;
		} elseif ( $product->get_regular_price() != 0 ) {
			$saved_amount = $product->get_regular_price() - $product->get_sale_price();
		}

		if ( intval( $saved_amount ) > 0 ) {
			echo wc_price( $saved_amount );
		}
	}
}
