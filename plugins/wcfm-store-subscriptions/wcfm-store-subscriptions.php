<?php
/**
 * Plugin Name: WCFM Store Subscriptions (Per-Vendor)
 * Description: Per-vendor (store) subscription plans using WooCommerce Subscriptions + WCFM Marketplace store tab + vendor-only discounts.
 * Version: 1.0.0
 * Author: Fahad Ali Khan
 * Requires Plugins: woocommerce, wc-multivendor-marketplace, woocommerce-subscriptions
 */

if ( ! defined('ABSPATH') ) exit;

final class WCFM_Store_Subscriptions {

  const META_IS_STORE_SUB = '_is_store_subscription';
  const META_DISCOUNT     = '_customer_discount';
  const META_VENDOR_ID    = '_store_vendor_id';

  const VENDOR_KEY_BASIC_PRODUCT = 'wss_basic_product_id';
  const VENDOR_KEY_ELITE_PRODUCT = 'wss_elite_product_id';

  const SETTINGS_KEY_ENABLED = 'wss_store_subscriptions_enabled';
  const SETTINGS_KEY_BASIC_PRICE = 'wss_basic_price';
  const SETTINGS_KEY_ELITE_PRICE = 'wss_elite_price';

  const DEFAULT_BASIC_PRICE = 9;
  const DEFAULT_ELITE_PRICE = 19;
  const DEFAULT_DISCOUNT    = 10;

  const STORE_TAB_SLUG = 'membership-plans';
  const MYACC_ENDPOINT = 'creator-subscriptions';

  public static function instance() {
    static $inst = null;
    if ( $inst === null ) $inst = new self();
    return $inst;
  }

  private function __construct() {
    register_activation_hook(__FILE__,  [$this, 'on_activate']);
    register_deactivation_hook(__FILE__,[$this, 'on_deactivate']);

    add_action('plugins_loaded', [$this, 'init'], 20);
  }

  public function init() {
    // Basic dependency checks
    if ( ! class_exists('WooCommerce') ) return;
    if ( ! function_exists('wcs_get_users_subscriptions') ) {
      // WooCommerce Subscriptions not active
      return;
    }

    // Create subscription products on vendor creation (best-effort, role-based)
    add_action('user_register', [$this, 'maybe_create_vendor_store_subscriptions'], 30, 1);

    // Add fields to WCFMMP Store settings (saved in wcfmmp_profile_settings)
    add_filter('wcfm_marketplace_settings_fields_general', [$this, 'add_vendor_settings_fields'], 50, 2);

    // Save fields into wcfmmp_profile_settings as WCFMMP does (same pattern used by WC Lovers support)
    add_action('wcfm_wcfmmp_settings_update', [$this, 'save_vendor_settings_fields'], 30, 2);
    add_action('wcfm_vendor_settings_update', [$this, 'save_vendor_settings_fields'], 30, 2);

    // Add Store Page "Subscription" tab (WCFM store page tabs are separate links)
    add_action('wcfmmp_rewrite_rules_loaded', [$this, 'register_store_tab_rewrite_rules'], 50, 1);
    add_filter('query_vars', [$this, 'register_query_vars'], 50);
    add_filter('wcfmmp_store_tabs', [$this, 'add_store_tab'], 90, 2);
    add_filter('wcfmp_store_tabs_url', [$this, 'store_tab_url'], 50, 2);
    add_filter('wcfmp_store_default_query_vars', [$this, 'store_default_query_vars'], 50);
    add_filter('wcfmmp_store_default_template', [$this, 'store_tab_template'], 50, 2);

    // Add Store Page "Live Media Kit" tab
    add_action('wcfmmp_rewrite_rules_loaded', [$this, 'register_media_kit_rewrite_rules'], 51, 1);
    add_filter('query_vars', [$this, 'register_media_kit_query_vars'], 51);
    add_filter('wcfmmp_store_tabs', [$this, 'add_media_kit_store_tab'], 91, 2);
    add_filter('wcfmp_store_tabs_url', [$this, 'media_kit_tab_url'], 51, 2);
    add_filter('wcfmp_store_default_query_vars', [$this, 'media_kit_default_query_vars'], 51);
    add_filter('wcfmmp_store_default_template', [$this, 'media_kit_tab_template'], 51, 2);

    // Add Store Page "Rate Card" tab
    add_action('wcfmmp_rewrite_rules_loaded', [$this, 'register_rate_card_rewrite_rules'], 52, 1);
    add_filter('query_vars', [$this, 'register_rate_card_query_vars'], 52);
    add_filter('wcfmmp_store_tabs', [$this, 'add_rate_card_store_tab'], 92, 2);
    add_filter('wcfmp_store_tabs_url', [$this, 'rate_card_tab_url'], 52, 2);
    add_filter('wcfmp_store_default_query_vars', [$this, 'rate_card_default_query_vars'], 52);
    add_filter('wcfmmp_store_default_template', [$this, 'rate_card_tab_template'], 52, 2);

    // Discount logic (vendor-scoped) — show as a "Subscribe & Save" fee line
    add_action('woocommerce_cart_calculate_fees', [$this, 'apply_vendor_subscription_discounts'], 20, 1);

    // My Account endpoint
    add_action('init', [$this, 'register_myaccount_endpoint']);
    add_filter('woocommerce_account_menu_items', [$this, 'add_myaccount_menu_item']);
    add_action('woocommerce_account_' . self::MYACC_ENDPOINT . '_endpoint', [$this, 'render_myaccount_endpoint']);

    // Assets
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

    // WCFM Dashboard Products Page Customization
    add_action('wcfm_products_before_sidebars', [$this, 'add_products_welcome_header']);
    add_action('after_wcfm_products', [$this, 'add_products_page_styles']);
  }

  public function on_activate() {
    $this->register_myaccount_endpoint();
    flush_rewrite_rules();
  }

  public function on_deactivate() {
    flush_rewrite_rules();
  }

  /* ---------------------------
   * Helpers: vendor settings storage (wcfmmp_profile_settings)
   * --------------------------- */

  private function get_vendor_profile_settings( $vendor_id ) {
    $data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
    return is_array($data) ? $data : [];
  }

  private function update_vendor_profile_settings( $vendor_id, array $new_data ) {
    $existing = $this->get_vendor_profile_settings($vendor_id);
    $merged = array_merge($existing, $new_data);
    update_user_meta($vendor_id, 'wcfmmp_profile_settings', $merged);
    return $merged;
  }

  private function vendor_subscriptions_enabled( $vendor_id ) {
    $enabled = get_user_meta($vendor_id, '_wss_subscriptions_enabled', true);
    return $enabled === 'yes';
  }

