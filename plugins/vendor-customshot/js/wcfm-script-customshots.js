jQuery(document).ready(function($) {

    // Debug: Log that script is loaded
    console.log('WCFM Customshots JS loaded');

    // Decline custom shot from list (using event delegation for dynamic content)
    $(document).on('click', '.decline-customshot', function(e) {
        e.preventDefault();
        var $this = $(this);
        var shotId = $this.data('shot-id');

        if (!confirm('Are you sure you want to decline this custom shot request?')) {
            return;
        }

        $.ajax({
            url: wcfm_customshots_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcfm_ajax_controller',
                controller: 'wcfm-customshots',
                action_type: 'decline',
                shot_id: shotId
            },
            beforeSend: function() {
                $this.addClass('loading').prop('disabled', true);
            },
            success: function(response) {
                $this.removeClass('loading').prop('disabled', false);
                if (response.success) {
                    // Update the card
                    var $card = $this.closest('.customshot-card');
                    $card.find('.customshot-status')
                        .removeClass('status-pending')
                        .addClass('status-declined')
                        .text('Declined');
                    $card.find('.action-accept, .action-decline').remove();

                    alert(response.data.message);

                    // Optionally reload the page
                    location.reload();
                } else {
                    alert(response.data.message || 'Error declining request');
                }
            },
            error: function() {
                $this.removeClass('loading').prop('disabled', false);
                alert('Error processing request');
            }
        });
    });

    // Decline from detail page (using event delegation, unbind first to prevent duplicates)
    $(document).off('click', '#decline-btn').on('click', '#decline-btn', function(e) {
        e.preventDefault();
        var $this = $(this);
        var shotId = $this.data('shot-id');

        if (!confirm('Are you sure you want to decline this custom shot request?')) {
            return;
        }

        $.ajax({
            url: wcfm_customshots_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcfm_ajax_controller',
                controller: 'wcfm-customshots',
                action_type: 'decline',
                shot_id: shotId
            },
            beforeSend: function() {
                $this.addClass('loading').prop('disabled', true);
            },
            success: function(response) {
                $this.removeClass('loading').prop('disabled', false);
                if (response.success) {
                    alert(response.data.message);
                    // Redirect back to list
                    window.location.href = window.location.href.split('?')[0];
                } else {
                    alert(response.data.message || 'Error declining request');
                }
            },
            error: function() {
                $this.removeClass('loading').prop('disabled', false);
                alert('Error processing request');
            }
        });
    });

    // Send quote (using event delegation, unbind first to prevent duplicates)
    $(document).off('click', '#send-quote-btn').on('click', '#send-quote-btn', function(e) {
        e.preventDefault();
        var $this = $(this);
        var shotId = $this.data('shot-id');
        var vendorNote = $('#vendor-note').val();
        var vendorQuote = $('#vendor-quote').val();
        var deliveryDate = $('#delivery-date').val();

        if (!vendorQuote || vendorQuote <= 0) {
            alert('Please enter a valid quote amount.');
            return;
        }

        if (!deliveryDate) {
            alert('Please select a delivery date.');
            return;
        }

        $.ajax({
            url: wcfm_customshots_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcfm_ajax_controller',
                controller: 'wcfm-customshots',
                action_type: 'send_quote',
                shot_id: shotId,
                vendor_note: vendorNote,
                vendor_quote: vendorQuote,
                delivery_date: deliveryDate
            },
            beforeSend: function() {
                $this.addClass('loading').prop('disabled', true).text('Sending...');
            },
            success: function(response) {
                $this.removeClass('loading').prop('disabled', false).text('Send Quote');
                if (response.success) {
                    alert(response.data.message);
                    // Reload to show updated status
                    location.reload();
                } else {
                    alert(response.data.message || 'Error sending quote');
                }
            },
            error: function() {
                $this.removeClass('loading').prop('disabled', false).text('Send Quote');
                alert('Error processing request');
            }
        });
    });

    // Accept Offer button - direct accept at buyer's price (unbind first to prevent duplicates)
    $(document).off('click', '#accept-offer-btn').on('click', '#accept-offer-btn', function(e) {
        console.log('Accept offer button clicked');
        e.preventDefault();
        var $this = $(this);
        var shotId = $this.data('shot-id');

        if (!confirm('Accept this offer at the buyer\'s proposed budget? This will start a chat with the buyer.')) {
            return;
        }

        $.ajax({
            url: wcfm_customshots_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcfm_ajax_controller',
                controller: 'wcfm-customshots',
                action_type: 'accept',
                shot_id: shotId
            },
            beforeSend: function() {
                $this.addClass('loading').prop('disabled', true).text('Accepting...');
            },
            success: function(response) {
                $this.removeClass('loading').prop('disabled', false);
                if (response.success) {
                    alert(response.data.message);
                    // Reload to show chat button
                    location.reload();
                } else {
                    alert(response.data.message || 'Error accepting offer');
                    $this.text('Accept Offer');
                }
            },
            error: function() {
                $this.removeClass('loading').prop('disabled', false).text('Accept Offer');
                alert('Error processing request');
            }
        });
    });

    // View buyer profile (using event delegation)
    $(document).on('click', '#view-buyer-profile', function(e) {
        e.preventDefault();
        var buyerId = $(this).data('buyer-id');
        // TODO: Implement profile view or redirect
        alert('Profile view feature coming soon!');
    });

});
