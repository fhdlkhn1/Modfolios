<?php

Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'  => __( 'Login/Register Popup', 'minimog' ),
	'id'     => 'panel_login_popup',
	'icon'   => 'eicon-user-circle-o',
	'fields' => array(
		array(
			'id'      => 'login_popup_enable',
			'type'    => 'switch',
			'title'   => __( 'Show Popup', 'minimog' ),
			'default' => Minimog_Redux::get_default_setting( 'login_popup_enable' ),
			'on'      => __( 'Yes', 'minimog' ),
			'off'     => __( 'No', 'minimog' ),
		),
		array(
			'id'       => 'section_start_form_login',
			'type'     => 'tm_heading',
			'title'    => __( 'Login Form', 'minimog' ),
			'indent'   => true,
			'collapse' => 'show',
		),
		array(
			'id'      => 'login_redirect',
			'type'    => 'select',
			'title'   => __( 'Login Redirect', 'minimog' ),
			'options' => array(
				'current'   => __( 'Current Page', 'minimog' ),
				'home'      => __( 'Home', 'minimog' ),
				'dashboard' => __( 'My Account', 'minimog' ),
				'custom'    => __( 'Custom', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'login_redirect' ),
		),
		array(
			'id'       => 'custom_login_redirect',
			'type'     => 'text',
			'title'    => __( 'Custom Url', 'minimog' ),
			'default'  => '',
			'required' => array( 'login_redirect', '=', 'custom' ),
		),
		array(
			'id'       => 'section_start_form_register',
			'type'     => 'tm_heading',
			'title'    => __( 'Register Form', 'minimog' ),
			'indent'   => true,
			'collapse' => 'show',
		),
		array(
			'id'      => 'page_for_terms_and_conditions',
			'type'    => 'select',
			'title'   => __( 'Terms and conditions', 'minimog' ),
			'options' => Minimog_Helper::get_all_pages(),
			'default' => Minimog_Redux::get_default_setting( 'page_for_terms_and_conditions' ),
		),
		array(
			'id'          => 'register_form_acceptance_text',
			'type'        => 'textarea',
			'title'       => __( 'Acceptance text', 'minimog' ),
			'default'     => Minimog_Redux::get_default_setting( 'register_form_acceptance_text' ),
			'description' => '{privacy} will replace with Privacy Policy page link. <a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">Select page</a><br/> {terms} will replace with Terms of Conditions page link.',
		),
	),
) );
