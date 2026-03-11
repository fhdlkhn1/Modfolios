<?php
/**
 * My Account Dashboard - Custom Buyer Dashboard
 *
 * This template displays the buyer dashboard with stats, downloads, custom shots, etc.
 *
 * @package suspended
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get user data
$display_name = $current_user->display_name;
$user_avatar = get_avatar_url($user_id, array('size' => 80));
// $last_login = get_user_meta($user_id, 'last_login', true);
$last_login = (int) get_user_meta($user_id, '_last_login', true);
$membership_plan = get_user_meta($user_id, '_membership_plan', true);
if (empty($membership_plan)) {
    $membership_plan = 'Basic Plan';
}

// Format last login
// $last_login_formatted = $last_login ? date_i18n('g:i A (F d, Y)', strtotime($last_login)) : date_i18n('g:i A (F d, Y)');
// 
// If no last login stored, fall back to "now"
$timestamp = $last_login ?: current_time('timestamp');
$last_login_formatted = wp_date(
	'g:i A (F j, Y)',
	$timestamp
);

// Get wallet/account balance (this is real data from the wallet meta key)
$wallet_balance = floatval(get_user_meta($user_id, '_modfolios_user_wallet', true));

// Get budget data
$monthly_budget = floatval(get_user_meta($user_id, '_monthly_budget', true));
if ($monthly_budget <= 0) $monthly_budget = 3000; // Default budget if not set

// Calculate real monthly spending from WooCommerce orders
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');

$monthly_orders = wc_get_orders(array(
    'customer_id' => $user_id,
    'status' => array('completed', 'processing'),
    'date_created' => $current_month_start . '...' . $current_month_end,
    'return' => 'ids',
));

$monthly_spending = 0;
foreach ($monthly_orders as $order_id) {
    $order = wc_get_order($order_id);
    if ($order) {
        $monthly_spending += floatval($order->get_total());
    }
}

// Remaining budget is wallet balance (available funds)
$remaining_budget = $wallet_balance;

// Get download count (from WooCommerce orders or custom tracking)
$active_downloads = 0;
$downloads = wc_get_customer_available_downloads($user_id);
if ($downloads) {
    $active_downloads = count($downloads);
}

// Get recent downloads (limit to 2 for dashboard)
$recent_downloads = array_slice($downloads, 0, 2);

// Get all custom shots for this buyer (matching buyer-customshots-list.php query)
$all_custom_shots = get_posts(array(
    'post_type'      => 'customshot',
    'post_status'    => array('pending', 'publish', 'draft'),
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'   => '_customshot_buyer_id',
            'value' => $user_id,
        ),
    ),
    'orderby' => 'date',
    'order'   => 'DESC',
));
// Sort into active vs pending (same logic as buyer-customshots-list.php)
$active_custom_shots = array();
$pending_custom_shots = array();
foreach ($all_custom_shots as $cs) {
    $cs_status = get_post_meta($cs->ID, '_customshot_status', true);
    switch ($cs_status) {
        case 'quoted':
        case 'accepted':
        case 'in_progress':
        case 'delivered':
            $active_custom_shots[] = $cs;
            break;
        case 'completed':
        case 'declined':
        case 'quote_rejected':
            // Completed/closed — skip for dashboard
            break;
        case 'pending':
        default:
            $pending_custom_shots[] = $cs;
            break;
    }
}
// Total count excludes completed/declined/rejected
$custom_shots_count = count($active_custom_shots) + count($pending_custom_shots);
$pending_custom_shots_count = count($pending_custom_shots);
// Limit to 2 each for dashboard display
$active_custom_shots = array_slice($active_custom_shots, 0, 2);
$pending_custom_shots = array_slice($pending_custom_shots, 0, 2);

// Calculate budget warning threshold (e.g., 90% used)
$budget_percentage = $monthly_budget > 0 ? ($monthly_spending / $monthly_budget) * 100 : 0;
$budget_warning = $budget_percentage >= 80;
?>

<div class="modfolio-buyer-dashboard">
    <!-- Welcome Header -->
    <div class="dashboard-welcome-header">
        <div class="welcome-user-info">
            <div class="user-avatar">
                <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($display_name); ?>">
            </div>
            <div class="user-details">
                <p class="welcome-text"><?php esc_html_e('WELCOME TO THE BUYER MODFOLIOS DASHBOARD', 'flavor starter theme'); ?></p>
                <h2 class="user-name"><?php echo esc_html($display_name); ?></h2>
            </div>
        </div>
        <div class="welcome-meta">
            <span class="membership-badge"><?php echo esc_html($membership_plan); ?></span>
            <span class="last-login"><?php esc_html_e('Last Login:', 'flavor starter theme'); ?> <?php echo esc_html($last_login_formatted); ?></span>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="dashboard-stats-row">
        <div class="stat-card">
            <span class="stat-label"><?php esc_html_e('Monthly Spending', 'flavor starter theme'); ?></span>
            <span class="stat-value stat-spending">$<?php echo number_format($monthly_spending, 0); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label"><?php esc_html_e('Remaining Budget', 'flavor starter theme'); ?></span>
            <span class="stat-value stat-budget">$<?php echo number_format($remaining_budget, 0); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label"><?php esc_html_e('Active Downloads', 'flavor starter theme'); ?></span>
            <span class="stat-value stat-downloads"><?php echo esc_html($active_downloads); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label"><?php esc_html_e('Account', 'flavor starter theme'); ?></span>
            <span class="stat-value stat-account">$<?php echo number_format($wallet_balance, 0); ?></span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-content-grid">
        <!-- Left Column -->
        <div class="dashboard-left-column">
            <!-- Recent Downloads Section -->
            <div class="dashboard-section recent-downloads-section">
                <div class="section-header">
                    <h3><?php esc_html_e('Recent Downloads', 'flavor starter theme'); ?></h3>
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('downloads')); ?>" class="view-all-link">
                        <?php esc_html_e('View All', 'flavor starter theme'); ?> →
                    </a>
                </div>
                <div class="downloads-list">
                    <?php if (!empty($recent_downloads)) : ?>
                        <?php foreach ($recent_downloads as $download) :
                            $product_id = $download['product_id'];
                            $product = wc_get_product($product_id);
                            $product_image = $product ? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') : '';
                            $product_name = $download['product_name'];
                            $download_date = isset($download['access_expires']) ? $download['access_expires'] : '';

                            // Get vendor name
                            $vendor_id = get_post_field('post_author', $product_id);
                            $vendor_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                            if (empty($vendor_name)) {
                                $vendor = get_user_by('id', $vendor_id);
                                $vendor_name = $vendor ? $vendor->display_name : '';
                            }
                        ?>
                            <div class="download-item">
                                <div class="download-thumb">
                                    <?php if ($product_image) : ?>
                                        <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_name); ?>">
                                    <?php else : ?>
                                        <div class="placeholder-thumb"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="download-info">
                                    <h4 class="download-title"><?php echo esc_html($product_name); ?></h4>
                                    <p class="download-meta"><?php echo esc_html($vendor_name); ?></p>
                                </div>
                                <div class="download-actions">
                                    <a href="#" class="action-btn" title="<?php esc_attr_e('Reorder', 'flavor starter theme'); ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 1l4 4-4 4"></path>
                                            <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                                            <path d="M7 23l-4-4 4-4"></path>
                                            <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                                        </svg>
                                        <span><?php esc_html_e('Reorder', 'flavor starter theme'); ?></span>
                                    </a>
                                    <a href="#" class="action-btn" title="<?php esc_attr_e('View License', 'flavor starter theme'); ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                        <span><?php esc_html_e('View License', 'flavor starter theme'); ?></span>
                                    </a>
                                    <a href="<?php echo esc_url($download['download_url']); ?>" class="action-btn" title="<?php esc_attr_e('Download', 'flavor starter theme'); ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        <span><?php esc_html_e('Download', 'flavor starter theme'); ?></span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="empty-state">
                            <p><?php esc_html_e('No downloads yet.', 'flavor starter theme'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Custom Shoot Requests Section -->
            <div class="dashboard-section custom-shots-section">
                <div class="section-header">
                    <h3>
                        <?php esc_html_e('Custom Shoot Requests', 'flavor starter theme'); ?> (<?php echo esc_html($custom_shots_count); ?>)
                        <?php if ($pending_custom_shots_count > 0) : ?>
                            <span class="shot-status-badge status-pending"><?php echo esc_html($pending_custom_shots_count); ?> <?php esc_html_e('Pending', 'flavor starter theme'); ?></span>
                        <?php endif; ?>
                    </h3>
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('custom-shots')); ?>" class="view-all-link">
                        <?php esc_html_e('View All', 'flavor starter theme'); ?> →
                    </a>
                </div>
                <div class="custom-shots-list">
                    <?php if (!empty($active_custom_shots)) : ?>
                        <?php foreach ($active_custom_shots as $shot) :
                            $shot_id = $shot->ID;
                            $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
                            $vendor = get_user_by('id', $vendor_id);
                            $vendor_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                            if (empty($vendor_name)) {
                                $vendor_name = $vendor ? $vendor->display_name : __('Unknown', 'flavor starter theme');
                            }
                            $vendor_avatar = get_avatar_url($vendor_id, array('size' => 50));

                            $status = get_post_meta($shot_id, '_customshot_status', true);
                            $received_date = $shot->post_date;
                            $revision = get_post_meta($shot_id, '_customshot_revision_count', true);
                            $revision = $revision ? $revision : 0;

                            // Calculate auto-approve countdown
                            $delivered_date = get_post_meta($shot_id, '_customshot_deliverable_submitted_date', true);
                            $auto_approve_days = 0;
                            if ($status === 'delivered' && $delivered_date) {
                                $delivered_timestamp = strtotime($delivered_date);
                                $auto_approve_deadline = $delivered_timestamp + (7 * DAY_IN_SECONDS);
                                $remaining_seconds = $auto_approve_deadline - time();
                                $auto_approve_days = max(0, ceil($remaining_seconds / DAY_IN_SECONDS));
                            }

                            $status_labels = array(
                                'quoted'      => __('Quoted', 'flavor starter theme'),
                                'accepted'    => __('In Progress', 'flavor starter theme'),
                                'in_progress' => __('In Progress', 'flavor starter theme'),
                                'delivered'   => __('Delivered', 'flavor starter theme'),
                            );
                            $status_label = isset($status_labels[$status]) ? $status_labels[$status] : ucfirst(str_replace('_', ' ', $status));
                        ?>
                            <div class="custom-shot-item">
                                <div class="shot-header">
                                    <img src="<?php echo esc_url($vendor_avatar); ?>" alt="<?php echo esc_attr($vendor_name); ?>" class="vendor-avatar">
                                    <div class="shot-info">
                                        <h4 class="shot-title"><?php echo esc_html($shot->post_title); ?></h4>
                                        <p class="shot-meta">
                                            <?php esc_html_e('From:', 'flavor starter theme'); ?> <?php echo esc_html($vendor_name); ?><br>
                                            <?php esc_html_e('Received:', 'flavor starter theme'); ?> <?php echo date_i18n('j F Y', strtotime($received_date)); ?><br>
                                            <?php esc_html_e('Revision:', 'flavor starter theme'); ?> <?php echo esc_html($revision); ?><?php echo $revision == 1 ? 'st' : ($revision == 2 ? 'nd' : ($revision == 3 ? 'rd' : 'th')); ?> Draft
                                        </p>
                                        <?php if ($auto_approve_days > 0) : ?>
                                            <p class="auto-approve-notice"><?php printf(esc_html__('Auto-Approve in %d Days', 'flavor starter theme'), $auto_approve_days); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="shot-status-badge status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_label); ?></span>
                                </div>
                                <div class="shot-actions">
                                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('custom-shots') . '?chat=1&id=' . $shot_id); ?>" class="action-btn" title="<?php esc_attr_e('Review', 'flavor starter theme'); ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span><?php esc_html_e('Review', 'flavor starter theme'); ?></span>
                                    </a>
                                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('custom-shots') . '?chat=1&id=' . $shot_id); ?>" class="action-btn" title="<?php esc_attr_e('Request Revision', 'flavor starter theme'); ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        <span><?php esc_html_e('Request Revision', 'flavor starter theme'); ?></span>
                                    </a>
                                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('custom-shots') . '?chat=1&id=' . $shot_id); ?>" class="action-btn action-approve" title="<?php esc_attr_e('Approve', 'flavor starter theme'); ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Approve', 'flavor starter theme'); ?></span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($pending_custom_shots)) : ?>
                        <?php foreach ($pending_custom_shots as $shot) :
                            $shot_id = $shot->ID;
                            $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
                            $vendor = get_user_by('id', $vendor_id);
                            $vendor_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                            if (empty($vendor_name)) {
                                $vendor_name = $vendor ? $vendor->display_name : __('Unknown', 'flavor starter theme');
                            }
                            $vendor_avatar = get_avatar_url($vendor_id, array('size' => 50));
                        ?>
                            <div class="custom-shot-item custom-shot-pending">
                                <div class="shot-header">
                                    <img src="<?php echo esc_url($vendor_avatar); ?>" alt="<?php echo esc_attr($vendor_name); ?>" class="vendor-avatar">
                                    <div class="shot-info">
                                        <h4 class="shot-title"><?php echo esc_html($shot->post_title); ?></h4>
                                        <p class="shot-meta">
                                            <?php esc_html_e('To:', 'flavor starter theme'); ?> <?php echo esc_html($vendor_name); ?><br>
                                            <?php esc_html_e('Submitted:', 'flavor starter theme'); ?> <?php echo date_i18n('j F Y', strtotime($shot->post_date)); ?>
                                        </p>
                                    </div>
                                    <span class="shot-status-badge status-pending"><?php esc_html_e('Pending', 'flavor starter theme'); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (empty($active_custom_shots) && empty($pending_custom_shots)) : ?>
                        <div class="empty-state">
                            <p><?php esc_html_e('No active custom shoot requests.', 'flavor starter theme'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="dashboard-right-column">
            <!-- Spending Overview -->
            <div class="dashboard-section spending-overview-section">
                <div class="section-header">
                    <h3><?php esc_html_e('Spending Overview', 'flavor starter theme'); ?></h3>
                    <div class="chart-filters">
                        <span class="chart-filter-btn" data-period="weekly"><?php esc_html_e('Weekly', 'flavor starter theme'); ?></span>
                        <span class="filter-separator">/</span>
                        <span class="chart-filter-btn filter-active" data-period="monthly"><?php esc_html_e('Monthly', 'flavor starter theme'); ?></span>
                        <span class="filter-separator">/</span>
                        <span class="chart-filter-btn" data-period="yearly"><?php esc_html_e('Yearly', 'flavor starter theme'); ?></span>
                    </div>
                </div>
                <div class="spending-chart-container">
                    <canvas id="spendingChart" height="180"></canvas>
                </div>
            </div>

            <?php
            // Build spending data for chart (passed to JS)
            // Monthly data: spending per month for the current year
            $spending_monthly_labels = array();
            $spending_monthly_data = array();
            $current_year = date('Y');
            for ($m = 1; $m <= 12; $m++) {
                $spending_monthly_labels[] = date_i18n('M', mktime(0, 0, 0, $m, 1));
                $month_start = $current_year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT) . '-01';
                $month_end = date('Y-m-t', strtotime($month_start));
                $m_orders = wc_get_orders(array(
                    'customer_id' => $user_id,
                    'status' => array('completed', 'processing'),
                    'date_created' => $month_start . '...' . $month_end,
                    'return' => 'ids',
                ));
                $m_total = 0;
                foreach ($m_orders as $oid) {
                    $o = wc_get_order($oid);
                    if ($o) $m_total += floatval($o->get_total());
                }
                $spending_monthly_data[] = $m_total;
            }

            // Weekly data: spending per day for the last 7 days
            $spending_weekly_labels = array();
            $spending_weekly_data = array();
            for ($d = 6; $d >= 0; $d--) {
                $day_date = date('Y-m-d', strtotime("-{$d} days"));
                $spending_weekly_labels[] = date_i18n('D', strtotime($day_date));
                $d_orders = wc_get_orders(array(
                    'customer_id' => $user_id,
                    'status' => array('completed', 'processing'),
                    'date_created' => $day_date . '...' . $day_date,
                    'return' => 'ids',
                ));
                $d_total = 0;
                foreach ($d_orders as $oid) {
                    $o = wc_get_order($oid);
                    if ($o) $d_total += floatval($o->get_total());
                }
                $spending_weekly_data[] = $d_total;
            }

            // Yearly data: spending per year for last 5 years
            $spending_yearly_labels = array();
            $spending_yearly_data = array();
            $cy = (int) date('Y');
            for ($y = $cy - 4; $y <= $cy; $y++) {
                $spending_yearly_labels[] = (string) $y;
                $y_orders = wc_get_orders(array(
                    'customer_id' => $user_id,
                    'status' => array('completed', 'processing'),
                    'date_created' => $y . '-01-01...' . $y . '-12-31',
                    'return' => 'ids',
                ));
                $y_total = 0;
                foreach ($y_orders as $oid) {
                    $o = wc_get_order($oid);
                    if ($o) $y_total += floatval($o->get_total());
                }
                $spending_yearly_data[] = $y_total;
            }
            ?>

            <!-- Notifications -->
            <div class="dashboard-section notifications-section">
                <div class="section-header">
                    <h3><?php esc_html_e('Notifications', 'flavor starter theme'); ?></h3>
                </div>
                <div class="notifications-list">
                    <?php
                    // Build dynamic notifications from real user activity
                    $notifications = array();

                    // 1. Recent purchase confirmations (last 30 days)
                    $recent_orders = wc_get_orders(array(
                        'customer_id' => $user_id,
                        'status'      => array('completed', 'processing'),
                        'limit'       => 1,
                        'orderby'     => 'date',
                        'order'       => 'DESC',
                        'date_created' => '>' . date('Y-m-d', strtotime('-30 days')),
                    ));
                    if (!empty($recent_orders)) {
                        $last_order = $recent_orders[0];
                        $notifications[] = array(
                            'type'    => 'confirmation',
                            'title'   => __('Purchase Confirmation', 'flavor starter theme'),
                            'message' => sprintf(
                                __('Order #%s — %s', 'flavor starter theme'),
                                $last_order->get_order_number(),
                                wc_format_datetime($last_order->get_date_created(), 'M d, Y')
                            ),
                        );
                    }

                    // 2. Recent custom shoot submissions
                    $recent_custom_shots = get_posts(array(
                        'post_type'      => 'customshot',
                        'posts_per_page' => 1,
                        'meta_query'     => array(
                            array(
                                'key'   => '_customshot_buyer_id',
                                'value' => $user_id,
                            ),
                        ),
                        'orderby'  => 'date',
                        'order'    => 'DESC',
                        'date_query' => array(
                            array('after' => '30 days ago'),
                        ),
                    ));
                    if (!empty($recent_custom_shots)) {
                        $last_shot = $recent_custom_shots[0];
                        $shot_status = get_post_meta($last_shot->ID, '_customshot_status', true);
                        $shot_status_label = ucfirst(str_replace('_', ' ', $shot_status));
                        $notifications[] = array(
                            'type'    => 'customshot',
                            'title'   => __('Custom Shoot Request', 'flavor starter theme'),
                            'message' => sprintf(
                                __('%s — %s', 'flavor starter theme'),
                                $last_shot->post_title,
                                $shot_status_label
                            ),
                        );
                    }

                    // 3. Products added to wishlist
                    if (defined('WOOSW_VERSION')) {
                        $woosw_key = isset($_COOKIE['woosw_key']) ? sanitize_text_field($_COOKIE['woosw_key']) : '';
                        if (empty($woosw_key)) {
                            $woosw_key = get_user_meta($user_id, 'woosw_key', true);
                        }
                        $wishlist_products = $woosw_key ? get_option('woosw_list_' . $woosw_key) : false;
                        if (!empty($wishlist_products) && is_array($wishlist_products)) {
                            $wishlist_count = count($wishlist_products);
                            $notifications[] = array(
                                'type'    => 'wishlist',
                                'title'   => __('Wishlist', 'flavor starter theme'),
                                'message' => sprintf(
                                    _n('%d product in your wishlist', '%d products in your wishlist', $wishlist_count, 'flavor starter theme'),
                                    $wishlist_count
                                ),
                            );
                        }
                    }

                    // Limit to 3 notifications
                    $notifications = array_slice($notifications, 0, 3);

                    if (!empty($notifications)) :
                        foreach ($notifications as $notification) :
                    ?>
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-message"><?php echo esc_html($notification['message']); ?></p>
                                <h4 class="notification-title"><?php echo esc_html($notification['title']); ?></h4>
                            </div>
                        </div>
                    <?php endforeach;
                    else : ?>
                        <div class="empty-state">
                            <p><?php esc_html_e('No recent notifications.', 'flavor starter theme'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for Spending Overview -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('spendingChart');
    if (!ctx) return;

    var spendingData = {
        weekly: {
            labels: <?php echo wp_json_encode($spending_weekly_labels); ?>,
            data: <?php echo wp_json_encode($spending_weekly_data); ?>
        },
        monthly: {
            labels: <?php echo wp_json_encode($spending_monthly_labels); ?>,
            data: <?php echo wp_json_encode($spending_monthly_data); ?>
        },
        yearly: {
            labels: <?php echo wp_json_encode($spending_yearly_labels); ?>,
            data: <?php echo wp_json_encode($spending_yearly_data); ?>
        }
    };

    var chartConfig = {
        type: 'line',
        data: {
            labels: spendingData.monthly.labels,
            datasets: [{
                label: 'Spending',
                data: spendingData.monthly.data,
                borderColor: '#00bcd4',
                backgroundColor: 'rgba(0, 188, 212, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#00bcd4',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toFixed(0);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        color: '#999',
                        callback: function(value) { return '$' + value; }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#999' }
                }
            }
        }
    };

    var spendingChart = new Chart(ctx, chartConfig);

    // Tab switching
    document.querySelectorAll('.chart-filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-filter-btn').forEach(function(b) {
                b.classList.remove('filter-active');
            });
            this.classList.add('filter-active');

            var period = this.getAttribute('data-period');
            var periodData = spendingData[period];
            if (periodData) {
                spendingChart.data.labels = periodData.labels;
                spendingChart.data.datasets[0].data = periodData.data;
                spendingChart.update();
            }
        });
    });
});
</script>