  private function vendor_basic_price( $vendor_id ) {
    $settings = $this->get_vendor_profile_settings($vendor_id);
    $val = $settings[self::SETTINGS_KEY_BASIC_PRICE] ?? self::DEFAULT_BASIC_PRICE;
    $val = is_numeric($val) ? (float)$val : self::DEFAULT_BASIC_PRICE;
    return max(0, $val);
  }

  private function vendor_elite_price( $vendor_id ) {
    $settings = $this->get_vendor_profile_settings($vendor_id);
    $val = $settings[self::SETTINGS_KEY_ELITE_PRICE] ?? self::DEFAULT_ELITE_PRICE;
    $val = is_numeric($val) ? (float)$val : self::DEFAULT_ELITE_PRICE;
    return max(0, $val);
  }

  /* ---------------------------
   * Create 2 subscription products per vendor
   * --------------------------- */

  public function maybe_create_vendor_store_subscriptions( $user_id ) {
    $user = get_user_by('id', $user_id);
    if ( ! $user ) return;

    // Adjust this check if you use a different vendor role/capability
    $is_vendor = in_array('wcfm_vendor', (array)$user->roles, true)
              || in_array('vendor', (array)$user->roles, true)
              || user_can($user_id, 'wcfm_vendor')
              || user_can($user_id, 'vendor');

    if ( ! $is_vendor ) return;

    $this->ensure_vendor_subscription_products($user_id);
  }

  private function ensure_vendor_subscription_products( $vendor_id ) {
    $basic_id = (int) get_user_meta($vendor_id, self::VENDOR_KEY_BASIC_PRODUCT, true);
    $elite_id = (int) get_user_meta($vendor_id, self::VENDOR_KEY_ELITE_PRODUCT, true);

    if ( $basic_id && get_post($basic_id) ) {
      // ok
    } else {
      $basic_id = $this->create_simple_subscription_product($vendor_id, 'Basic Subscription', self::DEFAULT_BASIC_PRICE, self::DEFAULT_DISCOUNT);
      if ($basic_id) update_user_meta($vendor_id, self::VENDOR_KEY_BASIC_PRODUCT, $basic_id);
    }

    if ( $elite_id && get_post($elite_id) ) {
      // ok
    } else {
      $elite_id = $this->create_simple_subscription_product($vendor_id, 'Elite Subscription', self::DEFAULT_ELITE_PRICE, self::DEFAULT_DISCOUNT);
      if ($elite_id) update_user_meta($vendor_id, self::VENDOR_KEY_ELITE_PRODUCT, $elite_id);
    }

    return [$basic_id, $elite_id];
  }

  private function create_simple_subscription_product($vendor_id, $title, $monthly_price, $discount_percent) {
    // Woo Subscriptions product type uses 'subscription' post type = product, product_type = subscription
    $product_id = wp_insert_post([
      'post_title'   => $title,
      'post_status'  => 'publish',
      'post_type'    => 'product',
      'post_author'  => $vendor_id,
    ]);

    if ( is_wp_error($product_id) || ! $product_id ) return 0;

    // Mark as subscription product type
    wp_set_object_terms($product_id, 'subscription', 'product_type');

    // Catalog visibility: hidden so it doesn't pollute shop/search
    update_post_meta($product_id, '_visibility', 'hidden');

    // Core WCS metas (common keys)
    update_post_meta($product_id, '_subscription_price', wc_format_decimal($monthly_price));
    update_post_meta($product_id, '_subscription_period', 'month');
    update_post_meta($product_id, '_subscription_period_interval', '1');
    update_post_meta($product_id, '_subscription_length', '0'); // 0 = indefinite
    update_post_meta($product_id, '_subscription_trial_length', '0');
    update_post_meta($product_id, '_subscription_trial_period', 'day');
    update_post_meta($product_id, '_subscription_sign_up_fee', '0');

    // Your metas
    update_post_meta($product_id, self::META_IS_STORE_SUB, 'yes');
    update_post_meta($product_id, self::META_DISCOUNT, (string)intval($discount_percent));
    update_post_meta($product_id, self::META_VENDOR_ID, (string)intval($vendor_id));

    // Also keep Woo regular price aligned (helps some themes)
    update_post_meta($product_id, '_regular_price', wc_format_decimal($monthly_price));
    update_post_meta($product_id, '_price', wc_format_decimal($monthly_price));

    return (int)$product_id;
  }

  private function update_subscription_product_price($product_id, $new_price) {
    $new_price = is_numeric($new_price) ? (float)$new_price : 0;
    $new_price = max(0, $new_price);

    update_post_meta($product_id, '_subscription_price', wc_format_decimal($new_price));
    update_post_meta($product_id, '_regular_price', wc_format_decimal($new_price));
    update_post_meta($product_id, '_price', wc_format_decimal($new_price));
  }

  /* ---------------------------
   * WCFM Store Settings fields
   * --------------------------- */

  public function add_vendor_settings_fields($fields, $user_id) {
    $vendor_data = $this->get_vendor_profile_settings($user_id);

    $enabled = $vendor_data[self::SETTINGS_KEY_ENABLED] ?? 'no';
    $basic_price = $vendor_data[self::SETTINGS_KEY_BASIC_PRICE] ?? self::DEFAULT_BASIC_PRICE;
    $elite_price = $vendor_data[self::SETTINGS_KEY_ELITE_PRICE] ?? self::DEFAULT_ELITE_PRICE;

    // Add fields near top (like WC Lovers example using array_slice) :contentReference[oaicite:2]{index=2}
    $insertion = [
      self::SETTINGS_KEY_ENABLED => [
        'label'       => __('Store Subscriptions', 'wc-multivendor-marketplace'),
        'type'        => 'checkbox',
        'class'       => 'wcfm-checkbox wcfm_ele',
        'label_class' => 'wcfm_title wcfm_ele',
        'value'       => 'yes',
        'dfvalue'     => $enabled,
        'hints'       => __('Enable store-level subscriptions for your customers.', 'wc-multivendor-marketplace'),
      ],
      self::SETTINGS_KEY_BASIC_PRICE => [
        'label'       => __('Basic Subscription Price (Monthly)', 'wc-multivendor-marketplace'),
        'type'        => 'number',
        'class'       => 'wcfm-text wcfm_ele',
        'label_class' => 'wcfm_title wcfm_ele',
        'value'       => $basic_price,
        'attributes'  => ['step' => '0.01', 'min' => '0'],
      ],
      self::SETTINGS_KEY_ELITE_PRICE => [
        'label'       => __('Elite Subscription Price (Monthly)', 'wc-multivendor-marketplace'),
        'type'        => 'number',
        'class'       => 'wcfm-text wcfm_ele',
        'label_class' => 'wcfm_title wcfm_ele',
        'value'       => $elite_price,
        'attributes'  => ['step' => '0.01', 'min' => '0'],
      ],
    ];

    // Put after store name block if present; otherwise append
    if ( isset($fields['store_name']) ) {
      $fields = array_slice($fields, 0, 3, true) + $insertion + array_slice($fields, 3, null, true);
    } else {
      $fields = $fields + $insertion;
    }

    return $fields;
  }

