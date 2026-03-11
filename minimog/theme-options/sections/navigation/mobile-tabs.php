<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Mobile Tabs', 'minimog' ),
	'id'         => 'mobile_tabs',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'mobile_tabs_enable',
			'type'    => 'switch',
			'title'   => __( 'Enable', 'minimog' ),
			'default' => Minimog_Redux::get_default_setting( 'mobile_tabs_enable' ),
		),
		array(
			'id'      => 'mobile_tabs',
			'type'    => 'sortable',
			'mode'    => 'toggle',
			'title'   => __( 'Tab Items', 'minimog' ),
			'options' => [
				'home'     => __( 'Home Link', 'minimog' ),
				'shop'     => __( 'Shop Link', 'minimog' ),
				'login'    => __( 'Login Popup', 'minimog' ),
				'wishlist' => __( 'Wishlist Page', 'minimog' ),
				'cart'     => __( 'Cart Popup', 'minimog' ),
				'search'   => __( 'Search Popup', 'minimog' ),
			],
			'default' => Minimog_Redux::get_default_setting( 'mobile_tabs' ),
		),
		array(
			'id'      => 'mobile_tabs_title_enable',
			'type'    => 'switch',
			'title'   => __( 'Show Title', 'minimog' ),
			'on'      => __( 'Yes', 'minimog' ),
			'off'     => __( 'No', 'minimog' ),
			'default' => Minimog_Redux::get_default_setting( 'mobile_tabs_title_enable' ),
		),
	),
) );
