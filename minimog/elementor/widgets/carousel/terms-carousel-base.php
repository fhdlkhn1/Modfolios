<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

abstract class Terms_Carousel_Base extends Carousel_Base {

	private $terms         = null;
	private $current_slide = null;
	private $current_key   = null;

	abstract protected function get_taxonomy_name();

	abstract protected function print_slide();

	protected function query_terms() {
		$settings = $this->get_settings_for_display();

		$taxonomy_name = $this->get_taxonomy_name();

		$attributes = [
			'taxonomy'   => $taxonomy_name,
			'number'     => $settings['number'],
			'hide_empty' => 'yes' === $settings['hide_empty'],
		];

		switch ( $settings['source'] ) {
			case 'by_id':
				$attributes['include'] = $settings['categories'];
				$attributes['orderby'] = 'include';
				break;
			case 'by_parent' :
				$attributes['parent']  = $settings['parent'];
				$attributes['orderby'] = $settings['orderby'];
				$attributes['order']   = $settings['order'];
				break;
			case 'current_subcategories':
				$attributes['parent']  = get_queried_object_id();
				$attributes['orderby'] = $settings['orderby'];
				$attributes['order']   = $settings['order'];
				break;
			default:
				$attributes['orderby'] = $settings['orderby'];
				$attributes['order']   = $settings['order'];
				break;
		}

		$terms = get_terms( $attributes );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			$terms = [];
		}

		$this->terms = $terms;
	}

	protected function get_terms() {
		if ( $this->terms === null ) {
			$this->query_terms();
		}

		return $this->terms;
	}

	protected function get_current_slide() {
		return $this->current_slide;
	}

	protected function get_current_key() {
		return $this->current_key;
	}

	protected function register_controls() {
		parent::register_controls();

		$this->register_query_section();
	}

	protected function register_query_section() {
		$this->start_controls_section( 'query_section', [
			'label' => __( 'Query', 'minimog' ),
		] );

		$this->add_control( 'source', [
			'label'       => __( 'Source', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				''                      => __( 'Show All', 'minimog' ),
				'by_id'                 => __( 'Manual Selection', 'minimog' ),
				'by_parent'             => __( 'By Parent', 'minimog' ),
				'current_subcategories' => __( 'Current Subcategories', 'minimog' ),
			],
			'label_block' => true,
		] );

		$taxonomy_name = $this->get_taxonomy_name();

		$categories = get_terms( [
			'taxonomy' => $taxonomy_name,
		] );

		$options = [];
		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = $category->name;
		}

		$this->add_control( 'categories', [
			'label'       => __( 'Categories', 'minimog' ),
			'type'        => Controls_Manager::SELECT2,
			'options'     => $options,
			'default'     => [],
			'label_block' => true,
			'multiple'    => true,
			'condition'   => [
				'source' => 'by_id',
			],
		] );

		$parent_options = [ '0' => __( 'Only Top Level', 'minimog' ) ] + $options;
		$this->add_control(
			'parent', [
			'label'     => __( 'Parent', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '0',
			'options'   => $parent_options,
			'condition' => [
				'source' => 'by_parent',
			],
		] );

		$this->add_control( 'hide_empty', [
			'label'     => __( 'Hide Empty', 'minimog' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => 'yes',
			'label_on'  => __( 'Hide', 'minimog' ),
			'label_off' => __( 'Show', 'minimog' ),
		] );

		$this->add_control( 'number', [
			'label'   => __( 'Categories Count', 'minimog' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => '10',
		] );

		$this->add_control( 'orderby', [
			'label'   => __( 'Order By', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'name',
			'options' => [
				'name'        => __( 'Name', 'minimog' ),
				'slug'        => __( 'Slug', 'minimog' ),
				'description' => __( 'Description', 'minimog' ),
				'count'       => __( 'Count', 'minimog' ),
			],
		] );

		$this->add_control( 'order', [
			'label'   => __( 'Order', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'desc',
			'options' => [
				'asc'  => __( 'ASC', 'minimog' ),
				'desc' => __( 'DESC', 'minimog' ),
			],
		] );

		$this->end_controls_section();
	}

	protected function print_slides( array $settings ) {
		$terms = $this->get_terms();

		if ( empty( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) :
			$term_id = $term->term_id;

			$this->current_slide = $term;
			$this->current_key   = "term_{$term_id}_";

			$this->print_slide();

		endforeach;
	}
}