  public function save_vendor_settings_fields($user_id, $wcfm_settings_form) {
    // WCFM sends serialized form in POST[wcfm_settings_form] like in WC Lovers snippet :contentReference[oaicite:3]{index=3}
    $posted = [];
    if ( isset($_POST['wcfm_settings_form']) ) {
      parse_str($_POST['wcfm_settings_form'], $posted);
    } elseif ( is_array($wcfm_settings_form) ) {
      $posted = $wcfm_settings_form;
    }

    $enabled = (isset($posted[self::SETTINGS_KEY_ENABLED]) && $posted[self::SETTINGS_KEY_ENABLED] === 'yes') ? 'yes' : 'no';
    $basic_price = isset($posted[self::SETTINGS_KEY_BASIC_PRICE]) ? $posted[self::SETTINGS_KEY_BASIC_PRICE] : self::DEFAULT_BASIC_PRICE;
    $elite_price = isset($posted[self::SETTINGS_KEY_ELITE_PRICE]) ? $posted[self::SETTINGS_KEY_ELITE_PRICE] : self::DEFAULT_ELITE_PRICE;

    $basic_price = is_numeric($basic_price) ? (float)$basic_price : self::DEFAULT_BASIC_PRICE;
    $elite_price = is_numeric($elite_price) ? (float)$elite_price : self::DEFAULT_ELITE_PRICE;

    $new_data = [
      self::SETTINGS_KEY_ENABLED => $enabled,
      self::SETTINGS_KEY_BASIC_PRICE => $basic_price,
      self::SETTINGS_KEY_ELITE_PRICE => $elite_price,
    ];

    $this->update_vendor_profile_settings($user_id, $new_data);

    // Ensure products exist and update prices
    [$basic_id, $elite_id] = $this->ensure_vendor_subscription_products($user_id);

    if ( $basic_id ) $this->update_subscription_product_price($basic_id, $basic_price);
    if ( $elite_id ) $this->update_subscription_product_price($elite_id, $elite_price);

    // If disabled, hide products from catalog
    if ( $enabled !== 'yes' ) {
      if ( $basic_id ) wp_update_post(['ID' => $basic_id, 'post_status' => 'draft']);
      if ( $elite_id ) wp_update_post(['ID' => $elite_id, 'post_status' => 'draft']);
    } else {
      if ( $basic_id ) wp_update_post(['ID' => $basic_id, 'post_status' => 'publish']);
      if ( $elite_id ) wp_update_post(['ID' => $elite_id, 'post_status' => 'publish']);
    }
  }

 /* ---------------------------
 * Store page tab (WCFMMP) - FIXED
 * --------------------------- */

public function register_query_vars($vars) {
    $vars[] = 'membership-plans';
    return $vars;
}

public function add_store_tab($store_tabs, $vendor_id) {
    if ( $this->vendor_subscriptions_enabled($vendor_id) ) {
        $store_tabs['membership-plans'] = __('Subscriptions', 'wc-multivendor-marketplace');
    }
    return $store_tabs;
}

public function register_store_tab_rewrite_rules($wcfm_store_url) {
    // /store/vendor-slug/membership-plans/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/membership-plans/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&membership-plans=true',
        'top'
    );

    // /store/vendor-slug/membership-plans/page/2/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/membership-plans/page/?([0-9]{1,})/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&paged=$matches[2]&membership-plans=true',
        'top'
    );
}

public function store_tab_url($store_tab_url, $tab) {
    if ( $tab === 'membership-plans' ) {
        $store_tab_url .= 'membership-plans/';
    }
    return $store_tab_url;
}

public function store_default_query_vars($query_var) {
    if ( get_query_var('membership-plans') ) {
        $query_var = 'membership-plans';
    }
    return $query_var;
}

// public function store_tab_template($template, $tab) {
//     if ( $tab === 'subscriptions' ) {
//         $plugin_template = plugin_dir_path(__FILE__) . 'templates/store-tab-subscriptions.php';
        
//         if ( file_exists($plugin_template) ) {
//             return $plugin_template;
//         }
//     }
//     return $template;
// }

public function store_tab_template($template, $tab) {
    if ( $tab === 'membership-plans' ) {
        // Return RELATIVE path just like your exclusive tab does
        $template = 'store/wcfmmp-view-store-subscriptions.php';
    }
    return $template;
}


/* ---------------------------
 * Live Media Kit Store Tab
 * --------------------------- */

public function register_media_kit_query_vars($vars) {
    $vars[] = 'live-media-kit';
    return $vars;
}

public function add_media_kit_store_tab($store_tabs, $vendor_id) {
    // Only show if vendor has active Pro or Elite membership subscription
    // Pro => 21878, Elite => 21879 (Basic => 2187 is excluded)
    if ( $this->vendor_has_pro_or_elite_membership( $vendor_id ) ) {
        $store_tabs['live-media-kit'] = __('Live Media Kit', 'wc-multivendor-marketplace');
    }
    return $store_tabs;
}

/**
 * Check if vendor has active Pro or Elite membership subscription
 * Uses WCFM membership user meta
 *
 * @param int $vendor_id
 * @return bool
 */
private function vendor_has_pro_or_elite_membership( $vendor_id ) {
    if ( ! $vendor_id ) {
        return false;
    }

    // Membership IDs - Pro and Elite only (not Basic)
    // Basic => 2187, Pro => 21878, Elite => 21879
    $allowed_membership_ids = array( 21878, 21879 ); // Pro, Elite

    // Check WCFM membership status
    $subscription_status = get_user_meta( $vendor_id, 'wcfm_subscription_status', true );
    if ( $subscription_status !== 'active' ) {
        return false;
    }

    // Check WCFM membership ID
    $membership_id = get_user_meta( $vendor_id, 'wcfm_membership', true );
    $membership_id = (int) $membership_id;

    if ( in_array( $membership_id, $allowed_membership_ids, true ) ) {
        return true;
    }

    return false;
}

public function register_media_kit_rewrite_rules($wcfm_store_url) {
    // /store/vendor-slug/live-media-kit/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/live-media-kit/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&live-media-kit=true',
        'top'
    );

    // /store/vendor-slug/live-media-kit/page/2/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/live-media-kit/page/?([0-9]{1,})/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&paged=$matches[2]&live-media-kit=true',
        'top'
    );
}

public function media_kit_tab_url($store_tab_url, $tab) {
    if ( $tab === 'live-media-kit' ) {
        $store_tab_url .= 'live-media-kit/';
    }
    return $store_tab_url;
}

public function media_kit_default_query_vars($query_var) {
    if ( get_query_var('live-media-kit') ) {
        $query_var = 'live-media-kit';
    }
    return $query_var;
}

