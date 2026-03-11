/**
 * WCFM Custom Shot Chat JavaScript
 * Handles chat messaging, file uploads, and deliverables
 */

(function($) {
    'use strict';

    var Chat = {
        lastMessageId: 0,
        pollTimer: null,
        isPolling: false,

        init: function() {
            if ($('.chat-container').length === 0) return;

            this.shotId = wcfm_chat_ajax.shot_id;
            this.userId = wcfm_chat_ajax.user_id;

            this.bindEvents();
            this.scrollToBottom();
            this.getLastMessageId();
            this.startPolling();
            this.autoResizeTextarea();
        },

        bindEvents: function() {
            var self = this;

            // Send message form
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                self.sendMessage();
            });

            // Attach file button
            $('#btn-attach').on('click', function() {
                $('#file-input').click();
            });

            // File input change
            $('#file-input').on('change', function() {
                self.uploadFile(this.files[0]);
                $(this).val('');
            });

            // Enter key to send (Shift+Enter for new line)
            $('#chat-input').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    $('#chat-form').submit();
                }
            });

            // Deliverable image upload - use event delegation and stop propagation
            $(document).on('click', '#upload-images, #btn-add-images', function(e) {
                // Don't trigger if clicking on the file input itself or remove button
                if ($(e.target).is('input[type="file"]') || $(e.target).closest('.remove-preview').length) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                $('#deliverable-images').trigger('click');
            });

            $(document).on('change', '#deliverable-images', function() {
                self.uploadDeliverables(this.files, 'image');
                $(this).val('');
            });

            // Deliverable video upload - use event delegation and stop propagation
            $(document).on('click', '#upload-videos, #btn-add-videos', function(e) {
                // Don't trigger if clicking on the file input itself or remove button
                if ($(e.target).is('input[type="file"]') || $(e.target).closest('.remove-preview').length) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                $('#deliverable-videos').trigger('click');
            });

            $(document).on('change', '#deliverable-videos', function() {
                self.uploadDeliverables(this.files, 'video');
                $(this).val('');
            });

            // Remove preview
            $(document).on('click', '.remove-preview', function(e) {
                e.stopPropagation();
                var id = $(this).data('id');
                self.removeDeliverable(id);
                $(this).closest('.preview-item').remove();
            });

            // Submit deliverables
            $('#btn-submit-deliverables').on('click', function() {
                self.submitDeliverables();
            });

            // Drag and drop for upload areas
            this.initDragDrop();
        },

        sendMessage: function() {
            var self = this;
            var $input = $('#chat-input');
            var message = $input.val().trim();

            if (!message) return;

            // Clear input immediately
            $input.val('');

            // Add optimistic message to UI
            var tempId = 'temp-' + Date.now();
            this.appendMessage({
                id: tempId,
                sender_id: this.userId,
                message_type: 'text',
                message_content: message,
                created_at: new Date().toISOString()
            }, true);

            // Send to server
            $.ajax({
                url: wcfm_chat_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_ajax_controller',
                    controller: 'wcfm-customshot-chat',
                    action_type: 'send_message',
                    shot_id: this.shotId,
                    message: message,
                    nonce: wcfm_chat_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update temp message with real ID
                        $('[data-message-id="' + tempId + '"]').attr('data-message-id', response.data.message_id);
                        self.lastMessageId = response.data.message_id;
                    } else {
                        self.showError(wcfm_chat_ajax.strings.send_error);
                        // Remove temp message on error
                        $('[data-message-id="' + tempId + '"]').remove();
                    }
                },
                error: function() {
                    self.showError(wcfm_chat_ajax.strings.send_error);
                    $('[data-message-id="' + tempId + '"]').remove();
                }
            });
        },

        uploadFile: function(file) {
            var self = this;

            if (!file) return;

            // Check file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                this.showError(wcfm_chat_ajax.strings.file_too_large);
                return;
            }

            var formData = new FormData();
            formData.append('action', 'wcfm_ajax_controller');
            formData.append('controller', 'wcfm-customshot-chat');
            formData.append('action_type', 'upload_file');
            formData.append('shot_id', this.shotId);
            formData.append('file', file);
            formData.append('nonce', wcfm_chat_ajax.nonce);

            // Show uploading indicator
            var tempId = 'temp-' + Date.now();
            this.appendMessage({
                id: tempId,
                sender_id: this.userId,
                message_type: 'file',
                message_content: wcfm_chat_ajax.strings.sending,
                file_name: file.name,
                file_type: file.type,
                created_at: new Date().toISOString()
            }, true);

            $.ajax({
                url: wcfm_chat_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Replace temp message with actual
                        var $temp = $('[data-message-id="' + tempId + '"]');
                        $temp.attr('data-message-id', response.data.message_id);

                        // Update content
                        var $content = $temp.find('.message-file');
                        if (response.data.is_image) {
                            $content.html('<a href="' + response.data.file_url + '" target="_blank"><img src="' + response.data.file_url + '" alt="" class="message-image"></a>');
                        } else {
                            $content.html('<a href="' + response.data.file_url + '" target="_blank" class="file-link"><span class="wcfmfa fa-file"></span> ' + response.data.file_name + '</a>');
                        }

                        self.lastMessageId = response.data.message_id;
                    } else {
                        self.showError(wcfm_chat_ajax.strings.upload_error);
                        $('[data-message-id="' + tempId + '"]').remove();
                    }
                },
                error: function() {
                    self.showError(wcfm_chat_ajax.strings.upload_error);
                    $('[data-message-id="' + tempId + '"]').remove();
                }
            });
        },

        appendMessage: function(msg, isMine) {
            var $container = $('#chat-messages');
            var html = '';

            if (msg.message_type === 'system') {
                html = '<div class="message-system"><span>' + this.escapeHtml(msg.message_content) + '</span></div>';
            } else {
                var bubbleClass = isMine ? 'sent' : 'received';
                var avatarHtml = '';

                html = '<div class="message-bubble ' + bubbleClass + '" data-message-id="' + msg.id + '">';

                if (!isMine && msg.sender_avatar) {
                    html += '<img src="' + msg.sender_avatar + '" alt="" class="message-avatar">';
                }

                html += '<div class="message-content">';

                if (msg.message_type === 'text') {
                    html += '<div class="message-text">' + this.nl2br(this.escapeHtml(msg.message_content)) + '</div>';
                } else if (msg.message_type === 'file') {
                    html += '<div class="message-file">';
                    if (msg.file_type && msg.file_type.indexOf('image') !== -1 && msg.file_url) {
                        html += '<a href="' + msg.file_url + '" target="_blank"><img src="' + msg.file_url + '" alt="" class="message-image"></a>';
                    } else {
                        html += '<a href="' + (msg.file_url || '#') + '" target="_blank" class="file-link"><span class="wcfmfa fa-file"></span> ' + this.escapeHtml(msg.file_name || msg.message_content) + '</a>';
                    }
                    html += '</div>';
                }

                html += '<span class="message-time">' + this.formatTime(msg.created_at) + '</span>';
                html += '</div></div>';
            }

            // Remove empty state if exists
            $container.find('.chat-empty').remove();

            $container.append(html);
            this.scrollToBottom();
        },

        startPolling: function() {
            var self = this;

            if (this.pollTimer) {
                clearInterval(this.pollTimer);
            }

            this.pollTimer = setInterval(function() {
                self.pollNewMessages();
            }, wcfm_chat_ajax.poll_interval);
        },

        pollNewMessages: function() {
            var self = this;

            if (this.isPolling) return;
            this.isPolling = true;

            $.ajax({
                url: wcfm_chat_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_ajax_controller',
                    controller: 'wcfm-customshot-chat',
                    action_type: 'get_new_messages',
                    shot_id: this.shotId,
                    last_id: this.lastMessageId,
                    nonce: wcfm_chat_ajax.nonce
                },
                success: function(response) {
                    self.isPolling = false;

                    if (response.success && response.data.messages) {
                        response.data.messages.forEach(function(msg) {
                            // Don't add messages we already have
                            if ($('[data-message-id="' + msg.id + '"]').length === 0) {
                                var isMine = msg.sender_id == self.userId;
                                self.appendMessage(msg, isMine);
                                self.lastMessageId = msg.id;
                            }
                        });
                    }
                },
                error: function() {
                    self.isPolling = false;
                }
            });
        },

        getLastMessageId: function() {
            var $lastMsg = $('.message-bubble:last');
            if ($lastMsg.length) {
                this.lastMessageId = parseInt($lastMsg.data('message-id')) || 0;
            }
        },

        // Deliverables functions
        uploadDeliverables: function(files, type) {
            var self = this;

            for (var i = 0; i < files.length; i++) {
                (function(file) {
                    var formData = new FormData();
                    formData.append('action', 'wcfm_ajax_controller');
                    formData.append('controller', 'wcfm-customshot-chat');
                    formData.append('action_type', 'upload_deliverable');
                    formData.append('shot_id', self.shotId);
                    formData.append('file', file);
                    formData.append('type', type);
                    formData.append('nonce', wcfm_chat_ajax.nonce);

                    $.ajax({
                        url: wcfm_chat_ajax.ajax_url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                self.addDeliverablePreview(response.data, type);
                            } else {
                                self.showError(response.data.message || wcfm_chat_ajax.strings.upload_error);
                            }
                        },
                        error: function() {
                            self.showError(wcfm_chat_ajax.strings.upload_error);
                        }
                    });
                })(files[i]);
            }
        },

        addDeliverablePreview: function(data, type) {
            var html = '';
            var $container = type === 'image' ? $('#images-preview') : $('#videos-preview');

            if (type === 'image') {
                html = '<div class="preview-item" data-id="' + data.attachment_id + '">' +
                       '<img src="' + data.url + '" alt="">' +
                       '<span type="button" class="remove-preview" data-id="' + data.attachment_id + '">&times;</span>' +
                       '</div>';
            } else {
                html = '<div class="preview-item video-item" data-id="' + data.attachment_id + '">' +
                       '<span class="wcfmfa fa-film"></span>' +
                       '<span type="button" class="remove-preview" data-id="' + data.attachment_id + '">&times;</span>' +
                       '</div>';
            }

            $container.append(html);
        },

        removeDeliverable: function(attachmentId) {
            $.ajax({
                url: wcfm_chat_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_ajax_controller',
                    controller: 'wcfm-customshot-chat',
                    action_type: 'remove_deliverable',
                    shot_id: this.shotId,
                    attachment_id: attachmentId,
                    nonce: wcfm_chat_ajax.nonce
                }
            });
        },

        submitDeliverables: function() {
            var self = this;
            var $btn = $('#btn-submit-deliverables');

            $btn.prop('disabled', true).text('Submitting...');

            $.ajax({
                url: wcfm_chat_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcfm_ajax_controller',
                    controller: 'wcfm-customshot-chat',
                    action_type: 'submit_deliverables',
                    shot_id: this.shotId,
                    nonce: wcfm_chat_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.text('Update Deliverables');

                        // Update status display
                        var $statusSection = $('.deliverable-status-section');
                        if ($statusSection.length === 0) {
                            $('<div class="deliverable-status-section"><label>Status</label><div class="deliverable-status status-pending_review">Pending Review</div></div>').insertBefore('.deliverables-actions');
                        } else {
                            $statusSection.find('.deliverable-status')
                                .removeClass()
                                .addClass('deliverable-status status-pending_review')
                                .text('Pending Review');
                        }

                        self.showSuccess('Deliverables submitted for review!');
                    } else {
                        self.showError(response.data.message || 'Failed to submit deliverables');
                    }
                    $btn.prop('disabled', false);
                },
                error: function() {
                    self.showError('Failed to submit deliverables');
                    $btn.prop('disabled', false).text('Submit for Review');
                }
            });
        },

        initDragDrop: function() {
            var self = this;

            $('.upload-area').on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            $('.upload-area').on('dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            $('#upload-images').on('drop', function(e) {
                var files = e.originalEvent.dataTransfer.files;
                self.uploadDeliverables(files, 'image');
            });

            $('#upload-videos').on('drop', function(e) {
                var files = e.originalEvent.dataTransfer.files;
                self.uploadDeliverables(files, 'video');
            });
        },

        scrollToBottom: function() {
            var $container = $('#chat-messages');
            $container.scrollTop($container[0].scrollHeight);
        },

        autoResizeTextarea: function() {
            var $textarea = $('#chat-input');

            $textarea.on('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        },

        showError: function(message) {
            // Simple alert for now, can be replaced with toast
            alert(message);
        },

        showSuccess: function(message) {
            alert(message);
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        nl2br: function(str) {
            return str.replace(/\n/g, '<br>');
        },

        formatTime: function(datetime) {
            var date = new Date(datetime);
            var now = new Date();
            var diff = (now - date) / 1000;

            if (diff < 60) {
                return 'Just now';
            } else if (diff < 3600) {
                var mins = Math.floor(diff / 60);
                return mins + ' min' + (mins > 1 ? 's' : '') + ' ago';
            } else if (diff < 86400) {
                var hours = Math.floor(diff / 3600);
                return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
            } else {
                return date.toLocaleDateString();
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        Chat.init();
    });

})(jQuery);
