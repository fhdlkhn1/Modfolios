<?php
/**
 * WCFM Marketplace Settings View - Custom Override for Modfolios
 * Matches Figma design with Personal Info, Live Media Kit, Demographics, etc.
 *
 * NOTE: This custom view is only for VENDORS. Admins will see the original WCFM settings.
 */

global $WCFM, $WCFMmp;

// Check if current user is admin - if so, load the original WCFM settings view
if (current_user_can('administrator') || current_user_can('shop_manager')) {
    // Load the original WCFMmp settings view for admins
    $original_file = WCFMmp_PLUGIN_DIR . 'views/settings/wcfmmp-view-settings.php';
    if (file_exists($original_file)) {
        include($original_file);
        return;
    }
}

$wcfm_is_allow_manage_settings = apply_filters('wcfm_is_allow_manage_settings', true);
if (!$wcfm_is_allow_manage_settings) {
    wcfm_restriction_message_show("Settings");
    return;
}

$user_id = apply_filters('wcfm_current_vendor_id', get_current_user_id());
$vendor_data = get_user_meta($user_id, 'wcfmmp_profile_settings', true);
if (!is_array($vendor_data)) $vendor_data = array();

$the_user = get_user_by('id', $user_id);
$user_email = $the_user->user_email;

// Get avatar and banner
$gravatar = isset($vendor_data['gravatar']) ? absint($vendor_data['gravatar']) : 0;
$banner = isset($vendor_data['banner']) ? absint($vendor_data['banner']) : 0;
$gravatar_url = $gravatar ? wp_get_attachment_url($gravatar) : get_avatar_url($user_id, array('size' => 150));
$banner_url = $banner ? wp_get_attachment_url($banner) : '';

// Store basics
$store_name = wcfm_get_vendor_store_name($user_id);
$store_name = empty($store_name) ? $the_user->display_name : $store_name;

// Tagline
$tagline = isset($vendor_data['tagline']) ? $vendor_data['tagline'] : '';
if (empty($tagline)) {
    $tagline = get_user_meta($user_id, 'tagline', true);
}
if (empty($tagline)) {
    $tagline = 'Editorial & Fashion Model';
}

// Get membership info
$membership_type = 'Basic Plan';

// Get last login
$last_login = get_user_meta($user_id, 'last_login', true);
$last_login_display = $last_login ? date_i18n('g:i a (F j, Y)', strtotime($last_login)) : date_i18n('g:i a (F j, Y)');

// Get store URL
$store_url = wcfmmp_get_store_url($user_id);

// Profile completion percentage
$profile_completion = modfolio_calculate_profile_completion($user_id, $vendor_data);

// ===== Personal Information =====
$about = isset($vendor_data['about']) ? $vendor_data['about'] : '';
$facebook_url = isset($vendor_data['social']['fb']) ? $vendor_data['social']['fb'] : '';
$linkedin_url = isset($vendor_data['social']['linkedin']) ? $vendor_data['social']['linkedin'] : '';
$twitter_url = isset($vendor_data['social']['twitter']) ? $vendor_data['social']['twitter'] : '';
$instagram_url = isset($vendor_data['social']['instagram']) ? $vendor_data['social']['instagram'] : '';

// ===== Live Media Kit =====
// Instagram
$instagram_followers = isset($vendor_data['media_kit']['instagram']['followers']) ? $vendor_data['media_kit']['instagram']['followers'] : '';
$instagram_engagement = isset($vendor_data['media_kit']['instagram']['engagement_rate']) ? $vendor_data['media_kit']['instagram']['engagement_rate'] : '';
$instagram_avg_likes = isset($vendor_data['media_kit']['instagram']['avg_likes']) ? $vendor_data['media_kit']['instagram']['avg_likes'] : '';

// TikTok
$tiktok_followers = isset($vendor_data['media_kit']['tiktok']['followers']) ? $vendor_data['media_kit']['tiktok']['followers'] : '';
$tiktok_avg_views = isset($vendor_data['media_kit']['tiktok']['avg_views']) ? $vendor_data['media_kit']['tiktok']['avg_views'] : '';
$tiktok_viral_count = isset($vendor_data['media_kit']['tiktok']['viral_count']) ? $vendor_data['media_kit']['tiktok']['viral_count'] : '';

// YouTube
$youtube_subscribers = isset($vendor_data['media_kit']['youtube']['subscribers']) ? $vendor_data['media_kit']['youtube']['subscribers'] : '';
$youtube_watch_time = isset($vendor_data['media_kit']['youtube']['watch_time']) ? $vendor_data['media_kit']['youtube']['watch_time'] : '';
$youtube_avg_views = isset($vendor_data['media_kit']['youtube']['avg_views']) ? $vendor_data['media_kit']['youtube']['avg_views'] : '';

// Snapchat
$snapchat_daily_users = isset($vendor_data['media_kit']['snapchat']['daily_users']) ? $vendor_data['media_kit']['snapchat']['daily_users'] : '';
$snapchat_story_viewers = isset($vendor_data['media_kit']['snapchat']['story_viewers']) ? $vendor_data['media_kit']['snapchat']['story_viewers'] : '';
$snapchat_swipe_rate = isset($vendor_data['media_kit']['snapchat']['swipe_rate']) ? $vendor_data['media_kit']['snapchat']['swipe_rate'] : '';

// ===== Audience Demographics =====
$gender_female = isset($vendor_data['demographics']['gender_female']) ? intval($vendor_data['demographics']['gender_female']) : 70;
$gender_male = 100 - $gender_female;
$dominant_age = isset($vendor_data['demographics']['dominant_age']) ? $vendor_data['demographics']['dominant_age'] : '18-24';
$top_locations = isset($vendor_data['demographics']['top_locations']) ? $vendor_data['demographics']['top_locations'] : '';

// ===== Achievements =====
$achievements = isset($vendor_data['achievements']) ? $vendor_data['achievements'] : array();

// ===== Theme Settings =====
$background_theme = isset($vendor_data['theme']['background']) ? $vendor_data['theme']['background'] : '#ffffff';
$accent_color = isset($vendor_data['theme']['accent']) ? $vendor_data['theme']['accent'] : '#00c4aa';

// ===== Language =====
$languages = isset($vendor_data['languages']) ? $vendor_data['languages'] : array();
if (empty($languages)) $languages = array('Native');

// ===== Collab Preferences =====
$collab_ugc = isset($vendor_data['collab']['ugc_video']) ? $vendor_data['collab']['ugc_video'] : 'no';
$collab_photography = isset($vendor_data['collab']['photography']) ? $vendor_data['collab']['photography'] : 'no';
$collab_modeling = isset($vendor_data['collab']['modeling']) ? $vendor_data['collab']['modeling'] : 'no';
$collab_sponsored = isset($vendor_data['collab']['sponsored_posts']) ? $vendor_data['collab']['sponsored_posts'] : 'no';
$collab_tfp = isset($vendor_data['collab']['tfp']) ? $vendor_data['collab']['tfp'] : 'no';

// ===== Travel Status =====
$travel_available = isset($vendor_data['travel']['available']) ? $vendor_data['travel']['available'] : 'yes';
$travel_base_city = isset($vendor_data['travel']['base_city']) ? $vendor_data['travel']['base_city'] : '';

// Age range options
$age_ranges = array(
    '13-17' => '13-17',
    '18-24' => '18-24',
    '25-34' => '25-34',
    '35-44' => '35-44',
    '45-54' => '45-54',
    '55-64' => '55-64',
    '65+' => '65+'
);

// Enqueue WordPress media uploader
wp_enqueue_media();
?>

