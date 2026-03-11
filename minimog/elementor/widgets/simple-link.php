<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Simple_Link extends Base {

	public function get_name() {
		return 'tm-simple-link';
	}

	public function get_title() {
		return __( 'Simple Link', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-editor-link';
	}

	public function get_keywords() {
		return [ 'link', 'text' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		$this->add_style_section();
	}

	private function add_content_section() {
		$this->start_controls_section( 'link_section', [
			'label' => __( 'Link', 'minimog' ),
		] );

		$this->add_control( 'link_style', [
			'label'        => __( 'Link Style', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'default'      => '',
			'options'      => [
				''                  => __( 'None', 'minimog' ),
				'bottom-line'       => __( 'Bottom line', 'minimog' ),
				'bottom-thick-line' => __( 'Bottom thick line', 'minimog' ),
			],
		] );

		$this->add_control( 'link', [
			'label'       => __( 'Link', 'minimog' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => [
				'active' => true,
			],
			'placeholder' => __( 'https://your-link.com', 'minimog' ),
		] );

		$this->add_control( 'link_text', [
			'label'   => __( 'Link Text', 'minimog' ),
			'type'    => Controls_Manager::TEXT,
			'default' => __( 'Click here', 'minimog' ),
			'dynamic' => [
				'active' => true,
			],
		] );

		$this->add_control( 'text_before', [
			'label'       => __( 'Text before', 'minimog' ),
			'type'        => Controls_Manager::TEXTAREA,
			'dynamic'     => [
				'active' => true,
			],
			'default'     => '',
			'label_block' => true,
			'separator'   => 'before',
		] );

		$this->add_control( 'text_after', [
			'label'       => __( 'Text after', 'minimog' ),
			'type'        => Controls_Manager::TEXTAREA,
			'dynamic'     => [
				'active' => true,
			],
			'default'     => '',
			'label_block' => true,
		] );

		$this->end_controls_section();
	}

	private function add_style_section() {
		$this->start_controls_section( 'link_style_section', [
			'label' => __( 'Link', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'text_align', [
			'label'                => __( 'Text Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_text_align_full(),
			'selectors'            => [
				'{{WRAPPER}} .tm-simple-link' => 'text-align: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'max_width', [
			'label'      => __( 'Width', 'minimog' ),
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
				'{{WRAPPER}} .tm-simple-link' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'horizontal_alignment', [
			'label'                => __( 'Horizontal Alignment', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => '',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}}',
			],
		] );

		$this->add_control( 'link_style_heading', [
			'label'     => __( 'Link', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'link_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .simple-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .simple-link'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'link_typography',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .simple-link',
		] );

		$this->start_controls_tabs( 'link_colors' );

		$this->start_controls_tab( 'link_colors_normal', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'link_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .simple-link' => 'color: {{VALUE}}',
			],
		]);

		$this->add_control( 'link_line_color', [
			'label'     => __( 'Line Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .simple-link:before' => 'background-color: {{VALUE}}',
			],
			'condition' => [
				'link_style!' => '',
			],
		]);

		$this->end_controls_tab();

		$this->start_controls_tab( 'link_colors_hover', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'link_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .simple-link:hover' => 'color: {{VALUE}}',
			],
		]);

		$this->add_control( 'link_line_hover_color', [
			'label'     => __( 'Line Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .simple-link:after' => 'background-color: {{VALUE}}',
			],
			'condition' => [
				'link_style!' => '',
			],
		]);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Text Style
		$this->add_control( 'text_style_heading', [
			'label'     => __( 'Text', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'text_typography',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .tm-simple-link',
		] );

		$this->add_control( 'text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-simple-link' => 'color: {{VALUE}}',
			],
		]);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'tm-simple-link' );

		if ( $settings['link_style'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'tm-simple-link--' . $settings['link_style'] );
		}

		$text_before = ! empty( $settings['text_before'] ) ? wp_kses( $settings['text_before'], 'minimog-default' ) : '';
		$text_after  = ! empty( $settings['text_after'] ) ? wp_kses( $settings['text_after'], 'minimog-default' ) : '';

		$link = $this->get_link( $settings );

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php echo '' . $text_before . $link . $text_after; ?>
		</div>
		<?php
	}

	protected function get_link( array $settings ) {
		$link_tag = 'span';
		$this->add_render_attribute( 'link_wrapper', 'class', 'simple-link' );

		if ( ! empty( $settings['link']['url'] ) ) {
			$link_tag = 'a';
			$this->add_link_attributes( 'link_wrapper', $settings['link'] );
		}

		return sprintf( '<%1$s %2$s>%3$s</%1$s>', $link_tag, $this->get_render_attribute_string( 'link_wrapper' ), esc_html( $settings['link_text'] ) );
	}
}
