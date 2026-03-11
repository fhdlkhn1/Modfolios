<?php
/**
 * WCFM Payments Page Override - Modfolio
 * Custom withdrawal & payments dashboard
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $WCFM, $WCFMmp, $wpdb;

// Include helper functions
require_once get_template_directory() . '/wcfm/wcfm-helpers.php';

$wcfm_is_allow_payments = apply_filters( 'wcfm_is_allow_payments', true );
if ( ! $wcfm_is_allow_payments ) {
    wcfm_restriction_message_show( "Payments" );
    return;
}

$vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

// ── Membership & Commission Rate ──
$membership_id = get_user_meta( $vendor_id, 'wcfm_membership', true );
$membership_id = intval( $membership_id );

// Commission percentages by membership
$commission_rates = array(
    21877 => 40, // Basic
    21878 => 20, // Professional
    21879 => 10, // Elite
);

$commission_rate = isset( $commission_rates[ $membership_id ] ) ? $commission_rates[ $membership_id ] : 40;

// Membership labels
$membership_labels = array(
    21877 => 'Basic Plan',
    21878 => 'Professional Plan',
    21879 => 'Elite Plan',
);
$current_plan_label = isset( $membership_labels[ $membership_id ] ) ? $membership_labels[ $membership_id ] : 'Basic Plan';

// Determine next upgrade plan
$upgrade_info = null;
if ( $membership_id == 21877 ) { // Basic -> Pro
    $upgrade_info = array(
        'label' => 'Professional Plan',
        'rate'  => 20,
    );
} elseif ( $membership_id == 21878 ) { // Pro -> Elite
    $upgrade_info = array(
        'label' => 'Elite Plan',
        'rate'  => 10,
    );
}
// Elite has no upgrade

// ── Available Commissions (Withdrawable) ──
$withdrawable_commissions = $wpdb->get_results( $wpdb->prepare(
    "SELECT co.ID, co.order_id, co.total_commission, co.withdraw_charges
     FROM {$wpdb->prefix}wcfm_marketplace_orders co
     WHERE co.vendor_id = %d
       AND co.withdraw_status IN ('pending', 'cancelled')
       AND co.refund_status != 'requested'
       AND co.is_withdrawable = 1
       AND co.is_auto_withdrawal = 0
       AND co.is_refunded = 0
       AND co.is_trashed = 0",
    $vendor_id
) );

$total_net_available = 0;
$total_gross_available = 0;
$commission_ids = array();

if ( $withdrawable_commissions ) {
    foreach ( $withdrawable_commissions as $comm ) {
        $total_net_available += (float) $comm->total_commission;
        $commission_ids[] = $comm->ID;

        // Get gross_total from meta
        $gross = $wpdb->get_var( $wpdb->prepare(
            "SELECT value FROM {$wpdb->prefix}wcfm_marketplace_orders_meta
             WHERE order_commission_id = %d AND `key` = 'gross_total'",
            $comm->ID
        ) );
        if ( $gross ) {
            $total_gross_available += (float) $gross;
        } else {
            // Fallback: estimate gross from commission rate
            $total_gross_available += (float) $comm->total_commission;
        }
    }
}

$admin_fee_amount = $total_gross_available - $total_net_available;

// ── Total Withdrawals (All Time - Completed) ──
$total_withdrawals_all = (float) $wpdb->get_var( $wpdb->prepare(
    "SELECT COALESCE(SUM(withdraw_amount), 0)
     FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request
     WHERE vendor_id = %d AND withdraw_status = 'completed'",
    $vendor_id
) );

// ── Withdrawals This Month ──
$first_of_month = date( 'Y-m-01 00:00:00' );
$withdrawals_this_month = (float) $wpdb->get_var( $wpdb->prepare(
    "SELECT COALESCE(SUM(withdraw_amount), 0)
     FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request
     WHERE vendor_id = %d AND withdraw_status = 'completed' AND created >= %s",
    $vendor_id, $first_of_month
) );

// ── Previous Withdrawals (History) ──
$previous_withdrawals = $wpdb->get_results( $wpdb->prepare(
    "SELECT ID, withdraw_amount, withdraw_charges, withdraw_status, created
     FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request
     WHERE vendor_id = %d
     ORDER BY created DESC
     LIMIT 20",
    $vendor_id
) );

// ── Withdrawal Settings ──
$withdrawal_mode = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] ) ? $WCFMmp->wcfmmp_withdrawal_options['withdrawal_mode'] : 'by_manual';
$withdrawal_limit = $WCFMmp->wcfmmp_withdraw->get_withdrawal_limit( $vendor_id );
$can_withdraw = ( $withdrawal_mode == 'by_manual' )
    && apply_filters( 'wcfm_is_allow_withdrawal', true )
    && ( (float) $total_net_available >= (float) $withdrawal_limit )
    && ! empty( $commission_ids );

// Savings if upgrade
$savings = 0;
if ( $upgrade_info && $total_gross_available > 0 ) {
    $current_fee = $total_gross_available * ( $commission_rate / 100 );
    $upgrade_fee = $total_gross_available * ( $upgrade_info['rate'] / 100 );
    $savings = $current_fee - $upgrade_fee;
}

// Breadcrumb
$breadcrumb_items = array(
    array( 'label' => __( 'Payments', 'wc-frontend-manager' ), 'url' => '' ),
);
?>

<div class="collapse wcfm-collapse modfolio-payments-page" id="wcfm_payments_listing">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-credit-card"></span>
        <span class="wcfm-page-heading-text"><?php _e( 'Payments', 'wc-frontend-manager' ); ?></span>
        <?php do_action( 'wcfm_page_heading' ); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <?php modfolio_wcfm_render_header( '', $breadcrumb_items ); ?>

        <div class="modfolio-payments-wrapper">

            <!-- ══ TOP ROW: Balance + Stats ══ -->
            <div class="payments-top-row">

                <!-- Available Balance Card -->
                <div class="payments-card balance-card">
                    <div class="balance-header">
                        <div class="balance-label">
                            Available Balance
                            <span class="balance-badge">Gross</span>
                        </div>
                        <div class="balance-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                    </div>
                    <div class="balance-amount"><?php echo wc_price( $total_gross_available ); ?></div>
                    <div class="balance-subtitle">Available for immediate withdrawal via Bank Transfer or PayPal</div>
                </div>

                <!-- Stats Column -->
                <div class="payments-stats-col">
                    <!-- Total Withdrawals (All Time) -->
                    <div class="payments-card stat-card">
                        <div class="stat-label">Total Withdrawals (All Time)</div>
                        <div class="stat-amount"><?php echo wc_price( $total_withdrawals_all ); ?></div>
                    </div>

                    <!-- Withdrawals This Month -->
                    <div class="payments-card stat-card">
                        <div class="stat-label">Withdrawals This Month</div>
                        <div class="stat-amount"><?php echo wc_price( $withdrawals_this_month ); ?></div>
                    </div>
                </div>
            </div>

            <!-- ══ WITHDRAW FUNDS SECTION ══ -->
            <div class="payments-card withdraw-card">
                <div class="withdraw-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    <h3>Withdraw Funds</h3>
                </div>

                <form method="post" id="wcfm_withdrawal_manage_form">
                    <label class="withdraw-label">Withdraw Amount</label>
                    <div class="withdraw-input-row">
                        <div class="withdraw-input-wrap">
                            <span class="currency-prefix"><?php echo get_woocommerce_currency_symbol(); ?></span>
                            <input type="text"
                                   name="withdraw_amount"
                                   value="<?php echo esc_attr( number_format( $total_net_available, 2, '.', '' ) ); ?>"
                                   disabled
                                   readonly
                                   class="withdraw-amount-input" />
                        </div>
                        <span class="max-available-btn">Max Available</span>
                    </div>

                    <!-- Hidden commission checkboxes (all selected) -->
                    <?php foreach ( $commission_ids as $cid ) : ?>
                        <input type="hidden" name="commissions[]" value="<?php echo esc_attr( $cid ); ?>" />
                    <?php endforeach; ?>

                    <!-- Withdrawal Breakdown -->
                    <div class="withdrawal-breakdown">
                        <h4>Withdrawal Breakdown</h4>
                        <div class="breakdown-row">
                            <span>Requested Amount</span>
                            <span class="breakdown-value"><?php echo wc_price( $total_gross_available ); ?></span>
                        </div>
                        <div class="breakdown-row breakdown-fee">
                            <span>
                                Commission Fee (<?php echo esc_html( $commission_rate ); ?>%)
                                <span class="plan-badge"><?php echo esc_html( strtoupper( $current_plan_label ) ); ?></span>
                            </span>
                            <span class="breakdown-value fee-value">-<?php echo wc_price( $admin_fee_amount ); ?></span>
                        </div>
                        <div class="breakdown-row breakdown-total">
                            <span><strong>Amount After Commission</strong></span>
                            <span class="breakdown-value total-value"><?php echo wc_price( $total_net_available ); ?></span>
                        </div>
                    </div>

                    <?php if ( $upgrade_info ) : ?>
                    <div class="upgrade-notice">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>
                            Upgrade to <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>"><?php echo esc_html( $upgrade_info['label'] ); ?></a>
                            to lower your commission rate to <?php echo esc_html( $upgrade_info['rate'] ); ?>%<?php if ( $savings > 0 ) : ?> and save <?php echo wc_price( $savings ); ?> on this withdrawal<?php endif; ?>.
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="wcfm-message" tabindex="-1" style="display:none;"></div>

                    <?php if ( $can_withdraw ) : ?>
                        <button type="submit" id="wcfm_withdrawal_request_button" class="submit-withdrawal-btn">
                            Submit Withdrawal
                        </button>
                    <?php elseif ( empty( $commission_ids ) ) : ?>
                        <button type="button" class="submit-withdrawal-btn disabled" disabled>
                            No Funds Available
                        </button>
                    <?php else : ?>
                        <button type="button" class="submit-withdrawal-btn disabled" disabled>
                            Below Minimum Threshold
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- ══ PREVIOUS WITHDRAWALS ══ -->
            <?php if ( ! empty( $previous_withdrawals ) ) : ?>
            <div class="payments-card history-card">
                <h3>Previous Withdrawals</h3>
                <div class="history-table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $previous_withdrawals as $wd ) :
                                $status_class = 'status-' . esc_attr( $wd->withdraw_status );
                                $status_label = ucfirst( $wd->withdraw_status );
                                if ( $wd->withdraw_status === 'completed' ) $status_label = 'Completed';
                                elseif ( $wd->withdraw_status === 'requested' ) $status_label = 'Processing';
                                elseif ( $wd->withdraw_status === 'cancelled' ) $status_label = 'Cancelled';
                            ?>
                            <tr>
                                <td><?php echo date_i18n( 'M d, Y', strtotime( $wd->created ) ); ?></td>
                                <td class="ref-id">#WD-<?php echo esc_html( sprintf( '%05d', $wd->ID ) ); ?></td>
                                <td class="wd-amount"><?php echo wc_price( $wd->withdraw_amount ); ?></td>
                                <td><span class="wd-status <?php echo $status_class; ?>"><?php echo esc_html( $status_label ); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- .modfolio-payments-wrapper -->

        <?php do_action( 'after_wcfm_payments' ); ?>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════
   Modfolio Payments Page Styles
   Light theme (default) + Dark theme override
   ═══════════════════════════════════════════ */