<div class="collapse wcfm-collapse modfolio-settings-page" id="wcfm_settings">

    <!-- Store Header with Banner -->
    <div class="modfolio-settings-header">
        <div class="header-banner" style="background-image: url('<?php echo esc_url($banner_url); ?>');">
            <div class="header-overlay"></div>

            <!-- Banner Edit Button (top right) -->
            <span type="button" class="banner-edit-btn" id="edit-banner-btn" title="<?php esc_attr_e('Edit Banner', 'wc-frontend-manager'); ?>">
                <i class="wcfmfa fa-pencil-alt"></i>
            </span>

            <div class="header-content">
                <div class="header-left">
                    <div class="header-avatar">
                        <img src="<?php echo esc_url($gravatar_url); ?>" alt="<?php echo esc_attr($store_name); ?>" id="avatar-preview">
                        <!-- Avatar Edit Button (bottom of avatar) -->
                        <span type="button" class="avatar-edit-btn" id="edit-avatar-btn" title="<?php esc_attr_e('Edit Avatar', 'wc-frontend-manager'); ?>">
                            <i class="wcfmfa fa-pencil-alt"></i>
                        </span>
                    </div>
                    <div class="header-info">
                        <h1 class="header-name"><?php echo esc_html($store_name); ?></h1>
                        <div class="header-tagline-wrap">
                            <p class="header-tagline" id="tagline-display"><?php echo esc_html($tagline); ?></p>
                            <span type="button" class="tagline-edit-btn" id="edit-tagline-btn" title="<?php esc_attr_e('Edit Tagline', 'wc-frontend-manager'); ?>">
                                <i class="wcfmfa fa-pencil-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <a href="#" class="membership-badge">
                        <span><?php _e('Membership:', 'wc-frontend-manager'); ?></span>
                        <strong><?php echo esc_html($membership_type); ?></strong>
                    </a>
                    <div class="last-login">
                        <span class="login-label"><?php _e('Last Login:', 'wc-frontend-manager'); ?></span>
                        <span class="login-time"><?php echo esc_html($last_login_display); ?></span>
                    </div>
                </div>
            </div>
            <div class="header-bottom">
                <div class="public-profile">
                    <span><?php _e('Public Profile', 'wc-frontend-manager'); ?></span>
                    <a href="<?php echo esc_url($store_url); ?>" target="_blank"><?php echo esc_url($store_url); ?></a>
                    <span type="button" class="copy-url-btn" data-url="<?php echo esc_url($store_url); ?>">
                        <i class="wcfmfa fa-copy"></i>
                    </span>
                </div>
            </div>
        </div>

        <!-- Profile Completion Bar -->
        <div class="profile-completion-bar">
            <div class="completion-progress" style="width: <?php echo intval($profile_completion); ?>%;"></div>
            <span class="completion-text"><?php echo intval($profile_completion); ?>% Complete!</span>
        </div>
    </div>

    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <?php do_action('before_wcfm_marketplace_settings', $user_id); ?>

        <form id="wcfm_settings_form" class="wcfm modfolio-settings-form">
            <?php wp_nonce_field('modfolio_save_settings', 'modfolio_settings_nonce'); ?>

            <!-- Hidden fields for avatar and banner -->
            <input type="hidden" name="gravatar" id="gravatar-input" value="<?php echo esc_attr($gravatar); ?>">
            <input type="hidden" name="banner" id="banner-input" value="<?php echo esc_attr($banner); ?>">
            <input type="hidden" name="tagline" id="tagline-input" value="<?php echo esc_attr($tagline); ?>">

            <div class="modfolio-settings-layout">
                <!-- Left Column -->
                <div class="settings-column settings-left">

                    <!-- Personal Information -->
                    <div class="settings-section" id="personal-info">
                        <div class="section-header">
                            <h2><?php _e('Personal Information', 'wc-frontend-manager'); ?></h2>
                        </div>
                        <div class="section-content">
                            <div class="field-group field-with-icon figma-input-field">
                                <label class="field-label"><?php _e('About', 'wc-frontend-manager'); ?></label>
                                <span class="field-separator"></span>
                                <div class="input-wrapper">
                                    <textarea name="about" rows="3" placeholder="<?php esc_attr_e('Enter Text Here', 'wc-frontend-manager'); ?>"><?php echo esc_textarea($about); ?></textarea>
                                </div>
                            </div>

                            <div class="social-links-grid">
                                <div class="figma-input-field">
                                    <span class="field-label"><?php _e('Facebook', 'wc-frontend-manager'); ?></span>
                                    <span class="field-separator"></span>
                                    <input type="url" name="social[fb]" value="<?php echo esc_attr($facebook_url); ?>" placeholder="www.facebook.com/username">
                                    <span type="button" class="copy-btn"><i class="wcfmfa fa-copy"></i></span>
                                </div>
                                <div class="figma-input-field">
                                    <span class="field-label"><?php _e('Linkedin', 'wc-frontend-manager'); ?></span>
                                    <span class="field-separator"></span>
                                    <input type="url" name="social[linkedin]" value="<?php echo esc_attr($linkedin_url); ?>" placeholder="www.linkedin.com/in/username">
                                    <span type="button" class="copy-btn"><i class="wcfmfa fa-copy"></i></span>
                                </div>
                                <div class="figma-input-field">
                                    <span class="field-label"><?php _e('Twitter', 'wc-frontend-manager'); ?></span>
                                    <span class="field-separator"></span>
                                    <input type="url" name="social[twitter]" value="<?php echo esc_attr($twitter_url); ?>" placeholder="www.twitter.com/username">
                                    <span type="button" class="copy-btn"><i class="wcfmfa fa-copy"></i></span>
                                </div>
                                <div class="figma-input-field">
                                    <span class="field-label"><?php _e('Instagram', 'wc-frontend-manager'); ?></span>
                                    <span class="field-separator"></span>
                                    <input type="url" name="social[instagram]" value="<?php echo esc_attr($instagram_url); ?>" placeholder="www.instagram.com/username">
                                    <span type="button" class="copy-btn"><i class="wcfmfa fa-copy"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Media Kit -->
                    <div class="settings-section" id="media-kit">
                        <div class="section-header">
                            <div class="section-title-with-icon">
                                <!-- <span class="section-icon media-kit-icon">
                                    <i class="wcfmfa fa-chart-line"></i>
                                </span> -->
                                <div style="display: flex;flex-direction: column;">
                                    <h2><?php _e('Live Media Kit', 'wc-frontend-manager'); ?></h2>
                                    <p class="section-subtitle"><?php _e('Manually Update your stats to keep your kit verified', 'wc-frontend-manager'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="section-content">

                            <!-- Instagram Statistics -->
                            <div class="stats-group">
                                <div class="stats-header">
                                    <span class="stats-icon instagram-icon">
                                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <defs>
                                                <linearGradient id="instagramGradient" x1="0%" y1="100%" x2="100%" y2="0%">
                                                <stop offset="0%" stop-color="#F58529"/>
                                                <stop offset="30%" stop-color="#DD2A7B"/>
                                                <stop offset="60%" stop-color="#8134AF"/>
                                                <stop offset="100%" stop-color="#515BD4"/>
                                                </linearGradient>
                                            </defs>
                                            <path d="M25.934 7.64555C25.873 6.26376 25.6496 5.31389 25.3294 4.49071C24.9992 3.61698 24.4912 2.83475 23.8257 2.18444C23.1754 1.52403 22.3879 1.01084 21.5243 0.685813C20.6965 0.365767 19.7515 0.142243 18.3697 0.0813832C16.9776 0.0152403 16.5357 0 13.0051 0C9.47439 0 9.03247 0.0152403 7.64555 0.0762015C6.26381 0.137163 5.31389 0.360789 4.49096 0.680632C3.61698 1.01084 2.83475 1.51885 2.18444 2.18444C1.52403 2.8347 1.01109 3.62211 0.685813 4.48573C0.365767 5.31389 0.142294 6.25868 0.0813832 7.64037C0.0152911 9.03247 0 9.47438 0 13.0051C0 16.5357 0.0152911 16.9777 0.0762015 18.3646C0.137163 19.7463 0.360789 20.6962 0.680886 21.5194C1.01109 22.3931 1.52403 23.1754 2.18444 23.8257C2.83475 24.4861 3.62216 24.9993 4.48578 25.3243C5.31383 25.6443 6.25863 25.8679 7.64062 25.9287C9.02729 25.9899 9.46946 26.0049 13.0001 26.0049C16.5308 26.0049 16.9727 25.9899 18.3596 25.9287C19.7414 25.8678 20.6913 25.6444 21.5142 25.3243C22.3785 24.9901 23.1635 24.479 23.8187 23.8238C24.474 23.1686 24.9851 22.3837 25.3194 21.5194C25.6392 20.6913 25.8629 19.7463 25.9238 18.3646C25.9848 16.9777 26 16.5357 26 13.0051C26 9.47438 25.9948 9.03242 25.934 7.64555ZM23.5921 18.263C23.5361 19.533 23.3228 20.2188 23.145 20.676C22.708 21.8089 21.8089 22.708 20.676 23.145C20.2188 23.3228 19.5281 23.5361 18.263 23.5919C16.8913 23.653 16.48 23.6681 13.0102 23.6681C9.54048 23.6681 9.12396 23.653 7.75726 23.5919C6.48724 23.5361 5.80142 23.3228 5.34421 23.145C4.78048 22.9366 4.26733 22.6064 3.85077 22.1746C3.41896 21.753 3.08875 21.245 2.88037 20.6812C2.70256 20.224 2.48925 19.533 2.43352 18.2681C2.37236 16.8965 2.35732 16.4849 2.35732 13.0152C2.35732 9.5454 2.37236 9.12889 2.43352 7.76244C2.48925 6.49242 2.70256 5.8066 2.88037 5.3494C3.08875 4.7854 3.41896 4.27236 3.85595 3.85569C4.27739 3.42389 4.7854 3.09368 5.3494 2.88555C5.8066 2.70774 6.4976 2.49438 7.76244 2.43845C9.13407 2.37749 9.54566 2.36225 13.0152 2.36225C16.4901 2.36225 16.9014 2.37749 18.2681 2.43845C19.5382 2.49443 20.224 2.70769 20.6812 2.8855C21.2449 3.09368 21.7581 3.42389 22.1746 3.85569C22.6064 4.27739 22.9366 4.7854 23.145 5.3494C23.3228 5.8066 23.5362 6.49734 23.5921 7.76244C23.653 9.13407 23.6683 9.5454 23.6683 13.0152C23.6683 16.4849 23.6531 16.8913 23.5921 18.263Z" fill="black"/>
                                            <path d="M13.0046 6.32561C9.3165 6.32561 6.32422 9.31769 6.32422 13.0059C6.32422 16.6942 9.3165 19.6863 13.0046 19.6863C16.6927 19.6863 19.6849 16.6942 19.6849 13.0059C19.6849 9.31769 16.6927 6.32561 13.0046 6.32561ZM13.0046 17.3393C10.6119 17.3393 8.67112 15.3988 8.67112 13.0059C8.67112 10.6131 10.6119 8.67261 13.0045 8.67261C15.3973 8.67261 17.3379 10.6131 17.3379 13.0059C17.3379 15.3988 15.3973 17.3393 13.0046 17.3393ZM21.5087 6.06144C21.5087 6.92272 20.8104 7.62103 19.9489 7.62103C19.0877 7.62103 18.3894 6.92272 18.3894 6.06144C18.3894 5.20006 19.0877 4.50195 19.949 4.50195C20.8104 4.50195 21.5087 5.20001 21.5087 6.06144Z" fill="black"/>
                                        </svg>
                                        <!-- <i class="wcfmfa fa-instagram"></i> -->

                                    </span>
                                    <h3><?php _e('Instagram Statistics', 'wc-frontend-manager'); ?></h3>
                                </div>
                                <div class="stats-fields">
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Followers', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[instagram][followers]" value="<?php echo esc_attr($instagram_followers); ?>" placeholder="E.g.125000">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Engagement Rate %', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[instagram][engagement_rate]" value="<?php echo esc_attr($instagram_engagement); ?>" placeholder="E.g.4.5">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Avg. Like / Post', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[instagram][avg_likes]" value="<?php echo esc_attr($instagram_avg_likes); ?>" placeholder="E.g.3500">
                                    </div>
                                </div>
                            </div>

                            <!-- TikTok Statistics -->
                            <div class="stats-group">
                                <div class="stats-header">
                                    <span class="stats-icon tiktok-icon">
                                        <svg width="26" height="30" viewBox="0 0 26 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <defs>
                                                <linearGradient id="tiktokGradient" x1="0%" y1="100%" x2="100%" y2="0%">
                                                    <stop offset="0%" stop-color="#25F4EE"/>
                                                    <stop offset="100%" stop-color="#FE2C55"/>
                                                </linearGradient>
                                            </defs>
                                            <path d="M9.42459 29.0817C7.30765 29.0817 5.31113 28.4063 3.65086 27.1283C3.33971 26.8887 3.0436 26.6301 2.76421 26.3541C0.826195 24.4388 -0.149063 21.8879 0.0184974 19.1712C0.145162 17.1178 0.972171 15.1384 2.34844 13.5974C4.17115 11.5565 6.68399 10.4325 9.42516 10.4325C9.89603 10.4325 10.3715 10.4677 10.8395 10.537L11.5665 10.6449V17.0968L10.447 16.7271C10.1218 16.6193 9.78139 16.5645 9.43879 16.5646C8.57202 16.5646 7.76035 16.9043 7.15316 17.5206C6.5488 18.134 6.22334 18.9451 6.23584 19.8056C6.25231 20.9149 6.83678 21.9203 7.79898 22.4963C8.24202 22.7609 8.7464 22.916 9.25874 22.9444C9.66315 22.9666 10.0625 22.9137 10.4464 22.7871C11.7591 22.3537 12.6412 21.1359 12.6412 19.7573L12.6469 13.0663V0H18.8131L18.8205 0.84405C18.8239 1.22631 18.8631 1.60858 18.9369 1.97892C19.2266 3.43527 20.0434 4.73315 21.2367 5.63287C22.2961 6.43204 23.5604 6.85407 24.8918 6.85407C24.9231 6.85407 24.9231 6.85407 25.2076 6.87395L26 6.92961V13.0072L25.716 13.0067H24.8589C23.2168 13.0067 21.6781 12.6937 20.153 12.0501C19.6986 11.8582 19.2561 11.6389 18.829 11.3941L18.8483 19.7897C18.8381 22.273 17.8566 24.6024 16.085 26.3535C14.6474 27.7741 12.8332 28.6818 10.8383 28.9771C10.3703 29.0467 9.89777 29.0816 9.42459 29.0817ZM9.42459 12.1365C7.17588 12.1365 5.11403 13.0583 3.61906 14.7322C2.49669 15.9892 1.8219 17.6029 1.71852 19.2757C1.5822 21.4937 2.37854 23.5772 3.96156 25.1414C4.19046 25.3675 4.43527 25.5816 4.68974 25.7776C6.04953 26.8244 7.68708 27.3777 9.42459 27.3777C9.81424 27.3777 10.2056 27.3487 10.589 27.2919C12.2231 27.0499 13.7096 26.3064 14.8876 25.142C16.3349 23.7118 17.1363 21.8107 17.1449 19.7886L17.1182 8.06561L18.4944 9.12721C19.2071 9.67641 19.9875 10.1315 20.8164 10.4813C21.9507 10.9601 23.0941 11.226 24.2954 11.289V8.53706C22.8107 8.42573 21.4083 7.89805 20.2104 6.99436C18.6824 5.84246 17.6368 4.17935 17.2653 2.31233C17.2255 2.11183 17.1931 1.90905 17.1693 1.70457H14.3503V13.0669L14.3446 19.7579C14.3446 21.8726 12.9928 23.7402 10.9803 24.4047C10.3936 24.5984 9.783 24.6791 9.16502 24.6456C8.37777 24.6024 7.60301 24.3644 6.92369 23.9583C5.45086 23.0773 4.55626 21.5341 4.53127 19.8306C4.51195 18.5106 5.01123 17.2655 5.9382 16.3244C6.86802 15.3803 8.11081 14.8606 9.43766 14.8606C9.57966 14.8606 9.72052 14.8669 9.86139 14.8788V12.1484C9.71598 12.1405 9.57 12.1365 9.42459 12.1365Z" fill="black"/>
                                        </svg>
                                        <!-- <i class="wcfmfa fa-music"></i> -->
                                    </span>
                                    <h3><?php _e('Tiktok Statistics', 'wc-frontend-manager'); ?></h3>
                                </div>
                                <div class="stats-fields">
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Followers', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[tiktok][followers]" value="<?php echo esc_attr($tiktok_followers); ?>" placeholder="E.g.500000">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Avg Views/Videos', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[tiktok][avg_views]" value="<?php echo esc_attr($tiktok_avg_views); ?>" placeholder="E.g.25000">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Viral Video Count', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[tiktok][viral_count]" value="<?php echo esc_attr($tiktok_viral_count); ?>" placeholder="E.g.12">
                                    </div>
                                </div>
                            </div>

                            <!-- YouTube Statistics -->
                            <div class="stats-group">
                                <div class="stats-header">
                                    <span class="stats-icon youtube-icon">
                                        <svg width="28" height="22" viewBox="0 0 28 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18.3134 9.35203L12.1271 5.96719C11.7714 5.77255 11.3509 5.77982 11.0023 5.98639C10.6534 6.19318 10.4453 6.55851 10.4453 6.964V13.6756C10.4453 14.0792 10.6522 14.4439 10.9987 14.6509C11.1796 14.759 11.3802 14.8133 11.5812 14.8133C11.7694 14.813 11.9544 14.7659 12.1198 14.6763L18.3064 11.3498C18.4866 11.2529 18.6373 11.1092 18.7425 10.9338C18.8478 10.7583 18.9038 10.5577 18.9045 10.3532C18.9053 10.1485 18.8508 9.94744 18.7468 9.77121C18.6427 9.59498 18.493 9.45015 18.3134 9.35203ZM12.0863 12.8313V7.81515L16.7099 10.345L12.0863 12.8313Z" fill="black"/>
                                            <path d="M27.7958 5.0057L27.7946 4.99291C27.7708 4.76752 27.5348 2.76277 26.5602 1.74306C25.4337 0.543683 24.1565 0.397986 23.5423 0.328146C23.4959 0.323024 23.4495 0.317464 23.4032 0.311465L23.3543 0.306379C19.6524 0.0371899 14.0618 0.000437528 14.0058 0.000218764L14.0009 0L13.996 0.000218764C13.94 0.000437528 8.34935 0.0371899 4.61412 0.306379L4.56478 0.311465C4.52502 0.316825 4.48122 0.321747 4.43358 0.327326C3.82646 0.39733 2.56298 0.5433 1.43329 1.78599C0.505017 2.79482 0.236703 4.75664 0.209138 4.9771L0.205966 5.0057C0.197599 5.0995 0 7.33226 0 9.57372V11.669C0 13.9105 0.197599 16.1433 0.205966 16.2373L0.207443 16.2513C0.231179 16.4731 0.467006 18.4412 1.43712 19.4612C2.49637 20.6204 3.83564 20.7738 4.55603 20.8563C4.6699 20.8693 4.76796 20.8804 4.83479 20.8922L4.89955 20.9012C7.03698 21.1045 13.7385 21.2047 14.0227 21.2088L14.0312 21.209L14.0398 21.2088C14.0957 21.2086 19.6862 21.1718 23.3881 20.9026L23.437 20.8975C23.4838 20.8913 23.5364 20.8858 23.594 20.8798C24.1978 20.8157 25.4544 20.6826 26.5685 19.4567C27.4968 18.4478 27.7653 16.4859 27.7927 16.2657L27.7958 16.237C27.8042 16.143 28.002 13.9105 28.002 11.669V9.57372C28.0018 7.3322 27.8042 5.09972 27.7958 5.0057ZM26.3608 11.669C26.3608 13.7437 26.1797 15.8802 26.1626 16.0764C26.093 16.6167 25.8098 17.8579 25.3576 18.3495C24.6603 19.1166 23.944 19.1927 23.421 19.2481C23.3627 19.254 23.3044 19.2606 23.2462 19.2677C19.6657 19.5266 14.2861 19.5663 14.0383 19.5678C13.7604 19.5638 7.15681 19.4627 5.08457 19.2703C4.97836 19.2529 4.86362 19.2397 4.74275 19.226C4.12939 19.1557 3.28977 19.0596 2.6442 18.3495L2.629 18.3332C2.18463 17.8703 1.9097 16.7096 1.83981 16.0828C1.82679 15.9346 1.64095 13.7727 1.64095 11.669V9.57372C1.64095 7.50147 1.8217 5.36721 1.8392 5.16704C1.92233 4.5306 2.21072 3.36459 2.6442 2.89326C3.36284 2.10281 4.12064 2.01525 4.62183 1.95734C4.66968 1.95176 4.71431 1.94667 4.75555 1.94131C8.38823 1.68109 13.8065 1.64243 14.0009 1.64095C14.1953 1.64221 19.6116 1.68109 23.2121 1.94131C23.2563 1.94689 23.3045 1.95241 23.3564 1.95843C23.872 2.01717 24.6511 2.10604 25.3662 2.86871L25.3727 2.87576C25.8172 3.33872 26.0921 4.51972 26.1619 5.15911C26.1743 5.29907 26.3608 7.46554 26.3608 9.57372V11.669Z" fill="black"/>
                                        </svg>

                                        <!-- <i class="wcfmfa fa-youtube"></i> -->
                                    </span>
                                    <h3><?php _e('Youtube', 'wc-frontend-manager'); ?></h3>
                                </div>
                                <div class="stats-fields">
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Verified Subscriber', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[youtube][subscribers]" value="<?php echo esc_attr($youtube_subscribers); ?>" placeholder="E.g.5m">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Avg Watch Time', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[youtube][watch_time]" value="<?php echo esc_attr($youtube_watch_time); ?>" placeholder="E.g.3.00hrs">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Avg View per Video', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[youtube][avg_views]" value="<?php echo esc_attr($youtube_avg_views); ?>" placeholder="E.g.92k">
                                    </div>
                                </div>
                            </div>

                            <!-- Snapchat Statistics -->
                            <div class="stats-group">
                                <div class="stats-header">
                                    <span class="stats-icon snapchat-icon">
                                        <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M24.5967 0H5.75494C2.58166 0 0 2.58166 0 5.75494V24.5965C0 27.7699 2.58166 30.3515 5.75494 30.3515H24.5966C27.7699 30.3515 30.3515 27.7699 30.3516 24.5966V5.75488C30.3516 2.5816 27.7699 0 24.5967 0ZM28.5342 24.5967C28.5342 26.7678 26.7678 28.5341 24.5967 28.5341H5.75494C3.58379 28.5341 1.81742 26.7678 1.81742 24.5967V5.75494C1.81742 3.58379 3.58379 1.81742 5.75494 1.81742H24.5966C26.7677 1.81742 28.5341 3.58379 28.5342 5.75494V24.5967Z" fill="black"/>
                                            <path d="M25.655 20.2109C24.094 19.2492 22.7966 17.8531 22.0635 16.3749L23.9481 15.4121C24.4875 15.1366 24.8874 14.6675 25.074 14.0913C25.2606 13.515 25.2116 12.9007 24.9361 12.3612C24.3673 11.2477 22.9987 10.8047 21.8852 11.3732L21.386 11.6282V10.1354C21.386 6.71137 18.6004 3.92578 15.1764 3.92578C11.7524 3.92578 8.96678 6.71137 8.96678 10.1354V11.6284L8.46722 11.3732C7.35382 10.8046 5.98515 11.2476 5.41636 12.3612C5.14082 12.9007 5.09185 13.515 5.27847 14.0913C5.46508 14.6676 5.86487 15.1367 6.40421 15.4121L8.28921 16.3749C7.5562 17.8531 6.25873 19.2492 4.69782 20.2108C4.56981 20.2897 4.46336 20.399 4.388 20.5291C4.31265 20.6592 4.27076 20.8059 4.26607 20.9562C4.26138 21.1064 4.29405 21.2555 4.36114 21.39C4.42824 21.5246 4.52766 21.6403 4.65051 21.727C4.77915 21.8177 5.68092 22.4337 6.84893 22.7217C6.64316 23.4922 6.56954 24.3978 7.07437 24.9802C7.1889 25.1123 7.33912 25.2086 7.50701 25.2575C7.6749 25.3064 7.85333 25.3058 8.0209 25.2558C8.54334 25.1 10.1195 24.7322 11.1261 24.8999C11.6054 24.9798 12.0789 25.2501 12.5804 25.5364C13.3069 25.9511 14.1302 26.4212 15.173 26.4253H15.1765L15.18 26.4253C16.2228 26.4213 17.0461 25.9512 17.7726 25.5365C18.274 25.2502 18.7476 24.9798 19.2269 24.8999C20.2278 24.7332 21.8082 25.1005 22.332 25.2559C22.4996 25.3059 22.678 25.3065 22.8459 25.2576C23.0138 25.2087 23.164 25.1124 23.2786 24.9802C23.7833 24.3979 23.7097 23.4924 23.504 22.7217C24.6721 22.4337 25.5738 21.8178 25.7024 21.7271C25.8252 21.6404 25.9246 21.5246 25.9917 21.39C26.0587 21.2555 26.0914 21.1065 26.0867 20.9562C26.082 20.806 26.0401 20.6592 25.9647 20.5292C25.8894 20.3991 25.783 20.2897 25.655 20.2109ZM22.2864 21.0512C21.9731 21.0451 21.677 21.1968 21.5041 21.4586C21.419 21.5874 21.3681 21.7356 21.3562 21.8895C21.3443 22.0433 21.3717 22.1976 21.4359 22.3379C21.5661 22.6222 21.6815 22.9581 21.7572 23.2498C20.9557 23.0922 19.8573 22.9524 18.9279 23.1072C18.1283 23.2404 17.4605 23.6217 16.8713 23.9581C16.2639 24.3049 15.7392 24.6043 15.1763 24.6078C14.6134 24.6043 14.0887 24.3048 13.4813 23.9581C12.8922 23.6217 12.2243 23.2404 11.4247 23.1072C11.1395 23.0596 10.8383 23.0399 10.5346 23.0399C9.84881 23.0399 9.15043 23.1408 8.59474 23.25C8.67002 22.9607 8.78503 22.6271 8.91663 22.3379C8.98085 22.1976 9.00829 22.0433 8.99636 21.8895C8.98443 21.7356 8.93353 21.5874 8.84846 21.4586C8.67542 21.1968 8.37967 21.0451 8.06619 21.0512C7.67826 21.0622 7.2831 20.9735 6.92984 20.8518C8.49562 19.5835 9.69617 17.9667 10.3122 16.2598C10.3873 16.0517 10.3839 15.8233 10.3027 15.6175C10.2214 15.4117 10.0678 15.2427 9.87081 15.142L7.23075 13.7935C7.17806 13.7667 7.13119 13.7298 7.09285 13.6848C7.0545 13.6398 7.02544 13.5876 7.00732 13.5313C6.98901 13.4751 6.982 13.4158 6.9867 13.3569C6.9914 13.298 7.00772 13.2405 7.03471 13.1879C7.14764 12.967 7.41938 12.8789 7.64038 12.9918L9.46201 13.9222C9.60054 13.993 9.75492 14.027 9.91036 14.021C10.0658 14.015 10.2171 13.9693 10.3498 13.888C10.4825 13.8068 10.5921 13.6929 10.6681 13.5572C10.7441 13.4215 10.7841 13.2686 10.7841 13.113V10.1356C10.7841 7.71368 12.7544 5.74338 15.1763 5.74338C17.5981 5.74338 19.5685 7.71368 19.5685 10.1356V13.1129C19.5685 13.2684 19.6085 13.4214 19.6845 13.5571C19.7606 13.6928 19.8702 13.8067 20.0028 13.8879C20.1355 13.9691 20.2868 14.0149 20.4422 14.0209C20.5977 14.0269 20.752 13.9929 20.8906 13.9221L22.7118 12.9918C22.933 12.8789 23.2046 12.9669 23.3175 13.1879C23.3914 13.3324 23.3664 13.4651 23.3449 13.5313C23.3234 13.5976 23.2659 13.7197 23.1214 13.7935L20.4817 15.1419C20.074 15.3501 19.8849 15.8291 20.0403 16.2596C20.6563 17.9664 21.8566 19.5831 23.4223 20.8515C23.0688 20.9731 22.6741 21.0619 22.2864 21.0512Z" fill="black"/>
                                        </svg>
                                        <!-- <i class="wcfmfa fa-snapchat"></i> -->
                                    </span>
                                    <h3><?php _e('Snapchat', 'wc-frontend-manager'); ?></h3>
                                </div>
                                <div class="stats-fields">
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Daily Active Users,', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[snapchat][daily_users]" value="<?php echo esc_attr($snapchat_daily_users); ?>" placeholder="E.g.5,000">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Avg Story Viewers', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[snapchat][story_viewers]" value="<?php echo esc_attr($snapchat_story_viewers); ?>" placeholder="E.g.2,000">
                                    </div>
                                    <div class="figma-input-field compact">
                                        <span class="field-label"><?php _e('Swipe-Up Rate', 'wc-frontend-manager'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" name="media_kit[snapchat][swipe_rate]" value="<?php echo esc_attr($snapchat_swipe_rate); ?>" placeholder="E.g.12">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audience Demographics -->
                    <div class="settings-section" id="demographics">
                        <div class="section-header">
                            <h2><?php _e('Audience Demographics', 'wc-frontend-manager'); ?></h2>
                        </div>
                        <div class="section-content">
                            <div class="demographics-row">
                                <div class="gender-slider figma-input-field">
                                    <label class="field-label"><?php _e('Gender Split (%)', 'wc-frontend-manager'); ?></label>
                                    <div class="gender-input-wrapper">
                                        <div class="gender-labels">
                                            <span class="female-label"><?php _e('Female', 'wc-frontend-manager'); ?></span>
                                            <span class="male-label"><?php _e('Male', 'wc-frontend-manager'); ?></span>
                                        </div>
                                        <div class="slider-container">
                                            <input type="range" name="demographics[gender_female]" id="gender-slider" min="0" max="100" value="<?php echo intval($gender_female); ?>">
                                            <div class="slider-values">
                                                <span class="female-value"><?php echo intval($gender_female); ?>%</span>
                                                <span class="male-value"><?php echo intval($gender_male); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="figma-input-field age-range-field">
                                    <label class="field-label"><?php _e('Dominant Age Range', 'wc-frontend-manager'); ?></label>
                                    <span class="field-separator"></span>
                                    <div class="select-wrapper">
                                        <select name="demographics[dominant_age]">
                                            <?php foreach ($age_ranges as $value => $label) : ?>
                                                <option value="<?php echo esc_attr($value); ?>" <?php selected($dominant_age, $value); ?>><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <!-- <i class="wcfmfa fa-chevron-down select-arrow"></i> -->
                                    </div>
                                </div>
                            </div>
                            <div class="field-group full-width figma-input-field">
                                <label class="field-label"><?php _e('Top Locations (Cities/Countries)', 'wc-frontend-manager'); ?></label>
                                <span class="field-separator"></span>
                                <input type="text" name="demographics[top_locations]" value="<?php echo esc_attr($top_locations); ?>" placeholder="<?php esc_attr_e('e.g. New York, London, Paris (Separate by commas)', 'wc-frontend-manager'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Achievements & Highlights -->
                    <div class="settings-section" id="achievements">
                        <div class="section-header">
                            <h2><?php _e('Achievements & Highlights', 'wc-frontend-manager'); ?></h2>
                            <span type="button" class="add-achievement-btn" id="add-achievement">
                                <i class="wcfmfa fa-plus-circle"></i>
                            </span>
                        </div>
                        <div class="section-content">
                            <div id="achievements-list">
                                <?php
                                if (!empty($achievements)) :
                                    foreach ($achievements as $index => $achievement) :
                                ?>
                                    <div class="achievement-row" data-index="<?php echo $index; ?>">
                                        <div class="figma-input-field">
                                            <label class="field-label"><?php _e('Achievement Title', 'wc-frontend-manager'); ?></label>
                                            <span class="field-separator"></span>
                                            <input type="text" name="achievements[<?php echo $index; ?>][title]" value="<?php echo esc_attr($achievement['title']); ?>" placeholder="<?php esc_attr_e('e.g. Featured in Vogue', 'wc-frontend-manager'); ?>">
                                        </div>
                                        <div class="figma-input-field">
                                            <label class="field-label"><?php _e('Year', 'wc-frontend-manager'); ?></label>
                                            <span class="field-separator"></span>
                                            <input type="text" name="achievements[<?php echo $index; ?>][year]" value="<?php echo esc_attr($achievement['year'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g 2024', 'wc-frontend-manager'); ?>">
                                        </div>
                                        <span type="button" class="remove-achievement-btn"><i class="wcfmfa fa-trash"></i></span>
                                    </div>
                                <?php
                                    endforeach;
                                else :
                                ?>
                                    <div class="achievement-row" data-index="0">
                                        <div class="figma-input-field">
                                            <label class="field-label"><?php _e('Achievement Title', 'wc-frontend-manager'); ?></label>
                                            <span class="field-separator"></span>
                                            <input type="text" name="achievements[0][title]" value="" placeholder="<?php esc_attr_e('e.g. Featured in Vogue', 'wc-frontend-manager'); ?>">
                                        </div>
                                        <div class="figma-input-field">
                                            <label class="field-label"><?php _e('Year', 'wc-frontend-manager'); ?></label>
                                            <span class="field-separator"></span>
                                            <input type="text" name="achievements[0][year]" value="" placeholder="<?php esc_attr_e('e.g 2024', 'wc-frontend-manager'); ?>">
                                        </div>
                                        <span type="button" class="remove-achievement-btn"><i class="wcfmfa fa-trash"></i></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="settings-column settings-right">

                    <!-- Theme Setting -->
                    <div class="settings-section" id="theme-settings">
                        <div class="section-header">
                            <h2><?php _e('Theme Setting', 'wc-frontend-manager'); ?></h2>
                        </div>
                        <div class="section-content">
                            <div class="field-group color-field figma-input-field">
                                <label class="field-label"><?php _e('Background Theme', 'wc-frontend-manager'); ?></label>
                                <span class="field-separator"></span>
                                <div class="color-input-wrap">
                                    <input type="color" name="theme[background]" value="<?php echo esc_attr($background_theme); ?>" class="color-picker" id="bg-color-picker">
                                    <span class="field-separator"></span>
                                    <a type="button" class="edit-color-btn" data-target="bg-color-picker"><i class="wcfmfa fa-pencil-alt"></i></a>
                                </div>
                            </div>
                            <div class="field-group color-field figma-input-field">
                                <label class="field-label"><?php _e('Accent Color', 'wc-frontend-manager'); ?></label>
                                <span class="field-separator"></span>
                                <div class="color-input-wrap">
                                    <input type="color" name="theme[accent]" value="<?php echo esc_attr($accent_color); ?>" class="color-picker" id="accent-color-picker">
                                    <span class="field-separator"></span>
                                    <a type="button" class="edit-color-btn" data-target="accent-color-picker"><i class="wcfmfa fa-pencil-alt"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Language -->
                    <div class="settings-section" id="language-settings">
                        <div class="section-header">
                            <h2><?php _e('Language', 'wc-frontend-manager'); ?></h2>
                        </div>
                        <div class="section-content">
                            <div id="languages-list">
                                <?php foreach ($languages as $index => $lang) : ?>
                                <div class="field-group language-row figma-input-field" data-index="<?php echo $index; ?>">
                                    <label class="field-label"><?php _e('Language', 'wc-frontend-manager'); ?></label>
                                    <span class="field-separator"></span>
                                    <div class="language-input-wrap">
                                        <input type="text" name="languages[]" value="<?php echo esc_attr($lang); ?>" placeholder="<?php esc_attr_e('e.g. English', 'wc-frontend-manager'); ?>">
                                        <span class="field-separator"></span>
                                        <a type="button" class="edit-lang-btn"><i class="wcfmfa fa-pencil-alt"></i></a>
                                        <?php if ($index > 0) : ?>
                                        <a type="button" class="remove-lang-btn"><i class="wcfmfa fa-times"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="javascript:void(0);" class="add-language-link" id="add-language">
                                + <?php _e('Add New Language', 'wc-frontend-manager'); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Collab Preferences -->
                    <div class="settings-section" id="collab-preferences">
                        <div class="section-header">
                            <h2><?php _e('Collab Preferences', 'wc-frontend-manager'); ?></h2>
                        </div>
                        <div class="section-content">
                            <div class="collab-checkboxes">
                                <div class="collab-option">
                                    <input type="checkbox" id="collab_ugc" name="collab[ugc_video]" value="yes" <?php checked($collab_ugc, 'yes'); ?>>
                                    <label for="collab_ugc"><?php _e('UGC Video', 'wc-frontend-manager'); ?></label>
                                </div>
                                <div class="collab-option">
                                    <input type="checkbox" id="collab_photography" name="collab[photography]" value="yes" <?php checked($collab_photography, 'yes'); ?>>
                                    <label for="collab_photography"><?php _e('Photography', 'wc-frontend-manager'); ?></label>
                                </div>
                                <div class="collab-option">
                                    <input type="checkbox" id="collab_modeling" name="collab[modeling]" value="yes" <?php checked($collab_modeling, 'yes'); ?>>
                                    <label for="collab_modeling"><?php _e('Modeling (In-person)', 'wc-frontend-manager'); ?></label>
                                </div>
                                <div class="collab-option">
                                    <input type="checkbox" id="collab_sponsored" name="collab[sponsored_posts]" value="yes" <?php checked($collab_sponsored, 'yes'); ?>>
                                    <label for="collab_sponsored"><?php _e('Sponsored Posts', 'wc-frontend-manager'); ?></label>
                                </div>
                                <div class="collab-option">
                                    <input type="checkbox" id="collab_tfp" name="collab[tfp]" value="yes" <?php checked($collab_tfp, 'yes'); ?>>
                                    <label for="collab_tfp"><?php _e('TFP (Time-for-Print)', 'wc-frontend-manager'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Travel Status -->
                    <div class="settings-section" id="travel-status">
                        <div class="section-header">
                            <h2><?php _e('Travel Status', 'wc-frontend-manager'); ?></h2>
                        </div>
                        <div class="section-content">
                            <div class="figma-input-field">
                                <label class="field-label"><?php _e('Available To Travel (Worldwide)', 'wc-frontend-manager'); ?></label>
                                <span class="field-separator"></span>
                                <div class="select-wrapper">
                                    <select name="travel[available]">
                                        <option value="yes" <?php selected($travel_available, 'yes'); ?>><?php _e('Yes', 'wc-frontend-manager'); ?></option>
                                        <option value="no" <?php selected($travel_available, 'no'); ?>><?php _e('No', 'wc-frontend-manager'); ?></option>
                                        <option value="limited" <?php selected($travel_available, 'limited'); ?>><?php _e('Limited', 'wc-frontend-manager'); ?></option>
                                    </select>
                                    <!-- <i class="wcfmfa fa-chevron-down select-arrow"></i> -->
                                </div>
                            </div>
                            <div class="figma-input-field">
                                <label class="field-label"><?php _e('Current Base City', 'wc-frontend-manager'); ?></label>
                                <span class="field-separator"></span>
                                <div class="select-wrapper inside__wrapper">
                                    <input type="text" name="travel[base_city]" value="<?php echo esc_attr($travel_base_city); ?>" placeholder="<?php esc_attr_e('e.g. USA', 'wc-frontend-manager'); ?>">
                                    <i class="wcfmfa fa-pencil-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="settings-actions">
                        <a href="<?php echo esc_url($store_url); ?>" class="btn-view-profile button" target="_blank">
                            <?php _e('View Profile', 'wc-frontend-manager'); ?>
                        </a>
                        <button type="submit" class="btn-save-settings" id="save-settings">
                            <?php _e('Save', 'wc-frontend-manager'); ?>
                        </button>
                    </div>

                </div>
            </div>

        </form>

        <?php do_action('after_wcfm_marketplace_settings', $user_id); ?>
    </div>
</div>

<!-- Tagline Edit Modal -->
<div class="modfolio-modal-overlay" id="tagline-modal" style="display: none;">
    <div class="modfolio-modal">
        <div class="modal-header">
            <h3><?php _e('Edit Tagline', 'wc-frontend-manager'); ?></h3>
            <button type="button" class="modal-close" id="close-tagline-modal"><i class="wcfmfa fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="field-group">
                <label><?php _e('Tagline', 'wc-frontend-manager'); ?></label>
                <input type="text" id="tagline-modal-input" value="<?php echo esc_attr($tagline); ?>" placeholder="<?php esc_attr_e('e.g. Editorial & Fashion Model', 'wc-frontend-manager'); ?>">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn btn-cancel" id="cancel-tagline"><?php _e('Cancel', 'wc-frontend-manager'); ?></button>
            <button type="button" class="modal-btn btn-confirm" id="save-tagline"><?php _e('Save', 'wc-frontend-manager'); ?></button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modfolio-modal-overlay" id="success-modal" style="display: none;">
    <div class="modfolio-modal success-modal">
        <div class="modal-content text-center">
            <div class="modal-icon success-icon">
                <i class="wcfmfa fa-check-circle"></i>
            </div>
            <h3 class="modal-title"><?php _e('Settings Saved!', 'wc-frontend-manager'); ?></h3>
            <p class="modal-text"><?php _e('Your profile has been updated successfully.', 'wc-frontend-manager'); ?></p>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modfolio-modal-overlay" id="error-modal" style="display: none;">
    <div class="modfolio-modal error-modal">
        <div class="modal-content text-center">
            <div class="modal-icon error-icon">
                <i class="wcfmfa fa-exclamation-circle"></i>
            </div>
            <h3 class="modal-title"><?php _e('Error', 'wc-frontend-manager'); ?></h3>
            <p class="modal-text" id="error-message"><?php _e('Something went wrong. Please try again.', 'wc-frontend-manager'); ?></p>
            <button type="button" class="modal-btn btn-confirm" id="close-error-modal"><?php _e('OK', 'wc-frontend-manager'); ?></button>
        </div>
    </div>
</div>

<style>
/* Modfolio Settings Page Styles */
.modfolio-settings-page {
    /* font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif; */
    background: #f5f5f5;
}

/* Header Section */
.modfolio-settings-header {
    margin-bottom: 0;
}

.header-banner {
    position: relative;
    background-size: cover;
    background-position: center;
    background-color: #1a1a1a;
    min-height: 320px;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 150, 136, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
    background: linear-gradient(0deg, #0CCAAE -20%, rgba(0, 0, 0, 0) 40%);
}

/* Banner Edit Button */
.banner-edit-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 10;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.banner-edit-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.header-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 30px;
    flex: 1;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-avatar {
    position: relative;
}

.header-avatar img {
    width: 83px;
    height: 83px;
    border-radius: 50%;
    /* border: 3px solid #fff; */
    object-fit: cover;
    padding: 3px;
    outline: 1px solid var(--new-color);
}

/* Avatar Edit Button */
.avatar-edit-btn {
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    background: #00c4aa;
    border: 2px solid #fff;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all 0.2s;
}

.avatar-edit-btn:hover {
    background: #00b09a;
}

.header-info {
    color: #fff;
}

.header-name {
    color: #fff;
    font-size: 24px;
    font-weight: 600;
    margin: 0 0 4px 0;
      line-height: 1;

}

.header-tagline-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1;
}

.header-tagline {
    color: rgba(255,255,255,0.8);
    font-size: 12px;
    margin: 0;
    line-height: 1;
}

.tagline-edit-btn {
    background: none;
    border: none;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    padding: 2px;
    font-size: 12px;
}

.tagline-edit-btn:hover {
    color: #fff;
}

.header-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.header-right .membership-badge {
    background: #fff;
    color: #000;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    display: flex;
    gap: 4px;
}

.header-right .last-login {
    color: rgba(255,255,255,0.7);
    color: #fff;
    font-size: 12px;
    text-align: right;
}
.header-right .last-login .label {
    font-weight: 800;
}

.header-bottom {
    position: relative;
    z-index: 1;
    /* padding: 15px 30px;
    border-top: 1px solid rgba(255,255,255,0.1); */
    position: absolute;
    right: 30px;
    bottom: 20px;
}

.public-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255,255,255);
    font-size: 13px;
}

.public-profile a {
    color: rgba(255,255,255, 0.7);
    text-decoration: none;
    padding: 0 10px;
    position: relative;
}
.public-profile a::before,
.public-profile a::after {
    content: '';
    height: 70%;
    width: 1px;
    background: rgba(255,255,255, 0.7);
    position: absolute;
    transform: translateY(30%);
}
.public-profile a::before {
    left: 0;
    top: 0;
}
.public-profile a::after {
    right: 0;
    top: 0;
}


.copy-url-btn {
    background: none;
    border: none;
    color: rgba(255,255,255);
    cursor: pointer;
    /* padding: 5px; */
    font-size: 14px;
}

.copy-url-btn:hover {
    color: #fff;
}

/* Profile Completion Bar */
.profile-completion-bar {
    background: #e5e5e5;
    height: 26px;
    position: relative;
    border-radius: 0 0 12px 12px;
    border-radius: 500px;
    overflow: hidden;
    margin-top: 20px;
}

.completion-progress {
    height: 100%;
    background: linear-gradient(90deg, #00c4aa, #00d4b8);
    transition: width 0.3s ease;
    border-radius: 500px;
}

.completion-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 13px;
    font-weight: 600;
    color: #fff;
}

.modfolio-settings-form{
    transform: translateY(-30px);
}

.modfolio-settings-page #wcfmmp_profile_complete_progressbar{
	display: none !important;
}

