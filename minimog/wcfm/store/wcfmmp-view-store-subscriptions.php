<?php
/**
 * Store Subscriptions Tab Template
 * Displays Basic and Premium subscription plans for the vendor
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

// Get vendor settings
$vendor_settings = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
$vendor_settings = is_array( $vendor_settings ) ? $vendor_settings : [];

// Get subscription product IDs
$basic_product_id = (int) get_user_meta( $vendor_id, 'wss_basic_product_id', true );
$elite_product_id = (int) get_user_meta( $vendor_id, 'wss_elite_product_id', true );

// Get prices from settings or defaults
$basic_price = isset( $vendor_settings['wss_basic_price'] ) ? floatval( $vendor_settings['wss_basic_price'] ) : 9;
$elite_price = isset( $vendor_settings['wss_elite_price'] ) ? floatval( $vendor_settings['wss_elite_price'] ) : 19;

// Get vendor store name
$store_name = '';
if ( isset( $store_user ) && is_object( $store_user ) && method_exists( $store_user, 'get_shop_name' ) ) {
    $store_name = $store_user->get_shop_name();
}

// Check if current user is the vendor (viewing own store)
$current_user_id = get_current_user_id();
$is_own_store = ( $current_user_id && $current_user_id === $vendor_id );

// Check if current user is already subscribed
$is_subscribed = false;
$current_subscription_type = '';

if ( $current_user_id && function_exists( 'wcfm_store_sub_is_subscriber' ) ) {
    $is_subscribed = wcfm_store_sub_is_subscriber( $current_user_id, $vendor_id );
}

// Check which subscription type user has
if ( $is_subscribed && function_exists( 'wcs_get_users_subscriptions' ) ) {
    $subscriptions = wcs_get_users_subscriptions( $current_user_id );
    foreach ( $subscriptions as $subscription ) {
        if ( ! is_a( $subscription, 'WC_Subscription' ) ) continue;
        if ( ! in_array( $subscription->get_status(), [ 'active', 'pending-cancel' ], true ) ) continue;

        foreach ( $subscription->get_items() as $item ) {
            $pid = (int) $item->get_product_id();
            if ( $pid === $basic_product_id ) {
                $current_subscription_type = 'basic';
                break 2;
            } elseif ( $pid === $elite_product_id ) {
                $current_subscription_type = 'elite';
                break 2;
            }
        }
    }
}

// Currency symbol
$currency_symbol = get_woocommerce_currency_symbol();
?>

<div class="wcfm-store-subscriptions-wrap">

    <div class="subscription-cards-grid">

        <!-- Basic Plan -->
        <div class="subscription-card <?php echo ( $current_subscription_type === 'basic' ) ? 'subscription-card-active' : ''; ?>">
            <div class="subscription-card-content">
                <h3 class="subscription-tier-name"><?php _e( 'Basic', 'wc-multivendor-marketplace' ); ?></h3>
                <div class="subscription-price">
                    <span class="price-currency"><?php echo esc_html( $currency_symbol ); ?></span>
                    <span class="price-amount"><?php echo number_format( $basic_price, 0 ); ?></span>
                    <span class="price-period"><?php _e( '/month', 'wc-multivendor-marketplace' ); ?></span>
                </div>
                <ul class="subscription-benefits">
                    <li><span class="benefit-bullet"></span><?php _e( '10% Discount on all purchase', 'wc-multivendor-marketplace' ); ?></li>
                </ul>
                <div class="subscription-action">
                    <?php if ( $is_own_store ) : ?>
                        <span class="subscribe-btn subscribe-btn-disabled"><?php _e( 'Subscribe', 'wc-multivendor-marketplace' ); ?></span>
                    <?php elseif ( $current_subscription_type === 'basic' ) : ?>
                        <span class="subscribed-badge"><?php _e( 'Subscribed', 'wc-multivendor-marketplace' ); ?></span>
                    <?php elseif ( $basic_product_id ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'add-to-cart', $basic_product_id, wc_get_checkout_url() ) ); ?>" class="subscribe-btn">
                            <?php _e( 'Subscribe', 'wc-multivendor-marketplace' ); ?>
                        </a>
                    <?php else : ?>
                        <span class="unavailable-text"><?php _e( 'Not available', 'wc-multivendor-marketplace' ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Premium Plan -->
        <div class="subscription-card <?php echo ( $current_subscription_type === 'elite' ) ? 'subscription-card-active' : ''; ?>">
            <div class="subscription-card-content">
                <h3 class="subscription-tier-name"><?php _e( 'Premium', 'wc-multivendor-marketplace' ); ?></h3>
                <div class="subscription-price">
                    <span class="price-currency"><?php echo esc_html( $currency_symbol ); ?></span>
                    <span class="price-amount"><?php echo number_format( $elite_price, 0 ); ?></span>
                    <span class="price-period"><?php _e( '/month', 'wc-multivendor-marketplace' ); ?></span>
                </div>
                <ul class="subscription-benefits">
                    <li><span class="benefit-bullet"></span><?php _e( '10% Discount on all purchase', 'wc-multivendor-marketplace' ); ?></li>
                    <li><span class="benefit-bullet"></span><?php _e( 'Exclusive Content Assess', 'wc-multivendor-marketplace' ); ?> <span class="benefit-note"><?php _e( '(Photos/Videos)', 'wc-multivendor-marketplace' ); ?></span></li>
                </ul>
                <div class="subscription-action">
                    <?php if ( $is_own_store ) : ?>
                        <span class="subscribe-btn subscribe-btn-disabled"><?php _e( 'Subscribe', 'wc-multivendor-marketplace' ); ?></span>
                    <?php elseif ( $current_subscription_type === 'elite' ) : ?>
                        <span class="subscribed-badge"><?php _e( 'Subscribed', 'wc-multivendor-marketplace' ); ?></span>
                    <?php elseif ( $elite_product_id ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'add-to-cart', $elite_product_id, wc_get_checkout_url() ) ); ?>" class="subscribe-btn">
                            <?php _e( 'Subscribe', 'wc-multivendor-marketplace' ); ?>
                        </a>
                    <?php else : ?>
                        <span class="unavailable-text"><?php _e( 'Not available', 'wc-multivendor-marketplace' ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <?php if ( $is_own_store ) : ?>
        <div class="own-store-notice">
            <span class="own-store-notice-icon">&#9432;</span>
            <?php _e( 'You are viewing your own store. You cannot subscribe to your own plans.', 'wc-multivendor-marketplace' ); ?>
        </div>
    <?php endif; ?>

</div>

<style>
/* Store Subscriptions Tab - Figma Design */
.wcfm-store-subscriptions-wrap {
    /* max-width: 1000px; */
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 0;
}

