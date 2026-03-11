<?php
/**
 * Search form on search popup
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.3.4
 */

defined( 'ABSPATH' ) || exit;

$content_type             = Minimog::setting( 'search_page_filter' );
$with_categories          = Minimog::setting( 'popup_search_categories_enable' );
$search_field_placeholder = _x( 'Search&hellip;', 'placeholder', 'minimog' );
$search_form_style        = Minimog::setting( 'header_search_form_style' );

$form_class = 'search-form popup-search-form style-' . $search_form_style;

if ( 'product' === $content_type ) {
	$form_class               .= ' woocommerce-product-search';
	$search_field_placeholder = _x( 'Search products', 'placeholder', 'minimog' );
}

if ( ! empty( $with_categories ) ) {
	$form_class .= ' search-form-categories';
}

$icon_style = Minimog::setting( 'header_icons_style' );
switch ( $icon_style ) :
	case 'icon-set-02':
		$icon_key = 'search-light';
		break;
	case 'icon-set-03':
		$icon_key = 'phr-magnifying-glass';
		break;
	case 'icon-set-04':
		$icon_key = 'search-solid';
		break;
	case 'icon-set-05':
		$icon_key = 'phb-magnifying-glass';
		break;
	default:
		$icon_key = 'search';
		break;
endswitch;
?>
<form role="search" method="get" class="<?php echo esc_attr( $form_class ); ?>"
      action="<?php echo esc_url( home_url( '/' ) ); ?>">

	<?php
	if ( ! empty( $with_categories ) ) {
		$dropdown_name = Minimog_Search::instance()->get_category_query();
		$dropdown_args = array(
			'show_option_all' => esc_html__( 'All Categories', 'minimog' ),
			'hierarchical'    => 1,
			'class'           => 'search-select',
			'echo'            => 1,
			'value_field'     => 'slug',
			'selected'        => 1,
			'name'            => $dropdown_name,
		);

		$search_child_cats = apply_filters( 'minimog/popup_search/show_child_cats', true );
		if ( ! $search_child_cats ) {
			$dropdown_args['parent'] = 0;
		}

		if ( 'product' === $content_type ) {
			$dropdown_args['taxonomy'] = 'product_cat';
		}

		if ( ! empty( $_GET[ $dropdown_name ] ) ) {
			$dropdown_args['selected'] = sanitize_text_field( $_GET[ $dropdown_name ] );
		}
		?>
		<div class="search-category-field">
			<?php wp_dropdown_categories( $dropdown_args ); ?>
		</div>
		<?php
	}
	?>
	<span class="screen-reader-text">
		<?php echo esc_html_x( 'Search for:', 'label', 'minimog' ); ?>
	</span>
	<span class="search-field__icon" aria-hidden="true"><?php echo Minimog_SVG_Manager::instance()->get( $icon_key ); ?></span>
	<input type="search" class="search-field"
	       placeholder="<?php echo esc_attr( $search_field_placeholder ); ?>"
	       value="<?php echo get_search_query() ?>" name="s"
	       autocomplete="off"
	       title="<?php echo esc_attr_x( 'Search for:', 'label', 'minimog' ); ?>"/>
	<button type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'minimog' ); ?>">
		<span class="search-btn-icon">
			<?php echo Minimog_SVG_Manager::instance()->get( $icon_key ); ?>
		</span>
		<span class="search-btn-text">
			<?php echo esc_html_x( 'Search', 'submit button', 'minimog' ); ?>
		</span>
	</button>
</form>
