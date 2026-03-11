<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Modules\DynamicTags\Module as TagsModule;

defined( 'ABSPATH' ) || exit;

class Widget_Typed_Headline extends Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'typed', MINIMOG_THEME_URI . '/assets/libs/typed/typed.min.js', array(), '2.0.11', true );

		wp_register_script( 'minimog-widget-typed-headline', MINIMOG_ELEMENTOR_URI . '/assets/js/widgets/widget-typed-headline.js', array(
			'elementor-frontend',
			'typed',
		), MINIMOG_THEME_VERSION, true );
	}

	public function get_name() {
		return 'tm-typed-headline';
	}

	public function get_title() {
		return __( 'Typed Headline', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-heading';
	}

	public function get_keywords() {
		return [ 'heading', 'title', 'text' ];
	}

	public function get_script_depends() {
		return [ 'minimog-widget-typed-headline' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		$this->add_wrapper_style_section();

		$this->add_headline_style_section();
	}

	private function add_content_section() {
		$this->start_controls_section( 'content_section', [
			'label' => __( 'Content', 'minimog' ),
		] );

		$this->add_control( 'before_text', [
			'label'       => __( 'Before Text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'dynamic'     => [
				'active'     => true,
				'categories' => [
					TagsModule::TEXT_CATEGORY,
				],
			],
			'default'     => __( 'This page is', 'minimog' ),
			'placeholder' => __( 'Enter your headline', 'minimog' ),
			'label_block' => true,
			'separator'   => 'before',
		] );

		$this->add_control( 'text', [
			'label'              => __( 'Text', 'minimog' ),
			'type'               => Controls_Manager::TEXTAREA,
			'placeholder'        => __( 'Enter each word in a separate line', 'minimog' ),
			'separator'          => 'none',
			'default'            => "Better\nBigger\nFaster",
			'frontend_available' => true,
		] );

		$this->add_control( 'after_text', [
			'label'       => __( 'After Text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'dynamic'     => [
				'active'     => true,
				'categories' => [
					TagsModule::TEXT_CATEGORY,
				],
			],
			'placeholder' => __( 'Enter your headline', 'minimog' ),
			'label_block' => true,
			'separator'   => 'none',
		] );

		$this->add_control( 'title_size', [
			'label'   => __( 'HTML Tag', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'h1'   => 'H1',
				'h2'   => 'H2',
				'h3'   => 'H3',
				'h4'   => 'H4',
				'h5'   => 'H5',
				'h6'   => 'H6',
				'div'  => 'div',
				'span' => 'span',
				'p'    => 'p',
			],
			'default' => 'h2',
		] );

		$this->add_control( 'view', [
			'label'   => __( 'View', 'minimog' ),
			'type'    => Controls_Manager::HIDDEN,
			'default' => 'traditional',
		] );

		$this->end_controls_section();
	}

	private function add_wrapper_style_section() {
		$this->start_controls_section( 'wrapper_style_section', [
			'tab'   => Controls_Manager::TAB_STYLE,
			'label' => __( 'Wrapper', 'minimog' ),
		] );

		$this->add_responsive_control( 'align', [
			'label'                => __( 'Text Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_text_align_full(),
			'selectors_dictionary' => [
				'left'  => 'start',
				'right' => 'end',
			],
			'default'              => '',
			'selectors'            => [
				'{{WRAPPER}}' => 'text-align: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'max_width', [
			'label'          => __( 'Max Width', 'minimog' ),
			'type'           => Controls_Manager::SLIDER,
			'default'        => [
				'unit' => 'px',
			],
			'tablet_default' => [
				'unit' => 'px',
			],
			'mobile_default' => [
				'unit' => 'px',
			],
			'size_units'     => [ 'px', '%' ],
			'range'          => [
				'%'  => [
					'min' => 1,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 1600,
				],
			],
			'selectors'      => [
				'{{WRAPPER}} .tm-typed-headline' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'alignment', [
			'label'                => __( 'Alignment', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}}',
			],
		] );

		$this->end_controls_section();
	}

	private function add_headline_style_section() {
		$this->start_controls_section( 'headline_style_section', [
			'label' => __( 'Headline', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'headline',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
			'selector' => '{{WRAPPER}} .minimog-headline',
		] );

		$this->add_group_control( Group_Control_Text_Stroke::get_type(), [
			'name'     => 'text_stroke',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
			'selector' => '{{WRAPPER}} .minimog-headline',
		] );

		$this->add_group_control( Group_Control_Text_Shadow::get_type(), [
			'name'     => 'text_shadow',
			'selector' => '{{WRAPPER}} .minimog-headline',
		] );

		$this->add_control( 'blend_mode', [
			'label'     => __( 'Blend Mode', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => [
				''            => __( 'Normal', 'minimog' ),
				'multiply'    => 'Multiply',
				'screen'      => 'Screen',
				'overlay'     => 'Overlay',
				'darken'      => 'Darken',
				'lighten'     => 'Lighten',
				'color-dodge' => 'Color Dodge',
				'saturation'  => 'Saturation',
				'color'       => 'Color',
				'difference'  => 'Difference',
				'exclusion'   => 'Exclusion',
				'hue'         => 'Hue',
				'luminosity'  => 'Luminosity',
			],
			'selectors' => [
				'{{WRAPPER}} .minimog-headline' => 'mix-blend-mode: {{VALUE}}',
			],
			'separator' => 'none',
		] );

		$this->add_group_control( Group_Control_Text_Gradient::get_type(), [
			'name'     => 'headline',
			'selector' => '{{WRAPPER}} .minimog-headline',
		] );

		$this->add_control( 'animated_text_heading', [
			'label'     => __( 'Animated Text', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'animated_text',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
			'selector' => '{{WRAPPER}} .headline-animate-text',
		] );

		$this->add_group_control( Group_Control_Text_Stroke::get_type(), [
			'name'     => 'animated_text_text_stroke',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
			'selector' => '{{WRAPPER}} .headline-animate-text',
		] );

		$this->add_group_control( Group_Control_Text_Gradient::get_type(), [
			'name'     => 'animated_text',
			'selector' => '{{WRAPPER}} .headline-animate-text',
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'tm-typed-headline' );

		$this->add_render_attribute( 'headline', 'class', 'minimog-headline' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php printf( '<%1$s %2$s>', $settings['title_size'], $this->get_render_attribute_string( 'headline' ) ); ?>
			<?php $this->print_before_text(); ?>
			<?php $this->print_animate_text(); ?>
			<?php $this->print_after_text(); ?>
			<?php printf( '</%1$s>', $settings['title_size'] ); ?>
		</div>
		<?php
	}

	private function print_before_text() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['before_text'] ) ) {
			return;
		}

		$this->add_render_attribute( 'before_text', 'class', 'headline-part headline-before-text' );
		?>
		<span <?php $this->print_render_attribute_string( 'before_text' ) ?>>
			<?php echo esc_html( $settings['before_text'] ); ?>
		</span>
		<?php
	}

	private function print_after_text() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['after_text'] ) ) {
			return;
		}

		$this->add_render_attribute( 'after_text', 'class', 'headline-part headline-after-text' );
		?>
		<span <?php $this->print_render_attribute_string( 'after_text' ) ?>>
			<?php echo esc_html( $settings['after_text'] ); ?>
		</span>
		<?php
	}

	private function print_animate_text() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['text'] ) ) {
			return;
		}

		$words = explode( "\n", str_replace( "\r", "", $settings['text'] ) );

		$this->add_render_attribute( 'animate_text', 'class', 'animate-text' );
		$this->add_render_attribute( 'animate_text', 'data-typed', wp_json_encode( $words ) );
		?>
		<div class="headline-part headline-animate-text">
			<span <?php $this->print_render_attribute_string( 'animate_text' ) ?>><?php echo esc_html( $words['0'] ); ?></span>
		</div>
		<?php
	}
}
