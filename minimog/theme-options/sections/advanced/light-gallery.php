<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Light Gallery', 'minimog' ),
	'id'         => 'light_gallery',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'light_gallery_auto_play',
			'type'    => 'switch',
			'title'   => __( 'Auto Play', 'minimog' ),
			'default' => false,
		),
		array(
			'id'      => 'light_gallery_download',
			'type'    => 'switch',
			'title'   => __( 'Download', 'minimog' ),
			'default' => true,
		),
		array(
			'id'      => 'light_gallery_full_screen',
			'type'    => 'switch',
			'title'   => __( 'Full Screen Button', 'minimog' ),
			'default' => true,
		),
		array(
			'id'      => 'light_gallery_share',
			'type'    => 'switch',
			'title'   => __( 'Share Button', 'minimog' ),
			'default' => true,
		),
		array(
			'id'      => 'light_gallery_zoom',
			'type'    => 'switch',
			'title'   => __( 'Zoom Buttons', 'minimog' ),
			'default' => true,
		),
		array(
			'id'      => 'light_gallery_thumbnail',
			'type'    => 'switch',
			'title'   => __( 'Thumbnail Gallery', 'minimog' ),
			'default' => false,
		),
	),
) );
