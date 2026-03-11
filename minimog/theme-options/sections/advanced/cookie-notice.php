<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Cookie Notice', 'minimog' ),
	'id'         => 'cookie_notice',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'notice_cookie_enable',
			'type'    => 'button_set',
			'title'   => __( 'Cookie Notice', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'notice_cookie_enable' ),
		),
		array(
			'id'          => 'notice_cookie_messages',
			'type'        => 'textarea',
			'title'       => __( 'Messages', 'minimog' ),
			'description' => __( 'Enter the messages that displays for cookie notice.', 'minimog' ),
			'default'     => __( 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'minimog' ),
		),
		array(
			'id'      => 'notice_cookie_button_text',
			'type'    => 'text',
			'title'   => __( 'Button Text', 'minimog' ),
			'default' => __( 'Ok, got it!', 'minimog' ),
		),
	),
) );
