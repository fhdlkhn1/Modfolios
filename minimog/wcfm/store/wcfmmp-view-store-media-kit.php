<?php
/**
 * Store Live Media Kit Tab Template
 * Displays social media stats and creator information from store settings
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

// Get vendor profile settings (dynamic data from store settings page)
$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
$vendor_data = is_array( $vendor_data ) ? $vendor_data : array();

// ===== Social Media Stats (from media_kit) =====
$instagram = array(
    'followers' => isset($vendor_data['media_kit']['instagram']['followers']) ? $vendor_data['media_kit']['instagram']['followers'] : '',
    'engagement' => isset($vendor_data['media_kit']['instagram']['engagement_rate']) ? $vendor_data['media_kit']['instagram']['engagement_rate'] : '',
    'avg_likes' => isset($vendor_data['media_kit']['instagram']['avg_likes']) ? $vendor_data['media_kit']['instagram']['avg_likes'] : ''
);

$tiktok = array(
    'followers' => isset($vendor_data['media_kit']['tiktok']['followers']) ? $vendor_data['media_kit']['tiktok']['followers'] : '',
    'avg_views' => isset($vendor_data['media_kit']['tiktok']['avg_views']) ? $vendor_data['media_kit']['tiktok']['avg_views'] : '',
    'viral_count' => isset($vendor_data['media_kit']['tiktok']['viral_count']) ? $vendor_data['media_kit']['tiktok']['viral_count'] : ''
);

$youtube = array(
    'subscribers' => isset($vendor_data['media_kit']['youtube']['subscribers']) ? $vendor_data['media_kit']['youtube']['subscribers'] : '',
    'watch_time' => isset($vendor_data['media_kit']['youtube']['watch_time']) ? $vendor_data['media_kit']['youtube']['watch_time'] : '',
    'avg_views' => isset($vendor_data['media_kit']['youtube']['avg_views']) ? $vendor_data['media_kit']['youtube']['avg_views'] : ''
);

$snapchat = array(
    'daily_users' => isset($vendor_data['media_kit']['snapchat']['daily_users']) ? $vendor_data['media_kit']['snapchat']['daily_users'] : '',
    'story_viewers' => isset($vendor_data['media_kit']['snapchat']['story_viewers']) ? $vendor_data['media_kit']['snapchat']['story_viewers'] : '',
    'swipe_rate' => isset($vendor_data['media_kit']['snapchat']['swipe_rate']) ? $vendor_data['media_kit']['snapchat']['swipe_rate'] : ''
);

// ===== Demographics =====
$gender_female = isset($vendor_data['demographics']['gender_female']) ? intval($vendor_data['demographics']['gender_female']) : 50;
$gender_male = 100 - $gender_female;
$dominant_age = isset($vendor_data['demographics']['dominant_age']) ? $vendor_data['demographics']['dominant_age'] : '';

// Top Locations - comma separated, calculate percentages dynamically
$top_locations_raw = isset($vendor_data['demographics']['top_locations']) ? $vendor_data['demographics']['top_locations'] : '';
$locations = array();
if ( ! empty($top_locations_raw) ) {
    $location_list = array_map('trim', explode(',', $top_locations_raw));
    $location_count = count($location_list);
    if ($location_count > 0) {
        // Distribute percentages (first location gets more, then descending)
        $base_percentage = 100 / $location_count;
        foreach ($location_list as $index => $city) {
            if (!empty($city)) {
                // Give higher weight to earlier locations
                $weight = max(15, intval($base_percentage + (($location_count - $index - 1) * 5)));
                $locations[] = array('city' => $city, 'percentage' => min(100, $weight));
            }
        }
    }
}

// ===== Achievements =====
$achievements = isset($vendor_data['achievements']) ? $vendor_data['achievements'] : array();

// ===== Additional Info =====
$travel_available = isset($vendor_data['travel']['available']) ? $vendor_data['travel']['available'] : 'no';
$travel_display = ($travel_available === 'yes') ? __('Yes', 'wc-multivendor-marketplace') :
                  (($travel_available === 'limited') ? __('Limited', 'wc-multivendor-marketplace') : __('No', 'wc-multivendor-marketplace'));

$languages = isset($vendor_data['languages']) ? $vendor_data['languages'] : array();
$languages_display = !empty($languages) ? implode(', ', $languages) : '';

// Collab Preferences
$collab_prefs = array();
if (isset($vendor_data['collab']['ugc_video']) && $vendor_data['collab']['ugc_video'] === 'yes') {
    $collab_prefs[] = __('UGC Video', 'wc-multivendor-marketplace');
}
if (isset($vendor_data['collab']['photography']) && $vendor_data['collab']['photography'] === 'yes') {
    $collab_prefs[] = __('Photography', 'wc-multivendor-marketplace');
}
if (isset($vendor_data['collab']['modeling']) && $vendor_data['collab']['modeling'] === 'yes') {
    $collab_prefs[] = __('Modeling (In-person)', 'wc-multivendor-marketplace');
}
if (isset($vendor_data['collab']['sponsored_posts']) && $vendor_data['collab']['sponsored_posts'] === 'yes') {
    $collab_prefs[] = __('Sponsored Posts', 'wc-multivendor-marketplace');
}
if (isset($vendor_data['collab']['tfp']) && $vendor_data['collab']['tfp'] === 'yes') {
    $collab_prefs[] = __('TFP (Time-for-Print)', 'wc-multivendor-marketplace');
}
$collab_display = !empty($collab_prefs) ? implode(', ', $collab_prefs) : '';

// Last sync time
$last_sync = '12 Mins Ago';
?>

<div class="wcfm-store-media-kit-wrap">

    <!-- Live Data Badge -->
    <div class="media-kit-live-badge">
        <span class="live-dot"></span>
        <span class="live-text"><?php _e( 'Live Data', 'wc-multivendor-marketplace' ); ?></span>
        <span class="sync-time"><?php printf( __( 'Synced %s', 'wc-multivendor-marketplace' ), esc_html( $last_sync ) ); ?></span>
    </div>

    <!-- Social Media Stats Grid -->
    <div class="social-stats-grid">

        <!-- Instagram -->
        <div class="social-stat-card">
            <div class="stat-header">
                <div class="social-icon instagram-icon">
                    <svg width="27" height="26" viewBox="0 0 27 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M25.9631 7.64555C25.9021 6.26376 25.6784 5.31389 25.3579 4.49071C25.0273 3.61698 24.5188 2.83475 23.8525 2.18444C23.2014 1.52403 22.4131 1.01084 21.5485 0.685813C20.7198 0.365767 19.7737 0.142243 18.3904 0.0813832C16.9967 0.0152403 16.5543 0 13.0197 0C9.48504 0 9.04263 0.0152403 7.65415 0.0762015C6.27086 0.137163 5.31986 0.360789 4.49601 0.680632C3.62105 1.01084 2.83793 1.51885 2.1869 2.18444C1.52574 2.8347 1.01223 3.62211 0.686585 4.48573C0.366179 5.31389 0.142454 6.25868 0.0814747 7.64037C0.0153083 9.03247 0 9.47438 0 13.0051C0 16.5357 0.0153083 16.9777 0.0762872 18.3646C0.137317 19.7463 0.361194 20.6962 0.681652 21.5194C1.01223 22.3931 1.52574 23.1754 2.1869 23.8257C2.83793 24.4861 3.62624 24.9993 4.49082 25.3243C5.31981 25.6443 6.26567 25.8679 7.64922 25.9287C9.03744 25.9899 9.48011 26.0049 13.0147 26.0049C16.5494 26.0049 16.9918 25.9899 18.3803 25.9287C19.7636 25.8678 20.7146 25.6444 21.5384 25.3243C22.4037 24.9901 23.1895 24.479 23.8455 23.8238C24.5015 23.1686 25.0132 22.3837 25.3478 21.5194C25.6681 20.6913 25.892 19.7463 25.953 18.3646C26.014 16.9777 26.0292 16.5357 26.0292 13.0051C26.0292 9.47438 26.0241 9.03242 25.9631 7.64555ZM23.6187 18.263C23.5626 19.533 23.3491 20.2188 23.1711 20.676C22.7336 21.8089 21.8335 22.708 20.6993 23.145C20.2415 23.3228 19.55 23.5361 18.2835 23.5919C16.9103 23.653 16.4985 23.6681 13.0249 23.6681C9.55121 23.6681 9.13422 23.653 7.76599 23.5919C6.49453 23.5361 5.80795 23.3228 5.35023 23.145C4.78585 22.9366 4.27213 22.6064 3.8551 22.1746C3.4228 21.753 3.09223 21.245 2.88361 20.6812C2.7056 20.224 2.49205 19.533 2.43626 18.2681C2.37502 16.8965 2.35997 16.4849 2.35997 13.0152C2.35997 9.5454 2.37502 9.12889 2.43626 7.76244C2.49205 6.49242 2.7056 5.8066 2.88361 5.3494C3.09223 4.7854 3.4228 4.27236 3.86029 3.85569C4.2822 3.42389 4.79079 3.09368 5.35541 2.88555C5.81314 2.70774 6.50491 2.49438 7.77117 2.43845C9.14434 2.37749 9.5564 2.36225 13.0298 2.36225C16.5087 2.36225 16.9205 2.37749 18.2887 2.43845C19.5601 2.49443 20.2467 2.70769 20.7045 2.8855C21.2688 3.09368 21.7826 3.42389 22.1996 3.85569C22.6319 4.27739 22.9625 4.7854 23.1711 5.3494C23.3491 5.8066 23.5626 6.49734 23.6186 7.76244C23.6797 9.13407 23.6949 9.5454 23.6949 13.0152C23.6949 16.4849 23.6797 16.8913 23.6187 18.263Z" fill="black"/>
                        <path d="M13.0199 6.32561C9.32768 6.32561 6.33203 9.31769 6.33203 13.0059C6.33203 16.6942 9.32768 19.6863 13.0199 19.6863C16.7122 19.6863 19.7077 16.6942 19.7077 13.0059C19.7077 9.31769 16.7122 6.32561 13.0199 6.32561ZM13.0199 17.3393C10.6246 17.3393 8.68158 15.3988 8.68158 13.0059C8.68158 10.6131 10.6246 8.67261 13.0198 8.67261C15.4153 8.67261 17.3581 10.6131 17.3581 13.0059C17.3581 15.3988 15.4153 17.3393 13.0199 17.3393ZM21.5336 6.06144C21.5336 6.92272 20.8345 7.62103 19.9721 7.62103C19.1099 7.62103 18.4108 6.92272 18.4108 6.06144C18.4108 5.20006 19.1099 4.50195 19.9721 4.50195C20.8345 4.50195 21.5336 5.20001 21.5336 6.06144Z" fill="black"/>
                    </svg>
                </div>
                <h3 class="social-platform"><?php _e( 'Instagram', 'wc-multivendor-marketplace' ); ?></h3>
            </div>
            <div class="social-metrics">
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $instagram['followers'] ?: '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Followers', 'wc-multivendor-marketplace' ); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $instagram['engagement'] ? $instagram['engagement'] . '%' : '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Engagement', 'wc-multivendor-marketplace' ); ?></span>
                </div>
            </div>
            <div class="metric-views">
                <span class="metric-value"><?php echo esc_html( $instagram['avg_likes'] ?: '-' ); ?></span>
                <span class="metric-label"><?php _e( 'Avg. Views / Post', 'wc-multivendor-marketplace' ); ?></span>
            </div>
        </div>

        <!-- TikTok -->
        <div class="social-stat-card">
            <div class="stat-header">
                <div class="social-icon tiktok-icon">
                    <svg width="26" height="30" viewBox="0 0 26 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.42459 29.0817C7.30765 29.0817 5.31113 28.4063 3.65086 27.1283C3.33971 26.8887 3.0436 26.6301 2.76421 26.3541C0.826195 24.4388 -0.149063 21.8879 0.0184974 19.1712C0.145162 17.1178 0.972171 15.1384 2.34844 13.5974C4.17115 11.5565 6.68399 10.4325 9.42516 10.4325C9.89603 10.4325 10.3715 10.4677 10.8395 10.537L11.5665 10.6449V17.0968L10.447 16.7271C10.1218 16.6193 9.78139 16.5645 9.43879 16.5646C8.57202 16.5646 7.76035 16.9043 7.15316 17.5206C6.5488 18.134 6.22334 18.9451 6.23584 19.8056C6.25231 20.9149 6.83678 21.9203 7.79898 22.4963C8.24202 22.7609 8.7464 22.916 9.25874 22.9444C9.66315 22.9666 10.0625 22.9137 10.4464 22.7871C11.7591 22.3537 12.6412 21.1359 12.6412 19.7573L12.6469 13.0663V0H18.8131L18.8205 0.84405C18.8239 1.22631 18.8631 1.60858 18.9369 1.97892C19.2266 3.43527 20.0434 4.73315 21.2367 5.63287C22.2961 6.43204 23.5604 6.85407 24.8918 6.85407C24.9231 6.85407 24.9231 6.85407 25.2076 6.87395L26 6.92961V13.0072L25.716 13.0067H24.8589C23.2168 13.0067 21.6781 12.6937 20.153 12.0501C19.6986 11.8582 19.2561 11.6389 18.829 11.3941L18.8483 19.7897C18.8381 22.273 17.8566 24.6024 16.085 26.3535C14.6474 27.7741 12.8332 28.6818 10.8383 28.9771C10.3703 29.0467 9.89777 29.0816 9.42459 29.0817ZM9.42459 12.1365C7.17588 12.1365 5.11403 13.0583 3.61906 14.7322C2.49669 15.9892 1.8219 17.6029 1.71852 19.2757C1.5822 21.4937 2.37854 23.5772 3.96156 25.1414C4.19046 25.3675 4.43527 25.5816 4.68974 25.7776C6.04953 26.8244 7.68708 27.3777 9.42459 27.3777C9.81424 27.3777 10.2056 27.3487 10.589 27.2919C12.2231 27.0499 13.7096 26.3064 14.8876 25.142C16.3349 23.7118 17.1363 21.8107 17.1449 19.7886L17.1182 8.06561L18.4944 9.12721C19.2071 9.67641 19.9875 10.1315 20.8164 10.4813C21.9507 10.9601 23.0941 11.226 24.2954 11.289V8.53706C22.8107 8.42573 21.4083 7.89805 20.2104 6.99436C18.6824 5.84246 17.6368 4.17935 17.2653 2.31233C17.2255 2.11183 17.1931 1.90905 17.1693 1.70457H14.3503V13.0669L14.3446 19.7579C14.3446 21.8726 12.9928 23.7402 10.9803 24.4047C10.3936 24.5984 9.783 24.6791 9.16502 24.6456C8.37777 24.6024 7.60301 24.3644 6.92369 23.9583C5.45086 23.0773 4.55626 21.5341 4.53127 19.8306C4.51195 18.5106 5.01123 17.2655 5.9382 16.3244C6.86802 15.3803 8.11081 14.8606 9.43766 14.8606C9.57966 14.8606 9.72052 14.8669 9.86139 14.8788V12.1484C9.71598 12.1405 9.57 12.1365 9.42459 12.1365Z" fill="black"/>
                    </svg>
                </div>
                <h3 class="social-platform"><?php _e( 'TikTok', 'wc-multivendor-marketplace' ); ?></h3>
            </div>
            <div class="social-metrics">
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $tiktok['followers'] ?: '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Followers', 'wc-multivendor-marketplace' ); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $tiktok['avg_views'] ? $tiktok['avg_views'] . '%' : '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Engagement', 'wc-multivendor-marketplace' ); ?></span>
                </div>
            </div>
            <div class="metric-views">
                <span class="metric-value"><?php echo esc_html( $tiktok['viral_count'] ?: '-' ); ?></span>
                <span class="metric-label"><?php _e( 'Avg. Views / Post', 'wc-multivendor-marketplace' ); ?></span>
            </div>
        </div>

        <!-- YouTube -->
        <div class="social-stat-card">
            <div class="stat-header">
                <div class="social-icon youtube-icon">
                    <svg width="28" height="22" viewBox="0 0 28 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.3095 9.35203L12.1232 5.96719C11.7674 5.77255 11.347 5.77982 10.9984 5.98639C10.6495 6.19318 10.4414 6.55851 10.4414 6.964V13.6756C10.4414 14.0792 10.6482 14.4439 10.9948 14.6509C11.1757 14.759 11.3763 14.8133 11.5773 14.8133C11.7655 14.813 11.9505 14.7659 12.1159 14.6763L18.3024 11.3498C18.4827 11.2529 18.6334 11.1092 18.7386 10.9338C18.8439 10.7583 18.8999 10.5577 18.9006 10.3532C18.9014 10.1485 18.8469 9.94744 18.7429 9.77121C18.6388 9.59498 18.4891 9.45015 18.3095 9.35203ZM12.0824 12.8313V7.81515L16.706 10.345L12.0824 12.8313Z" fill="black"/>
                        <path d="M27.7958 5.0057L27.7946 4.99291C27.7708 4.76752 27.5348 2.76277 26.5602 1.74306C25.4337 0.543683 24.1565 0.397986 23.5423 0.328146C23.4959 0.323024 23.4495 0.317464 23.4032 0.311465L23.3543 0.306379C19.6524 0.0371899 14.0618 0.000437528 14.0058 0.000218764L14.0009 0L13.996 0.000218764C13.94 0.000437528 8.34935 0.0371899 4.61412 0.306379L4.56478 0.311465C4.52502 0.316825 4.48122 0.321747 4.43358 0.327326C3.82646 0.39733 2.56298 0.5433 1.43329 1.78599C0.505017 2.79482 0.236703 4.75664 0.209138 4.9771L0.205966 5.0057C0.197599 5.0995 0 7.33226 0 9.57372V11.669C0 13.9105 0.197599 16.1433 0.205966 16.2373L0.207443 16.2513C0.231179 16.4731 0.467006 18.4412 1.43712 19.4612C2.49637 20.6204 3.83564 20.7738 4.55603 20.8563C4.6699 20.8693 4.76796 20.8804 4.83479 20.8922L4.89955 20.9012C7.03698 21.1045 13.7385 21.2047 14.0227 21.2088L14.0312 21.209L14.0398 21.2088C14.0957 21.2086 19.6862 21.1718 23.3881 20.9026L23.437 20.8975C23.4838 20.8913 23.5364 20.8858 23.594 20.8798C24.1978 20.8157 25.4544 20.6826 26.5685 19.4567C27.4968 18.4478 27.7653 16.4859 27.7927 16.2657L27.7958 16.237C27.8042 16.143 28.002 13.9105 28.002 11.669V9.57372C28.0018 7.3322 27.8042 5.09972 27.7958 5.0057ZM26.3608 11.669C26.3608 13.7437 26.1797 15.8802 26.1626 16.0764C26.093 16.6167 25.8098 17.8579 25.3576 18.3495C24.6603 19.1166 23.944 19.1927 23.421 19.2481C23.3627 19.254 23.3044 19.2606 23.2462 19.2677C19.6657 19.5266 14.2861 19.5663 14.0383 19.5678C13.7604 19.5638 7.15681 19.4627 5.08457 19.2703C4.97836 19.2529 4.86362 19.2397 4.74275 19.226C4.12939 19.1557 3.28977 19.0596 2.6442 18.3495L2.629 18.3332C2.18463 17.8703 1.9097 16.7096 1.83981 16.0828C1.82679 15.9346 1.64095 13.7727 1.64095 11.669V9.57372C1.64095 7.50147 1.8217 5.36721 1.8392 5.16704C1.92233 4.5306 2.21072 3.36459 2.6442 2.89326C3.36284 2.10281 4.12064 2.01525 4.62183 1.95734C4.66968 1.95176 4.71431 1.94667 4.75555 1.94131C8.38823 1.68109 13.8065 1.64243 14.0009 1.64095C14.1953 1.64221 19.6116 1.68109 23.2121 1.94131C23.2563 1.94689 23.3045 1.95241 23.3564 1.95843C23.872 2.01717 24.6511 2.10604 25.3662 2.86871L25.3727 2.87576C25.8172 3.33872 26.0921 4.51972 26.1619 5.15911C26.1743 5.29907 26.3608 7.46554 26.3608 9.57372V11.669Z" fill="black"/>
                    </svg>
                </div>
                <h3 class="social-platform"><?php _e( 'Youtube', 'wc-multivendor-marketplace' ); ?></h3>
            </div>
            <div class="social-metrics">
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $youtube['subscribers'] ?: '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Followers', 'wc-multivendor-marketplace' ); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $youtube['watch_time'] ? $youtube['watch_time'] . '%' : '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Engagement', 'wc-multivendor-marketplace' ); ?></span>
                </div>
            </div>
            <div class="metric-views">
                <span class="metric-value"><?php echo esc_html( $youtube['avg_views'] ?: '-' ); ?></span>
                <span class="metric-label"><?php _e( 'Avg. Views / Post', 'wc-multivendor-marketplace' ); ?></span>
            </div>
        </div>

        <!-- Snapchat -->
        <div class="social-stat-card">
            <div class="stat-header">
                <div class="social-icon snapchat-icon">
                    <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24.5967 0H5.75494C2.58166 0 0 2.58166 0 5.75494V24.5965C0 27.7699 2.58166 30.3515 5.75494 30.3515H24.5966C27.7699 30.3515 30.3515 27.7699 30.3516 24.5966V5.75488C30.3516 2.5816 27.7699 0 24.5967 0ZM28.5342 24.5967C28.5342 26.7678 26.7678 28.5341 24.5967 28.5341H5.75494C3.58379 28.5341 1.81742 26.7678 1.81742 24.5967V5.75494C1.81742 3.58379 3.58379 1.81742 5.75494 1.81742H24.5966C26.7677 1.81742 28.5341 3.58379 28.5342 5.75494V24.5967Z" fill="black"/>
                        <path d="M25.655 20.2109C24.094 19.2492 22.7966 17.8531 22.0635 16.3749L23.9481 15.4121C24.4875 15.1366 24.8874 14.6675 25.074 14.0913C25.2606 13.515 25.2116 12.9007 24.9361 12.3612C24.3673 11.2477 22.9987 10.8047 21.8852 11.3732L21.386 11.6282V10.1354C21.386 6.71137 18.6004 3.92578 15.1764 3.92578C11.7524 3.92578 8.96678 6.71137 8.96678 10.1354V11.6284L8.46722 11.3732C7.35382 10.8046 5.98515 11.2476 5.41636 12.3612C5.14082 12.9007 5.09185 13.515 5.27847 14.0913C5.46508 14.6676 5.86487 15.1367 6.40421 15.4121L8.28921 16.3749C7.5562 17.8531 6.25873 19.2492 4.69782 20.2108C4.56981 20.2897 4.46336 20.399 4.388 20.5291C4.31265 20.6592 4.27076 20.8059 4.26607 20.9562C4.26138 21.1064 4.29405 21.2555 4.36114 21.39C4.42824 21.5246 4.52766 21.6403 4.65051 21.727C4.77915 21.8177 5.68092 22.4337 6.84893 22.7217C6.64316 23.4922 6.56954 24.3978 7.07437 24.9802C7.1889 25.1123 7.33912 25.2086 7.50701 25.2575C7.6749 25.3064 7.85333 25.3058 8.0209 25.2558C8.54334 25.1 10.1195 24.7322 11.1261 24.8999C11.6054 24.9798 12.0789 25.2501 12.5804 25.5364C13.3069 25.9511 14.1302 26.4212 15.173 26.4253H15.1765L15.18 26.4253C16.2228 26.4213 17.0461 25.9512 17.7726 25.5365C18.274 25.2502 18.7476 24.9798 19.2269 24.8999C20.2278 24.7332 21.8082 25.1005 22.332 25.2559C22.4996 25.3059 22.678 25.3065 22.8459 25.2576C23.0138 25.2087 23.164 25.1124 23.2786 24.9802C23.7833 24.3979 23.7097 23.4924 23.504 22.7217C24.6721 22.4337 25.5738 21.8178 25.7024 21.7271C25.8252 21.6404 25.9246 21.5246 25.9917 21.39C26.0587 21.2555 26.0914 21.1065 26.0867 20.9562C26.082 20.806 26.0401 20.6592 25.9647 20.5292C25.8894 20.3991 25.783 20.2897 25.655 20.2109ZM22.2864 21.0512C21.9731 21.0451 21.677 21.1968 21.5041 21.4586C21.419 21.5874 21.3681 21.7356 21.3562 21.8895C21.3443 22.0433 21.3717 22.1976 21.4359 22.3379C21.5661 22.6222 21.6815 22.9581 21.7572 23.2498C20.9557 23.0922 19.8573 22.9524 18.9279 23.1072C18.1283 23.2404 17.4605 23.6217 16.8713 23.9581C16.2639 24.3049 15.7392 24.6043 15.1763 24.6078C14.6134 24.6043 14.0887 24.3048 13.4813 23.9581C12.8922 23.6217 12.2243 23.2404 11.4247 23.1072C11.1395 23.0596 10.8383 23.0399 10.5346 23.0399C9.84881 23.0399 9.15043 23.1408 8.59474 23.25C8.67002 22.9607 8.78503 22.6271 8.91663 22.3379C8.98085 22.1976 9.00829 22.0433 8.99636 21.8895C8.98443 21.7356 8.93353 21.5874 8.84846 21.4586C8.67542 21.1968 8.37967 21.0451 8.06619 21.0512C7.67826 21.0622 7.2831 20.9735 6.92984 20.8518C8.49562 19.5835 9.69617 17.9667 10.3122 16.2598C10.3873 16.0517 10.3839 15.8233 10.3027 15.6175C10.2214 15.4117 10.0678 15.2427 9.87081 15.142L7.23075 13.7935C7.17806 13.7667 7.13119 13.7298 7.09285 13.6848C7.0545 13.6398 7.02544 13.5876 7.00732 13.5313C6.98901 13.4751 6.982 13.4158 6.9867 13.3569C6.9914 13.298 7.00772 13.2405 7.03471 13.1879C7.14764 12.967 7.41938 12.8789 7.64038 12.9918L9.46201 13.9222C9.60054 13.993 9.75492 14.027 9.91036 14.021C10.0658 14.015 10.2171 13.9693 10.3498 13.888C10.4825 13.8068 10.5921 13.6929 10.6681 13.5572C10.7441 13.4215 10.7841 13.2686 10.7841 13.113V10.1356C10.7841 7.71368 12.7544 5.74338 15.1763 5.74338C17.5981 5.74338 19.5685 7.71368 19.5685 10.1356V13.1129C19.5685 13.2684 19.6085 13.4214 19.6845 13.5571C19.7606 13.6928 19.8702 13.8067 20.0028 13.8879C20.1355 13.9691 20.2868 14.0149 20.4422 14.0209C20.5977 14.0269 20.752 13.9929 20.8906 13.9221L22.7118 12.9918C22.933 12.8789 23.2046 12.9669 23.3175 13.1879C23.3914 13.3324 23.3664 13.4651 23.3449 13.5313C23.3234 13.5976 23.2659 13.7197 23.1214 13.7935L20.4817 15.1419C20.074 15.3501 19.8849 15.8291 20.0403 16.2596C20.6563 17.9664 21.8566 19.5831 23.4223 20.8515C23.0688 20.9731 22.6741 21.0619 22.2864 21.0512Z" fill="black"/>
                    </svg>
                </div>
                <h3 class="social-platform"><?php _e( 'Snapchat', 'wc-multivendor-marketplace' ); ?></h3>
            </div>
            <div class="social-metrics">
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $snapchat['daily_users'] ?: '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Followers', 'wc-multivendor-marketplace' ); ?></span>
                </div>
                <div class="metric">
                    <span class="metric-value"><?php echo esc_html( $snapchat['swipe_rate'] ? $snapchat['swipe_rate'] . '%' : '-' ); ?></span>
                    <span class="metric-label"><?php _e( 'Engagement', 'wc-multivendor-marketplace' ); ?></span>
                </div>
            </div>
            <div class="metric-views">
                <span class="metric-value"><?php echo esc_html( $snapchat['story_viewers'] ?: '-' ); ?></span>
                <span class="metric-label"><?php _e( 'Avg. Views / Post', 'wc-multivendor-marketplace' ); ?></span>
            </div>
        </div>

    </div>

    <!-- Demographics & Locations Row -->
    <div class="media-kit-row">

        <!-- Audience Demographics -->
        <div class="media-kit-card demographics-card">
            <h3 class="card-title"><?php _e( 'Audience Demographics', 'wc-multivendor-marketplace' ); ?></h3>

            <div class="demographic-section">
                <h4 class="section-label"><?php _e( 'Gender Split', 'wc-multivendor-marketplace' ); ?></h4>
                <div class="gender-bar">
                    <div class="gender-female" style="width: <?php echo esc_attr( $gender_female ); ?>%;"></div>
                    <div class="gender-male" style="width: <?php echo esc_attr( $gender_male ); ?>%;"></div>
                </div>
                <div class="gender-labels">
                    <span class="female-label"><?php printf( __( 'Female (%d%%)', 'wc-multivendor-marketplace' ), $gender_female ); ?></span>
                    <span class="male-label"><?php printf( __( 'Male (%d%%)', 'wc-multivendor-marketplace' ), $gender_male ); ?></span>
                </div>
            </div>

            <?php if ( ! empty($dominant_age) ) : ?>
            <div class="demographic-section">
                <h4 class="section-label"><?php _e( 'Top Age Ranges', 'wc-multivendor-marketplace' ); ?></h4>
                <div class="age-range-item">
                    <span class="age-label"><?php echo esc_html($dominant_age); ?></span>
                    <div class="age-bar-container">
                        <div class="age-bar" style="width: 100%;"></div>
                    </div>
                    <span class="age-percent"><?php _e('Primary', 'wc-multivendor-marketplace'); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Top Locations -->
        <div class="media-kit-card locations-card">
            <h3 class="card-title"><?php _e( 'Top Locations', 'wc-multivendor-marketplace' ); ?></h3>

            <?php if ( ! empty($locations) ) : ?>
                <?php foreach ( $locations as $location ) : ?>
                    <div class="location-item">
                        <span class="location-name"><?php echo esc_html( $location['city'] ); ?></span>
                        <div class="location-bar-container">
                            <div class="location-bar" style="width: <?php echo esc_attr( $location['percentage'] ); ?>%;"></div>
                        </div>
                        <span class="location-percent"><?php echo esc_html( $location['percentage'] ); ?>%</span>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p class="no-data"><?php _e('No location data available', 'wc-multivendor-marketplace'); ?></p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Achievements & Highlights -->
    <?php if ( ! empty($achievements) ) : ?>
    <div class="media-kit-card achievement-section-card achievements-section">
        <h3 class="section-title"><?php _e( 'Achievements & Highlights', 'wc-multivendor-marketplace' ); ?></h3>
        <div class="achievements-grid">
            <?php foreach ( $achievements as $achievement ) : ?>
                <?php if ( ! empty($achievement['title']) ) : ?>
                <div class="achievement-card">
                    <span class="achievement-title"><?php echo esc_html( $achievement['title'] ); ?></span>
                    <span class="achievement-year"><?php echo esc_html( $achievement['year'] ?? '' ); ?></span>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Additional Info - Separate Cards -->
    <div class="additional-info-grid">

        <div class="info-card">
            <h4 class="info-label"><?php _e( 'Available to Travel Worldwide', 'wc-multivendor-marketplace' ); ?></h4>
            <span class="info-value"><?php echo esc_html( $travel_display ); ?></span>
        </div>

        <?php if ( ! empty($languages_display) ) : ?>
        <div class="info-card">
            <h4 class="info-label"><?php _e( 'Language', 'wc-multivendor-marketplace' ); ?></h4>
            <span class="info-value"><?php echo esc_html( $languages_display ); ?></span>
        </div>
        <?php endif; ?>

        <?php if ( ! empty($collab_display) ) : ?>
        <div class="info-card info-card-wide">
            <h4 class="info-label"><?php _e( 'Collab Preferences', 'wc-multivendor-marketplace' ); ?></h4>
            <span class="info-value"><?php echo esc_html( $collab_display ); ?></span>
        </div>
        <?php endif; ?>

    </div>

</div>

<style>
/* Live Media Kit Styles - Figma Design */
.wcfm-store-media-kit-wrap {
    /* max-width: 1200px; */
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 0;
    /* font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif; */
}

