<?php
/**
 * WCFM Services & Subscriptions View
 * Displays rate card services and fan subscription settings for vendor
 * Design matched to Figma specifications
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $wpdb;

// Get current vendor ID
$vendor_id = get_current_user_id();

/* -------------------------------------------------------------
 *  Check if vendor has Pro or Elite membership
 * ------------------------------------------------------------- */
$allowed_membership_ids = array( 21878, 21879 ); // Pro, Elite (Basic 2187 excluded)
$subscription_status = get_user_meta( $vendor_id, 'wcfm_subscription_status', true );
$membership_id = (int) get_user_meta( $vendor_id, 'wcfm_membership', true );
$has_pro_or_elite = ( $subscription_status === 'active' && in_array( $membership_id, $allowed_membership_ids, true ) );

$has_pro_or_elite = true;

// If vendor doesn't have Pro or Elite membership, show upgrade prompt
if ( ! $has_pro_or_elite ) :
?>
<div class="collapse wcfm-collapse" id="wcfm_services_subscriptions">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-credit-card"></span>
        <span class="wcfm-page-heading-text"><?php _e('Services & Subscriptions', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <style>
        .membership-upgrade-box {
            max-width: 500px;
            margin: 60px auto;
            padding: 50px 40px;
            text-align: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);

            max-width: 100%;
            margin: 2px;
            background: #fff;
            /* box-shadow: 0 0 6px rgba(0, 0, 0, 0.1); */
            box-shadow: none;
            border: none;
        }

        .membership-upgrade-box .upgrade-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #00c4aa 0%, #00a896 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .membership-upgrade-box .upgrade-icon svg {
            width: 40px;
            height: 40px;
            fill: #ffffff;
        }

        .membership-upgrade-box .upgrade-icon::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            border-radius: 50%;
            display: none !important;
        }

        .membership-upgrade-box .upgrade-icon::before {
            content: '\26A1';
            position: absolute;
            bottom: 2px;
            right: 2px;
            font-size: 14px;
            z-index: 1;
            display: none !important;
        }

        .membership-upgrade-box .upgrade-title {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;

            display: block !important;
            font-style: normal !important;
            color: #1a1a1a;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            float: none !important;
        }

        .membership-upgrade-box .upgrade-description {
            font-size: 15px;
            /* color: rgba(255, 255, 255, 0.7); */
            line-height: 1.7;
            margin-bottom: 30px;
            color: #1a1a1a;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .membership-upgrade-box .upgrade-description .highlight {
            color: #00c4aa;
            font-weight: 600;
        }

        .membership-upgrade-box .upgrade-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            /* background: linear-gradient(135deg, #00c4aa 0%, #00a896 100%); */
            background: #00c4aa;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            /* box-shadow: 0 4px 20px rgba(0, 196, 170, 0.4); */
        }

        .membership-upgrade-box .upgrade-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 196, 170, 0.4);
            color: #ffffff;
            text-decoration: none;
        }

        .membership-upgrade-box .upgrade-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
            transition: transform 0.3s ease;
        }

        .membership-upgrade-box .upgrade-btn:hover svg {
            transform: translateX(4px);
        }
        </style>

        <div class="membership-upgrade-box">
            <div class="upgrade-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
            </div>
            <h2 class="upgrade-title"><?php _e( 'Unlock Services & Subscriptions', 'vendor-customshot' ); ?></h2>
            <p class="upgrade-description">
                <?php _e( 'Create rate cards and offer fan subscriptions to build recurring revenue! This feature is available for', 'vendor-customshot' ); ?>
                <span class="highlight"><?php _e( 'Pro and Elite members', 'vendor-customshot' ); ?></span>.
            </p>
            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="upgrade-btn">
                <?php _e( 'Upgrade Your Plan', 'vendor-customshot' ); ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
<?php
return;
endif;

/* -------------------------------------------------------------
 *  Vendor has Pro/Elite - Show normal content
 * ------------------------------------------------------------- */

// Get vendor settings
$vendor_settings = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
$vendor_settings = is_array($vendor_settings) ? $vendor_settings : array();

// Get subscription settings
$subscriptions_enabled = get_user_meta($vendor_id, '_wss_subscriptions_enabled', true);
$basic_price = get_user_meta($vendor_id, 'wss_basic_price', true);
$elite_price = get_user_meta($vendor_id, 'wss_elite_price', true);
$basic_product_id = get_user_meta($vendor_id, 'wss_basic_product_id', true);
$elite_product_id = get_user_meta($vendor_id, 'wss_elite_product_id', true);

