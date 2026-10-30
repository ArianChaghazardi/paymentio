/**
 * Paymentio Frontend Checkout JavaScript
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

jQuery(document).ready(function ($) {
    'use strict';

    function restoreSelectedSubgateway() {
        var saved = sessionStorage.getItem('paymentio_selected_subgateway');
        if (saved) {
            var $target = $('.paymentio-badge-card input[type="radio"][value="' + saved + '"]');
            if ($target.length) {
                $('.paymentio-badge-card input[type="radio"]').prop('checked', false);
                $('.paymentio-badge-card').removeClass('active');
                $target.prop('checked', true);
                $target.closest('.paymentio-badge-card').addClass('active');
            }
        }
    }

    $(document).on('change', '.paymentio-badge-card input[type="radio"]', function () {
        $('.paymentio-badge-card').removeClass('active');
        $(this).closest('.paymentio-badge-card').addClass('active');
        var val = $(this).val();
        if (val) {
            sessionStorage.setItem('paymentio_selected_subgateway', val);
        }
    });

    $(document).on('click', '.paymentio-badge-card', function (e) {
        if ($(e.target).is('input[type="radio"]')) {
            return;
        }
        var $radio = $(this).find('input[type="radio"]');
        if (!$radio.is(':checked')) {
            $radio.prop('checked', true).trigger('change');
        }
    });

    $(document.body).on('updated_checkout', function () {
        restoreSelectedSubgateway();
    });

    restoreSelectedSubgateway();
});
