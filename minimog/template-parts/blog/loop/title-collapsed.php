<?php
/**
 * The template for displaying loop post title.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Minimog
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$excerpt    = get_the_excerpt();
?>
<h3 class="post-title post-title-2-rows">
	<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h3>
<p class="modfolio-blog-excerpt custom__blog_excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 18, '...' ) ); ?></p>
<a href="<?php the_permalink(); ?>" class="tm-button blog__readmore">Read More</a>