/* Subscription Cards Grid */
.subscription-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

@media (max-width: 768px) {
    .subscription-cards-grid {
        grid-template-columns: 1fr;
    }
}

/* Subscription Card */
.subscription-card {
    /* background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 50%, rgba(87, 239, 228, 0.35) 100%); */
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 50%, #0CCAAE 140%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.subscription-card:hover {
    /* transform: translateY(-4px); */
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
}

.subscription-card-active {
    border: 2px solid #00C4AA;
}

.subscription-card-content {
    padding: 40px 32px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 320px;
}

/* Tier Name */
.subscription-tier-name {
    font-size: 18px;
    font-weight: 600;
    color: #00C4AA;
    margin: 0 0 8px 0;
}

/* Subscription Price */
.subscription-price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    margin-bottom: 24px;
    font-size: 45px;
    font-weight: 800 !important;
    background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    background-clip: text;
     background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
}

.price-currency {
    /* font-size: 24px; */
    /* font-weight: 700; */
    /* color: #00C4AA; */
    color: transparent;
    margin-right: 2px;
}

.price-amount {
    /* font-size: 48px;
    font-weight: 700; */
    /* color: #00C4AA; */
    color: transparent;
    line-height: 1;
}

.price-period {
    /* font-size: 16px; 
    font-weight: 600;*/
    /* color: #00C4AA; */
    color: transparent;
    margin-left: 4px;
}

/* Subscription Benefits */
.subscription-benefits {
    list-style: none;
    padding: 0;
    margin: 0 0 32px 0;
    text-align: left;
    width: 100%;
}

.subscription-benefits li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 14px;
    color: #1a1a1a;
    margin-bottom: 12px !important;
    line-height: 1.4;
    justify-content: center;
    padding-left: none !important;
    font-weight: 600 !important;
}

.subscription-benefits li:last-child {
    margin-bottom: 0;
}

.benefit-bullet {
    width: 6px;
    height: 6px;
    min-width: 6px;
    background: #1a1a1a;
    border-radius: 50%;
    margin-top: 6px;
}

.benefit-note {
    color: #666;
}

/* Subscription Action */
.subscription-action {
    margin-top: auto;
    width: 100%;
}

.subscribe-btn {
    display: inline-block;
    width: 100%;
    max-width: 200px;
    padding: 14px 32px;
    background: #00C4AA;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    border-radius: 50px;
    transition: all 0.2s ease, transform 0.2s ease;
    box-shadow: 0 4px 12px rgba(0, 196, 170, 0.3);
}

.subscribe-btn:hover {
    background: #00b39b;
    /* background: linear-gradient(135deg, #00BFA6 0%, #007BFF 100%); */
    color: #fff;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 196, 170, 0.4);
}

.subscribed-badge {
    display: inline-block;
    padding: 14px 32px;
    background: #e8f8f5;
    color: #00C4AA;
    font-size: 16px;
    font-weight: 600;
    border-radius: 50px;
    border: 2px solid #00C4AA;
}

.unavailable-text {
    display: inline-block;
    font-size: 14px;
    color: #999;
}

/* Disabled subscribe button (own store) */
.subscribe-btn-disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Own store notice */
.own-store-notice {
    text-align: center;
    padding: 14px 20px;
    margin-top: 20px;
    background: #f0f9f7;
    border: 1px solid #d0ebe6;
    border-radius: 10px;
    color: #555;
    font-size: 14px;
}

.own-store-notice-icon {
    margin-right: 6px;
    font-size: 16px;
}
</style>
