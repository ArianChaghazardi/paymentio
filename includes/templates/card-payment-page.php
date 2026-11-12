<?php
/**
 * Paymentio Dedicated Card to Card Payment and Receipt Portal (Widescreen No-Scroll Layout)
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$order_id    = $order->get_id();
$order_key   = $order->get_order_key();
$total_toman = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
$total_rial  = Paymentio_Helper::to_rial( $order->get_total(), $order->get_currency() );
$receipt     = Paymentio_DB::get_receipt_by_order( $order_id );

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
        /* 1. Local Vazirmatn Font Face */
        @font-face {
            font-family: 'Vazirmatn';
            src: url('<?php echo esc_url( PAYMENTIO_URL . 'assets/fonts/Vazirmatn-Regular.woff2' ); ?>') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('<?php echo esc_url( PAYMENTIO_URL . 'assets/fonts/Vazirmatn-Medium.woff2' ); ?>') format('woff2');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('<?php echo esc_url( PAYMENTIO_URL . 'assets/fonts/Vazirmatn-Bold.woff2' ); ?>') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('<?php echo esc_url( PAYMENTIO_URL . 'assets/fonts/Vazirmatn-Black.woff2' ); ?>') format('woff2');
            font-weight: 900;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --p-bg: #f8fafc;
            --p-card-bg: #ffffff;
            --p-primary: #0284c7;
            --p-primary-hover: #0369a1;
            --p-primary-light: #e0f2fe;
            --p-text-main: #0f172a;
            --p-text-muted: #64748b;
            --p-border: #e2e8f0;
            --p-border-focus: #38bdf8;
            --p-success: #16a34a;
            --p-success-bg: #f0fdf4;
            --p-success-border: #86efac;
            --p-warning: #d97706;
            --p-warning-bg: #fefce8;
            --p-warning-border: #fde047;
            --p-danger: #dc2626;
            --p-danger-bg: #fef2f2;
            --p-danger-border: #fca5a5;
            --p-radius: 14px;
            --p-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            min-height: 100vh;
        }

        body, button, input, select, textarea {
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        body {
            background: radial-gradient(circle at 100% 0%, #f1f5f9 0%, #f8fafc 50%, #e2e8f0 100%);
            color: var(--p-text-main);
            direction: rtl;
            text-align: right;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
        }

        .paymentio-page-wrapper {
            width: 100%;
            max-width: 1380px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Top Bar */
        .paymentio-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 18px;
            background: #ffffff;
            border: 1px solid var(--p-border);
            border-radius: var(--p-radius);
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            flex-wrap: wrap;
            gap: 10px;
        }

        .paymentio-brand-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .paymentio-site-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--p-text-main);
            font-size: 1.05rem;
            font-weight: 800;
        }

        .paymentio-brand-badge {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            border: 1px solid #bae6fd;
        }

        .paymentio-top-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .paymentio-order-pill {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .paymentio-back-link {
            font-size: 0.85rem;
            color: var(--p-primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s;
        }
        .paymentio-back-link:hover {
            color: var(--p-primary-hover);
            text-decoration: underline;
        }

        /* 2-Column Portal Grid */
        .paymentio-portal-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 20px;
            align-items: start;
            flex: 1;
            margin-bottom: 16px;
        }

        .paymentio-col-right,
        .paymentio-col-left {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* Amount Card */
        .paymentio-amount-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1.5px solid #bae6fd;
            border-radius: var(--p-radius);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(2, 132, 199, 0.08);
        }

        .paymentio-amount-meta span {
            display: block;
            font-size: 0.8rem;
            color: #0369a1;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .paymentio-amount-value {
            font-size: 1.55rem;
            font-weight: 900;
            color: #0c4a6e;
            line-height: 1.15;
        }

        .paymentio-amount-value small {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0284c7;
            margin-right: 6px;
        }

        .paymentio-amount-rial {
            display: block;
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 2px;
        }

        /* Copy Button */
        .paymentio-copy-btn {
            font-family: 'Vazirmatn', sans-serif !important;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #1e293b;
            padding: 7px 13px;
            border-radius: 9px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            white-space: nowrap;
            user-select: none;
        }
        .paymentio-copy-btn span {
            font-family: 'Vazirmatn', sans-serif !important;
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

        /* Bank Card Panel */
        .paymentio-card-panel {
            background: #ffffff;
            border: 1px solid var(--p-border);
            border-radius: var(--p-radius);
            padding: 16px 18px;
            box-shadow: var(--p-shadow);
        }

        .paymentio-card-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .paymentio-panel-title {
            font-size: 0.88rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .paymentio-card-tabs {
            display: flex;
            gap: 6px;
            overflow-x: auto;
        }

        .paymentio-card-tab {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 4px 10px;
            border-radius: 7px;
            font-size: 0.76rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .paymentio-card-tab.active {
            background: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
        }

        /* Realistic Bank Card */
        .paymentio-bank-card {
            background: linear-gradient(135deg, #090e17 0%, #0f1f38 45%, #1e3a8a 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 16px 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 24px -5px rgba(2, 132, 199, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .paymentio-bank-card::after {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, transparent 70%);
            pointer-events: none;
        }

        .paymentio-bank-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .paymentio-bank-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            font-weight: 800;
            color: #ffffff;
        }

        .paymentio-chip-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .paymentio-bank-chip {
            width: 36px;
            height: 26px;
            background: linear-gradient(135deg, #fde047 0%, #ca8a04 100%);
            border-radius: 5px;
            border: 1px solid #a16207;
            position: relative;
        }

        .paymentio-contactless-icon {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.75);
        }

        .paymentio-card-number-row {
            margin-bottom: 12px;
            direction: ltr;
            text-align: center;
        }

        .paymentio-card-digits {
            font-family: 'Vazirmatn', sans-serif !important;
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.45);
            user-select: all;
        }

        .paymentio-bank-card-bottom {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .paymentio-holder-info span {
            display: block;
            font-size: 0.74rem;
            color: #94a3b8;
            margin-bottom: 1px;
        }

        .paymentio-holder-info strong {
            font-size: 0.92rem;
            color: #ffffff;
            font-weight: 700;
        }

        .paymentio-card-copy-btn {
            font-family: 'Vazirmatn', sans-serif !important;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        .paymentio-card-copy-btn span {
            font-family: 'Vazirmatn', sans-serif !important;
        }
        .paymentio-card-copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .paymentio-card-copy-btn.copied {
            background: #16a34a !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
        }

        .paymentio-shaba-box {
            background: #f8fafc;
            border: 1px solid var(--p-border);
            border-radius: 8px;
            padding: 6px 12px;
            margin-top: 10px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            direction: ltr;
            font-family: 'Vazirmatn', sans-serif !important;
        }
        .paymentio-shaba-box span {
            font-family: 'Vazirmatn', sans-serif !important;
        }

        /* Rules Box */
        .paymentio-rules-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: var(--p-radius);
            padding: 12px 16px;
        }

        .paymentio-rules-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 800;
            color: #92400e;
            margin-bottom: 6px;
        }

        .paymentio-rules-content {
            font-size: 0.78rem;
            color: #78350f;
            line-height: 1.6;
            white-space: pre-line;
        }

        /* Left Column Action / Upload Card */
        .paymentio-action-card {
            background: #ffffff;
            border: 1px solid var(--p-border);
            border-radius: var(--p-radius);
            padding: 18px 20px;
            box-shadow: var(--p-shadow);
        }

        .paymentio-action-head {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--p-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .paymentio-action-title h2 {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 2px 0;
        }

        .paymentio-action-title p {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
        }

        .paymentio-action-badge {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            white-space: nowrap;
        }

        /* Rejection Banner */
        .paymentio-rejection-banner {
            background: var(--p-danger-bg);
            border: 1px solid var(--p-danger-border);
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 12px;
        }

        .paymentio-rejection-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--p-danger);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .paymentio-rejection-reason {
            font-size: 0.8rem;
            color: #7f1d1d;
            font-weight: 600;
        }

        /* Status Cards for Submitted Receipts */
        .paymentio-status-card {
            border-radius: 12px;
            padding: 16px;
            border: 1.5px solid transparent;
        }

        .paymentio-status-card.pending {
            background: var(--p-warning-bg);
            border-color: var(--p-warning-border);
            color: #713f12;
        }

        .paymentio-status-card.approved {
            background: var(--p-success-bg);
            border-color: var(--p-success-border);
            color: #14532d;
        }

        .paymentio-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 0.82rem;
            margin-bottom: 10px;
        }

        .paymentio-status-card.pending .paymentio-status-badge {
            background: #fef08a;
            color: #854d0e;
        }
        .paymentio-status-card.approved .paymentio-status-badge {
            background: #bbf7d0;
            color: #166534;
        }

        .paymentio-submitted-receipt-preview {
            margin-top: 12px;
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
        }

        .paymentio-receipt-thumb {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .paymentio-receipt-thumb:hover {
            transform: scale(1.04);
        }

        /* Dropzone */
        .paymentio-dropzone {
            background: #f8fafc;
            border: 1.5px dashed #94a3b8;
            border-radius: 10px;
            padding: 12px 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            margin-bottom: 12px;
        }

        .paymentio-dropzone:hover,
        .paymentio-dropzone.dragover {
            border-color: var(--p-primary);
            background: #f0f9ff;
        }

        .paymentio-dropzone-icon {
            width: 36px;
            height: 36px;
            background: #e0f2fe;
            color: var(--p-primary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }

        .paymentio-dropzone-title {
            font-size: 0.88rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .paymentio-dropzone-hint {
            font-size: 0.75rem;
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

        /* Dynamic Preview Box */
        .paymentio-preview-box {
            display: none;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 9px;
            border: 1px solid var(--p-border);
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .paymentio-preview-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .paymentio-preview-img {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            flex-shrink: 0;
        }

        .paymentio-preview-meta {
            min-width: 0;
        }

        .paymentio-preview-meta strong {
            display: block;
            font-size: 0.82rem;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .paymentio-preview-meta span {
            font-size: 0.72rem;
            color: #64748b;
        }

        .paymentio-remove-file-btn {
            background: transparent;
            border: none;
            color: var(--p-danger);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            padding: 4px 6px;
            flex-shrink: 0;
        }

        /* 2-Column Form Row for Inputs */
        .paymentio-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .paymentio-form-group {
            margin-bottom: 10px;
        }

        .paymentio-form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
        }

        .paymentio-form-label small {
            font-weight: 400;
            color: #94a3b8;
        }

        .paymentio-form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--p-border);
            border-radius: 9px;
            font-size: 0.88rem;
            color: var(--p-text-main);
            background: #ffffff;
            box-sizing: border-box;
            transition: all 0.2s;
            font-family: inherit;
        }

        .paymentio-form-input:focus {
            outline: none;
            border-color: var(--p-primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        /* Submit Button */
        .paymentio-submit-btn {
            font-family: 'Vazirmatn', sans-serif !important;
            width: 100%;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            padding: 12px 18px;
            border: none;
            border-radius: 10px;
            font-size: 0.98rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-sizing: border-box;
        }
        .paymentio-submit-btn span {
            font-family: 'Vazirmatn', sans-serif !important;
        }
        .paymentio-submit-btn:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            transform: translateY(-1px);
        }
        .paymentio-submit-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .paymentio-security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.74rem;
            color: #64748b;
            margin-top: 8px;
        }

        /* Footer */
        .paymentio-footer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            font-size: 0.78rem;
            color: var(--p-text-muted);
            border-top: 1px solid var(--p-border);
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Lightbox Modal */
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

        /* Responsive Media Queries */
        @media (max-width: 959px) {
            .paymentio-portal-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .paymentio-page-wrapper {
                padding: 12px 14px;
            }
        }

        @media (max-width: 640px) {
            .paymentio-top-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .paymentio-top-meta {
                justify-content: space-between;
            }
            .paymentio-form-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .paymentio-card-digits {
                font-size: 1.25rem;
                letter-spacing: 2px;
            }
            .paymentio-amount-value {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>

<div class="paymentio-page-wrapper">
    <!-- Top Bar -->
    <header class="paymentio-top-bar">
        <div class="paymentio-brand-area">
            <a href="<?php echo esc_url( $site_url ); ?>" class="paymentio-site-link">
                <span><?php echo esc_html( $site_title ); ?></span>
            </a>
            <span class="paymentio-brand-badge">درگاه امن پرداخت کارت به کارت</span>
        </div>

        <div class="paymentio-top-meta">
            <div class="paymentio-order-pill">
                سفارش #<?php echo esc_html( Paymentio_Helper::to_persian_digits( $order->get_order_number() ) ); ?>
            </div>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="paymentio-back-link">
                حساب کاربری و سفارش ها &larr;
            </a>
        </div>
    </header>

    <!-- Widescreen 2-Column Portal Grid -->
    <main class="paymentio-portal-grid">
        <!-- Right Column: Amount & Bank Card Details -->
        <div class="paymentio-col-right" id="col_right">
            <!-- Amount Card -->
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
                    <?php echo Paymentio_Helper::icon( 'copy', 15 ); ?>
                    <span>کپی مبلغ (تومان)</span>
                </button>
            </div>

            <!-- Bank Card Presentation Panel -->
            <div class="paymentio-card-panel">
                <div class="paymentio-card-panel-header">
                    <div class="paymentio-panel-title">
                        <?php echo Paymentio_Helper::icon( 'card', 16 ); ?>
                        <span>اطلاعات کارت بانکی مقصد:</span>
                    </div>

                    <?php if ( count( $cards ) > 1 ) : ?>
                        <div class="paymentio-card-tabs" id="card_tabs">
                            <?php foreach ( $cards as $c_idx => $c_item ) : ?>
                                <button type="button" class="paymentio-card-tab <?php echo 0 === $c_idx ? 'active' : ''; ?>" data-index="<?php echo esc_attr( $c_idx ); ?>">
                                    <?php echo esc_html( $c_item['bank_name'] ); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                $first_card      = $cards[0] ?? array( 'bank_name' => 'بانک عضو شتاب', 'card_number' => '', 'card_holder' => '', 'shaba' => '' );
                $formatted_card  = Paymentio_Helper::format_card_number( $first_card['card_number'] );
                $raw_card        = $first_card['card_number'];
                $first_shaba     = isset( $first_card['shaba'] ) ? trim( (string) $first_card['shaba'] ) : '';
                $shaba_digits    = preg_replace( '/[^0-9]/', '', $first_shaba );
                $has_first_shaba = ! empty( $first_shaba ) && ! empty( $shaba_digits ) && ! preg_match( '/^0+$/', $shaba_digits ) && 'IR...' !== $first_shaba;
                ?>

                <!-- Realistic Bank Card Visual -->
                <div class="paymentio-bank-card" id="active_bank_card">
                    <div class="paymentio-bank-card-top">
                        <div class="paymentio-bank-brand" id="display_bank_name">
                            <?php echo Paymentio_Helper::icon( 'bank', 20 ); ?>
                            <span><?php echo esc_html( $first_card['bank_name'] ); ?></span>
                        </div>
                        <div class="paymentio-chip-wrapper">
                            <div class="paymentio-contactless-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8.5 16.5a5 5 0 0 1 0-7"></path>
                                    <path d="M12 19a8.5 8.5 0 0 0 0-12"></path>
                                    <path d="M15.5 21.5a12 12 0 0 0 0-17"></path>
                                </svg>
                            </div>
                            <div class="paymentio-bank-chip"></div>
                        </div>
                    </div>

                    <div class="paymentio-card-number-row">
                        <div class="paymentio-card-digits" id="display_card_digits">
                            <?php echo esc_html( $formatted_card ); ?>
                        </div>
                    </div>

                    <div class="paymentio-bank-card-bottom">
                        <div class="paymentio-holder-info">
                            <span>صاحب حساب:</span>
                            <strong id="display_card_holder"><?php echo esc_html( $first_card['card_holder'] ?: 'فروشگاه' ); ?></strong>
                        </div>

                        <button type="button" class="paymentio-card-copy-btn" id="btn_copy_card" data-copy="<?php echo esc_attr( $raw_card ); ?>">
                            <?php echo Paymentio_Helper::icon( 'copy', 14 ); ?>
                            <span>کپی شماره کارت</span>
                        </button>
                    </div>
                </div>

                <!-- Shaba Box -->
                <div class="paymentio-shaba-box" id="display_shaba_box" style="<?php echo $has_first_shaba ? 'display: flex;' : 'display: none;'; ?>">
                    <span id="display_shaba_val"><?php echo esc_html( $first_shaba ); ?></span>
                    <button type="button" class="paymentio-copy-btn" id="btn_copy_shaba" style="padding: 3px 8px; font-size: 0.74rem;" data-copy="<?php echo esc_attr( $first_shaba ); ?>">
                        کپی شماره شبا
                    </button>
                </div>
            </div>

            <!-- Guidelines and Rules -->
            <div class="paymentio-rules-box" id="rules_box">
                <div class="paymentio-rules-header">
                    <?php echo Paymentio_Helper::icon( 'info', 16 ); ?>
                    <span>راهنمای مهم انتقال و ثبت رسید:</span>
                </div>
                <div class="paymentio-rules-content"><?php echo esc_html( $rules_text ); ?></div>
            </div>
        </div>

        <!-- Left Column: Action / Receipt Submission Card -->
        <div class="paymentio-col-left" id="col_left">
            <div class="paymentio-action-card">
                <div class="paymentio-action-head">
                    <div class="paymentio-action-title">
                        <h2>ارسال رسید و اطلاعات واریز</h2>
                        <p>تصویر رسید و مشخصات انتقال را در فرم زیر وارد فرمایید</p>
                    </div>
                    <span class="paymentio-action-badge">مرحله نهایی: ثبت و تایید</span>
                </div>

                <!-- Receipt Status Card (when already submitted) -->
                <?php if ( $receipt ) : ?>
                    <?php if ( 'rejected' === $receipt->status ) : ?>
                        <!-- Compact Rejection Alert -->
                        <div class="paymentio-rejection-banner">
                            <div class="paymentio-rejection-title">
                                <?php echo Paymentio_Helper::icon( 'dismiss', 16 ); ?>
                                <span>رسید قبلی تایید نشد (رد شده)</span>
                            </div>
                            <?php if ( ! empty( $receipt->rejection_reason ) ) : ?>
                                <div class="paymentio-rejection-reason">
                                    علت رد: <?php echo esc_html( $receipt->rejection_reason ); ?>
                                </div>
                            <?php endif; ?>
                            <div style="font-size: 0.78rem; color: #991b1b; margin-top: 4px;">
                                لطفاً تصویر صحیح یا جدید رسید پرداخت را از طریق فرم زیر بارگذاری فرمایید:
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- Pending or Approved State Card -->
                        <div class="paymentio-status-card <?php echo esc_attr( $receipt->status ); ?>">
                            <div class="paymentio-status-badge">
                                <?php if ( 'approved' === $receipt->status ) : ?>
                                    <?php echo Paymentio_Helper::icon( 'success', 16 ); ?>
                                    <span>وضعیت رسید: تایید شده و سفارش تکمیل است</span>
                                <?php else : ?>
                                    <?php echo Paymentio_Helper::icon( 'clock', 16 ); ?>
                                    <span>وضعیت رسید: در صف بررسی و تایید واحد مالی</span>
                                <?php endif; ?>
                            </div>

                            <?php if ( 'approved' === $receipt->status ) : ?>
                                <p style="font-size: 0.88rem; margin-bottom: 12px; line-height: 1.6;">
                                    پرداخت شما با موفقیت تایید شد. سفارش شما فعال گردیده و دسترسی به سفارش یا فایل های خریداری شده به حساب کاربری تحویل شد.
                                </p>
                                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="paymentio-submit-btn" style="text-decoration: none; width: auto; display: inline-flex; padding: 10px 20px;">
                                    مشاهده سفارش و دانلود محصول
                                </a>
                            <?php else : ?>
                                <p style="font-size: 0.86rem; line-height: 1.6;">
                                    رسید شما با موفقیت ثبت شد و هم اکنون توسط تیم پشتیبانی و مالی در دست بررسی است. به محض بررسی، وضعیت سفارش بروزرسانی می گردد.
                                </p>
                            <?php endif; ?>

                            <!-- Submitted Details Preview -->
                            <div class="paymentio-submitted-receipt-preview">
                                <?php if ( ! empty( $receipt->receipt_url ) ) : ?>
                                    <img src="<?php echo esc_url( $receipt->receipt_url ); ?>" alt="رسید پرداختی" class="paymentio-receipt-thumb" onclick="openLightbox('<?php echo esc_url( $receipt->receipt_url ); ?>')" title="برای بزرگنمایی کلیک کنید" />
                                <?php endif; ?>

                                <div style="font-size: 0.82rem; line-height: 1.7;">
                                    <?php if ( ! empty( $receipt->tracking_code ) ) : ?>
                                        <div>کد پیگیری: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->tracking_code ) ); ?></strong></div>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $receipt->payer_card ) ) : ?>
                                        <div>کارت مبدا: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->payer_card ) ); ?></strong></div>
                                    <?php endif; ?>
                                    <div>تاریخ ثبت: <?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->created_at ) ); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Receipt Upload Form (when not uploaded yet or rejected) -->
                <?php if ( $can_upload ) : ?>
                    <form id="paymentio_receipt_form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( add_query_arg( array( 'paymentio_action' => 'card_pay', 'order_id' => $order_id, 'order_key' => $order_key ), home_url( '/' ) ) ); ?>">
                        <?php wp_nonce_field( 'paymentio_upload_receipt_' . $order_id, 'paymentio_receipt_nonce' ); ?>
                        <input type="hidden" name="paymentio_submit_receipt" value="1">
                        <input type="hidden" name="card_number" id="form_card_number" value="<?php echo esc_attr( $first_card['card_number'] ?? '' ); ?>">
                        <input type="hidden" name="card_holder" id="form_card_holder" value="<?php echo esc_attr( $first_card['card_holder'] ?? '' ); ?>">
                        <input type="hidden" name="bank_name" id="form_bank_name" value="<?php echo esc_attr( $first_card['bank_name'] ?? '' ); ?>">

                        <!-- Compact Dropzone -->
                        <div class="paymentio-dropzone" id="dropzone">
                            <div class="paymentio-dropzone-icon">
                                <?php echo Paymentio_Helper::icon( 'upload', 20 ); ?>
                            </div>
                            <div class="paymentio-dropzone-title">بارگذاری تصویر رسید پرداخت (اسکرین شات)</div>
                            <div class="paymentio-dropzone-hint">
                                کلیک کنید یا تصویر رسید را بکشید و رها کنید (JPG, PNG, WEBP - حداکثر <?php echo esc_html( Paymentio_Helper::to_persian_digits( $max_file_mb ) ); ?> مگابایت)
                            </div>
                            <input type="file" name="receipt_image" id="receipt_image" class="paymentio-file-input" accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <!-- Dynamic File Preview -->
                        <div class="paymentio-preview-box" id="preview_box">
                            <div class="paymentio-preview-left">
                                <img src="" alt="پیش نمایش" id="preview_img" class="paymentio-preview-img">
                                <div class="paymentio-preview-meta">
                                    <strong id="preview_filename">فایل انتخاب شد</strong>
                                    <span id="preview_filesize">۰ کیلوبایت</span>
                                </div>
                            </div>
                            <button type="button" class="paymentio-remove-file-btn" id="remove_file_btn">تغییر / حذف تصویر</button>
                        </div>

                        <!-- 2-Column Inputs Row: Tracking Code & Payer Card -->
                        <div class="paymentio-form-row">
                            <div class="paymentio-form-group" style="margin-bottom: 0;">
                                <label class="paymentio-form-label" for="tracking_code">
                                    شماره پیگیری / ارجاع:
                                    <small>(الزامی)</small>
                                </label>
                                <input type="text" name="tracking_code" id="tracking_code" class="paymentio-form-input" placeholder="مثال: ۱۲۳۴۵۶۷۸" dir="ltr" required>
                            </div>

                            <div class="paymentio-form-group" style="margin-bottom: 0;">
                                <label class="paymentio-form-label" for="payer_card">
                                    چهار رقم آخر کارت مبدا:
                                    <small>(اختیاری)</small>
                                </label>
                                <input type="text" name="payer_card" id="payer_card" class="paymentio-form-input" placeholder="مثال: ۵۶۷۸" maxlength="4" dir="ltr">
                            </div>
                        </div>

                        <!-- Customer Note -->
                        <div class="paymentio-form-group">
                            <label class="paymentio-form-label" for="customer_note">
                                یادداشت یا توضیح اختیاری:
                            </label>
                            <input type="text" name="customer_note" id="customer_note" class="paymentio-form-input" placeholder="اگر توضیح یا پیامی درباره این پرداخت دارید بنویسید...">
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="paymentio-submit-btn" id="submit_btn">
                            <?php echo Paymentio_Helper::icon( 'check-simple', 18 ); ?>
                            <span id="btn_text">تایید و ارسال رسید پرداخت</span>
                        </button>

                        <div class="paymentio-security-badge">
                            <?php echo Paymentio_Helper::icon( 'shield', 14 ); ?>
                            <span>اطلاعات پرداخت به صورت امن در سیستم ثبت می گردد.</span>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer Bar -->
    <footer class="paymentio-footer-bar">
        <span><?php echo esc_html( $site_title ); ?> - سامانه مدیریت پرداخت</span>
        <span>اتصال امن SSL - پیمنتیو</span>
    </footer>