/* Gradient Title Style */
.card-title,
.section-title {
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 20px 0;
}

.section-title {
    font-size: 18px;
    margin-bottom: 16px;
}

/* Live Badge */
.media-kit-live-badge {
    display: none !important;;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
    font-size: 13px;
}

.live-dot {
    width: 8px;
    height: 8px;
    background: #00c4aa;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.live-text {
    color: #00c4aa;
    font-weight: 600;
}

.sync-time {
    color: #666;
}

/* Social Stats Grid */
.social-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.social-stat-card {
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 0 6px rgba(0,0,0,0.06);
}

.stat-header {
    display: flex;
    flex-direction: column;
    align-items: start;
    gap: 5px;
    margin-bottom: 20px;
}

.social-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a1a1a;
}

.social-platform {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.social-metrics {
    display: flex;
    gap: 24px;
    /* margin-bottom: 16px; */
}

.metric {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.metric-value {
    font-size: 30px;
    font-weight: 800;
    background: linear-gradient(180deg, #57EFE4 30%, #42A5D8 90%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.metric-label {
    font-size: 12px;
    color: #000;
    margin-top: -2px;
}

.metric-views {
    display: flex;
    flex-direction: column;
    padding-top: 12px;
    /* border-top: 1px solid #e5e5e5; */
}

/* .metric-views .metric-value {
    font-size: 18px;
} */

/* Demographics & Locations Row */
.media-kit-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.media-kit-card {
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 0 6px rgba(0,0,0,0.06);
}
.achievements-section .section-title,
.media-kit-card .card-title{
    color: #10A993 !important;
    -webkit-text-fill-color: #10A993 !important;
    background: transparent !important;
    font-weight: 700;
    font-size: 18px;
}

/* Demographics */
.demographic-section {
    margin-bottom: 24px;
}

.demographic-section:last-child {
    margin-bottom: 0;
}

.section-label {
    font-size: 13px;
    font-weight: 600;
    color: #000;
    margin: 0 0 12px 0;
}

.gender-bar {
    display: flex;
    height: 20px;
    border-radius: 500px;
    overflow: hidden;
    margin-bottom: 8px;
}

.gender-female {
    background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    background: #0CCAAE !important;
}

.gender-male {
    background: #e5e5e5;
}

.gender-labels {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #1a1a1a;
}

/* Age Ranges */
.age-range-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.age-range-item:last-child {
    margin-bottom: 0;
}

.age-label {
    width: 60px;
    font-size: 13px;
    color: #1a1a1a;
}

.age-bar-container {
    flex: 1;
    height: 8px;
    background: #e5e5e5;
    border-radius: 4px;
    overflow: hidden;
}

.age-bar {
    height: 100%;
    background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    background: #0CCAAE !important;
    border-radius: 4px;
}

.age-percent {
    width: 50px;
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    text-align: right;
}

/* Locations */
.location-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.location-item:last-child {
    margin-bottom: 0;
}

.location-name {
    width: 140px;
    font-size: 13px;
    color: #1a1a1a;
    flex-shrink: 0;
}

.location-bar-container {
    flex: 1;
    height: 8px;
    background: #e5e5e5;
    border-radius: 4px;
    overflow: hidden;
}

.location-bar {
    height: 100%;
    background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    background: #0CCAAE !important;
    border-radius: 4px;
}

.location-percent {
    width: 40px;
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    text-align: right;
}

.no-data {
    color: #999;
    font-size: 13px;
    font-style: italic;
    margin: 0;
}

/* Achievements - Separate Cards with Drop Shadows */
.achievements-section {
    margin-bottom: 24px;
}


.achievements-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.achievement-card {
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.2s ease;
}

.achievement-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.achievement-title {
    font-size: 16px;
    color: #1a1a1a;
    margin-bottom: 4px;
    font-weight: 700;
    text-transform: capitalize;
}

.achievement-year {
    font-size: 40px;
    font-weight: 900;
    background: linear-gradient(180deg, #57EFE4 30%, #42A5D8 90%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    padding-top: 4px;
}

/* Additional Info - Separate Cards */
.additional-info-grid {
    display: grid;
    grid-template-columns: auto auto 1fr;
    gap: 16px;
}

.info-card {
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border-radius: 12px;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.06);
}

.info-card-wide {
    grid-column: 3;
}

.info-label {
    font-size: 14px;
    font-weight: 700;
    /* background: linear-gradient(90deg, #57EFE4 0%, #42A5D8 100%);
    -webkit-text-fill-color: transparent;
    background-clip: text;
    -webkit-background-clip: text; */
    color: #10A993 !important;
    margin: 0 0 8px 0;
}

.info-value {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
}





/* ============ Dark Theme ============== */

.dark__theme .social-stat-card,
.dark__theme .media-kit-card,
.dark__theme .info-card,
.dark__theme .achievement-card {
    background: #202020 !important;
    border: none !important;
    box-shadow: 0px 0px 6px rgba(255, 255, 255, 0.1) !important;
}

.dark__theme .metric-label,
.dark__theme .age-label,
.dark__theme .location-name,
.dark__theme .achievement-title,
.dark__theme .info-value,
.dark__theme .age-percent{
    color: #fff !important;
}

.dark__theme .social-icon svg path{
    fill: #fff !important;
}
.dark__theme body:not(.wcfm-dashboard-page) h4.info-label,
.dark__theme .info-label{
    color: #00c4aa !important;
}







/* Responsive */
@media (max-width: 1024px) {
    .social-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .achievements-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .additional-info-grid {
        grid-template-columns: 1fr 1fr;
    }

    .info-card-wide {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .wcfm-store-media-kit-wrap {
        padding: 16px;
    }

    .social-stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .media-kit-row {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .achievements-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .achievement-card {
        padding: 16px;
    }

    .achievement-year {
        font-size: 28px;
    }

    .additional-info-grid {
        grid-template-columns: 1fr;
    }

    .info-card-wide {
        grid-column: 1;
    }

    .location-name {
        width: 100px;
        font-size: 12px;
    }

    .location-percent {
        width: 35px;
        font-size: 12px;
    }

    .social-metrics {
        gap: 16px;
    }

    .metric-value {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .achievements-grid {
        grid-template-columns: 1fr;
    }

    .achievement-card {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .achievement-title {
        margin-bottom: 0;
    }
}
</style>
