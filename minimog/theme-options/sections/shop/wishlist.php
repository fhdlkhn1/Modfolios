<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Wishlist', 'minimog' ),
	'id'         => 'wishlist',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'       => 'wishlist_general_settings',
			'type'     => 'tm_heading',
			'title'    => __( 'General', 'minimog' ),
			'indent'   => true,
			'collapse' => 'show',
		),
		array(
			'id'      => 'wishlist_icon_type',
			'type'    => 'button_set',
			'title'   => __( 'Wishlist Icon', 'minimog' ),
			'options' => [
				'star'  => __( 'Star', 'minimog' ),
				'heart' => __( 'Heart', 'minimog' ),
			],
			'default' => Minimog_Redux::get_default_setting( 'wishlist_icon_type' ),
		),
		array(
			'id'       => 'wishlist_single_product_settings',
			'type'     => 'tm_heading',
			'title'    => __( 'Single Product', 'minimog' ),
			'indent'   => true,
			'collapse' => 'show',
		),
		array(
			'id'      => 'single_product_wishlist_enable',
			'type'    => 'button_set',
			'title'   => __( 'Show button', 'minimog' ),
			'options' => array(
				'0' => __( 'No', 'minimog' ),
				'1' => __( 'Yes', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_product_wishlist_enable' ),
		),
	),
) );
