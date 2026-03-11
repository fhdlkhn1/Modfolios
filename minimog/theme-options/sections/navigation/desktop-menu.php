<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Desktop Menu', 'minimog' ),
	'id'         => 'navigation_desktop_menu',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'section_navigation_dropdown_hr',
			'type'    => 'tm_heading',
			'title'   => 'Dropdown menu',
			'indent'  => true,
			'collapse' => 'show',
		),
		array(
			'id'          => 'navigation_dropdown_bg_color',
			'type'        => 'color',
			'title'       => __( 'Background', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'       => 'navigation_dropdown_box_shadow',
			'type'     => 'box_shadow',
			'title'    => __( 'Box Shadow', 'minimog' ),
			'subtitle' => __( 'Input box shadow for dropdown menu', 'minimog' ),
			'default'  => [
				'inset'        => false,
				'drop'         => false,
				'inset-shadow' => array(
					'checked'    => false,
					'color'      => '#ABABAB',
					'horizontal' => 0,
					'vertical'   => 0,
					'blur'       => 10,
					'spread'     => 0,
				),
				'drop-shadow'  => array(
					'checked'    => true,
					'color'      => 'rgba(0, 0, 0, 0.06)',
					'horizontal' => 0,
					'vertical'   => 0,
					'blur'       => 30,
					'spread'     => 0,
				),
			],
		),
		array(
			'id'          => 'navigation_dropdown_link_color',
			'type'        => 'color',
			'title'       => __( 'Link Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'navigation_dropdown_link_hover_color',
			'type'        => 'color',
			'title'       => __( 'Link Hover Color', 'minimog' ),
			'color_alpha' => true,
		),
		array(
			'id'          => 'navigation_dropdown_link_hover_bg_color',
			'type'        => 'color',
			'title'       => __( 'Link Hover Background Color', 'minimog' ),
			'color_alpha' => true,
		),
	),
) );
