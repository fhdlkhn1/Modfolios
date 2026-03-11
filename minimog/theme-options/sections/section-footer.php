<?php

Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'  => __( 'Footer', 'minimog' ),
	'id'     => 'panel_footer',
	'icon'   => 'eicon-footer',
	'fields' => array(
		array(
			'id'       => 'footer_copyright_text',
			'type'     => 'editor',
			'title'    => __( 'Copyright Text', 'minimog' ),
			'subtitle' => __( 'Specify the copyright text to show at the bottom of the website', 'minimog' ),
			'default'  => Minimog_Redux::get_default_setting( 'footer_copyright_text' ),
			'args'     => array(
				'textarea_rows'  => 3,
				'default_editor' => 'html',
			),
		),
	),
) );