public function media_kit_tab_template($template, $tab) {
    if ( $tab === 'live-media-kit' ) {
        $template = 'store/wcfmmp-view-store-media-kit.php';
    }
    return $template;
}


/* ---------------------------
 * Rate Card Store Tab
 * --------------------------- */

public function register_rate_card_query_vars($vars) {
    $vars[] = 'rate-card';
    return $vars;
}

public function add_rate_card_store_tab($store_tabs, $vendor_id) {
    // Check if vendor has any rate card services
    $services = get_user_meta($vendor_id, '_vendor_rate_card_services', true);
    if ( !empty($services) && is_array($services) ) {
        $store_tabs['rate-card'] = __('Rate Card', 'wc-multivendor-marketplace');
    }
    return $store_tabs;
}

public function register_rate_card_rewrite_rules($wcfm_store_url) {
    // /store/vendor-slug/rate-card/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/rate-card/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&rate-card=true',
        'top'
    );

    // /store/vendor-slug/rate-card/page/2/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/rate-card/page/?([0-9]{1,})/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&paged=$matches[2]&rate-card=true',
        'top'
    );
}

public function rate_card_tab_url($store_tab_url, $tab) {
    if ( $tab === 'rate-card' ) {
        $store_tab_url .= 'rate-card/';
    }
    return $store_tab_url;
}

public function rate_card_default_query_vars($query_var) {
    if ( get_query_var('rate-card') ) {
        $query_var = 'rate-card';
    }
    return $query_var;
}

public function rate_card_tab_template($template, $tab) {
    if ( $tab === 'rate-card' ) {
        $template = 'store/wcfmmp-view-store-rate-card.php';
    }
    return $template;
}


  /* ---------------------------
   * Subscription status + discount resolution
   * --------------------------- */

  private function get_product_vendor_id($product_id) {
    $vendor_id = (int) get_post_field('post_author', $product_id);
    if ( $vendor_id > 0 ) return $vendor_id;

    $meta_vendor = (int) get_post_meta($product_id, self::META_VENDOR_ID, true);
    return $meta_vendor > 0 ? $meta_vendor : 0;
  }

  private function get_vendor_subscription_product_ids($vendor_id) {
    $basic_id = (int) get_user_meta($vendor_id, self::VENDOR_KEY_BASIC_PRODUCT, true);
    $elite_id = (int) get_user_meta($vendor_id, self::VENDOR_KEY_ELITE_PRODUCT, true);
    return [$basic_id, $elite_id];
  }

  private function user_discount_for_vendor($user_id, $vendor_id) {
    static $cache = [];
    $key = $user_id . ':' . $vendor_id;
    if ( isset($cache[$key]) ) return $cache[$key];

    [$basic_id, $elite_id] = $this->get_vendor_subscription_product_ids($vendor_id);
    $product_ids = array_filter([(int)$basic_id, (int)$elite_id]);

    if ( empty($product_ids) ) return $cache[$key] = 0;

    $subs = wcs_get_users_subscriptions($user_id);
    if ( empty($subs) ) return $cache[$key] = 0;

    $best_discount = 0;

    foreach ($subs as $subscription) {
      if ( ! is_a($subscription, 'WC_Subscription') ) continue;

      $status = $subscription->get_status();
      if ( ! in_array($status, ['active', 'pending-cancel'], true) ) continue;

      foreach ($subscription->get_items() as $item) {
        $pid = (int) $item->get_product_id();
        if ( in_array($pid, $product_ids, true) ) {
          $d = (int) get_post_meta($pid, self::META_DISCOUNT, true);
          $best_discount = max($best_discount, $d);
        }
      }
    }

    return $cache[$key] = max(0, min(100, $best_discount));
  }

  /* ---------------------------
   * Apply vendor-only discount in cart
   * --------------------------- */

  public function apply_vendor_subscription_discounts($cart) {
    if ( is_admin() && ! defined('DOING_AJAX') ) return;
    if ( ! is_user_logged_in() ) return;
    if ( ! $cart || ! is_a($cart, 'WC_Cart') ) return;

    $user_id = get_current_user_id();
    $total_discount = 0;

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
      if ( empty($cart_item['data']) || ! is_a($cart_item['data'], 'WC_Product') ) continue;

      $product = $cart_item['data'];
      $product_id = (int) $product->get_id();

      // Skip if the cart item itself is a store subscription product
      if ( get_post_meta($product_id, self::META_IS_STORE_SUB, true) === 'yes' ) continue;

      $vendor_id = $this->get_product_vendor_id($product_id);
      if ( $vendor_id <= 0 ) continue;

      $discount = $this->user_discount_for_vendor($user_id, $vendor_id);
      if ( $discount <= 0 ) continue;

      $base_price = (float) $product->get_price();
      $quantity   = (int) $cart_item['quantity'];
      $savings    = $base_price * ($discount / 100) * $quantity;
      $total_discount += $savings;
    }

    if ( $total_discount > 0 ) {
      $cart->add_fee(
        __('Subscribe & Save', 'wc-multivendor-marketplace'),
        -1 * wc_format_decimal($total_discount),
        false // not taxable
      );
    }
  }

  /* ---------------------------
   * My Account: Creator Subscriptions
   * --------------------------- */

  public function register_myaccount_endpoint() {
    add_rewrite_endpoint(self::MYACC_ENDPOINT, EP_ROOT | EP_PAGES);
  }

  public function add_myaccount_menu_item($items) {
    // Insert near top (after Dashboard)
    $new = [];
    foreach ($items as $key => $label) {
      $new[$key] = $label;
      if ($key === 'dashboard') {
        $new[self::MYACC_ENDPOINT] = __('Creator Subscriptions', 'wc-multivendor-marketplace');
      }
    }
    if ( ! isset($new[self::MYACC_ENDPOINT]) ) {
      $new[self::MYACC_ENDPOINT] = __('Creator Subscriptions', 'wc-multivendor-marketplace');
    }
    return $new;
  }

  public function render_myaccount_endpoint() {
    $template = plugin_dir_path(__FILE__) . 'templates/myaccount-creator-subscriptions.php';
    if ( file_exists($template) ) {
      include $template;
      return;
    }
    echo '<p>' . esc_html__('Template missing.', 'wc-multivendor-marketplace') . '</p>';
  }

  /* ---------------------------
   * Assets
   * --------------------------- */

  public function enqueue_assets() {
    if ( is_account_page() || get_query_var(self::STORE_TAB_SLUG) ) {
      wp_enqueue_style('wss-store-subscriptions', plugins_url('assets/store-subscriptions.css', __FILE__), [], '1.0.0');
    }
  }
}

WCFM_Store_Subscriptions::instance();


/**
 * Helper function: Check if user has active paid subscription for a vendor
 * Uses WooCommerce Subscriptions to verify subscription status
 *
 * @param int $user_id User ID to check
 * @param int $vendor_id Vendor ID to check against
 * @return bool True if user has active paid subscription
 */
