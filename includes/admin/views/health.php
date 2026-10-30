<?php
/**
 * Paymentio System Health and Connectivity Check View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$curl_installed    = function_exists( 'curl_version' );
$curl_version_info = $curl_installed ? curl_version() : array();
$openssl_installed = extension_loaded( 'openssl' );
$php_version       = phpversion();
$wp_version        = get_bloginfo( 'version' );
$wc_version        = defined( 'WC_VERSION' ) ? WC_VERSION : 'نصب نشده';
$memory_limit      = ini_get( 'memory_limit' );
$max_exec_time     = ini_get( 'max_execution_time' );
?>

<div class="paymentio-health-container">
    <div class="paymentio-section-desc">
        <h3>وضعیت سلامت سرور و تست ارتباط با درگاه ها</h3>
        <p>بررسی پیش نیازهای فنی سرور جهت ارسال و دریافت تراکنش های بانکی، ماژول های ارتباطی و تست پینگ آنلاین به سرورهای پرداخت.</p>
    </div>

    <!-- Live API Connectivity Test Grid -->
    <div class="paymentio-card">
        <div class="paymentio-card-header">
            <h3>تست اتصال به وب سرویس های پرداخت یار</h3>
            <span class="paymentio-badge paymentio-badge-info">آزمایش زنده</span>
        </div>
        <div class="paymentio-card-body">
            <div class="paymentio-api-test-grid">
                <!-- Zarinpal -->
                <div class="paymentio-api-card" id="api-test-zarinpal">
                    <div class="paymentio-api-header">
                        <img src="<?php echo esc_url( PAYMENTIO_URL . 'assets/images/zarinpal.png' ); ?>" alt="زرین پال" />
                        <div>
                            <strong>زرین پال</strong>
                            <small>payment.zarinpal.com</small>
                        </div>
                    </div>
                    <div class="paymentio-api-status">
                        <span class="status-indicator ready">در انتظار تست</span>
                    </div>
                    <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline paymentio-test-api" data-slug="zarinpal">
                        بررسی پینگ
                    </button>
                </div>

                <!-- Zibal -->
                <div class="paymentio-api-card" id="api-test-zibal">
                    <div class="paymentio-api-header">
                        <img src="<?php echo esc_url( PAYMENTIO_URL . 'assets/images/zibal.png' ); ?>" alt="زیبال" />
                        <div>
                            <strong>زیبال</strong>
                            <small>gateway.zibal.ir</small>
                        </div>
                    </div>
                    <div class="paymentio-api-status">
                        <span class="status-indicator ready">در انتظار تست</span>
                    </div>
                    <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline paymentio-test-api" data-slug="zibal">
                        بررسی پینگ
                    </button>
                </div>

                <!-- PayPing -->
                <div class="paymentio-api-card" id="api-test-payping">
                    <div class="paymentio-api-header">
                        <img src="<?php echo esc_url( PAYMENTIO_URL . 'assets/images/payping.png' ); ?>" alt="پی پینگ" />
                        <div>
                            <strong>پی پینگ</strong>
                            <small>api.payping.ir</small>
                        </div>
                    </div>
                    <div class="paymentio-api-status">
                        <span class="status-indicator ready">در انتظار تست</span>
                    </div>
                    <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline paymentio-test-api" data-slug="payping">
                        بررسی پینگ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Environment Table -->
    <div class="paymentio-card" style="margin-top: 24px;">
        <div class="paymentio-card-header">
            <h3>اطلاعات و پیش نیازهای سیستمی هاست</h3>
        </div>
        <div class="paymentio-card-body" style="padding: 0;">
            <div class="paymentio-table-responsive">
                <table class="paymentio-table">
                <tbody>
                    <tr>
                        <td style="width: 250px;"><strong>نسخه PHP سرور</strong></td>
                        <td>
                            <?php if ( version_compare( $php_version, '7.4', '>=' ) ) : ?>
                                <span class="paymentio-badge paymentio-badge-success"><?php echo esc_html( $php_version ); ?> (پشتیبانی می شود)</span>
                            <?php else : ?>
                                <span class="paymentio-badge paymentio-badge-danger"><?php echo esc_html( $php_version ); ?> (نیاز به ارتقا به حداقل ۷.۴)</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>ماژول شبکه cURL</strong></td>
                        <td>
                            <?php if ( $curl_installed ) : ?>
                                <span class="paymentio-badge paymentio-badge-success">فعال - نسخه <?php echo esc_html( $curl_version_info['version'] ?? '' ); ?></span>
                            <?php else : ?>
                                <span class="paymentio-badge paymentio-badge-danger">غیرفعال (برای اتصال به بانک ضروری است)</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>ماژول امنیتی OpenSSL</strong></td>
                        <td>
                            <?php if ( $openssl_installed ) : ?>
                                <span class="paymentio-badge paymentio-badge-success">فعال و آماده اتصالات SSL/TLS</span>
                            <?php else : ?>
                                <span class="paymentio-badge paymentio-badge-danger">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>محدودیت حافظه رم (Memory Limit)</strong></td>
                        <td><code><?php echo esc_html( $memory_limit ); ?></code></td>
                    </tr>

                    <tr>
                        <td><strong>حداکثر زمان اجرا (Max Execution Time)</strong></td>
                        <td><code><?php echo esc_html( $max_exec_time ); ?> ثانیه</code></td>
                    </tr>

                    <tr>
                        <td><strong>نسخه وردپرس</strong></td>
                        <td><code><?php echo esc_html( $wp_version ); ?></code></td>
                    </tr>

                    <tr>
                        <td><strong>نسخه ووکامرس</strong></td>
                        <td><code><?php echo esc_html( $wc_version ); ?></code></td>
                    </tr>

                    <tr>
                        <td><strong>آی پی خروجی سرور شما</strong></td>
                        <td><code><?php echo esc_html( Paymentio_Helper::get_server_ip() ); ?></code> (جهت ثبت در پنل پرداخت یارها مانند زیبال در صورت نیاز به آی پی مجاز)</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