.modfolio-payments-wrapper {
    max-width: 1100px;
    margin: 24px auto 0;
    padding: 0 16px;
}

/* ── Top Row Grid ── */
.payments-top-row {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    margin-bottom: 20px;
}

.payments-stats-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Card Base ── */
.payments-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 24px;
}

/* ── Balance Card ── */
.balance-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.balance-label {
    font-size: 14px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
}

.balance-badge {
    background: #d7d7d7;
    color: #fff;
    color: #606060;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.balance-icon {
    width: 40px;
    height: 40px;
    background: #0CCAAE;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.balance-amount {
    font-size: 36px;
    font-weight: 700;
    color: #111;
    margin-bottom: 8px;
    line-height: 1.2;
}

.balance-amount .woocommerce-Price-amount {
    font-size: inherit;
    font-weight: inherit;
    color: inherit;
}

.balance-subtitle {
    font-size: 12px;
    color: #999;
}

/* ── Stat Cards ── */
.stat-card {
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1;
}

.stat-label {
    font-size: 12px;
    color: #888;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.stat-amount {
    font-size: 24px;
    font-weight: 700;
    color: #111;
}

.stat-amount .woocommerce-Price-amount {
    font-size: inherit;
    font-weight: inherit;
    color: inherit;
}

/* ── Withdraw Card ── */
.withdraw-card {
    margin-bottom: 20px;
}

