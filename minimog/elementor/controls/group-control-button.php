<?php

namespace Minimog_Elementor;

use Elementor\Group_Control_Base;
use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Elementor advanced border control.
 *
 * A base control for creating border control. Displays input fields to define
 * border type, border width and border color.
 *
 * @since 1.0.0
 */
class Group_Control_Button extends Group_Control_Base {

	protected static $fields;

	public static function get_type() {
		return 'button';
	}

	protected function init_fields() {
		$fields = [];

		$fields['heading'] = [
			'label'     => __( 'Button', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		];

		$fields['style'] = [
			'label'   => __( 'Style', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'flat',
			'options' => \Minimog_Helper::get_button_style_options(),
		];

		$fields['text'] = [
			'label'   => __( 'Text', 'minimog' ),
			'type'    => Controls_Manager::TEXT,
			'dynamic' => [
				'active' => true,
			],
		];

		$fields['link'] = [
			'label'       => __( 'Link', 'minimog' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => [
				'active' => true,
			],
			'placeholder' => __( 'https://your-link.com', 'minimog' ),
			'default'     => [
				'url' => '#',
			],
		];

		$fields['size'] = [
			'label'   => __( 'Size', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'nm',
			'options' => [
				'xs' => __( 'Extra Small', 'minimog' ),
				'sm' => __( 'Small', 'minimog' ),
				'nm' => __( 'Normal', 'minimog' ),
				'lg' => __( 'Large', 'minimog' ),
			],
		];

		$fields['icon'] = [
			'label'       => __( 'Icon', 'minimog' ),
			'type'        => Controls_Manager::ICONS,
			'label_block' => true,
		];

		$fields['icon_align'] = [
			'label'       => __( 'Icon Position', 'minimog' ),
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
			'condition'   => [
				'icon[value]!' => '',
			],
		];

		$fields['icon_hover'] = [
			'label'        => __( 'Icon Hover Effect', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'default'      => '',
			'options'      => [
				''                 => __( 'None', 'minimog' ),
				'fade'             => __( 'Fade', 'minimog' ),
				'slide-from-left'  => __( 'Slide From Left', 'minimog' ),
				'slide-from-right' => __( 'Slide From Right', 'minimog' ),
			],
			'prefix_class' => 'minimog-button-icon-animation--',
		];

		return $fields;
	}

	protected function get_default_options() {
		return [
			'popover' => false,
		];
	}
}
