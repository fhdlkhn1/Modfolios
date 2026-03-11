<?php
/**
 * Result Count
 *
 * Shows text: Showing x - x of x results.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/result-count.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.9.0
 */

defined( 'ABSPATH' ) || exit;

$show_result_count = Minimog::setting( 'shop_archive_result_count' );
?>
<div class="shop-actions-toolbar shop-actions-toolbar-left col">
	<div class="inner">
		<?php do_action( 'minimog/shop_archive/actions_toolbar_left/before' ); ?>

		<?php if ( '1' === $show_result_count ): ?>
			<div class="woocommerce-result-count archive-result-count" role="alert" aria-relevant="all"<?php echo ( empty( $orderedby ) || 1 === intval( $total ) ) ? '' : ' data-is-sorted-by="true"'; ?>>
				<?php
				$pagination_type = Minimog::setting( 'shop_archive_pagination_type' );

				if ( 1 === intval( $total ) ) {
					$text = esc_html__( 'Showing the single result', 'minimog' );
				} elseif ( $total <= $per_page || - 1 === $per_page ) {
					$orderedby_placeholder = empty( $orderedby ) ? '%2$s' : '<span class="screen-reader-text">%2$s</span>';
					/* translators: 1: total results 2: sorted by */
					$text = sprintf( _n( 'Showing all %1$d result', 'Showing all %1$d results', $total, 'minimog' ) . $orderedby_placeholder, $total, esc_html( $orderedby ) );
				} else {
					if ( in_array( $pagination_type, [ 'load-more', 'infinite' ], true ) ) {
						$first = min( $total, $per_page * $current );

						$text = sprintf( _nx( 'Showing %1$d of %2$d result', 'Showing %1$d of %2$d results', $total, 'with first and last result', 'minimog' ), $first, $total );
					} else {
						$first                 = ( $per_page * $current ) - $per_page + 1;
						$last                  = min( $total, $per_page * $current );
						$orderedby_placeholder = empty( $orderedby ) ? '%4$s' : '<span class="screen-reader-text">%4$s</span>';
						/* translators: 1: first result 2: last result 3: total results 4: sorted by */
						$text = sprintf( _nx( 'Showing %1$d&ndash;%2$d of %3$d result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'minimog' ) . $orderedby_placeholder, $first, $last, $total, esc_html( $orderedby ) );
					}
				}

				echo '<p>' . $text . '</p>';
				?>
			</div>
		<?php endif; ?>

		<?php do_action( 'minimog/shop_archive/actions_toolbar_left/after' ); ?>
	</div>
</div>