.withdraw-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.withdraw-card-header svg {
    color: #0CCAAE;
}

.withdraw-card-header h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #111;
}

.withdraw-label {
    display: block;
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
    font-weight: 500;
}

.withdraw-input-row {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    align-items: stretch;
}

.withdraw-input-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.currency-prefix {
    padding: 0 14px;
    font-size: 16px;
    font-weight: 600;
    color: #666;
    border-right: 1px solid #ddd;
    display: flex;
    align-items: center;
    height: 100%;
    min-height: 48px;
    /* background: #eee; */
}

.withdraw-amount-input {
    border: none !important;
    background: transparent !important;
    padding: 12px 16px !important;
    font-size: 18px !important;
    font-weight: 600 !important;
    color: #333 !important;
    width: 100% !important;
    outline: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    cursor: default;
}

.max-available-btn {
    display: flex;
    align-items: center;
    padding: 0 20px;
    background: #0CCAAE;
    color: #fff;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    cursor: default;
    user-select: none;
}

/* ── Breakdown ── */
.withdrawal-breakdown {
    background: #f8f9fa;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 16px;
}

.withdrawal-breakdown h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 16px;
    color: #333;
}

.breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 14px;
    color: #555;
}

.breakdown-row + .breakdown-row {
    border-top: 1px solid #e8e8e8;
}

