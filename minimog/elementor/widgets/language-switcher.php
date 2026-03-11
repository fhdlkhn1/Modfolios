<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Language_Switcher extends Base {

	public function get_name() {
		return 'tm-language-switcher';
	}

	public function get_title() {
		return __( 'Language Switcher', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-globe';
	}

	public function get_keywords() {
		return [ 'language', 'national', 'switcher' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		$this->add_style_section();
	}

	private function add_content_section() {
		$this->start_controls_section( 'language_section', [
			'label' => __( 'Language', 'minimog' ),
		] );

		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$this->add_control(
				'add_form_notice',
				[
					'show_label'      => false,
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => wp_kses(
						__( 'The language switcher requires WPML plugin to be installed and activated.', 'minimog' ),
						[
							'b'  => [],
							'br' => [],
						]
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				]
			);
		}

		$this->add_control( 'x_direction', [
			'label'   => __( 'Directions', 'minimog' ),
			'type'    => Controls_Manager::CHOOSE,
			'options' => [
				'down' => [
					'title' => __( 'Down', 'minimog' ),
					'icon'  => 'eicon-arrow-down',
				],
				'up'   => [
					'title' => __( 'Up', 'minimog' ),
					'icon'  => 'eicon-arrow-up',
				],
			],
			'default' => 'down',
			'toggle'  => false,
		] );

		$this->add_control( 'y_direction', [
			'label'      => __( 'Direction', 'minimog' ),
			'type'       => Controls_Manager::CHOOSE,
			'options'    => [
				'left'  => [
					'title' => __( 'Left', 'minimog' ),
					'icon'  => 'eicon-arrow-left',
				],
				'right' => [
					'title' => __( 'Right', 'minimog' ),
					'icon'  => 'eicon-arrow-right',
				],
			],
			'default'    => 'left',
			'toggle'     => false,
			'show_label' => false,
		] );

		$this->end_controls_section();
	}

	private function add_style_section() {
		$this->start_controls_section( 'language_style_section', [
			'label' => __( 'Language', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		// Active Item.
		$this->add_control( 'active_item_style_hr', [
			'label' => __( 'Active Item', 'minimog' ),
			'type'  => Controls_Manager::HEADING,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'active_item',
			'selector' => '{{WRAPPER}} .wpml-ls-item-toggle',
		] );

		$this->start_controls_tabs( 'active_item_style_tabs' );

		$this->start_controls_tab( 'active_item_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'active_item_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-item-toggle' => 'color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'active_item_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'active_item_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-item-toggle:hover' => 'color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// List Items.
		$this->add_control( 'list_items_style_hr', [
			'label'     => __( 'List Items', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'list_items_wrapper_min_width', [
			'label'      => __( 'Wrapper Min Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 170,
					'max' => 500,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .wpml-ls-sub-menu' => 'min-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'list_items_wrapper_padding', [
			'label'      => __( 'Wrapper Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .wpml-ls-sub-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .wpml-ls-sub-menu'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'list_items_wrapper_bg_color', [
			'label'     => __( 'Wrapper Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-sub-menu' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'sub_item',
			'selector' => '{{WRAPPER}} .wpml-ls-sub-menu a',
		] );

		$this->start_controls_tabs( 'sub_item_style_tabs' );

		$this->start_controls_tab( 'sub_item_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'sub_item_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-sub-menu a' => 'color: {{VALUE}} !important;',
			],
		] );

		$this->add_control( 'sub_item_bg_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-sub-menu a' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'sub_item_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'sub_item_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-sub-menu a:hover' => 'color: {{VALUE}} !important;',
			],
		] );

		$this->add_control( 'sub_item_hover_bg_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .wpml-ls-sub-menu a:hover' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$classes = [
			'switcher-language-wrapper',
			'switcher-language-wrapper--elementor',
			'switcher-language-wrapper--' . $settings['x_direction'],
			'switcher-language-wrapper--' . $settings['y_direction'],
		];

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		$this->add_render_attribute( 'wrapper', 'id', 'switcher-language-wrapper' );

		do_action( 'minimog_before_add_language_selector_elementor', $settings );

		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return;
		}

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php do_action( 'wpml_add_language_selector' ); ?>
		</div>
		<?php
	}
}
