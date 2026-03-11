<?php
/**
 * Pagination - Show pagination for blog archive
 *
 * @since   1.0.0
 * @version 2.9.1
 */

defined( 'ABSPATH' ) || exit;

$pagination_type = Minimog::setting( 'blog_archive_pagination_type' );

global $wp_query, $wp_rewrite;

$total = $wp_query->max_num_pages;

if ( get_query_var( 'paged' ) ) {
	$paged = get_query_var( 'paged' );
} elseif ( get_query_var( 'page' ) ) {
	$paged = get_query_var( 'page' );
} else {
	$paged = 1;
}

$current       = max( 1, $paged );
$page_num_link = html_entity_decode( get_pagenum_link() );
$query_args    = array();
$url_parts     = explode( '?', $page_num_link );

if ( isset( $url_parts[1] ) ) {
	wp_parse_str( $url_parts[1], $query_args );
}

$page_num_link = esc_url( remove_query_arg( array_keys( $query_args ), $page_num_link ) );
$page_num_link = trailingslashit( $page_num_link ) . '%_%';

$format = '';
if ( $wp_rewrite->using_index_permalinks() && ! strpos( $page_num_link, 'index.php' ) ) {
	$format = 'index.php/';
}
if ( $wp_rewrite->using_permalinks() ) {
	$format .= user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' );
} else {
	$format .= '?paged=%#%';
}
?>
<nav class="minimog-grid-pagination" data-type="<?php echo esc_attr( $pagination_type ); ?>">
	<?php if ( in_array( $pagination_type, [ 'load-more', 'infinite' ], true ) ) : ?>
		<?php if ( $total > $current ): ?>
			<?php
			$load_more_url = str_replace( '%_%', $format, $page_num_link );
			$load_more_url = str_replace( '%#%', $current + 1, $load_more_url );
			?>
			<button data-url="<?php echo esc_url( $load_more_url ); ?>" class="archive-load-more-button">
				<span class="button-text"><?php esc_html_e( 'Load more', 'minimog' ); ?></span>
			</button>
		<?php endif; ?>
	<?php else: ?>
		<?php if ( $total > 1 ) : ?>
			<?php
			echo paginate_links( [
				// WPCS: XSS ok.
				'base'      => $page_num_link,
				'format'    => $format,
				'add_args'  => false,
				'current'   => $current,
				'total'     => $total,
				'prev_text' => Minimog_Templates::get_pagination_prev_text(),
				'next_text' => Minimog_Templates::get_pagination_next_text(),
				'type'      => 'list',
				'end_size'  => 3,
				'mid_size'  => 3,
			] );
			?>
		<?php endif; ?>
	<?php endif; ?>
</nav>