function wcfm_store_sub_is_subscriber( $user_id, $vendor_id ) {
    if ( ! $user_id || ! $vendor_id ) {
        return false;
    }

    // Check WooCommerce Subscriptions (paid subscriptions only)
    if ( ! function_exists( 'wcs_get_users_subscriptions' ) ) {
        return false; // WC Subscriptions plugin not active
    }

    $basic_id = (int) get_user_meta( $vendor_id, 'wss_basic_product_id', true );
    $elite_id = (int) get_user_meta( $vendor_id, 'wss_elite_product_id', true );
    $product_ids = array_filter( [ $basic_id, $elite_id ] );

    if ( empty( $product_ids ) ) {
        return false; // Vendor has no subscription products set up
    }

    $subscriptions = wcs_get_users_subscriptions( $user_id );

    foreach ( $subscriptions as $subscription ) {
        if ( ! is_a( $subscription, 'WC_Subscription' ) ) {
            continue;
        }

        $status = $subscription->get_status();
        if ( ! in_array( $status, [ 'active', 'pending-cancel' ], true ) ) {
            continue;
        }

        foreach ( $subscription->get_items() as $item ) {
            $pid = (int) $item->get_product_id();
            if ( in_array( $pid, $product_ids, true ) ) {
                return true; // User has active paid subscription
            }
        }
    }

    return false;
}


/* code from the snippet */


/**
 * Add "Exclusive product" dropdown to WCFM product manage (Pricing tab)
 */
add_filter( 'wcfm_product_manage_fields_pricing', 'is_wcfm_add_exclusive_product_field', 10, 2 );
function is_wcfm_add_exclusive_product_field( $fields, $product_id ) {

    // Get saved meta (default = 'no')
    $meta_exclusive = $product_id ? get_post_meta( $product_id, '_is_exclusive_product', true ) : '';
    if ( $meta_exclusive !== 'yes' && $meta_exclusive !== 'no' ) {
        $meta_exclusive = 'no';
    }

    // Our field definition as dropdown
    $fields['_is_exclusive_product'] = array(
        'label'       => __( 'Exclusive product', 'your-textdomain' ),
        'name'        => '_is_exclusive_product',
        'type'        => 'select',
        'class'       => 'wcfm-select',
        'label_class' => 'wcfm_title',
        'options'     => array(
            'no'  => __( 'No', 'your-textdomain' ),
            'yes' => __( 'Yes', 'your-textdomain' ),
        ),
        'dfvalue'     => $meta_exclusive, // 'yes' or 'no'
        'hints'       => __( 'If set to "Yes", this product will appear under the vendor\'s ExclusiveTab for subscribers.', 'your-textdomain' ),
    );

    return $fields;
}


/**
 * Save "Exclusive product" from WCFM product manage form
 */
add_action( 'after_wcfm_products_manage_meta_save', 'is_wcfm_save_exclusive_product_field', 10, 2 );
function is_wcfm_save_exclusive_product_field( $product_id, $form_data ) {

    // Expect value 'yes' or 'no' from select
    $is_exclusive = isset( $form_data['_is_exclusive_product'] ) ? $form_data['_is_exclusive_product'] : 'no';

    // Normalize just in case
    $is_exclusive = ( $is_exclusive === 'yes' ) ? 'yes' : 'no';

    update_post_meta( $product_id, '_is_exclusive_product', $is_exclusive );
}



/************************************************************
 * 2) EXCLUSIVE STORE TAB – WCFM
 ************************************************************/

/**
 * Register "exclusive" as a public query var
 * (THIS was the missing piece)
 */
add_filter( 'query_vars', 'is_exclusive_query_vars', 50 );
function is_exclusive_query_vars( $vars ) {
    $vars[] = 'exclusive';
    return $vars;
}

/**
 * Add "Premium" tab to WCFM store page - always visible
 */
add_filter( 'wcfmmp_store_tabs', 'is_store_tabs_add_exclusive_tab', 50, 2 );
function is_store_tabs_add_exclusive_tab( $store_tabs, $store_id ) {

    // Insert "Premium" tab right after "products" tab - always visible
    $new_tabs = array();

    foreach ( $store_tabs as $key => $label ) {
        $new_tabs[ $key ] = $label;

        if ( 'products' === $key ) {

            // Count vendor exclusive products
            $exclusive_count = new WP_Query( array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'author'         => $store_id,
                'meta_query'     => array(
                    array(
                        'key'   => '_is_exclusive_product',
                        'value' => 'yes',
                    ),
                ),
            ));

            // Add the tab - renamed to Premium
            $new_tabs['exclusive'] = sprintf(
                __( 'Premium (%d)', 'your-textdomain' ),
                $exclusive_count->found_posts
            );
        }
    }

    return $new_tabs;
}



/**
 * Add rewrite rules for "Exclusive" store tab URLs
 */
add_action( 'wcfmmp_rewrite_rules_loaded', 'is_register_exclusive_store_tab_rewrites', 50 );
function is_register_exclusive_store_tab_rewrites( $wcfm_store_url ) {

    // /store/vendor-name/exclusive/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/exclusive/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&exclusive=true',
        'top'
    );

    // /store/vendor-name/exclusive/page/2/
    add_rewrite_rule(
        $wcfm_store_url . '/([^/]+)/exclusive/page/?([0-9]{1,})/?$',
        'index.php?' . $wcfm_store_url . '=$matches[1]&paged=$matches[2]&exclusive=true',
        'top'
    );
}

/**
 * Build proper tab URL for "Exclusive"
 */
add_filter( 'wcfmp_store_tabs_url', 'is_store_tabs_url_exclusive', 50, 2 );
function is_store_tabs_url_exclusive( $store_tab_url, $tab ) {
    if ( 'exclusive' === $tab ) {
        $store_tab_url .= 'exclusive/';
    }
    return $store_tab_url;
}

/**
 * Tell WCFM which tab is active when ?exclusive=1 is set
 */
add_filter( 'wcfmp_store_default_query_vars', 'is_store_default_query_vars_exclusive', 50 );
function is_store_default_query_vars_exclusive( $query_var ) {
    if ( get_query_var( 'exclusive' ) ) {
        $query_var = 'exclusive';
    }
    return $query_var;
}

/**
 * Point the "Exclusive" tab to our custom template file
 */
add_filter( 'wcfmmp_store_default_template', 'is_store_default_template_exclusive', 50, 2 );
function is_store_default_template_exclusive( $template, $tab ) {
    if ( 'exclusive' === $tab ) {
        $template = 'store/wcfmmp-view-store-exclusive.php';
    }
    return $template;
}














// ================= add count on products tabs ==========

/**
 * Add non-exclusive product count to the "Products" tab label
 */
