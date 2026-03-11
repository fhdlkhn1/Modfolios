<?php
if ( ! defined('ABSPATH') ) exit;

if ( ! is_user_logged_in() ) {
  echo '<p>' . esc_html__('Please login.', 'wc-multivendor-marketplace') . '</p>';
  return;
}

$user_id = get_current_user_id();

// Handle cancel request
if ( isset($_GET['wss_cancel_sub']) ) {
  $sub_id = absint($_GET['wss_cancel_sub']);
  $sub = wcs_get_subscription($sub_id);

  if ( $sub && (int)$sub->get_user_id() === $user_id ) {
    // Cancel (WCS may treat as "cancel at period end" depending on settings)
    $sub->update_status('cancelled', __('User cancelled store subscription', 'wc-multivendor-marketplace'), true);
    wc_print_notice(__('Subscription cancelled.', 'wc-multivendor-marketplace'), 'success');
  } else {
    wc_print_notice(__('Invalid subscription.', 'wc-multivendor-marketplace'), 'error');
  }
}

$subs = wcs_get_users_subscriptions($user_id);

$rows = [];

foreach ($subs as $sub) {
  if ( ! is_a($sub, 'WC_Subscription') ) continue;

  foreach ($sub->get_items() as $item) {
    $pid = (int)$item->get_product_id();
    if ( get_post_meta($pid, '_is_store_subscription', true) !== 'yes' ) continue;

    $vendor_id = (int) get_post_meta($pid, '_store_vendor_id', true);
    if ( $vendor_id <= 0 ) $vendor_id = (int) get_post_field('post_author', $pid);

    $store_name = get_the_author_meta('display_name', $vendor_id);
    $plan_name = get_the_title($pid);

    // Store URL: WCFM store URL building differs by setup; best effort:
    $store_url = function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url($vendor_id) : home_url('/');

    $rows[] = [
      'sub_id' => $sub->get_id(),
      'status' => $sub->get_status(),
      'renewal' => $sub->get_date_to_display('next_payment'),
      'store_name' => $store_name,
      'store_url' => $store_url,
      'plan_name' => $plan_name,
    ];
  }
}

?>

<div class="creator__subscription_page">
	
<h3><?php echo esc_html__('Creator Subscriptions', 'wc-multivendor-marketplace'); ?></h3>

<?php if (empty($rows)): ?>
  <p><?php echo esc_html__('You have no store subscriptions.', 'wc-multivendor-marketplace'); ?></p>
<?php else: ?>
  <table class="shop_table shop_table_responsive my_account_orders">
    <thead>
      <tr>
        <th><?php echo esc_html__('Store', 'wc-multivendor-marketplace'); ?></th>
        <th><?php echo esc_html__('Plan', 'wc-multivendor-marketplace'); ?></th>
        <th><?php echo esc_html__('Status', 'wc-multivendor-marketplace'); ?></th>
        <th><?php echo esc_html__('Next Payment', 'wc-multivendor-marketplace'); ?></th>
        <th><?php echo esc_html__('Actions', 'wc-multivendor-marketplace'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td data-title="Store"><?php echo esc_html($r['store_name']); ?></td>
          <td data-title="Plan"><?php echo esc_html($r['plan_name']); ?></td>
          <td data-title="Status"><?php echo esc_html($r['status']); ?></td>
          <td data-title="Next Payment"><?php echo esc_html($r['renewal'] ?: '-'); ?></td>
          <td data-title="Actions">
            <a class="button" href="<?php echo esc_url($r['store_url']); ?>">
              <?php echo esc_html__('Visit Store', 'wc-multivendor-marketplace'); ?>
            </a>

            <?php if ( in_array($r['status'], ['active','pending-cancel'], true) ): ?>
              <a class="button" href="<?php echo esc_url( add_query_arg('wss_cancel_sub', $r['sub_id'], wc_get_account_endpoint_url('creator-subscriptions')) ); ?>">
                <?php echo esc_html__('Unsubscribe', 'wc-multivendor-marketplace'); ?>
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

</div>