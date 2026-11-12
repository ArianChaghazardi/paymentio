<?php
/**
 * Paymentio Dedicated Card to Card Payment and Receipt Upload Page
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$order_id   = $order->get_id();
$order_key  = $order->get_order_key();
$total_toman = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
$total_rial  = Paymentio_Helper::to_rial( $order->get_total(), $order->get_currency() );
$receipt    = Paymentio_DB::get_receipt_by_order( $order_id );

$card_gateway = new Paymentio_Gateway_Card();
$cards        = $card_gateway->get_configured_cards();
$rules_text   = $card_gateway->rules_notes ?: Paymentio_Helper::get_default_card_rules();
$max_file_mb  = $card_gateway->max_file_size ?: 5;
$site_title   = get_bloginfo( 'name' );
$site_url     = home_url( '/' );
$items_count  = $order->get_item_count();

// Check if re-upload allowed (either no receipt or previous receipt was rejected)
$can_upload = ( ! $receipt || 'rejected' === $receipt->status );
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت کارت به کارت | سفارش شماره <?php echo esc_html( Paymentio_Helper::to_persian_digits( $order->get_order_number() ) ); ?> - <?php echo esc_html( $site_title ); ?></title>
    <link rel="stylesheet" href="<?php echo esc_url( PAYMENTIO_URL . 'assets/css/checkout.css?v=' . PAYMENTIO_VERSION ); ?>">
    <style>
        :root {
            --p-bg: #f8fafc;
            --p-card-bg: #ffffff;
            --p-primary: #0284c7;
            --p-primary-hover: #0369a1;
            --p-text-main: #0f172a;
            --p-text-muted: #64748b;
            --p-border: #e2e8f0;
            --p-success: #16a34a;
            --p-warning: #d97706;
            --p-danger: #dc2626;
            --p-radius: 16px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
            color: var(--p-text-main);
            direction: rtl;
            text-align: right;
            min-height: 100vh;
            padding: 24px 16px 48px 16px;
            line-height: 1.6;
        }

        .paymentio-container {
            max-width: 680px;
            margin: 0 auto;
        }

        /* Top Brand Bar */
        .paymentio-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 0 4px;
        }

        .paymentio-site-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--p-text-main);
            font-size: 1.05rem;
            font-weight: 800;
        }

        .paymentio-back-link {
            font-size: 0.88rem;
            color: var(--p-primary);
            text-decoration: none;
            font-weight: 600;
        }
        .paymentio-back-link:hover {
            text-decoration: underline;
        }

        /* Main Card */
        .paymentio-main-card {
            background: var(--p-card-bg);
            border-radius: var(--p-radius);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            border: 1px solid var(--p-border);
            overflow: hidden;
            margin-bottom: 24px;
        }

        /* Header */
        .paymentio-card-head {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .paymentio-head-title h1 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #ffffff;
        }

        .paymentio-head-title p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin: 0;
        }

        .paymentio-order-pill {
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.35);
            color: #38bdf8;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Body Sections */
        .paymentio-card-content {
            padding: 28px;
        }

        /* Amount Highlight Box */
        .paymentio-amount-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1.5px solid #bae6fd;
            border-radius: 14px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }

        .paymentio-amount-meta span {
            display: block;
            font-size: 0.85rem;
            color: #0369a1;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .paymentio-amount-value {
            font-size: 1.7rem;
            font-weight: 900;
            color: #0c4a6e;
            line-height: 1.2;
        }

        .paymentio-amount-value small {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0284c7;
            margin-right: 6px;
        }

        .paymentio-amount-rial {
            display: block;
            font-size: 0.82rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* Copy Button */
        .paymentio-copy-btn {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #1e293b;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .paymentio-copy-btn:hover {
            border-color: var(--p-primary);
            color: var(--p-primary);
            background: #f8fafc;
        }
        .paymentio-copy-btn.copied {
            background: #16a34a !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
        }

        /* Bank Account Cards */
        .paymentio-section-heading {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .paymentio-bank-card-container {
            margin-bottom: 24px;
        }

        .paymentio-bank-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0369a1 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 24px 26px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .paymentio-bank-card::after {
            content: '';
            position: absolute;
            top: -60px;
            left: -60px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .paymentio-bank-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .paymentio-bank-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .paymentio-bank-chip {
            width: 44px;
            height: 32px;
            background: linear-gradient(135deg, #fde047 0%, #ca8a04 100%);
            border-radius: 6px;
            border: 1px solid #a16207;
            position: relative;
        }

        .paymentio-card-number-row {
            margin-bottom: 18px;
            direction: ltr;
            text-align: center;
        }

        .paymentio-card-digits {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: 3px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            user-select: all;
        }

        .paymentio-bank-card-bottom {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .paymentio-holder-info span {
            display: block;
            font-size: 0.78rem;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .paymentio-holder-info strong {
            font-size: 1rem;
            color: #ffffff;
            font-weight: 700;
        }

        .paymentio-card-actions {
            display: flex;
            gap: 8px;
        }

        .paymentio-card-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .paymentio-card-btn:hover {
            background: rgba(255, 255, 255, 0.28);
        }

        .paymentio-shaba-box {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 12px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            direction: ltr;
            font-family: monospace;
        }

        /* Rules Accordion / Box */
        .paymentio-rules-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }

        .paymentio-rules-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #92400e;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .paymentio-rules-content {
            font-size: 0.86rem;
            color: #78350f;
            line-height: 1.7;
            white-space: pre-line;
        }

        /* Receipt Status Display (when already submitted) */
        .paymentio-status-card {
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 24px;
            border: 1.5px solid transparent;
        }

        .paymentio-status-card.pending {
            background: #fefce8;
            border-color: #fde047;
            color: #713f12;
        }

        .paymentio-status-card.approved {
            background: #f0fdf4;
            border-color: #86efac;
            color: #14532d;
        }

        .paymentio-status-card.rejected {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #7f1d1d;
        }

        .paymentio-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 0.88rem;
            margin-bottom: 12px;
        }

        .paymentio-status-card.pending .paymentio-status-badge {
            background: #fef08a;
            color: #854d0e;
        }
        .paymentio-status-card.approved .paymentio-status-badge {
            background: #bbf7d0;
            color: #166534;
        }
        .paymentio-status-card.rejected .paymentio-status-badge {
            background: #fecaca;
            color: #991b1b;
        }

        .paymentio-submitted-receipt-preview {
            margin-top: 16px;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .paymentio-receipt-thumb {
            width: 110px;
            height: 110px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .paymentio-receipt-thumb:hover {
            transform: scale(1.04);
        }

        /* Upload Form */
        .paymentio-upload-section {
            background: #ffffff;
            border: 1.5px dashed #94a3b8;
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
            margin-bottom: 20px;
        }

        .paymentio-upload-section:hover,
        .paymentio-upload-section.dragover {
            border-color: var(--p-primary);
            background: #f0f9ff;
        }

        .paymentio-upload-icon {
            width: 54px;
            height: 54px;
            background: #e0f2fe;
            color: var(--p-primary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .paymentio-upload-title {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .paymentio-upload-hint {
            font-size: 0.82rem;
            color: #64748b;
        }

        .paymentio-file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Image Preview Box */
        .paymentio-preview-box {
            display: none;
            margin-top: 16px;
            padding: 14px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--p-border);
            text-align: right;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .paymentio-preview-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .paymentio-preview-img {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
        }

        .paymentio-preview-meta strong {
            display: block;
            font-size: 0.88rem;
            color: #0f172a;
        }

        .paymentio-preview-meta span {
            font-size: 0.78rem;
            color: #64748b;
        }

        .paymentio-remove-file-btn {
            background: transparent;
            border: none;
            color: var(--p-danger);
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            padding: 4px 8px;
        }

        /* Form Inputs */
        .paymentio-form-group {
            margin-bottom: 16px;
        }

        .paymentio-form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        .paymentio-form-label small {
            font-weight: 400;
            color: #94a3b8;
        }

        .paymentio-form-input,
        .paymentio-form-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--p-border);
            border-radius: 10px;
            font-size: 0.92rem;
            color: var(--p-text-main);
            background: #ffffff;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .paymentio-form-input:focus,
        .paymentio-form-textarea:focus {
            outline: none;
            border-color: var(--p-primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        /* Submit Button */
        .paymentio-submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .paymentio-submit-btn:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            transform: translateY(-1px);
        }
        .paymentio-submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal for full size view */
        .paymentio-modal-lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.85);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .paymentio-modal-lightbox img {
            max-width: 90%;
            max-height: 85vh;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }
        .paymentio-modal-close-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #ffffff;
            font-size: 32px;
            cursor: pointer;
            background: none;
            border: none;
        }
    </style>
</head>
<body>

<div class="paymentio-container">
    <!-- Top Bar -->
    <div class="paymentio-top-bar">
        <a href="<?php echo esc_url( $site_url ); ?>" class="paymentio-site-link">
            <span><?php echo esc_html( $site_title ); ?></span>
        </a>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="paymentio-back-link">
            حساب کاربری و سفارش ها &larr;
        </a>
    </div>

    <!-- Main Card -->
    <div class="paymentio-main-card">
        <!-- Header -->
        <div class="paymentio-card-head">
            <div class="paymentio-head-title">
                <h1>پرداخت کارت به کارت</h1>
                <p>سفارش شما ثبت شد. لطفاً اطلاعات واریز را در فرم زیر تکمیل نمایید.</p>
            </div>
            <div class="paymentio-order-pill">
                سفارش #<?php echo esc_html( Paymentio_Helper::to_persian_digits( $order->get_order_number() ) ); ?>
            </div>
        </div>

        <div class="paymentio-card-content">
            <!-- Amount Highlight Box -->
            <div class="paymentio-amount-card">
                <div class="paymentio-amount-meta">
                    <span>مبلغ دقیق قابل پرداخت:</span>
                    <div class="paymentio-amount-value">
                        <?php echo esc_html( Paymentio_Helper::to_persian_digits( number_format( $total_toman ) ) ); ?>
                        <small>تومان</small>
                    </div>
                    <span class="paymentio-amount-rial">
                        معادل <?php echo esc_html( Paymentio_Helper::to_persian_digits( number_format( $total_rial ) ) ); ?> ریال
                    </span>
                </div>
                <button type="button" class="paymentio-copy-btn" data-copy="<?php echo esc_attr( $total_toman ); ?>">
                    <?php echo Paymentio_Helper::icon( 'copy', 16 ); ?>
                    <span>کپی مبلغ (تومان)</span>
                </button>
            </div>

            <!-- Bank Accounts Cards -->
            <div class="paymentio-bank-card-container">
                <div class="paymentio-section-heading">
                    <?php echo Paymentio_Helper::icon( 'card', 18 ); ?>
                    <span>اطلاعات حساب و شماره کارت مقصد:</span>
                </div>

                <?php foreach ( $cards as $idx => $card ) :
                    $formatted_card = Paymentio_Helper::format_card_number( $card['card_number'] );
                    $raw_card       = $card['card_number'];
                ?>
                    <div class="paymentio-bank-card" style="<?php echo $idx > 0 ? 'margin-top: 16px;' : ''; ?>">
                        <div class="paymentio-bank-card-top">
                            <div class="paymentio-bank-name">
                                <?php echo Paymentio_Helper::icon( 'bank', 22 ); ?>
                                <span><?php echo esc_html( $card['bank_name'] ); ?></span>
                            </div>
                            <div class="paymentio-bank-chip"></div>
                        </div>

                        <div class="paymentio-card-number-row">
                            <div class="paymentio-card-digits">
                                <?php echo esc_html( $formatted_card ); ?>
                            </div>
                        </div>

                        <div class="paymentio-bank-card-bottom">
                            <div class="paymentio-holder-info">
                                <span>صاحب حساب:</span>
                                <strong><?php echo esc_html( $card['card_holder'] ?: 'فروشگاه' ); ?></strong>
                            </div>

                            <div class="paymentio-card-actions">
                                <button type="button" class="paymentio-card-btn" data-copy="<?php echo esc_attr( $raw_card ); ?>">
                                    <?php echo Paymentio_Helper::icon( 'copy', 14 ); ?>
                                    <span>کپی شماره کارت</span>
                                </button>
                            </div>
                        </div>

                        <?php if ( ! empty( $card['shaba'] ) ) : ?>
                            <div class="paymentio-shaba-box">
                                <span><?php echo esc_html( $card['shaba'] ); ?></span>
                                <button type="button" class="paymentio-copy-btn" style="padding: 4px 8px; font-size: 0.75rem;" data-copy="<?php echo esc_attr( $card['shaba'] ); ?>">
                                    کپی شبا
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Rules & Guidance Box -->
            <div class="paymentio-rules-box">
                <div class="paymentio-rules-title">
                    <?php echo Paymentio_Helper::icon( 'info', 18 ); ?>
                    <span>نکات و راهنمای مهم پرداخت:</span>
                </div>
                <div class="paymentio-rules-content"><?php echo esc_html( $rules_text ); ?></div>
            </div>

            <!-- Receipt Status (if already submitted) -->
            <?php if ( $receipt ) : ?>
                <div class="paymentio-status-card <?php echo esc_attr( $receipt->status ); ?>">
                    <div class="paymentio-status-badge">
                        <?php if ( 'approved' === $receipt->status ) : ?>
                            <?php echo Paymentio_Helper::icon( 'success', 16 ); ?>
                            <span>وضعیت رسید: تایید شده</span>
                        <?php elseif ( 'rejected' === $receipt->status ) : ?>
                            <?php echo Paymentio_Helper::icon( 'dismiss', 16 ); ?>
                            <span>وضعیت رسید: تایید نشد (رد شده)</span>
                        <?php else : ?>
                            <?php echo Paymentio_Helper::icon( 'clock', 16 ); ?>
                            <span>وضعیت رسید: در انتظار بررسی و تایید مدیریت</span>
                        <?php endif; ?>
                    </div>

                    <?php if ( 'approved' === $receipt->status ) : ?>
                        <p style="font-size: 0.9rem; margin-bottom: 12px;">
                            پرداخت شما با موفقیت تایید شد! سفارش شما فعال شده و محصول یا دسترسی به فایل های دانلودی به حساب شما تحویل گردید.
                        </p>
                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="paymentio-submit-btn" style="text-decoration: none; display: inline-flex; width: auto; padding: 10px 22px; font-size: 0.95rem;">
                            مشاهده سفارش و دانلود محصول
                        </a>
                    <?php elseif ( 'rejected' === $receipt->status ) : ?>
                        <p style="font-size: 0.9rem; margin-bottom: 6px;">
                            متاسفانه رسید بارگذاری شده تایید نشد.
                        </p>
                        <?php if ( ! empty( $receipt->rejection_reason ) ) : ?>
                            <div style="background: rgba(220, 38, 38, 0.1); padding: 10px 14px; border-radius: 8px; font-size: 0.88rem; font-weight: 700; margin-bottom: 14px;">
                                علت رد: <?php echo esc_html( $receipt->rejection_reason ); ?>
                            </div>
                        <?php endif; ?>
                        <p style="font-size: 0.88rem; color: #7f1d1d; margin-bottom: 14px;">
                            لطفاً تصویر صحیح یا جدید رسید واریز را از طریق فرم زیر ارسال فرمایید:
                        </p>
                    <?php else : ?>
                        <p style="font-size: 0.9rem; line-height: 1.7;">
                            رسید شما دریافت شد و هم اکنون در صف بررسی توسط بخش مالی قرار دارد. پس از تایید رسید، سفارش شما تکمیل شده و دسترسی لازم فعال می گردد.
                        </p>
                    <?php endif; ?>

                    <!-- Submitted Details Preview -->
                    <div class="paymentio-submitted-receipt-preview">
                        <?php if ( ! empty( $receipt->receipt_url ) ) : ?>
                            <img src="<?php echo esc_url( $receipt->receipt_url ); ?>" alt="رسید پرداختی" class="paymentio-receipt-thumb" onclick="openLightbox('<?php echo esc_url( $receipt->receipt_url ); ?>')" title="برای بزرگنمایی کلیک کنید" />
                        <?php endif; ?>

                        <div style="font-size: 0.85rem;">
                            <?php if ( ! empty( $receipt->tracking_code ) ) : ?>
                                <div>کد پیگیری: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->tracking_code ) ); ?></strong></div>
                            <?php endif; ?>
                            <?php if ( ! empty( $receipt->payer_card ) ) : ?>
                                <div>کارت مبدا: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->payer_card ) ); ?></strong></div>
                            <?php endif; ?>
                            <div>تاریخ ثبت رسید: <?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->created_at ) ); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Receipt Upload Form (when not uploaded yet or rejected) -->
            <?php if ( $can_upload ) : ?>
                <form id="paymentio_receipt_form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( add_query_arg( array( 'paymentio_action' => 'card_pay', 'order_id' => $order_id, 'order_key' => $order_key ), home_url( '/' ) ) ); ?>">
                    <?php wp_nonce_field( 'paymentio_upload_receipt_' . $order_id, 'paymentio_receipt_nonce' ); ?>
                    <input type="hidden" name="paymentio_submit_receipt" value="1">
                    <input type="hidden" name="card_number" value="<?php echo esc_attr( $cards[0]['card_number'] ?? '' ); ?>">
                    <input type="hidden" name="card_holder" value="<?php echo esc_attr( $cards[0]['card_holder'] ?? '' ); ?>">
                    <input type="hidden" name="bank_name" value="<?php echo esc_attr( $cards[0]['bank_name'] ?? '' ); ?>">

                    <!-- File Upload Dropzone -->
                    <div class="paymentio-upload-section" id="dropzone">
                        <div class="paymentio-upload-icon">
                            <?php echo Paymentio_Helper::icon( 'upload', 26 ); ?>
                        </div>
                        <div class="paymentio-upload-title">بارگذاری اسکرین شات یا تصویر رسید پرداخت</div>
                        <div class="paymentio-upload-hint">
                            کلیک کنید یا فایل تصویر را بکشید و در این قسمت رها کنید (فرمت های مجاز: JPG, PNG, WEBP - حداکثر <?php echo esc_html( Paymentio_Helper::to_persian_digits( $max_file_mb ) ); ?> مگابایت)
                        </div>
                        <input type="file" name="receipt_image" id="receipt_image" class="paymentio-file-input" accept="image/jpeg,image/png,image/webp" required>
                    </div>

                    <!-- Dynamic Preview Box -->
                    <div class="paymentio-preview-box" id="preview_box">
                        <div class="paymentio-preview-left">
                            <img src="" alt="پیش نمایش" id="preview_img" class="paymentio-preview-img">
                            <div class="paymentio-preview-meta">
                                <strong id="preview_filename">فایل انتخاب شد</strong>
                                <span id="preview_filesize">۰ کیلوبایت</span>
                            </div>
                        </div>
                        <button type="button" class="paymentio-remove-file-btn" id="remove_file_btn">حذف / تغییر تصویر</button>
                    </div>

                    <!-- Tracking Code Field -->
                    <div class="paymentio-form-group">
                        <label class="paymentio-form-label" for="tracking_code">
                            شماره پیگیری / کد ارجاع بانکی:
                            <small>(موجود در رسید یا پیامک تراکنش)</small>
                        </label>
                        <input type="text" name="tracking_code" id="tracking_code" class="paymentio-form-input" placeholder="مثال: ۱۲۳۴۵۶۷۸" dir="ltr">
                    </div>

                    <!-- Payer Card 4 Digits -->
                    <div class="paymentio-form-group">
                        <label class="paymentio-form-label" for="payer_card">
                            چهار رقم آخر کارت واریز کننده:
                            <small>(اختیاری جهت تطبیق سریع تر)</small>
                        </label>
                        <input type="text" name="payer_card" id="payer_card" class="paymentio-form-input" placeholder="مثال: ۵۶۷۸" maxlength="4" dir="ltr">
                    </div>

                    <!-- Customer Note -->
                    <div class="paymentio-form-group">
                        <label class="paymentio-form-label" for="customer_note">
                            یادداشت اختیاری:
                        </label>
                        <textarea name="customer_note" id="customer_note" class="paymentio-form-textarea" rows="2" placeholder="اگر توضیح یا پیامی درباره این پرداخت دارید اینجا بنویسید..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="paymentio-submit-btn" id="submit_btn">
                        <?php echo Paymentio_Helper::icon( 'check-simple', 20 ); ?>
                        <span id="btn_text">تایید و ارسال رسید پرداخت</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Lightbox for Full Size View -->
