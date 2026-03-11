<?php
/**
 * Buyer Custom Shot Detail View
 * Shows details of a specific request with quote accept/reject
 */

if (!defined('ABSPATH')) exit;

$buyer_id = get_current_user_id();

// Verify this shot belongs to this buyer
$shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
if ($shot_buyer_id != $buyer_id) {
    echo '<p>' . esc_html__('You do not have permission to view this request.', 'vendor-customshot') . '</p>';
    return;
}

$shot = get_post($shot_id);
if (!$shot) {
    echo '<p>' . esc_html__('Request not found.', 'vendor-customshot') . '</p>';
    return;
}

// Get data
$vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
$vendor = get_user_by('id', $vendor_id);
$store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
$vendor_name = $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot'));
$vendor_avatar = get_avatar_url($vendor_id, array('size' => 80));

$status = get_post_meta($shot_id, '_customshot_status', true);
$budget = get_post_meta($shot_id, '_customshot_budget', true);
$brief = get_post_meta($shot_id, '_customshot_brief', true);
$deliverables_requested = get_post_meta($shot_id, '_customshot_deliverables', true);
$shoot_date = get_post_meta($shot_id, '_customshot_shoot_date', true);

// Quote data
$vendor_quote = get_post_meta($shot_id, '_customshot_vendor_quote', true);
$vendor_note = get_post_meta($shot_id, '_customshot_vendor_note', true);
$delivery_date = get_post_meta($shot_id, '_customshot_delivery_date', true);
$quote_date = get_post_meta($shot_id, '_customshot_quote_date', true);

// Buyer wallet balance
$wallet_balance = floatval(get_user_meta($buyer_id, '_modfolios_user_wallet', true));
$has_sufficient_funds = $wallet_balance >= floatval($vendor_quote);

// Taxonomies
$shoot_type_terms = wp_get_post_terms($shot_id, 'shoot_type');
$usage_type_terms = wp_get_post_terms($shot_id, 'usage_type');
$shoot_type = !empty($shoot_type_terms) ? $shoot_type_terms[0]->name : '-';
$usage_type = !empty($usage_type_terms) ? $usage_type_terms[0]->name : '-';

$base_url = wc_get_account_endpoint_url('custom-shots');
?>

