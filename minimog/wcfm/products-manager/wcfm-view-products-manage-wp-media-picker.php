<?php
/**
 * WCFM Products Manage View - Modfolio Design
 * Clean template matching Figma design exactly
 */

require_once get_template_directory() . '/wcfm/wcfm-helpers.php';

global $wp, $WCFM, $wc_product_attributes;

// Permission checks
if( apply_filters( 'wcfm_is_pref_restriction_check', true ) ) {
    if( !apply_filters( 'wcfm_is_allow_manage_products', true ) ) {
        wcfm_restriction_message_show( "Products" );
        return;
    }
}

if( isset( $wp->query_vars['wcfm-products-manage'] ) && empty( $wp->query_vars['wcfm-products-manage'] ) ) {
    if( !apply_filters( 'wcfm_is_allow_add_products', true ) || !apply_filters( 'wcfm_is_allow_pm_add_products', true ) ) {
        wcfm_restriction_message_show( "Add Product" );
        return;
    }
    if( !apply_filters( 'wcfm_is_allow_product_limit', true ) ) {
        ?>
        <div class="collapse wcfm-collapse modfolio-product-page" id="wcfm_products_manage">
            <div class="wcfm-collapse-content">
                <div class="modfolio-limit-reached">
                    <div class="limit-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#00c4aa" stroke-width="2"/>
                            <path d="M12 7v5" stroke="#00c4aa" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="16" r="1" fill="#00c4aa"/>
                        </svg>
                    </div>
                    <h2><?php _e('Product Limit Reached', 'wc-frontend-manager'); ?></h2>
                    <p class="limit-message"><?php _e('You have reached the maximum number of products allowed on your current plan.', 'wc-frontend-manager'); ?></p>
                    <p class="limit-submessage"><?php _e('Upgrade your membership to unlock more product uploads and premium features.', 'wc-frontend-manager'); ?></p>
                    <div class="limit-actions">
                        <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="btn-upgrade">
                            <!-- <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                            </svg> -->
                            <?php _e('Upgrade Now', 'wc-frontend-manager'); ?>
                        </a>
                        <a href="<?php echo esc_url(get_wcfm_products_url()); ?>" class="btn-back">
                            <?php _e('View My Products', 'wc-frontend-manager'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <style>
        .modfolio-limit-reached {
            /* max-width: 500px; */
            /* margin: 60px auto; */
            text-align: center;
            padding: 50px 40px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }
        
        .modfolio-limit-reached .limit-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #e8faf8 0%, #d0f5f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
        }
        .modfolio-limit-reached h2 {
            font-size: 28px !important;
            font-weight: 700 !important;
            color: #1a1a1a !important;
            margin: 0 0 16px;
            font-style: normal !important;
            width: 100% !important;
        }
        .modfolio-limit-reached .limit-message {
            font-size: 16px;
            color: #666;
            margin: 0 0 8px;
            line-height: 1.6;
        }
        .modfolio-limit-reached .limit-submessage {
            font-size: 15px;
            color: #888;
            margin: 0 0 32px;
            line-height: 1.6;
        }
        .modfolio-limit-reached .limit-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }
        .modfolio-limit-reached .btn-upgrade {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #00c4aa 0%, #00b09a 100%);
            color: #fff;
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(0, 196, 170, 0.35);
            transition: all 0.3s ease;
        }
        .modfolio-limit-reached .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0, 196, 170, 0.45);
            color: #fff;
        }
        .modfolio-limit-reached .btn-back {
            color: #666;
            font-size: 14px;
            text-decoration: none;
            padding: 10px 20px;
            transition: color 0.2s;
        }
        .modfolio-limit-reached .btn-back:hover {
            color: #00c4aa;
        }
        @media (max-width: 600px) {
            .modfolio-limit-reached {
                margin: 30px 16px;
                padding: 40px 24px;
            }
            .modfolio-limit-reached h2 {
                font-size: 22px;
            }
            .modfolio-limit-reached .btn-upgrade {
                width: 100%;
                justify-content: center;
            }
        }






        /* ====================== dark theme styling ================= */

        .dark__theme .modfolio-limit-reached{
            background: #202020 !important;
        }
        .dark__theme .modfolio-limit-reached h2{
            color: #fff !important;
        }
        .dark__theme .modfolio-limit-reached .limit-message,
        .dark__theme .modfolio-limit-reached .limit-submessage{
            color: #bababa !important;
        }
        .dark__theme .modfolio-limit-reached .btn-back{
            color: #fff !important;
        }
        #wcfm-main-contentainer input.upload_button{
            color: #fff !important;
            border-radius: 20px !important;
        }
        #wcfm-main-contentainer input.remove_button{
            padding: 7px 10px !important;
            line-height: 1;
            min-height: fit-content;
            height: unset !important;
        }
        #wcfm-main-contentainer .multi_input_block_manupulate{
            border-radius: 20px !important;
        }





        </style>
        <?php
        return;
    }
} elseif( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
    $wcfm_products_single = get_post( $wp->query_vars['wcfm-products-manage'] );
    if( !apply_filters( 'wcfm_is_allow_edit_products', true ) || !apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) {
        wcfm_restriction_message_show( "Edit Product" );
        return;
    }
    if( wcfm_is_vendor() && !$WCFM->wcfm_vendor_support->wcfm_is_product_from_vendor( $wp->query_vars['wcfm-products-manage'] ) ) {
        wcfm_restriction_message_show( "Restricted Product" );
        return;
    }
}

// Initialize variables
$product_id = 0;
$product = null;
$title = '';
$excerpt = '';
$description = '';
$featured_img = '';
$gallery_img_ids = array();
$gallery_img_urls = array();
$categories = array();
$is_downloadable = 'enable';
$is_virtual = 'enable';
$downloadable_files = array();
$download_limit = '';
$download_expiry = '';
$regular_price = '';

// Load existing product data
if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
    $product = wc_get_product( $wp->query_vars['wcfm-products-manage'] );
    if( is_a( $product, 'WC_Product' ) ) {
        $product_id = $wp->query_vars['wcfm-products-manage'];
        $wcfm_products_single = get_post($product_id);
        $title = $product->get_title( 'edit' );
        $excerpt = wp_strip_all_tags( $product->get_short_description( 'edit' ) );
        $description = wp_strip_all_tags( $product->get_description( 'edit' ) );
        $regular_price = $product->get_regular_price( 'edit' );

        $featured_img = $product->get_image_id() ?: '';
        $gallery_img_ids = $product->get_gallery_image_ids();
        if(!empty($gallery_img_ids)) {
            foreach($gallery_img_ids as $gallery_img_id) {
                $gallery_img_urls[]['gimage'] = $gallery_img_id;
            }
        }

        $pcategories = get_the_terms( $product_id, 'product_cat' );
        if( !empty($pcategories) ) {
            foreach($pcategories as $pcategory) {
                $categories[] = $pcategory->term_id;
            }
        }

        $is_virtual = ( get_post_meta( $product_id, '_virtual', true) == 'yes' ) ? 'enable' : '';
        $is_downloadable = ( get_post_meta( $product_id, '_downloadable', true) == 'yes' ) ? 'enable' : '';
        if($is_downloadable == 'enable') {
            $downloadable_files = (array) get_post_meta( $product_id, '_downloadable_files', true);
            $download_limit = ( -1 == get_post_meta( $product_id, '_download_limit', true) ) ? '' : get_post_meta( $product_id, '_download_limit', true);
            $download_expiry = ( -1 == get_post_meta( $product_id, '_download_expiry', true) ) ? '' : get_post_meta( $product_id, '_download_expiry', true);
        }
    }
}

