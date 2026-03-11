<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'  => __( 'Pre Loader', 'minimog' ),
	'id'     => 'panel_pre_loader',
	'icon'   => 'eicon-loading',
	'fields' => array(
		array(
			'id'      => 'pre_loader_enable',
			'type'    => 'button_set',
			'title'   => __( 'Pre Loader', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'pre_loader_enable' ),
		),
		array(
			'id'      => 'pre_loader_style',
			'type'    => 'select',
			'title'   => __( 'Style', 'minimog' ),
			'options' => [
				'rotating-plane' => __( 'Rotating Plane', 'minimog' ),
				'circle'         => __( 'Circle', 'minimog' ),
				'gif-image'      => __( 'Gif Image', 'minimog' ),
			],
			'default' => Minimog_Redux::get_default_setting( 'pre_loader_style' ),
		),
		array(
			'id'          => 'pre_loader_background_color',
			'type'        => 'color',
			'title'       => __( 'Background Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'pre_loader_shape_color',
			'type'        => 'color',
			'title'       => __( 'Shape Color', 'minimog' ),
			'color_alpha' => true,
			'required'    => array(
				[ 'pre_loader_style', '!=', 'gif-image' ],
			),
		),
		array(
			'id'       => 'pre_loader_image',
			'type'     => 'media',
			'title'    => __( 'Gif Image', 'minimog' ),
			'default'  => Minimog_Redux::get_default_setting( 'pre_loader_image' ),
			'required' => array(
				[ 'pre_loader_style', '=', 'gif-image' ],
			),
		),
		array(
			'id'             => 'pre_loader_image_width',
			'type'           => 'dimensions',
			'units'          => array( 'em', 'px', '%' ),
			'units_extended' => 'true',
			'title'          => __( 'Image Width', 'minimog' ),
			'default'        => Minimog_Redux::get_default_setting( 'pre_loader_image_width' ),
			'height'         => false,
			'required'       => array(
				[ 'pre_loader_style', '=', 'gif-image' ],
			),
		),
	),
) );
