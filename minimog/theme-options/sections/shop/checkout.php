<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Checkout Page', 'minimog' ),
	'id'         => 'checkout_page',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'checkout_page_modal_customer_notes_enable',
			'type'    => 'button_set',
			'title'   => __( 'Customer Notes Modal', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'checkout_page_modal_customer_notes_enable' ),
		),
	),
) );
