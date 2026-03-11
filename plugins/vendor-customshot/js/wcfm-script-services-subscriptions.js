/**
 * WCFM Services & Subscriptions JavaScript
 */
(function($) {
    'use strict';

    var ServicesSubscriptions = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Service modal
            $('#add-service-btn').on('click', this.openAddServiceModal.bind(this));
            $('#close-service-modal, #cancel-service-btn').on('click', this.closeServiceModal.bind(this));
            $('#save-service-btn').on('click', this.saveService.bind(this));

            // Edit/Delete service
            $(document).on('click', '.edit-service', this.editService.bind(this));
            $(document).on('click', '.delete-service', this.deleteService.bind(this));

            // Subscription toggle
            $('#subscriptions-toggle').on('change', this.toggleSubscriptions.bind(this));

            // Save subscription settings
            $('#save-subscriptions-btn').on('click', this.saveSubscriptionSettings.bind(this));

            // Close modal on overlay click
            $('.service-modal-overlay').on('click', function(e) {
                if ($(e.target).hasClass('service-modal-overlay')) {
                    this.closeServiceModal();
                }
            }.bind(this));

            // Close modal on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#service-modal').is(':visible')) {
                    this.closeServiceModal();
                }
            }.bind(this));
        },

        openAddServiceModal: function() {
            $('#service-modal-title').text(wcfm_services_subscriptions.i18n.add_service);
            $('#service-edit-index').val('');
            $('#service-form')[0].reset();
            $('#service-modal').fadeIn(200);
        },

        closeServiceModal: function() {
            $('#service-modal').fadeOut(200);
        },

        editService: function(e) {
            e.preventDefault();
            var index = $(e.currentTarget).data('index');
            var service = wcfm_services_subscriptions.services[index];

            if (!service) return;

            $('#service-modal-title').text(wcfm_services_subscriptions.i18n.edit_service);
            $('#service-edit-index').val(index);
            $('#service-name').val(service.name);
            $('#service-rate').val(service.base_rate);
            $('#service-turnaround').val(service.turnaround);
            $('#service-description').val(service.description || '');

            $('#service-modal').fadeIn(200);
        },

        saveService: function() {
            var serviceName = $('#service-name').val().trim();
            var serviceRate = $('#service-rate').val();
            var serviceTurnaround = $('#service-turnaround').val().trim();
            var serviceDescription = $('#service-description').val().trim();
            var editIndex = $('#service-edit-index').val();

            // Validate
            if (!serviceName || !serviceRate || !serviceTurnaround) {
                alert(wcfm_services_subscriptions.i18n.fill_required);
                return;
            }

            var $btn = $('#save-service-btn');
            $btn.prop('disabled', true).text(wcfm_services_subscriptions.i18n.saving);

            $.ajax({
                url: wcfm_services_subscriptions.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_save_vendor_service',
                    nonce: wcfm_services_subscriptions.nonce,
                    service_name: serviceName,
                    service_rate: serviceRate,
                    service_turnaround: serviceTurnaround,
                    service_description: serviceDescription,
                    edit_index: editIndex
                },
                success: function(response) {
                    if (response.success) {
                        wcfm_services_subscriptions.services = response.data.services;
                        this.renderServices();
                        this.closeServiceModal();
                    } else {
                        alert(response.data.message || wcfm_services_subscriptions.i18n.error);
                    }
                }.bind(this),
                error: function() {
                    alert(wcfm_services_subscriptions.i18n.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(wcfm_services_subscriptions.i18n.save || 'Save Service');
                }
            });
        },

        deleteService: function(e) {
            e.preventDefault();

            if (!confirm(wcfm_services_subscriptions.i18n.confirm_delete)) {
                return;
            }

            var index = $(e.currentTarget).data('index');
            var $card = $(e.currentTarget).closest('.service-card');

            $card.css('opacity', '0.5');

            $.ajax({
                url: wcfm_services_subscriptions.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_delete_vendor_service',
                    nonce: wcfm_services_subscriptions.nonce,
                    service_index: index
                },
                success: function(response) {
                    if (response.success) {
                        wcfm_services_subscriptions.services = response.data.services;
                        this.renderServices();
                    } else {
                        alert(response.data.message || wcfm_services_subscriptions.i18n.error);
                        $card.css('opacity', '1');
                    }
                }.bind(this),
                error: function() {
                    alert(wcfm_services_subscriptions.i18n.error);
                    $card.css('opacity', '1');
                }
            });
        },

        renderServices: function() {
            var $grid = $('#services-list');
            var services = wcfm_services_subscriptions.services;
            var currencySymbol = $grid.find('.detail-value').first().text().charAt(0) || '$';

            // Remove existing service cards (except the add button)
            $grid.find('.service-card:not(.service-card-add)').remove();

            // Add service cards
            if (services && services.length > 0) {
                services.forEach(function(service, index) {
                    var html = this.getServiceCardHTML(service, index, currencySymbol);
                    $('#add-service-card').before(html);
                }.bind(this));
            }
        },

        getServiceCardHTML: function(service, index, currencySymbol) {
            var description = service.description ?
                '<div class="service-description">' + this.escapeHtml(service.description) + '</div>' : '';

            return '<div class="service-card" data-service-index="' + index + '">' +
                '<div class="service-card-header">' +
                    '<h3 class="service-name">' + this.escapeHtml(service.name) + '</h3>' +
                    '<div class="service-actions">' +
                        '<button type="button" class="service-action-btn edit-service" data-index="' + index + '" title="Edit">' +
                            '<span class="wcfmfa fa-pencil-alt"></span>' +
                        '</button>' +
                        '<button type="button" class="service-action-btn delete-service" data-index="' + index + '" title="Delete">' +
                            '<span class="wcfmfa fa-trash"></span>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="service-card-body">' +
                    '<div class="service-detail">' +
                        '<span class="detail-label">Base Rate:</span>' +
                        '<span class="detail-value">' + currencySymbol + parseFloat(service.base_rate).toFixed(2) + '</span>' +
                    '</div>' +
                    '<div class="service-detail">' +
                        '<span class="detail-label">Turnaround:</span>' +
                        '<span class="detail-value">' + this.escapeHtml(service.turnaround) + '</span>' +
                    '</div>' +
                    description +
                '</div>' +
            '</div>';
        },

        toggleSubscriptions: function(e) {
            var enabled = $(e.currentTarget).is(':checked') ? 'yes' : 'no';
            var $tiers = $('#subscription-tiers');
            var $status = $('#save-status');

            $status.text(wcfm_services_subscriptions.i18n.saving).removeClass('success error');

            $.ajax({
                url: wcfm_services_subscriptions.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_toggle_subscriptions',
                    nonce: wcfm_services_subscriptions.nonce,
                    enabled: enabled
                },
                success: function(response) {
                    if (response.success) {
                        if (enabled === 'yes') {
                            $tiers.slideDown(300);
                        } else {
                            $tiers.slideUp(300);
                        }
                        $status.text(wcfm_services_subscriptions.i18n.saved).addClass('success');

                        // Update product IDs if they were created
                        if (response.data.basic_product_id || response.data.elite_product_id) {
                            // Optionally refresh page to show product IDs
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        // Revert toggle
                        $(e.currentTarget).prop('checked', !$(e.currentTarget).is(':checked'));
                        $status.text(response.data.message || wcfm_services_subscriptions.i18n.error).addClass('error');
                    }
                }.bind(this),
                error: function() {
                    // Revert toggle
                    $(e.currentTarget).prop('checked', !$(e.currentTarget).is(':checked'));
                    $status.text(wcfm_services_subscriptions.i18n.error).addClass('error');
                },
                complete: function() {
                    setTimeout(function() {
                        $status.text('');
                    }, 3000);
                }
            });
        },

        saveSubscriptionSettings: function() {
            var basicPrice = $('#basic-price').val();
            var elitePrice = $('#elite-price').val();
            var $btn = $('#save-subscriptions-btn');
            var $status = $('#save-status');

            $btn.prop('disabled', true);
            $status.text(wcfm_services_subscriptions.i18n.saving).removeClass('success error');

            $.ajax({
                url: wcfm_services_subscriptions.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_save_subscription_settings',
                    nonce: wcfm_services_subscriptions.nonce,
                    basic_price: basicPrice,
                    elite_price: elitePrice
                },
                success: function(response) {
                    if (response.success) {
                        $status.text(wcfm_services_subscriptions.i18n.saved).addClass('success');
                    } else {
                        $status.text(response.data.message || wcfm_services_subscriptions.i18n.error).addClass('error');
                    }
                },
                error: function() {
                    $status.text(wcfm_services_subscriptions.i18n.error).addClass('error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    setTimeout(function() {
                        $status.text('');
                    }, 3000);
                }
            });
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    $(document).ready(function() {
        if ($('#wcfm_services_subscriptions').length) {
            ServicesSubscriptions.init();
        }
    });

})(jQuery);
