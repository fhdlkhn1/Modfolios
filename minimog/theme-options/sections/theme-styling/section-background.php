<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Site Background', 'minimog' ),
	'id'         => 'site_background',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'body_background',
			'title'   => __( 'Body Background', 'minimog' ),
			'type'    => 'background',
			'default' => array(
				'background-color' => '#fff',
			),
		),
		array(
			'id'      => 'page_blocks_style',
			'type'    => 'select',
			'title'   => __( 'Page Blocks Style', 'minimog' ),
			'options' => [
				'normal'          => __( 'Normal', 'minimog' ),
				'border-block'    => __( 'Border Block', 'minimog' ),
				'border-block-02' => __( 'Border Block 02', 'minimog' ),
				'border-block-03' => __( 'Border Block 03', 'minimog' ),
			],
			'default' => Minimog_Redux::get_default_setting( 'page_blocks_style' ),
		),
	),
) );
