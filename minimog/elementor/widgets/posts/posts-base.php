<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

defined( 'ABSPATH' ) || exit;

abstract class Posts_Base extends Base {

	/**
	 * @var \WP_Query
	 */
	private $_query      = null;
	private $_query_args = null;

	abstract protected function get_post_type();

	abstract protected function get_post_category();

	public function query_posts() {
		$settings          = $this->get_settings_for_display();
		$post_type         = $this->get_post_type();
		$this->_query      = Module_Query_Base::instance()->get_query( $settings, $post_type );
		$this->_query_args = Module_Query_Base::instance()->get_query_args();
	}

	protected function get_query() {
		return $this->_query;
	}

	protected function get_query_args() {
		return $this->_query_args;
	}

	protected function register_controls() {
		$this->register_query_section();
	}

	protected function get_query_author_object() {
		return Module_Query_Base::QUERY_OBJECT_AUTHOR;
	}

	protected function get_query_orderby_options() {
		$options = [
			'date'           => __( 'Date', 'minimog' ),
			'ID'             => __( 'Post ID', 'minimog' ),
			'author'         => __( 'Author', 'minimog' ),
			'title'          => __( 'Title', 'minimog' ),
			'modified'       => __( 'Last modified date', 'minimog' ),
			'parent'         => __( 'Post/page parent ID', 'minimog' ),
			'comment_count'  => __( 'Number of comments', 'minimog' ),
			'menu_order'     => __( 'Menu order/Page Order', 'minimog' ),
			'meta_value'     => __( 'Meta value', 'minimog' ),
			'meta_value_num' => __( 'Meta value number', 'minimog' ),
			'rand'           => __( 'Random order', 'minimog' ),
			'views'          => __( 'Views', 'minimog' ),
		];

		$post_type = $this->get_post_type();

		if ( 'product' === $post_type ) {
			$options = array_merge( $options, [
				'woo_featured'     => __( 'Featured Products', 'minimog' ),
				'woo_best_selling' => __( 'Best Selling Products', 'minimog' ),
				'woo_on_sale'      => __( 'On Sale Products', 'minimog' ),
				'woo_top_rated'    => __( 'Top Rated Products', 'minimog' ),
			] );
		}

		return $options;
	}

