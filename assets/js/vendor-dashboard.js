/* global plfvData, wp */
(function ($) {
    'use strict';

    // ── Copy Link ──────────────────────────────────────────
    function copyToClipboard(text, $btn) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function () {
                flashCopied($btn);
            }).catch(function () {
                fallbackCopy(text, $btn);
            });
        } else {
            fallbackCopy(text, $btn);
        }
    }

    function fallbackCopy(text, $btn) {
        var $temp = $('<textarea>').val(text).css({ position: 'fixed', opacity: 0 }).appendTo('body');
        $temp.select();
        try {
            document.execCommand('copy');
            flashCopied($btn);
        } catch (e) {
            alert(plfvData.i18n.copyFailed);
        }
        $temp.remove();
    }

    function flashCopied($btn) {
        var original = $btn.text();
        $btn.text(plfvData.i18n.copied).addClass('copied');
        setTimeout(function () {
            $btn.text(original).removeClass('copied');
        }, 2000);
    }

    // Copy on dashboard table
    $(document).on('click', '.plfv-btn-copy', function () {
        var url = $(this).data('url');
        copyToClipboard(url, $(this));
    });

    // Copy inside modal
    $(document).on('click', '#plfv-copy-modal-btn', function () {
        var url = $('#plfv-generated-link').val();
        copyToClipboard(url, $(this));
    });

    // ── Media Uploader ──────────────────────────────────────────
    var mediaFrame;

    $(document).on('click', '#plfv-upload-btn', function (e) {
        e.preventDefault();

        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media({
            title: plfvData.i18n.selectImage,
            button: { text: plfvData.i18n.useImage },
            multiple: false,
            library: { type: 'image' }
        });

        mediaFrame.on('select', function () {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            $('#plfv-image-id').val(attachment.id);
            var thumbUrl = (attachment.sizes && attachment.sizes.thumbnail)
                ? attachment.sizes.thumbnail.url
                : attachment.url;
            $('#plfv-image-preview')
                .html('<img src="' + thumbUrl + '" alt="" />')
                .addClass('has-image');
            $('#plfv-remove-btn').show();
        });

        mediaFrame.open();
    });

    $(document).on('click', '#plfv-remove-btn', function (e) {
        e.preventDefault();
        $('#plfv-image-id').val('');
        $('#plfv-image-preview').html('').removeClass('has-image');
        $(this).hide();
    });

    // ── Form Submission ──────────────────────────────────────────
    $(document).on('click', '#plfv-submit-btn', function () {
        var $btn    = $(this);
        var action  = $btn.data('action');
        var prodId  = $btn.data('product-id');
        var price   = $.trim($('#plfv-price').val());
        var desc    = $.trim($('#plfv-description').val());
        var imageId = $('#plfv-image-id').val();

        // Basic client-side validation
        $('.plfv-notice-error').remove();
        if (!price || parseFloat(price) <= 0) {
            showFormError(plfvData.i18n.errorPrice);
            return;
        }
        if (!desc) {
            showFormError(plfvData.i18n.errorDesc);
            return;
        }

        $btn.prop('disabled', true).text(plfvData.i18n.saving);

        var data = {
            action:      action,
            nonce:       plfvData.nonce,
            price:       price,
            description: desc,
            image_id:    imageId
        };

        if (action === 'plfv_update_product') {
            data.product_id = prodId;
        }

        $.post(plfvData.ajaxUrl, data, function (response) {
            if (response.success) {
                if (action === 'plfv_create_product') {
                    // Show modal
                    $('#plfv-generated-link').val(response.data.payment_url);
                    $('#plfv-modal').fadeIn(200);
                } else {
                    // Edit: redirect to dashboard with success indicator
                    window.location.href = plfvData.dashUrl + '&updated=1';
                }
            } else {
                showFormError(response.data.message || plfvData.i18n.errorServer);
                $btn.prop('disabled', false).text(
                    action === 'plfv_update_product' ? plfvData.i18n.saveChanges : plfvData.i18n.generateLink
                );
            }
        }).fail(function () {
            showFormError(plfvData.i18n.errorServer);
            $btn.prop('disabled', false).text(
                action === 'plfv_update_product' ? plfvData.i18n.saveChanges : plfvData.i18n.generateLink
            );
        });
    });

    function showFormError(msg) {
        $('.plfv-form-actions').before(
            '<div class="plfv-notice plfv-notice-error">' + msg + '</div>'
        );
        $('html, body').animate({ scrollTop: $('.plfv-notice-error').offset().top - 40 }, 200);
    }

    // ── Modal: Create Another ──────────────────────────────────────────
    $(document).on('click', '#plfv-create-another', function () {
        $('#plfv-modal').fadeOut(150);
        $('#plfv-price').val('');
        $('#plfv-description').val('');
        $('#plfv-image-id').val('');
        $('#plfv-image-preview').html('').removeClass('has-image');
        $('#plfv-remove-btn').hide();
        $('#plfv-submit-btn').prop('disabled', false).text(plfvData.i18n.generateLink);
        mediaFrame = null;
    });

    // Close modal on backdrop click
    $(document).on('click', '.plfv-modal-backdrop', function () {
        $('#plfv-modal').fadeOut(150);
    });

    // ── Settings page: default image uploader ──────────────────────────────────────────
    var defaultMediaFrame;

    $(document).on('click', '#plfv-default-upload-btn', function (e) {
        e.preventDefault();

        if (defaultMediaFrame) {
            defaultMediaFrame.open();
            return;
        }

        defaultMediaFrame = wp.media({
            title: plfvData.i18n.selectImage,
            button: { text: plfvData.i18n.useImage },
            multiple: false,
            library: { type: 'image' }
        });

        defaultMediaFrame.on('select', function () {
            var attachment = defaultMediaFrame.state().get('selection').first().toJSON();
            $('#plfv-default-image-id').val(attachment.id);
            var thumbUrl = (attachment.sizes && attachment.sizes.thumbnail)
                ? attachment.sizes.thumbnail.url
                : attachment.url;
            $('#plfv-default-image-preview')
                .html('<img src="' + thumbUrl + '" alt="" />')
                .addClass('has-image');
            $('#plfv-default-remove-btn').show();
        });

        defaultMediaFrame.open();
    });

    $(document).on('click', '#plfv-default-remove-btn', function (e) {
        e.preventDefault();
        $('#plfv-default-image-id').val('');
        $('#plfv-default-image-preview').html('').removeClass('has-image');
        $(this).hide();
        defaultMediaFrame = null;
    });

    // ── Dashboard: show updated notice ──────────────────────────────────────────
    if (window.location.search.indexOf('updated=1') !== -1) {
        var $notice = $('<div class="notice notice-success is-dismissible"><p>' + plfvData.i18n.updatedNotice + '</p></div>');
        $('.plfv-page-title').after($notice);
    }

})(jQuery);
