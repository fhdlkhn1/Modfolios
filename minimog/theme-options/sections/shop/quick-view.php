<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Quick View', 'minimog' ),
	'id'         => 'quick_view',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'shop_quick_view_enable',
			'type'    => 'button_set',
			'title'   => __( 'Quick View', 'minimog' ),
			'options' => array(
				'0' => __( 'Disable', 'minimog' ),
				'1' => __( 'Enable', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'shop_quick_view_enable' ),
		),
		array(
			'id'      => 'shop_quick_view_light_gallery_enable',
			'type'    => 'button_set',
			'title'   => 'Light Gallery Enable',
			'desc'    => 'Show popup image/video when click on product images.',
			'options' => array(
				'0' => __( 'Disable', 'minimog' ),
				'1' => __( 'Enable', 'minimog' ),
			),
			'default' => '0',
		),
		array(
			'id'      => 'shop_quick_view_product_description',
			'type'    => 'button_set',
			'title'   => __( 'Product Description', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'shop_quick_view_product_description' ),
		),
		array(
			'id'      => 'shop_quick_view_product_badges',
			'type'    => 'button_set',
			'title'   => __( 'Product Badges', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'shop_quick_view_product_badges' ),
		),
		array(
			'id'      => 'shop_quick_view_product_meta',
			'type'    => 'button_set',
			'title'   => __( 'Product Meta', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'shop_quick_view_product_meta' ),
		),
	),
) );
