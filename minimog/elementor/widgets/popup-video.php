<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Modules\DynamicTags\Module as TagsModule;

defined( 'ABSPATH' ) || exit;

class Widget_Popup_Video extends Base {

	public function get_name() {
		return 'tm-popup-video';
	}

	public function get_title() {
		return __( 'Popup Video', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-youtube';
	}

	public function get_keywords() {
		return [ 'popup', 'video', 'player', 'embed', 'youtube', 'vimeo' ];
	}

	public function get_script_depends() {
		return [ 'lightgallery' ];
	}

	public function get_style_depends() {
		return [ 'lightgallery' ];
	}

	protected function register_controls() {
		$this->add_video_section();

		$this->add_image_style_section();

		$this->add_overlay_style_section();

		$this->add_caption_style_section();

		$this->add_button_style_section();
	}

	private function add_video_section() {
		$this->start_controls_section( 'video_section', [
			'label' => __( 'Video', 'minimog' ),
		] );

		$this->add_control( 'video_type', [
			'label'   => __( 'Source', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'external',
			'options' => [
				'external' => __( 'External', 'minimog' ),
				'hosted'   => __( 'Self Hosted', 'minimog' ),
			],
		] );

		$this->add_control( 'hosted_url', [
			'label'      => __( 'Choose File', 'minimog' ),
			'type'       => Controls_Manager::MEDIA,
			'dynamic'    => [
				'active'     => true,
				'categories' => [
					TagsModule::MEDIA_CATEGORY,
				],
			],
			'media_type' => 'video',
			'condition'  => [
				'video_type' => 'hosted',
			],
		] );

		$this->add_control( 'video_url', [
			'label'       => __( 'Video Url', 'minimog' ),
			'description' => __( 'Input Youtube video url or Vimeo video url. For e.g: "https://www.youtube.com/watch?v=XHOmBV4js_E"', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
			'condition'   => [
				'video_type' => 'external',
			],
		] );

		$this->add_control( 'type', [
			'label'   => __( 'Type', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'poster',
			'options' => [
				'poster' => __( 'Poster', 'minimog' ),
				'button' => __( 'Button', 'minimog' ),
			],
		] );

		$this->add_control( 'video_text', [
			'label'         => __( 'Video Text', 'minimog' ),
			'type'          => Controls_Manager::TEXT,
			'label  _block' => true,
			'condition'     => [
				'button_type!' => 'text',
			],
		] );

		$this->add_control( 'video_text_animate', [
			'label'        => __( 'Text Animate', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'options'      => [
				''             => __( 'None', 'minimog' ),
				'animate-line' => __( 'Animate Line', 'minimog' ),
			],
			'default'      => '',
			'prefix_class' => 'minimog-text-',
			'condition'    => [
				'button_type!' => 'text',
				'video_text!'  => '',
			],
		] );

		$this->add_control( 'position', [
			'label'        => __( 'Icon Position', 'minimog' ),
			'type'         => Controls_Manager::CHOOSE,
			'default'      => 'top',
			'options'      => [
				'left'  => [
					'title' => __( 'Left', 'minimog' ),
					'icon'  => 'eicon-h-align-left',
				],
				'top'   => [
					'title' => __( 'Top', 'minimog' ),
					'icon'  => 'eicon-v-align-top',
				],
				'right' => [
					'title' => __( 'Right', 'minimog' ),
					'icon'  => 'eicon-h-align-right',
				],
			],
			'prefix_class' => 'minimog-popup-video-icon-position-',
			'toggle'       => false,
			'condition'    => [
				'button_type!' => 'text',
				'video_text!'  => '',
			],
		] );

		$this->add_control( 'poster', [
			'label'     => __( 'Poster Image', 'minimog' ),
			'type'      => Controls_Manager::MEDIA,
			'default'   => [
				'url' => Utils::get_placeholder_image_src(),
			],
			'condition' => [
				'type' => [ 'poster' ],
			],
		] );

		$this->add_control( 'poster_background', [
			'label'        => __( 'Set Poster as Background', 'minimog' ),
			'type'         => Controls_Manager::SWITCHER,
			'prefix_class' => 'minimog-popup-video-poster-background-',
		] );

		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'      => 'poster',
			'default'   => 'full',
			'condition' => [
				'type' => [ 'poster' ],
			],
		] );

		$this->add_control( 'poster_caption', [
			'label'         => __( 'Caption', 'minimog' ),
			'type'          => Controls_Manager::TEXTAREA,
			'label  _block' => true,
			'condition'     => [
				'type' => [ 'poster' ],
			],
		] );

		$this->add_control( 'hover_effect', [
			'label'        => __( 'Hover Effect', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'options'      => [
				''         => __( 'None', 'minimog' ),
				'zoom-in'  => __( 'Zoom In', 'minimog' ),
				'zoom-out' => __( 'Zoom Out', 'minimog' ),
			],
			'default'      => '',
			'prefix_class' => 'minimog-animation-',
			'condition'    => [
				'type' => [ 'poster' ],
			],
		] );

		$this->add_responsive_control( 'align', [
			'label'        => __( 'Alignment', 'minimog' ),
			'type'         => Controls_Manager::CHOOSE,
			'options'      => Widget_Utils::get_control_options_horizontal_alignment(),
			'prefix_class' => 'elementor%s-align-',
			'default'      => '',
		] );

		$this->add_control( 'button_type', [
			'label'     => __( 'Button Type', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '',
			'options'   => [
				''      => __( 'Default', 'minimog' ),
				'image' => __( 'Image', 'minimog' ),
				'text'  => __( 'Text', 'minimog' ),
			],
			'separator' => 'before',
		] );

		$this->add_control( 'button_image', [
			'label'     => __( 'Button Image', 'minimog' ),
			'type'      => Controls_Manager::MEDIA,
			'default'   => [
				'url' => $this->get_default_play_icon(),
			],
			'condition' => [
				'button_type' => 'image',
			],
			'classes'   => 'minimog-control-media-auto',
		] );

		$this->add_control( 'button_text', [
			'label'       => __( 'Text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => false,
			'default'     => __( 'Play', 'minimog' ),
			'condition'   => [
				'button_type' => 'text',
			],
		] );

		$this->add_control( 'button_effect', [
			'label'        => __( 'Button Effect', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'default'      => '',
			'options'      => [
				''           => __( 'None', 'minimog' ),
				'wave-pulse' => __( 'Wave Pulse', 'minimog' ),
			],
			'prefix_class' => 'minimog-popup-video--button-effect-',
			'render_type'  => 'template',
			'condition'    => [
				'button_type' => '',
			],
		] );

		$this->end_controls_section();
	}

	private function add_image_style_section() {
		$this->start_controls_section( 'image_style_section', [
			'label'     => __( 'Image', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'type' => 'poster',
			],
		] );

		$this->add_responsive_control( 'image_height', [
			'label'      => __( 'Height', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%', 'px', 'vh' ],
			'range'      => [
				'%'  => [
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'min'  => 100,
					'max'  => 1000,
					'step' => 1,
				],
				'vh' => [
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-popup-video .video-poster' => 'height: {{SIZE}}{{UNIT}}',
			],
			'condition'  => [
				'poster_background' => 'yes',
			],
		] );

		$this->add_responsive_control( 'image_width', [
			'label'      => __( 'Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%', 'px', 'vw' ],
			'range'      => [
				'%'  => [
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'max'  => 1600,
					'step' => 1,
				],
				'vw' => [
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-popup-video' => 'width: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'video_horizontal_alignment', [
			'label'                => __( 'Horizontal Alignment', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => 'center',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}}',
			],
		] );

		/**
		 * use .video-poster instead of .minimog-image
		 * to make it working perfect with hover effects.
		 */

		$this->add_responsive_control( 'image_border_width', [
			'label'     => __( 'Border Width', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'selectors' => [
				'{{WRAPPER}} .video-poster' => 'border-width: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'image_border_radius', [
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
				'{{WRAPPER}} .video-poster' => 'border-radius: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->start_controls_tabs( 'image_style_tabs' );

		$this->start_controls_tab( 'image_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'image_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .video-poster' => 'border-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'image_box_shadow',
			'selector' => '{{WRAPPER}} .video-poster',
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'image_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'hover_image_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .video-link:hover .video-poster' => 'border-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'hover_image_box_shadow',
			'selector' => '{{WRAPPER}} .video-link:hover .video-poster',
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function add_caption_style_section() {
		$this->start_controls_section( 'caption_style_section', [
			'label'     => __( 'Caption', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'type'            => 'poster',
				'poster_caption!' => '',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'caption_typography',
			'selector' => '{{WRAPPER}} .video-poster-caption',
		] );

		$this->add_control( 'caption_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .video-poster-caption' => 'background: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'caption_spacing', [
			'label'     => __( 'Spacing', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 0,
					'max' => 200,
				],
			],
			'default'   => [
				'unit' => 'px',
			],
			'selectors' => [
				'{{WRAPPER}} .video-poster-caption' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function add_overlay_style_section() {
		$this->start_controls_section( 'overlay_style_section', [
			'label'     => __( 'Overlay', 'minimog' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [
				'type' => 'poster',
			],
		] );

		$this->start_controls_tabs( 'overlay_style_tabs' );

		$this->start_controls_tab( 'overlay_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'overlay_background', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .video-overlay' => 'background: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'overlay_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'overlay_hover_background', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .video-link:hover .video-overlay' => 'background: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function add_button_style_section() {
		$this->start_controls_section( 'button_style_section', [
			'label' => __( 'Button', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		/**
		 * Video Text
		 */
		$text_conditions = [
			'button_type!' => 'text',
			'video_text!'  => '',
		];

		$text_line_conditions = [
			'button_type!'       => 'text',
			'video_text!'        => '',
			'video_text_animate' => 'animate-line',
		];

		$this->add_control( 'video_text_heading', [
			'label'     => __( 'Video Text', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'condition' => $text_conditions,
		] );

		$this->add_responsive_control( 'video_text_margin', [
			'label'      => __( 'Margin', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .video-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .video-text'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
			'condition'  => $text_conditions,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'video_text_typography',
			'selector'  => '{{WRAPPER}} .video-text',
			'condition' => $text_conditions,
		] );

		$this->start_controls_tabs( 'video_text_style_tabs', [
			'condition' => $text_conditions,
		] );

		$this->start_controls_tab( 'video_text_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'video_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-text' => 'color: {{VALUE}};',
			],
			'condition' => $text_conditions,
		] );

		$this->add_control( 'video_text_line_color', [
			'label'     => __( 'Text Line Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-text:before' => 'background: {{VALUE}};',
			],
			'condition' => $text_line_conditions,
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'video_text_text_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'hover_video_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-link:hover .video-text' => 'color: {{VALUE}};',
			],
			'condition' => $text_conditions,
		] );

		$this->add_control( 'hover_video_text_line_color', [
			'label'     => __( 'Text Line Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-text:after' => 'background: {{VALUE}};',
			],
			'condition' => $text_line_conditions,
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Button
		$this->add_control( 'button_style_heading', [
			'label'     => __( 'Button', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$button_alignment_conditions = [
			'type' => 'poster',
		];

		$this->add_responsive_control( 'poster_button_h_align', [
			'label'                => __( 'Horizontal Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => 'center',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .video-button' => 'justify-content: {{VALUE}}',
			],
			'condition'            => $button_alignment_conditions,
		] );

		$this->add_responsive_control( 'poster_button_v_align', [
			'label'                => __( 'Vertical Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_vertical_alignment(),
			'default'              => 'middle',
			'toggle'               => false,
			'selectors_dictionary' => [
				'top'    => 'flex-start',
				'middle' => 'center',
				'bottom' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .video-button' => 'align-items: {{VALUE}}',
			],
			'condition'            => $button_alignment_conditions,
		] );

		$this->add_responsive_control( 'poster_button_offset', [
			'label'      => __( 'Offset', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .video-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .video-button'       => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
			'condition'  => $button_alignment_conditions,
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'button_box_shadow',
			'selector' => '{{WRAPPER}} .video-play',
		] );

		$this->add_responsive_control( 'button_size', [
			'label'     => __( 'Width', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 50,
					'max' => 200,
				],
			],
			'default'   => [
				'unit' => 'px',
			],
			'selectors' => [
				'{{WRAPPER}} .tm-popup-video-icon-play .video-play-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .tm-popup-video-text-play .video-play-text' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .tm-popup-video-image-play .video-play img' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'video_button_radius', [
			'label'      => __( 'Rounded', 'minimog' ),
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
				'{{WRAPPER}} .video-play' => 'border-radius: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->add_responsive_control( 'button_border_size', [
			'label'     => __( 'Border Width', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 1,
					'max' => 20,
				],
			],
			'default'   => [
				'unit' => 'px',
			],
			'selectors' => [
				'{{WRAPPER}} .video-play-icon' => 'border-width: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .video-play-text' => 'border-width: {{SIZE}}{{UNIT}};',
			],
			'condition' => [
				'button_type!' => 'image',
			],
		] );

		$this->add_responsive_control( 'button_icon_size', [
			'label'     => __( 'Icon Size', 'minimog' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default'   => [
				'unit' => 'px',
			],
			'selectors' => [
				'{{WRAPPER}} .icon:before' => 'border-top-width: {{SIZE}}{{UNIT}}; border-bottom-width: {{SIZE}}{{UNIT}}; border-left-width: calc( {{SIZE}}{{UNIT}} * 3 / 2 );',
			],
			'condition' => [
				'button_type' => '',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'video_play_text_typography',
			'selector'  => '{{WRAPPER}} .video-play-text',
			'condition' => [
				'button_type' => 'text',
			],
		] );

		$this->start_controls_tabs( 'button_style_tabs', [
			'condition' => [
				'button_type!' => 'image',
			],
		] );

		$this->start_controls_tab( 'button_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'button_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .icon:before'     => 'border-left-color: {{VALUE}};',
				'{{WRAPPER}} .video-play-text' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-play' => 'background: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-play' => 'border-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_wave_color', [
			'label'     => __( 'Wave Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}}.minimog-popup-video--button-effect-wave-pulse .video-button-mask' => 'color: {{VALUE}};',
			],
			'condition' => [
				'button_type!'  => 'image',
				'button_effect' => 'wave-pulse',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'button_hover_text_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-link:hover .icon:before'     => 'border-left-color: {{VALUE}};',
				'{{WRAPPER}} .video-link:hover .video-play-text' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_hover_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-link:hover .video-play' => 'background: {{VALUE}};',
			],
		] );

		$this->add_control( 'button_hover_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .video-link:hover .video-play' => 'border-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'tm-popup-video' );
		$this->add_render_attribute( 'wrapper', 'class', 'type-' . $settings['type'] );

		if ( ! empty( $settings['button_type'] ) && 'image' === $settings['button_type'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'tm-popup-video-image-play' );
		} elseif ( 'text' === $settings['button_type'] && $settings['button_text'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'tm-popup-video-text-play' );
		} else {
			$this->add_render_attribute( 'wrapper', 'class', 'tm-popup-video-icon-play' );
		}

		$this->add_render_attribute( 'link', 'class', 'video-link minimog-box link-secret' );

		if ( 'hosted' === $settings['video_type'] ) {
			$html5_video = uniqid( 'hosted-video-' );
			$video_url   = $settings['hosted_url']['url'];
			$this->add_render_attribute( 'link', 'data-html', '#' . $html5_video );

			$this->add_render_attribute( 'video-inline', [
				'id'    => $html5_video,
				'style' => 'display:none;',
			] );
		} else {
			$video_url = $settings['video_url'];
			$this->add_render_attribute( 'link', 'href', esc_url( $video_url ) );
		}

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<a <?php $this->print_render_attribute_string( 'link' ); ?>>
				<?php if ( 'button' === $settings['type'] ) : ?>
					<?php $this->print_video_button( $settings ); ?>
				<?php else: ?>
					<?php $this->print_video_poster( $settings ); ?>
				<?php endif; ?>
			</a>

			<?php if ( 'hosted' === $settings['video_type'] ) : ?>
				<div <?php $this->print_render_attribute_string( 'video-inline' ) ?>>
					<video class="lg-video-object lg-html5 video-js vjs-default-skin" controls preload="none"
					       src="<?php echo esc_url( $video_url ); ?>"></video>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	private function print_video_poster( array $settings ) {
		?>
		<div class="video-poster">
			<div class="minimog-image">
				<?php echo \Minimog_Image::get_elementor_attachment( [
					'settings'  => $settings,
					'image_key' => 'poster',
				] ); ?>
			</div>

			<div class="video-overlay"></div>

			<?php $this->print_video_button( $settings ); ?>
		</div>

		<?php if ( ! empty( $settings['poster_caption'] ) ) : ?>
			<div class="video-poster-caption">
				<?php echo esc_html( $settings['poster_caption'] ); ?>
			</div>
		<?php endif; ?>
		<?php
	}

	private function print_video_button( array $settings ) {
		?>
		<div class="video-button">
			<?php if ( 'image' === $settings['button_type'] ) { ?>
				<?php $this->print_button_image( $settings ); ?>
			<?php } elseif ( 'text' === $settings['button_type'] && $settings['button_text'] ) { ?>
				<div class="video-play video-play-text">
					<span class="text"><?php echo esc_html( $settings['button_text'] ) ?></span>
				</div>
			<?php } else { ?>
				<div class="video-button-play">
					<?php $this->print_button_mask_effect(); ?>
					<div class="video-play video-play-icon">
						<span class="icon"></span>
					</div>
				</div>
			<?php } ?>

			<?php if ( ! empty( $settings['video_text'] ) ) : ?>
				<div class="video-text"><?php echo esc_html( $settings['video_text'] ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	private function print_button_image( array $settings ) {
		if ( empty( $settings['button_image']['url'] ) ) {
			return;
		}
		?>
		<div class="video-play video-play-image">
			<?php echo \Minimog_Image::get_elementor_attachment( [
				'settings'   => $settings,
				'image_key'  => 'button_image',
				'attributes' => [
					'alt' => __( 'Play Icon', 'minimog' ),
				],
			] ); ?>
		</div>
		<?php
	}

	private function get_default_play_icon() {
		$icon_url = MINIMOG_ELEMENTOR_ASSETS . '/images/video-play-light.png';

		return $icon_url;
	}

	private function print_button_mask_effect() {
		$settings = $this->get_settings_for_display();

		$effect = ! empty( $settings['button_effect'] ) ? $settings['button_effect'] : '';

		switch ( $effect ) {
			case 'wave-pulse':
				?>
				<div class="video-button-mask video-mask-wave-pulse"></div>
				<?php
				break;
		}
	}
}
