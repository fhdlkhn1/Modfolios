<?php
if ( ! defined('ABSPATH') ) exit;

$vendor_id = 0;

// WCFMMP store pages typically set vendor id via global or query
// Try common patterns:
if ( function_exists('wcfmmp_get_store') ) {
  // Not always available depending on version; best effort
}
if ( isset($GLOBALS['WCFMmp']) && function_exists('wcfmmp_get_store_id') ) {
  $vendor_id = (int) wcfmmp_get_store_id();
}
if ( ! $vendor_id && isset($_GET['store_id']) ) {
  $vendor_id = (int) $_GET['store_id'];
}

// Fallback: WCFM sets store user in query var (store slug); resolve vendor ID from store slug is theme/plugin dependent.
// If vendor_id is not resolved by above, at least avoid fatal.
if ( $vendor_id <= 0 ) {
  echo '<p>' . esc_html__('Unable to resolve store.', 'wc-multivendor-marketplace') . '</p>';
  return;
}

$enabled = (get_user_meta($vendor_id, 'wcfmmp_profile_settings', true)['wss_store_subscriptions_enabled'] ?? 'no') === 'yes';
if ( ! $enabled ) {
  echo '<p>' . esc_html__('Subscriptions are not enabled for this store.', 'wc-multivendor-marketplace') . '</p>';
  return;
}

$basic_id = (int) get_user_meta($vendor_id, 'wss_basic_product_id', true);
$elite_id = (int) get_user_meta($vendor_id, 'wss_elite_product_id', true);

$basic = $basic_id ? wc_get_product($basic_id) : null;
$elite = $elite_id ? wc_get_product($elite_id) : null;

if ( ! $basic && ! $elite ) {
  echo '<p>' . esc_html__('No subscription plans found.', 'wc-multivendor-marketplace') . '</p>';
  return;
}

$user_id = get_current_user_id();
$has_active = false;
if ( $user_id && function_exists('wcs_get_users_subscriptions') ) {
  $subs = wcs_get_users_subscriptions($user_id);
  foreach ($subs as $s) {
    if ( ! is_a($s, 'WC_Subscription') ) continue;
    if ( ! in_array($s->get_status(), ['active','pending-cancel'], true) ) continue;
    foreach ($s->get_items() as $it) {
      $pid = (int)$it->get_product_id();
      if ( $pid === $basic_id || $pid === $elite_id ) { $has_active = true; break 2; }
    }
  }
}

?>
<div class="wss-wrap">
  <h3 class="wss-title"><?php echo esc_html__('Fan Subscription', 'wc-multivendor-marketplace'); ?></h3>

  <div class="wss-grid">
    <?php foreach (['basic' => $basic, 'elite' => $elite] as $key => $prod): ?>
      <?php if ( ! $prod ) continue; ?>
      <?php
        $pid = $prod->get_id();
        $price = $prod->get_price();
        $discount = (int) get_post_meta($pid, '_customer_discount', true);
        $name = $prod->get_name();
        $add_url = esc_url( add_query_arg('add-to-cart', $pid, wc_get_cart_url()) );
      ?>
      <div class="wss-card">
        <div class="wss-card-head">
          <div class="wss-plan"><?php echo esc_html($name); ?></div>
          <div class="wss-price">
            <?php echo wp_kses_post( wc_price($price) ); ?>
            <span class="wss-per">/month</span>
          </div>
        </div>

        <div class="wss-benefits">
          <div class="wss-benefits-title"><?php echo esc_html__('Automatic Benefits', 'wc-multivendor-marketplace'); ?></div>
          <ul>
            <li><?php echo esc_html($discount); ?>% <?php echo esc_html__('Discount on all purchases from this store', 'wc-multivendor-marketplace'); ?></li>
            <?php if ($key === 'elite'): ?>
              <li><?php echo esc_html__('Exclusive Content Access (Photos/Videos)', 'wc-multivendor-marketplace'); ?></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="wss-actions">
          <?php if ( $has_active ): ?>
            <a class="wss-btn wss-btn-secondary" href="<?php echo esc_url( wc_get_account_endpoint_url('creator-subscriptions') ); ?>">
              <?php echo esc_html__('Manage', 'wc-multivendor-marketplace'); ?>
            </a>
          <?php else: ?>
            <a class="wss-btn" href="<?php echo $add_url; ?>">
              <?php echo esc_html__('Subscribe', 'wc-multivendor-marketplace'); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