</div>

<!-- Modal Lightbox for Full Size View -->
<div class="paymentio-modal-lightbox" id="lightbox" onclick="closeLightbox()">
    <button type="button" class="paymentio-modal-close-btn" onclick="closeLightbox()">&times;</button>
    <img src="" id="lightbox_img" alt="رسید کامل" onclick="event.stopPropagation()">
</div>

<script>
    // Adaptive Layout for Mobile Flow (Amount -> Card -> Form -> Rules)
    function adaptLayout() {
        var rules = document.getElementById('rules_box');
        var rightCol = document.getElementById('col_right');
        var leftCol = document.getElementById('col_left');
        if (!rules || !rightCol || !leftCol) return;
        if (window.innerWidth < 960) {
            if (rules.parentElement !== leftCol) {
                leftCol.appendChild(rules);
            }
        } else {
            if (rules.parentElement !== rightCol) {
                rightCol.appendChild(rules);
            }
        }
    }
    adaptLayout();
    window.addEventListener('resize', adaptLayout);

    // Cards Data for Multi-card Tab Switching
    var paymentioCards = <?php echo wp_json_encode( $cards ); ?>;

    // Card Tabs Switching Logic
    var cardTabs = document.querySelectorAll('.paymentio-card-tab');
    if (cardTabs.length > 0) {
        cardTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                cardTabs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');

                var idx = parseInt(this.getAttribute('data-index'), 10);
                var card = paymentioCards[idx];
                if (!card) return;

                // Update Visual Bank Card
                var bankNameEl = document.querySelector('#display_bank_name span');
                if (bankNameEl) bankNameEl.textContent = card.bank_name;

                var digitsEl = document.getElementById('display_card_digits');
                if (digitsEl) {
                    var formatted = formatCardNumber(card.card_number);
                    digitsEl.textContent = toPersianDigits(formatted);
                }

                var holderEl = document.getElementById('display_card_holder');
                if (holderEl) holderEl.textContent = card.card_holder || 'فروشگاه';

                var copyBtn = document.getElementById('btn_copy_card');
                if (copyBtn) copyBtn.setAttribute('data-copy', card.card_number);

                // Update Shaba Box
                var shabaBox = document.getElementById('display_shaba_box');
                var shabaVal = document.getElementById('display_shaba_val');
                var copyShabaBtn = document.getElementById('btn_copy_shaba');
                var rawShaba = (card.shaba || '').trim();
                var shabaDigits = rawShaba.replace(/[^0-9]/g, '');

                if (rawShaba && shabaDigits && !/^0+$/.test(shabaDigits) && rawShaba !== 'IR...') {
                    if (shabaVal) shabaVal.textContent = rawShaba;
                    if (copyShabaBtn) copyShabaBtn.setAttribute('data-copy', rawShaba);
                    if (shabaBox) shabaBox.style.display = 'flex';
                } else {
                    if (shabaBox) shabaBox.style.display = 'none';
                }

                // Update Hidden Inputs in Form
                var formCardNum = document.getElementById('form_card_number');
                if (formCardNum) formCardNum.value = card.card_number || '';

                var formCardHolder = document.getElementById('form_card_holder');
                if (formCardHolder) formCardHolder.value = card.card_holder || '';

                var formBankName = document.getElementById('form_bank_name');
                if (formBankName) formBankName.value = card.bank_name || '';
            });
        });
    }

    function formatCardNumber(str) {
        var clean = (str || '').replace(/\D/g, '');
        if (clean.length !== 16) return str;
        return clean.match(/.{1,4}/g).join(' - ');
    }

    function toPersianDigits(str) {
        var p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return (str || '').toString().replace(/[0-9]/g, function(d) {
            return p[parseInt(d, 10)];
        });
    }

    // Copy to clipboard helper
    document.querySelectorAll('[data-copy]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var text = this.getAttribute('data-copy');
            if (!text) return;

            var self = this;
            var originalHtml = self.innerHTML;

            function showCopied() {
                self.classList.add('copied');
                self.innerHTML = '<span>کپی شد! ✓</span>';
                setTimeout(function() {
                    self.classList.remove('copied');
                    self.innerHTML = originalHtml;
                }, 2000);
            }

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(showCopied);
            } else {
                var input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                showCopied();
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

        // Drag and Drop support
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', function(e) {
                var dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    fileInput.files = dt.files;
                    handleFile(dt.files[0]);
                }
            }, false);
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
                btnText.textContent = 'در حال ارسال و ثبت رسید... لطفاً صبور باشید';
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
