<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

abstract class Posts_Carousel_Base extends Carousel_Base {

	/**
	 * @var \WP_Query
	 */
	private $_query      = null;
	private $_query_args = null;

	abstract protected function get_post_type();

	public function query_posts() {
		$settings          = $this->get_settings_for_display();
		$post_type         = $this->get_post_type();
		$this->_query      = Module_Query_Base::instance()->get_query( $settings, $post_type );
		$this->_query_args = Module_Query_Base::instance()->get_query_args();
	}

	protected function get_query() {
		return $this->_query;
	}

	protected function get_query_args() {
		return $this->_query_args;
	}

	protected function get_query_author_object() {
		return Module_Query_Base::QUERY_OBJECT_AUTHOR;
	}

	abstract protected function print_slide( array $settings );

	protected function register_controls() {
		parent::register_controls();

		$this->register_query_section();
	}

	protected function get_query_orderby_options() {
		$options = [
			'date'           => __( 'Date', 'minimog' ),
			'ID'             => __( 'Post ID', 'minimog' ),
			'author'         => __( 'Author', 'minimog' ),
			'title'          => __( 'Title', 'minimog' ),
			'modified'       => __( 'Last modified date', 'minimog' ),
			'parent'         => __( 'Post/page parent ID', 'minimog' ),
			'comment_count'  => __( 'Number of comments', 'minimog' ),
			'menu_order'     => __( 'Menu order/Page Order', 'minimog' ),
			'meta_value'     => __( 'Meta value', 'minimog' ),
			'meta_value_num' => __( 'Meta value number', 'minimog' ),
			'rand'           => __( 'Random order', 'minimog' ),
			'views'          => __( 'Views', 'minimog' ),
		];

		$post_type = $this->get_post_type();

		if ( 'product' === $post_type ) {
			$options = array_merge( $options, [
				'woo_featured'     => __( 'Featured Products', 'minimog' ),
				'woo_best_selling' => __( 'Best Selling Products', 'minimog' ),
				'woo_on_sale'      => __( 'On Sale Products', 'minimog' ),
				'woo_top_rated'    => __( 'Top Rated Products', 'minimog' ),
			] );
		}

		return $options;
	}

	protected function register_query_section() {
		$this->start_controls_section( 'query_section', [
			'label' => __( 'Query', 'minimog' ),
		] );

		$this->add_control( 'query_source', [
			'label'   => __( 'Source', 'minimog' ),
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'custom_query'  => __( 'Custom Query', 'minimog' ),
				'current_query' => __( 'Current Query', 'minimog' ),
			),
			'default' => 'custom_query',
		] );

		$this->start_controls_tabs( 'query_args_tabs', [
			'condition' => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->start_controls_tab( 'query_include_tab', [
			'label' => __( 'Include', 'minimog' ),
		] );

		$this->add_control( 'query_include', [
			'label'       => __( 'Include By', 'minimog' ),
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => [
				'terms'   => __( 'Term', 'minimog' ),
				'authors' => __( 'Author', 'minimog' ),
			],
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_include_term_ids', [
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'options'      => [],
			'label_block'  => true,
			'multiple'     => true,
			'autocomplete' => [
				'object'  => Module_Query_Base::QUERY_OBJECT_TAX,
				'display' => 'detailed',
				'query'   => [
					'post_type' => $this->get_post_type(),
				],
			],
			'condition'    => [
				'query_include' => 'terms',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_include_authors', [
			'label'        => __( 'Author', 'minimog' ),
			'label_block'  => true,
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'multiple'     => true,
			'default'      => [],
			'options'      => [],
			'autocomplete' => [
				'object' => $this->get_query_author_object(),
			],
			'condition'    => [
				'query_include' => 'authors',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'query_exclude_tab', [
			'label' => __( 'Exclude', 'minimog' ),
		] );

		$this->add_control( 'query_exclude', [
			'label'       => __( 'Exclude By', 'minimog' ),
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => [
				'terms'   => __( 'Term', 'minimog' ),
				'authors' => __( 'Author', 'minimog' ),
			],
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_exclude_term_ids', [
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'options'      => [],
			'label_block'  => true,
			'multiple'     => true,
			'autocomplete' => [
				'object'  => Module_Query_Base::QUERY_OBJECT_TAX,
				'display' => 'detailed',
				'query'   => [
					'post_type' => $this->get_post_type(),
				],
			],
			'condition'    => [
				'query_exclude' => 'terms',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_exclude_authors', [
			'label'        => __( 'Author', 'minimog' ),
			'label_block'  => true,
			'type'         => Module_Query_Base::AUTOCOMPLETE_CONTROL_ID,
			'multiple'     => true,
			'default'      => [],
			'options'      => [],
			'autocomplete' => [
				'object' => $this->get_query_author_object(),
			],
			'condition'    => [
				'query_exclude' => 'authors',
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'query_number', [
			'label'       => __( 'Items per page', 'minimog' ),
			'description' => __( 'Number of items to show per page. Input "-1" to show all posts. Leave blank to use global setting.', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => -1,
			'max'         => 100,
			'step'        => 1,
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
			'separator'   => 'before',
		] );

		$this->add_control( 'query_offset', [
			'label'       => __( 'Offset', 'minimog' ),
			'description' => __( 'Number of items to displace or pass over.', 'minimog' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_orderby', [
			'label'       => __( 'Order by', 'minimog' ),
			'description' => __( 'Select order type. If "Meta value" or "Meta value Number" is chosen then meta key is required.', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => $this->get_query_orderby_options(),
			'default'     => 'date',
			'condition'   => [
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_sort_meta_key', [
			'label'     => __( 'Meta key', 'minimog' ),
			'type'      => Controls_Manager::TEXT,
			'condition' => [
				'query_orderby' => [
					'meta_value',
					'meta_value_num',
				],
				'query_source!' => [ 'current_query' ],
			],
		] );

		$this->add_control( 'query_order', [
			'label'     => __( 'Sort order', 'minimog' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				'DESC' => __( 'Descending', 'minimog' ),
				'ASC'  => __( 'Ascending', 'minimog' ),
			),
			'default'   => 'DESC',
			'condition' => [
				'query_source!'  => [ 'current_query' ],
				'query_orderby!' => [
					'views',
					'woo_best_selling',
					'woo_top_rated',
				],
			],
		] );

		$this->end_controls_section();
	}

	protected function print_slides( array $settings ) {
		$settings = $this->get_settings_for_display();
		$this->query_posts();
		/**
		 * @var $query \WP_Query
		 */
		$query = $this->get_query();
		?>
		<?php if ( $query->have_posts() ) : ?>

			<?php $this->before_loop(); ?>

			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php $this->print_slide( $settings ); ?>
			<?php endwhile; ?>

			<?php $this->after_loop(); ?>

		<?php endif;
		wp_reset_postdata();
	}

	protected function before_loop() {
	}

	protected function after_loop() {
	}
}