	protected function register_query_section() {
		$this->start_controls_section( 'query_section', [
			'label' => __( 'Query', 'minimog' ),
		] );

		$this->add_control( 'query_source', [
			'label'   => __( 'Source', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'custom_query'  => __( 'Custom Query', 'minimog' ),
				'current_query' => __( 'Current Query', 'minimog' ),
			),
			'default' => 'custom_query',
		] );

		$this->start_controls_tabs( 'query_args_tabs', [
			'condition' => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->start_controls_tab( 'query_include_tab', [
			'label' => __( 'Include', 'minimog' ),
		] );

		$this->add_control( 'query_include', [
			'label'       => __( 'Include By', 'minimog' ),
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => [
				'terms'   => __( 'Term', 'minimog' ),
				'authors' => __( 'Author', 'minimog' ),
			],
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_include_term_ids', [
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'options'      => [],
			'label_block'  => true,
			'multiple'     => true,
			'autocomplete' => [
				'object'  => Module_Query_Base::QUERY_OBJECT_TAX,
				'display' => 'detailed',
				'query'   => [
					'post_type' => $this->get_post_type(),
				],
			],
			'condition'    => [
				'query_include' => 'terms',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_include_authors', [
			'label'        => __( 'Author', 'minimog' ),
			'label_block'  => true,
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'multiple'     => true,
			'default'      => [],
			'options'      => [],
			'autocomplete' => [
				'object' => $this->get_query_author_object(),
			],
			'condition'    => [
				'query_include' => 'authors',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'query_exclude_tab', [
			'label' => __( 'Exclude', 'minimog' ),
		] );

		$this->add_control( 'query_exclude', [
			'label'       => __( 'Exclude By', 'minimog' ),
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => [
				'terms'   => __( 'Term', 'minimog' ),
				'authors' => __( 'Author', 'minimog' ),
			],
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_exclude_term_ids', [
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'options'      => [],
			'label_block'  => true,
			'multiple'     => true,
			'autocomplete' => [
				'object'  => Module_Query_Base::QUERY_OBJECT_TAX,
				'display' => 'detailed',
				'query'   => [
					'post_type' => $this->get_post_type(),
				],
			],
			'condition'    => [
				'query_exclude' => 'terms',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_exclude_authors', [
			'label'        => __( 'Author', 'minimog' ),
			'label_block'  => true,
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'multiple'     => true,
			'default'      => [],
			'options'      => [],
			'autocomplete' => [
				'object' => $this->get_query_author_object(),
			],
			'condition'    => [
				'query_exclude' => 'authors',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'query_number', [
			'label'       => __( 'Items per page', 'minimog' ),
			'description' => __( 'Number of items to show per page. Input "-1" to show all posts. Leave blank to use global setting.', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => -1,
			'max'         => 100,
			'step'        => 1,
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
			'separator'   => 'before',
		] );

		$this->add_control( 'query_offset', [
			'label'       => __( 'Offset', 'minimog' ),
			'description' => __( 'Number of items to displace or pass over.', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_orderby', [
			'label'       => __( 'Order by', 'minimog' ),
			'description' => __( 'Select order type. If "Meta value" or "Meta value Number" is chosen then meta key is required.', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => $this->get_query_orderby_options(),
			'default'     => 'date',
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_sort_meta_key', [
			'label'     => __( 'Meta key', 'minimog' ),
			'type'      => Controls_Manager::TEXT,
			'condition' => [
				'query_orderby' => [
					'meta_value',
					'meta_value_num',
				],
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_order', [
			'label'     => __( 'Sort order', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				'DESC' => __( 'Descending', 'minimog' ),
				'ASC'  => __( 'Ascending', 'minimog' ),
			),
			'default'   => 'DESC',
			'condition' => [
				'query_source!'  => [ 'current_query' ],
				'query_orderby!' => [
					'views',
					'woo_best_selling',
					'woo_top_rated',
				],
			],
		] );

		$this->end_controls_section();
	}

	protected function add_pagination_section() {
		$this->start_controls_section( 'pagination_section', [
			'label' => __( 'Pagination', 'minimog' ),
		] );

		$this->add_control( 'pagination_type', [
			'label'   => __( 'Pagination', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				''              => __( 'None', 'minimog' ),
				'numbers'       => __( 'Numbers', 'minimog' ),
				'load-more'     => __( 'Button', 'minimog' ),
				'load-more-alt' => __( 'Custom Button', 'minimog' ),
				'infinite'      => __( 'Infinite Scroll', 'minimog' ),
			),
			'default' => '',
		] );

		$this->add_control( 'pagination_button_style', [
			'label'     => __( 'Style', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'bottom-thick-line',
			'options'   => \Minimog_Helper::get_button_style_options(),
			'condition' => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_control( 'pagination_button_text', [
			'label'     => __( 'Button Text', 'minimog' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'View More', 'minimog' ),
			'condition' => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_control( 'pagination_custom_button_id', [
			'label'       => __( 'Custom Button ID', 'minimog' ),
			'description' => __( 'Input id of custom button to load more posts when click. For e.g: #product-load-more-btn', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'condition'   => [
				'pagination_type' => 'load-more-alt',
			],
		] );

		$this->add_control( 'pagination_button_icon', [
			'label'       => __( 'Icon', 'minimog' ),
			'type'        => Controls_Manager::ICONS,
			'label_block' => true,
			'condition'   => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->end_controls_section();
	}

	protected function add_pagination_style_section() {
		$this->start_controls_section( 'pagination_style_section', [
			'label'     => __( 'Pagination', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'pagination_type!' => '',
			],
		] );

		$this->add_responsive_control( 'pagination_alignment', [
			'label'     => __( 'Alignment', 'minimog' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'   => 'center',
			'selectors' => [
				'{{WRAPPER}} .minimog-grid-pagination' => 'text-align: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'pagination_spacing', [
			'label'       => __( 'Spacing', 'minimog' ),
			'type'        => Controls_Manager::SLIDER,
			'placeholder' => '70',
			'range'       => [
				'px' => [
					'min' => 0,
					'max' => 200,
				],
			],
			'selectors'   => [
				'{{WRAPPER}} .minimog-grid-pagination' => 'padding-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'pagination_button_min_height', [
			'label'      => __( 'Height', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max'  => 300,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button' => 'min-height: {{SIZE}}{{UNIT}};',
			],
			'condition'  => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_responsive_control( 'pagination_buttonwidth', [
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
				'{{WRAPPER}} .minimog-load-more-button.tm-button' => 'min-width: {{SIZE}}{{UNIT}};',
			],
			'condition'  => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'pagination_typography',
			'selector'  => '{{WRAPPER}} .minimog-load-more-button.tm-button',
			'condition' => [
				'pagination_type' => [ 'load-more' ],
			],
		] );

		$this->start_controls_tabs( 'pagination_button_skin_tabs' );

		// Normal
		$this->start_controls_tab( 'pagination_button_skin_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		// Color
		$this->add_control( 'pagination_button_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .page-pagination li'                 => 'color: {{VALUE}};',
				'{{WRAPPER}} .minimog-load-more-button.tm-button' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'pagination_button_line_color', [
			'label'     => __( 'Line', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button.style-bottom-line .button-content-wrapper:before'       => 'background: {{VALUE}};',
				'{{WRAPPER}} .minimog-load-more-button.tm-button.style-bottom-thick-line .button-content-wrapper:before' => 'background: {{VALUE}};',
			],
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'bottom-line', 'bottom-thick-line' ],
			],
		] );

		$this->add_control( 'pagination_button_line_winding_color', [
			'label'     => __( 'Line Winding', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button.style-bottom-line-winding .button-content-wrapper .line-winding svg path' => 'fill: {{VALUE}};',
			],
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'bottom-line-winding' ],
			],
		] );

		// Background
		$this->add_control( 'pagination_button_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button' => 'background-color: {{VALUE}};',
			],
			'condition' => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_control( 'pagination_button_border_color', [
			'label'     => __( 'Border', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button' => 'border-color: {{VALUE}};',
			],
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'border', 'flat' ],
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'      => 'pagination_button_box_shadow',
			'selector'  => '{{WRAPPER}} .minimog-load-more-button.tm-button',
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'border', 'flat' ],
			],
		] );

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab( 'pagination_button_skin_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		// Color
		$this->add_control( 'pagination_button_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .page-pagination li > a:hover'             => 'color: {{VALUE}};',
				'{{WRAPPER}} .minimog-load-more-button.tm-button:hover' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'pagination_hover_button_line_color', [
			'label'     => __( 'Line', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button.style-bottom-line .button-content-wrapper:after'       => 'background: {{VALUE}};',
				'{{WRAPPER}} .minimog-load-more-button.tm-button.style-bottom-thick-line .button-content-wrapper:after' => 'background: {{VALUE}};',
			],
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'bottom-line', 'bottom-thick-line' ],
			],
		] );

		$this->add_control( 'pagination_hover_button_line_winding_color', [
			'label'     => __( 'Line Winding', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button.style-bottom-line-winding:hover .button-content-wrapper .line-winding svg path' => 'fill: {{VALUE}};',
			],
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'bottom-line-winding' ],
			],
		] );

		// Background
		$this->add_control( 'pagination_button_hover_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button:hover' => 'background-color: {{VALUE}};',
			],
			'condition' => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_control( 'pagination_button_hover_border_color', [
			'label'     => __( 'Border', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button:hover' => 'border-color: {{VALUE}};',
			],
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'border', 'flat' ],
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'      => 'pagination_button_hover_box_shadow',
			'selector'  => '{{WRAPPER}} .minimog-load-more-button.tm-button:hover',
			'condition' => [
				'pagination_type'         => 'load-more',
				'pagination_button_style' => [ 'border', 'flat' ],
			],
		] );

		$this->end_controls_tab();

		// Active
		$this->start_controls_tab( 'pagination_button_skin_active_tab', [
			'label'     => __( 'Active', 'minimog' ),
			'condition' => [
				'pagination_type' => [ 'numbers' ],
			],
		] );

		// Color
		$this->add_control( 'pagination_button_active_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'.page-pagination li .current' => 'color: {{VALUE}};',
			],
		] );

		// Background
		$this->add_control( 'pagination_button_active_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .page-pagination li .current' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Button icon
		$this->add_control( 'pagination_button_icon_heading', [
			'label'     => __( 'Button Icon', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_control( 'pagination_button_icon_align', [
			'label'       => __( 'Position', 'minimog' ),
			'type'        => Controls_Manager::CHOOSE,
			'options'     => [
				'left'  => [
					'title' => __( 'Left', 'minimog' ),
					'icon'  => 'eicon-h-align-left',
				],
				'right' => [
					'title' => __( 'Right', 'minimog' ),
					'icon'  => 'eicon-h-align-right',
				],
			],
			'default'     => 'left',
			'toggle'      => false,
			'label_block' => false,
			'render_type' => 'template',
			'condition'   => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_responsive_control( 'pagination_button_icon_margin', [
			'label'      => __( 'Margin', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .minimog-load-more-button.tm-button .button-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .minimog-load-more-button.tm-button .button-icon'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
			'condition'  => [
				'pagination_type' => 'load-more',
			],
		] );

		$this->add_responsive_control( 'pagination_button_icon_font_size', [
			'label'     => __( 'Font Size', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 8,
					'max' => 100,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .minimog-load-more-button.tm-button .button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
			],
			'condition' => [
				'pagination_type' => 'load-more',
			],
		] );

		// Loading Icon
		$this->add_control( 'pagination_loading_heading', [
			'label'     => __( 'Loading Icon', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => [
				'pagination_type!' => 'numbers',
			],
		] );

		$this->add_control( 'pagination_loading_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-infinite-loader .sk-wrap' => 'color: {{VALUE}};',
			],
			'condition' => [
				'pagination_type!' => 'numbers',
			],
		] );

		$this->end_controls_section();
	}

	/**
	 * Check if layout is grid|metro|masonry
	 *
	 * @return bool
	 */
	protected function is_grid() {
		$settings = $this->get_settings_for_display();
		if ( ! empty( $settings['layout'] ) &&
		     in_array( $settings['layout'], array(
			     'grid',
			     'metro',
			     'masonry',
		     ), true ) ) {
			return true;
		}

		return false;
	}

	protected function update_grid_options( $settings, $grid_options ) {
		return $grid_options;
	}

	protected function get_grid_options( array $settings ) {
		$grid_options = [
			'type' => $settings['layout'],
//			'ratio' => $settings['metro_image_ratio']['size'],
		];

		if ( ! empty( $settings['zigzag_reversed'] ) && 'yes' === $settings['zigzag_reversed'] ) {
			$grid_options['zigzagReversed'] = 1;
		}

		$columns_settings       = $this->parse_responsive_settings( $settings, 'grid_columns', 'columns' );
		$gutter_settings        = $this->parse_responsive_settings( $settings, 'grid_gutter', 'gutter' );
		$zigzag_height_settings = $this->parse_responsive_settings( $settings, 'zigzag_height', 'zigzagHeight' );

		$grid_options += $columns_settings + $gutter_settings + $zigzag_height_settings;

		$grid_options = $this->update_grid_options( $settings, $grid_options );

		return $grid_options;
	}

	protected function print_pagination( $query, $settings ) {
		$number          = ! empty( $settings['query_number'] ) ? $settings['query_number'] : get_option( 'posts_per_page' );
		$pagination_type = $settings['pagination_type'];

		if ( $pagination_type !== '' && $query->found_posts > $number ) {
			?>
			<div class="minimog-grid-pagination">
				<div class="pagination-wrapper">

					<?php if ( in_array( $pagination_type, array(
						'load-more',
						'load-more-alt',
						'infinite',
					), true ) ) { ?>
						<div class="inner">
							<div class="minimog-infinite-loader">
								<?php minimog_load_template( 'preloader/style', 'circle' ); ?>
							</div>
						</div>

						<div class="inner">
							<?php if ( $pagination_type === 'load-more' ) { ?>
								<?php
								$this->add_render_attribute( 'load_button_wrapper', [
									'href'  => '#',
									'class' => [
										'minimog-load-more-button',
										'tm-button',
										'tm-button-sm',
										'style-' . $settings['pagination_button_style'],
										'icon-' . $settings['pagination_button_icon_align'],
									],
								] );
								?>
								<a <?php $this->print_render_attribute_string( 'load_button_wrapper' ); ?>>

									<span class="button-content-wrapper">
										<?php
										if ( ! empty( $settings['pagination_button_icon']['value'] ) && 'left' === $settings['pagination_button_icon_align'] ) {
											$this->print_button_icon( $settings );
										}
										?>

										<?php $this->print_button_text( $settings ); ?>

										<?php
										if ( ! empty( $settings['pagination_button_icon'] ) && 'right' === $settings['pagination_button_icon_align'] ) {
											$this->print_button_icon( $settings );
										}
										?>
									</span>
								</a>
							<?php } ?>
						</div>
					<?php } elseif ( $pagination_type === 'numbers' ) { ?>
						<?php \Minimog_Templates::paging_nav( $query ); ?>
					<?php } ?>

				</div>
				<div class="minimog-grid-messages" style="display: none;">
					<?php esc_html_e( 'All items displayed.', 'minimog' ); ?>
				</div>
			</div>
			<?php
		}
	}

	protected function print_button_icon( array $settings ) {
		if ( 'load-more' !== $settings['pagination_type'] ) {
			return;
		}
		$this->add_render_attribute( 'button_icon_wrap', 'class', [
			'minimog-icon',
			'minimog-solid-icon',
			'icon',
		] );

		$is_svg = isset( $settings['pagination_button_icon']['library'] ) && 'svg' === $settings['pagination_button_icon']['library'];

		if ( $is_svg ) {
			$this->add_render_attribute( 'button_icon_wrap', 'class', [
				'minimog-svg-icon',
			] );
		}

		?>
		<div class="button-icon">
			<div <?php $this->print_render_attribute_string( 'button_icon_wrap' ); ?>>
				<?php $this->render_icon( $settings, $settings['pagination_button_icon'], [ 'aria-hidden' => 'true' ], $is_svg, 'pagination_button_icon' ); ?>
			</div>
		</div>
		<?php
	}

	protected function print_button_text( array $settings ) {
		if ( 'load-more' !== $settings['pagination_type'] ) {
			return;
		}

		$text = isset( $settings['pagination_button_text'] ) ? $settings['pagination_button_text'] : '';

		if ( empty( $text ) ) {
			return;
		}

		?>
		<span class="button-text">
			<?php echo esc_html( $text ); ?>

			<?php if ( $settings['pagination_button_style'] === 'bottom-line-winding' ) : ?>
				<span class="line-winding">
					<svg width="42" height="6" viewBox="0 0 42 6" fill="none"
					     xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd"
						      d="M0.29067 2.60873C1.30745 1.43136 2.72825 0.72982 4.24924 0.700808C5.77022 0.671796 7.21674 1.31864 8.27768 2.45638C8.97697 3.20628 9.88872 3.59378 10.8053 3.5763C11.7218 3.55882 12.6181 3.13683 13.2883 2.36081C14.3051 1.18344 15.7259 0.481897 17.2469 0.452885C18.7679 0.423873 20.2144 1.07072 21.2753 2.20846C21.9746 2.95836 22.8864 3.34586 23.8029 3.32838C24.7182 3.31092 25.6133 2.89009 26.2831 2.11613C26.2841 2.11505 26.285 2.11396 26.2859 2.11288C27.3027 0.935512 28.7235 0.233974 30.2445 0.204962C31.7655 0.17595 33.212 0.822796 34.2729 1.96053C34.9722 2.71044 35.884 3.09794 36.8005 3.08045C37.7171 3.06297 38.6134 2.64098 39.2836 1.86496C39.6445 1.44697 40.276 1.40075 40.694 1.76173C41.112 2.12271 41.1582 2.75418 40.7972 3.17217C39.7804 4.34954 38.3597 5.05108 36.8387 5.08009C35.3177 5.1091 33.8712 4.46226 32.8102 3.32452C32.1109 2.57462 31.1992 2.18712 30.2826 2.2046C29.3674 2.22206 28.4723 2.64289 27.8024 3.41684C27.8015 3.41793 27.8005 3.41901 27.7996 3.42009C26.7828 4.59746 25.362 5.299 23.841 5.32801C22.3201 5.35703 20.8735 4.71018 19.8126 3.57244C19.1133 2.82254 18.2016 2.43504 17.285 2.45252C16.3685 2.47 15.4722 2.89199 14.802 3.66802C13.7852 4.84539 12.3644 5.54693 10.8434 5.57594C9.32242 5.60495 7.8759 4.9581 6.81496 3.82037C6.11568 3.07046 5.20392 2.68296 4.28738 2.70044C3.37083 2.71793 2.47452 3.13992 1.80434 3.91594C1.44336 4.33393 0.811887 4.38015 0.393899 4.01917C-0.0240897 3.65819 -0.0703068 3.02672 0.29067 2.60873Z"
						      fill="#E8C8B3"/>
					</svg>
				</span>
			<?php endif; ?>
		</span>
		<?php
	}
}
