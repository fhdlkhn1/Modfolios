<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Sticky', 'minimog' ),
	'id'         => 'header_sticky',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'header_sticky_enable',
			'type'    => 'button_set',
			'title'   => __( 'Enable', 'minimog' ),
			'options' => array(
				'0' => __( 'No', 'minimog' ),
				'1' => __( 'Yes', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'header_sticky_enable' ),
		),
		array(
			'id'      => 'header_sticky_type',
			'type'    => 'button_set',
			'title'   => __( 'Type', 'minimog' ),
			'options' => array(
				'both' => __( 'Both', 'minimog' ),
				'up'   => __( 'Up', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'header_sticky_type' ),
		),
		array(
			'id'      => 'header_sticky_logo',
			'type'    => 'button_set',
			'title'   => __( 'Logo Version', 'minimog' ),
			'options' => array(
				'dark'  => __( 'Dark', 'minimog' ),
				'light' => __( 'Light', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'header_sticky_logo' ),
		),
		array(
			'id'      => 'header_sticky_background',
			'type'    => 'background',
			'default' => Minimog_Redux::get_default_setting( 'header_sticky_background' ),
			'title'   => __( 'Background', 'minimog' ),
		),
		array(
			'id'          => 'header_sticky_text_color',
			'type'        => 'color',
			'title'       => __( 'Text Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_link_color',
			'type'        => 'color',
			'title'       => __( 'Link Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_link_hover_color',
			'type'        => 'color',
			'title'       => __( 'Link Hover Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_nav_link_color',
			'type'        => 'color',
			'title'       => __( 'Nav Link Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_nav_link_hover_color',
			'type'        => 'color',
			'title'       => __( 'Nav Link Hover Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_nav_line_color',
			'type'        => 'color',
			'title'       => __( 'Nav Line Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_icon_color',
			'type'        => 'color',
			'title'       => __( 'Icon Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_icon_hover_color',
			'type'        => 'color',
			'title'       => __( 'Icon Hover Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_icon_badge_text_color',
			'type'        => 'color',
			'title'       => __( 'Icon Badge Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'header_sticky_icon_badge_background_color',
			'type'        => 'color',
			'title'       => __( 'Icon Badge Background', 'minimog' ),
			'color_alpha' => true,
		),
	),
) );
