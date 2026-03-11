<?php
/**
 * Template: Premium products tab for WCFM store
 * File: your-child-theme/wcfm/store/wcfmmp-view-store-exclusive.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Resolve the correct vendor ID from $store_user / $store_info
 */
$vendor_id = 0;

// Prefer the WCFM store object if present
if ( isset( $store_user ) && $store_user ) {

    // If it's a WCFM store object, it has get_id()
    if ( is_object( $store_user ) && method_exists( $store_user, 'get_id' ) ) {
        $vendor_id = (int) $store_user->get_id();

    // Fallback: sometimes WCFM passes a WP_User
    } elseif ( is_object( $store_user ) && isset( $store_user->ID ) ) {
        $vendor_id = (int) $store_user->ID;

    // Very last fallback if it's just an ID
    } else {
        $vendor_id = (int) $store_user;
    }
}

// Extra safety: try to read from $store_info if it's there
if ( ! $vendor_id && ! empty( $store_info ) && is_array( $store_info ) ) {
    if ( ! empty( $store_info['vendor_id'] ) ) {
        $vendor_id = (int) $store_info['vendor_id'];
    } elseif ( ! empty( $store_info['id'] ) ) {
        $vendor_id = (int) $store_info['id'];
    }
}

$current_user_id = get_current_user_id();

/* -------------------------------------------------------------
 *  ACCESS LOGIC (Vendor, Admin, Subscriber Allowed)
 * ------------------------------------------------------------- */

// Vendor can always view
$is_vendor_itself = ( $current_user_id && $current_user_id == $vendor_id );

// Admin can always view
$is_admin = current_user_can('manage_options');

// Subscriber check using WC Subscriptions
$is_subscriber = false;
if ( function_exists( 'wcfm_store_sub_is_subscriber' ) ) {
    $is_subscriber = wcfm_store_sub_is_subscriber( $current_user_id, $vendor_id );
}

// Final access logic
$can_access = ( $is_vendor_itself || $is_admin || $is_subscriber );

// Get vendor/creator name
$creator_name = '';
$vendor_user = get_user_by( 'id', $vendor_id );
if ( $vendor_user ) {
    $creator_name = $vendor_user->display_name;
}

// Get store URL for membership plans link
$store_url = '';
if ( function_exists( 'wcfmmp_get_store_url' ) ) {
    $store_url = wcfmmp_get_store_url( $vendor_id );
}
$membership_plans_url = trailingslashit( $store_url ) . 'membership-plans/#tab_links_area';

/* -------------------------------------------------------------
 *  Pagination + Query
 * ------------------------------------------------------------- */

$paged = max(
    1,
    get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' )
);

$per_page = apply_filters( 'is_exclusive_products_per_page', 12 );

$args = array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'author'         => $vendor_id,
    'meta_query'     => array(
        array(
            'key'   => '_is_exclusive_product',
            'value' => 'yes',
        ),
    ),
);

$exclusive_query = new WP_Query( $args );
$has_products = $exclusive_query->have_posts();
?>

<style>
.premium-notice-box {
    /* max-width: 600px;
    margin: 40px auto; */
    padding: 40px 30px;
    text-align: center;
    /* background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); */
    background: #FFF;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
}

.premium-notice-box .notice-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #00c4aa 0%, #00a896 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.premium-notice-box .notice-icon svg {
    width: 35px;
    height: 35px;
    fill: #ffffff;
}

#wcfmmp-store h2.notice-title,
.premium-notice-box .notice-title {
    font-size: 26px !important;
    font-weight: 700 !important;
    color: #000 !important;
    margin-bottom: 12px;
}

.premium-notice-box .notice-description {
    font-size: 16px;
    color: #6c757d;
    line-height: 1.6 !important;
    margin-bottom: 25px !important;
}

.premium-notice-box .notice-description .creator-name {
    font-weight: 600;
    color: #1a1a2e;
    text-transform: capitalize;
}

.premium-notice-box .notice-btn {
    display: inline-block;
    padding: 14px 32px;
    background: linear-gradient(135deg, #00c4aa 0%, #00a896 100%);
    color: #ffffff;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 196, 170, 0.35);
}

.premium-notice-box .notice-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 196, 170, 0.45);
    color: #ffffff;
    text-decoration: none;
}

/* .premium-notice-box.no-content .notice-icon {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
} */

.premium-notice-box.no-content .notice-icon svg {
    fill: #FFF;
}
</style>

<div class="wcfmmp-store-products wcfmmp-store-exclusive-products">

<?php if ( ! $can_access ) : ?>
    <!-- User is NOT a subscriber - show subscription prompt -->
    <div class="premium-notice-box">
        <div class="notice-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
        </div>
        <h2 class="notice-title"><?php esc_html_e( 'Premium Content', 'your-textdomain' ); ?></h2>
        <p class="notice-description">
            <?php esc_html_e( 'Purchase a store subscription to gain access to premium content.', 'your-textdomain' ); ?>
        </p>
        <a href="<?php echo esc_url( $membership_plans_url ); ?>" class="notice-btn">
            <?php esc_html_e( 'View Subscription Plans', 'your-textdomain' ); ?>
        </a>
    </div>

<?php elseif ( ! $has_products ) : ?>
    <!-- User has access but no premium products yet -->
    <div class="premium-notice-box no-content">
        <div class="notice-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>
        </div>
        <h2 class="notice-title"><?php esc_html_e( 'No Premium Content Yet', 'your-textdomain' ); ?></h2>
        <p class="notice-description">
            <?php
            printf(
                /* translators: %s: creator name */
                esc_html__( '%s hasn\'t shared any subscriber-only content yet. Check back soon for BTS and early access assets.', 'your-textdomain' ),
                '<span class="creator-name">' . esc_html( $creator_name ) . '</span>'
            );
            ?>
        </p>
    </div>

<?php else : ?>
    <!-- User has access and there are products to show -->
    <?php woocommerce_product_loop_start(); ?>

    <?php while ( $exclusive_query->have_posts() ) : $exclusive_query->the_post(); ?>
        <?php wc_get_template_part( 'content', 'product' ); ?>
    <?php endwhile; ?>

    <?php woocommerce_product_loop_end(); ?>

    <?php
    echo paginate_links( array(
        'total'   => $exclusive_query->max_num_pages,
        'current' => $paged,
    ) );
    ?>

<?php endif; ?>

</div>

<?php
wp_reset_postdata();