add_filter( 'wcfmmp_store_tabs', 'is_store_tabs_add_non_exclusive_count', 40, 2 );
function is_store_tabs_add_non_exclusive_count( $store_tabs, $store_id ) {

    // Count NON-exclusive products for this vendor
    $query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'author'         => $store_id,
        'meta_query'     => array(
            'relation' => 'OR',
            // Product does NOT have the meta key
            array(
                'key'     => '_is_exclusive_product',
                'compare' => 'NOT EXISTS',
            ),
            // OR Product meta exists and is set to 'no'
            array(
                'key'     => '_is_exclusive_product',
                'value'   => 'no',
                'compare' => '=',
            ),
        ),
    ));

    $count = $query->found_posts;

    // Replace "products" tab label with count
    if ( isset( $store_tabs['products'] ) ) {
        $store_tabs['products'] = sprintf(
            __( 'Products (%d)', 'your-textdomain' ),
            $count
        );
    }

    return $store_tabs;
}



// ========== exclude exclusive product from products tab ==========

/**
 * Exclude exclusive products from WCFM store products query
 */
add_filter( 'wcfmmp_store_product_query_args', 'is_exclude_exclusive_from_wcfm_query', 20, 2 );
function is_exclude_exclusive_from_wcfm_query( $args, $store_id ) {
    // Only modify the main products tab query
    $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'products';
    if ( $current_tab === 'exclusive' ) {
        return $args;
    }
    
    // Add meta query to exclude exclusive products (same logic as your count function)
    if ( ! isset( $args['meta_query'] ) ) {
        $args['meta_query'] = array();
    }
    
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key'     => '_is_exclusive_product',
            'compare' => 'NOT EXISTS',
        ),
        array(
            'key'     => '_is_exclusive_product',
            'value'   => 'yes',
            'compare' => '!=',
        ),
    );
    
    return $args;
}

