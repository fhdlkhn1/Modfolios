<?php
/**
 * Login button on header
 *
 * @package Minimog
 * @since   1.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

$icon_display = Minimog::setting( 'header_icons_display' );
$icon_style   = Minimog::setting( 'header_icons_style' );

$link_classes = ' style-' . $args['style'];
$link_classes .= ' icon-display--' . $icon_display;

if ( is_user_logged_in() ) {
	$button_text = apply_filters( 'minimog/user_profile/text', __( 'Log out', 'minimog' ) );
	$button_url  = minimog_get_user_profile_url();
} else {
	$button_text = apply_filters( 'minimog/user_login/text', __( 'Log in', 'minimog' ) );
	$button_url  = minimog_get_login_url();

	/**
	 * Force remove link.
	 */
	if ( Minimog::setting( 'login_popup_enable' ) ) {
		$button_url   = '#';
		$link_classes .= ' open-modal-login';
	}
}

if ( empty( $button_text ) || empty( $button_url ) ) {
	return;
}
?>









<?php if ( is_user_logged_in() ) { 
    global $wpdb, $WCFM;

    $current_user = wp_get_current_user();
    $user_id      = $current_user->ID;

    // Check if user is vendor
    $is_vendor = in_array( 'wcfm_vendor', (array) $current_user->roles, true );

    // Dashboard URL (vendor vs normal user)
    $dashboard_url = $is_vendor ? home_url( '/store-manager/' ) : home_url( '/my-account-2/' );

    // Profile settings (WCFM vendor)
    $profile = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );

    // Avatar fallback logic
    $avatar = '';
    $wp_user_avatar_id = get_user_meta( $user_id, $wpdb->get_blog_prefix( get_current_blog_id() ) . 'user_avatar', true );
    if ( $wp_user_avatar_id ) {
        $avatar = wp_get_attachment_image_url( $wp_user_avatar_id, 'full' );
    }

    if ( ! $avatar && ! empty( $profile['gravatar'] ) ) {
        $avatar_id = intval( $profile['gravatar'] );
        $avatar    = wp_get_attachment_image_url( $avatar_id, 'full' );
    }

    if ( ! $avatar ) {
        $avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
    }

    // Membership Plan (only for vendor)
    $plan_label = '';
    if ( $is_vendor ) {
        $plan_id = get_user_meta( $user_id, 'wcfm_membership', true );
        if ( $plan_id ) {
            $plan_post = get_post( $plan_id );
//             if ( $plan_post && $plan_post->post_type === 'wcfm_membership' ) {
                $plan_label = $plan_post->post_title;
//             }
        }
    }
?>
<div class="header-user-wrapper">
    <a href="javascript:void(0)" class="header-icon custom__header_login header-login-link">
        <span class="icon">
            <?php echo Minimog_SVG_Manager::instance()->get( 'user' ); ?>
        </span>
    </a>

    <!-- Drawer -->
    <div class="user-drawer">
        <div class="user-info">
            <div class="avatar">
                <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" />
            </div>
            <div class="details">
                <strong><?php echo esc_html( $current_user->display_name ); ?></strong>
                <small><?php echo esc_html( $current_user->user_email ); ?></small>
                <?php if ( $plan_label ) : ?>
                    <div class="plan">
                        <?php echo esc_html__( 'Membership: ', 'your-text-domain' ) . esc_html( $plan_label ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
		<?php
			$is_vendor = function_exists('wcfm_is_vendor') && wcfm_is_vendor();

			if ( ! $is_vendor ) :
				$wallet_balance = (float) get_user_meta( get_current_user_id(), '_modfolios_user_wallet', true );
				if ( $wallet_balance < 0 ) {
					$wallet_balance = 0;
				}
			?>
				<div class="user-wallet-balance">
					<i class="fas fa-wallet"></i>
					<span class="label"><?php esc_html_e( 'Balance:', 'your-text-domain' ); ?></span>
					<strong class="amount">
						<?php echo wc_price( $wallet_balance ); ?>
					</strong>
				</div>
		<?php endif; ?>
        <ul class="user-links">
            <li>
                <a href="<?php echo esc_url( $dashboard_url ); ?>">
                    <i class="fas fa-tachometer-alt"></i> <?php esc_html_e( 'Dashboard', 'your-text-domain' ); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo esc_url( wp_logout_url( home_url( '/my-account-2/' ) ) ); ?>">
                    <i class="fas fa-sign-out-alt"></i> <?php esc_html_e( 'Sign Out', 'your-text-domain' ); ?>
                </a>
            </li>
        </ul>
    </div>
</div>
<?php 
}
else{
	?>

	<a href="<?php echo esc_url( $button_url ); ?>" class="header-icon custom__header_login header-login-link hint--bounce hint--bottom<?php echo esc_attr( $link_classes ); ?>"
   aria-label="<?php echo esc_attr( $button_text ); ?>">
	<?php
	switch ( $icon_style ) {
		case 'icon-set-02':
			$icon_key = 'user-light';
			break;
		case 'icon-set-03':
			$icon_key = 'phr-user';
			break;
		case 'icon-set-04':
			$icon_key = 'user-solid';
			break;
		case 'icon-set-05':
			$icon_key = 'phb-user';
			break;
		default :
			$icon_key = 'user';
			break;
	}
	?>
	<span class="icon">
		<?php echo Minimog_SVG_Manager::instance()->get( $icon_key ) ?>
	</span>
	<?php if ( in_array( $args['display'], [ 'text', 'icon-text' ], true ) ): ?>
		<span class="text"><?php echo esc_html( $button_text ); ?></span>
	<?php endif; ?>
</a>

<?php
}
?>

<?php
// Force logout redirect (in case other plugins override)
add_filter( 'logout_redirect', function( $redirect_to, $requested_redirect_to, $user ) {
    return home_url( '/my-account-2/' );
}, 10, 3 );