// Default prices
$basic_price = $basic_price ? floatval($basic_price) : 4.99;
$elite_price = $elite_price ? floatval($elite_price) : 14.99;

// Get vendor services (rate cards)
$services = get_user_meta($vendor_id, '_vendor_rate_card_services', true);
$services = is_array($services) ? $services : array();

// Get currency symbol
$currency_symbol = get_woocommerce_currency_symbol();
?>

<div class="collapse wcfm-collapse wcfm_services_subscriptions" id="wcfm_services_subscriptions">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-credit-card"></span>
        <span class="wcfm-page-heading-text"><?php _e('Services & Subscriptions', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <?php
		// Use the reusable header function
		// $breadcrumb_items = array(
		// 	array( 'label' => __('Portfolio', 'wc-frontend-manager'), 'url' => '' ),
		// 	array( 'label' => isset($custom_menu_labels[$product_status]) ? $custom_menu_labels[$product_status] : 'All', 'url' => '' )
		// );
		//modfolio_wcfm_render_header( '' );
		?>

        <!-- Rate Card Section -->
        <div class="wcfm-container wcfm-top-element-container rate-card-section">
            <div class="section-header-row">
                <div class="section-header-left">
                    <h2 class="section-title"><?php _e('Rate Card', 'vendor-customshot'); ?></h2>
                    <p class="section-description"><?php _e('Define your standard services and price for direct booking', 'vendor-customshot'); ?></p>
                </div>
                <div class="section-header-right">
                    <button type="button" class="add-services-btn" id="add-service-btn">
                        <span class="btn-icon">+</span>
                        <span class="btn-text"><?php _e('Add Services', 'vendor-customshot'); ?></span>
                    </button>
                </div>
            </div>

            <div class="rate-cards-grid" id="services-list">
                <?php if (!empty($services)) : ?>
                    <?php foreach ($services as $index => $service) : ?>
                        <div class="rate-card" data-service-index="<?php echo esc_attr($index); ?>">
                            <div class="rate-card-actions">
                                <span type="button" class="card-action-btn edit-service" data-index="<?php echo esc_attr($index); ?>" title="<?php esc_attr_e('Edit', 'vendor-customshot'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M11.333 2.00004C11.5081 1.82494 11.7169 1.68605 11.9471 1.59129C12.1773 1.49653 12.4244 1.44775 12.6737 1.44775C12.9231 1.44775 13.1701 1.49653 13.4003 1.59129C13.6305 1.68605 13.8394 1.82494 14.0145 2.00004C14.1896 2.17513 14.3285 2.384 14.4232 2.61419C14.518 2.84439 14.5668 3.09143 14.5668 3.34079C14.5668 3.59015 14.518 3.83719 14.4232 4.06739C14.3285 4.29758 14.1896 4.50645 14.0145 4.68154L5.00004 13.697L1.33337 14.667L2.30337 11.0003L11.333 2.00004Z" stroke="#00C4AA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                                <span type="button" class="card-action-btn delete-service" data-index="<?php echo esc_attr($index); ?>" title="<?php esc_attr_e('Delete', 'vendor-customshot'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2 4H3.33333H14" stroke="#FF6B6B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M5.33337 4.00004V2.66671C5.33337 2.31309 5.47385 1.97395 5.72389 1.7239C5.97395 1.47385 6.31309 1.33337 6.66671 1.33337H9.33337C9.687 1.33337 10.0261 1.47385 10.2762 1.7239C10.5262 1.97395 10.6667 2.31309 10.6667 2.66671V4.00004M12.6667 4.00004V13.3334C12.6667 13.687 12.5262 14.0261 12.2762 14.2762C12.0261 14.5262 11.687 14.6667 11.3334 14.6667H4.66671C4.31309 14.6667 3.97395 14.5262 3.7239 14.2762C3.47385 14.0261 3.33337 13.687 3.33337 13.3334V4.00004H12.6667Z" stroke="#FF6B6B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            <div class="rate-card-content">
                                <div class="rate-card-row">
                                    <span class="row-label"><?php _e('Service Name', 'vendor-customshot'); ?></span>
                                    <span class="row-value service-name-value"><?php echo esc_html($service['name']); ?></span>
                                </div>
                                <div class="rate-card-row">
                                    <span class="row-label"><?php _e('Base Rate', 'vendor-customshot'); ?></span>
                                    <span class="row-value"><?php echo esc_html($currency_symbol . number_format($service['base_rate'], 0)); ?></span>
                                </div>
                                <div class="rate-card-row">
                                    <span class="row-label"><?php _e('Turn Around Time', 'vendor-customshot'); ?></span>
                                    <span class="row-value"><?php echo esc_html($service['turnaround']); ?></span>
                                </div>
                                <div class="rate-card-row description-row">
                                    <span class="row-label"><?php _e('Description', 'vendor-customshot'); ?></span>
                                    <span class="row-value description-value"><?php echo esc_html($service['description']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- New Rate Card Form (Hidden by default, shown inline when adding) -->
        <template id="rate-card-template">
            <div class="rate-card rate-card-editing" data-service-index="-1">
                <div class="rate-card-actions">
                    <span type="button" class="card-action-btn save-service" title="<?php esc_attr_e('Save', 'vendor-customshot'); ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.3334 4L6.00004 11.3333L2.66671 8" stroke="#00C4AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span type="button" class="card-action-btn cancel-edit" title="<?php esc_attr_e('Cancel', 'vendor-customshot'); ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4L4 12M4 4L12 12" stroke="#FF6B6B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
                <div class="rate-card-content rate-card-form">
                    <div class="rate-card-row">
                        <span class="row-label"><?php _e('Service Name', 'vendor-customshot'); ?></span>
                        <input type="text" class="row-input service-name-input" name="service_name" placeholder="<?php esc_attr_e('e.g., Instagram Feed Post', 'vendor-customshot'); ?>" required>
                    </div>
                    <div class="rate-card-row">
                        <span class="row-label"><?php _e('Base Rate', 'vendor-customshot'); ?></span>
                        <div class="input-with-prefix">
                            <span class="input-prefix"><?php echo esc_html($currency_symbol); ?></span>
                            <input type="number" class="row-input service-rate-input" name="service_rate" min="0" step="1" placeholder="0" required>
                        </div>
                    </div>
                    <div class="rate-card-row">
                        <span class="row-label"><?php _e('Turn Around Time', 'vendor-customshot'); ?></span>
                        <input type="text" class="row-input service-turnaround-input" name="service_turnaround" placeholder="<?php esc_attr_e('e.g., 2 Days', 'vendor-customshot'); ?>" required>
                    </div>
                    <div class="rate-card-row">
                        <span class="row-label"><?php _e('Description', 'vendor-customshot'); ?></span>
                        <input type="text" class="row-input service-description-input" name="service_description" placeholder="<?php esc_attr_e('Brief description of the service', 'vendor-customshot'); ?>">
                    </div>
                </div>
            </div>
        </template>

        <!-- Fan Subscription Section -->
        <div class="wcfm-container wcfm-top-element-container fan-subscription-section">
            <div class="section-header-row">
                <div class="section-header-left">
                    <h2 class="section-title"><?php _e('Fan Subscription', 'vendor-customshot'); ?></h2>
                    <p class="section-description"><?php _e('Create recurring revenue by selling memberships to your fans.', 'vendor-customshot'); ?></p>
                </div>
                <div class="section-header-right">
                    <div class="toggle-wrapper">
                        <span class="toggle-label"><?php _e('Enable Subscription', 'vendor-customshot'); ?></span>
                        <label class="custom-toggle">
                            <input type="checkbox" id="subscriptions-toggle" <?php checked($subscriptions_enabled, 'yes'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="subscription-tiers-grid <?php echo $subscriptions_enabled == 'yes' ? 'visible' : ''; ?>" id="subscription-tiers" <?php echo $subscriptions_enabled !== 'yes' ? 'style="opacity: 0.5; pointer-events: none;"' : ''; ?>>
                <!-- Basic Tier -->
                <div class="subscription-tier-card">
                    <div class="tier-badge"><?php _e('Basic Tier', 'vendor-customshot'); ?></div>
                    <div class="tier-content">
                        <div class="tier-price-row">
                            <span class="tier-price-label"><?php _e('Monthly Price', 'vendor-customshot'); ?></span>
                            <div class="tier-price-input">
                                <span class="price-currency"><?php echo esc_html($currency_symbol); ?></span>
                                <input type="number" id="basic-price" name="basic_price" value="<?php echo esc_attr($basic_price); ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="tier-benefits">
                            <h4 class="benefits-title"><?php _e('Automatic Benifits', 'vendor-customshot'); ?></h4>
                            <ul class="benefits-list">
                                <li><span class="benefit-bullet"></span><?php _e('10% Discount', 'vendor-customshot'); ?> <span class="benefit-note"><?php _e('on all purchase', 'vendor-customshot'); ?></span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Premium Tier -->
                <div class="subscription-tier-card">
                    <div class="tier-badge"><?php _e('Premium Tier', 'vendor-customshot'); ?></div>
                    <div class="tier-content">
                        <div class="tier-price-row">
                            <span class="tier-price-label"><?php _e('Monthly Price', 'vendor-customshot'); ?></span>
                            <div class="tier-price-input">
                                <span class="price-currency"><?php echo esc_html($currency_symbol); ?></span>
                                <input type="number" id="elite-price" name="elite_price" value="<?php echo esc_attr($elite_price); ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="tier-benefits premium-tier-benefits">
                            <h4 class="benefits-title"><?php _e('Automatic Benifits', 'vendor-customshot'); ?></h4>
                            <ul class="benefits-list">
                                <li><span class="benefit-bullet"></span><?php _e('10% Discount', 'vendor-customshot'); ?> <span class="benefit-note"><?php _e('on all purchase', 'vendor-customshot'); ?></span></li>
                                <li><span class="benefit-bullet"></span><a href="javascript:Void(0);" class="benefit-link"><?php _e('Exclusive Content Assess', 'vendor-customshot'); ?></a> <span class="benefit-note"><?php _e('(Photos/Videos)', 'vendor-customshot'); ?></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="subscription-save-row">
                <button type="button" class="save-changes-btn" id="save-subscriptions-btn">
                    <?php _e('Save Changes', 'vendor-customshot'); ?>
                </button>
                <span class="save-status" id="save-status"></span>
            </div>
        </div>

    </div>
</div>

<style>
/* Services & Subscriptions - Figma Design */

.rate-card-section,
.fan-subscription-section {
    background: #fff;
    border-radius: 20px;
    /* padding: 24px; */
    margin-bottom: 24px !important;
    /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); */
    background: transparent !important;
    padding: 0 5px !important;
}

.wcfm-collapse .wcfm-container.rate-card-section,
.wcfm-collapse .wcfm-container.fan-subscription-section{
    background: transparent !important;
}


/* Section Header */
.section-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    margin-top: 10px;
}

.section-header-left {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: start;
    justify-content: center;
}

.section-title {
    /* font-size: 18px;
    font-weight: 700; */
    color: #1a1a1a !important;
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #000 !important;
    line-height: 1 !important;
    margin-bottom: 5px !important;
    font-style: normal !important;
    padding-top: 5px !important;
    margin-top: 0 !important;
}

.section-description {
    font-size: 11px;
    color: #262626;
    margin: 0;
    margin-bottom: 0px;
    transform: translateY(-7px);
}

/* Add Services Button */
.add-services-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #00C4AA;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.add-services-btn:hover {
    background: #00b39b;
}

.add-services-btn .btn-icon {
    font-size: 18px;
    font-weight: 700;
    line-height: 1;
}

/* Rate Cards Grid */
.rate-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .rate-cards-grid {
        grid-template-columns: 1fr;
    }
}

