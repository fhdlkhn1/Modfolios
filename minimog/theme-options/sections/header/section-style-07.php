<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => 'Header Style 07',
	'id'         => 'header_style_07',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'header_style_07_content_width',
			'type'    => 'select',
			'title'   => __( 'Content Width', 'minimog' ),
			'default' => Minimog_Site_Layout::CONTAINER_WIDE,
			'options' => Minimog_Site_Layout::instance()->get_container_wide_list(),
		),
		array(
			'id'      => 'header_style_07_header_above_enable',
			'type'    => 'button_set',
			'title'   => __( 'Header Above', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_info_list_enable',
			'type'    => 'button_set',
			'title'   => __( 'Info List', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_info_list_secondary_enable',
			'type'    => 'button_set',
			'title'   => __( 'Info List (Secondary)', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '0',
		),
		array(
			'id'      => 'header_style_07_search_enable',
			'type'    => 'button_set',
			'title'   => __( 'Search', 'minimog' ),
			'options' => array(
				'0'      => __( 'Hide', 'minimog' ),
				'inline' => __( 'Inline Form', 'minimog' ),
				'popup'  => __( 'Popup Search', 'minimog' ),
			),
			'default' => 'popup',
		),
		array(
			'id'      => 'header_style_07_login_enable',
			'type'    => 'button_set',
			'title'   => __( 'Login', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_wishlist_enable',
			'type'    => 'button_set',
			'title'   => __( 'Wishlist', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_cart_enable',
			'type'    => 'button_set',
			'title'   => __( 'Cart', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_currency_switcher_enable',
			'type'    => 'button_set',
			'title'   => __( 'Currency Switcher', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_language_switcher_enable',
			'type'    => 'button_set',
			'title'   => __( 'Language Switcher', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_social_networks_enable',
			'type'    => 'button_set',
			'title'   => __( 'Social Networks', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '0',
		),
		array(
			'id'      => 'header_style_07_text_enable',
			'type'    => 'button_set',
			'title'   => __( 'Text', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '1',
		),
		array(
			'id'      => 'header_style_07_button_enable',
			'type'    => 'button_set',
			'title'   => __( 'Button', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => '0',
		),
	),
) );
