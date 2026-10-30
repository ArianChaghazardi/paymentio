<?php
/**
 * Paymentio General Settings View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle form save
$notice = '';
if ( isset( $_POST['paymentio_save_general_settings'] ) && check_admin_referer( 'paymentio_save_settings_nonce' ) ) {
    $mode        = sanitize_text_field( $_POST['display_mode'] ?? 'separate' );
    $default_gw  = sanitize_text_field( $_POST['default_gateway'] ?? 'zarinpal' );
    $failover    = isset( $_POST['auto_failover'] ) ? 'yes' : 'no';
    $unified_title = sanitize_text_field( $_POST['unified_title'] ?? 'پرداخت اینترنتی امن' );
    $unified_desc  = sanitize_textarea_field( $_POST['unified_desc'] ?? 'پرداخت با کلیه کارت های عضو شبکه شتاب' );

    update_option( 'paymentio_display_mode', $mode );
    update_option( 'paymentio_default_gateway', $default_gw );
    update_option( 'paymentio_auto_failover', $failover );

    // Save Currency
    $store_currency = sanitize_text_field( $_POST['store_currency'] ?? 'IRT' );
    if ( in_array( $store_currency, array( 'IRT', 'IRR' ), true ) ) {
        update_option( 'paymentio_currency', $store_currency );
        update_option( 'woocommerce_currency', $store_currency );
    }

    // Update unified gateway options
    $unified_settings = get_option( 'woocommerce_paymentio_unified_settings', array() );
    $unified_settings['enabled']         = ( 'unified' === $mode ) ? 'yes' : 'no';
    $unified_settings['title']           = $unified_title;
    $unified_settings['description']     = $unified_desc;
    $unified_settings['default_gateway'] = $default_gw;
    $unified_settings['smart_failover']  = $failover;
    update_option( 'woocommerce_paymentio_unified_settings', $unified_settings );

    $notice = 'تنظیمات عمومی پیمنتیو با موفقیت ذخیره شد.';
}

$current_currency   = get_option( 'paymentio_currency', get_option( 'woocommerce_currency', 'IRT' ) );
$current_mode       = get_option( 'paymentio_display_mode', 'separate' );
$current_default    = get_option( 'paymentio_default_gateway', 'zarinpal' );
$current_failover   = get_option( 'paymentio_auto_failover', 'yes' );
$unified_settings   = get_option( 'woocommerce_paymentio_unified_settings', array() );
$current_title      = $unified_settings['title'] ?? 'پرداخت اینترنتی امن (شتاب)';
$current_desc       = $unified_settings['description'] ?? 'پرداخت با کلیه کارت های عضو شبکه شتاب از طریق درگاه امن بانکی';
?>

<div class="paymentio-settings-container">
    <div class="paymentio-section-desc">
        <h3>تنظیمات عمومی و نحوه نمایش درگاه ها</h3>
        <p>تنظیم نحوه نمایش درگاه های پرداخت در صفحه تسویه حساب ووکامرس، تعیین درگاه پیش فرض و ویژگی های مسیردهی هوشمند.</p>
    </div>

    <?php if ( $notice ) : ?>
        <div class="paymentio-alert paymentio-alert-success">
            <span class="dashicons dashicons-yes"></span>
            <?php echo esc_html( $notice ); ?>
        </div>
    <?php endif; ?>

    <div class="paymentio-card">
        <form method="post" action="">
            <?php wp_nonce_field( 'paymentio_save_settings_nonce' ); ?>

            <!-- Currency Selection Section -->
            <div class="paymentio-settings-section">
                <h4>واحد پول رسمی فروشگاه</h4>
                <p style="color: #64748b; font-size: 13px; margin: 4px 0 14px 0;">تعیین واحد پول فروشگاه برای نمایش قیمت محصولات، سبد خرید، صورتحساب و ارسال مبالغ به درگاه های پرداخت.</p>

                <div class="paymentio-mode-options">
                    <label class="paymentio-mode-card <?php echo 'IRT' === $current_currency ? 'selected' : ''; ?>">
                        <input type="radio" name="store_currency" value="IRT" <?php checked( $current_currency, 'IRT' ); ?> />
                        <div class="paymentio-mode-content">
                            <div class="paymentio-mode-header">
                                <span class="dashicons dashicons-money-alt"></span>
                                <strong>تومان (پیش فرض پیشنهادی)</strong>
                            </div>
                            <p>تمامی قیمت های محصولات و صورتحساب به تومان نمایش داده شده و مبالغ درگاه ها بر پایه تومان پردازش می شوند.</p>
                        </div>
                    </label>

                    <label class="paymentio-mode-card <?php echo 'IRR' === $current_currency ? 'selected' : ''; ?>">
                        <input type="radio" name="store_currency" value="IRR" <?php checked( $current_currency, 'IRR' ); ?> />
                        <div class="paymentio-mode-content">
                            <div class="paymentio-mode-header">
                                <span class="dashicons dashicons-cart"></span>
                                <strong>ریال</strong>
                            </div>
                            <p>قیمت های فروشگاه به ریال نمایش داده می شوند.</p>
                        </div>
                    </label>
                </div>
            </div>

            <hr style="border:0; border-top: 1px solid #e2e8f0; margin: 24px 0;" />

            <div class="paymentio-settings-section">
                <h4>نحوه نمایش درگاه ها در تسویه حساب (Checkout)</h4>

                <div class="paymentio-mode-options">
                    <label class="paymentio-mode-card <?php echo 'separate' === $current_mode ? 'selected' : ''; ?>">
                        <input type="radio" name="display_mode" value="separate" <?php checked( $current_mode, 'separate' ); ?> />
                        <div class="paymentio-mode-content">
                            <div class="paymentio-mode-header">
                                <span class="dashicons dashicons-list-view"></span>
                                <strong>نمایش درگاه های مجزا (پیش فرض)</strong>
                            </div>
                            <p>هر یک از درگاه های فعال (زرین پال، زیبال، پی پینگ) به عنوان یک گزینه جداگانه در فرم تسویه حساب ووکامرس نمایش داده می شوند.</p>
                        </div>
                    </label>

                    <label class="paymentio-mode-card <?php echo 'unified' === $current_mode ? 'selected' : ''; ?>">
                        <input type="radio" name="display_mode" value="unified" <?php checked( $current_mode, 'unified' ); ?> />
                        <div class="paymentio-mode-content">
                            <div class="paymentio-mode-header">
                                <span class="dashicons dashicons-randomize"></span>
                                <strong>تک درگاه یکپارچه هوشمند (پیشنهادی)</strong>
                            </div>
                            <p>تمام درگاه ها در قالب یک درگاه واحد و شیک ادغام می شوند و کاربر می تواند از بین نشان های گرافیکی درگاه ها انتخاب کند یا به درگاه پیش فرض برود.</p>
                        </div>
                    </label>
                </div>
            </div>

            <hr style="border:0; border-top: 1px solid #1e293b; margin: 24px 0;" />

            <div class="paymentio-settings-section">
                <h4>تنظیمات درگاه یکپارچه و هوشمند</h4>

                <div class="paymentio-form-group">
                    <label>عنوان درگاه یکپارچه در تسویه حساب</label>
                    <input type="text" name="unified_title" value="<?php echo esc_attr( $current_title ); ?>" class="paymentio-input" />
                    <small>تنها در صورت انتخاب حالت "تک درگاه یکپارچه" نمایش داده می شود.</small>
                </div>

                <div class="paymentio-form-group">
                    <label>توضیحات درگاه یکپارچه</label>
                    <textarea name="unified_desc" rows="2" class="paymentio-input"><?php echo esc_textarea( $current_desc ); ?></textarea>
                </div>

                <div class="paymentio-form-group">
                    <label>درگاه پیش فرض اصلی</label>
                    <select name="default_gateway" class="paymentio-input" style="max-width: 300px;">
                        <option value="zarinpal" <?php selected( $current_default, 'zarinpal' ); ?>>زرین پال</option>
                        <option value="zibal" <?php selected( $current_default, 'zibal' ); ?>>زیبال</option>
                        <option value="payping" <?php selected( $current_default, 'payping' ); ?>>پی پینگ</option>
                    </select>
                    <small>درگاهی که اولویت اول در پرداخت و انتخاب خودکار دارد.</small>
                </div>

                <div class="paymentio-form-group paymentio-checkbox-group">
                    <label>
                        <input type="checkbox" name="auto_failover" value="1" <?php checked( $current_failover, 'yes' ); ?> />
                        فعال سازی هدایت هوشمند در صورت قطعی درگاه (Smart Failover)
                    </label>
                    <small>چنانچه درگاه انتخابی با خطای سرور یا عدم پاسخگویی مواجه شود، تراکنش به طور خودکار به درگاه بعدی منتقل می شود.</small>
                </div>
            </div>

            <div class="paymentio-form-actions" style="margin-top: 24px;">
                <button type="submit" name="paymentio_save_general_settings" class="paymentio-btn paymentio-btn-primary">
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>
</div>
