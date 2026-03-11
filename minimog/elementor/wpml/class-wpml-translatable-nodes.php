<?php

namespace Minimog_Elementor;

defined( 'ABSPATH' ) || exit;

class WPML_Translatable_Nodes {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function initialize() {
		add_action( 'init', [ $this, 'wp_init' ] );
	}

	public function wp_init() {
		add_filter( 'wpml_elementor_widgets_to_translate', [ $this, 'wpml_widgets_to_translate_filter' ] );
	}

	public function get_translatable_node() {
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-google-map.php' );
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-list.php' );
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-attribute-list.php' );
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-pricing-table.php' );
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-table.php' );
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-modern-slider.php' );
		minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/wpml/class-translate-widget-testimonial-carousel.php' );

		$widgets['tm-attribute-list'] = [
			'fields'            => [],
			'integration-class' => '\Minimog_Elementor\Translate_Widget_Attribute_List',
		];

		$widgets['tm-heading'] = [
			'fields' => [
				[
					'field'       => 'title',
					'type'        => __( 'Modern Heading: Primary', 'minimog' ),
					'editor_type' => 'AREA',
				],
				'title_link' => [
					'field'       => 'url',
					'type'        => __( 'Modern Heading: Link', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description',
					'type'        => __( 'Modern Heading: Description', 'minimog' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'sub_title_text',
					'type'        => __( 'Modern Heading: Secondary', 'minimog' ),
					'editor_type' => 'AREA',
				],
			],
		];

		$widgets['tm-button'] = [
			'fields' => [
				[
					'field'       => 'text',
					'type'        => __( 'Button: Text', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'badge_text',
					'type'        => __( 'Button: Badge', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'link' => [
					'field'       => 'url',
					'type'        => __( 'Button: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets['tm-banner'] = [
			'fields' => [
				[
					'field'       => 'title_text',
					'type'        => __( 'Banner: Title', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'link' => [
					'field'       => 'url',
					'type'        => __( 'Banner: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets['tm-flip-box'] = [
			'fields' => [
				[
					'field'       => 'title_text_a',
					'type'        => __( 'Flip Box: Front Title', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text_a',
					'type'        => __( 'Flip Box: Front Description', 'minimog' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'title_text_b',
					'type'        => __( 'Flip Box: Back Title', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text_b',
					'type'        => __( 'Flip Box: Back Description', 'minimog' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'button_text',
					'type'        => __( 'Flip Box: Button Text', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'link' => [
					'field'       => 'url',
					'type'        => __( 'Flip Box: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets['tm-google-map'] = [
			'fields'            => [],
			'integration-class' => '\Minimog_Elementor\Translate_Widget_Google_Map',
		];

		$widgets['tm-icon'] = [
			'fields' => [
				'link' => [
					'field'       => 'url',
					'type'        => __( 'Icon: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets['tm-icon-box'] = [
			'fields' => [
				[
					'field'       => 'title_text',
					'type'        => __( 'Icon Box: Title', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text',
					'type'        => __( 'Icon Box: Description', 'minimog' ),
					'editor_type' => 'AREA',
				],
				'link'        => [
					'field'       => 'url',
					'type'        => __( 'Icon Box: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
				[
					'field'       => 'button_text',
					'type'        => __( 'Icon Box: Button', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'button_link' => [
					'field'       => 'url',
					'type'        => __( 'Icon Box: Button Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets['tm-image-box'] = [
			'fields' => [
				[
					'field'       => 'title_text',
					'type'        => __( 'Image Box: Title', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text',
					'type'        => __( 'Image Box: Content', 'minimog' ),
					'editor_type' => 'AREA',
				],
				'link' => [
					'field'       => 'url',
					'type'        => __( 'Image Box: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
				[
					'field'       => 'button_text',
					'type'        => __( 'Image Box: Button', 'minimog' ),
					'editor_type' => 'LINE',
				],
			],
		];

		$widgets['tm-list'] = [
			'fields'            => [],
			'integration-class' => '\Minimog_Elementor\Translate_Widget_List',
		];

		$widgets['tm-popup-video'] = [
			'fields' => [
				[
					'field'       => 'video_text',
					'type'        => __( 'Popup Video: Text', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'video_url' => [
					'field'       => 'url',
					'type'        => __( 'Popup Video: Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
				[
					'field'       => 'poster_caption',
					'type'        => __( 'Popup Video: Caption', 'minimog' ),
					'editor_type' => 'AREA',
				],
			],
		];

		$widgets['tm-pricing-table'] = [
			'fields'            => [
				[
					'field'       => 'heading',
					'type'        => __( 'Pricing Table: Heading', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'sub_heading',
					'type'        => __( 'Pricing Table: Description', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'currency',
					'type'        => __( 'Pricing Table: Currency', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'price',
					'type'        => __( 'Pricing Table: Price', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'period',
					'type'        => __( 'Pricing Table: Period', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'button_text',
					'type'        => __( 'Pricing Table: Button', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'button_link' => [
					'field'       => 'url',
					'type'        => __( 'Pricing Table: Button Link', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
			'integration-class' => '\Minimog_Elementor\Translate_Widget_Pricing_Table',
		];

		$widgets['tm-table'] = [
			'fields'            => [],
			'integration-class' => [
				'\Minimog_Elementor\Translate_Widget_Pricing_Table_Head',
				'\Minimog_Elementor\Translate_Widget_Pricing_Table_Body',
			],
		];

		$widgets['tm-team-member'] = [
			'fields' => [
				[
					'field'       => 'name',
					'type'        => __( 'Team Member: Name', 'minimog' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'content',
					'type'        => __( 'Team Member: Content', 'minimog' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'position',
					'type'        => __( 'Team Member: Position', 'minimog' ),
					'editor_type' => 'LINE',
				],
				'profile' => [
					'field'       => 'url',
					'type'        => __( 'Team Member: Profile', 'minimog' ),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets['tm-modern-slider'] = [
			'fields'            => [],
			'integration-class' => '\Minimog_Elementor\Translate_Widget_Modern_Slider',
		];

		$widgets['tm-testimonial-carousel'] = [
			'fields'            => [],
			'integration-class' => '\Minimog_Elementor\Translate_Widget_Testimonial_Carousel',
		];

		return $widgets;
	}

	public function wpml_widgets_to_translate_filter( $widgets ) {
		$minimog_widgets = $this->get_translatable_node();

		foreach ( $minimog_widgets as $widget_name => $widget ) {
			$widgets[ $widget_name ]               = $widget;
			$widgets[ $widget_name ]['conditions'] = [
				'widgetType' => $widget_name,
			];
		}

		return $widgets;
	}
}

WPML_Translatable_Nodes::instance()->initialize();
