<?php

namespace Minimog_Elementor;

use Elementor\Plugin;

defined( 'ABSPATH' ) || exit;

class WC_Compatible {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function initialize() {
		add_filter( 'woocommerce_product_tabs', [ $this, 'fix_editor_content_area_not_found' ], 9998 );
	}

	/**
	 * Add the description (content) tab for a new product, so it can be edited with Elementor.
	 *
	 * Elementor also do that but with low priority.
	 * Then 3rd plugins like WPC Product Tabs override and raising bug.
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 *
	 * @see \Elementor\Compatibility::init()
	 */
	public function fix_editor_content_area_not_found( $tabs ) {
		if ( ! isset( $tabs['description'] ) && Plugin::$instance->preview->is_preview_mode() ) {
			$desc_tab = [
				'description' => [
					'title'    => __( 'Description', 'minimog' ),
					'priority' => 10,
					'callback' => 'woocommerce_product_description_tab',
				],
			];

			/**
			 * Use + operator to make it's as first tab. because this hook run after woocommerce_sort_product_tabs hooked at 99
			 *
			 * @see woocommerce_sort_product_tabs()
			 */
			$tabs = $desc_tab + $tabs;
		}

		return $tabs;
	}
}

WC_Compatible::instance()->initialize();
