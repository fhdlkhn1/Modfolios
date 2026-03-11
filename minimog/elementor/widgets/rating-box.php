<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Rating_Box extends Base {

	public function get_name() {
		return 'tm-rating-box';
	}

	public function get_title() {
		return __( 'Rating Box', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-rating';
	}

	public function get_keywords() {
		return [ 'rating box', 'box rating', 'rating', 'box' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		// Style
		$this->add_style_section();

		$this->register_common_button_style_section();
	}

	private function add_content_section() {
		$this->start_controls_section( 'rating_box_section', [
			'label' => __( 'Rating Box', 'minimog' ),
		] );

		$this->add_control( 'rating', [
			'label'   => __( 'Rating', 'minimog' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0,
			'max'     => 5,
			'step'    => 0.1,
			'default' => 5,
		] );

		$this->add_control( 'rating_content', [
			'label'       => __( 'Content', 'minimog' ),
			'type'        => Controls_Manager::TEXTAREA,
			'dynamic'     => [
				'active' => true,
			],
			'default'     => __( '5000+ 5 Star reviews', 'minimog' ),
			'placeholder' => __( 'Enter your rating content', 'minimog' ),
			'description' => __( 'Wrap any words with &lt;mark&gt;&lt;/mark&gt; tag to make them highlight.', 'minimog' ),
		] );

		$this->add_group_control( Group_Control_Button::get_type(), [
			'name'           => 'button',
			// Change button style text as default.
			'fields_options' => [
				'style' => [
					'default' => 'text',
				],
				'text'  => [
					'default' => __( 'Read More', 'minimog' ),
				],
			],
		] );

		$this->add_control( 'view', [
			'label'   => __( 'View', 'minimog' ),
			'type'    => Controls_Manager::HIDDEN,
			'default' => 'traditional',
		] );

		$this->end_controls_section();
	}

	/**
	 * Style Section
	 *
	 * @return void
	 */
	private function add_style_section() {
		$this->start_controls_section( 'rating_box_style_section', [
			'label' => __( 'Rating Box', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'box_max_width', [
			'label'      => __( 'Max Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [
				'unit' => 'px',
			],
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'%'  => [
					'min' => 1,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 1600,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-rating-box__wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'text_align', [
			'label'                => __( 'Text Align', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_text_align(),
			'selectors'            => [
				'{{WRAPPER}} .tm-rating-box' => 'text-align: {{VALUE}};',
			],
			'selectors_dictionary' => [
				'left'  => 'start',
				'right' => 'end',
			],
		] );

		$this->add_responsive_control( 'box_horizontal_alignment', [
			'label'                => __( 'Alignment', 'minimog' ),
			'label_block'          => true,
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => '',
			'toggle'               => false,
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .tm-rating-box' => 'justify-content: {{VALUE}}',
			],
		] );

		// Star Rating
		$this->add_control( 'star_style_heading', [
			'label'     => __( 'Star', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'star_size', [
			'label'      => __( 'Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [],
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-star-rating' => '--size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'star_full_color', [
			'label'     => __( 'Fill', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-star-rating' => '--fill: {{VALUE}}; --half: {{VALUE}};',
			],
		] );

		$this->add_control( 'star_empty_color', [
			'label'     => __( 'Empty', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-star-rating' => '--empty: {{VALUE}};',
			],
		] );

		// Review
		$this->add_control( 'content_style_heading', [
			'label'     => __( 'Content', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'review_typography',
			'selector' => '{{WRAPPER}} .tm-rating-box__content',
		] );

		$this->add_control( 'review_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-rating-box__content' => 'color: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'content_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [],
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 500,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-rating-box__content' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'highlight_heading', [
			'label'     => __( 'Highlight Words', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'title_highlight',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
			'selector' => '{{WRAPPER}} .tm-rating-box__content mark',
		] );

		$this->add_group_control( Group_Control_Text_Stroke::get_type(), [
			'name'     => 'title_highlight_text_stroke',
			'global' => [
				'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
			],
			'selector' => '{{WRAPPER}} .tm-rating-box__content mark',
		] );

		$this->add_group_control( Group_Control_Text_Gradient::get_type(), [
			'name'     => 'title_highlight',
			'selector' => '{{WRAPPER}} .tm-rating-box__content mark',
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'box', 'class', 'tm-rating-box' );

		$this->add_render_attribute( 'rating_content', 'class', 'tm-rating-box__content' );

		?>

		<div <?php $this->print_render_attribute_string( 'box' ) ?>>
			<div class="tm-rating-box__wrapper">
				<?php
				if ( ! empty( $settings['rating'] ) ) {
					\Minimog_Templates::render_rating( $settings['rating'] );
				}

				if ( ! empty( $settings['rating_content'] ) ) {
					printf( '<div %1$s>%2$s</div>', $this->get_render_attribute_string( 'rating_content' ), $settings['rating_content'] );
				}

				$this->render_common_button();
				?>
			</div>
		</div>
		<?php
	}
}
