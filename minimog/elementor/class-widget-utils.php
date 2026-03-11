<?php

namespace Minimog_Elementor;

defined( 'ABSPATH' ) || exit;

class Widget_Utils {

	public static function get_control_options_horizontal_alignment() {
		return [
			'left'   => [
				'title' => __( 'Left', 'minimog' ),
				'icon'  => 'eicon-h-align-left',
			],
			'center' => [
				'title' => __( 'Center', 'minimog' ),
				'icon'  => 'eicon-h-align-center',
			],
			'right'  => [
				'title' => __( 'Right', 'minimog' ),
				'icon'  => 'eicon-h-align-right',
			],
		];
	}

	public static function get_control_options_horizontal_alignment_full() {
		return [
			'left'    => [
				'title' => __( 'Left', 'minimog' ),
				'icon'  => 'eicon-h-align-left',
			],
			'center'  => [
				'title' => __( 'Center', 'minimog' ),
				'icon'  => 'eicon-h-align-center',
			],
			'right'   => [
				'title' => __( 'Right', 'minimog' ),
				'icon'  => 'eicon-h-align-right',
			],
			'stretch' => [
				'title' => __( 'Stretch', 'minimog' ),
				'icon'  => 'eicon-h-align-stretch',
			],
		];
	}

	public static function get_control_options_vertical_alignment() {
		return [
			'top'    => [
				'title' => __( 'Top', 'minimog' ),
				'icon'  => 'eicon-v-align-top',
			],
			'middle' => [
				'title' => __( 'Middle', 'minimog' ),
				'icon'  => 'eicon-v-align-middle',
			],
			'bottom' => [
				'title' => __( 'Bottom', 'minimog' ),
				'icon'  => 'eicon-v-align-bottom',
			],
		];
	}

	public static function get_control_options_vertical_alignment_full() {
		return [
			'top'     => [
				'title' => __( 'Top', 'minimog' ),
				'icon'  => 'eicon-v-align-top',
			],
			'middle'  => [
				'title' => __( 'Middle', 'minimog' ),
				'icon'  => 'eicon-v-align-middle',
			],
			'bottom'  => [
				'title' => __( 'Bottom', 'minimog' ),
				'icon'  => 'eicon-v-align-bottom',
			],
			'stretch' => [
				'title' => __( 'Stretch', 'minimog' ),
				'icon'  => 'eicon-v-align-stretch',
			],
		];
	}

	public static function get_control_options_text_align() {
		return [
			'left'   => [
				'title' => __( 'Left', 'minimog' ),
				'icon'  => 'eicon-text-align-left',
			],
			'center' => [
				'title' => __( 'Center', 'minimog' ),
				'icon'  => 'eicon-text-align-center',
			],
			'right'  => [
				'title' => __( 'Right', 'minimog' ),
				'icon'  => 'eicon-text-align-right',
			],
		];
	}

	public static function get_control_options_text_align_full() {
		return [
			'left'    => [
				'title' => __( 'Left', 'minimog' ),
				'icon'  => 'eicon-text-align-left',
			],
			'center'  => [
				'title' => __( 'Center', 'minimog' ),
				'icon'  => 'eicon-text-align-center',
			],
			'right'   => [
				'title' => __( 'Right', 'minimog' ),
				'icon'  => 'eicon-text-align-right',
			],
			'justify' => [
				'title' => __( 'Justified', 'minimog' ),
				'icon'  => 'eicon-text-align-justify',
			],
		];
	}

	/**
	 * Get recommended social icons for control ICONS.
	 *
	 * @return array
	 */
	public static function get_recommended_social_icons() {
		return [
			'fa-brands' => [
				'android',
				'apple',
				'behance',
				'bitbucket',
				'codepen',
				'delicious',
				'deviantart',
				'digg',
				'dribbble',
				'envelope',
				'facebook',
				"facebook-f",
				"facebook-messenger",
				"facebook-square",
				'flickr',
				'foursquare',
				'free-code-camp',
				'github',
				'gitlab',
				'globe',
				'houzz',
				'instagram',
				'jsfiddle',
				'link',
				'linkedin',
				'medium',
				'meetup',
				'mix',
				'mixcloud',
				'odnoklassniki',
				'pinterest',
				'product-hunt',
				'reddit',
				'rss',
				'shopping-cart',
				'skype',
				'slideshare',
				'snapchat',
				'soundcloud',
				'spotify',
				'stack-overflow',
				'steam',
				'telegram',
				'thumb-tack',
				'tripadvisor',
				'tumblr',
				'twitch',
				'twitter',
				'viber',
				'vimeo',
				'vk',
				'weibo',
				'weixin',
				'whatsapp',
				'wordpress',
				'xing',
				'yelp',
				'youtube',
				'500px',
			],
		];
	}

	public static function get_grid_metro_size() {
		return [
			'1:1'   => __( 'Width 1 - Height 1', 'minimog' ),
			'1:2'   => __( 'Width 1 - Height 2', 'minimog' ),
			'1:0.7' => __( 'Width 1 - Height 70%', 'minimog' ),
			'1:1.3' => __( 'Width 1 - Height 130%', 'minimog' ),
			'2:1'   => __( 'Width 2 - Height 1', 'minimog' ),
			'2:2'   => __( 'Width 2 - Height 2', 'minimog' ),
		];
	}
}