.breakdown-value {
    font-weight: 600;
    color: #333;
}

.breakdown-value .woocommerce-Price-amount {
    font-weight: inherit;
    color: inherit;
}

.fee-value,
.fee-value .woocommerce-Price-amount,
.dark__theme .fee-value,
.dark__theme .fee-value .woocommerce-Price-amount {
    color: #e74c3c !important;
}

.plan-badge {
    display: inline-block;
    /* background: #333; */
    background: #d7d7d7;
    /* color: #fff; */
    color: #606060;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 6px;
    vertical-align: middle;
}

.breakdown-total,
.dark__theme .breakdown-total {
    margin-top: 4px;
    padding-top: 12px !important;
    border-top: 2px solid #0CCAAE !important;
}

.total-value,
.total-value .woocommerce-Price-amount,
.dark__theme .total-value,
.dark__theme .total-value .woocommerce-Price-amount {
    font-size: 20px;
    font-weight: 700;
    color: #0CCAAE !important;
}

/* ── Upgrade Notice ── */
.upgrade-notice {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 12px 16px;
    background: rgba(45, 212, 168, 0.08);
    border: 1px solid rgba(45, 212, 168, 0.2);
    border-radius: 8px;
    font-size: 13px;
    color: #555;
    margin-bottom: 20px;
}