/* Settings Layout */
.modfolio-settings-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 24px;
    padding: 24px 0;
}

.settings-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Settings Section */
.settings-section {
    background: #fff;
    border-radius: 20px;
    /* border: 1px solid #e5e5e5; */
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    /* border-bottom: 1px solid #f0f0f0; */
    padding-bottom: 10px;
}

.section-header h2 {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #000 !important;
    line-height: 1 !important;
    margin-bottom: 5px !important;
    font-style: normal !important;
    padding-top: 5px !important;
}

.section-title-with-icon {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title-with-icon h2 {
    margin-bottom: 2px;
}

.section-subtitle {
    font-size: 11px;
    color: #262626;
    margin: 0;
    transform: translateY(-7px);
}

.section-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #00c4aa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.media-kit-icon {
    background: #00c4aa;
}

.section-edit-btn,
.add-achievement-btn {
    background: none;
    border: none;
    color: #00c4aa;
    cursor: pointer;
    padding: 5px;
    font-size: 20px;
}

.section-content {
    padding: 20px 20px 35px;
}

/* Field Groups */
.field-group {
    margin-bottom: 16px;
}

.field-group:last-child {
    margin-bottom: 0;
}

.field-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #666;
    margin-bottom: 6px;
}

.field-group input[type="text"],
.field-group input[type="url"],
.field-group input[type="email"],
.field-group textarea,
.field-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    font-size: 13px;
    transition: border-color 0.2s;
    box-sizing: border-box;
    background: #fff;
}

