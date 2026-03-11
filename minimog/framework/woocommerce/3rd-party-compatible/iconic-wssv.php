<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

/**
 * Compatible with WooCommerce Show Single Variations by Iconic
 *
 * @see https://iconicwp.com/products/woocommerce-show-single-variations/
 */
class Iconic_WSSV {
	public function __construct() {
		add_filter( 'minimog/product_query/product_types', [ $this, 'add_product_variation_to_types' ] );
	}

	public function add_product_variation_to_types( $types ) {
		$types[] = 'product_variation';

		return $types;
	}
}

new Iconic_WSSV();
