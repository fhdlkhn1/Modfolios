/**
 * Buyer Custom Shots JavaScript
 * Handles buyer actions: accept/reject quote, approve deliverables
 */

(function($) {
    'use strict';

    var BuyerCustomshots = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Accept quote
            $(document).on('click', '.btn-accept-quote', function() {
                var shotId = $(this).data('shot-id');
                self.acceptQuote(shotId, $(this));
            });

            // Reject quote
            $(document).on('click', '.btn-reject-quote', function() {
                var shotId = $(this).data('shot-id');
                if (confirm('Are you sure you want to decline this quote?')) {
                    self.rejectQuote(shotId, $(this));
                }
            });

            // Approve deliverables
            $(document).on('click', '.btn-approve-deliverables', function() {
                var shotId = $(this).data('shot-id');
                if (confirm('Are you sure you want to approve these deliverables? This will complete the project.')) {
                    self.approveDeliverables(shotId, $(this));
                }
            });

            // Request revision - show modal
            $(document).on('click', '.btn-request-revision', function() {
                var shotId = $(this).data('shot-id');
                $('#revision-modal').data('shot-id', shotId).show();
            });

            // Cancel revision modal
            $('#cancel-revision').on('click', function() {
                $('#revision-modal').hide();
                $('#revision-reason').val('');
            });

            // Submit revision request
            $('#submit-revision').on('click', function() {
                var shotId = $('#revision-modal').data('shot-id');
                var reason = $('#revision-reason').val();
                BuyerCustomshots.requestRevision(shotId, reason);
            });

            // Close modal on backdrop click
            $(document).on('click', '#revision-modal', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });

            // Approve single deliverable item (new class)
            $(document).on('click', '.btn-approve-item', function() {
                var shotId = $(this).data('shot-id');
                var attachmentId = $(this).data('attachment-id');
                self.approveSingleItem(shotId, attachmentId, $(this));
            });

            // Approve single deliverable item (old class for backwards compatibility)
            $(document).on('click', '.btn-approve-single', function() {
                var shotId = $(this).data('shot-id');
                var attachmentId = $(this).data('attachment-id');
                self.approveSingleItem(shotId, attachmentId, $(this));
            });

            // Approve all deliverables (new green button)
            $(document).on('click', '.btn-approve-all-green', function() {
                var shotId = $(this).data('shot-id');
                var feedback = $('#deliverable-feedback').val();
                if (confirm('Are you sure you want to approve all deliverables? This will complete the project.')) {
                    self.approveAllDeliverables(shotId, feedback, $(this));
                }
            });

            // Approve all deliverables (old class for backwards compatibility)
            $(document).on('click', '.btn-approve-all', function() {
                var shotId = $(this).data('shot-id');
                var feedback = $('#deliverable-feedback').val();
                if (confirm('Are you sure you want to approve all deliverables? This will complete the project.')) {
                    self.approveAllDeliverables(shotId, feedback, $(this));
                }
            });

            // Submit revision request from Bootstrap modal
            $(document).on('click', '#submit-revision-request', function() {
                var shotId = $(this).data('shot-id');
                var notes = $('#revision-notes').val();

                // Get selected items for revision
                var selectedItems = [];
                $('.revision-checkbox:checked').each(function() {
                    selectedItems.push($(this).data('attachment-id'));
                });

                if (!notes.trim()) {
                    alert('Please enter revision notes.');
                    return;
                }

                self.submitRevisionRequest(shotId, notes, selectedItems, $(this));
            });

            // Download all deliverables
            $(document).on('click', '#download-all-deliverables', function(e) {
                e.preventDefault();
                var shotId = $(this).data('shot-id');
                self.downloadAllDeliverables(shotId);
            });
        },

        acceptQuote: function(shotId, $btn) {
            var self = this;
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_accept_quote',
                    shot_id: shotId,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        if (response.data.chat_url) {
                            window.location.href = response.data.chat_url;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        rejectQuote: function(shotId, $btn) {
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_reject_quote',
                    shot_id: shotId,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        approveDeliverables: function(shotId, $btn) {
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_approve_deliverables',
                    shot_id: shotId,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        requestRevision: function(shotId, reason) {
            var $btn = $('#submit-revision');
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Submitting...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_request_revision',
                    shot_id: shotId,
                    reason: reason,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $('#revision-modal').hide();
                        $('#revision-reason').val('');
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        submitRevisionRequest: function(shotId, notes, selectedItems, $btn) {
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Sending...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_request_revision',
                    shot_id: shotId,
                    reason: notes,
                    selected_items: selectedItems,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Close Bootstrap modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('revisionModal'));
                        if (modal) {
                            modal.hide();
                        }
                        alert(response.data.message);
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        approveSingleItem: function(shotId, attachmentId, $btn) {
            var originalText = $btn.text();
            var $card = $btn.closest('.deliverable-item, .deliverable-card');

            $btn.prop('disabled', true).text('...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_approve_single_item',
                    shot_id: shotId,
                    attachment_id: attachmentId,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI without reload
                        $card.addClass('is-approved');

                        // Add approved check badge for new design
                        var $thumb = $card.find('.item-thumb, .deliverable-thumb');
                        if (!$thumb.find('.approved-check, .approved-badge').length) {
                            $thumb.append('<span class="approved-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></span>');
                        }

                        // Replace button with approved label
                        $btn.replaceWith('<span class="item-approved-label">Approved</span>');

                        // Update item count in header
                        if (response.data.approved_count !== undefined && response.data.total_count !== undefined) {
                            var totalStr = response.data.total_count.toString().padStart(2, '0');
                            $('.item-count').text('#' + response.data.approved_count + ' of ' + totalStr);

                            // Update progress bar if exists
                            var progress = (response.data.approved_count / response.data.total_count) * 100;
                            $('.progress-fill').css('width', progress + '%');
                            $('.progress-text').text(response.data.approved_count + ' of ' + response.data.total_count + ' approved');
                        }

                        // If all approved, reload to show completed state
                        if (response.data.all_approved) {
                            window.location.reload();
                        }
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        approveAllDeliverables: function(shotId, feedback, $btn) {
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_approve_all_deliverables',
                    shot_id: shotId,
                    feedback: feedback,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'An error occurred.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        downloadAllDeliverables: function(shotId) {
            // For now, we'll just alert - in production this would trigger a zip download
            $.ajax({
                url: customshots_buyer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'customshots_buyer_download_deliverables',
                    shot_id: shotId,
                    nonce: customshots_buyer_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.download_url) {
                        window.location.href = response.data.download_url;
                    } else {
                        alert(response.data.message || 'Download not available.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    };

    $(document).ready(function() {
        BuyerCustomshots.init();
    });

})(jQuery);