.field-group input:focus,
.field-group textarea:focus,
.field-group select:focus {
    outline: none;
    border-color: #00c4aa;
}



/* Input wrapper with edit button */
.input-wrapper {
    position: relative;
}

#wcfm-main-contentainer .input-wrapper textarea,
.input-wrapper textarea {
    padding-right: 45px;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.field-edit-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #00c4aa;
    border: none;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}


.field-group textarea {
    resize: vertical;
    min-height: 120px;
}

.field-with-icon {
    align-items: start !important;
    padding-top: 10px;
    margin-bottom: 32px !important;
}
.field-with-icon .input-wrapper{
    flex: 1;
    display: flex;
}

.field-with-icon label,
.field-with-icon textarea{
    padding-top: 0 !important;
}
.field-with-icon .ai-btn {
    visibility: hidden;
    margin: 0 10px;
}
.field-with-icon .ai-btn svg {
    width: 28px;
    height: 28px;
}

/* Select wrapper */
.select-wrapper {
    position: relative;
}

.select-wrapper select {
    appearance: none;
    padding-right: 35px;
}

.select-wrapper .select-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
    font-size: 12px;
}

/* Social Links Grid */
.social-links-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Figma-style Input Field (Label | Input) */
.figma-input-field {
    display: flex;
    align-items: center;
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 16px;
    min-height: 57px;
}