/* Rate Card */
.rate-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    position: relative;
    box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
}

.rate-card-editing {
    border: 2px solid #00C4AA;
}

.rate-card-actions {
    position: absolute;
    top: 12px;
    right: 20px;
    display: flex;
    gap: 8px;
}

.card-action-btn {
    background: #fff;
    border: none;
    border-radius: 6px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-action-btn:hover {
    background: #f5f5f5;
}

/* Rate Card Content */
.rate-card-content {
    padding-top: 35px;
}

.rate-card-row {
    /* display: flex;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06); */
    display: flex;
    align-items: center;
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border: none !important;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 16px;
    min-height: 57px;
    text-align: left !important;
}
.rate-card-row.description-row{
    align-items: start;
}
.rate-card-row.description-row .row-label{
    margin-top: 3px;
}

.rate-card-row:last-child {
    border-bottom: none;
}

.row-label {
    /* width: 140px;
    flex-shrink: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a; */
    font-size: 13px;
    font-weight: 700;
    color: #1a1a1a;
    padding: 10px 14px;
    white-space: nowrap;
    min-width: fit-content;
    width: 145px;
    position: relative;
    margin-right: 5px;
}
.row-label::after{
    content: '';
    position: absolute;
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    top: 16px;
    right: 6px;
    transform: translateY(-20%);
}

.row-value {
    flex: 1;
    font-size: 14px;
    color: #666;
}

.service-name-value {
    font-weight: 500;
    color: #1a1a1a;
}

.description-value {
    line-height: 1.5;
    padding: 15px;
    padding-left: 0;
}

/* Rate Card Form (Inline Editing) */
#wcfm-main-contentainer input.row-input,
.rate-card-form .row-input {
    /* flex: 1;
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    background: #fff;
    transition: border-color 0.2s ease; */

    flex: 1;
    border: none !important;
    background: transparent !important;
    padding: 10px 12px !important;
    font-size: 14px;
    /* color: #666; */
    color: #1a1a1a;
    min-width: 0;
    box-shadow: none !important;
}

.rate-card-form .row-input:focus {
    outline: none;
    border-color: #00C4AA;
}

.input-with-prefix {
    display: flex;
    align-items: center;
    flex: 1;
}

.input-prefix {
    background: #f5f5f5;
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    border-right: none;
    border-radius: 6px 0 0 6px;
    font-size: 14px;
    color: #666;

    background: transparent;
    border: none;
    font-weight: 500;
    padding-right: 0px;
    transform: translateY(1px);
}

.input-with-prefix .row-input {
    border-radius: 0 6px 6px 0;
}

/* Fan Subscription Section */
.toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toggle-label {
    font-size: 14px;
    color: #666;
}

/* Custom Toggle Switch */
.custom-toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
}

