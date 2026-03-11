<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Product_Categories_List extends Base {

	const PRODUCT_CATEGORY = 'product_cat';
	private $terms = [];

	public function get_name() {
		return 'tm-product-categories-list';
	}

	public function get_title() {
		return __( 'Product Categories List', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-bullet-list';
	}

	public function get_keywords() {
		return [ 'product', 'product category', 'product categories', 'list' ];
	}

	protected function register_controls() {
		$this->add_layout_section();

		$this->add_category_style_section();

		$this->add_query_section();
	}

	private function add_layout_section() {
		$this->start_controls_section( 'product_categories_section', [
			'label' => __( 'Product Categories', 'minimog' ),
		] );

		$this->add_responsive_control( 'alignment', [
			'label'                => __( 'Alignment', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_text_align(),
			'default'              => '',
			'selectors'            => [
				'{{WRAPPER}}' => 'text-align: {{VALUE}};',
			],
			'selectors_dictionary' => [
				'left'  => 'start',
				'right' => 'end',
			],
		] );

		$this->add_control( 'icon', [
			'label'      => __( 'Icon', 'minimog' ),
			'show_label' => true,
			'type'       => Controls_Manager::ICONS,
			'default'    => [],
			'separator'  => 'before',
		] );

		$this->end_controls_section();
	}

	private function add_category_style_section() {
		$this->start_controls_section( 'category_style_section', [
			'label' => __( 'Category', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'category_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 100,
					'min' => 0,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-product-categories-list ul li + li' => 'margin-top: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'category_typography',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_ACCENT,
			],
			'selector' => '{{WRAPPER}} .minimog-product-categories-list ul a',
		] );

		$this->start_controls_tabs( 'category_style_tabs' );

		$this->start_controls_tab(
			'category_style_normal_tab',
			[
				'label' => __( 'Normal', 'minimog' ),
			]
		);

		$this->add_control( 'category_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-product-categories-list ul a'                     => 'color: {{VALUE}};',
				'{{WRAPPER}} .minimog-product-categories-list ul .category-name:before' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab(
			'category_style_hover_tab',
			[
				'label' => __( 'Hover', 'minimog' ),
			]
		);

		$this->add_control( 'category_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-product-categories-list ul a:hover'                       => 'color: {{VALUE}};',
				'{{WRAPPER}} .minimog-product-categories-list ul a:hover .category-name:before' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'icon_style_heading', [
			'label'     => __( 'Icon', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'icon_size', [
			'label'      => __( 'Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 100,
					'min' => 0,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-product-categories-list ul .category-icon' => 'font-size: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'icon_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 100,
					'min' => 0,
				],
			],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .minimog-product-categories-list ul .category-icon' => 'margin-left: {{SIZE}}{{UNIT}}',
				'body.rtl {{WRAPPER}} .minimog-product-categories-list ul .category-icon'       => 'margin-right: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->end_controls_section();
	}

	private function add_query_section() {
		$this->start_controls_section( 'query_section', [
			'label' => __( 'Query', 'minimog' ),
		] );

		$this->add_control( 'source', [
			'label'       => __( 'Source', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				''                      => __( 'Show All', 'minimog' ),
				'by_id'                 => __( 'Manual Selection', 'minimog' ),
				'by_parent'             => __( 'By Parent', 'minimog' ),
				'current_subcategories' => __( 'Current Subcategories', 'minimog' ),
			],
			'label_block' => true,
		] );

		$options = \Minimog_Woo::instance()->get_product_categories_dropdown_options();

		$this->add_control( 'categories', [
			'label'       => __( 'Categories', 'minimog' ),
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

		$this->add_control( 'hide_empty', [
			'label'     => __( 'Hide Empty', 'minimog' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => 'yes',
			'label_on'  => 'Hide',
			'label_off' => 'Show',
		] );

		$this->add_control( 'number', [
			'label'     => __( 'Categories Count', 'minimog' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => '4',
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
				'order'       => __( 'Category order', 'minimog' ),
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

	private function query_terms() {
		$settings = $this->get_settings_for_display();

		$term_args = [
			'taxonomy'   => self::PRODUCT_CATEGORY,
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
				$term_args['include'] = $settings['categories'];
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

	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->query_terms();

		if ( empty( $this->terms ) ) {
			return;
		}

		$this->add_render_attribute( 'wrapper', 'class', 'minimog-product-categories-list' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<ul>
				<?php foreach ( $this->terms as $term ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
							<span class="category-name"><?php echo esc_html( $term->name ); ?></span>
							<?php $this->print_icon( $settings ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	private function print_icon( array $settings ) {
		if ( empty( $settings['icon']['value'] ) ) {
			return;
		}

		$this->add_render_attribute( 'icon', 'class', [
			'category-icon',
			'minimog-solid-icon',
			'minimog-icon',
		] );

		$is_svg = isset( $settings['icon']['library'] ) && 'svg' === $settings['icon']['library'];

		if ( $is_svg ) {
			$this->add_render_attribute( 'icon', 'class', [
				'svg-icon',
			] );
		}

		printf(
			'<span %1$s>%2$s</span>',
			$this->get_render_attribute_string( 'icon' ),
			$this->get_render_icon( $settings, $settings['icon'], [ 'aria-hidden' => 'true' ], $is_svg, 'icon' )
		);
	}
}
