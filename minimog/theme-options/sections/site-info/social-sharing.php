<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Share List', 'minimog' ),
	'id'         => 'section_sharing',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'      => 'social_sharing_item_enable',
			'type'    => 'sortable',
			'mode'    => 'toggle',
			'title'   => __( 'Sharing Links', 'minimog' ),
			'default' => Minimog_Redux::get_default_setting( 'social_sharing_item_enable' ),
			'options' => [
				'facebook'    => __( 'Facebook', 'minimog' ),
				'twitter'     => __( 'Twitter', 'minimog' ),
				'linkedin'    => __( 'Linkedin', 'minimog' ),
				'tumblr'      => __( 'Tumblr', 'minimog' ),
				'email'       => __( 'Email', 'minimog' ),
				'pinterest'   => __( 'Pinterest', 'minimog' ),
				'vk'          => __( 'VK', 'minimog' ),
				'digg'        => __( 'Digg', 'minimog' ),
				'reddit'      => __( 'Reddit', 'minimog' ),
				'stumbleupon' => __( 'StumbleUpon', 'minimog' ),
				'whatsapp'    => __( 'WhatsApp', 'minimog' ),
				'xing'        => __( 'Xing', 'minimog' ),
				'telegram'    => __( 'Telegram', 'minimog' ),
				'skype'       => __( 'Skype', 'minimog' ),
			],
		),
	),
) );
