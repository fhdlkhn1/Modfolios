<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Utils;

defined( 'ABSPATH' ) || exit;

class Widget_Countdown extends Base {

	public function get_name() {
		return 'tm-countdown';
	}

	public function get_title() {
		return __( 'Countdown', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-countdown';
	}

	public function get_keywords() {
		return [ 'countdown' ];
	}

	public function get_script_depends() {
		return [ 'minimog-widget-countdown' ];
	}

	protected function register_controls() {
		$this->add_countdown_settings_section();

		$this->add_countdown_styling_section();
	}

	private function add_countdown_settings_section() {
		$this->start_controls_section( 'countdown_section', [
			'label' => __( 'Countdown', 'minimog' ),
		] );

		$this->add_control( 'style', [
			'label'       => __( 'Style', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => array(
				'01' => sprintf( __( 'Style %s', 'minimog' ), '01' ),
				'02' => sprintf( __( 'Style %s', 'minimog' ), '02' ),
			),
			'default'     => '01',
			'render_type' => 'template',
		] );

		$this->add_control( 'countdown_type', [
			'label'   => __( 'Type', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'due_date' => __( 'Due Date', 'minimog' ),
				'daily'    => __( 'Daily', 'minimog' ),
			],
			'default' => 'due_date',
		] );

		$this->add_control( 'due_date', [
			'label'       => __( 'Due Date', 'minimog' ),
			'type'        => Controls_Manager::DATE_TIME,
			'default'     => gmdate( 'Y-m-d H:i', strtotime( '+1 month' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
			/* translators: %s: Time zone. */
			'description' => sprintf( __( 'Date set according to your timezone: %s.', 'minimog' ), Utils::get_timezone_string() ),
			'condition'   => [
				'countdown_type' => 'due_date',
			],
		] );

		$this->add_control( 'sale_text_style_hr', [
			'label'     => __( 'Text before', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => [
				'style' => '02',
			],
		] );

		$this->add_control( 'sale_text', [
			'label'       => __( 'Text before', 'minimog' ),
			'show_label'  => false,
			'label_block' => true,
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Sale ends in:', 'minimog' ),
			'default'     => '',
			'condition'   => [
				'style' => '02',
			],
		] );

		$this->add_control( 'sale_text_size', [
			'label'     => __( 'HTML Tag', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => [
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
			'default'   => 'p',
			'condition' => [
				'style' => '02',
			],
		] );

		$this->add_responsive_control( 'layout', [
			'label'                => __( 'Layout', 'minimog' ),
			'label_block'          => false,
			'type'                 => Controls_Manager::CHOOSE,
			'default'              => 'row',
			'options'              => [
				'column' => [
					'title' => __( 'Vertical', 'minimog' ),
					'icon'  => 'eicon-ellipsis-v',
				],
				'row'    => [
					'title' => __( 'Horizontal', 'minimog' ),
					'icon'  => 'eicon-ellipsis-h',
				],
			],
			'selectors'            => [
				'{{WRAPPER}} .countdown-wrap' => 'flex-direction: {{VALUE}}',
			],
			'condition'            => [
				'style'      => '02',
				'sale_text!' => '',
			],
		] );

		$this->add_control( 'hide_countdown_text', [
			'label'        => __( 'Hide Text', 'minimog' ),
			'type'         => Controls_Manager::SWITCHER,
			'prefix_class' => 'minimog-hide-countdown-text-',
			'separator'    => 'before',
		] );

		$this->add_control( 'countdown_days_text', [
			'label'       => __( 'Days Text', 'minimog' ),
			'description' => __( 'Leave blank to use default text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
		] );

		$this->add_control( 'countdown_hours_text', [
			'label'       => __( 'Hours Text', 'minimog' ),
			'description' => __( 'Leave blank to use default text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
		] );

		$this->add_control( 'countdown_minutes_text', [
			'label'       => __( 'Minutes Text', 'minimog' ),
			'description' => __( 'Leave blank to use default text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
		] );

		$this->add_control( 'countdown_seconds_text', [
			'label'       => __( 'Seconds Text', 'minimog' ),
			'description' => __( 'Leave blank to use default text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
		] );

		$this->end_controls_section();
	}

	private function add_countdown_styling_section() {
		$this->start_controls_section( 'countdown_style_section', [
			'label' => __( 'Countdown Styling', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'countdown_max_width', [
			'label'          => __( 'Width', 'minimog' ),
			'type'           => Controls_Manager::SLIDER,
			'default'        => [
				'unit' => 'px',
			],
			'tablet_default' => [
				'unit' => 'px',
			],
			'mobile_default' => [
				'unit' => 'px',
			],
			'size_units'     => [ 'px', '%' ],
			'range'          => [
				'%'  => [
					'min' => 1,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 1600,
				],
			],
			'selectors'      => [
				'{{WRAPPER}} .countdown-wrap' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .countdown-clock > div'          => 'margin-bottom: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .countdown-clock .clock-divider' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_alignment', [
			'label'                => __( 'Alignment', 'minimog' ),
			'label_block'          => false,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .minimog-countdown' => 'display: flex; justify-content: {{VALUE}}',
			],
		] );

		$this->add_responsive_control( 'countdown_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .countdown-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .countdown-wrap'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_rounded', [
			'label'      => __( 'Rounded', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .countdown-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'countdown_bg',
			'types'    => [ 'classic', 'gradient' ],
			'selector' => '{{WRAPPER}} .countdown-wrap',
		] );

		$this->add_control( 'sale_text_hr', [
			'label'     => __( 'Text before', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition'            => [
				'style'      => '02',
				'sale_text!' => '',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'sale_text_typography',
			'label'     => __( 'Text Typography', 'minimog' ),
			'selector'  => '{{WRAPPER}} .sale-text',
			'condition'            => [
				'style'      => '02',
				'sale_text!' => '',
			],
		] );

		$this->add_control( 'sale_text_color', [
			'label'     => __( 'Text', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .sale-text' => 'color: {{VALUE}};',
			],
			'condition'            => [
				'style'      => '02',
				'sale_text!' => '',
			],
		] );

		$this->add_responsive_control( 'sale_text_margin', [
			'label'      => __( 'Margin', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .sale-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .sale-text'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
			'condition'            => [
				'style'      => '02',
				'sale_text!' => '',
			],
		] );

		$this->add_control( 'countdown_item_hr', [
			'label'     => __( 'Item', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'countdown_item_width', [
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
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .clock-item' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_item_height', [
			'label'      => __( 'Height', 'minimog' ),
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
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .clock-item' => 'height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_item_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .clock-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_item_rounded', [
			'label'      => __( 'Rounded', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .clock-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'countdown_item_background', [
			'label'     => __( 'Background', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .clock-item' => 'background: {{VALUE}};',
			],
		] );

		$this->add_control( 'countdown_number_hr', [
			'label'     => __( 'Number', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'countdown_number_typography',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .countdown .number',
		] );

		$this->add_control( 'countdown_number_color', [
			'label'     => __( 'Number', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .countdown .number' => 'color: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'countdown_number_width', [
			'label'      => __( 'Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 1,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .countdown .number' => 'min-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'countdown_text_hr', [
			'label'     => __( 'Text', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'countdown_text_typography',
			'label'    => __( 'Text Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .countdown .text',
		] );

		$this->add_control( 'countdown_text_color', [
			'label'     => __( 'Text', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .countdown .text' => 'color: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'countdown_text_top_spacing', [
			'label'     => __( 'Spacing', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 0,
					'max' => 50,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .countdown .text' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'countdown_divider_hr', [
			'label'     => __( 'Divider', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'countdown_divider_typography',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .countdown .clock-divider, {{WRAPPER}} .countdown .clock-divider:before',
		] );

		$this->add_control( 'countdown_divider_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .countdown .clock-divider'        => 'color: {{VALUE}};',
				'{{WRAPPER}} .countdown .clock-divider:before' => 'color: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'countdown_divider_width', [
			'label'          => __( 'Width', 'minimog' ),
			'type'           => Controls_Manager::SLIDER,
			'default'        => [
				'unit' => 'px',
			],
			'tablet_default' => [
				'unit' => 'px',
			],
			'mobile_default' => [
				'unit' => 'px',
			],
			'size_units'     => [ 'px', '%' ],
			'range'          => [
				'%'  => [
					'min' => 1,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 100,
				],
			],
			'selectors'      => [
				'{{WRAPPER}} .countdown .clock-divider' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'countdown_divider_posx', [
			'label'      => __( 'Position X', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => -200,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .countdown .clock-divider' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$days_text    = ! empty( $settings['countdown_days_text'] ) ? $settings['countdown_days_text'] : __( 'Days', 'minimog' );
		$hours_text   = ! empty( $settings['countdown_hours_text'] ) ? $settings['countdown_hours_text'] : __( 'Hours', 'minimog' );
		$minutes_text = ! empty( $settings['countdown_minutes_text'] ) ? $settings['countdown_minutes_text'] : __( 'Minutes', 'minimog' );
		$seconds_text = ! empty( $settings['countdown_seconds_text'] ) ? $settings['countdown_seconds_text'] : __( 'Seconds', 'minimog' );

		if ( \Minimog_Helper::is_demo_site() ) {
			$datetime = $this->get_sample_countdown_date();
		} else {
			switch ( $settings['countdown_type'] ) {
				case 'due_date':
					$due_date = $settings['due_date'];
					// Handle timezone ( we need to set GMT time ).
					$gmt      = get_gmt_from_date( $due_date . ':00' );
					$datetime = date( 'm/d/Y H:i:s', strtotime( $gmt ) );
					break;
				case 'daily':
					$now      = strtotime( current_time( 'm/d/Y H:i:s' ) );
					$endOfDay = strtotime( "tomorrow", $now ) - 1;

					$datetime = date( 'm/d/Y H:i:s', $endOfDay );
					break;
			}
		}

		if ( ! $datetime ) {
			return;
		}

		$this->add_render_attribute( 'countdown', [
			'class'             => 'countdown',
			'data-date'         => $datetime,
			'data-days-text'    => $days_text,
			'data-hours-text'   => $hours_text,
			'data-minutes-text' => $minutes_text,
			'data-seconds-text' => $seconds_text,
		] );

		$this->add_render_attribute( 'countdown-wrap', [
			'class' => [
				'minimog-countdown',
				'minimog-countdown--style-' . $settings['style'],
				'minimog-box',
			],
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'countdown-wrap' ); ?>>
			<div class="countdown-wrap">
				<?php if ( ! empty( $settings['sale_text'] ) ) : ?>
					<?php printf( '<%1$s class="sale-text">%2$s</%1$s>', $settings['sale_text_size'], $settings['sale_text'] ); ?>
				<?php endif; ?>
				<div <?php $this->print_render_attribute_string( 'countdown' ); ?>></div>
			</div>
		</div>
		<?php
	}

	private function get_sample_countdown_date() {
		$date = date( 'm/d/Y H:i:s', strtotime( '+1 month', strtotime( date( 'm/d/Y H:i:s' ) ) ) );

		return $date;
	}
}