<div class="paymentio-modal-lightbox" id="lightbox" onclick="closeLightbox()">
    <button type="button" class="paymentio-modal-close-btn" onclick="closeLightbox()">&times;</button>
    <img src="" id="lightbox_img" alt="رسید کامل" onclick="event.stopPropagation()">
</div>

<script>
    // Copy to clipboard helper
    document.querySelectorAll('[data-copy]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var text = this.getAttribute('data-copy');
            if (!text) return;

            var self = this;
            var originalHtml = self.innerHTML;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    self.classList.add('copied');
                    self.innerHTML = '<span>کپی شد! ✓</span>';
                    setTimeout(function() {
                        self.classList.remove('copied');
                        self.innerHTML = originalHtml;
                    }, 2000);
                });
            } else {
                var input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);

                self.classList.add('copied');
                self.innerHTML = '<span>کپی شد! ✓</span>';
                setTimeout(function() {
                    self.classList.remove('copied');
                    self.innerHTML = originalHtml;
                }, 2000);
            }
        });
    });

    // File Preview Logic
    var fileInput = document.getElementById('receipt_image');
    var dropzone = document.getElementById('dropzone');
    var previewBox = document.getElementById('preview_box');
    var previewImg = document.getElementById('preview_img');
    var previewFilename = document.getElementById('preview_filename');
    var previewFilesize = document.getElementById('preview_filesize');
    var removeBtn = document.getElementById('remove_file_btn');
    var maxBytes = <?php echo (int) ( $max_file_mb * 1024 * 1024 ); ?>;

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            handleFile(this.files[0]);
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                fileInput.value = '';
                previewBox.style.display = 'none';
                dropzone.style.display = 'block';
            });
        }
    }

    function handleFile(file) {
        if (!file) return;

        // Size check
        if (file.size > maxBytes) {
            alert('حجم فایل بیشتر از حد مجاز است. لطفاً فایلی با حجم کمتر از <?php echo esc_js( $max_file_mb ); ?> مگابایت انتخاب فرمایید.');
            fileInput.value = '';
            return;
        }

        // Type check
        var validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (validTypes.indexOf(file.type) === -1) {
            alert('فرمت فایل مجاز نیست. لطفاً یک تصویر با فرمت JPG، PNG یا WEBP انتخاب فرمایید.');
            fileInput.value = '';
            return;
        }

        // Display preview
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewFilename.textContent = file.name;
            var sizeKb = Math.round(file.size / 1024);
            var sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' مگابایت' : sizeKb + ' کیلوبایت';
            previewFilesize.textContent = sizeStr;

            dropzone.style.display = 'none';
            previewBox.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    // Form Submission UI
    var form = document.getElementById('paymentio_receipt_form');
    var submitBtn = document.getElementById('submit_btn');
    var btnText = document.getElementById('btn_text');

    if (form) {
        form.addEventListener('submit', function() {
            if (submitBtn) {
                submitBtn.disabled = true;
                btnText.textContent = 'در حال ارسال و ثبت رسید... لطفاً شکیبا باشید';
            }
        });
    }

    // Lightbox modal
    function openLightbox(url) {
        var lb = document.getElementById('lightbox');
        var img = document.getElementById('lightbox_img');
        img.src = url;
        lb.style.display = 'flex';
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
    }
</script>

</body>
</html>
