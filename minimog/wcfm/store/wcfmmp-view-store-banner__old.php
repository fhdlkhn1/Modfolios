<?php
/**
 * The Template for displaying store banner - Figma Design
 *
 * @package WCfM Markeplace Views Store Banner
 *
 * Theme override for Modfolio design
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $WCFM, $WCFMmp;

if( !apply_filters( 'wcfm_is_allow_store_banner', true ) ) return;

$banner_type    = $store_user->get_banner_type();
$banner         = '';
$default_banner = !empty( $WCFMmp->wcfmmp_marketplace_options['store_default_banner'] ) ? wcfm_get_attachment_url($WCFMmp->wcfmmp_marketplace_options['store_default_banner']) : esc_url($WCFMmp->plugin_url . 'assets/images/default_banner.jpg');

if( $banner_type == 'slider' ) {
    $banner_sliders = $store_user->get_banner_slider();
} elseif( $banner_type == 'video' ) {
    $banner_video = $store_user->get_banner_video();
} else {
    $banner = $store_user->get_banner();
}
if( !$banner ) {
    $banner = $default_banner;
    $banner = apply_filters( 'wcfmmp_store_default_banner', $banner );
}

// Get vendor data for tagline and about
$vendor_id = $store_user->get_id();
$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
if ( ! is_array( $vendor_data ) ) {
    $vendor_data = array();
}

$tagline = isset( $vendor_data['tagline'] ) ? $vendor_data['tagline'] : '';
$store_name = isset( $store_info['store_name'] ) ? $store_info['store_name'] : '';

// Get social links
$social_fb = isset( $store_info['social']['fb'] ) ? $store_info['social']['fb'] : '';
$social_linkedin = isset( $store_info['social']['linkedin'] ) ? $store_info['social']['linkedin'] : '';
$social_instagram = isset( $store_info['social']['instagram'] ) ? $store_info['social']['instagram'] : '';
$social_twitter = isset( $store_info['social']['twitter'] ) ? $store_info['social']['twitter'] : '';

$store_banner_height = isset( $WCFMmp->wcfmmp_marketplace_options['store_banner_height'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_banner_height'] : '350';
$store_banner_mheight = isset( $WCFMmp->wcfmmp_marketplace_options['store_banner_mheight'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_banner_mheight'] : '250';

?>

<style>
/* Modfolio Store Banner Styles */
.modfolio-store-banner {
    position: relative;
    width: 100%;
    height: <?php echo esc_attr($store_banner_height); ?>px;
    overflow: hidden;
    border-radius: 0 0 20px 20px;
}

.modfolio-store-banner .banner-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url(<?php echo esc_url($banner); ?>);
    background-size: cover;
    background-position: center;
}

.modfolio-store-banner .banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(102, 51, 153, 0.7) 0%, rgba(0, 100, 150, 0.5) 100%);
}

.modfolio-store-banner .banner-content {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    height: 100%;
    padding: 30px 40px;
    box-sizing: border-box;
}

.modfolio-store-banner .banner-left {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding-top: 20px;
}

.modfolio-store-banner .store-name-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.modfolio-store-banner .store-name {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    margin: 0;
    line-height: 1.2;
}

.modfolio-store-banner .verified-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #00c4aa;
    border-radius: 50%;
    color: #fff;
    font-size: 12px;
}

.modfolio-store-banner .store-tagline {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    margin-top: 6px;
    font-weight: 400;
}

.modfolio-store-banner .banner-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 16px;
    padding-top: 20px;
}

.modfolio-store-banner .social-icons {
    display: flex;
    gap: 12px;
}

.modfolio-store-banner .social-icons a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    color: #fff;
    font-size: 16px;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
}

.modfolio-store-banner .social-icons a:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.modfolio-store-banner .request-shoot-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #00c4aa;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.modfolio-store-banner .request-shoot-btn:hover {
    background: #00a08a;
    transform: translateY(-2px);
    color: #fff;
}

/* Mobile Responsive */
@media screen and (max-width: 768px) {
    .modfolio-store-banner {
        height: <?php echo esc_attr($store_banner_mheight); ?>px;
    }

    .modfolio-store-banner .banner-content {
        padding: 20px;
        flex-direction: column;
    }

    .modfolio-store-banner .banner-left {
        padding-top: 10px;
    }

    .modfolio-store-banner .store-name {
        font-size: 24px;
    }

    .modfolio-store-banner .banner-right {
        position: absolute;
        top: 20px;
        right: 20px;
        padding-top: 0;
    }

    .modfolio-store-banner .social-icons a {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .modfolio-store-banner .request-shoot-btn {
        padding: 10px 18px;
        font-size: 13px;
    }
}

@media screen and (max-width: 480px) {
    .modfolio-store-banner .store-name {
        font-size: 20px;
    }

    .modfolio-store-banner .store-tagline {
        font-size: 12px;
    }

    .modfolio-store-banner .social-icons {
        gap: 8px;
    }

    .modfolio-store-banner .social-icons a {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}
</style>

<?php do_action( 'wcfmmp_store_before_bannar', $store_user->get_id() ); ?>

<div class="modfolio-store-banner">
    <div class="banner-bg"></div>
    <div class="banner-overlay"></div>

    <div class="banner-content">
        <div class="banner-left">
            <div class="store-name-wrapper">
                <h1 class="store-name"><?php echo esc_html( $store_name ); ?></h1>
                <?php
                // Check if vendor has a membership badge
                $has_badge = false;
                if ( function_exists( 'wcfm_membership_badge' ) ) {
                    $has_badge = true;
                }
                ?>
                <span class="verified-badge"><i class="wcfmfa fa-check"></i></span>
            </div>
            <?php if ( $tagline ) : ?>
                <p class="store-tagline"><?php echo esc_html( $tagline ); ?></p>
            <?php endif; ?>
        </div>

        <div class="banner-right">
            <?php if ( $social_linkedin || $social_instagram || $social_fb || $social_twitter ) : ?>
                <div class="social-icons">
                    <?php if ( $social_linkedin ) : ?>
                        <a href="<?php echo esc_url( wcfmmp_generate_social_url( $social_linkedin, 'linkedin' ) ); ?>" target="_blank" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ( $social_instagram ) : ?>
                        <a href="<?php echo esc_url( wcfmmp_generate_social_url( $social_instagram, 'instagram' ) ); ?>" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ( $social_fb ) : ?>
                        <a href="<?php echo esc_url( wcfmmp_generate_social_url( $social_fb, 'facebook' ) ); ?>" target="_blank" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ( $social_twitter ) : ?>
                        <a href="<?php echo esc_url( wcfmmp_generate_social_url( $social_twitter, 'twitter' ) ); ?>" target="_blank" title="Twitter/X">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( apply_filters( 'wcfm_is_pref_enquiry', true ) && wcfm_vendor_has_capability( $vendor_id, 'enquiry' ) ) : ?>
                <a href="#" class="request-shoot-btn wcfm_catalog_enquiry" data-store="<?php echo esc_attr( $vendor_id ); ?>">
                    Request Custom Shoots
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php do_action( 'wcfmmp_store_after_bannar', $store_user->get_id() ); ?>