.custom-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 26px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.custom-toggle input:checked + .toggle-slider {
    background-color: #00C4AA;
}

.custom-toggle input:checked + .toggle-slider:before {
    transform: translateX(22px);
}

/* Subscription Tiers Grid */
.subscription-tiers-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
    max-height: 0;
    overflow: hidden;
}

@media (max-width: 768px) {
    .subscription-tiers-grid {
        grid-template-columns: 1fr;
    }
}


#subscription-tiers.visible{
    max-height: 2000px;
    overflow: visible;
}

/* Subscription Tier Card */
.subscription-tier-card {
    /* background: #FDF8F3; */
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
    border: none !important;
}

.tier-badge {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 10px;
    width: 100%;
    text-align: left;
    padding-left: 0;
    text-transform: capitalize;
    letter-spacing: 0px;
}

.tier-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Tier Price Row */
.tier-price-row {
    /* display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06); */

    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #e5e5e5;
    border: none !important;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 16px;
    min-height: 57px;
    text-align: left !important;
}

.tier-price-label {
    /* font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    width: 120px;
    flex-shrink: 0; */

    font-size: 13px;
    font-weight: 700;
    color: #1a1a1a;
    padding: 10px 14px;
    white-space: nowrap;
    min-width: fit-content;
    width: 145px;
    position: relative;
    margin-right: 5px;
}
.tier-price-label::after{
    content: '';
    position: absolute;
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    top: 16px;
    right: 6px;
    transform: translateY(-20%);
}