// Get ALL categories dynamically (hierarchical)
$product_categories = get_terms( array(
    'taxonomy' => 'product_cat',
    'orderby' => 'name',
    'hide_empty' => false,
) );

$gallerylimit = apply_filters( 'wcfm_gallerylimit', 5 );
$wcfm_is_translated_product = false;
$wcfm_wpml_edit_disable_element = '';
$product_type = 'simple';

// Get saved license data
$saved_licenses = $product_id ? get_post_meta($product_id, '_product_licenses', true) : array();
if (!is_array($saved_licenses)) $saved_licenses = array();
$saved_package = $product_id ? get_post_meta($product_id, '_license_package', true) : 'standard';
if (empty($saved_package)) $saved_package = 'standard'; // Default to standard

// Get exclusive product flag
$is_exclusive_product = $product_id ? get_post_meta($product_id, '_is_exclusive_product', true) : 'no';
if (empty($is_exclusive_product)) $is_exclusive_product = 'no'; // Default to no

// Get make product free flag (only applies when exclusive)
$make_product_free = $product_id ? get_post_meta($product_id, '_make_product_free', true) : 'no';
if (empty($make_product_free)) $make_product_free = 'no';

// Breadcrumb
$breadcrumb_items = array(
    array( 'label' => __('Portfolio', 'wc-frontend-manager'), 'url' => get_wcfm_products_url() ),
    array( 'label' => $product_id ? __('Edit', 'wc-frontend-manager') : __('Create New', 'wc-frontend-manager'), 'url' => '' )
);
?>

