/**
 * Modfolios Wallet System - Checkout Enhancement
 *
 * Wallet payment is now handled as a standard WooCommerce payment gateway.
 * This script provides minor UX enhancements on the checkout page.
 */
jQuery(function ($) {

  // When wallet is selected but balance is insufficient, disable Place Order button
  function checkWalletAvailability() {
    var $insufficient = $('.modfolios-wallet-checkout-info .modfolios-wallet-insufficient, .modfolios-wallet-checkout-info div[style*="fff3cd"]');
    var $selected = $('input[name="payment_method"]:checked');

    if ($selected.val() === 'modfolios_wallet' && $insufficient.length) {
      $('#place_order').prop('disabled', true).css('opacity', '0.5');
    } else {
      $('#place_order').prop('disabled', false).css('opacity', '');
    }
  }

  // Run on payment method change
  $(document.body).on('change', 'input[name="payment_method"]', checkWalletAvailability);

  // Run after WooCommerce updates the checkout (e.g. coupon applied, address changed)
  $(document.body).on('updated_checkout', checkWalletAvailability);

  // Initial check
  $(document).ready(function () {
    setTimeout(checkWalletAvailability, 500);
  });

});
