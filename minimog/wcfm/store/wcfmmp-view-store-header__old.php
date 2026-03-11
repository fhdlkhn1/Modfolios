<?php
/**
 * The Template for displaying store header - Figma Design
 *
 * @package WCfM Markeplace Views Store Header
 *
 * Theme override for Modfolio design
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $WCFM, $WCFMmp;

$vendor_id = $store_user->get_id();
$gravatar = $store_user->get_avatar();
$address  = $store_user->get_address_string();

// Get vendor profile settings
$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
if ( ! is_array( $vendor_data ) ) {
    $vendor_data = array();
}

$about = isset( $vendor_data['about'] ) ? $vendor_data['about'] : '';

// Get review stats using database query
$review_count = 0;
$avg_rating = 0;
if ( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) && apply_filters( 'wcfm_is_allow_review_rating', true ) ) {
    global $wpdb;
    $reviews_table = $wpdb->prefix . 'wcfm_marketplace_reviews';

    // Check if table exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$reviews_table'" ) == $reviews_table ) {
        $review_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT COUNT(*) as count, AVG(review_rating) as avg_rating
             FROM {$reviews_table}
             WHERE vendor_id = %d AND approved = 1",
            $vendor_id
        ) );

        if ( $review_data ) {
            $review_count = intval( $review_data->count );
            $avg_rating = $review_data->avg_rating ? floatval( $review_data->avg_rating ) : 0;
        }
    }
}

// Get followers count
$followers_count = 0;
if ( function_exists( 'wcfm_get_user_meta' ) ) {
    $followers_count = wcfm_get_user_meta( $vendor_id, '_wcfmmp_follower_count', true );
    if ( ! $followers_count ) {
        $followers_count = 0;
    }
}

// Get product count for "120+" style display
$product_count = count_user_posts( $vendor_id, 'product', true );

// Format location - show city, country only
$location_parts = array();
if ( isset( $store_info['store_city'] ) && ! empty( $store_info['store_city'] ) ) {
    $location_parts[] = $store_info['store_city'];
}
if ( isset( $store_info['store_state'] ) && ! empty( $store_info['store_state'] ) ) {
    $location_parts[] = $store_info['store_state'];
}
if ( isset( $store_info['store_country'] ) && ! empty( $store_info['store_country'] ) ) {
    $countries = WC()->countries->get_countries();
    $country_name = isset( $countries[ $store_info['store_country'] ] ) ? $countries[ $store_info['store_country'] ] : $store_info['store_country'];
    $location_parts[] = $country_name;
}
$location_display = implode( ', ', $location_parts );
if ( empty( $location_display ) && $address ) {
    $location_display = $address;
}

?>

<style>
/* Modfolio Store Header Styles */
.modfolio-store-header {
    position: relative;
    margin-top: -60px;
    padding: 0 40px;
    z-index: 10;
}

.modfolio-store-header .header-row {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 24px;
}

.modfolio-store-header .avatar-wrapper {
    flex-shrink: 0;
}

.modfolio-store-header .avatar-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    object-fit: cover;
    background: #fff;
}

.modfolio-store-header .info-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 12px;
    flex-wrap: wrap;
}

.modfolio-store-header .location-info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #666;
}

.modfolio-store-header .location-info i {
    color: #00c4aa;
}

.modfolio-store-header .rating-info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #666;
}

.modfolio-store-header .rating-info .star {
    color: #f5a623;
    font-size: 14px;
}

.modfolio-store-header .rating-info .rating-value {
    font-weight: 600;
    color: #1a1a1a;
}

.modfolio-store-header .rating-info .review-count {
    color: #888;
}

.modfolio-store-header .portfolio-count {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
}

.modfolio-store-header .info-separator {
    width: 1px;
    height: 16px;
    background: #ddd;
}

/* About Me Section */
.modfolio-about-section {
    padding: 0 40px 30px;
}

.modfolio-about-section .about-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 16px;
    background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.modfolio-about-section .about-content {
    font-size: 14px;
    line-height: 1.7;
    color: #555;
    max-width: 900px;
}

.modfolio-about-section .about-content p {
    margin-bottom: 12px;
}

.modfolio-about-section .about-content p:last-child {
    margin-bottom: 0;
}

/* Mobile Responsive */
@media screen and (max-width: 768px) {
    .modfolio-store-header {
        margin-top: -50px;
        padding: 0 20px;
    }

    .modfolio-store-header .avatar-img {
        width: 100px;
        height: 100px;
    }

    .modfolio-store-header .info-row {
        gap: 12px;
    }

    .modfolio-about-section {
        padding: 0 20px 24px;
    }

    .modfolio-about-section .about-title {
        font-size: 20px;
    }
}

@media screen and (max-width: 480px) {
    .modfolio-store-header {
        margin-top: -40px;
        padding: 0 16px;
    }

    .modfolio-store-header .header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .modfolio-store-header .avatar-img {
        width: 80px;
        height: 80px;
    }

    .modfolio-store-header .info-row {
        flex-wrap: wrap;
        gap: 8px;
    }

    .modfolio-store-header .location-info,
    .modfolio-store-header .rating-info,
    .modfolio-store-header .portfolio-count {
        font-size: 13px;
    }

    .modfolio-about-section {
        padding: 0 16px 20px;
    }

    .modfolio-about-section .about-title {
        font-size: 18px;
    }

    .modfolio-about-section .about-content {
        font-size: 13px;
    }
}
</style>

<?php do_action( 'wcfmmp_store_before_header', $vendor_id ); ?>

<div id="wcfm_store_header" class="modfolio-store-header">
    <div class="header-row">
        <div class="avatar-wrapper">
            <img src="<?php echo esc_url( $gravatar ); ?>" alt="<?php echo esc_attr( $store_info['store_name'] ); ?>" class="avatar-img" />
        </div>

        <div class="info-row">
            <?php if ( $location_display && ( $store_info['store_hide_address'] == 'no' ) && wcfm_vendor_has_capability( $vendor_id, 'vendor_address' ) ) : ?>
                <div class="location-info">
                    <?php echo esc_html( $location_display ); ?>
                </div>
                <span class="info-separator"></span>
            <?php endif; ?>

            <?php if ( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) && apply_filters( 'wcfm_is_allow_review_rating', true ) ) : ?>
                <div class="rating-info">
                    <span class="star"><i class="wcfmfa fa-star"></i></span>
                    <span class="rating-value"><?php echo number_format( $avg_rating, 1 ); ?></span>
                    <span class="review-count">(<?php echo esc_html( $review_count ); ?>)</span>
                </div>
                <span class="info-separator"></span>
            <?php endif; ?>

            <div class="portfolio-count">
                <?php echo esc_html( $product_count ); ?>+
            </div>
        </div>
    </div>
</div>

<?php if ( $about ) : ?>
<div class="modfolio-about-section">
    <h2 class="about-title"><?php _e( 'About Me', 'wc-frontend-manager' ); ?></h2>
    <div class="about-content">
        <?php echo wp_kses_post( wpautop( $about ) ); ?>
    </div>
</div>
<?php endif; ?>

<?php do_action( 'wcfmmp_store_after_header', $vendor_id ); ?>