<div class="collapse wcfm-collapse modfolio-product-page" id="wcfm_products_manage">
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <?php modfolio_wcfm_render_header( '', $breadcrumb_items ); ?>
        <?php do_action( 'before_wcfm_product_simple' ); ?>

        <form id="wcfm_products_manage_form" class="wcfm">
            <?php do_action( 'begin_wcfm_products_manage_form' ); ?>

            <div class="modfolio-form-card">

                <!-- ========== GALLERY UPLOAD SECTION ========== -->
                <div class="modfolio-gallery-section">
                    <div class="modfolio-dropzone" id="modfolio-dropzone">
                        <div class="dropzone-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                <path d="M24 8L24 32M24 8L16 16M24 8L32 16" stroke="#00c4aa" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 28V36C8 38.2091 9.79086 40 12 40H36C38.2091 40 40 38.2091 40 36V28" stroke="#00c4aa" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3><?php _e('Drag and Drop up to 5 Images (Min. 3 Required)', 'wc-frontend-manager'); ?></h3>
                        <p><?php _e('You can upload JPEG, PNG, PDF, and MP4 files up to 50MB', 'wc-frontend-manager'); ?></p>
                        <button type="button" class="modfolio-upload-btn" id="trigger-gallery-upload">
                            <?php _e('Choose File', 'wc-frontend-manager'); ?>
                        </button>
                    </div>

                    <!-- Gallery Preview Grid -->
                    <div class="modfolio-gallery-grid" id="gallery-preview">
                        <?php
                        // Featured image
                        if( $featured_img ) {
                            $img_url = wp_get_attachment_image_url( $featured_img, 'medium' );
                            if( $img_url ) {
                                echo '<div class="gallery-thumb featured" data-id="' . esc_attr($featured_img) . '">';
                                echo '<img src="' . esc_url($img_url) . '" alt="">';
                                echo '<div class="thumb-actions">';
                                echo '<span class="thumb-btn featured-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></span>';
                                echo '<span type="button" class="thumb-btn remove-thumb" data-type="featured"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></span>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                        // Gallery images
                        if( !empty($gallery_img_ids) ) {
                            foreach( $gallery_img_ids as $gimg_id ) {
                                $gimg_url = wp_get_attachment_image_url( $gimg_id, 'medium' );
                                if( $gimg_url ) {
                                    echo '<div class="gallery-thumb" data-id="' . esc_attr($gimg_id) . '">';
                                    echo '<img src="' . esc_url($gimg_url) . '" alt="">';
                                    echo '<div class="thumb-actions">';
                                    echo '<span type="button" class="thumb-btn remove-thumb" data-type="gallery"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></span>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                        }
                        ?>
                    </div>

                    <!-- Hidden inputs for featured and gallery images -->
                    <input type="hidden" name="featured_img" id="featured_img" value="<?php echo esc_attr($featured_img); ?>">
                    <input type="hidden" name="gallery_img_ids" id="gallery_img_ids" value="<?php echo esc_attr(implode(',', $gallery_img_ids)); ?>">
                </div>

                <!-- ========== TITLE FIELD ========== -->
                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Title', 'wc-frontend-manager'); ?></label>
                        <input type="text" name="pro_title" id="pro_title" placeholder="<?php _e('Enter Title Here', 'wc-frontend-manager'); ?>" value="<?php echo esc_attr($title); ?>">
                    </div>
                </div>

                <!-- ========== CATEGORY FIELD ========== -->
                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline modfolio-category-field">
                        <label><?php _e('Category', 'wc-frontend-manager'); ?></label>
                        <div class="category-pills">
                            <?php
                            if ( $product_categories && !is_wp_error($product_categories) ) {
                                foreach( $product_categories as $cat ) {
                                    $checked = in_array( $cat->term_id, $categories ) ? 'checked' : '';
                                    echo '<label class="pill"><input type="checkbox" name="product_cats[]" value="' . esc_attr($cat->term_id) . '" ' . $checked . '><span>' . esc_html($cat->name) . '</span></label>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- ========== DESCRIPTION ========== -->
                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline modfolio-full">
                        <label><?php _e('Description', 'wc-frontend-manager'); ?></label>
                        <div class="textarea-wrap">
                            <textarea name="excerpt" id="excerpt" placeholder="<?php _e('Enter Text Here', 'wc-frontend-manager'); ?>" rows="4"><?php echo esc_textarea($excerpt); ?></textarea>
                            <span type="button" class="ai-btn" title="AI Assist">
                                <!-- <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2L14.5 9.5H22L16 14L18.5 22L12 17L5.5 22L8 14L2 9.5H9.5L12 2Z" stroke="currentColor" stroke-width="2"/></svg>
                                AI -->
                                <svg width="34" height="33" viewBox="0 0 34 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.67854 7.73158L5.7666 19.9368C5.74339 20.0047 5.7366 20.077 5.74679 20.148C5.75699 20.219 5.78387 20.2865 5.82524 20.345C5.8666 20.4036 5.92127 20.4515 5.98475 20.4848C6.04823 20.5181 6.11871 20.5359 6.19039 20.5367H7.28574C7.3835 20.5364 7.47855 20.5045 7.55669 20.4457C7.63484 20.387 7.69188 20.3045 7.71931 20.2107L8.33218 18.2547H13.6883L13.6133 18.1341L14.2849 20.2107C14.3123 20.3045 14.3693 20.387 14.4475 20.4457C14.5256 20.5045 14.6207 20.5364 14.7184 20.5367H15.8138C15.8855 20.5371 15.9563 20.5205 16.0204 20.4882C16.0845 20.4559 16.14 20.4088 16.1823 20.3509C16.2246 20.293 16.2526 20.2258 16.2639 20.155C16.2752 20.0841 16.2695 20.0116 16.2473 19.9434L12.3354 7.7381C12.3075 7.64454 12.2503 7.56238 12.1723 7.5037C12.0943 7.44502 11.9995 7.41292 11.9018 7.41211H10.1023C10.0071 7.41405 9.91485 7.44596 9.83877 7.50331C9.76269 7.56066 9.70663 7.64054 9.67854 7.73158ZM8.98417 16.2987L11.0021 10.0168L13.0363 16.2987H8.98417Z" fill="#009F88"/>
                                    <path d="M20.73 7.41602H19.6869C19.4348 7.41602 19.2305 7.62035 19.2305 7.87241V20.0777C19.2305 20.3297 19.4348 20.5341 19.6869 20.5341H20.73C20.9821 20.5341 21.1864 20.3297 21.1864 20.0777V7.87241C21.1864 7.62035 20.9821 7.41602 20.73 7.41602Z" fill="#009F88"/>
                                    <path d="M15.4065 25.4276H5.92007C5.21456 25.4276 4.53795 25.1474 4.03908 24.6485C3.54021 24.1496 3.25995 23.473 3.25995 22.7675V5.92008C3.25995 5.21457 3.54021 4.53796 4.03908 4.03909C4.53795 3.54022 5.21456 3.25996 5.92007 3.25996H22.7675C23.473 3.25996 24.1496 3.54022 24.6485 4.03909C25.1474 4.53796 25.4276 5.21457 25.4276 5.92008V14.3438C25.4336 14.5216 25.5095 14.6899 25.639 14.812C25.7684 14.9341 25.9408 15.0002 26.1187 14.9958C26.6738 14.9941 27.2218 15.1213 27.7194 15.3674C27.8227 15.4204 27.9379 15.4457 28.0539 15.4409C28.1699 15.436 28.2826 15.4012 28.3811 15.3398C28.4797 15.2784 28.5606 15.1925 28.616 15.0905C28.6714 14.9885 28.6994 14.8739 28.6973 14.7578V5.92008C28.6974 5.14182 28.5439 4.3712 28.2458 3.65231C27.9477 2.93341 27.5107 2.28035 26.9599 1.7305C26.4092 1.18064 25.7554 0.744772 25.036 0.447837C24.3166 0.150901 23.5458 -0.00127759 22.7675 8.07881e-06H5.92007C4.34997 8.07881e-06 2.84418 0.623728 1.73395 1.73396C0.62372 2.84419 0 4.34998 0 5.92008V22.7675C0 24.3376 0.62372 25.8434 1.73395 26.9536C2.84418 28.0639 4.34997 28.6876 5.92007 28.6876H16.7757C16.9091 28.6877 17.0395 28.6484 17.1506 28.5744C17.2616 28.5005 17.3482 28.3953 17.3995 28.2722C17.4508 28.149 17.4644 28.0135 17.4387 27.8826C17.413 27.7517 17.349 27.6314 17.2549 27.5368C16.7477 27.0714 16.3368 26.511 16.0455 25.8873C15.9988 25.7545 15.9125 25.6392 15.7982 25.557C15.684 25.4748 15.5473 25.4297 15.4065 25.4276Z" fill="#009F88"/>
                                    <path d="M29.8998 26.6658L32.8533 25.502C32.9141 25.478 32.9663 25.4362 33.0031 25.3822C33.0399 25.3281 33.0595 25.2642 33.0595 25.1989C33.0595 25.1335 33.0399 25.0696 33.0031 25.0155C32.9663 24.9615 32.9141 24.9197 32.8533 24.8957L29.8998 23.7319C29.377 23.5252 28.9021 23.2134 28.5046 22.8158C28.107 22.4183 27.7952 21.9434 27.5885 21.4206L26.4247 18.4866C26.4006 18.426 26.3587 18.374 26.3047 18.3373C26.2506 18.3007 26.1868 18.2812 26.1215 18.2813C26.0562 18.2812 25.9924 18.3007 25.9384 18.3373C25.8843 18.374 25.8425 18.426 25.8184 18.4866L24.6546 21.4206C24.4483 21.9437 24.1367 22.4188 23.7391 22.8165C23.3415 23.2141 22.8664 23.5257 22.3433 23.7319L19.4093 24.8957C19.3485 24.9197 19.2963 24.9615 19.2596 25.0155C19.2228 25.0696 19.2031 25.1335 19.2031 25.1989C19.2031 25.2642 19.2228 25.3281 19.2596 25.3822C19.2963 25.4362 19.3485 25.478 19.4093 25.502L22.3433 26.6658C22.8664 26.8721 23.3415 27.1837 23.7391 27.5813C24.1367 27.9789 24.4483 28.454 24.6546 28.9771L25.8184 31.9111C25.842 31.9724 25.8836 32.0251 25.9377 32.0623C25.9918 32.0995 26.0559 32.1195 26.1215 32.1197C26.1872 32.1195 26.2513 32.0995 26.3054 32.0623C26.3595 32.0251 26.4011 31.9724 26.4247 31.9111L27.5885 28.9771C27.7952 28.4543 28.107 27.9794 28.5046 27.5819C28.9021 27.1843 29.377 26.8726 29.8998 26.6658Z" fill="#009F88"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="description" id="description" value="<?php echo esc_attr($excerpt); ?>">

                <!-- ========== LICENSE SECTION ========== -->
                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('License Type', 'wc-frontend-manager'); ?></label>
                        <select name="_license_package" id="_license_package">
                            <option value="standard" <?php selected($saved_package, 'standard'); ?>><?php _e('Standard (Personal + Commercial + AI Training)', 'wc-frontend-manager'); ?></option>
                            <option value="exclusive" <?php selected($saved_package, 'exclusive'); ?>><?php _e('Exclusive', 'wc-frontend-manager'); ?></option>
                        </select>
                    </div>
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Is this product exclusive?', 'wc-frontend-manager'); ?></label>
                        <select name="_is_exclusive_product" id="_is_exclusive_product">
                            <option value="no" <?php selected($is_exclusive_product, 'no'); ?>><?php _e('No', 'wc-frontend-manager'); ?></option>
                            <option value="yes" <?php selected($is_exclusive_product, 'yes'); ?>><?php _e('Yes', 'wc-frontend-manager'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Standard License Fields -->
                <div class="license-fields-group license-group-standard" style="<?php echo $saved_package === 'standard' ? 'display:block;' : 'display:none;'; ?>">
                    <div class="modfolio-row">
                        <div class="modfolio-field modfolio-field-inline">
                            <label><?php _e('Individual Price $', 'wc-frontend-manager'); ?></label>
                            <input type="number" name="_product_license_personal_price" placeholder="<?php _e('Enter Price', 'wc-frontend-manager'); ?>" step="0.01" min="0" value="<?php echo esc_attr(isset($saved_licenses['personal']['price']) ? $saved_licenses['personal']['price'] : ''); ?>">
                        </div>
                        <div class="modfolio-field modfolio-field-inline">
                            <label><?php _e('Commercial Price $', 'wc-frontend-manager'); ?></label>
                            <input type="number" name="_product_license_commercial_price" placeholder="<?php _e('Enter Price', 'wc-frontend-manager'); ?>" step="0.01" min="0" value="<?php echo esc_attr(isset($saved_licenses['commercial']['price']) ? $saved_licenses['commercial']['price'] : ''); ?>">
                        </div>
                    </div>
                    <div class="modfolio-row">
                        <div class="modfolio-field modfolio-field-inline">
                            <label><?php _e('AI Training Price $', 'wc-frontend-manager'); ?></label>
                            <input type="number" name="_product_license_ai_training_price" placeholder="<?php _e('Enter Price', 'wc-frontend-manager'); ?>" step="0.01" min="0" value="<?php echo esc_attr(isset($saved_licenses['ai_training']['price']) ? $saved_licenses['ai_training']['price'] : ''); ?>">
                        </div>
                        <div class="modfolio-field modfolio-field-inline" style="visibility: hidden;"></div>
                    </div>
                </div>

                <!-- Exclusive License Fields -->
                <div class="license-fields-group license-group-exclusive" style="<?php echo $saved_package === 'exclusive' ? 'display:block;' : 'display:none;'; ?>">
                    <div class="modfolio-row">
                        <div class="modfolio-field modfolio-field-inline">
                            <label><?php _e('Exclusive Price $', 'wc-frontend-manager'); ?></label>
                            <input type="number" name="_product_license_exclusive_price" placeholder="<?php _e('Enter Price', 'wc-frontend-manager'); ?>" step="0.01" min="0" value="<?php echo esc_attr(isset($saved_licenses['exclusive']['price']) ? $saved_licenses['exclusive']['price'] : ''); ?>">
                        </div>
                        <div class="modfolio-field modfolio-field-inline" style="visibility: hidden;"></div>
                    </div>
                </div>

                <!-- Make Product Free (only shows when exclusive product is Yes) -->
                <div class="modfolio-row make-product-free-row" id="make-product-free-section" style="<?php echo $is_exclusive_product === 'yes' ? 'display:flex;' : 'display:none;'; ?>">
                    <div class="modfolio-field modfolio-checkbox-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="_make_product_free" id="_make_product_free" value="yes" <?php checked($make_product_free, 'yes'); ?>>
                            <span class="checkbox-text"><?php _e('Make Product Free', 'wc-frontend-manager'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- ========== DOWNLOADABLE FILES ========== -->
                <div class="modfolio-downloads-section" id="downloads-section">
                    <div class="section-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00c4aa" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        <span><?php _e('Downloadable Files', 'wc-frontend-manager'); ?></span>
                    </div>
                    <?php
                    $WCFM->wcfm_fields->wcfm_generate_form_field( array(
                        "downloadable_files" => array(
                            // 'label' => __('Files', 'wc-frontend-manager'),
                            'type' => 'multiinput',
                            'class' => 'wcfm-text wcfm_ele simple downlodable',
                            'label_class' => 'wcfm_title',
                            'value' => $downloadable_files,
                            'options' => array(
                                "name" => array(
                                    'label' => __('Name', 'wc-frontend-manager'),
                                    'type' => 'text',
                                    'class' => 'wcfm-text wcfm_ele simple downlodable',
                                    'label_class' => 'wcfm_ele wcfm_title simple downlodable',
                                    'custom_attributes' => array( 'required' => 1 )
                                ),
                                "file" => array(
                                    'label' => __('File', 'wc-frontend-manager'),
                                    'type' => 'upload',
                                    'mime' => 'Uploads',
                                    'button_class' => 'downloadable_product',
                                    'class' => 'wcfm-text wcfm_ele simple downlodable downlodable_file',
                                    'label_class' => 'wcfm_ele wcfm_title simple downlodable',
                                    'custom_attributes' => array( 'required' => 1 )
                                ),
                                "previous_hash" => array( 'type' => 'hidden', 'name' => 'id' )
                            )
                        ),
                        "download_limit" => array(
                            'label' => __('Download Limit', 'wc-frontend-manager'),
                            'type' => 'number',
                            'value' => $download_limit,
                            'placeholder' => __('Unlimited', 'wc-frontend-manager'),
                            'class' => 'wcfm-text wcfm_ele simple downlodable wcfm_half_ele',
                            'label_class' => 'wcfm_ele wcfm_title simple downlodable wcfm_half_ele_title',
                            'attributes' => array( 'min' => '0', 'step' => '1' )
                        ),
                        "download_expiry" => array(
                            'label' => __('Download Expiry', 'wc-frontend-manager'),
                            'type' => 'number',
                            'value' => $download_expiry,
                            'placeholder' => __('Never', 'wc-frontend-manager'),
                            'class' => 'wcfm-text wcfm_ele simple downlodable wcfm_half_ele wcfm_half_ele_right',
                            'label_class' => 'wcfm_ele wcfm_title simple downlodable wcfm_half_ele_title',
                            'attributes' => array( 'min' => '0', 'step' => '1' )
                        )
                    ) );
                    ?>
                </div>

                <!-- Custom Fields Section Hidden -->
                <?php
                // Custom fields are hidden as per user request
                // Uncomment below to enable custom fields
                /*
                <div class="modfolio-custom-fields-section" id="custom-fields-section">
                    <?php
                    $custom_field_template = $WCFM->plugin_path . 'views/products-manager/wcfm-view-customfield-products-manage.php';
                    if( file_exists( $custom_field_template ) ) {
                        include( $custom_field_template );
                    }
                    ?>
                </div>
                */
                ?>

                <!-- Hidden fields -->
                <input type="hidden" name="product_type" value="simple">
                <input type="hidden" name="is_virtual" value="enable">
                <input type="hidden" name="is_downloadable" value="enable">
                <input type="hidden" name="pro_id" id="pro_id" value="<?php echo esc_attr($product_id); ?>">

                <?php do_action( 'wcfm_product_manager_left_panel_after', $product_id ); ?>
                <?php do_action( 'end_wcfm_products_manage', $product_id, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element ); ?>

                <!-- ========== SUBMIT BUTTONS ========== -->
                <div class="modfolio-submit">
                    <div class="wcfm-message" tabindex="-1"></div>
                    <div class="gallery-validation-message" id="gallery-validation-msg" style="display: none; margin-bottom: 15px; padding: 12px 16px; border-radius: 8px; background: #fff3cd; border: 1px solid #ffc107; color: #856404;">
                        <?php _e('Please upload between 3 and 5 images to submit your product.', 'wc-frontend-manager'); ?>
                    </div>
                    <div class="submit-buttons">
                        <input type="submit" name="submit-data" value="<?php echo $product_id ? __('Update', 'wc-frontend-manager') : __('Submit for Review', 'wc-frontend-manager'); ?>" id="wcfm_products_simple_submit_button" class="wcfm_submit_button btn-primary">
                        <button type="button" class="btn-secondary" id="preview-btn">
                            <?php _e('Preview', 'wc-frontend-manager'); ?>
                        </button>
                    </div>
                    <input type="hidden" name="wcfm_nonce" value="<?php echo wp_create_nonce( 'wcfm_products_manage' ); ?>">
                </div>

            </div>
        </form>
        <?php do_action( 'after_wcfm_products_manage' ); ?>
    </div>
</div>

<!-- Bootstrap 5 Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true" style="z-index: 999999;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel"><?php _e('Product Preview', 'wc-frontend-manager'); ?></h5>
                <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></span>
            </div>
            <div class="modal-body">
                <div class="preview-gallery" id="preview-gallery"></div>
                <div class="preview-details">
                    <h3 id="preview-title"></h3>
                    <div class="preview-categories" id="preview-categories"></div>
                    <p id="preview-description"></p>
                    <div class="preview-license" id="preview-license"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Close', 'wc-frontend-manager'); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
<?php echo modfolio_wcfm_get_header_styles(); ?>

/* ========== BASE ========== */
.modfolio-product-page { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; }
.modfolio-product-page .wcfm-page-headig { display: none !important; }

/* ========== FORM CARD ========== */
.modfolio-form-card { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #e8e8e8; border: none; box-shadow: none;}

/* ========== GALLERY SECTION ========== */
.modfolio-gallery-section { margin-bottom: 32px; }
.modfolio-dropzone { border: 2px dashed #d0d0d0; border-radius: 12px; padding: 50px 40px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.3s; }
.modfolio-dropzone:hover, .modfolio-dropzone.dragover { border-color: #00c4aa; background: #f0fdfb; }
.modfolio-dropzone h3 { font-size: 18px; font-weight: 600; color: #1a1a1a; margin: 16px 0 8px; }
.modfolio-dropzone p { font-size: 14px; color: #666; margin: 0 0 20px; }
.modfolio-upload-btn { background: #00c4aa; color: #fff; border: none; border-radius: 25px; padding: 14px 32px; font-size: 15px; font-weight: 600; cursor: pointer; }
.modfolio-upload-btn:hover { background: #00b09a; }

/* Gallery Grid */
.modfolio-gallery-grid { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 24px; }
.gallery-thumb { position: relative; width: 150px; height: 180px; border-radius: 12px; overflow: hidden; border: 2px solid #e5e5e5; background: #f5f5f5; }
.gallery-thumb.featured { border-color: #00c4aa; }
.gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }

/* Thumb action buttons at bottom */
.gallery-thumb .thumb-actions { position: absolute; bottom: 8px; left: 8px; right: 8px; display: flex; justify-content: flex-end; gap: 6px; }
.gallery-thumb.featured .thumb-actions { justify-content: space-between; }
.gallery-thumb .thumb-btn { width: 28px; height: 28px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
.gallery-thumb .thumb-btn:hover { transform: scale(1.1); }
.gallery-thumb .featured-badge { background: #00c4aa; color: #fff; }
.gallery-thumb .remove-thumb { background: #00c4aa; color: #fff; }

/* ========== FORM ROWS ========== */
.modfolio-row { display: flex; gap: 24px; margin-bottom: 24px; }
.modfolio-field { flex: 1; min-width: 0; }
.modfolio-field.modfolio-full { flex: 1 1 100%; }
.modfolio-field.modfolio-title-field { flex: 0 0 35%; }

/* Default field (label above) */
.modfolio-field > label { display: block; font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 10px; padding-left: 12px; border-left: 3px solid #00c4aa; }

/* Inline field (label on left, input on right) - Figma style */
.modfolio-field.modfolio-field-inline { display: flex; align-items: stretch; background: #fff; border: 1px solid #e5e5e5; border-radius: 20px; overflow: hidden; min-height: 57px; box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1); }
.modfolio-field.modfolio-field-inline > label { display: flex !important; align-items: center; font-size: 14px; font-weight: 600; color: #1a1a1a; margin: 0; padding: 0 20px; border-left:none; background: #fff; white-space: nowrap; min-width: fit-content; position: relative; max-height: 57px; }
.modfolio-field.modfolio-field-inline > label::after { content: ''; position: absolute; right: 0; top: 12px; bottom: 12px; width: 1px; background: #e0e0e0; }
.modfolio-field.modfolio-field-inline input[type="text"],
.modfolio-field.modfolio-field-inline input[type="number"],
.modfolio-field.modfolio-field-inline select { flex: 1; border: none !important; border-radius: 0 !important; padding: 14px 16px; font-size: 14px; color: #666; background: #fff; transition: background 0.2s; box-sizing: border-box; outline: none; height: 40px; }
.modfolio-field.modfolio-field-inline input:focus,
.modfolio-field.modfolio-field-inline select:focus { background: #fafafa; }
.modfolio-field.modfolio-field-inline input::placeholder { color: #999; }
.modfolio-field.modfolio-field-inline select { color: #1a1a1a; cursor: pointer; }


.modfolio-field.modfolio-field-inline select{padding : 0 16px;}

/* Inline field with textarea */
.modfolio-field.modfolio-field-inline .textarea-wrap { flex: 1; position: relative; display: flex; }
.modfolio-field.modfolio-field-inline textarea { border: none !important; border-radius: 0 !important; padding: 14px 80px 14px 16px; font-size: 14px; color: #666; background: #fff; min-height: 80px; resize: vertical; width: 100%; box-sizing: border-box; outline: none; }
.modfolio-field.modfolio-field-inline textarea:focus { background: #fafafa; }
.modfolio-field.modfolio-field-inline textarea::placeholder { color: #999; }

/* Non-inline fields (keep original style) */
.modfolio-field:not(.modfolio-field-inline) input[type="text"],
.modfolio-field:not(.modfolio-field-inline) input[type="number"],
.modfolio-field:not(.modfolio-field-inline) textarea,
.modfolio-field:not(.modfolio-field-inline) select { width: 100%; border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px 16px; font-size: 15px; color: #1a1a1a; background: #fff; transition: border-color 0.2s; box-sizing: border-box; }
.modfolio-field:not(.modfolio-field-inline) input:focus,
.modfolio-field:not(.modfolio-field-inline) textarea:focus,
.modfolio-field:not(.modfolio-field-inline) select:focus { outline: none; border-color: #00c4aa; }
.modfolio-field:not(.modfolio-field-inline) input::placeholder,
.modfolio-field:not(.modfolio-field-inline) textarea::placeholder { color: #999; }

/* Category Field with Pills */
.modfolio-category-field .category-pills { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 16px; align-items: center; }
.pill { display: inline-flex; cursor: pointer; margin: 0; }
.pill input { display: none; }
.pill span { display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; background: #fff; border: 1px solid #e5e5e5; border-radius: 20px; font-size: 13px; font-weight: 500; color: #666; transition: all 0.2s; }
.pill input:checked + span { background: #00c4aa; border-color: #00c4aa; color: #fff; }
.pill:hover span { border-color: #00c4aa; }

/* AI button inside textarea wrap */
.ai-btn { position: absolute; right: 12px; top: 12px; background: transparent; border-left: 1px solid #e5e5e5; border-radius: 0px; padding: 6px 12px; font-size: 12px; font-weight: 500; color: #666; cursor: pointer; display: flex; align-items: center; gap: 4px; z-index: 1; }
/* .ai-btn:hover { border-color: #00c4aa; color: #00c4aa; background: #f0fdfb; } */
.ai-btn svg { width: 24px; height: 24px; }

/* ========== LICENSE FIELDS ========== */
.license-fields-group { margin-top: 0; margin-bottom: 24px; }
.license-fields-group .modfolio-row { margin-bottom: 24px; }

/* Make Product Free Checkbox */
.make-product-free-row { margin-bottom: 24px; }
.modfolio-checkbox-field {
    display: flex;
    align-items: center;
}
.modfolio-checkbox-field .checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 16px 24px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 20px;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
}
.modfolio-checkbox-field .checkbox-label:hover {
    border-color: #00c4aa;
}
.modfolio-checkbox-field input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    accent-color: #00c4aa;
    cursor: pointer;
}
.modfolio-checkbox-field .checkbox-text {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
}

/* ========== DOWNLOADS SECTION ========== */
.modfolio-downloads-section {
    margin: 32px 0;
    background: #fff;
    /* border: 1px solid #e5e5e5; */
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1)
}
.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e5e5;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
}

.modfolio-downloads-section .section-header {
    padding-bottom: 0 !important;
    border-bottom: none !important;
}

/* WCFM element visibility fix */
.modfolio-downloads-section .wcfm_ele_hide,
.modfolio-downloads-section .wcfm_downloadable_files,
.modfolio-downloads-section .multi_input_holder.wcfm_ele_hide {
    display: block !important;
    visibility: visible !important;
}

/* Labels styling */
.modfolio-downloads-section .wcfm_title {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #1a1a1a !important;
    margin-bottom: 8px !important;
    display: block !important;
}

/* Input fields */
.modfolio-downloads-section .wcfm-text {
    width: 100% !important;
    border: 1px solid #e5e5e5 !important;
    border-radius: 8px !important;
    padding: 12px 14px !important;
    font-size: 14px !important;
    color: #666 !important;
    background: #fff !important;
    margin-bottom: 12px !important;
}
.modfolio-downloads-section .wcfm-text:focus {
    border-color: #00c4aa !important;
    outline: none !important;
}

/* Multi input holder */
.modfolio-downloads-section .multi_input_holder {
    background: #f9fafb !important;
    border: 1px solid #e5e5e5 !important;
    border-radius: 20px !important;
    padding: 16px !important;
    margin-bottom: 16px !important;
    
    background: #fff !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}

/* Each file row */
.modfolio-downloads-section .multi_input_block {
    background: #fff !important;
    border: 1px solid #e5e5e5 !important;
    border-radius: 20px !important;
    padding: 16px !important;
    margin-bottom: 12px !important;

    border: none !important;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1) !important;
}
.modfolio-downloads-section .multi_input_block:last-of-type {
    margin-bottom: 0 !important;
}

/* Buttons */
.modfolio-downloads-section .add_multi_input_block {
    background: #00c4aa !important;
    color: #fff !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 10px 20px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    margin-top: 12px !important;
}
.modfolio-downloads-section .add_multi_input_block:hover {
    background: #00b09a !important;
}

.modfolio-downloads-section .upload_button,
.modfolio-downloads-section button.button-secondary {
    background: #00c4aa !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-size: 13px !important;
    cursor: pointer !important;
    display: inline-block !important;
}
.modfolio-downloads-section .upload_button:hover,
.modfolio-downloads-section button.button-secondary:hover {
    background: #00b09a !important;
}

/* Remove button */
.modfolio-downloads-section .remove_multi_input_block {
    background: #ff5252 !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    font-size: 12px !important;
    cursor: pointer !important;
}
.modfolio-downloads-section .remove_multi_input_block:hover {
    background: #e04545 !important;
}

/* Half width elements for limit/expiry */
.modfolio-downloads-section .wcfm_half_ele {
    display: inline-block !important;
    width: calc(50% - 8px) !important;
    margin-right: 16px !important;
    margin-bottom: 0 !important;
}
.modfolio-downloads-section .wcfm_half_ele_right {
    margin-right: 0 !important;
}
.modfolio-downloads-section .wcfm_half_ele_title {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #1a1a1a !important;
    margin-bottom: 8px !important;
    display: block !important;
}

/* ========== CUSTOM FIELDS SECTION ========== */
.modfolio-custom-fields-section { margin: 32px 0; padding: 24px; background: #f9fafb; border: 1px solid #e5e5e5; border-radius: 12px; }
.modfolio-custom-fields-section .wcfm_title { font-size: 14px !important; font-weight: 600 !important; color: #1a1a1a !important; margin-bottom: 10px !important; padding-left: 12px !important; border-left: 3px solid #00c4aa !important; display: block !important; }
.modfolio-custom-fields-section .wcfm-text, .modfolio-custom-fields-section .wcfm-select, .modfolio-custom-fields-section .wcfm-textarea { border: 1px solid #e0e0e0 !important; border-radius: 8px !important; padding: 12px 14px !important; font-size: 14px !important; width: 100% !important; background: #fff !important; }
.modfolio-custom-fields-section .wcfm-text:focus, .modfolio-custom-fields-section .wcfm-select:focus, .modfolio-custom-fields-section .wcfm-textarea:focus { outline: none !important; border-color: #00c4aa !important; }
.modfolio-custom-fields-section .wcfm_ele { margin-bottom: 16px !important; }
.modfolio-custom-fields-section .multi_input_holder { background: #fff; border: 1px solid #e5e5e5; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
.modfolio-custom-fields-section .multi_input_block { background: #f9fafb; border: 1px solid #e5e5e5; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
.modfolio-custom-fields-section .add_multi_input_block { background: #00c4aa !important; color: #fff !important; border: none !important; border-radius: 8px !important; padding: 10px 20px !important; font-size: 14px !important; cursor: pointer !important; }
.modfolio-custom-fields-section:empty { display: none; }

/* ========== SUBMIT ========== */
.modfolio-submit { margin-top: 40px; padding-top: 32px; border-top: 1px solid #e5e5e5; border: none; }
.modfolio-submit .wcfm-message { margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; }
.submit-buttons { display: flex; justify-content: flex-end; gap: 16px; }
.btn-primary { background: #00c4aa !important; color: #fff !important; border: none !important; border-radius: 30px !important; padding: 16px 40px !important; font-size: 15px !important; font-weight: 600 !important; cursor: pointer !important; box-shadow: 0 4px 12px rgba(0,196,170,0.25); }
.btn-primary:hover { background: #00b09a !important; }
.btn-secondary { background: #fff !important; color: #1a1a1a !important; border: 2px solid #e5e5e5 !important; border-radius: 30px !important; padding: 14px 40px !important; font-size: 15px !important; font-weight: 600 !important; cursor: pointer !important; }
.btn-secondary:hover { background: #f8f8f8 !important; border-color: #d0d0d0 !important; }

/* ========== PREVIEW MODAL ========== */
#previewModal { z-index: 999999 !important; }
#previewModal::before { content: ''; width: 100dvw; height: 100dvh; position: fixed; left: 0; top: 0; background: rgba(0,0,0,0.7); z-index: 50; }
#previewModal .modal-dialog { z-index: 1000000; }
#previewModal .modal-content { border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
#previewModal .modal-header { border-bottom: 1px solid #e5e5e5; padding: 20px 24px; }
#previewModal .modal-title { font-weight: 600; }
#previewModal .modal-body { padding: 24px; }
.modal-backdrop { z-index: 999998 !important; }
.modal-backdrop.show { z-index: 8 !important; display: none !important; }
.preview-gallery { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.preview-gallery img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; }
.preview-details h3 { font-size: 22px; font-weight: 600; margin-bottom: 12px; }
.preview-categories { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.preview-categories span { background: #00c4aa; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12px; }
.preview-description { color: #666; line-height: 1.6; margin-bottom: 16px; }
.preview-license { background: #f5f5f5; padding: 16px; border-radius: 10px; }
.preview-license p { margin: 4px 0; font-size: 14px; }



.wcfm_popup_form select, .wcfm_popup_form input[type="text"], #wcfm-main-contentainer input[type="password"], #wcfm-main-contentainer input[type="text"], #wcfm-main-contentainer select, #wcfm-main-contentainer input[type="number"], #wcfm-main-contentainer input[type="time"], #wcfm-main-contentainer input[type="search"], #wcfm-main-contentainer textarea {
    background-color: #fff !important;
    border: none !important;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    box-shadow: none;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1024px) {
    .modfolio-form-card { padding: 28px; }
    .modfolio-row { flex-direction: column; }
    .modfolio-field.modfolio-title-field { flex: 1; }
}
@media (max-width: 768px) {
    .modfolio-form-card { padding: 20px; border-radius: 12px; }
    .modfolio-dropzone { padding: 30px 20px; }
    .modfolio-dropzone h3 { font-size: 16px; }
    .gallery-thumb { width: 110px; height: 130px; }
    .submit-buttons { flex-direction: column; }
    .btn-primary, .btn-secondary { width: 100%; text-align: center; }
    .modfolio-field.modfolio-field-inline { flex-direction: column; align-items: stretch; }
    .modfolio-field.modfolio-field-inline > label { width: 100%; padding: 12px 16px; border-bottom: 1px solid #e5e5e5; }
    .modfolio-field.modfolio-field-inline > label::after { display: none; }
    .modfolio-field.modfolio-field-inline input,
    .modfolio-field.modfolio-field-inline textarea,
    .modfolio-field.modfolio-field-inline select { width: 100%; }
    .modfolio-category-field .category-pills { padding: 12px; }

    /* Downloads section responsive */
    .modfolio-downloads-section { padding: 16px; }
    .modfolio-downloads-section .wcfm_half_ele {
        display: block !important;
        width: 100% !important;
        margin-right: 0 !important;
        margin-bottom: 16px !important;
    }
}



/* ================== Additional Styling ================== */



#wcfm-main-contentainer input.wcfm_submit_button{
    margin-top: 0 !important;
}
.modfolio-downloads-section .wcfm_half_ele_title.download_limit,
.modfolio-downloads-section .wcfm_half_ele_title.download_expiry {
    display: none !important;
}

#wcfm-main-contentainer select#_license_package,
#wcfm-main-contentainer select{
    margin-top: auto;
    margin-bottom: auto;
}

.modfolio-field.modfolio-field-inline input{
    margin-top: auto;
    margin-bottom: auto;
}

</style>

<script>
jQuery(document).ready(function($) {
    // ========== GALLERY IMAGE LIMIT VALIDATION ==========
    var MIN_IMAGES = 3;
    var MAX_IMAGES = 5;

    function getTotalImageCount() {
        var featuredId = $('#featured_img').val();
        var galleryIds = $('#gallery_img_ids').val() ? $('#gallery_img_ids').val().split(',').filter(Boolean) : [];
        var total = galleryIds.length;
        if (featuredId && featuredId !== '') {
            total += 1;
        }
        return total;
    }

    function validateGalleryLimit() {
        var count = getTotalImageCount();
        var $submitBtn = $('#wcfm_products_simple_submit_button');
        var $validationMsg = $('#gallery-validation-msg');

        if (!$submitBtn.length || !$validationMsg.length) {
            return true; // Elements not found, skip validation
        }

        if (count < MIN_IMAGES) {
            $submitBtn.prop('disabled', true).css({
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
            $validationMsg.text('Please upload at least 3 images. Current count: ' + count).show();
            return false;
        } else if (count > MAX_IMAGES) {
            $submitBtn.prop('disabled', true).css({
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
            $validationMsg.text('Maximum 5 images allowed. Current count: ' + count + '. Please remove some images.').show();
            return false;
        } else {
            $submitBtn.prop('disabled', false).css({
                'opacity': '1',
                'cursor': 'pointer'
            });
            $validationMsg.hide();
            return true;
        }
    }

    // Run validation on page load (delayed to ensure DOM is ready)
    setTimeout(function() {
        validateGalleryLimit();
    }, 100);

    // WordPress Media Library for gallery upload
    var mediaUploader;

    function openMediaUploader() {
        var currentCount = getTotalImageCount();
        var remainingSlots = MAX_IMAGES - currentCount;

        if (remainingSlots <= 0) {
            alert('Maximum 5 images allowed. Please remove some images before adding more.');
            return;
        }

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: '<?php _e("Select Images", "wc-frontend-manager"); ?>',
            button: { text: '<?php _e("Add to Gallery", "wc-frontend-manager"); ?>' },
            multiple: true,
            library: { type: 'image' }
        });

        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            addImagesToGallery(attachments);
        });

        mediaUploader.open();
    }

    // Helper function to add images to gallery
    function addImagesToGallery(attachments) {
        var $preview = $('#gallery-preview');
        var featuredId = $('#featured_img').val();
        var galleryIds = $('#gallery_img_ids').val() ? $('#gallery_img_ids').val().split(',').filter(Boolean) : [];

        attachments.forEach(function(attachment, index) {
            var imgUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
            var imgId = attachment.id;

            // First image becomes featured if no featured exists
            if (!featuredId && index === 0) {
                featuredId = imgId;
                $('#featured_img').val(imgId);

                var html = '<div class="gallery-thumb featured" data-id="' + imgId + '">';
                html += '<img src="' + imgUrl + '" alt="">';
                html += '<div class="thumb-actions">';
                html += '<span class="thumb-btn featured-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></span>';
                html += '<span type="button" class="thumb-btn remove-thumb" data-type="featured"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></span>';
                html += '</div></div>';
                $preview.prepend(html);
            } else {
                // Add to gallery
                if (galleryIds.indexOf(String(imgId)) === -1) {
                    galleryIds.push(imgId);

                    var html = '<div class="gallery-thumb" data-id="' + imgId + '">';
                    html += '<img src="' + imgUrl + '" alt="">';
                    html += '<div class="thumb-actions">';
                    html += '<span type="button" class="thumb-btn remove-thumb" data-type="gallery"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></span>';
                    html += '</div></div>';
                    $preview.append(html);
                }
            }
        });

        $('#gallery_img_ids').val(galleryIds.join(','));

        // Validate after adding images
        validateGalleryLimit();
    }

    // Trigger upload on click
    $('#modfolio-dropzone, #trigger-gallery-upload').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openMediaUploader();
    });

    // Drag and drop functionality
    var $dropzone = $('#modfolio-dropzone');

    $dropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    $dropzone.on('dragleave dragend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    // Handle actual file drop
    $dropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');

        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadDroppedFiles(files);
        }
    });

    // Upload dropped files via AJAX
    function uploadDroppedFiles(files) {
        var formData = new FormData();
        var validFiles = 0;

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Check if it's an image
            if (file.type.match(/image\/(jpeg|jpg|png|gif|webp)/i)) {
                formData.append('async-upload', file);
                validFiles++;

                // Upload one file at a time
                var singleFormData = new FormData();
                singleFormData.append('async-upload', file);
                singleFormData.append('action', 'upload-attachment');
                singleFormData.append('_wpnonce', '<?php echo wp_create_nonce('media-form'); ?>');

                $.ajax({
                    url: '<?php echo admin_url('async-upload.php'); ?>',
                    type: 'POST',
                    data: singleFormData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response && response.success && response.data) {
                            var attachment = {
                                id: response.data.id,
                                url: response.data.url,
                                sizes: response.data.sizes || { medium: { url: response.data.url } }
                            };
                            addImagesToGallery([attachment]);
                        }
                    },
                    error: function() {
                        console.log('Upload failed');
                    }
                });
            }
        }

        if (validFiles === 0) {
            alert('<?php _e("Please drop valid image files (JPEG, PNG, GIF, WebP)", "wc-frontend-manager"); ?>');
        }
    }

    // Remove gallery thumb
    $(document).on('click', '.remove-thumb', function(e) {
        e.stopPropagation();
        var $thumb = $(this).closest('.gallery-thumb');
        var imgId = $thumb.data('id');
        var type = $(this).data('type');

        if (type === 'featured') {
            $('#featured_img').val('');
            // If there are gallery images, promote first one to featured
            var galleryIds = $('#gallery_img_ids').val() ? $('#gallery_img_ids').val().split(',').filter(Boolean) : [];
            if (galleryIds.length > 0) {
                var newFeaturedId = galleryIds.shift();
                $('#featured_img').val(newFeaturedId);
                $('#gallery_img_ids').val(galleryIds.join(','));
                // Update UI - find the thumb and update its structure
                var $newFeatured = $('.gallery-thumb[data-id="' + newFeaturedId + '"]');
                $newFeatured.addClass('featured');
                $newFeatured.find('.remove-thumb').attr('data-type', 'featured');
                // Add featured badge to thumb-actions
                $newFeatured.find('.thumb-actions').prepend('<span class="thumb-btn featured-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></span>');
            }
        } else {
            var galleryIds = $('#gallery_img_ids').val() ? $('#gallery_img_ids').val().split(',').filter(Boolean) : [];
            galleryIds = galleryIds.filter(function(id) { return id != imgId; });
            $('#gallery_img_ids').val(galleryIds.join(','));
        }

        $thumb.remove();

        // Validate after removing images
        validateGalleryLimit();
    });

    // Copy excerpt to description
    $('#excerpt').on('input change', function() {
        $('#description').val($(this).val());
    });

    // License package toggle (select dropdown)
    $('#_license_package').on('change', function() {
        var v = $(this).val();
        if (v === 'standard') {
            $('.license-group-standard').show();
            $('.license-group-exclusive').hide();
        } else if (v === 'exclusive') {
            $('.license-group-standard').hide();
            $('.license-group-exclusive').show();
        }
    });

    // Is Exclusive Product toggle - show/hide "Make Product Free" checkbox
    $('#_is_exclusive_product').on('change', function() {
        var v = $(this).val();
        if (v === 'yes') {
            $('#make-product-free-section').show();
        } else {
            $('#make-product-free-section').hide();
            // Uncheck the checkbox when hiding
            $('#_make_product_free').prop('checked', false);
        }
    });

    // Preview Modal
    $('#preview-btn').on('click', function() {
        // Title
        $('#preview-title').text($('#pro_title').val() || 'Untitled');

        // Description
        $('#preview-description').text($('#excerpt').val() || 'No description');

        // Categories
        var cats = [];
        $('input[name="product_cats[]"]:checked').each(function() {
            cats.push($(this).next('span').text());
        });
        $('#preview-categories').html(cats.map(function(c) { return '<span>' + c + '</span>'; }).join(''));

        // Gallery
        var gallery = '';
        $('#gallery-preview .gallery-thumb img').each(function() {
            gallery += '<img src="' + $(this).attr('src') + '" alt="">';
        });
        $('#preview-gallery').html(gallery || '<p>No images uploaded</p>');

        // License (enabled if price > 0)
        var license = '';
        var pkg = $('#_license_package').val();
        if (pkg === 'standard') {
            license = '<p><strong>Package:</strong> Standard</p>';
            var personalPrice = parseFloat($('input[name="_product_license_personal_price"]').val()) || 0;
            var commercialPrice = parseFloat($('input[name="_product_license_commercial_price"]').val()) || 0;
            if (personalPrice > 0) {
                license += '<p>Individual: $' + personalPrice.toFixed(2) + '</p>';
            }
            if (commercialPrice > 0) {
                license += '<p>Commercial: $' + commercialPrice.toFixed(2) + '</p>';
            }
        } else if (pkg === 'exclusive') {
            license = '<p><strong>Package:</strong> Exclusive</p>';
            var exclusivePrice = parseFloat($('input[name="_product_license_exclusive_price"]').val()) || 0;
            if (exclusivePrice > 0) {
                license += '<p>Exclusive: $' + exclusivePrice.toFixed(2) + '</p>';
            }
        } else {
            license = '<p>No license selected</p>';
        }
        $('#preview-license').html(license);

        // Show modal using Bootstrap 5
        var modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    });

    // Form submission - ensure WCFM handles it
    $('#wcfm_products_manage_form').on('submit', function(e) {
        // Let WCFM handle the submission
        // The form already has the correct ID and class for WCFM
    });
});
</script>
