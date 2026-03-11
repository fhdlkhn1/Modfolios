<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_Blog_Query' ) ) {
	class Minimog_Blog_Query {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_action( 'pre_get_posts', [ $this, 'update_main_query' ], 99 );
		}

		/**
		 * @param \WP_Query $query
		 */
		public function update_main_query( $query ) {
			if ( $query->is_main_query() && ! is_admin() && Minimog_Post::instance()->is_archive() ) {
				if ( get_query_var( 'paged' ) ) {
					$paged = intval( get_query_var( 'paged' ) );
				} elseif ( get_query_var( 'page' ) ) {
					$paged = intval( get_query_var( 'page' ) );
				} else {
					$paged = 1;
				}

				$query->set( 'paged', $paged );

				// Change post per page.
				$posts_per_page = Minimog_Post::instance()->get_blog_posts_per_page();
				$query->set( 'posts_per_page', apply_filters( 'minimog/archive_blog/posts_per_page', $posts_per_page ) );
			}
		}
	}

	Minimog_Blog_Query::instance()->initialize();
}
