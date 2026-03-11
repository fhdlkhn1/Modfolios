<?php
/**
 * Buyer Dashboard - Custom My Account Dashboard
 *
 * Handles enqueuing of buyer dashboard CSS and any additional functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue buyer dashboard styles
 */
function modfolio_enqueue_buyer_dashboard_styles() {
    // Only load on My Account page
    if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
        return;
    }

    wp_enqueue_style(
        'modfolio-buyer-dashboard',
        MINIMOG_THEME_URI . '/assets/css/buyer-dashboard.css',
        array(),
        MINIMOG_THEME_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'modfolio_enqueue_buyer_dashboard_styles', 25 );

/**
 * Update last login timestamp
 */
function modfolio_update_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', current_time( 'mysql' ) );
}
add_action( 'wp_login', 'modfolio_update_last_login', 10, 2 );

/**
 * Get buyer spending data for current month
 */
function modfolio_get_monthly_spending( $user_id ) {
    global $wpdb;

    $current_month_start = date( 'Y-m-01 00:00:00' );
    $current_month_end = date( 'Y-m-t 23:59:59' );

    // Get completed orders for this month
    $orders = wc_get_orders( array(
        'customer_id' => $user_id,
        'status' => array( 'completed', 'processing' ),
        'date_created' => $current_month_start . '...' . $current_month_end,
        'return' => 'ids',
    ) );

    $total_spending = 0;

    foreach ( $orders as $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $total_spending += $order->get_total();
        }
    }

    return $total_spending;
}

/**
 * Get buyer spending data for chart (last 8 months)
 */
function modfolio_get_spending_chart_data( $user_id ) {
    $data = array();
    $labels = array();

    for ( $i = 7; $i >= 0; $i-- ) {
        $month_start = date( 'Y-m-01 00:00:00', strtotime( "-{$i} months" ) );
        $month_end = date( 'Y-m-t 23:59:59', strtotime( "-{$i} months" ) );
        $month_label = date( 'M', strtotime( "-{$i} months" ) );

        $orders = wc_get_orders( array(
            'customer_id' => $user_id,
            'status' => array( 'completed', 'processing' ),
            'date_created' => $month_start . '...' . $month_end,
            'return' => 'ids',
        ) );

        $monthly_total = 0;
        foreach ( $orders as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $monthly_total += $order->get_total();
            }
        }

        $labels[] = $month_label;
        $data[] = $monthly_total;
    }

    return array(
        'labels' => $labels,
        'data' => $data,
    );
}

/**
 * AJAX handler for getting spending chart data
 */
function modfolio_ajax_get_spending_chart() {
    check_ajax_referer( 'modfolio_dashboard_nonce', 'nonce' );

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( 'Not logged in' );
    }

    $chart_data = modfolio_get_spending_chart_data( $user_id );
    wp_send_json_success( $chart_data );
}
add_action( 'wp_ajax_modfolio_get_spending_chart', 'modfolio_ajax_get_spending_chart' );

/**
 * Register Help & Support endpoint
 */
function modfolio_add_help_support_endpoint() {
    add_rewrite_endpoint( 'help-center', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'modfolio_add_help_support_endpoint' );

/**
 * Add Help & Support to My Account menu
 */
function modfolio_add_help_support_menu_item( $items ) {
    // Insert Help & Support before Logout
    $logout = false;
    if ( isset( $items['customer-logout'] ) ) {
        $logout = $items['customer-logout'];
        unset( $items['customer-logout'] );
    }

    $items['help-center'] = __( 'Help & Support', 'minimog' );

    if ( $logout ) {
        $items['customer-logout'] = $logout;
    }

    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'modfolio_add_help_support_menu_item', 99 );

/**
 * Rename the "orders" tab back to "Orders"
 */
function modfolio_rename_orders_tab( $items ) {
    if ( isset( $items['orders'] ) ) {
        $items['orders'] = __( 'Orders', 'minimog' );
    }
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'modfolio_rename_orders_tab', 100 );

/**
 * Add Help & Support endpoint query var
 */
function modfolio_help_support_query_vars( $vars ) {
    $vars[] = 'help-center';
    return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'modfolio_help_support_query_vars' );

/**
 * Add Help & Support endpoint content
 */
function modfolio_help_support_endpoint_content() {
    wc_get_template( 'myaccount/help-support.php' );
}
add_action( 'woocommerce_account_help-center_endpoint', 'modfolio_help_support_endpoint_content' );
