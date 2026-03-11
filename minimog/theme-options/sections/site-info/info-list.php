<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Info List', 'minimog' ),
	'id'         => 'section_info_list',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'           => 'info_list',
			'type'         => 'repeater',
			'title'        => __( 'Info List', 'minimog' ),
			'item_name'    => __( 'Item', 'minimog' ),
			'bind_title'   => 'text',
			'group_values' => true,
			'fields'       => array(
				array(
					'id'    => 'text',
					'type'  => 'textarea',
					'title' => __( 'Text', 'minimog' ),
				),
				array(
					'id'    => 'url',
					'type'  => 'text',
					'title' => __( 'Link Url', 'minimog' ),
				),
				array(
					'id'    => 'icon_class',
					'title' => __( 'Icon CSS class', 'minimog' ),
					'type'  => 'text',
				),
				array(
					'id'          => 'icon',
					'title'       => 'Icon',
					'type'        => 'media',
					'description' => 'Select an image or a svg file.',
					'compiler'    => 'true',
					'mode'        => false,
				),
				array(
					'id'          => 'item_class',
					'title'       => __( 'Item CSS class', 'minimog' ),
					'description' => 'Add custom CSS class to the item',
					'type'        => 'text',
				),
			),
			'default'      => [
				'Redux_repeater_data' => [
					[
						'title' => '',
					],
					[
						'title' => '',
					],
				],
				'text'                => [
					'100k Followers',
					'300k Followers',
				],
				'url'                 => [
					'https://instagram.com',
					'https://facebook.com',
				],
				'icon_class'          => [
					'fab fa-instagram',
					'fab fa-facebook',
				],
				'icon'                => [
					[
						'id'  => '',
						'url' => '',
					],
					[
						'id'  => '',
						'url' => '',
					],
				],
				'item_class'          => [
					'',
					'',
				],
			],
		),
	),
) );