.tier-price-input {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
}

.price-currency {
    font-size: 14px;
    color: #666;
}

#wcfm-main-contentainer input#basic-price,
#wcfm-main-contentainer input#elite-price,
.tier-price-input input {
    /* width: 80px;
    padding: 6px 10px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    background: #fff; */

    flex: 1;
    border: none !important;
    background: transparent !important;
    padding: 10px 12px !important;
    font-size: 14px;
    color: #666;
    color: #1a1a1a;
    min-width: 0;
    box-shadow: none !important;
    padding-left: 2px !important;
}

.tier-price-input input:focus {
    /* outline: none;
    border-color: #00C4AA; */
}

/* Tier Benefits */
.tier-benefits {
    background: #F4FEFF;
    border-radius: 20px;
    padding: 20px 32px;
    box-shadow: 0 0 15px rgba(0,0,0,0.03);
}

.benefits-title {
    font-size: 14px;
    font-weight: 600;
    color: #00C4AA;
    margin: 0 0 12px 0;
    text-align: left;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
    margin-bottom: 20px;
}

.benefits-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #00C4AA;
    margin-bottom: 8px;
    margin: 0 !important;
}

.benefits-list li:last-child {
    margin-bottom: 0;
}

.benefit-bullet {
    width: 6px;
    height: 6px;
    background: #00C4AA;
    border-radius: 50%;
    flex-shrink: 0;
}