.upgrade-notice svg {
    color: #0CCAAE;
    flex-shrink: 0;
    margin-top: 1px;
}

.upgrade-notice a {
    color: #0CCAAE;
    font-weight: 600;
    text-decoration: underline;
}

/* ── Submit Button ── */
.submit-withdrawal-btn {
    display: block;
    width: 100%;
    max-width: 320px;
    margin: 0 0 0 auto;
    padding: 14px 24px;
    background: #0CCAAE;
    color: #fff;
    border: none;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    text-align: center;
}

.submit-withdrawal-btn:hover {
    background: #25b892;
    transform: translateY(-1px);
}

.submit-withdrawal-btn:active {
    transform: translateY(0);
}

.submit-withdrawal-btn.disabled {
    background: #ccc;
    cursor: not-allowed;
    color: #888;
}

.submit-withdrawal-btn.disabled:hover {
    transform: none;
}

/* ── History Card ── */
.history-card h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 16px;
    color: #111;
}

.history-table-wrap {
    overflow-x: auto;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table thead th {
    text-align: left;
    padding: 10px 16px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #888;
    border-bottom: 1px solid #e8e8e8;
    font-weight: 500;
}

.history-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #333;
    border-bottom: 1px solid #f0f0f0;
}

.ref-id {
    color: #999;
    font-size: 13px;
}

.wd-amount {
    font-weight: 600;
}

.wd-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-completed {
    background: rgba(45, 212, 168, 0.12);
    color: #0CCAAE;
}

.status-requested {
    background: rgba(255, 193, 7, 0.12);
    color: #d4a017;
}

.status-cancelled {
    background: rgba(231, 76, 60, 0.12);
    color: #e74c3c;
}

/* ── WCFM Message Overrides ── */
.withdraw-card .wcfm-message {
    margin: 12px 0;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
}

/* ═══════════════════════════════════
   Responsive
   ═══════════════════════════════════ */
@media (max-width: 768px) {
    .payments-top-row {
        grid-template-columns: 1fr;
    }

    .payments-stats-col {
        flex-direction: row;
    }

    .stat-card {
        flex: 1;
    }

    .balance-amount {
        font-size: 28px;
    }

    .withdraw-input-row {
        flex-direction: column;
    }

    .max-available-btn {
        justify-content: center;
        padding: 12px 20px;
    }
}

/* ═══════════════════════════════════
   Dark Theme (.dark__theme)
   ═══════════════════════════════════ */
.dark__theme .payments-card {
    background: #202020;
    border-color: #333;
}

.dark__theme .balance-label {
    color: #aaa;
}

.dark__theme .balance-badge {
    background: #444;
    color: #ccc;
}

.dark__theme .balance-amount,
.dark__theme .balance-amount .woocommerce-Price-amount {
    color: #fff;
}

.dark__theme .balance-subtitle {
    color: #777;
}

.dark__theme .stat-label {
    color: #888;
}

.dark__theme .stat-amount,
.dark__theme .stat-amount .woocommerce-Price-amount {
    color: #fff;
}

.dark__theme .withdraw-card-header h3 {
    color: #fff;
}

.dark__theme .withdraw-label {
    color: #aaa;
}

.dark__theme .withdraw-input-wrap {
    /* background: #1a1a1a; */
    background: #585858;
    border-color: #333;
}

.dark__theme .currency-prefix {
    /* background: #161616; */
    /* border-color: #333; */
    border-color: #585858;
    background: transparent;
    color: #aaa;
}

.dark__theme .withdraw-amount-input {
    color: #fff !important;
}

.dark__theme .withdrawal-breakdown {
    background: #1a1a1a;
    border-color: #333;
}

.dark__theme .withdrawal-breakdown h4 {
    color: #eee;
}

.dark__theme .breakdown-row {
    color: #ccc;
    border-color: #333 !important;
}

.dark__theme .breakdown-value {
    color: #eee;
}

