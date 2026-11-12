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

    // 7. Approve Receipt via AJAX
    $(document).on('click', '.paymentio-btn-approve', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var receiptId = $btn.data('id');
        if (!receiptId) return;

        if (!confirm('آیا از تایید این رسید پرداخت و تکمیل خودکار سفارش اطمینان دارید؟')) {
            return;
        }

        $btn.prop('disabled', true).text('در حال تایید...');

        $.ajax({
            url: paymentioData.ajax_url,
            type: 'POST',
            data: {
                action: 'paymentio_approve_receipt',
                nonce: paymentioData.nonce,
                receipt_id: receiptId
            },
            success: function (res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.data.message || 'خطا در تایید رسید');
                    $btn.prop('disabled', false).text('تایید');
                }
            },
            error: function () {
                alert('خطا در برقراری ارتباط با سرور');
                $btn.prop('disabled', false).text('تایید');
            }
        });
    });

    // 8. Reject Receipt via AJAX
    $(document).on('click', '.paymentio-btn-reject', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var receiptId = $btn.data('id');
        if (!receiptId) return;

        var reason = prompt('لطفاً دلیل رد رسید را وارد نمایید (این دلیل برای مشتری نمایش داده خواهد شد):', 'عدم تطابق مبلغ یا ناخوانا بودن رسید');
        if (reason === null) return;

        $btn.prop('disabled', true).text('در حال رد...');

        $.ajax({
            url: paymentioData.ajax_url,
            type: 'POST',
            data: {
                action: 'paymentio_reject_receipt',
                nonce: paymentioData.nonce,
                receipt_id: receiptId,
                reason: reason
            },
            success: function (res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.data.message || 'خطا در رد رسید');
                    $btn.prop('disabled', false).text('رد');
                }
            },
            error: function () {
                alert('خطا در برقراری ارتباط با سرور');
                $btn.prop('disabled', false).text('رد');
            }
        });
    });

    // 9. Save Card Gateway Settings via AJAX
    $(document).on('submit', '.paymentio-card-gw-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');

        $btn.prop('disabled', true).text('در حال ذخیره...');

        // Update hidden bank names from select before serializing
        $form.find('.paymentio-bank-select').each(function () {
            var $sel = $(this);
            var bankName = $sel.find('option:selected').data('name') || $sel.find('option:selected').text().trim();
            $sel.closest('.paymentio-card-row').find('.paymentio-bank-name-hidden').val(bankName);
        });

        var postData = $form.serialize() + '&action=paymentio_save_card_gateway_settings&nonce=' + paymentioData.nonce;

        $.ajax({
            url: paymentioData.ajax_url,
            type: 'POST',
            data: postData,
            success: function (res) {
                $btn.prop('disabled', false).text('ذخیره تنظیمات کارت به کارت');
                if (res.success) {
                    $form.closest('.paymentio-modal').fadeOut(200);
                    window.location.reload();
                } else {
                    alert(res.data.message || 'خطا در ذخیره سازی اطلاعات');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('ذخیره تنظیمات کارت به کارت');
                alert('خطا در برقراری ارتباط با سرور');
            }
        });
    });

    // 10. Add New Bank Card Row Repeater
    $(document).on('click', '.paymentio-add-card-row', function (e) {
        e.preventDefault();
        var $container = $('#paymentio-cards-container');
        var newIndex = $container.find('.paymentio-card-row').length;

        var html = '<div class="paymentio-card-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 12px; position: relative;">' +
            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">' +
            '<strong style="font-size: 13px; color: #0284c7;">کارت بانکی جدید</strong>' +
            '<button type="button" class="paymentio-remove-card-row" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 12px; font-weight: bold;">حذف این کارت &times;</button>' +
            '</div>' +
            '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">' +
            '<div>' +
            '<label style="font-size: 12px; display: block; margin-bottom: 4px;">انتخاب بانک:</label>' +
            '<select name="cards[' + newIndex + '][bank_slug]" class="paymentio-input paymentio-bank-select">' +
            '<option value="saman" data-name="بانک سامان">بانک سامان</option>' +
            '<option value="melli" data-name="بانک ملی ایران">بانک ملی ایران</option>' +
            '<option value="mellat" data-name="بانک ملت">بانک ملت</option>' +
            '<option value="saderat" data-name="بانک صادرات ایران">بانک صادرات ایران</option>' +
            '<option value="tejarat" data-name="بانک تجارت">بانک تجارت</option>' +
            '<option value="pasargad" data-name="بانک پاسارگاد">بانک پاسارگاد</option>' +
            '<option value="parsian" data-name="بانک پارسیان">بانک پارسیان</option>' +
            '<option value="blubank" data-name="بلوبانک (سامان)">بلوبانک (سامان)</option>' +
            '<option value="resalat" data-name="بانک قرض الحسنه رسالت">بانک قرض الحسنه رسالت</option>' +
            '<option value="mehr_iran" data-name="بانک قرض الحسنه مهر ایران">بانک قرض الحسنه مهر ایران</option>' +
            '<option value="ayandeh" data-name="بانک آینده">بانک آینده</option>' +
            '<option value="sepah" data-name="بانک سپه">بانک سپه</option>' +
            '<option value="keshavarzi" data-name="بانک کشاورزی">بانک کشاورزی</option>' +
            '<option value="maskan" data-name="بانک مسکن">بانک مسکن</option>' +
            '<option value="refah" data-name="بانک رفاه کارگران">بانک رفاه کارگران</option>' +
            '<option value="shahr" data-name="بانک شهر">بانک شهر</option>' +
            '<option value="sina" data-name="بانک سینا">بانک سینا</option>' +
            '<option value="other" data-name="سایر بانک ها">سایر بانک ها</option>' +
            '</select>' +
            '<input type="hidden" name="cards[' + newIndex + '][bank_name]" class="paymentio-bank-name-hidden" value="بانک سامان" />' +
            '</div>' +
            '<div>' +
            '<label style="font-size: 12px; display: block; margin-bottom: 4px;">شماره کارت (۱۶ رقم):</label>' +
            '<input type="text" name="cards[' + newIndex + '][card_number]" value="" class="paymentio-input paymentio-card-num-input" placeholder="۶۲۱۹۸۶۱۰..." maxlength="19" dir="ltr" />' +
            '</div>' +
            '</div>' +
            '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">' +
            '<div>' +
            '<label style="font-size: 12px; display: block; margin-bottom: 4px;">نام صاحب حساب:</label>' +
            '<input type="text" name="cards[' + newIndex + '][card_holder]" value="" class="paymentio-input" placeholder="نام و نام خانوادگی دارنده کارت" />' +
            '</div>' +
            '<div>' +
            '<label style="font-size: 12px; display: block; margin-bottom: 4px;">شماره شبا (اختیاری):</label>' +
            '<input type="text" name="cards[' + newIndex + '][shaba]" value="" class="paymentio-input" placeholder="IR..." dir="ltr" />' +
            '</div>' +
            '</div>' +
            '</div>';

        $container.append(html);
    });

    // 11. Remove Card Row
    $(document).on('click', '.paymentio-remove-card-row', function (e) {
        e.preventDefault();
        $(this).closest('.paymentio-card-row').remove();
    });

    // 12. Auto Detect Bank on Card Number Input
    $(document).on('input', '.paymentio-card-num-input', function () {
        var val = $(this).val().replace(/\D/g, '');
        if (val.length >= 6) {
            var bin = val.substring(0, 6);
            var $row = $(this).closest('.paymentio-card-row');
            var $select = $row.find('.paymentio-bank-select');

            var binMap = {
                '603799': 'melli',
                '610433': 'mellat',
                '991975': 'mellat',
                '603769': 'saderat',
                '585983': 'tejarat',
                '627353': 'tejarat',
                '589210': 'sepah',
                '603770': 'keshavarzi',
                '628023': 'maskan',
                '621986': 'saman',
                '502229': 'pasargad',
                '622106': 'parsian',
                '636214': 'ayandeh',
                '504706': 'shahr',
                '639346': 'sina',
                '589463': 'refah',
                '606373': 'mehr_iran',
                '504172': 'resalat'
            };

            if (binMap[bin]) {
                $select.val(binMap[bin]);
                var bName = $select.find('option:selected').data('name');
                $row.find('.paymentio-bank-name-hidden').val(bName);
            }
        }
    });
});
