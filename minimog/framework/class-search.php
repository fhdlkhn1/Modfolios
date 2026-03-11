<?php
defined( 'ABSPATH' ) || exit;

class Minimog_Search {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'alter_search_loop' ), 1 );
			add_filter( 'pre_get_posts', array( $this, 'search_filter' ) );
			add_filter( 'pre_get_posts', array( $this, 'empty_search_filter' ) );
		}
	}

	public function get_category_query() {
		return '_cat';
	}

	/**
	 * @param WP_Query $query Query instance.
	 */
	public function alter_search_loop( $query ) {
		if ( $query->is_main_query() && $query->is_search() ) {
			$number_results = Minimog::setting( 'search_page_number_results' );
			$query->set( 'posts_per_page', $number_results );
		}
	}

	/**
	 * @param WP_Query $query Query instance.
	 *
	 * @return WP_Query $query
	 *
	 * Apply filters to the search query.
	 * Determines if we only want to display posts/pages and changes the query accordingly
	 */
	public function search_filter( $query ) {
		if ( $query->is_main_query() && $query->is_search ) {
			$post_type = Minimog::setting( 'search_page_filter' );

			if ( ! empty( $post_type ) && 'all' !== $post_type ) {
				$query->set( 'post_type', $post_type );

				$cat_query = $this->get_category_query();

				if ( ! empty( $_GET[ $cat_query ] ) ) {
					$search_in_cat = sanitize_text_field( $_GET[ $cat_query ] );

					switch ( $post_type ) :
						case 'post':
							$taxonomy_name = 'category';
							break;
						case 'product':
							$taxonomy_name = 'product_cat';
							break;
					endswitch;

					if ( ! empty( $taxonomy_name ) ) {
						$query->set( 'tax_query', array(
							array(
								'taxonomy' => $taxonomy_name,
								'field'    => 'slug',
								'terms'    => array( $search_in_cat ),
							),
						) );
					}
				}
				
				// Ignore hidden products from search page.
				if ( 'product' === $post_type ) {
					$product_visibility_terms  = wc_get_product_visibility_term_ids();
					$product_visibility_not_in = [
						$product_visibility_terms['exclude-from-search'],
					];

					$tax_query = $query->get( 'tax_query' );
					if ( is_array( $tax_query ) ) {
						// Append terms if exist instead of add new tax query.

						$appended = false;
						foreach ( $tax_query as $key => $tax_query_term ) {
							if ( is_array( $tax_query_term ) && ! empty( $tax_query_term['taxonomy'] ) && 'product_visibility' === $tax_query_term['taxonomy'] && 'term_taxonomy_id' === $tax_query_term['field'] && 'NOT IN' === $tax_query_term['operator'] ) {
								$new_terms               = array_merge( $tax_query_term['terms'], $product_visibility_not_in );
								$tax_query_term['terms'] = $new_terms;
								$tax_query[ $key ]       = $tax_query_term;

								$appended = true;
								break;
							}
						}

						if ( false === $appended ) {
							$tax_query[] = array(
								'taxonomy' => 'product_visibility',
								'field'    => 'term_taxonomy_id',
								'terms'    => $product_visibility_not_in,
								'operator' => 'NOT IN',
							);
						}
					} else {
						$tax_query   = array();
						$tax_query[] = array(
							'taxonomy' => 'product_visibility',
							'field'    => 'term_taxonomy_id',
							'terms'    => $product_visibility_not_in,
							'operator' => 'NOT IN',
						);
					}
					$query->set( 'tax_query', $tax_query );
				}
			}
		}

		return $query;
	}

	/**
	 * Make wordpress respect the search template on an empty search
	 *
	 * @param \WP_Query $query
	 *
	 * @return \WP_Query $query
	 */
	public function empty_search_filter( $query ) {
		if ( isset( $_GET['s'] ) && empty( $_GET['s'] ) && $query->is_main_query() ) {
			$query->is_search = true;
			$query->is_home   = false;
		}

		return $query;
	}
}

Minimog_Search::instance()->initialize();
