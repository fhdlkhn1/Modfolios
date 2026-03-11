<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;

defined( 'ABSPATH' ) || exit;

class Modify_Widget_Column extends Modify_Base {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function initialize() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], PHP_INT_MAX );

		//add_action( 'elementor/frontend/column/before_render', [ $this, 'before_render_options' ], 10 );
		add_action( 'elementor/element/column/layout/before_section_end', [ $this, 'add_column_order_control' ] );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [
			$this,
			'update_padding_option_selectors',
		] );
		add_action( 'elementor/element/column/layout/after_section_end', [ $this, 'add_column_collapsible' ] );
		add_action( 'elementor/element/column/section_typo/after_section_end', [
			$this,
			'add_column_collapsible_style',
		] );
	}

	/**
	 * @param \Elementor\Widget_Base $element The edited element.
	 */
	public function add_column_collapsible( $element ) {
		$element->start_controls_section( 'collapsible_section', [
			'tab'   => Controls_Manager::TAB_LAYOUT,
			'label' => __( 'Collapsible', 'minimog' ),
		] );

		$device_options = [];

		$active_devices     = Plugin::$instance->breakpoints->get_active_devices_list();
		$active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

		foreach ( $active_devices as $breakpoint_key ) {
			$label = 'desktop' === $breakpoint_key ? __( 'Desktop', 'minimog' ) : $active_breakpoints[ $breakpoint_key ]->get_label();

			$device_options[ $breakpoint_key ] = $label;
		}

		$element->add_control( 'tm_collapsible', [
			'label'              => __( 'Enable', 'minimog' ),
			'description'        => __( 'This works on the frontend only.', 'minimog' ),
			'type'               => Controls_Manager::SWITCHER,
			'label_off'          => __( 'Yes', 'minimog' ),
			'label_on'           => __( 'No', 'minimog' ),
			'default'            => '',
			'frontend_available' => true,
		] );

		$element->add_control( 'tm_collapsible_on', [
			'label'              => __( 'Collapsible On', 'minimog' ),
			'type'               => Controls_Manager::SELECT2,
			'multiple'           => true,
			'label_block'        => true,
			'default'            => [
				Breakpoints_Manager::BREAKPOINT_KEY_MOBILE,
				Breakpoints_Manager::BREAKPOINT_KEY_MOBILE_EXTRA,
			],
			'options'            => $device_options,
			'frontend_available' => true,
			'condition'          => [
				'tm_collapsible' => 'yes',
			],
		] );

		$element->add_control( 'tm_collapsible_status', [
			'label'              => __( 'Status', 'minimog' ),
			'type'               => Controls_Manager::SWITCHER,
			'label_off'          => __( 'Close', 'minimog' ),
			'label_on'           => __( 'Open', 'minimog' ),
			'return_value'       => 'open',
			'default'            => 'open',
			'frontend_available' => true,
			'condition'          => [
				'tm_collapsible' => 'yes',
			],
		] );

		$element->add_control( 'tm_collapsible_title_hr', [
			'label'     => __( 'Title', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => [
				'tm_collapsible' => 'yes',
			],
		] );

		$element->add_control( 'tm_collapsible_title', [
			'label'              => __( 'Title', 'minimog' ),
			'type'               => Controls_Manager::TEXTAREA,
			'dynamic'            => [
				'active' => true,
			],
			'show_label'         => false,
			'placeholder'        => __( 'Enter your title', 'minimog' ),
			'default'            => __( 'Add Your Heading Text Here', 'minimog' ),
			'frontend_available' => true,
			'condition'          => [
				'tm_collapsible' => 'yes',
			],
		] );

		$element->add_control( 'tm_collapsible_title_size', [
			'label'              => __( 'HTML Tag', 'minimog' ),
			'type'               => Controls_Manager::SELECT,
			'options'            => [
				'h1'   => 'H1',
				'h2'   => 'H2',
				'h3'   => 'H3',
				'h4'   => 'H4',
				'h5'   => 'H5',
				'h6'   => 'H6',
				'div'  => 'div',
				'span' => 'span',
				'p'    => 'p',
			],
			'default'            => 'h4',
			'frontend_available' => true,
			'condition'          => [
				'tm_collapsible' => 'yes',
			],
		] );

		$element->end_controls_section();
	}

	/**
	 * @param \Elementor\Widget_Base $element The edited element.
	 */
	public function add_column_collapsible_style( $element ) {
		$element->start_controls_section(
			'collapsible_style_section',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'type'  => Controls_Manager::SECTION,
				'label' => __( 'Collapsible', 'minimog' ),
			]
		);

		$element->add_control( 'heading_style_hr', [
			'label'     => __( 'Heading', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$element->add_responsive_control( 'heading_align', [
			'label'                => __( 'Alignment', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_text_align(),
			'default'              => '',
			'selectors'            => [
				'{{WRAPPER}} .tm-collapsible__title' => 'text-align: {{VALUE}};',
			],
			'selectors_dictionary' => [
				'left'  => 'start',
				'right' => 'end',
			],
		] );

		$element->add_responsive_control( 'title_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .tm-collapsible__title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .tm-collapsible__title'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$element->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .tm-collapsible__title',
		] );

		$element->add_control( 'title_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-collapsible__title' => 'color: {{VALUE}};',
			],
		] );

		$element->end_controls_section();
	}

	/**
	 * Adding column order control to layout section.
	 *
	 * @param \Elementor\Widget_Base $element The edited element.
	 */
	public function add_column_order_control( $element ) {
		$element->add_responsive_control( 'order', [
			'label'     => __( 'Column Order', 'minimog' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1,
			'max'       => 12,
			'step'      => 1,
			'selectors' => [
				'{{WRAPPER}}' => 'order: {{VALUE}};',
			],
		] );
	}

	/**
	 * Update padding option selectors.
	 *
	 * @param \Elementor\Widget_Base $element The edited element.
	 */
	public function update_padding_option_selectors( $element ) {
		$element->update_responsive_control( 'padding', [
			'selectors' => [
				// Make stronger selector for compatible with theme.
				'body:not(.rtl) {{WRAPPER}} > .elementor-element-populated.elementor-element-populated' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} > .elementor-element-populated.elementor-element-populated'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );
	}

	public function enqueue_scripts() {
		wp_register_script( 'minimog-column-collapsible', MINIMOG_ELEMENTOR_URI . '/assets/js/column.js', array(
			'jquery',
			'elementor-frontend',
		), MINIMOG_THEME_VERSION, true );

		wp_enqueue_script( 'minimog-column-collapsible' );
	}

	/**
	 * @param \Elementor\Element_Base $element
	 */
	public function before_render_options( $element ) {
		$settings = $element->get_settings_for_display();

		wp_enqueue_script( 'minimog-column-collapsible' );

		if ( isset( $settings['tm_collapsible'] ) && 'yes' === $settings['tm_collapsible'] ) {
			$element->add_render_attribute( '_wrapper', 'class', 'elementor-column__tm-collapsible' );
		}
	}
}

Modify_Widget_Column::instance()->initialize();
