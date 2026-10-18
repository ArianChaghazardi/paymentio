/**
 * Paymentio Frontend Checkout JavaScript
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

jQuery(document).ready(function ($) {
    'use strict';

    $(document).on('change', '.paymentio-badge-card input[type="radio"]', function () {
        $('.paymentio-badge-card').removeClass('active');
        $(this).closest('.paymentio-badge-card').addClass('active');
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
});
