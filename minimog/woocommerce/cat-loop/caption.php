<?php
/**
 * Default Category Style
 */

defined( 'ABSPATH' ) || exit;
extract( $args );
?>
<div class="category-info">
	<div class="category-info-wrapper">
		<h2 class="category-name">
			<a href="<?php echo esc_url( $link ); ?>">
				<?php echo esc_html( $category->name ); ?>
			</a>
		</h2>

		<?php wc_get_template( 'cat-loop/item-count.php', [
			'category' => $category,
			'link'     => $link,
			'settings' => $settings,
		] ); ?>

		<?php wc_get_template( 'cat-loop/min-price.php', [
			'category' => $category,
			'link'     => $link,
			'settings' => $settings,
		] ); ?>
	</div>
</div>