/* .dark__theme .breakdown-value .woocommerce-Price-amount {
    color: inherit;
} */

.dark__theme .plan-badge {
    background: #444;
    color: #ccc;
}

.dark__theme .upgrade-notice {
    background: rgba(45, 212, 168, 0.06);
    border-color: rgba(45, 212, 168, 0.15);
    color: #aaa;
}

.dark__theme .history-card h3 {
    color: #fff;
}

.dark__theme .history-table thead th {
    color: #777;
    border-color: #333;
}

.dark__theme .history-table tbody td {
    color: #ccc;
    border-color: #2a2a2a;
}

.dark__theme .ref-id {
    color: #666;
}

.dark__theme .submit-withdrawal-btn.disabled {
    background: #333;
    color: #666;
}

#wcfm-main-contentainer input[type="text"].withdraw-amount-input{
    border: none !important;
    background: none !important;
}

.dark__theme .balance-badge{
    background: #333;
    color: #fff;
}



</style>

<!-- Withdrawal 2FA Modal (Light Mode) -->
<div id="wd-2fa-overlay" class="mf2fa-overlay" style="display:none;">
    <div class="mf2fa-modal">
        <div class="mf2fa-header">
            <h3 style="color: #333 !important;"><?php esc_html_e( '2FA Confirmation', 'minimog' ); ?></h3>
            <span class="mf2fa-close" id="wd-2fa-close">&times;</span>
        </div>
        <div class="mf2fa-body">
            <div class="mf2fa-icon">
                <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="28" cy="28" r="28" fill="#e6faf8"/>
                    <path d="M28 14l12 5.5v8.25c0 7.7-5.1 14.9-12 16.75-6.9-1.85-12-9.05-12-16.75V19.5L28 14z" stroke="#4ECDC4" stroke-width="2" fill="none"/>
                    <path d="M22 28.5l4 4 8-8" stroke="#4ECDC4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
            </div>
            <p id="wd-2fa-text"><?php
                printf(
                    esc_html__( 'For security, please enter the 6-digit code sent to your email to confirm the withdrawal of %s.', 'minimog' ),
                    '<strong>' . wc_price( $total_net_available ) . '</strong>'
                );
            ?></p>
            <input type="text" id="wd-2fa-code" class="mf2fa-input" placeholder="<?php esc_attr_e( 'Enter 6-digit code', 'minimog' ); ?>" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
            <button type="button" id="wd-2fa-verify" class="mf2fa-btn"><?php esc_html_e( 'Verify & Confirm', 'minimog' ); ?></button>
            <div id="wd-2fa-message" class="mf2fa-message"></div>
            <span id="wd-2fa-resend" class="mf2fa-resend"><?php esc_html_e( 'Resend code', 'minimog' ); ?></span>
        </div>
    </div>
</div>

