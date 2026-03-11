<?php

namespace Minimog_Elementor;

use Elementor\Group_Control_Base;
use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Elementor tooltip control.
 *
 * A base control for creating tooltip control.
 *
 * @since 1.0.0
 */
class Group_Control_Tooltip extends Group_Control_Base {

	protected static $fields;

	public static function get_type() {
		return 'tooltip';
	}

	protected function init_fields() {
		$fields = [];

		$fields['skin'] = [
			'label'   => __( 'Tooltip Skin', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''        => __( 'Black', 'minimog' ),
				'white'   => __( 'White', 'minimog' ),
				'primary' => __( 'Primary', 'minimog' ),
			],
			'default' => '',
		];

		$fields['position'] = [
			'label'   => __( 'Tooltip Position', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'top'          => __( 'Top', 'minimog' ),
				'right'        => __( 'Right', 'minimog' ),
				'bottom'       => __( 'Bottom', 'minimog' ),
				'left'         => __( 'Left', 'minimog' ),
				'top-left'     => __( 'Top Left', 'minimog' ),
				'top-right'    => __( 'Top Right', 'minimog' ),
				'bottom-left'  => __( 'Bottom Left', 'minimog' ),
				'bottom-right' => __( 'Bottom Right', 'minimog' ),
			],
			'default' => 'top',
		];

		return $fields;
	}

	protected function get_default_options() {
		return [
			'popover' => [
				'starter_title' => _x( 'Tooltip', 'Tooltip Control', 'minimog' ),
				'starter_name'  => 'enable',
				'starter_value' => 'yes',
				'settings'      => [
					'render_type' => 'template',
				],
			],
		];
	}
}