add_action( 'woocommerce_after_add_to_cart_button', 'my_live_views_subscribe_box', 20 );
function my_live_views_subscribe_box() {
    if ( ! is_product() ) {
        return;
    }

    global $product;

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $product_id = $product->get_id();

    // Initial random views between 20–50
    $initial_views = rand( 20, 50 );
		
    ?>

		
		
    <div class="live-views-subscribe-box" id="live-views-subscribe-box">
		<div class="lvs-row">
			<span class="lvs-icon">
				<!-- <i class="fas fa-eye"></i> -->
		<svg width="27" height="17" viewBox="0 0 27 17" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M26.3127 7.92395C26.0762 7.60034 20.4395 0 13.2404 0C6.04131 0 0.404349 7.60034 0.168036 7.92364C0.0588424 8.07324 0 8.25366 0 8.43886C0 8.62407 0.0588424 8.80449 0.168036 8.95408C0.404349 9.2777 6.04131 16.878 13.2404 16.878C20.4395 16.878 26.0762 9.27765 26.3127 8.95434C26.4221 8.80482 26.481 8.62438 26.481 8.43915C26.481 8.25391 26.4221 8.07348 26.3127 7.92395ZM13.2404 15.132C7.9375 15.132 3.34463 10.0875 1.98504 8.43842C3.34287 6.78785 7.92612 1.74599 13.2404 1.74599C18.543 1.74599 23.1356 6.78961 24.4957 8.43961C23.1379 10.0901 18.5547 15.132 13.2404 15.132Z" fill="black"/>
<path d="M13.2419 3.20117C10.3537 3.20117 8.00391 5.55101 8.00391 8.43921C8.00391 11.3274 10.3537 13.6772 13.2419 13.6772C16.1301 13.6772 18.48 11.3274 18.48 8.43921C18.48 5.55101 16.1301 3.20117 13.2419 3.20117ZM13.2419 11.9312C11.3164 11.9312 9.74995 10.3647 9.74995 8.43921C9.74995 6.51369 11.3164 4.94722 13.2419 4.94722C15.1675 4.94722 16.7339 6.51369 16.7339 8.43921C16.7339 10.3647 15.1675 11.9312 13.2419 11.9312Z" fill="black"/>
</svg>

			</span>
			<span class="lvs-text">
				<span id="lvs-live-views"><?php echo esc_html( $initial_views ); ?></span>
				(People are viewing this right now)
			</span>
		</div>

		<hr class="lvs-divider" />

		<div class="lvs-row">
			<span class="lvs-icon">
				<!--<i class="fas fa-lock"></i>-->
		<svg width="26" height="20" viewBox="0 0 26 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M24.3576 12.6101C23.9239 12.6101 23.5719 12.924 23.5719 13.3107V18.2148H1.57146V9.8078H10.2145C10.6482 9.8078 11.0002 9.49394 11.0002 9.10722C11.0002 8.72049 10.6482 8.40663 10.2145 8.40663H1.57146V5.60429H10.2145C10.6482 5.60429 11.0002 5.29043 11.0002 4.90371C11.0002 4.51699 10.6482 4.20312 10.2145 4.20312H1.57146C0.704014 4.20312 0 4.83085 0 5.60429V18.2148C0 18.9883 0.704014 19.616 1.57146 19.616H23.5719C24.4393 19.616 25.1434 18.9883 25.1434 18.2148V13.3107C25.1434 12.924 24.7913 12.6101 24.3576 12.6101Z" fill="black"/>
<path d="M7.07123 12.6102H3.92831C3.49459 12.6102 3.14258 12.924 3.14258 13.3108C3.14258 13.6975 3.49459 14.0113 3.92831 14.0113H7.07123C7.50495 14.0113 7.85696 13.6975 7.85696 13.3108C7.85696 12.924 7.50495 12.6102 7.07123 12.6102ZM24.6669 2.15745L19.1668 0.0556951C19.0685 0.0189336 18.963 0 18.8564 0C18.7498 0 18.6443 0.0189336 18.546 0.0556951L13.0459 2.15745C12.9049 2.2119 12.7849 2.30193 12.7006 2.41646C12.6162 2.53098 12.5713 2.66499 12.5713 2.80199V5.60433C12.5713 9.45894 14.1695 11.712 18.4659 13.9189C18.5869 13.9805 18.722 14.0113 18.8572 14.0113C18.9923 14.0113 19.1275 13.9805 19.2485 13.9189C23.5448 11.7176 25.143 9.46455 25.143 5.60433V2.80199C25.143 2.52175 24.956 2.26814 24.6669 2.15745ZM23.5716 5.60433C23.5716 8.83963 22.371 10.6205 18.8572 12.4981C15.3434 10.6163 14.1428 8.83542 14.1428 5.60433V3.26437L18.8572 1.46247L23.5716 3.26437V5.60433Z" fill="black"/>
<path d="M21.7063 4.35594C21.3684 4.11774 20.875 4.16398 20.6015 4.46523L18.1312 7.21993L17.1538 5.91685C16.9102 5.59458 16.4215 5.50911 16.0647 5.72208C15.7049 5.93646 15.6059 6.37223 15.8463 6.69309L17.4178 8.79485C17.5576 8.9812 17.7871 9.0961 18.0385 9.10731H18.0715C18.3088 9.10731 18.5351 9.01203 18.6859 8.84389L21.8288 5.34096C22.0991 5.03831 22.0457 4.59834 21.7063 4.35594Z" fill="black"/>
</svg>

			</span>
			<span class="lvs-text">Secure access to files after purchase</span>
		</div>

		<hr class="lvs-divider" />

		<div class="lvs-row">
			<span class="lvs-icon">
				<!--<i class="fas fa-shopping-cart"></i>-->
		<svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M0.837209 0C0.615167 0 0.40222 0.0786473 0.245213 0.21864C0.0882057 0.358634 0 0.548505 0 0.746485C0 0.944465 0.0882057 1.13434 0.245213 1.27433C0.40222 1.41432 0.615167 1.49297 0.837209 1.49297V0ZM5.30232 3.4836H18.6977V1.99063H5.30232V3.4836ZM22.3256 6.71837V11.6949H24V6.71837H22.3256ZM18.6977 14.9297H9.76743V16.4227H18.6977V14.9297ZM6.13953 11.6949V2.73711H4.46511V11.6949H6.13953ZM3.06977 0H0.837209V1.49297H3.06977V0ZM6.13953 2.73711C6.13953 2.01118 5.81611 1.31499 5.24042 0.801682C4.66472 0.288374 3.88392 0 3.06977 0V1.49297C3.84 1.49297 4.46511 2.05035 4.46511 2.73711H6.13953ZM9.76743 14.9297C8.80525 14.9297 7.88248 14.5889 7.20212 13.9823C6.52175 13.3756 6.13953 12.5528 6.13953 11.6949H4.46511C4.46511 12.9488 5.02375 14.1513 6.01813 15.0379C7.01251 15.9246 8.36117 16.4227 9.76743 16.4227V14.9297ZM22.3256 11.6949C22.3256 12.5528 21.9433 13.3756 21.263 13.9823C20.5826 14.5889 19.6598 14.9297 18.6977 14.9297V16.4227C20.1039 16.4227 21.4526 15.9246 22.447 15.0379C23.4413 14.1513 24 12.9488 24 11.6949H22.3256ZM18.6977 3.4836C19.6598 3.4836 20.5826 3.8244 21.263 4.43104C21.9433 5.03768 22.3256 5.86045 22.3256 6.71837H24C24 5.46449 23.4413 4.26197 22.447 3.37535C21.4526 2.48873 20.1039 1.99063 18.6977 1.99063V3.4836Z" fill="black"/>
<path d="M4.47628 3.85485C4.51269 4.05019 4.63464 4.22463 4.81531 4.3398C4.99597 4.45497 5.22054 4.50143 5.43962 4.46896C5.65871 4.43649 5.85435 4.32775 5.98351 4.16667C6.11268 4.00558 6.16478 3.80534 6.12837 3.61L4.47628 3.85485ZM0.837209 0C0.615167 0 0.40222 0.0786473 0.245213 0.21864C0.0882057 0.358634 0 0.548505 0 0.746485C0 0.944465 0.0882057 1.13434 0.245213 1.27433C0.40222 1.41432 0.615167 1.49297 0.837209 1.49297V0ZM6.12837 3.61L5.72651 1.45515L4.07442 1.70099L4.47628 3.85485L6.12837 3.61ZM3.79869 0H0.837209V1.49297H3.79869V0ZM5.72651 1.45515C5.65042 1.04853 5.41394 0.679161 5.06115 0.412718C4.70837 0.146274 4.26103 3.27539e-05 3.79869 0V1.49297C3.86466 1.49311 3.92843 1.51409 3.9787 1.55217C4.02897 1.59025 4.0636 1.64297 4.07442 1.70099L5.72651 1.45515ZM10.0465 19.1598C10.0465 19.3578 9.9583 19.5476 9.80129 19.6876C9.64428 19.8276 9.43134 19.9063 9.2093 19.9063V21.3992C9.87542 21.3992 10.5143 21.1633 10.9853 20.7433C11.4563 20.3233 11.7209 19.7537 11.7209 19.1598H10.0465ZM9.2093 19.9063C8.98725 19.9063 8.77431 19.8276 8.6173 19.6876C8.46029 19.5476 8.37209 19.3578 8.37209 19.1598H6.69767C6.69767 19.7537 6.96229 20.3233 7.43331 20.7433C7.90433 21.1633 8.54317 21.3992 9.2093 21.3992V19.9063ZM8.37209 19.1598C8.37209 18.9618 8.46029 18.7719 8.6173 18.6319C8.77431 18.4919 8.98725 18.4133 9.2093 18.4133V16.9203C8.54317 16.9203 7.90433 17.1563 7.43331 17.5763C6.96229 17.9962 6.69767 18.5658 6.69767 19.1598H8.37209ZM9.2093 18.4133C9.43134 18.4133 9.64428 18.4919 9.80129 18.6319C9.9583 18.7719 10.0465 18.9618 10.0465 19.1598H11.7209C11.7209 18.5658 11.4563 17.9962 10.9853 17.5763C10.5143 17.1563 9.87542 16.9203 9.2093 16.9203V18.4133ZM20.093 19.1598C20.093 19.3578 20.0048 19.5476 19.8478 19.6876C19.6908 19.8276 19.4778 19.9063 19.2558 19.9063V21.3992C19.9219 21.3992 20.5608 21.1633 21.0318 20.7433C21.5028 20.3233 21.7674 19.7537 21.7674 19.1598H20.093ZM19.2558 19.9063C19.0338 19.9063 18.8208 19.8276 18.6638 19.6876C18.5068 19.5476 18.4186 19.3578 18.4186 19.1598H16.7442C16.7442 19.7537 17.0088 20.3233 17.4798 20.7433C17.9508 21.1633 18.5897 21.3992 19.2558 21.3992V19.9063ZM18.4186 19.1598C18.4186 18.9618 18.5068 18.7719 18.6638 18.6319C18.8208 18.4919 19.0338 18.4133 19.2558 18.4133V16.9203C18.5897 16.9203 17.9508 17.1563 17.4798 17.5763C17.0088 17.9962 16.7442 18.5658 16.7442 19.1598H18.4186ZM19.2558 18.4133C19.4778 18.4133 19.6908 18.4919 19.8478 18.6319C20.0048 18.7719 20.093 18.9618 20.093 19.1598H21.7674C21.7674 18.5658 21.5028 17.9962 21.0318 17.5763C20.5608 17.1563 19.9219 16.9203 19.2558 16.9203V18.4133ZM17.0567 11.2271C17.139 11.1588 17.205 11.0764 17.2507 10.9848C17.2965 10.8932 17.3211 10.7944 17.3231 10.6942C17.3251 10.5939 17.3044 10.4944 17.2623 10.4014C17.2202 10.3085 17.1575 10.224 17.078 10.1532C16.9985 10.0823 16.9038 10.0264 16.7995 9.98884C16.6953 9.9513 16.5836 9.93286 16.4712 9.93463C16.3588 9.9364 16.2479 9.95834 16.1452 9.99914C16.0426 10.0399 15.9501 10.0988 15.8735 10.1721L17.0567 11.2271ZM12.5916 10.1721C12.515 10.0988 12.4225 10.0399 12.3198 9.99914C12.2172 9.95834 12.1063 9.9364 11.9939 9.93463C11.8815 9.93286 11.7698 9.9513 11.6656 9.98884C11.5613 10.0264 11.4666 10.0823 11.3871 10.1532C11.3076 10.224 11.2449 10.3085 11.2028 10.4014C11.1607 10.4944 11.14 10.5939 11.142 10.6942C11.144 10.7944 11.1686 10.8932 11.2144 10.9848C11.2601 11.0764 11.3261 11.1588 11.4084 11.2271L12.5916 10.1721ZM15.0698 6.71837C15.0698 6.52039 14.9816 6.33051 14.8245 6.19052C14.6675 6.05053 14.4546 5.97188 14.2325 5.97188C14.0105 5.97188 13.7976 6.05053 13.6406 6.19052C13.4835 6.33051 13.3953 6.52039 13.3953 6.71837H15.0698ZM13.3953 11.6949C13.3953 11.8929 13.4835 12.0828 13.6406 12.2228C13.7976 12.3628 14.0105 12.4414 14.2325 12.4414C14.4546 12.4414 14.6675 12.3628 14.8245 12.2228C14.9816 12.0828 15.0698 11.8929 15.0698 11.6949H13.3953ZM15.8735 10.1721L14.4301 11.459L15.6134 12.5141L17.0567 11.2271L15.8735 10.1721ZM14.035 11.459L12.5916 10.1721L11.4084 11.2271L12.8517 12.5141L14.035 11.459ZM14.4301 11.459C14.4042 11.4822 14.3734 11.5006 14.3395 11.5131C14.3056 11.5257 14.2693 11.5321 14.2325 11.5321C14.1958 11.5321 14.1595 11.5257 14.1256 11.5131C14.0917 11.5006 14.0609 11.4822 14.035 11.459L12.8517 12.5141C13.218 12.8405 13.7147 13.0238 14.2325 13.0238C14.7504 13.0238 15.2471 12.8405 15.6134 12.5141L14.4301 11.459ZM13.3953 6.71837V11.6949H15.0698V6.71837H13.3953Z" fill="black"/>
</svg>

			</span>
			<span class="lvs-text">Add to cart and download instantly</span>
		</div>

		
		
		
		<?php

		// Get vendor ID from WCFM
		$vendor_id = 0;
		// $vendor_id = apply_filters( 'wcfm_product_author', $product_id );
		if ( function_exists( 'wcfm_get_vendor_id_by_post' ) ) {
			$vendor_id = (int) wcfm_get_vendor_id_by_post( $product_id );
		}

		if ( ! $vendor_id ) {
			echo '</div>';
			return; // No vendor mapped
		}

		// Check if user has active paid subscription
		$is_subscriber = false;
		if ( is_user_logged_in() ) {
			$is_subscriber = wcfm_store_sub_is_subscriber( get_current_user_id(), $vendor_id );
		}

		// Get vendor store URL for membership-plans tab
		$store_url = '';
		if ( function_exists( 'wcfmmp_get_store_url' ) ) {
			$store_url = wcfmmp_get_store_url( $vendor_id );
		}
		$membership_plans_url = trailingslashit( $store_url ) . 'membership-plans/';

		// --- OUTPUT SECTION --- ?>

		<?php if ( $is_subscriber ) : ?>

			<div class="lvs-save-more">
				<div class="lvs-heading">Subscribed to Creator!</div>
				<div class="lvs-subheading">
					You now enjoy a 10% discount on all of this creator&rsquo;s products.
				</div>
			</div>

		<?php else : ?>

			<div class="lvs-save-more">
				<div class="lvs-heading">Want to save more?</div>
				<div class="lvs-subheading">
					Subscribe now and get 10% off on this and all future purchases
				</div>
			</div>

			<!-- Subscribe button - redirects to membership plans tab -->
			<a href="<?php echo esc_url( $membership_plans_url ); ?>" class="lvs-subscribe-btn">
				Subscribe
			</a>

		<?php endif; ?>

		</div>

		<?php

}





