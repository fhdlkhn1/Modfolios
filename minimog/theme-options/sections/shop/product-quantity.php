<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Product Quantity', 'minimog' ),
	'id'         => 'product_quantity',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'product_quantity_type',
			'type'    => 'button_set',
			'title'   => __( 'Type', 'minimog' ),
			'options' => array(
				'input'  => __( 'Input (Default)', 'minimog' ),
				'select' => __( 'Select', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'product_quantity_type' ),
		),
		array(
			'id'          => 'product_quantity_ranges',
			'type'        => 'textarea',
			'title'       => __( 'Values', 'minimog' ),
			'description' => __( 'These values will be used for select type. Enter each value in one line and can use the range e.g "1-5".', 'minimog' ),
		),
	),
) );
