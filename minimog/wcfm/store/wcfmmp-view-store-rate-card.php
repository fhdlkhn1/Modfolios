<?php
/**
 * Store Rate Card Tab Template
 * Displays vendor's rate card services/packages for customers
 * Design matched to Figma specifications with gradient cards
 */

defined('ABSPATH') || exit;

global $WCFM, $WCFMmp;

// Get vendor info
$vendor_id = 0;
if ( isset( $store_user ) && $store_user ) {
    if ( is_object( $store_user ) && method_exists( $store_user, 'get_id' ) ) {
        $vendor_id = (int) $store_user->get_id();
    } elseif ( is_object( $store_user ) && isset( $store_user->ID ) ) {
        $vendor_id = (int) $store_user->ID;
    } else {
        $vendor_id = (int) $store_user;
    }
}

if ( ! $vendor_id && ! empty( $store_info ) && is_array( $store_info ) ) {
    if ( ! empty( $store_info['vendor_id'] ) ) {
        $vendor_id = (int) $store_info['vendor_id'];
    } elseif ( ! empty( $store_info['id'] ) ) {
        $vendor_id = (int) $store_info['id'];
    }
}

// Get vendor services (rate cards)
$services = get_user_meta( $vendor_id, '_vendor_rate_card_services', true );
$services = is_array( $services ) ? $services : array();

// Get currency symbol
$currency_symbol = get_woocommerce_currency_symbol();

// Get vendor store name
$store_name = '';
if ( isset( $store_user ) && is_object( $store_user ) && method_exists( $store_user, 'get_shop_name' ) ) {
    $store_name = $store_user->get_shop_name();
}

// Get vendor/creator name
$creator_name = '';
$vendor_user = get_user_by( 'id', $vendor_id );
if ( $vendor_user ) {
    $creator_name = $vendor_user->display_name;
}
?>

<div class="wcfm-store-rate-card-wrap">

    <?php if ( empty( $services ) ) : ?>
        <div class="rate-card-empty">
            <div class="notice-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
            </div>
            <h2 class="notice-title"><?php esc_html_e( 'No services Yet', 'your-textdomain' ); ?></h2>
            <p class="notice-description">
                <?php
                printf(
                    /* translators: %s: creator name */
                    esc_html__( '%s hasn\'t created any service yet. Check back soon for specific services.', 'your-textdomain' ),
                    '<span class="creator-name">' . esc_html( $creator_name ) . '</span>'
                );
                ?>
            </p>
        </div>
    <?php else : ?>
        <div class="rate-card-packages-grid">
            <?php foreach ( $services as $index => $service ) : ?>
                <div class="rate-card-package">
                    <div class="package-content">
                        <h3 class="package-title"><?php echo esc_html( $service['name'] ); ?></h3>
                        <p class="package-description"><?php echo esc_html( $service['description'] ); ?></p>
                        <div class="package-price">
                            <span class="price-amount"><?php echo esc_html( $currency_symbol . number_format( $service['base_rate'], 0 ) ); ?></span>
                            <?php if ( ! empty( $service['turnaround'] ) ) : ?>
                                <span class="price-unit">/ <?php echo esc_html( strtolower( $service['turnaround'] ) ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<style>
/* Rate Card Store Tab - Figma Design */
.wcfm-store-rate-card-wrap {
    /* max-width: 1200px; */
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 0;
}

/* Packages Grid */
.rate-card-packages-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

@media (max-width: 768px) {
    .rate-card-packages-grid {
        grid-template-columns: 1fr;
    }
}

/* Package Card */
.rate-card-package {
    /* background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 40%, rgba(87, 239, 228, 0.3) 100%); */
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 50%, #0CCAAE 140%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.rate-card-package:hover {
    /* transform: translateY(-4px); */
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.package-content {
    padding: 32px 24px;
    text-align: center;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Package Title */
.package-title {
    font-size: 24px;
    font-weight: 800;
    color: #00C4AA;
    margin: 0 0 12px 0;
}

/* Package Description */
.package-description {
    font-size: 16px;
    color: #1a1a1a;
    line-height: 1.6;
    margin: 0 0 20px 0;
    max-width: 80%;
    /* flex-grow: 1; */
    margin-left: auto !important;
    margin-right: auto !important;
    margin-bottom: 20px !important;
}

/* Package Price */
.package-price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 8px;
}

.price-amount {
    font-size: 42px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1;
}

.price-unit {
    font-size: 16px;
    color: #666;
    font-weight: 400;
}

/* Empty State */
.rate-card-empty {
    text-align: center;
    padding: 60px 20px;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 50%, #0CCAAE 140%);
    border-radius: 20px;
}

/* .rate-card-empty p {
    font-size: 18px;
    margin: 0;
    color: #1a1a1a;
    font-weight: 700;
} */
#wcfmmp-store h2.notice-title,
.rate-card-empty .notice-title {
    font-size: 26px !important;
    font-weight: 700 !important;
    color: #000 !important;
    margin-bottom: 12px;
}

.rate-card-empty .notice-description {
    font-size: 16px;
    color: #6c757d;
    line-height: 1.6 !important;
    margin-bottom: 25px !important;
}

.rate-card-empty .notice-description .creator-name {
    font-weight: 600;
    color: #1a1a2e;
    text-transform: capitalize;
}


.rate-card-empty .notice-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, #00c4aa 0%, #00a896 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rate-card-empty .notice-icon svg {
    width: 35px;
    height: 35px;
    fill: #ffffff;
}
</style>
