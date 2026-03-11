<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="minimog-wc-account-wrap">
	<div class="minimog-wc-account-nav">
		<a href="#" id="btn-toggle-account-nav" class="btn-toggle-account-nav">
			<i class="far fa-list-ul"></i>
		</a>
		<div class="minimog-wc-account-nav-inner">
			<?php if ( is_user_logged_in() ) : ?>
				<?php
				$current_user = wp_get_current_user();
				if ( ( $current_user instanceof WP_User ) ) {
					?>
					<div class="my-account-profile">
						<div class="my-avatar">
							<div class="avatar">
								<?php
								/**
								 * Force display to avoid broken layout when option show_avatars disabled.
								 */
								echo get_avatar( $current_user->ID, 108, '', '', [ 'force_display' => true ] );
								?>

								<?php do_action( 'minimog/myaccount/after_avatar' ); ?>
							</div>

							<?php do_action( 'minimog/myaccount/before_info' ); ?>
						</div>
						<h6 class="my-name fn"><?php echo esc_html( $current_user->display_name ); ?></h6>
					</div>
				<?php } ?>
			<?php endif; ?>
			<?php
			/**
			 * My Account navigation.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_navigation' );
			?>
		</div>
	</div>
	<div class="minimog-wc-account-content woocommerce-MyAccount-content">
		<div class="minimog-wc-account-content-inner">
			<?php
			/**
			 * My Account content.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_content' );
			?>
		</div>
	</div>
</div>