.figma-input-field .field-label {
    font-size: 13px;
    font-weight: 500;
    color: #1a1a1a;
    padding: 10px 14px;
    white-space: nowrap;
    min-width: fit-content;
}

.figma-input-field .field-separator {
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    flex-shrink: 0;
}

.figma-input-field input {
    flex: 1;
    border: none !important;
    background: transparent !important;
    padding: 10px 12px !important;
    font-size: 13px;
    color: #666;
    min-width: 0;
}
.figma-input-field select{
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.figma-input-field input:focus {
    outline: none;
    box-shadow: none !important;
}

.figma-input-field input::placeholder {
    color: #999;
}

.figma-input-field .copy-btn {
    background: none;
    border: none;
    color: #00c4aa;
    cursor: pointer;
    padding: 10px 12px;
    font-size: 16px;
    flex-shrink: 0;
}

.figma-input-field .copy-btn:hover {
    color: #00a08a;
}
.figma-input-field .inside__wrapper{
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-right: 15px;
}
.figma-input-field .inside__wrapper input{
    max-width: 90% !important;
}

/* Compact version for stats fields */
.figma-input-field.compact {
    /* background: #fafafa; */
}

.figma-input-field.compact .field-label {
    font-size: 12px;
    font-weight: 500;
    color: #1a1a1a;
    padding: 8px 12px;
}

.figma-input-field.compact .field-separator {
    height: 20px;
}

.figma-input-field.compact input {
    padding: 8px 10px !important;
    font-size: 12px;
}
#wcfm-main-contentainer input[type="text"],
.figma-input-field.compact input{
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.input-with-icon {
    display: flex;
    align-items: center;
    position: relative;
}

.input-with-icon input {
    padding-right: 40px;
}

.input-with-icon .copy-btn {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 5px;
}

.input-with-icon .copy-btn:hover {
    color: #00c4aa;
}

/* Stats Groups */
.stats-group {
    margin-bottom: 24px;
    /* padding-bottom: 20px; */
    /* border-bottom: 1px solid #f0f0f0; */
    padding: 20px 10px;
  border-radius: 20px;
  box-shadow: 0 0 6px rgba(0,0,0,0.1);
}

.stats-group:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.stats-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.stats-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.stats-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

/* .instagram-icon {
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    color: #fff;
}

.tiktok-icon {
    background: #000;
    color: #fff;
}

.youtube-icon {
    background: #ff0000;
    color: #fff;
}

.snapchat-icon {
    background: #fffc00;
    color: #000;
} */

.stats-fields {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

/* Demographics */
.demographics-row {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.gender-slider {
    width: 100%;
    box-shadow: none !important;
    border-radius: 0 !important;
}

.gender-labels {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #666;
    color: #202020;
    /* margin-bottom: 8px; */
    line-height: 1;
    font-weight: 700;
}
.gender-input-wrapper{
    width: 100%;
}

.slider-container {
    position: relative;
}

.slider-container input[type="range"] {
    width: 100%;
    height: 8px;
    -webkit-appearance: none;
    background: linear-gradient(to right, #00c4aa 0%, #00c4aa var(--val, 70%), #e5e5e5 var(--val, 70%), #e5e5e5 100%) !important;
    border-radius: 4px;
    outline: none;
    padding: 0 !important;
}

.slider-container input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    background: #00c4aa;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider-values {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #999;
    color: #202020;
    /* margin-top: 4px; */
    line-height: 1;
    font-weight: 700;
}

.age-range-field{
    height: 57px !important;
}
.age-range-field select,
#wcfm-main-contentainer .figma-input-field select{
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}


.full-width {
    grid-column: 1 / -1;
}

/* Achievements */
.achievement-row {
    display: grid;
    grid-template-columns: 1fr 120px 40px;
    gap: 12px;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    /* border-bottom: 1px solid #f0f0f0; */
}

.achievement-row:last-child {
    margin-bottom: 0;
    border-bottom: none;
}

.remove-achievement-btn {
    /* background: #fee2e2; */
    border: none;
    color: #ef4444;
    cursor: pointer;
    /* padding: 10px; */
    font-size: 22px;
    border-radius: 8px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease-in-out;
}

.remove-achievement-btn:hover {
    background: #fecaca;
    /* background: #ef4444;
    color: #fff; */
}

/* Color Fields */
.color-field {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.color-field label {
    margin-bottom: 0;
}

.color-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.figma-input-field input.color-picker,
.color-picker {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 50%;
    padding: 0 !important;
    cursor: pointer;
    overflow: hidden;
    flex: unset !important;
    margin-left: auto;
    border-radius: 500px;
   border: 1.5px solid #ccc !important;
}

.color-picker::-webkit-color-swatch-wrapper {
    padding: 0;
}

.color-picker::-webkit-color-swatch {
    border: 2px solid #e5e5e5;
    border-radius: 50%;
}

.edit-color-btn,
.edit-lang-btn {
    background: none;
    border: none;
    color: #00c4aa;
    cursor: pointer;
    padding: 5px;
    font-size: 14px;
}

/* Language */
.language-row {
    margin-bottom: 12px;
}

.language-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-right: 15px;
}

.language-input-wrap input {
    flex: 1;
}

.remove-lang-btn {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    /* padding: 5px; */
    /* font-size: 12px; */
    font-size: 18px;
}

.add-language-link {
    display: inline-block;
    color: #00c4aa;
    font-size: 13px;
    text-decoration: none;
    margin-top: 8px;
    cursor: pointer;
}

.add-language-link:hover {
    text-decoration: underline;
    color: #00c4aa !important;
}

/* Collab Checkboxes */
.collab-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.collab-option {
    display: flex;
    align-items: center;
    gap: 10px;
}

.collab-option input[type="checkbox"] {
    width: 20px;
    height: 20px;
    border: 2px solid #00c4aa;
    border-radius: 4px;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    position: relative;
    background: #fff;
    flex-shrink: 0;
}

.collab-option input[type="checkbox"]:checked {
    background: #00c4aa;
    border-color: #00c4aa;
}

input[type="checkbox"]::before{
    border: none !important;
}

.collab-option input[type="checkbox"]:checked::before {
    background: transparent !important;
}

.collab-option input[type="checkbox"]:checked::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 12px;
    color: #00c4aa;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.collab-option label {
    font-size: 13px;
    color: #1a1a1a;
    cursor: pointer;
    margin: 0;
}

/* Action Buttons */
.settings-actions {
    display: flex;
    gap: 12px;
    margin-top: 10px;
    padding-bottom: 20px;
}

.btn-view-profile,
.btn-save-settings {
    flex: 1;
    padding: 14px 24px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-view-profile {
    background: #fff;
    border: 2px solid #00c4aa;
    color: #00c4aa;
}

.btn-view-profile:hover {
    background: #f0faf9;
}

.btn-save-settings {
    background: #00c4aa;
    border: 2px solid #00c4aa;
    color: #fff;
}

.btn-save-settings:hover {
    background: #00b09a;
    border-color: #00b09a;
}

/* Modal Styles */
.modfolio-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

.modfolio-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 400px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
}

.modal-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 16px;
    padding: 5px;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    gap: 12px;
    padding: 16px 20px;
    border-top: 1px solid #f0f0f0;
}

.modal-btn {
    flex: 1;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel {
    background: #f5f5f5;
    border: 1px solid #e5e5e5;
    color: #666;
}

.btn-cancel:hover {
    background: #eee;
}

.btn-confirm {
    background: #00c4aa;
    border: none;
    color: #fff;
}

.btn-confirm:hover {
    background: #00b09a;
}

/* Success/Error Modal */
.success-modal,
.error-modal {
    text-align: center;
    padding: 40px 30px;
}

.modal-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
}

.success-icon {
    background: #d1fae5;
    color: #10b981;
}

.error-icon {
    background: #fee2e2;
    color: #ef4444;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 10px;
}

.modal-text {
    color: #666;
    font-size: 14px;
    margin: 0;
}

.text-center {
    text-align: center;
}

/* Responsive */
@media (max-width: 1024px) {
    .modfolio-settings-layout {
        grid-template-columns: 1fr;
    }

    .header-content {
        flex-direction: column;
        gap: 20px;
    }

    .header-right {
        align-items: flex-start;
    }

    .social-links-grid {
        grid-template-columns: 1fr;
    }

    .stats-fields {
        grid-template-columns: 1fr;
    }

    .demographics-row {
        grid-template-columns: 1fr;
    }

    .achievement-row {
        grid-template-columns: 1fr 100px 40px;
    }
}

@media (max-width: 768px) {
    .header-content {
        padding: 20px;
    }

    .section-content {
        padding: 15px 15px 22px;
    }

    .settings-actions {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {

    // ===== Gender Slider =====
    var $genderSlider = $('#gender-slider');
    function updateGenderSlider() {
        var val = $genderSlider.val();
        $genderSlider.css('--val', val + '%');
        $genderSlider.parent().find('.female-value').text(val + '%');
        $genderSlider.parent().find('.male-value').text((100 - val) + '%');
    }
    $genderSlider.on('input', updateGenderSlider);
    updateGenderSlider();

    // ===== Media Uploader for Banner =====
    var bannerFrame;
    $('#edit-banner-btn').on('click', function(e) {
        e.preventDefault();

        if (bannerFrame) {
            bannerFrame.open();
            return;
        }

        bannerFrame = wp.media({
            title: '<?php _e('Select Banner Image', 'wc-frontend-manager'); ?>',
            button: { text: '<?php _e('Use this image', 'wc-frontend-manager'); ?>' },
            multiple: false
        });

        bannerFrame.on('select', function() {
            var attachment = bannerFrame.state().get('selection').first().toJSON();
            $('#banner-input').val(attachment.id);
            $('.header-banner').css('background-image', 'url(' + attachment.url + ')');
        });

        bannerFrame.open();
    });

    // ===== Media Uploader for Avatar =====
    var avatarFrame;
    $('#edit-avatar-btn').on('click', function(e) {
        e.preventDefault();

        if (avatarFrame) {
            avatarFrame.open();
            return;
        }

        avatarFrame = wp.media({
            title: '<?php _e('Select Avatar Image', 'wc-frontend-manager'); ?>',
            button: { text: '<?php _e('Use this image', 'wc-frontend-manager'); ?>' },
            multiple: false
        });

        avatarFrame.on('select', function() {
            var attachment = avatarFrame.state().get('selection').first().toJSON();
            $('#gravatar-input').val(attachment.id);
            $('#avatar-preview').attr('src', attachment.url);
        });

        avatarFrame.open();
    });

    // ===== Tagline Modal =====
    $('#edit-tagline-btn').on('click', function() {
        $('#tagline-modal-input').val($('#tagline-input').val());
        $('#tagline-modal').fadeIn(200);
    });

    $('#close-tagline-modal, #cancel-tagline').on('click', function() {
        $('#tagline-modal').fadeOut(200);
    });

    $('#save-tagline').on('click', function() {
        var newTagline = $('#tagline-modal-input').val().trim();
        if (newTagline) {
            $('#tagline-input').val(newTagline);
            $('#tagline-display').text(newTagline);
        }
        $('#tagline-modal').fadeOut(200);
    });

    // ===== Add Achievement Row =====
    var achievementIndex = <?php echo count($achievements) > 0 ? count($achievements) : 1; ?>;
    $('#add-achievement').on('click', function() {
        var html = '<div class="achievement-row" data-index="' + achievementIndex + '">' +
            '<div class="field-group">' +
                '<label><?php _e('Achievement Title', 'wc-frontend-manager'); ?></label>' +
                '<input type="text" name="achievements[' + achievementIndex + '][title]" value="" placeholder="<?php esc_attr_e('e.g. Featured in Vogue', 'wc-frontend-manager'); ?>">' +
            '</div>' +
            '<div class="field-group">' +
                '<label><?php _e('Year', 'wc-frontend-manager'); ?></label>' +
                '<input type="text" name="achievements[' + achievementIndex + '][year]" value="" placeholder="<?php esc_attr_e('e.g 2024', 'wc-frontend-manager'); ?>">' +
            '</div>' +
            '<button type="button" class="remove-achievement-btn"><i class="wcfmfa fa-trash"></i></button>' +
        '</div>';
        $('#achievements-list').append(html);
        achievementIndex++;
    });

    // Remove achievement row
    $(document).on('click', '.remove-achievement-btn', function() {
        if ($('#achievements-list .achievement-row').length > 1) {
            $(this).closest('.achievement-row').remove();
        }
    });

    // ===== Add Language Row =====
    var languageIndex = <?php echo count($languages); ?>;
    $('#add-language').on('click', function(e) {
        e.preventDefault();
        var html = '<div class="field-group language-row figma-input-field" data-index="' + languageIndex + '">' +
            '<label class="field-label"><?php _e('Language', 'wc-frontend-manager'); ?></label>' +
            '<span class="field-separator"></span>'+
            '<div class="language-input-wrap">' +
                '<input type="text" name="languages[]" value="" placeholder="<?php esc_attr_e('e.g. English', 'wc-frontend-manager'); ?>">' +
                '<span class="field-separator"></span>'+
                '<a type="button" class="edit-lang-btn"><i class="wcfmfa fa-pencil-alt"></i></a>' +
                '<a type="button" class="remove-lang-btn"><i class="wcfmfa fa-times"></i></a>' +
            '</div>' +
        '</div>';
        $('#languages-list').append(html);
        languageIndex++;
    });

    // Remove language row
    $(document).on('click', '.remove-lang-btn', function() {
        $(this).closest('.language-row').remove();
    });

    // ===== Copy URL/Text Button =====
    $('.copy-url-btn, .copy-btn').on('click', function() {
        var $this = $(this);
        var $input = $this.siblings('input, a');
        var text = $input.is('input') ? $input.val() : $input.attr('href') || $input.text();

        if ($this.data('url')) {
            text = $this.data('url');
        }

        navigator.clipboard.writeText(text).then(function() {
            $this.find('i').removeClass('fa-copy').addClass('fa-check');
            setTimeout(function() {
                $this.find('i').removeClass('fa-check').addClass('fa-copy');
            }, 1500);
        });
    });

    // ===== Color Picker Edit Button =====
    $('.edit-color-btn').on('click', function() {
        var targetId = $(this).data('target');
        $('#' + targetId).click();
    });

    // ===== Close modals on overlay click =====
    $('.modfolio-modal-overlay').on('click', function(e) {
        if ($(e.target).hasClass('modfolio-modal-overlay')) {
            $(this).fadeOut(200);
        }
    });

    // ===== Close error modal =====
    $('#close-error-modal').on('click', function() {
        $('#error-modal').fadeOut(200);
    });

    // ===== Save Settings via AJAX =====
    $('#wcfm_settings_form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#save-settings');
        var originalText = $btn.text();

        $btn.prop('disabled', true).text('<?php _e('Saving...', 'wc-frontend-manager'); ?>');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: $form.serialize() + '&action=modfolio_save_vendor_settings',
            success: function(response) {
                if (response.success) {
                    // Show success modal
                    $('#success-modal').fadeIn(200);

                    // Auto close and refresh after 2 seconds
                    setTimeout(function() {
                        $('#success-modal').fadeOut(200, function() {
                            location.reload();
                        });
                    }, 2000);
                } else {
                    $('#error-message').text(response.data.message || '<?php _e('Error saving settings.', 'wc-frontend-manager'); ?>');
                    $('#error-modal').fadeIn(200);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                $('#error-message').text('<?php _e('Error saving settings. Please try again.', 'wc-frontend-manager'); ?>');
                $('#error-modal').fadeIn(200);
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

});
</script>



<!-- =========== Update Ai Butto text ============ -->

<script>
(function () {

    const svgIcon = `<svg width="34" height="33" viewBox="0 0 34 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.67854 7.73158L5.7666 19.9368C5.74339 20.0047 5.7366 20.077 5.74679 20.148C5.75699 20.219 5.78387 20.2865 5.82524 20.345C5.8666 20.4036 5.92127 20.4515 5.98475 20.4848C6.04823 20.5181 6.11871 20.5359 6.19039 20.5367H7.28574C7.3835 20.5364 7.47855 20.5045 7.55669 20.4457C7.63484 20.387 7.69188 20.3045 7.71931 20.2107L8.33218 18.2547H13.6883L13.6133 18.1341L14.2849 20.2107C14.3123 20.3045 14.3693 20.387 14.4475 20.4457C14.5256 20.5045 14.6207 20.5364 14.7184 20.5367H15.8138C15.8855 20.5371 15.9563 20.5205 16.0204 20.4882C16.0845 20.4559 16.14 20.4088 16.1823 20.3509C16.2246 20.293 16.2526 20.2258 16.2639 20.155C16.2752 20.0841 16.2695 20.0116 16.2473 19.9434L12.3354 7.7381C12.3075 7.64454 12.2503 7.56238 12.1723 7.5037C12.0943 7.44502 11.9995 7.41292 11.9018 7.41211H10.1023C10.0071 7.41405 9.91485 7.44596 9.83877 7.50331C9.76269 7.56066 9.70663 7.64054 9.67854 7.73158ZM8.98417 16.2987L11.0021 10.0168L13.0363 16.2987H8.98417Z" fill="#009F88"/>
                        <path d="M20.73 7.41602H19.6869C19.4348 7.41602 19.2305 7.62035 19.2305 7.87241V20.0777C19.2305 20.3297 19.4348 20.5341 19.6869 20.5341H20.73C20.9821 20.5341 21.1864 20.3297 21.1864 20.0777V7.87241C21.1864 7.62035 20.9821 7.41602 20.73 7.41602Z" fill="#009F88"/>
                        <path d="M15.4065 25.4276H5.92007C5.21456 25.4276 4.53795 25.1474 4.03908 24.6485C3.54021 24.1496 3.25995 23.473 3.25995 22.7675V5.92008C3.25995 5.21457 3.54021 4.53796 4.03908 4.03909C4.53795 3.54022 5.21456 3.25996 5.92007 3.25996H22.7675C23.473 3.25996 24.1496 3.54022 24.6485 4.03909C25.1474 4.53796 25.4276 5.21457 25.4276 5.92008V14.3438C25.4336 14.5216 25.5095 14.6899 25.639 14.812C25.7684 14.9341 25.9408 15.0002 26.1187 14.9958C26.6738 14.9941 27.2218 15.1213 27.7194 15.3674C27.8227 15.4204 27.9379 15.4457 28.0539 15.4409C28.1699 15.436 28.2826 15.4012 28.3811 15.3398C28.4797 15.2784 28.5606 15.1925 28.616 15.0905C28.6714 14.9885 28.6994 14.8739 28.6973 14.7578V5.92008C28.6974 5.14182 28.5439 4.3712 28.2458 3.65231C27.9477 2.93341 27.5107 2.28035 26.9599 1.7305C26.4092 1.18064 25.7554 0.744772 25.036 0.447837C24.3166 0.150901 23.5458 -0.00127759 22.7675 8.07881e-06H5.92007C4.34997 8.07881e-06 2.84418 0.623728 1.73395 1.73396C0.62372 2.84419 0 4.34998 0 5.92008V22.7675C0 24.3376 0.62372 25.8434 1.73395 26.9536C2.84418 28.0639 4.34997 28.6876 5.92007 28.6876H16.7757C16.9091 28.6877 17.0395 28.6484 17.1506 28.5744C17.2616 28.5005 17.3482 28.3953 17.3995 28.2722C17.4508 28.149 17.4644 28.0135 17.4387 27.8826C17.413 27.7517 17.349 27.6314 17.2549 27.5368C16.7477 27.0714 16.3368 26.511 16.0455 25.8873C15.9988 25.7545 15.9125 25.6392 15.7982 25.557C15.684 25.4748 15.5473 25.4297 15.4065 25.4276Z" fill="#009F88"/>
                        <path d="M29.8998 26.6658L32.8533 25.502C32.9141 25.478 32.9663 25.4362 33.0031 25.3822C33.0399 25.3281 33.0595 25.2642 33.0595 25.1989C33.0595 25.1335 33.0399 25.0696 33.0031 25.0155C32.9663 24.9615 32.9141 24.9197 32.8533 24.8957L29.8998 23.7319C29.377 23.5252 28.9021 23.2134 28.5046 22.8158C28.107 22.4183 27.7952 21.9434 27.5885 21.4206L26.4247 18.4866C26.4006 18.426 26.3587 18.374 26.3047 18.3373C26.2506 18.3007 26.1868 18.2812 26.1215 18.2813C26.0562 18.2812 25.9924 18.3007 25.9384 18.3373C25.8843 18.374 25.8425 18.426 25.8184 18.4866L24.6546 21.4206C24.4483 21.9437 24.1367 22.4188 23.7391 22.8165C23.3415 23.2141 22.8664 23.5257 22.3433 23.7319L19.4093 24.8957C19.3485 24.9197 19.2963 24.9615 19.2596 25.0155C19.2228 25.0696 19.2031 25.1335 19.2031 25.1989C19.2031 25.2642 19.2228 25.3281 19.2596 25.3822C19.2963 25.4362 19.3485 25.478 19.4093 25.502L22.3433 26.6658C22.8664 26.8721 23.3415 27.1837 23.7391 27.5813C24.1367 27.9789 24.4483 28.454 24.6546 28.9771L25.8184 31.9111C25.842 31.9724 25.8836 32.0251 25.9377 32.0623C25.9918 32.0995 26.0559 32.1195 26.1215 32.1197C26.1872 32.1195 26.2513 32.0995 26.3054 32.0623C26.3595 32.0251 26.4011 31.9724 26.4247 31.9111L27.5885 28.9771C27.7952 28.4543 28.107 27.9794 28.5046 27.5819C28.9021 27.1843 29.377 26.8726 29.8998 26.6658Z" fill="#009F88"/>
                    </svg>`;

    function transformAiButton(btn) {
        if (btn.dataset.transformed) return;

        // Insert separator BEFORE the button
        const separator = document.createElement('span');
        separator.className = 'field-separator';
        btn.parentNode.insertBefore(separator, btn);

        // Create <a> element
        const link = document.createElement('a');
        link.href = 'javascript:void(0)';

        // Remove "button" class, keep others
        link.className = btn.className
            .split(' ')
            .filter(cls => cls !== 'button')
            .join(' ');

        // Copy attributes except type/class
        [...btn.attributes].forEach(attr => {
            if (!['type', 'class'].includes(attr.name)) {
                link.setAttribute(attr.name, attr.value);
            }
        });

        link.innerHTML = svgIcon;
        link.dataset.transformed = '1';
        link.style.visibility = 'visible';

        btn.replaceWith(link);
    }

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (!(node instanceof HTMLElement)) return;

                // Direct match
                if (node.matches?.('.ai-btn')) {
                    transformAiButton(node);
                }

                // Nested match
                node.querySelectorAll?.('.ai-btn').forEach(transformAiButton);
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

})();
</script>