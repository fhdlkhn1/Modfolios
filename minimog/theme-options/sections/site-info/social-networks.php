<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Social Networks', 'minimog' ),
	'id'         => 'section_social_networks',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'social_link_target',
			'type'    => 'button_set',
			'title'   => __( 'Open link in a new tab.', 'minimog' ),
			'options' => array(
				'0' => __( 'No', 'minimog' ),
				'1' => __( 'Yes', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'social_link_target' ),
		),
		array(
			'id'           => 'social_link',
			'type'         => 'repeater',
			'title'        => __( 'Social Networks', 'minimog' ),
			'item_name'    => __( 'Item', 'minimog' ),
			'bind_title'   => 'tooltip',
			'group_values' => true,
			'fields'       => array(
				array(
					'id'          => 'tooltip',
					'type'        => 'text',
					'title'       => __( 'Tooltip', 'minimog' ),
					'description' => __( 'Enter your hint text for your icon', 'minimog' ),
				),
				array(
					'id'    => 'link_url',
					'type'  => 'text',
					'title' => __( 'Link Url', 'minimog' ),
				),
				array(
					'id'    => 'icon_class',
					'title' => __( 'Icon CSS class', 'minimog' ),
					'type'  => 'text',
				),
			),
			'default'      => [
				'Redux_repeater_data' => [
					[ 'title' => '' ],
					[ 'title' => '' ],
					[ 'title' => '' ],
					[ 'title' => '' ],
				],
				'tooltip'             => [
					'Twitter',
					'Facebook',
					'Instagram',
					'Linkedin',
				],
				'link_url'            => [
					'https://twitter.com',
					'https://facebook.com',
					'https://instagram.com',
					'https://linkedin.com',
				],
				'icon_class'          => [
					'fab fa-twitter',
					'fab fa-facebook',
					'fab fa-instagram',
					'fab fa-linkedin',
				],
			],
		),
	),
) );
