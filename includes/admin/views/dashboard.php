<?php
/**
 * Paymentio Dashboard View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$stats           = Paymentio_DB::get_stats();
$active_gateways = Paymentio::instance()->get_active_sub_gateways();
$gateways_count  = count( $active_gateways );
$recent_tx       = Paymentio_DB::get_transactions( array( 'limit' => 5 ) );
$mode            = get_option( 'paymentio_display_mode', 'separate' );
?>

<div class="paymentio-dashboard">
    <!-- Stat Cards -->
    <div class="paymentio-stats-grid">
        <div class="paymentio-stat-card">
            <div class="paymentio-stat-icon success">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">تراکنش های موفق</div>
                <div class="paymentio-stat-value"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $stats['success_count'] ) ); ?></div>
                <div class="paymentio-stat-hint">پرداخت های انجام شده</div>
            </div>
        </div>

        <div class="paymentio-stat-card">
            <div class="paymentio-stat-icon primary">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">مجموع فروش موفق</div>
                <div class="paymentio-stat-value"><?php echo esc_html( Paymentio_Helper::format_price( $stats['total_amount'], 'IRT' ) ); ?></div>
                <div class="paymentio-stat-hint">گردش مالی تایید شده</div>
            </div>
        </div>

        <div class="paymentio-stat-card">
            <div class="paymentio-stat-icon danger">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">تراکنش های ناموفق</div>
                <div class="paymentio-stat-value"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $stats['failed_count'] ) ); ?></div>
                <div class="paymentio-stat-hint">لغو شده یا خطای بانکی</div>
            </div>
        </div>

        <div class="paymentio-stat-card">
            <div class="paymentio-stat-icon info">
                <span class="dashicons dashicons-shield"></span>
            </div>
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">درگاه های فعال</div>
                <div class="paymentio-stat-value"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $gateways_count ) ); ?> <span style="font-size:14px; font-weight:normal; color:#94a3b8;">/ ۳</span></div>
                <div class="paymentio-stat-hint"><?php echo ( 'unified' === $mode ) ? 'حالت تک درگاه هوشمند' : 'حالت درگاه های مجزا'; ?></div>
            </div>
        </div>
    </div>

    <!-- Main Section Grid -->
    <div class="paymentio-grid-2">
        <!-- Active Gateways Status -->
        <div class="paymentio-card">
            <div class="paymentio-card-header">
                <h3>وضعیت درگاه های پرداخت</h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=paymentio-gateways' ) ); ?>" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline">مدیریت درگاه ها</a>
            </div>
            <div class="paymentio-card-body">
                <div class="paymentio-mini-gateways">
                    <?php
                    $all_gateways = array(
                        'paymentio_zarinpal' => array( 'title' => 'زرین پال', 'slug' => 'zarinpal', 'logo' => 'zarinpal.png' ),
                        'paymentio_zibal'    => array( 'title' => 'زیبال', 'slug' => 'zibal', 'logo' => 'zibal.png' ),
                        'paymentio_payping'  => array( 'title' => 'پی پینگ', 'slug' => 'payping', 'logo' => 'payping.png' ),
                    );

                    foreach ( $all_gateways as $gw_id => $info ) :
                        $gw_settings = get_option( 'woocommerce_' . $gw_id . '_settings', array() );
                        $is_on       = isset( $gw_settings['enabled'] ) && 'yes' === $gw_settings['enabled'];
                        $is_sandbox  = isset( $gw_settings['sandbox'] ) && 'yes' === $gw_settings['sandbox'];
                    ?>
                        <div class="paymentio-mini-gw-row">
                            <div class="paymentio-mini-gw-info">
                                <img src="<?php echo esc_url( PAYMENTIO_URL . 'assets/images/' . $info['logo'] ); ?>" alt="<?php echo esc_attr( $info['title'] ); ?>" class="paymentio-mini-logo" />
                                <div>
                                    <strong><?php echo esc_html( $info['title'] ); ?></strong>
                                    <?php if ( $is_sandbox ) : ?>
                                        <span class="paymentio-pill paymentio-pill-warning">سندباکس</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <?php if ( $is_on ) : ?>
                                    <span class="paymentio-badge paymentio-badge-success">فعال</span>
                                <?php else : ?>
                                    <span class="paymentio-badge paymentio-badge-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Info & Tips -->
        <div class="paymentio-card">
            <div class="paymentio-card-header">
                <h3>ویژگی های هوشمند پیمنتیو</h3>
            </div>
            <div class="paymentio-card-body">
                <ul class="paymentio-feature-list">
                    <li>
                        <span class="dashicons dashicons-randomize"></span>
                        <div>
                            <strong>هدایت هوشمند و خودکار (Smart Failover)</strong>
                            <p>در صورتی که یک درگاه موقتاً با کندی یا اختلال مواجه شود، سیستم به طور خودکار کاربر را به درگاه جایگزین هدایت می کند.</p>
                        </div>
                    </li>
                    <li>
                        <span class="dashicons dashicons-admin-settings"></span>
                        <div>
                            <strong>انعطاف پذیری در تسویه حساب</strong>
                            <p>امکان نمایش درگاه ها به صورت تک تک و مجزا، یا به صورت یک درگاه واحد با استایل مدرن در برگه تسویه حساب.</p>
                        </div>
                    </li>
                    <li>
                        <span class="dashicons dashicons-database"></span>
                        <div>
                            <strong>ثبت دقیق و مستقل تراکنش ها</strong>
                            <p>ذخیره سازی کلیه جزییات پرداخت شامل کد پیگیری، شماره کارت، آی پی کاربر و تاریخ در جدول اختصاصی دیتابیس.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="paymentio-card" style="margin-top: 24px;">
        <div class="paymentio-card-header">
            <h3>آخرین تراکنش ها</h3>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=paymentio-transactions' ) ); ?>" class="paymentio-btn paymentio-btn-sm paymentio-btn-primary">مشاهده تمام تراکنش ها</a>
        </div>
        <div class="paymentio-card-body" style="padding: 0;">
            <?php if ( empty( $recent_tx['items'] ) ) : ?>
                <div style="padding: 30px; text-align: center; color: #94a3b8;">
                    <span class="dashicons dashicons-info-outline" style="font-size: 32px; width: 32px; height: 32px;"></span>
                    <p>هنوز تراکنشی در سیستم ثبت نشده است.</p>
                </div>
            <?php else : ?>
                <table class="paymentio-table">
                    <thead>
                        <tr>
                            <th>شماره سفارش</th>
                            <th>درگاه</th>
                            <th>مبلغ</th>
                            <th>کد پیگیری</th>
                            <th>خریدار</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_tx['items'] as $tx ) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $tx->order_id . '&action=edit' ) ); ?>" class="paymentio-link">
                                        #<?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->order_id ) ); ?>
                                    </a>
                                </td>
                                <td><strong><?php echo esc_html( $tx->gateway_title ); ?></strong></td>
                                <td><?php echo esc_html( Paymentio_Helper::format_price( $tx->amount, $tx->currency ) ); ?></td>
                                <td><code><?php echo esc_html( $tx->reference_id ?: ( $tx->transaction_id ?: '-' ) ); ?></code></td>
                                <td><?php echo esc_html( $tx->customer_name ?: 'مشتری مهمان' ); ?></td>
                                <td>
                                    <?php if ( 'completed' === $tx->status ) : ?>
                                        <span class="paymentio-badge paymentio-badge-success">موفق</span>
                                    <?php elseif ( 'pending' === $tx->status ) : ?>
                                        <span class="paymentio-badge paymentio-badge-warning">در انتظار پرداخت</span>
                                    <?php else : ?>
                                        <span class="paymentio-badge paymentio-badge-danger">ناموفق</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->created_at ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