<div class="buyer-customshot-detail">
    <!-- Back Link -->
    <a href="<?php echo esc_url($base_url); ?>" class="back-link">
        &larr; <?php esc_html_e('Back to Custom Shots', 'vendor-customshot'); ?>
    </a>

    <div class="detail-container">
        <!-- Main Content -->
        <div class="detail-main">
            <div class="detail-header">
                <h2><?php echo esc_html($shot->post_title); ?></h2>
                <span class="status-badge status-<?php echo esc_attr($status); ?>">
                    <?php
                    $status_labels = array(
                        'pending'        => __('Pending', 'vendor-customshot'),
                        'quoted'         => __('Quote Received', 'vendor-customshot'),
                        'accepted'       => __('Accepted', 'vendor-customshot'),
                        'in_progress'    => __('In Progress', 'vendor-customshot'),
                        'delivered'      => __('Delivered', 'vendor-customshot'),
                        'completed'      => __('Completed', 'vendor-customshot'),
                        'declined'       => __('Declined by Vendor', 'vendor-customshot'),
                        'quote_rejected' => __('Quote Rejected', 'vendor-customshot'),
                    );
                    echo esc_html($status_labels[$status] ?? ucfirst($status));
                    ?>
                </span>
            </div>

            <!-- Quote Section (if quoted) -->
            <?php if ($status === 'quoted' && $vendor_quote) : ?>
                <div class="quote-section">
                    <h3><?php esc_html_e('Quote from Vendor', 'vendor-customshot'); ?></h3>
                    <div class="quote-box">
                        <div class="quote-amount-large">
                            $<?php echo number_format((float)$vendor_quote, 2); ?>
                        </div>
                        <div class="quote-meta">
                            <div class="meta-item">
                                <span class="meta-label"><?php esc_html_e('Delivery by', 'vendor-customshot'); ?></span>
                                <span class="meta-value"><?php echo $delivery_date ? date_i18n('F d, Y', strtotime($delivery_date)) : '-'; ?></span>
                            </div>
                            <?php if ($quote_date) : ?>
                                <div class="meta-item">
                                    <span class="meta-label"><?php esc_html_e('Quote sent', 'vendor-customshot'); ?></span>
                                    <span class="meta-value"><?php echo date_i18n('M d, Y', strtotime($quote_date)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($vendor_note) : ?>
                            <div class="quote-note">
                                <strong><?php esc_html_e('Vendor\'s Note:', 'vendor-customshot'); ?></strong>
                                <p><?php echo nl2br(esc_html($vendor_note)); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Wallet Balance Info -->
                    <div class="wallet-info-box <?php echo !$has_sufficient_funds ? 'insufficient' : ''; ?>">
                        <div class="wallet-balance">
                            <span class="wallet-label"><?php esc_html_e('Your Wallet Balance:', 'vendor-customshot'); ?></span>
                            <span class="wallet-amount">$<?php echo number_format($wallet_balance, 2); ?></span>
                        </div>
                        <?php if (!$has_sufficient_funds) : ?>
                            <div class="wallet-warning">
                                <span class="warning-icon">&#9888;</span>
                                <?php
                                $shortfall = floatval($vendor_quote) - $wallet_balance;
                                printf(
                                    esc_html__('You need $%.2f more to accept this quote.', 'vendor-customshot'),
                                    $shortfall
                                );
                                ?>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('wallet')); ?>" class="add-funds-link">
                                    <?php esc_html_e('Add Funds', 'vendor-customshot'); ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="wallet-notice">
                                <?php esc_html_e('Accepting this quote will deduct the amount from your wallet and hold it in escrow until project completion.', 'vendor-customshot'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="quote-actions">
                        <button type="button" class="btn btn-primary btn-lg btn-accept-quote <?php echo !$has_sufficient_funds ? 'disabled' : ''; ?>"
                                data-shot-id="<?php echo esc_attr($shot_id); ?>"
                                <?php echo !$has_sufficient_funds ? 'disabled' : ''; ?>>
                            <?php esc_html_e('Accept Quote', 'vendor-customshot'); ?>
                        </button>
                        <button type="button" class="btn btn-outline btn-reject-quote" data-shot-id="<?php echo esc_attr($shot_id); ?>">
                            <?php esc_html_e('Decline Quote', 'vendor-customshot'); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Chat Button (if accepted) -->
            <?php if (in_array($status, array('accepted', 'in_progress', 'delivered'))) : ?>
                <div class="chat-cta">
                    <a href="<?php echo esc_url($base_url . '?chat=1&id=' . $shot_id); ?>" class="btn btn-primary btn-lg">
                        <?php esc_html_e('Open Chat', 'vendor-customshot'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Request Details -->
            <div class="detail-section">
                <h3><?php esc_html_e('Request Details', 'vendor-customshot'); ?></h3>

                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label"><?php esc_html_e('Type of Shoot', 'vendor-customshot'); ?></span>
                        <span class="detail-value"><?php echo esc_html($shoot_type); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php esc_html_e('Usage Type', 'vendor-customshot'); ?></span>
                        <span class="detail-value"><?php echo esc_html($usage_type); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php esc_html_e('Your Budget', 'vendor-customshot'); ?></span>
                        <span class="detail-value">$<?php echo number_format((float)$budget, 0); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php esc_html_e('Preferred Date', 'vendor-customshot'); ?></span>
                        <span class="detail-value"><?php echo $shoot_date ? date_i18n('M d, Y', strtotime($shoot_date)) : '-'; ?></span>
                    </div>
                </div>

                <?php if ($deliverables_requested) : ?>
                    <div class="detail-item full-width">
                        <span class="detail-label"><?php esc_html_e('Deliverables Requested', 'vendor-customshot'); ?></span>
                        <span class="detail-value"><?php echo esc_html($deliverables_requested); ?></span>
                    </div>
                <?php endif; ?>

                <div class="detail-item full-width">
                    <span class="detail-label"><?php esc_html_e('Project Brief', 'vendor-customshot'); ?></span>
                    <div class="detail-brief"><?php echo nl2br(esc_html($brief)); ?></div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Vendor Info -->
        <div class="detail-sidebar">
            <div class="vendor-card">
                <img src="<?php echo esc_url($vendor_avatar); ?>" alt="" class="vendor-avatar-large">
                <h4><?php echo esc_html($vendor_name); ?></h4>
                <?php if ($vendor) : ?>
                    <a href="<?php echo esc_url(wcfmmp_get_store_url($vendor_id)); ?>" class="btn btn-outline btn-sm" target="_blank">
                        <?php esc_html_e('View Store', 'vendor-customshot'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="timeline">
                <h4><?php esc_html_e('Timeline', 'vendor-customshot'); ?></h4>
                <div class="timeline-item">
                    <span class="timeline-date"><?php echo date_i18n('M d, Y', strtotime($shot->post_date)); ?></span>
                    <span class="timeline-event"><?php esc_html_e('Request Submitted', 'vendor-customshot'); ?></span>
                </div>
                <?php if ($quote_date) : ?>
                    <div class="timeline-item">
                        <span class="timeline-date"><?php echo date_i18n('M d, Y', strtotime($quote_date)); ?></span>
                        <span class="timeline-event"><?php esc_html_e('Quote Received', 'vendor-customshot'); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($status === 'declined') : ?>
                    <div class="timeline-item timeline-declined">
                        <span class="timeline-event"><?php esc_html_e('Declined by Vendor', 'vendor-customshot'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
