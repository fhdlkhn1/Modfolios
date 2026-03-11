<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Product_Brands_Carousel extends Carousel_Base {

	const PRODUCT_BRANDS = 'product_brand';
	private $terms = [];

	public function get_name() {
		return 'tm-product-brands-carousel';
	}

	public function get_title() {
		return __( 'Product Brands Carousel', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-logo';
	}

	public function get_keywords() {
		return [ 'product', 'logo', 'brand' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		parent::register_controls();

		$this->add_query_section();

		$this->add_item_style_section();

		$this->add_image_style_section();

		$this->add_name_style_section();

		$this->update_controls();
	}

	private function add_content_section() {
		$this->start_controls_section( 'content_section', [
			'label' => __( 'Content', 'minimog' ),
		] );

		$this->add_control( 'style', [
			'label'   => __( 'Style', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''   => __( 'Normal', 'minimog' ),
				'01' => __( 'Border', 'minimog' ),
				'02' => __( 'Circle', 'minimog' ),
			],
			'default' => '',
		] );

		$this->add_control( 'hover', [
			'label'   => __( 'Hover Type', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''           => __( 'None', 'minimog' ),
				'grayscale'  => __( 'Grayscale to normal', 'minimog' ),
				'opacity'    => __( 'Opacity to normal', 'minimog' ),
				'blackwhite' => __( 'Normal to grayscale', 'minimog' ),
				'faded'      => __( 'Normal to opacity', 'minimog' ),
			],
			'default' => '',
		] );

		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'      => 'image',
			'default'   => 'full',
			'separator' => 'none',
		] );

		$this->end_controls_section();
	}

	private function add_item_style_section() {
		$this->start_controls_section( 'item_style_section', [
			'label' => __( 'Item', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'item_min_height', [
			'label'      => __( 'Min Height', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [
				'unit' => 'px',
			],
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 1,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .item' => 'min-height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'item_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'item_radius', [
			'label'      => __( 'Border Radius', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => 'px',
			'selectors'  => [
				'{{WRAPPER}} .item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->start_controls_tabs( 'items_colors' );

		$this->start_controls_tab( 'item_colors_normal', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'item_background', [
			'label'     => __( 'Background', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .item' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'item_box_shadow',
			'selector' => '{{WRAPPER}} .item',
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'item_colors_hover', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'item_hover_background', [
			'label'     => __( 'Background', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .item:hover' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'item_hover_box_shadow',
			'selector' => '{{WRAPPER}} .item:hover',
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function add_image_style_section() {
		$this->start_controls_section( 'image_style_section', [
			'label' => __( 'Image', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'image_width', [
			'label'          => __( 'Width', 'minimog' ),
			'type'           => Controls_Manager::SLIDER,
			'default'        => [
				'unit' => '%',
			],
			'tablet_default' => [
				'unit' => '%',
			],
			'mobile_default' => [
				'unit' => '%',
			],
			'size_units'     => [ '%', 'px' ],
			'range'          => [
				'%'  => [
					'min' => 5,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 1000,
				],
			],
			'selectors'      => [
				'{{WRAPPER}} .tm-product-brands' => '--brand-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function add_name_style_section() {
		$this->start_controls_section( 'name_style_section', [
			'label'     => __( 'Name', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'style' => '02',
			],
		] );

		$this->add_responsive_control( 'name_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [
				'unit' => 'px',
			],
			'size_units' => [ 'px', '%', 'em' ],
			'range'      => [
				'%'  => [
					'min' => 0,
					'max' => 100,
				],
				'px' => [
					'min' => 0,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .brand-name' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
			'separator'  => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'name',
			'selector' => '{{WRAPPER}} .brand-name',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
		] );

		$this->start_controls_tabs( 'name_colors' );

		$this->start_controls_tab( 'name_color_normal', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_group_control( Group_Control_Text_Gradient::get_type(), [
			'name'     => 'name',
			'selector' => '{{WRAPPER}} .brand-name',
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'name_color_hover', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_group_control( Group_Control_Text_Gradient::get_type(), [
			'name'     => 'name_hover',
			'selector' => '{{WRAPPER}} .brand-name:hover',
		] );
	}

	private function add_query_section() {
		$this->start_controls_section( 'query_section', [
			'label' => __( 'Query', 'minimog' ),
		] );

		$this->add_control( 'source', [
			'label'       => __( 'Source', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				''                  => __( 'Show All', 'minimog' ),
				'by_id'             => __( 'Manual Selection', 'minimog' ),
				'by_parent'         => __( 'By Parent', 'minimog' ),
				'current_subbrands' => __( 'Current Sub brands', 'minimog' ),
			],
			'label_block' => true,
		] );

		$brands = get_terms( [
			'taxonomy'   => self::PRODUCT_BRANDS,
			'hide_empty' => false,
		] );

		$options = [];
		foreach ( $brands as $brand ) {
			$options[ $brand->term_id ] = $brand->name;
		}

		$this->add_control( 'brands', [
			'label'       => __( 'Brands', 'minimog' ),
			'type'        => Controls_Manager::SELECT2,
			'options'     => $options,
			'default'     => [],
			'label_block' => true,
			'multiple'    => true,
			'condition'   => [
				'source' => 'by_id',
			],
		] );

		$parent_options = [ '0' => __( 'Only Top Level', 'minimog' ) ] + $options;
		$this->add_control(
			'parent', [
			'label'     => __( 'Parent', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '0',
			'options'   => $parent_options,
			'condition' => [
				'source' => 'by_parent',
			],
		] );

		$this->add_control( 'show_name', [
			'label'        => __( 'Show Name', 'minimog' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => '1',
			'default'      => '1',
			'condition'    => [
				'style' => '02',
			],
		] );

		$this->add_control( 'hide_empty', [
			'label'     => __( 'Hide Empty', 'minimog' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => 'yes',
			'label_on'  => 'Hide',
			'label_off' => 'Show',
		] );

		$this->add_control( 'number', [
			'label'     => __( 'Brands Count', 'minimog' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => '6',
			'condition' => [
				'source!' => 'by_id',
			],
		] );

		$this->add_control( 'orderby', [
			'label'     => __( 'Order By', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'name',
			'options'   => [
				'name'        => __( 'Name', 'minimog' ),
				'slug'        => __( 'Slug', 'minimog' ),
				'description' => __( 'Description', 'minimog' ),
				'count'       => __( 'Count', 'minimog' ),
				'order'       => __( 'Brand order', 'minimog' ),
			],
			'condition' => [
				'source!' => 'by_id',
			],
		] );

		$this->add_control( 'order', [
			'label'     => __( 'Order', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'desc',
			'options'   => [
				'asc'  => __( 'ASC', 'minimog' ),
				'desc' => __( 'DESC', 'minimog' ),
			],
			'condition' => [
				'source!' => 'by_id',
			],
		] );

		$this->end_controls_section();
	}

	private function update_controls() {
		$this->update_responsive_control( 'swiper_items', [
			'default'        => 6,
			'tablet_default' => 3,
			'mobile_default' => 2,
		] );
	}

	private function query_terms() {
		$settings = $this->get_settings_for_display();

		$term_args = [
			'taxonomy'   => self::PRODUCT_BRANDS,
			'number'     => $settings['number'],
			'hide_empty' => 'yes' === $settings['hide_empty'],
		];

		// Setup order.
		switch ( $settings['source'] ) {
			case 'by_id':
				$term_args['orderby'] = 'include';
				break;
			default:
				if ( 'order' === $settings['orderby'] ) {
					$term_args['orderby']  = 'meta_value_num';
					$term_args['meta_key'] = 'order';
				} else {
					$term_args['orderby'] = $settings['orderby'];
					$term_args['order']   = $settings['order'];
				}
				break;
		}

		// Setup source.
		switch ( $settings['source'] ) {
			case 'by_id':
				$term_args['include'] = $settings['brands'];
				break;
			case 'by_parent' :
				$term_args['parent'] = $settings['parent'];
				break;
			case 'current_subcategories':
				$term_args['parent'] = get_queried_object_id();
				break;
		}

		$this->terms = get_terms( $term_args );
	}

	protected function print_slides( array $settings ) {
		if ( empty( $this->terms ) ) {
			return;
		}

		$image_size = \Minimog_Image::elementor_parse_image_size( $settings, 'full', 'image' );

		foreach ( $this->terms as $term ) {
			$term_link_key = 'brand-term-' . $term->term_id;
			$link          = get_term_link( $term );
			$thumbnail_id  = get_term_meta( $term->term_id, 'thumbnail_id', true );

			$this->add_render_attribute( $term_link_key, 'class', 'item' );
			$this->add_render_attribute( $term_link_key, 'href', $link );
			?>
			<div class="swiper-slide">
				<a <?php $this->print_render_attribute_string( $term_link_key ); ?>>
					<div class="minimog-image image">
						<?php if ( ! empty( $thumbnail_id ) ) : ?>
							<?php \Minimog_Image::the_attachment_by_id( array(
								'id'   => $thumbnail_id,
								'size' => $image_size,
							) ); ?>
						<?php else: ?>
							<?php echo wc_placeholder_img(); ?>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $settings['show_name'] ) ) { ?>
						<div class="brand-name">
							<?php echo esc_html( $term->name ); ?>
						</div>
					<?php } ?>
				</a>
			</div>
			<?php
		}
	}

	protected function before_slider() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( $this->get_slider_key(), 'class', 'tm-product-brands' );

		if ( ! empty( $settings['hover'] ) ) {
			$this->add_render_attribute( $this->get_slider_key(), 'class', 'hover-' . $settings['hover'] );
		}

		if ( ! empty( $settings['style'] ) ) {
			$this->add_render_attribute( $this->get_slider_key(), 'class', 'style-' . $settings['style'] );
		}

		$this->query_terms();
	}
}