// ====== script and style ====


add_action( 'wp_footer', 'my_live_views_subscribe_assets' );
function my_live_views_subscribe_assets() {
    if ( ! is_product() ) {
        return;
    }
    ?>
    
	
	<style>
        .live-views-subscribe-box {
            text-align: left;
            max-width: 480px;
        }

        .lvs-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 14px;
        }

        .lvs-icon {
            font-size: 18px;
            flex-shrink: 0;
        }
        .lvs-icon svg{
            width: 22px;
        }
        .lvs-text{
            color: #000;
        }

        .lvs-divider {
            border: none;
            border-top: 1px solid #ececec;
            margin: 6px 0;
        }

        .lvs-save-more {
            text-align: center;
            margin: 18px 0 10px;
        }

        .lvs-heading {
            color: #00c4aa;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 4px;
        }

        .lvs-subheading {
            font-size: 16px;
            line-height: 1.4;
            color: #000;
        }

        .lvs-subscribe-btn {
            display: block;
            width: 100%;
            margin-top: 10px;
            padding: 14px 20px;
            border-radius: 999px;
            border: none;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            background: #00c4aa;
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .lvs-subscribe-btn:hover {
            background: #00b39b;
            color: #ffffff;
            text-decoration: none;
        }

        .lvs-subscribe-btn:active {
            transform: translateY(1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }
    </style>


    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function () {
                // Live views counter update
                var min = 20;
                var max = 50;
                var viewsEl = document.getElementById('lvs-live-views');

                if (viewsEl) {
                    function updateViews() {
                        var value = Math.floor(Math.random() * (max - min + 1)) + min;
                        viewsEl.textContent = value;
                    }
                    setInterval(updateViews, 60000); // every 60 seconds
                }

                // Subscribe button now links directly to membership-plans tab
                // No AJAX needed - just redirect (handled by <a> tag)
            });
        })();
    </script>
    <?php
}


// ========= bottom of product detail page ==========

/**
 * Show Elementor template at the end of single product page (before footer)
 */
add_action( 'woocommerce_after_single_product', 'waves_show_elementor_template_end', 20 );
function waves_show_elementor_template_end() {

    if ( ! is_product() ) {
        return;
    }

    echo do_shortcode( '[elementor-template id="23252"]' );
}


