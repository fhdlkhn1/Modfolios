<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

abstract class Carousel_Base extends Base {

	protected $slider_key = 'slider';

	public function get_script_depends() {
		return [ 'minimog-group-widget-carousel' ];
	}

	protected function set_slider_key( $name ) {
		$this->slider_key = $name;
	}

	protected function get_slider_key() {
		return $this->slider_key;
	}

	abstract protected function print_slides( array $settings );

	protected function register_controls() {
		$this->add_swiper_options_section();

		$this->add_swiper_arrows_style_section();

		$this->add_swiper_dots_style_section();
	}

	private function add_swiper_options_section() {
		$this->start_controls_section( 'swiper_options_section', [
			'label' => __( 'Carousel Options', 'minimog' ),
		] );

		/**
		 * We don't use "frontend_available" js var
		 * But we need it to avoid responsive settings lost on the second page load.
		 */
		$this->add_responsive_control( 'swiper_items', [
			'label'              => __( 'Slides Per View', 'minimog' ),
			'type'               => Controls_Manager::SELECT,
			'options'            => array(
				'auto'       => __( 'Auto', 'minimog' ),
				'auto-fixed' => __( 'Auto - Fixed Width', 'minimog' ),
				'1'          => '1',
				'2'          => '2',
				'3'          => '3',
				'4'          => '4',
				'5'          => '5',
				'6'          => '6',
				'7'          => '7',
				'8'          => '8',
			),
			'default'            => '3',
			'tablet_default'     => '2',
			'mobile_default'     => '1',
			'render_type'        => 'template',
			'frontend_available' => true,
			'selectors'          => [
				'{{WRAPPER}} .tm-swiper' => '--slides-view: {{VALUE}}',
			],
		] );

		$this->add_responsive_control( 'swiper_items_per_group', [
			'label'     => __( 'Slides Per Group', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				'inherit' => __( 'Same as Slides Per View', 'minimog' ),
				'1'       => '1',
				'2'       => '2',
				'3'       => '3',
				'4'       => '4',
				'5'       => '5',
				'6'       => '6',
				'7'       => '7',
				'8'       => '8',
			),
			'condition' => [
				'swiper_items!' => [ 'auto', 'auto-fixed' ],
			],
		] );

		$this->add_responsive_control( 'swiper_slides_width', [
			'label'       => __( 'Slides Width', 'minimog' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => [ 'px', '%' ],
			'range'       => [
				'px' => [
					'min'  => 100,
					'max'  => 1000,
					'step' => 1,
				],
				'%'  => [
					'min' => 10,
					'max' => 100,
				],
			],
			'render_type' => 'template',
			'selectors'   => [
				'{{WRAPPER}} .tm-swiper'                                                                                                 => '--slides-width:{{SIZE}}{{UNIT}}',
				'{{WRAPPER}} .tm-swiper > .swiper-inner > .swiper > .swiper-wrapper[data-active-items="auto"] > .swiper-slide' => 'width: {{SIZE}}{{UNIT}} !important;',
			],
			'condition'   => [
				'swiper_items' => 'auto-fixed',
			],
		] );

		$this->add_responsive_control( 'swiper_slides_max_width', [
			'label'      => __( 'Slides Max Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => 100,
					'max'  => 1000,
					'step' => 1,
				],
				'%'  => [
					'min' => 10,
					'max' => 100,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-swiper > .swiper-inner > .swiper > .swiper-wrapper[data-active-items="auto"] > .swiper-slide' => 'max-width: {{SIZE}}{{UNIT}} !important;',
			],
			'condition'  => [
				'swiper_items' => 'auto-fixed',
			],
		] );

		$this->add_responsive_control( 'swiper_gutter', [
			'label'       => __( 'Space Between', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 200,
			'step'        => 1,
			'default'     => 30,
			'render_type' => 'template',
			'selectors'   => [
				'{{WRAPPER}} .tm-swiper' => '--gutter: {{VALUE}}',
			],
		] );

		$this->add_control( 'swiper_effect', [
			'label'   => __( 'Transition', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'slide' => __( 'Slide', 'minimog' ),
				'fade'  => __( 'Fade', 'minimog' ),
			),
			'default' => 'slide',
		] );

		$this->add_control( 'swiper_speed', [
			'label'       => __( 'Transition Duration', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'placeholder' => '500',
		] );

		$this->add_control( 'swiper_autoplay', [
			'label'       => __( 'Auto Play', 'minimog' ),
			'description' => __( 'Delay between transitions (in ms). For e.g: 3000. Leave blank to disabled. Input 1 to make smooth transition.', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => '',
		] );

		$this->add_control( 'swiper_loop', [
			'label'   => __( 'Infinite Loop', 'minimog' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'swiper_looped_slides', [
			'label'     => __( 'Looped Slides', 'minimog' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0,
			'max'       => 8,
			'step'      => 1,
			'default'   => '',
			'condition' => [
				'swiper_loop' => 'yes',
			],
		] );

		$this->add_control( 'swiper_free_mode', [
			'label' => __( 'Free Mode', 'minimog' ),
			'type'  => Controls_Manager::SWITCHER,
		] );

		$this->add_control( 'swiper_touch', [
			'label'       => __( 'Touchable', 'minimog' ),
			'description' => __( 'Click and drag to change slides', 'minimog' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => 'yes',
		] );

		$this->add_control( 'swiper_mousewheel', [
			'label' => __( 'Mousewheel', 'minimog' ),
			'type'  => Controls_Manager::SWITCHER,
		] );

		$this->add_control( 'swiper_centered', [
			'label' => __( 'Centered', 'minimog' ),
			'type'  => Controls_Manager::SWITCHER,
		] );

		$this->add_control( 'swiper_centered_highlight', [
			'label'     => __( 'Highlight Active Items', 'minimog' ),
			'type'      => Controls_Manager::SWITCHER,
			'condition' => [
				'swiper_centered' => 'yes',
			],
		] );

		$this->add_control( 'swiper_centered_highlight_style', [
			'label'     => __( 'Highlight Style', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				'opacity' => __( 'Opacity', 'minimog' ),
			),
			'default'   => 'opacity',
			'condition' => [
				'swiper_centered'           => 'yes',
				'swiper_centered_highlight' => 'yes',
			],
		] );

		$this->add_control( 'swiper_navigation_heading', [
			'label'     => __( 'Navigation', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_swiper_arrows_popover();

		$this->add_swiper_dots_popover();

		$this->add_control( 'swiper_inner_heading', [
			'label'     => __( 'Slider Inner', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'swiper_inner_margin', [
			'label'       => __( 'Margin', 'minimog' ),
			'type'        => Controls_Manager::DIMENSIONS,
			'size_units'  => [ 'px', '%', 'em' ],
			'selectors'   => [
				'body:not(.rtl) {{WRAPPER}} .swiper-inner' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .swiper-inner'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
			'render_type' => 'template',
		] );

		$this->add_control( 'swiper_container_heading', [
			'label'     => __( 'Slider Container', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'swiper_container_padding', [
			'label'       => __( 'Padding', 'minimog' ),
			'type'        => Controls_Manager::DIMENSIONS,
			'size_units'  => [ 'px', '%', 'em' ],
			'selectors'   => [
				'body:not(.rtl) {{WRAPPER}} .swiper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				'body.rtl {{WRAPPER}} .swiper'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}} !important;',
			],
			'render_type' => 'template',
		] );

		$this->add_control( 'swiper_content_alignment_heading', [
			'label'     => __( 'Content Alignment', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'swiper_content_horizontal_align', [
			'label'                => __( 'Horizontal Align', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'selectors_dictionary' => [
				'left'   => '--swiper-content-h-align: flex-start;',
				'center' => '--swiper-content-h-align: center;',
				'right'  => '--swiper-content-h-align: flex-end;',
			],
			'selectors'            => [
				'{{WRAPPER}} .tm-swiper' => '{{VALUE}}; --swiper-content-display: flex;',
			],
		] );

		$this->add_responsive_control( 'swiper_content_vertical_align', [
			'label'                => __( 'Vertical Align', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_vertical_alignment_full(),
			'selectors_dictionary' => [
				'top'     => '--swiper-content-v-align: flex-start;',
				'middle'  => '--swiper-content-v-align: center;',
				'bottom'  => '--swiper-content-v-align: flex-end;',
				'stretch' => '--swiper-content-v-align: auto; --swiper-slide-height: auto; --swiper-slide-children-height: 100%;',
			],
			'selectors'            => [
				'{{WRAPPER}} .tm-swiper' => '{{VALUE}}; --swiper-content-display: flex;',
			],
		] );

		$this->end_controls_section();
	}

	/**
	 * Register swiper arrows options in popover.
	 */
	private function add_swiper_arrows_popover() {
		$this->add_control( 'swiper_arrows_show', [
			'label'        => __( 'Arrows', 'minimog' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'label_off'    => __( 'Hide', 'minimog' ),
			'label_on'     => __( 'Show', 'minimog' ),
			'return_value' => 'yes',
		] );

		$this->start_popover();

		$this->add_control( 'custom_nav_button_id', [
			'label'       => __( 'Button ID', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'dynamic'     => [
				'active' => true,
			],
			'default'     => '',
			'title'       => __( 'Input your custom nav button id WITHOUT the Pound key. e.g: my-id', 'minimog' ),
			'label_block' => false,
			'description' => wp_kses( __( 'Please make sure the ID is the same ID that you input in Carousel Nav Buttons widget', 'minimog' ), 'minimog-default' ),
		] );

		$this->add_control( 'swiper_arrows_style', [
			'label'   => __( 'Style', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'01' => '01',
				'02' => '02',
				'03' => '03',
				'04' => '04',
				'05' => '05',
				'06' => '06',
			],
			'default' => '01',
		] );

		$this->add_control( 'swiper_arrows_aligned_by', [
			'label'       => __( 'Aligned By', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				'slider' => __( 'Slider', 'minimog' ),
				'grid'   => __( 'Grid', 'minimog' ),
			],
			'default'     => 'slider',
			'render_type' => 'template',
		] );

		$this->add_responsive_control( 'swiper_arrows_horizontal_align', [
			'label'                => __( 'Horizontal Align', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment_full(),
			'default'              => 'stretch',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'    => 'flex-start',
				'right'   => 'flex-end',
				'stretch' => 'space-between',
			],
			'selectors'            => [
				'{{WRAPPER}} .swiper-nav-buttons' => 'justify-content: {{VALUE}}',
			],
			'render_type'          => 'template',
		] );

		$this->add_responsive_control( 'swiper_arrows_vertical_align', [
			'label'                => __( 'Vertical Align', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_vertical_alignment(),
			'default'              => 'center',
			'toggle'               => false,
			'selectors_dictionary' => [
				'top'    => 'flex-start',
				'middle' => 'center',
				'bottom' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .swiper-nav-buttons' => 'align-items: {{VALUE}}',
			],
			'render_type'          => 'template',
		] );

		$this->add_control( 'swiper_left_arrow_hr', [
			'type' => Controls_Manager::DIVIDER,
		] );

		$this->add_control( 'swiper_left_arrow_heading', [
			'label' => __( 'Left Arrow', 'minimog' ),
			'type'  => Controls_Manager::HEADING,
		] );

		$this->add_responsive_control( 'swiper_left_arrow_margin', [
			'label'      => __( 'Offset', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .swiper-button-prev' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .swiper-button-prev'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'swiper_right_arrow_hr', [
			'type' => Controls_Manager::DIVIDER,
		] );

		$this->add_control( 'swiper_right_arrow_heading', [
			'label' => __( 'Right Arrow', 'minimog' ),
			'type'  => Controls_Manager::HEADING,
		] );

		$this->add_responsive_control( 'swiper_right_arrow_margin', [
			'label'      => __( 'Offset', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .swiper-button-next' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .swiper-button-next'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'swiper_arrows_visibility_hr', [
			'type' => Controls_Manager::DIVIDER,
		] );

		$this->add_control( 'swiper_arrows_visibility_heading', [
			'label' => __( 'Visibility', 'minimog' ),
			'type'  => Controls_Manager::HEADING,
		] );

		$this->add_control( 'swiper_arrows_show_always', [
			'label' => __( 'Show Always', 'minimog' ),
			'type'  => Controls_Manager::SWITCHER,
		] );

		$this->end_popover();
	}

	/**
	 * Register swiper bullets options in popover.
	 */
	private function add_swiper_dots_popover() {
		$this->add_control( 'swiper_dots_show', [
			'label'        => __( 'Dots', 'minimog' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'label_off'    => __( 'Hide', 'minimog' ),
			'label_on'     => __( 'Show', 'minimog' ),
			'return_value' => 'yes',
		] );

		$this->start_popover();

		$this->add_control( 'swiper_dots_style', [
			'label'   => __( 'Style', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'01' => __( '01 - Circle Bullets', 'minimog' ),
				'02' => __( '02 - Rectangle Bullets', 'minimog' ),
				'03' => __( '03 - Fraction', 'minimog' ),
				'04' => __( '04 - Fraction & Circle Arrows', 'minimog' ),
				'05' => __( '05 - Modern Circle Bullets', 'minimog' ),
				'06' => __( '06 - Fraction 02', 'minimog' ),
				'07' => __( '07 - Fraction 03', 'minimog' ),
				'08' => __( '08 - Circle Bullets & Arrows', 'minimog' ),
				'09' => __( '09 - Progress Bar', 'minimog' ),
				'10' => __( '10 - Circle Bullets & Arrows 02', 'minimog' ),
				'11' => __( '11 - Circle Bullets', 'minimog' ),
			],
			'default' => '01',
		] );

		$this->add_control( 'swiper_dots_aligned_by', [
			'label'       => __( 'Aligned By', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				'slider' => __( 'Slider', 'minimog' ),
				'grid'   => __( 'Grid', 'minimog' ),
			],
			'default'     => 'slider',
			'render_type' => 'template',
		] );

		$this->add_responsive_control( 'pagination_spacing', [
			'label'      => __( 'Dot Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max'  => 200,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-swiper' => '--swiper-pagination-spacing: {{SIZE}}{{UNIT}};',
			],
			'condition'  => [
				'swiper_dots_style!' => [ '04', '06', ],
			],
		] );

		$pagination_spacing = 'var(--swiper-pagination-spacing)';

		$this->add_responsive_control( 'swiper_dots_direction', [
			'label'                => __( 'Direction', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => [
				'horizontal' => [
					'title' => __( 'Horizontal', 'minimog' ),
					'icon'  => 'eicon-navigation-horizontal',
				],
				'vertical'   => [
					'title' => __( 'Vertical', 'minimog' ),
					'icon'  => 'eicon-navigation-vertical',
				],
			],
			'selectors_dictionary' => [
				'horizontal' => sprintf( '--swiper-pagination-direction: row; --swiper-pagination-v-spacing: 0; --swiper-pagination-h-spacing: %s;', $pagination_spacing ),
				'vertical'   => sprintf( '--swiper-pagination-direction: column; --swiper-pagination-arrow-rotate: 90deg; --swiper-pagination-h-spacing: 0; --swiper-pagination-v-spacing: %s;', $pagination_spacing ),
			],
			'default'              => 'horizontal',
			'toggle'               => false,
			'selectors'            => [
				'{{WRAPPER}} .tm-swiper' => '{{VALUE}}',
			],
		] );

		// Dots Horizontal
		$this->add_responsive_control( 'swiper_dots_horizontal_offset', [
			'label'      => __( 'Horizontal Offset', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => - 1000,
					'max'  => 1000,
					'step' => 1,
				],
				'%'  => [
					'min' => - 100,
					'max' => 100,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-swiper' => '--swiper-pagination-h-offset: {{SIZE}}{{UNIT}}',
			],
		] );

		$pagination_h_offset_var = 'var(--swiper-pagination-h-offset)';

		$this->add_responsive_control( 'swiper_dots_horizontal_align', [
			'label'                => __( 'Horizontal Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => 'center',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'   => sprintf( '--swiper-pagination-horizontal-align: flex-start; --swiper-pagination-margin-left: %s; --swiper-pagination-margin-right: 0;', $pagination_h_offset_var ),
				'right'  => sprintf( '--swiper-pagination-horizontal-align: flex-end; --swiper-pagination-margin-left: 0; --swiper-pagination-margin-right: %s;', $pagination_h_offset_var ),
				'center' => sprintf( '--swiper-pagination-horizontal-align: center; --swiper-pagination-margin-left: %s; --swiper-pagination-margin-right: 0;', $pagination_h_offset_var ),
			],
			'selectors'            => [
				'{{WRAPPER}} .tm-swiper' => '{{VALUE}}',
			],
		] );

		// Dots Vertical
		$this->add_responsive_control( 'swiper_dots_vertical_offset', [
			'label'      => __( 'Vertical Offset', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => - 1000,
					'max'  => 1000,
					'step' => 1,
				],
				'%'  => [
					'min' => - 100,
					'max' => 100,
				],
			],
			'default'    => [
				'unit' => 'px',
				'size' => 44,
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-swiper' => '--swiper-pagination-v-offset: {{SIZE}}{{UNIT}}',
			],
		] );

		$pagination_v_offset_var = 'var(--swiper-pagination-v-offset)';

		$this->add_responsive_control( 'swiper_dots_vertical_align', [
			'label'                => __( 'Vertical Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => [
				'top'    => [
					'title' => __( 'Top', 'minimog' ),
					'icon'  => 'eicon-v-align-top',
				],
				'middle' => [
					'title' => __( 'Middle', 'minimog' ),
					'icon'  => 'eicon-v-align-middle',
				],
				'bottom' => [
					'title' => __( 'Bottom', 'minimog' ),
					'icon'  => 'eicon-v-align-bottom',
				],
				'below'  => [
					'title' => __( 'Below Slider', 'minimog' ),
					'icon'  => 'eicon-thumbnails-down',
				],
			],
			'default'              => 'below',
			'toggle'               => false,
			'selectors_dictionary' => [
				'top'    => sprintf( '--swiper-pagination-vertical-align: flex-start; --swiper-pagination-vertical-position: absolute; --swiper-pagination-margin-top: %s; --swiper-pagination-margin-bottom: 0;', $pagination_v_offset_var ),
				'middle' => sprintf( '--swiper-pagination-vertical-align: center; --swiper-pagination-vertical-position: absolute; --swiper-pagination-margin-top: %s; --swiper-pagination-margin-bottom: 0;', $pagination_v_offset_var ),
				'bottom' => sprintf( '--swiper-pagination-vertical-align: flex-end; --swiper-pagination-vertical-position: absolute; --swiper-pagination-margin-top: 0; --swiper-pagination-margin-bottom: %s;', $pagination_v_offset_var ),
				'below'  => sprintf( '--swiper-pagination-vertical-align: flex-end; --swiper-pagination-vertical-position: static; --swiper-pagination-margin-top: %s; --swiper-pagination-margin-bottom: 0;', $pagination_v_offset_var ),
			],
			'selectors'            => [
				'{{WRAPPER}} .tm-swiper' => '{{VALUE}}',
			],
		] );

		$this->end_popover();
	}

	private function add_swiper_arrows_style_section() {
		$this->start_controls_section( 'swiper_arrows_style_section', [
			'label'     => __( 'Carousel Arrows', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'swiper_arrows_show' => 'yes',
			],
		] );

		$this->add_responsive_control( 'swiper_arrows_size', [
			'label'      => __( 'Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min'  => 10,
					'max'  => 200,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .swiper-nav-button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'swiper_arrows_icon_size', [
			'label'      => __( 'Icon Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min'  => 8,
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .swiper-nav-button' => 'font-size: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->start_controls_tabs( 'swiper_arrows_style_tabs' );

		$this->start_controls_tab( 'swiper_arrows_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'swiper_arrows_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_arrows_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button' => 'background: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_arrows_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button' => 'border-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'swiper_arrows_box_shadow',
			'selector' => '{{WRAPPER}} .swiper-nav-button',
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'swiper_arrows_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'swiper_arrows_hover_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button:hover' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_arrows_hover_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button:hover' => '--minimog-swiper-nav-button-hover-background: {{VALUE}}; background: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_arrows_hover_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button:hover' => 'border-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'swiper_arrows_hover_box_shadow',
			'selector' => '{{WRAPPER}} .swiper-nav-button:hover',
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control( 'swiper_arrows_border_width', [
			'label'     => __( 'Border Width', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'selectors' => [
				'{{WRAPPER}} .swiper-nav-button' => 'border-width: {{SIZE}}{{UNIT}}',
			],
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'swiper_arrows_border_radius', [
			'label'      => __( 'Border Radius', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%', 'px' ],
			'range'      => [
				'%'  => [
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'max'  => 200,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .swiper-nav-button' => 'border-radius: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->end_controls_section();
	}

	private function add_swiper_dots_style_section() {
		$this->start_controls_section( 'swiper_dots_style_section', [
			'label'     => __( 'Carousel Dots', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'swiper_dots_show' => 'yes',
			],
		] );

		$this->add_responsive_control( 'fraction_alignment', [
			'label'                => __( 'Vertical Alignment', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_vertical_alignment(),
			'selectors_dictionary' => [
				'top'    => 'flex-start',
				'middle' => 'center',
				'bottom' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .pagination-style-06 .fraction' => 'display: flex; align-items: {{VALUE}}',
			],
			'condition'            => [
				'swiper_dots_style' => [ '06' ],
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'fraction_typography',
			'label'     => __( 'Typography', 'minimog' ),
			'selector'  => '{{WRAPPER}} .pagination-style-03 .fraction,
							{{WRAPPER}} .pagination-style-06 .fraction,
							{{WRAPPER}} .pagination-style-07 .swiper-pagination-bullet',
			'condition' => [
				'swiper_dots_style' => [ '03', '06', '07' ],
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'fraction_current_typography',
			'label'     => __( 'Current Typography', 'minimog' ),
			'selector'  => '{{WRAPPER}} .pagination-style-06 .fraction .current',
			'condition' => [
				'swiper_dots_style' => [ '06' ],
			],
		] );

		$this->add_control( 'swiper_dots_primary_color', [
			'label'     => __( 'Primary Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-swiper' => '--swiper-pagination-color-primary: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_dots_secondary_color', [
			'label'     => __( 'Secondary Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-swiper' => '--swiper-pagination-color-secondary: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'pagination_separator_width', [
			'label'      => __( 'Separator Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max'  => 100,
					'min'  => 20,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-swiper' => '--swiper-pagination-separator-line-width: {{SIZE}}{{UNIT}};',
			],
			'condition'  => [
				'swiper_dots_style' => [ '07' ],
			],
		] );

		$this->add_control( 'swiper_pagination_arrows_style_heading', [
			'label'     => __( 'Nav Buttons', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => [
				'swiper_dots_style' => [ '04' ],
			],
		] );

		$this->start_controls_tabs( 'swiper_pagination_arrows_style_tabs', [
			'condition' => [
				'swiper_dots_style' => [ '04' ],
			],
		] );

		$this->start_controls_tab( 'swiper_pagination_arrows_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'swiper_pagination_arrows_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-alt-arrow-button' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_pagination_arrows_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-alt-arrow-button' => 'background: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_pagination_arrows_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-alt-arrow-button' => 'border-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'swiper_pagination_arrows_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'swiper_pagination_arrows_hover_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-alt-arrow-button:hover' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_pagination_arrows_hover_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-alt-arrow-button:hover' => 'background: {{VALUE}};',
			],
		] );

		$this->add_control( 'swiper_pagination_arrows_hover_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .swiper-alt-arrow-button:hover' => 'border-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function before_slider() {
	}

	protected function after_slider() {
	}

	protected function before_slider_container() {
	}

	protected function after_slider_container() {
	}

	protected function update_slider_settings( $settings, $slider_settings ) {
		return $slider_settings;
	}

	protected function get_slider_settings( array $settings ) {
		$slider_settings = [
			'class' => [ 'tm-swiper tm-slider-widget use-elementor-breakpoints' ],
		];

		$items_settings           = $this->parse_slider_responsive_settings( $settings, 'swiper_items', 'items' );
		$items_per_group_settings = $this->parse_slider_responsive_settings( $settings, 'swiper_items_per_group', 'items-group' );
		$gutter_settings          = $this->parse_slider_responsive_settings( $settings, 'swiper_gutter', 'gutter' );

		$slider_settings += $items_settings + $items_per_group_settings + $gutter_settings;

		if ( ! empty( $settings['swiper_content_vertical_align'] ) ) {
			$slider_settings['class'][] = 'v-' . $settings['swiper_content_vertical_align'];
		}

		if ( ! empty( $settings['swiper_content_horizontal_align'] ) ) {
			$slider_settings['class'][] = 'h-' . $settings['swiper_content_horizontal_align'];
		}

		if ( ! empty( $settings['swiper_arrows_show'] ) ) {
			$slider_settings['data-nav']            = '1';
			$slider_settings['data-nav-aligned-by'] = $settings['swiper_arrows_aligned_by'];
			$slider_settings['data-nav-style']      = $settings['swiper_arrows_style'];
			$slider_settings['class'][]             = 'nav-style-' . $settings['swiper_arrows_style'];

			if ( '' !== $settings['custom_nav_button_id'] ) {
				$slider_settings['data-custom-nav'] = $settings['custom_nav_button_id'];
			}

			if ( 'yes' === $settings['swiper_arrows_show_always'] ) {
				$slider_settings['class'][] = 'nav-show-always';
			}
		}

		if ( ! empty( $settings['swiper_dots_show'] ) ) {
			$dots_style      = ! empty( $settings['swiper_dots_style'] ) ? $settings['swiper_dots_style'] : '01';
			$dots_aligned_by = $settings['swiper_dots_aligned_by'] ?? 'slider';
			$dots_h_align    = $settings['swiper_dots_horizontal_align'] ?? 'center';
			$dots_v_align    = $settings['swiper_dots_vertical_align'] ?? 'below';
			$dots_dir        = $settings['swiper_dots_direction'] ?? 'horizontal';

			$slider_settings['class'][] = 'pagination-style-' . $dots_style;
			$slider_settings['class'][] = 'bullets-' . $dots_dir;
			$slider_settings['class'][] = 'bullets-h-align-' . $dots_h_align;
			$slider_settings['class'][] = 'bullets-v-align-' . $dots_v_align;

			$slider_settings['data-pagination-aligned-by'] = $dots_aligned_by;

			$slider_settings['data-pagination'] = '1';

			switch ( $dots_style ) {
				case '03':
				case '04':
				case '06':
					$slider_settings['data-pagination-type'] = 'custom';
					break;
				case '09':
					$slider_settings['data-pagination-type'] = 'progressbar';
					$slider_settings['class'][]              = 'swiper-pagination-type-progressbar';
					break;
			}

			if ( '04' === $dots_style ) {
				$slider_settings['data-pagination-text'] = __( 'Show', 'minimog' ) . '&nbsp;';
			}
		}

		if ( ! empty( $settings['swiper_loop'] ) && 'yes' === $settings['swiper_loop'] ) {
			$slider_settings['data-loop'] = '1';

			if ( isset( $settings['swiper_looped_slides'] ) && '' !== $settings['swiper_looped_slides'] ) {
				$slider_settings['data-looped-slides'] = intval( $settings['swiper_looped_slides'] );
			}
		}

		if ( ! empty( $settings['swiper_centered'] ) && 'yes' === $settings['swiper_centered'] ) {
			$slider_settings['data-centered'] = '1';

			if ( ! empty( $settings['swiper_centered_highlight'] ) && 'yes' === $settings['swiper_centered_highlight'] ) {
				$slider_settings['class'][] = 'highlight-centered-items';
				$slider_settings['class'][] = 'highlight-centered-items-' . $settings['swiper_centered_highlight_style'];
			}
		}

		if ( ! empty( $settings['swiper_free_mode'] ) && 'yes' === $settings['swiper_free_mode'] ) {
			$slider_settings['data-free-mode'] = '1';
		}

		if ( ! empty( $settings['swiper_mousewheel'] ) && 'yes' === $settings['swiper_mousewheel'] ) {
			$slider_settings['data-mousewheel'] = '1';
		}

		if ( ! empty( $settings['swiper_touch'] ) && 'yes' === $settings['swiper_touch'] ) {
			$slider_settings['data-simulate-touch'] = '1';
		}

		if ( ! empty( $settings['swiper_autoplay_reverse_direction'] ) && 'yes' === $settings['swiper_autoplay_reverse_direction'] ) {
			$slider_settings['data-autoplay-reverse-direction'] = '1';
		}

		if ( ! empty( $settings['swiper_speed'] ) ) {
			$slider_settings['data-speed'] = $settings['swiper_speed'];
		}

		if ( ! empty( $settings['swiper_autoplay'] ) ) {
			$slider_settings['data-autoplay'] = $settings['swiper_autoplay'];
		}

		if ( ! empty( $settings['swiper_effect'] ) ) {
			$slider_settings['data-effect'] = $settings['swiper_effect'];
		}

		/**
		 * Use for scaling up animation
		 */
		if ( ! empty( $settings['swiper_gutter'] ) ) {
			$slider_settings['style'][] = "--slide-gutter: {$settings['swiper_gutter']}px";
		}

		return $this->update_slider_settings( $settings, $slider_settings );
	}

	/**
	 * @param array  $settings Elementor widget settings
	 * @param string $name     Setting name
	 * @param string $attr_base_name
	 * @param array  $excludes Array of string to exclude
	 *
	 * @return array
	 */
	protected function parse_slider_responsive_settings( $settings, $name, $attr_base_name, $excludes = array() ) {
		$breakpoints = [
			'widescreen'   => 'wide-screen',
			'desktop'      => 'desktop',
			'laptop'       => 'laptop',
			'tablet_extra' => 'tablet-extra',
			'tablet'       => 'tablet',
			'mobile_extra' => 'mobile-extra',
			'mobile'       => 'mobile',
		];

		$results = [];

		foreach ( $breakpoints as $breakpoint => $suffix ) {
			$setting_name = 'desktop' === $breakpoint ? $name : $name . '_' . $breakpoint;
			$attr_name    = "data-$attr_base_name-$suffix";

			if ( ! isset( $settings[ $setting_name ] ) || '' === $settings[ $setting_name ] ) {
				continue;
			}

			if ( ! empty( $excludes ) && in_array( $settings[ $setting_name ], $excludes, true ) ) {
				continue;
			}

			$results[ $attr_name ] = $settings[ $setting_name ];
		}

		return $results;
	}

	protected function print_slider( array $settings = null ) {
		if ( null === $settings ) {
			$settings = $this->get_active_settings();
		}

		$slider_settings = $this->get_slider_settings( $settings );

		$this->add_render_attribute( $this->get_slider_key(), $slider_settings );
		?>

		<div <?php $this->print_render_attribute_string( $this->get_slider_key() ); ?>>
			<div class="swiper-inner">

				<?php $this->before_slider_container(); ?>

				<div class="swiper">
					<div class="swiper-wrapper">
						<?php $this->print_slides( $settings ); ?>
					</div>
				</div>

				<?php $this->after_slider_container(); ?>

			</div>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->before_slider();

		$this->print_slider( $settings );

		$this->after_slider();
	}
}
