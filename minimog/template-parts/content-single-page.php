<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Minimog
 * @since   1.0.0
 * @version 2.7.2
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php the_title( '<h1 class="screen-reader-text">', '</h1>' ); ?>
	<?php
	the_content();

	Minimog_Templates::page_links();
	?>
</article>
