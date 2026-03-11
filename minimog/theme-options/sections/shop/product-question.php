<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Product Question', 'minimog' ),
	'id'         => 'product_question',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'single_product_question_enable',
			'type'    => 'button_set',
			'title'   => __( 'Enable', 'minimog' ),
			'options' => array(
				'0' => __( 'No', 'minimog' ),
				'1' => __( 'Yes', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_product_question_enable' ),
		),
		array(
			'id'       => 'product_ask_question_role',
			'type'     => 'select',
			'title'    => __( 'Who can ask question?', 'minimog' ),
			'options'  => [
				'all'             => __( 'Everyone', 'minimog' ),
				'logged_in_users' => __( 'Logged in users', 'minimog' ),
			],
			'default'  => Minimog_Redux::get_default_setting( 'product_ask_question_role' ),
			'required' => array(
				[ 'single_product_question_enable', '=', '1' ],
			),
		),
		array(
			'id'       => 'product_reply_question_role',
			'type'     => 'select',
			'title'    => __( 'Who can reply question?', 'minimog' ),
			'options'  => [
				'all'             => __( 'Everyone', 'minimog' ),
				'logged_in_users' => __( 'Logged in users', 'minimog' ),
				'administrators'           => __( 'Only Administrators', 'minimog' ),
			],
			'default'  => Minimog_Redux::get_default_setting( 'product_reply_question_role' ),
			'required' => array(
				[ 'single_product_question_enable', '=', '1' ],
			),
		),
	),
) );
