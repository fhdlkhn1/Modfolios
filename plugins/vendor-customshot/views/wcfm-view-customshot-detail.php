<?php
/**
 * WCFM Custom Shot Detail View
 * Displays single custom shot request details
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $wpdb;

$vendor_id = get_current_user_id();
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;

if (!$request_id) {
    echo '<p>' . __('Invalid request.', 'vendor-customshot') . '</p>';
    return;
}

// Get the custom shot
$shot = get_post($request_id);
if (!$shot || $shot->post_type !== 'customshot') {
    echo '<p>' . __('Custom shot request not found.', 'vendor-customshot') . '</p>';
    return;
}

// Verify this shot belongs to this vendor
$shot_vendor_id = get_post_meta($request_id, '_customshot_vendor_id', true);
if ($shot_vendor_id != $vendor_id) {
    echo '<p>' . __('You do not have permission to view this request.', 'vendor-customshot') . '</p>';
    return;
}

// Get all data
$buyer_id = get_post_meta($request_id, '_customshot_buyer_id', true);
$buyer = get_user_by('id', $buyer_id);
$buyer_name = $buyer ? $buyer->display_name : __('Unknown', 'vendor-customshot');
$buyer_email = $buyer ? $buyer->user_email : '';
$buyer_avatar = get_avatar_url($buyer_id, array('size' => 80));

// Get buyer's company/store name if available
$buyer_company = get_user_meta($buyer_id, 'billing_company', true);
if (empty($buyer_company)) {
    $buyer_company = get_user_meta($buyer_id, 'company', true);
}

$shoot_type_terms = wp_get_post_terms($request_id, 'shoot_type');
$shoot_type = !empty($shoot_type_terms) ? $shoot_type_terms[0]->name : '-';

$usage_type_terms = wp_get_post_terms($request_id, 'usage_type');
$usage_type = !empty($usage_type_terms) ? $usage_type_terms[0]->name : '-';

$brief = get_post_meta($request_id, '_customshot_brief', true);
$deliverables = get_post_meta($request_id, '_customshot_deliverables', true);
$budget = get_post_meta($request_id, '_customshot_budget', true);
$shoot_date = get_post_meta($request_id, '_customshot_shoot_date', true);
$status = get_post_meta($request_id, '_customshot_status', true);
$received_date = $shot->post_date;

// Get quote data if exists
$vendor_quote = get_post_meta($request_id, '_customshot_vendor_quote', true);
$vendor_note = get_post_meta($request_id, '_customshot_vendor_note', true);
$delivery_date = get_post_meta($request_id, '_customshot_delivery_date', true);
$quote_date = get_post_meta($request_id, '_customshot_quote_date', true);

$status_class = 'status-' . $status;
$status_labels = array(
    'pending'   => __('Pending', 'vendor-customshot'),
    'quoted'    => __('Quoted', 'vendor-customshot'),
    'accepted'  => __('Accepted', 'vendor-customshot'),
    'rejected'  => __('Declined', 'vendor-customshot'),
    'declined'  => __('Declined', 'vendor-customshot'),
    'completed' => __('Completed', 'vendor-customshot'),
);
$status_label = isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);

$base_url = get_wcfm_customshots_url();
?>

<div class="collapse wcfm-collapse" id="wcfm_customshot_detail">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-camera"></span>
        <span class="wcfm-page-heading-text"><?php _e('Custom Shoot Request Details', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <!-- Breadcrumb -->
        <div class="customshot-breadcrumb">
            <a href="<?php echo esc_url($base_url); ?>"><?php _e('Home', 'vendor-customshot'); ?></a>
            <span class="separator">&gt;</span>
            <a href="<?php echo esc_url($base_url); ?>"><?php _e('Dashboard', 'vendor-customshot'); ?></a>
            <span class="separator">&gt;</span>
            <a href="<?php echo esc_url($base_url); ?>"><?php _e('Portfolio', 'vendor-customshot'); ?></a>
            <span class="separator">&gt;</span>
            <span class="current"><?php printf(__('Request ID:%s', 'vendor-customshot'), $request_id); ?></span>
        </div>

        <!-- Action Buttons Row -->
        <div class="customshot-detail-actions">
            <?php if (in_array($status, array('accepted', 'in_progress', 'delivered'))) : ?>
                <a href="<?php echo esc_url(get_wcfm_customshot_messages_url($request_id)); ?>" class="btn-action btn-message">
                    <?php _e('Message Buyer', 'vendor-customshot'); ?>
                </a>
            <?php endif; ?>
            <?php if ($status === 'pending') : ?>
                <button type="button" class="btn-action btn-accept" id="accept-offer-btn" data-shot-id="<?php echo esc_attr($request_id); ?>">
                    <?php _e('Accept & Quote', 'vendor-customshot'); ?>
                </button>
                <button type="button" class="btn-action btn-decline" id="decline-btn" data-shot-id="<?php echo esc_attr($request_id); ?>">
                    <?php _e('Decline', 'vendor-customshot'); ?>
                </button>
            <?php endif; ?>
        </div>

        <div class="customshot-detail-container">
            <!-- Left Column - Request Details -->
            <div class="customshot-detail-main">
                <div class="detail-card">
                    <!-- Header with Status -->
                    <div class="detail-card-header">
                        <h3><?php _e('Custom Shoot Request Details', 'vendor-customshot'); ?></h3>
                        <div class="header-meta">
                            <span class="customshot-status <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                            <span class="meta-text"><span><?php _e('Request ID:', 'vendor-customshot'); ?></span> <?php echo esc_html($request_id); ?></span>
                            <span class="meta-text"><span><?php _e('Delivery Date:', 'vendor-customshot'); ?></span> <?php echo $shoot_date ? date_i18n('M d, Y', strtotime($shoot_date)) : '-'; ?></span>
                        </div>
                    </div>

                    <!-- Project Details -->
                    <div class="detail-section">
                        <h4 class="section-title"><?php _e('Project Details', 'vendor-customshot'); ?></h4>
                        <p class="project-brief"><?php echo nl2br(esc_html($brief)); ?></p>
                    </div>

                    <!-- Info Grid -->
                    <div class="detail-info-row">
                        <div class="info-item">
                            <span class="info-label"><?php _e('Type of Shoot:', 'vendor-customshot'); ?></span>
                            <span class="info-value"><?php echo esc_html($shoot_type); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php _e('Usage Type:', 'vendor-customshot'); ?></span>
                            <span class="info-value"><?php echo esc_html($usage_type); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php _e('Deliverables:', 'vendor-customshot'); ?></span>
                            <span class="info-value"><?php echo esc_html($deliverables ?: '-'); ?></span>
                        </div>
                    </div>

                    <?php if ($status === 'pending') : ?>
                        <!-- Quote Form -->
                        <div class="quote-form-section">
                            <h4 class="section-title"><?php _e('Send Your Quote', 'vendor-customshot'); ?></h4>
                            <p class="section-desc"><?php _e('Review the request and send your quote with price and delivery date.', 'vendor-customshot'); ?></p>

                            <div class="form-row">
                                <label for="vendor-note"><?php _e('Short Note for Buyer', 'vendor-customshot'); ?></label>
                                <input type="text" id="vendor-note" name="vendor_note" placeholder="<?php esc_attr_e('Enter Text Here', 'vendor-customshot'); ?>">
                            </div>

                            <div class="form-row-inline">
                                <div class="form-group">
                                    <label for="vendor-quote"><?php _e('Your Quote (USD)', 'vendor-customshot'); ?></label>
                                    <div class="input-with-prefix">
                                        <span class="prefix">$</span>
                                        <input type="number" id="vendor-quote" name="vendor_quote" value="<?php echo esc_attr($budget); ?>" min="0" step="1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="delivery-date"><?php _e('Delivery Date', 'vendor-customshot'); ?></label>
                                    <input type="date" id="delivery-date" name="delivery_date" value="<?php echo esc_attr($delivery_date ?: $shoot_date); ?>">
                                </div>
                            </div>

                            <div class="form-submit-row">
                                <button type="button" class="btn-send-quote" id="send-quote-btn" data-shot-id="<?php echo esc_attr($request_id); ?>">
                                    <?php _e('Send Quote', 'vendor-customshot'); ?>
                                </button>
                                <span class="quote-note"><?php _e('Funds will be held in escrow once buyer accepts your quote.', 'vendor-customshot'); ?></span>
                            </div>
                        </div>
                    <?php elseif (in_array($status, array('quoted', 'accepted', 'completed'))) : ?>
                        <!-- Quote Details (Already Quoted) -->
                        <div class="quote-details-section">
                            <h4 class="section-title"><?php _e('Your Quote', 'vendor-customshot'); ?></h4>
                            <div class="quote-info-row">
                                <div class="quote-item">
                                    <span class="quote-label"><?php _e('Quote Amount', 'vendor-customshot'); ?></span>
                                    <span class="quote-value quote-amount">$<?php echo number_format((float)$vendor_quote, 2); ?> USD</span>
                                </div>
                                <div class="quote-item">
                                    <span class="quote-label"><?php _e('Delivery Date', 'vendor-customshot'); ?></span>
                                    <span class="quote-value"><?php echo $delivery_date ? date_i18n('M d, Y', strtotime($delivery_date)) : '-'; ?></span>
                                </div>
                                <div class="quote-item">
                                    <span class="quote-label"><?php _e('Quote Sent', 'vendor-customshot'); ?></span>
                                    <span class="quote-value"><?php echo $quote_date ? date_i18n('M d, Y', strtotime($quote_date)) : '-'; ?></span>
                                </div>
                            </div>
                            <?php if ($vendor_note) : ?>
                                <div class="quote-note-display">
                                    <span class="quote-label"><?php _e('Your Note to Buyer', 'vendor-customshot'); ?></span>
                                    <p><?php echo esc_html($vendor_note); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($status === 'quoted') : ?>
                                <div class="status-message status-waiting">
                                    <span class="wcfmfa fa-clock"></span>
                                    <?php _e('Waiting for buyer to accept or decline your quote.', 'vendor-customshot'); ?>
                                </div>
                            <?php elseif ($status === 'accepted') : ?>
                                <div class="status-message status-accepted">
                                    <span class="wcfmfa fa-check-circle"></span>
                                    <?php _e('Buyer has accepted your quote! You can start working on this project.', 'vendor-customshot'); ?>
                                </div>
                            <?php elseif ($status === 'completed') : ?>
                                <div class="status-message status-completed">
                                    <span class="wcfmfa fa-check-circle"></span>
                                    <?php _e('This project has been completed.', 'vendor-customshot'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($status === 'declined') : ?>
                        <div class="quote-details-section">
                            <div class="status-message status-declined">
                                <span class="wcfmfa fa-times-circle"></span>
                                <?php _e('You declined this custom shot request.', 'vendor-customshot'); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Buyer Info -->
            <div class="customshot-detail-sidebar">
                <div class="buyer-card">
                    <div class="buyer-avatar-wrapper">
                        <img src="<?php echo esc_url($buyer_avatar); ?>" alt="<?php echo esc_attr($buyer_name); ?>">
                    </div>
                    <h4 class="buyer-name"><?php echo esc_html($buyer_name); ?></h4>
                    <?php if ($buyer_company) : ?>
                        <p class="buyer-company"><?php echo esc_html($buyer_company); ?></p>
                    <?php endif; ?>

                    <div class="buyer-stats">
                        <div class="stat-row">
                            <span class="stat-label budget-label"><?php _e('Budget Offered', 'vendor-customshot'); ?></span>
                            <span class="stat-value stat-budget">$<?php echo number_format((float)$budget, 0); ?> USD</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label"><?php _e('Request Date', 'vendor-customshot'); ?></span>
                            <span class="stat-value"><?php echo date_i18n('M d, Y', strtotime($received_date)); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label"><?php _e('Delivery Deadline', 'vendor-customshot'); ?></span>
                            <span class="stat-value"><?php echo $shoot_date ? date_i18n('M d, Y', strtotime($shoot_date)) : '-'; ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label"><?php _e('License Type', 'vendor-customshot'); ?></span>
                            <span class="stat-value"><?php echo esc_html($usage_type); ?></span>
                        </div>
                    </div>

                    <!-- <a href="#" class="btn-view-profile" id="view-buyer-profile" data-buyer-id="<?php //echo esc_attr($buyer_id); ?>">
                        <?php //_e('View Profile', 'vendor-customshot'); ?>
                    </a> -->

                    <?php
                        $buyer_id = absint( $buyer_id );
                        $user     = get_user_by( 'id', $buyer_id );

                        if ( $user && user_can( $buyer_id, 'edit_products' ) ) {

                            // WCFM store URL pattern
                            $store_url = site_url( '/store/' . $user->user_nicename );
                            ?>

                            <a href="<?php echo esc_url( $store_url ); ?>"
                            class="btn-view-profile"
                            id="view-buyer-profile">
                                <?php esc_html_e( 'View Profile', 'vendor-customshot' ); ?>
                            </a>

                    <?php } ?>



                </div>
            </div>
        </div>
    </div>
</div>