/**
 * Paymentio Admin JavaScript
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

jQuery(document).ready(function ($) {
    'use strict';

    // 1. Quick Toggle Gateway Status
    $(document).on('change', '.paymentio-gw-toggle', function () {
        var $toggle = $(this);
        var gatewayId = $toggle.data('gateway');
        var isEnabled = $toggle.is(':checked') ? 1 : 0;
        var $card = $toggle.closest('.paymentio-gw-card');

        $toggle.prop('disabled', true);

        $.ajax({
            url: paymentioData.ajax_url,
            type: 'POST',
            data: {
                action: 'paymentio_toggle_gateway',
                nonce: paymentioData.nonce,
                gateway_id: gatewayId,
                enabled: isEnabled
            },
            success: function (res) {
                $toggle.prop('disabled', false);
                if (res.success) {
                    if (isEnabled) {
                        $card.addClass('enabled');
                    } else {
                        $card.removeClass('enabled');
                    }
                } else {
                    alert(res.data.message || 'خطا در تغییر وضعیت درگاه');
                    $toggle.prop('checked', !isEnabled);
                }
            },
            error: function () {
                $toggle.prop('disabled', false);
                alert('خطا در برقراری ارتباط با سرور');
                $toggle.prop('checked', !isEnabled);
            }
        });
    });

    // 2. Open / Close Settings Modals
    $(document).on('click', '.paymentio-open-settings, .paymentio-open-modal', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target') || $(this).data('modal');
        if (targetId) {
            $('#' + targetId).css('display', 'flex').hide().fadeIn(200);
        }
    });

    $(document).on('click', '.paymentio-modal-close', function (e) {
        e.preventDefault();
        $(this).closest('.paymentio-modal').fadeOut(200);
    });

    // Close on background click
    $(document).on('click', '.paymentio-modal', function (e) {
        if ($(e.target).hasClass('paymentio-modal')) {
            $(this).fadeOut(200);
        }
    });

    // 3. Save Gateway Settings via AJAX
    $(document).on('submit', '.paymentio-gw-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var gatewayId = $form.data('gateway');

        $btn.prop('disabled', true).text('در حال ذخیره...');

        var formData = $form.serializeArray();
        var postData = {
            action: 'paymentio_save_gateway_settings',
            nonce: paymentioData.nonce,
            gateway_id: gatewayId
        };

        $.each(formData, function (i, field) {
            postData[field.name] = field.value;
        });

        $.ajax({
            url: paymentioData.ajax_url,
            type: 'POST',
            data: postData,
            success: function (res) {
                $btn.prop('disabled', false).text('ذخیره تغییرات');
                if (res.success) {
                    $form.closest('.paymentio-modal').fadeOut(200);
                    // Reload to reflect changes
                    window.location.reload();
                } else {
                    alert(res.data.message || 'خطا در ذخیره سازی اطلاعات');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('ذخیره تغییرات');
                alert('خطا در برقراری ارتباط با سرور');
            }
        });
    });

    // 4. API Latency Test
    $(document).on('click', '.paymentio-test-api', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var slug = $btn.data('slug');
        var $card = $('#api-test-' + slug);
        var $status = $card.find('.paymentio-api-status');

        $btn.prop('disabled', true).text('در حال تست...');
        if ($status.length) {
            $status.html('<span class="status-indicator ready">در حال پینگ...</span>');
        }

        $.ajax({
            url: paymentioData.ajax_url,
            type: 'POST',
            data: {
                action: 'paymentio_test_gateway',
                nonce: paymentioData.nonce,
                gateway: slug
            },
            success: function (res) {
                $btn.prop('disabled', false).text('بررسی پینگ');
                if (res.success && res.data.success) {
                    var ms = res.data.latency;
                    if ($status.length) {
                        $status.html('<span class="status-indicator success">متصل - زمان پاسخ: ' + ms + ' میلی ثانیه</span>');
                    } else {
                        alert('پاسخ سرور دریافت شد (زمان: ' + ms + ' میلی ثانیه)');
                    }
                } else {
                    var errMsg = (res.data && res.data.message) ? res.data.message : 'خطا در برقراری ارتباط';
                    if ($status.length) {
                        $status.html('<span class="status-indicator error">خطا در اتصال: ' + errMsg + '</span>');
                    } else {
                        alert('خطا در اتصال: ' + errMsg);
                    }
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('بررسی پینگ');
                if ($status.length) {
                    $status.html('<span class="status-indicator error">خطای شبکه</span>');
                } else {
                    alert('خطای شبکه در درخواست تست');
                }
            }
        });
    });

    // 5. Copy RefID on Click
    $(document).on('click', '.paymentio-code', function () {
        var text = $(this).text();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                alert('کد پیگیری کپی شد: ' + text);
            });
        }
    });

    // 6. Settings Mode Card selection sync
    $(document).on('change', '.paymentio-mode-card input[type="radio"]', function () {
        $('.paymentio-mode-card').removeClass('selected');
        $(this).closest('.paymentio-mode-card').addClass('selected');
    });
});