.benefit-note {
    color: #00C4AA;
}

.benefit-link {
    color: #00C4AA;
    text-decoration: underline;
}

.premium-tier-benefits{
    background: #F5F4FF;
}
.premium-tier-benefits *{
    /* color: #ACA3FF; */
    color: #877AFF !important;
}
.premium-tier-benefits .benefit-bullet{
    background: #877AFF !important;
}




/* Save Button */
.subscription-save-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 35px;
}

.save-changes-btn {
    background: #00C4AA;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.save-changes-btn:hover {
    background: #00b39b;
}

.save-status {
    font-size: 14px;
    color: #00C4AA;
}

/* Empty State */
.rate-cards-grid:empty::after {
    content: 'No services added yet. Click "Add Services" to create your first rate card.';
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 14px;
}




/* ==== Page Background ==== */

#wcfm-main-contentainer .wcfm-collapse.wcfm_services_subscriptions{
    background: #fff !important;
    padding: 20px;
    border-radius: 20px;
    overflow: hidden;
}
.dark__theme #wcfm-main-contentainer .wcfm-collapse.wcfm_services_subscriptions{
    background: #202020 !important;
}

.dark__theme .rate-card,
.dark__theme .subscription-tier-card{
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.1) !important;
}




</style>

<script type="text/javascript">
(function($) {
    'use strict';

    var services = <?php echo json_encode($services); ?>;
    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var nonce = '<?php echo wp_create_nonce('wcfm_services_subscriptions_nonce'); ?>';
    var currencySymbol = '<?php echo esc_js($currency_symbol); ?>';

    // Add new service card
    $('#add-service-btn').on('click', function() {
        var template = document.getElementById('rate-card-template');
        var clone = template.content.cloneNode(true);
        var $card = $(clone).find('.rate-card');

        // Add to grid
        $('#services-list').prepend(clone);

        // Focus first input
        $('#services-list .rate-card-editing:first .service-name-input').focus();
    });

    // Save new/edited service
    $(document).on('click', '.save-service', function() {

        var $btn = $(this);
        var originalIcon = $btn.html();
        
        var $card = $(this).closest('.rate-card');
        var index = $card.data('service-index');

        var name = $card.find('.service-name-input').val().trim();
        var rate = $card.find('.service-rate-input').val();
        var turnaround = $card.find('.service-turnaround-input').val().trim();
        var description = $card.find('.service-description-input').val().trim();

        if (!name || !rate || !turnaround) {
            alert('<?php echo esc_js(__('Please fill in all required fields.', 'vendor-customshot')); ?>');
            return;
        }

        // Replace icon with loader
        $btn
        .addClass('is-loading')
        .html('<i class="fas fa-spinner fa-spin" style="color:#00C4AA; font-size: 14px;"></i>');

        // AJAX save
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'wcfm_save_vendor_service',
                nonce: nonce,
                service_name: name,
                service_rate: rate,
                service_turnaround: turnaround,
                service_description: description,
                edit_index: index >= 0 ? index : ''
            },
            success: function(response) {
                if (response.success) {
                    services = response.data.services;
                    renderServices();
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error saving service.', 'vendor-customshot')); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error saving service. Please try again.', 'vendor-customshot')); ?>');
            },
            complete: function () {
                // Restore original SVG
                $btn
                    .removeClass('is-loading')
                    .html(originalIcon);
            }
        });
    });

    // Cancel editing
    $(document).on('click', '.cancel-edit', function() {
        var $card = $(this).closest('.rate-card');
        var index = $card.data('service-index');

        if (index < 0) {
            // New card, just remove
            $card.remove();
        } else {
            // Existing card, restore view mode
            renderServices();
        }
    });

    // Edit service
    $(document).on('click', '.edit-service', function() {
        var index = $(this).data('index');
        var service = services[index];

        var $card = $(this).closest('.rate-card');
        $card.addClass('rate-card-editing');
        $card.data('service-index', index);

        // Replace content with form
        $card.find('.rate-card-content').html(`
            <div class="rate-card-row">
                <span class="row-label"><?php _e('Service Name', 'vendor-customshot'); ?></span>
                <input type="text" class="row-input service-name-input" value="${escapeHtml(service.name)}" required>
            </div>
            <div class="rate-card-row">
                <span class="row-label"><?php _e('Base Rate', 'vendor-customshot'); ?></span>
                <div class="input-with-prefix">
                    <span class="input-prefix">${currencySymbol}</span>
                    <input type="number" class="row-input service-rate-input" value="${service.base_rate}" min="0" step="1" required>
                </div>
            </div>
            <div class="rate-card-row">
                <span class="row-label"><?php _e('Turn Around Time', 'vendor-customshot'); ?></span>
                <input type="text" class="row-input service-turnaround-input" value="${escapeHtml(service.turnaround)}" required>
            </div>
            <div class="rate-card-row">
                <span class="row-label"><?php _e('Description', 'vendor-customshot'); ?></span>
                <input type="text" class="row-input service-description-input" value="${escapeHtml(service.description || '')}">
            </div>
        `);

        // Replace actions
        $card.find('.rate-card-actions').html(`
            <span type="button" class="card-action-btn save-service" title="<?php esc_attr_e('Save', 'vendor-customshot'); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3334 4L6.00004 11.3333L2.66671 8" stroke="#00C4AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span type="button" class="card-action-btn cancel-edit" title="<?php esc_attr_e('Cancel', 'vendor-customshot'); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M12 4L4 12M4 4L12 12" stroke="#FF6B6B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        `);

        $card.find('.service-name-input').focus();
    });

    // Delete service
    $(document).on('click', '.delete-service', function() {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this service?', 'vendor-customshot')); ?>')) {
            return;
        }

        var index = $(this).data('index');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'wcfm_delete_vendor_service',
                nonce: nonce,
                service_index: index
            },
            success: function(response) {
                if (response.success) {
                    services = response.data.services;
                    renderServices();
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error deleting service.', 'vendor-customshot')); ?>');
                }
            }
        });
    });

    // Render services
    function renderServices() {
        var html = '';

        for (var i = 0; i < services.length; i++) {
            var s = services[i];
            html += `
                <div class="rate-card" data-service-index="${i}">
                    <div class="rate-card-actions">
                        <span type="button" class="card-action-btn edit-service" data-index="${i}" title="<?php esc_attr_e('Edit', 'vendor-customshot'); ?>">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M11.333 2.00004C11.5081 1.82494 11.7169 1.68605 11.9471 1.59129C12.1773 1.49653 12.4244 1.44775 12.6737 1.44775C12.9231 1.44775 13.1701 1.49653 13.4003 1.59129C13.6305 1.68605 13.8394 1.82494 14.0145 2.00004C14.1896 2.17513 14.3285 2.384 14.4232 2.61419C14.518 2.84439 14.5668 3.09143 14.5668 3.34079C14.5668 3.59015 14.518 3.83719 14.4232 4.06739C14.3285 4.29758 14.1896 4.50645 14.0145 4.68154L5.00004 13.697L1.33337 14.667L2.30337 11.0003L11.333 2.00004Z" stroke="#00C4AA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <span type="button" class="card-action-btn delete-service" data-index="${i}" title="<?php esc_attr_e('Delete', 'vendor-customshot'); ?>">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 4H3.33333H14" stroke="#FF6B6B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.33337 4.00004V2.66671C5.33337 2.31309 5.47385 1.97395 5.72389 1.7239C5.97395 1.47385 6.31309 1.33337 6.66671 1.33337H9.33337C9.687 1.33337 10.0261 1.47385 10.2762 1.7239C10.5262 1.97395 10.6667 2.31309 10.6667 2.66671V4.00004M12.6667 4.00004V13.3334C12.6667 13.687 12.5262 14.0261 12.2762 14.2762C12.0261 14.5262 11.687 14.6667 11.3334 14.6667H4.66671C4.31309 14.6667 3.97395 14.5262 3.7239 14.2762C3.47385 14.0261 3.33337 13.687 3.33337 13.3334V4.00004H12.6667Z" stroke="#FF6B6B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                    <div class="rate-card-content">
                        <div class="rate-card-row">
                            <span class="row-label"><?php _e('Service Name', 'vendor-customshot'); ?></span>
                            <span class="row-value service-name-value">${escapeHtml(s.name)}</span>
                        </div>
                        <div class="rate-card-row">
                            <span class="row-label"><?php _e('Base Rate', 'vendor-customshot'); ?></span>
                            <span class="row-value">${currencySymbol}${parseFloat(s.base_rate).toLocaleString()}</span>
                        </div>
                        <div class="rate-card-row">
                            <span class="row-label"><?php _e('Turn Around Time', 'vendor-customshot'); ?></span>
                            <span class="row-value">${escapeHtml(s.turnaround)}</span>
                        </div>
                        <div class="rate-card-row description-row">
                            <span class="row-label"><?php _e('Description', 'vendor-customshot'); ?></span>
                            <span class="row-value description-value">${escapeHtml(s.description || '')}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#services-list').html(html);
    }

    // Helper function
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Toggle subscriptions
    $('#subscriptions-toggle').on('change', function() {
        var enabled = $(this).is(':checked') ? 'yes' : 'no';

        if (enabled === 'yes') {
            $('#subscription-tiers').css({opacity: 1, 'pointer-events': 'auto'});
            $('#subscription-tiers').addClass('visible');
        } else {
            $('#subscription-tiers').css({opacity: 0.5, 'pointer-events': 'none'});
            $('#subscription-tiers').removeClass('visible');
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'wcfm_toggle_subscriptions',
                nonce: nonce,
                enabled: enabled
            }
        });
    });

    // Save subscription settings
    // $('#save-subscriptions-btn').on('click', function() {
    //     var $btn = $(this);
    //     var $status = $('#save-status');

    //     $btn.prop('disabled', true);
    //     $status.text('<?php echo esc_js(__('Saving...', 'vendor-customshot')); ?>');

    //     $.ajax({
    //         url: ajaxUrl,
    //         type: 'POST',
    //         data: {
    //             action: 'wcfm_save_subscription_settings',
    //             nonce: nonce,
    //             basic_price: $('#basic-price').val(),
    //             elite_price: $('#elite-price').val()
    //         },
    //         success: function(response) {
    //             $btn.prop('disabled', false);
    //             if (response.success) {
    //                 $status.text('<?php echo esc_js(__('Saved!', 'vendor-customshot')); ?>');
    //                 setTimeout(function() { $status.text(''); }, 3000);
    //             } else {
    //                 $status.text(response.data.message || '<?php echo esc_js(__('Error saving.', 'vendor-customshot')); ?>');
    //             }
    //         },
    //         error: function() {
    //             $btn.prop('disabled', false);
    //             $status.text('<?php echo esc_js(__('Error saving. Please try again.', 'vendor-customshot')); ?>');
    //         }
    //     });
    // });



    $('#save-subscriptions-btn').on('click', function () {
        var $btn = $(this);
        var $status = $('#save-status');
        var originalText = $btn.text();

        // Prevent double click
        if ($btn.hasClass('is-loading')) return;

        $btn
            .addClass('is-loading')
            .prop('disabled', true)
            .text('<?php echo esc_js(__('Saving...', 'vendor-customshot')); ?>');

        $status.text('').removeClass('error success');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'wcfm_save_subscription_settings',
                nonce: nonce,
                basic_price: $('#basic-price').val(),
                elite_price: $('#elite-price').val()
            },
            success: function (response) {
                if (response.success) {
                    $btn.text('<?php echo esc_js(__('Saved!', 'vendor-customshot')); ?>');

                    // Reload after 3 seconds
                    setTimeout(function () {
                        location.reload();
                    }, 1500);

                } else {
                    // Show error in status wrapper
                    $status
                        .addClass('error')
                        .text(response.data.message || '<?php echo esc_js(__('Error saving.', 'vendor-customshot')); ?>');

                    // Restore button
                    $btn
                        .removeClass('is-loading')
                        .prop('disabled', false)
                        .text(originalText);
                }
            },
            error: function () {
                // Show error in status wrapper
                $status
                    .addClass('error')
                    .text('<?php echo esc_js(__('Error saving. Please try again.', 'vendor-customshot')); ?>');

                // Restore button
                $btn
                    .removeClass('is-loading')
                    .prop('disabled', false)
                    .text(originalText);
            }
        });
    });


})(jQuery);
</script>