<?php
/**
 * This Widget is useful for build Menu.
 * For build complicated list, use Modern Icon List Widget instead.
 */

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Widget_Simple_List extends Base {

	public function get_name() {
		return 'tm-simple-list';
	}

	public function get_title() {
		return __( 'Modern List', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-bullet-list';
	}

	public function get_keywords() {
		return [ 'modern', 'list' ];
	}

	protected function register_controls() {
		$this->add_list_section();

		$this->add_styling_section();

		$this->add_badge_style_section();
	}

	private function add_list_section() {
		$this->start_controls_section( 'list_section', [
			'label' => __( 'List', 'minimog' ),
		] );

		$this->add_control( 'style', [
			'label'        => __( 'Style', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'default'      => '01',
			'options'      => [
				'01' => '01',
				'02' => '02',
				'03' => '03',
			],
		] );

		$this->add_control( 'item_short_display', [
			'label'       => __( 'Item Short Display', 'minimog' ),
			'type'        => Controls_Manager::SWITCHER,
			'label_off'   => __( 'No', 'minimog' ),
			'label_on'    => __( 'Yes', 'minimog' ),
			'default'     => 'yes',
			'condition'   => [
				'style' => [ '03' ],
			],
			'description' => __( 'The text is truncated', 'minimog' ),
			'separator'   => 'after',
		]);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'item_tabs' );

		// Content Tab
		$repeater->start_controls_tab( 'item_content_tab', [
			'label' => __( 'Content', 'minimog' ),
		] );

		$repeater->add_control( 'link', [
			'label'       => __( 'Link', 'minimog' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => [
				'active' => true,
			],
			'placeholder' => __( 'https://your-link.com', 'minimog' ),
		] );

		$repeater->add_control( 'text', [
			'label'       => __( 'Text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Text', 'minimog' ),
			'label_block' => true,
		] );

		$repeater->add_control( 'text_badge', [
			'label'       => __( 'Badge Text', 'minimog' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'label_block' => true,
		] );

		$repeater->end_controls_tab();

		// Style Tab
		$repeater->start_controls_tab( 'item_style_tab', [
			'label' => __( 'Style', 'minimog' ),
		] );

		$repeater->add_control( 'custom_style', [
			'label'       => __( 'Custom', 'minimog' ),
			'type'        => Controls_Manager::SWITCHER,
			'description' => __( 'Set custom style that will only affect this specific item.', 'minimog' ),
		] );

		$repeater->add_control( 'badge_color', [
			'label'     => __( 'Badge Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} {{CURRENT_ITEM}} .badge__text' => 'color: {{VALUE}};',
			],
			'condition' => [
				'custom_style' => 'yes',
			],
		] );

		$repeater->add_control( 'badge_bg_color', [
			'label'     => __( 'Badge Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} {{CURRENT_ITEM}} .badge__text' => 'background-color: {{VALUE}};',
			],
			'condition' => [
				'custom_style' => 'yes',
			],
		] );

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control( 'items', [
			'label'       => __( 'Items', 'minimog' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[
					'text' => 'List Item #1',
				],
				[
					'text' => 'List Item #2',
				],
				[
					'text' => 'List Item #3',
				],
			],
			'title_field' => '{{{ text }}}',
		] );

		$this->end_controls_section();
	}

	private function add_styling_section() {
		$this->start_controls_section( 'list_style_section', [
			'label' => __( 'List', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'list_width', [
			'label'      => __( 'Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%', 'px' ],
			'range'      => [
				'%'  => [
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'max'  => 1000,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-simple-list' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'list_alignment', [
			'label'                => __( 'Alignment', 'minimog' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => Widget_Utils::get_control_options_horizontal_alignment(),
			'default'              => '',
			'selectors_dictionary' => [
				'left'  => 'flex-start',
				'right' => 'flex-end',
			],
			'selectors'            => [
				'{{WRAPPER}} .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}}',
			],
		] );

		$this->add_responsive_control( 'list_text_align', [
			'label'     => __( 'Text Align', 'minimog' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => Widget_Utils::get_control_options_text_align(),
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .minimog-simple-list' => 'text-align: {{VALUE}};',
			],
		] );

		$this->add_control( 'item_style_hr', [
			'label'     => __( 'Items', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'items_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-simple-list .item + .item' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'item_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .link'       => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'link_typography',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .link',
		] );

		$this->start_controls_tabs( 'link_style_tabs' );

		$this->start_controls_tab( 'text_style_normal_tab', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'link_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .link' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'link_bg_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .link' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'link_style_hover_tab', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'link_hover_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .link:hover' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'link_hover_bg_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .link:hover' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'link_hover_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .link:hover:after' => 'background-color: {{VALUE}};',
			],
			'condition' => [
				'style' => [ '03' ],
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function add_badge_style_section() {
		$this->start_controls_section( 'badge_style_section', [
			'label' => __( 'Badge', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'badge_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .badge__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'badge_rounded', [
			'label'      => __( 'Rounded', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .badge__text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'badge_typography',
			'label'    => __( 'Typography', 'minimog' ),
			'selector' => '{{WRAPPER}} .badge__text',
		] );

		$this->add_control( 'badge_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .badge__text' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'badge_bg_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .badge__text' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) ) {
			return;
		}

		$this->add_render_attribute( 'wrapper', 'class', [
			'minimog-simple-list',
			'minimog-simple-list--style-' . $settings['style'],
		] );

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php foreach ( $settings['items'] as $key => $item ) {
				if ( empty( $item['text'] ) ) {
					continue;
				}

				$item_key = $item['_id'];

				$item_classes = [
					'item',
					'elementor-repeater-item-' . $item_key,
				];

				if ( '03' === $settings['style'] && 'yes' === $settings['item_short_display'] ) {
					$item_classes[] = 'truncated';
				}

				$this->add_render_attribute( $item_key, 'class', $item_classes );

				$link_tag = 'div';

				$item_link_key = 'item_link_' . $item['_id'];

				$this->add_render_attribute( $item_link_key, 'class', 'link' );

				if ( ! empty( $item['link']['url'] ) ) {
					$link_tag = 'a';
					$this->add_link_attributes( $item_link_key, $item['link'] );
				}

				?>
				<div <?php $this->print_render_attribute_string( $item_key ); ?>>
					<?php printf( '<%1$s %2$s>', $link_tag, $this->get_render_attribute_string( $item_link_key ) ); ?>
						<?php echo esc_html( $item['text'] ); ?>

						<?php if ( ! empty( $item['text_badge'] ) ) { ?>
							<span class="badge"><span class="badge__text"><?php echo esc_html( $item['text_badge'] ); ?></span></span>
						<?php } ?>

					<?php printf( '</%1$s>', $link_tag ); ?>
				</div>
				<?php
				}
			?>
		</div>
		<?php
	}

	protected function content_template() {
		// @formatter:off
		?>
		<div class="minimog-simple-list minimog-simple-list--style-{{ settings.style }}">
			<# _.each( settings.items, function( item, index ) { #>
				<#
				var item_key = 'item_' + item._id;
				var item_link_key = item._id;


				view.addRenderAttribute( item_link_key, 'class', 'link' );

				var link_tag = 'div';
				if ( '' !== item.link.url ) {
					link_tag = 'a';
					view.addRenderAttribute( item_link_key, 'href', '#' );
				}

				view.addRenderAttribute( item_key, 'class', [
					'item',
					'elementor-repeater-item-' + item_link_key,
				] );

				if ( '03' === settings.style && 'yes' === settings.item_short_display ) {
					view.addRenderAttribute( item_key, 'class', 'truncated' );
				}
				#>
				<div {{{ view.getRenderAttributeString( item_key ) }}}>
					<{{{ link_tag }}} {{{ view.getRenderAttributeString( item_link_key ) }}}>
						<#	if ( '' !== item.text ) { #>
							{{{ item.text }}}
						<# } #>

						<#	if ( '' !== item.text_badge ) { #>
							<span class="badge"><span class="badge__text">{{{ item.text_badge }}}</span></span>
						<# } #>
					</{{{ link_tag }}}>
				</div>
			<# }); #>
		</div>
		<?php
		// @formatter:off
	}
}