<style>
/* Withdrawal 2FA Modal Styles */
.mf2fa-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:999999;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s}
.mf2fa-overlay.active{opacity:1;visibility:visible}
.mf2fa-modal{background:#fff;border-radius:12px;width:420px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.15);overflow:hidden;transform:translateY(20px);transition:transform .3s}
.mf2fa-overlay.active .mf2fa-modal{transform:translateY(0)}
.mf2fa-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #e8e8e8}
.mf2fa-header h3{margin:0;font-size:18px;font-weight:700;color:#1a1a1a}
.mf2fa-close{background:none;border:none;font-size:22px;color:#999;cursor:pointer;padding:0;line-height:1;transition:color .2s}
.mf2fa-close:hover{color:#333}
.mf2fa-body{padding:32px 24px;text-align:center}
.mf2fa-icon{width:56px;height:56px;margin:0 auto 20px}
.mf2fa-icon svg{width:100%;height:100%}
.mf2fa-body p{margin:0 0 24px;font-size:14px;color:#555;line-height:1.6}
.mf2fa-input{width:100%;padding:14px 16px;border:2px solid #e0e0e0;border-radius:8px;font-size:18px;text-align:center;letter-spacing:6px;font-weight:600;color:#1a1a1a;outline:none;transition:border-color .2s;box-sizing:border-box}
.mf2fa-input:focus{border-color:#4ECDC4}
.mf2fa-input::placeholder{letter-spacing:0;font-weight:400;font-size:14px;color:#aaa}
.mf2fa-btn{display:block;width:100%;padding:14px;margin-top:16px;background:#4ECDC4;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;transition:background .2s}
.mf2fa-btn:hover{background:#3dbdb5}
.mf2fa-btn:disabled{background:#b0e8e4;cursor:not-allowed}
.mf2fa-message{margin-top:12px;padding:10px 14px;border-radius:6px;font-size:13px;display:none}
.mf2fa-message.error{display:block;background:#fff5f5;color:#e53e3e;border:1px solid #fed7d7}
.mf2fa-message.success{display:block;background:#f0fff4;color:#38a169;border:1px solid #c6f6d5}
.mf2fa-resend{margin-top:12px;display:inline-block;font-size:13px;color:#4ECDC4;cursor:pointer;border:none;background:none;text-decoration:underline;transition:color .2s}
.mf2fa-resend:hover{color:#3dbdb5}
.mf2fa-spinner{display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:mf2fa-spin .6s linear infinite;vertical-align:middle;margin-right:6px}
@keyframes mf2fa-spin{to{transform:rotate(360deg)}}
</style>

<script>
jQuery(document).ready(function($) {

    var wdToken = '';

    // ── Process the actual withdrawal AJAX ──
    function processWithdrawal() {
        var $btn = $('#wcfm_withdrawal_request_button');
        $btn.prop('disabled', true).text('Processing...');

        $('#wcfm-content').block({
            message: null,
            overlayCSS: { background: '#fff', opacity: 0.6 }
        });

        var data = {
            action                      : 'wcfm_ajax_controller',
            controller                  : 'wcfm-withdrawal-request',
            wcfm_withdrawal_manage_form : $('#wcfm_withdrawal_manage_form').serialize(),
            wcfm_ajax_nonce             : wcfm_params.wcfm_ajax_nonce,
            status                      : 'submit'
        };

        $.post(wcfm_params.ajax_url, data, function(response) {
            if (response) {
                var res = $.parseJSON(response);
                var $msg = $('#wcfm_withdrawal_manage_form .wcfm-message');
                $msg.html('').removeClass('wcfm-success wcfm-error').hide();

                if (typeof wcfm_notification_sound !== 'undefined') {
                    wcfm_notification_sound.play();
                }

                if (res.status) {
                    $msg.html('<span class="wcicon-status-completed"></span> ' + res.message)
                        .addClass('wcfm-success').slideDown();
                    setTimeout(function() { window.location.reload(); }, 2000);
                } else {
                    $msg.html('<span class="wcicon-status-cancelled"></span> ' + res.message)
                        .addClass('wcfm-error').slideDown();
                    $btn.prop('disabled', false).text('Submit Withdrawal');
                }

                if (typeof wcfmMessageHide === 'function') {
                    wcfmMessageHide();
                }
            }
            $('#wcfm-content').unblock();
        }).fail(function() {
            $('#wcfm-content').unblock();
            $btn.prop('disabled', false).text('Submit Withdrawal');
        });
    }

    // ── Submit Withdrawal Button - Check 2FA first ──
    $('#wcfm_withdrawal_request_button').on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        $btn.prop('disabled', true).text('Verifying...');

        // Ask server if 2FA is needed
        $.post(wcfm_params.ajax_url, {
            action: 'modfolio_withdrawal_send_2fa'
        }, function(resp) {
            if (resp.success && resp.data['2fa_not_required']) {
                // No 2FA - process withdrawal directly
                $btn.prop('disabled', false).text('Submit Withdrawal');
                processWithdrawal();
            } else if (resp.success && resp.data['2fa_required']) {
                // Show 2FA modal
                wdToken = resp.data['2fa_token'];
                $btn.prop('disabled', false).text('Submit Withdrawal');
                openWd2fa();
            } else {
                $btn.prop('disabled', false).text('Submit Withdrawal');
                alert(resp.data ? resp.data.message : 'An error occurred.');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Submit Withdrawal');
        });
    });

    // ── Withdrawal 2FA Modal Helpers ──
    var $wdOverlay = $('#wd-2fa-overlay'),
        $wdCode    = $('#wd-2fa-code'),
        $wdVerify  = $('#wd-2fa-verify'),
        $wdMsg     = $('#wd-2fa-message'),
        $wdResend  = $('#wd-2fa-resend');

    function openWd2fa() {
        $wdCode.val('');
        $wdMsg.hide().removeClass('error success');
        $wdOverlay.css('display', 'flex').addClass('active');
        $wdCode.focus();
    }

    function closeWd2fa() {
        $wdOverlay.removeClass('active');
        setTimeout(function() { $wdOverlay.css('display', 'none'); }, 300);
        wdToken = '';
    }

    function showWdMsg(text, type) {
        if (!text) { $wdMsg.hide(); return; }
        $wdMsg.text(text).removeClass('error success').addClass(type).show();
    }

    // Close
    $('#wd-2fa-close').on('click', closeWd2fa);
    $wdOverlay.on('click', function(e) { if (e.target === this) closeWd2fa(); });
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $wdOverlay.hasClass('active')) closeWd2fa();
    });

    // Only digits
    $wdCode.on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
    });

    // Enter key
    $wdCode.on('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); $wdVerify.click(); }
    });

    // Verify
    $wdVerify.on('click', function() {
        var code = $wdCode.val().trim();
        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
            showWdMsg('Please enter a valid 6-digit code.', 'error');
            return;
        }
        $wdVerify.prop('disabled', true).html('<span class="mf2fa-spinner"></span> Verifying...');
        showWdMsg('', '');

        $.post(wcfm_params.ajax_url, {
            action: 'modfolio_withdrawal_verify_2fa',
            token: wdToken,
            code: code
        }, function(resp) {
            $wdVerify.prop('disabled', false).text('Verify & Confirm');
            if (resp.success && resp.data.verified) {
                showWdMsg(resp.data.message, 'success');
                setTimeout(function() {
                    closeWd2fa();
                    processWithdrawal();
                }, 800);
            } else {
                showWdMsg(resp.data ? resp.data.message : 'An error occurred.', 'error');
                $wdCode.val('').focus();
                if (resp.data && resp.data.expired) {
                    setTimeout(closeWd2fa, 2000);
                }
            }
        }).fail(function() {
            $wdVerify.prop('disabled', false).text('Verify & Confirm');
            showWdMsg('An error occurred. Please try again.', 'error');
        });
    });

    // Resend
    $wdResend.on('click', function() {
        if ($wdResend.hasClass('disabled')) return;
        $wdResend.addClass('disabled').text('Sending...');

        $.post(wcfm_params.ajax_url, {
            action: 'modfolio_withdrawal_send_2fa'
        }, function(resp) {
            if (resp.success && resp.data['2fa_required']) {
                wdToken = resp.data['2fa_token'];
                showWdMsg(resp.data.message, 'success');
            } else if (resp.data) {
                showWdMsg(resp.data.message, 'error');
            }
            // 30s cooldown
            var cd = 30;
            $wdResend.text('Resend code (' + cd + 's)');
            var t = setInterval(function() {
                cd--;
                if (cd <= 0) {
                    clearInterval(t);
                    $wdResend.removeClass('disabled').text('Resend code');
                } else {
                    $wdResend.text('Resend code (' + cd + 's)');
                }
            }, 1000);
        });
    });
});
</script>
