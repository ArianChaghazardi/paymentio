<?php
/**
 * Paymentio Gateways View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$gateways_data = array(
    'paymentio_zarinpal' => array(
        'slug'        => 'zarinpal',
        'title'       => 'زرین پال',
        'desc'        => 'درگاه پرداخت اینترنتی زرین پال با پشتیبانی از پروتکل v4 و زرین گیت اختصاصی',
        'logo'        => 'zarinpal.png',
        'docs_url'    => 'https://www.zarinpal.com/docs/',
        'auth_label'  => 'کد مرچنت (Merchant ID)',
        'auth_field'  => 'merchant_id',
        'auth_type'   => 'text',
        'auth_hint'   => 'کد ۳۶ رقمی دریافتی از پنل زرین پال',
        'has_sandbox' => true,
        'has_direct'  => true,
    ),
    'paymentio_zibal' => array(
        'slug'        => 'zibal',
        'title'       => 'زیبال',
        'desc'        => 'درگاه پرداخت آنلاین زیبال با زیرساخت مسیردهی هوشمند بانکی و اتصال چند دامنه ای',
        'logo'        => 'zibal.png',
        'docs_url'    => 'https://help.zibal.ir/',
        'auth_label'  => 'کد مرچنت زیبال',
        'auth_field'  => 'merchant',
        'auth_type'   => 'text',
        'auth_hint'   => 'کد مرچنت اختصاصی درگاه (یا عبارت zibal برای حالت آزمایشی)',
        'has_sandbox' => true,
        'has_direct'  => false,
    ),
    'paymentio_payping' => array(
        'slug'        => 'payping',
        'title'       => 'پی پینگ',
        'desc'        => 'درگاه پرداخت مدرن پی پینگ بر پایه پروتکل امنیتی Bearer Token و پایداری بالا',
        'logo'        => 'payping.png',
        'docs_url'    => 'https://docs.payping.ir/',
        'auth_label'  => 'توکن اختصاصی (Bearer Token)',
        'auth_field'  => 'token',
        'auth_type'   => 'password',
        'auth_hint'   => 'کد توکن احراز هویت دریافتی از پنل پی پینگ',
        'has_sandbox' => false,
        'has_direct'  => false,
    ),
);
?>

<div class="paymentio-gateways-container">
    <div class="paymentio-section-desc">
        <h3>مدیریت و پیکربندی درگاه های پرداخت</h3>
        <p>در این بخش می توانید درگاه های پرداخت مورد نظر خود را فعال یا غیرفعال کنید، کلیدهای دسترسی را وارد نمایید و وضعیت اتصال به سرور درگاه ها را تست کنید.</p>
    </div>

    <div class="paymentio-gateways-grid">
        <?php foreach ( $gateways_data as $gw_id => $gw ) :
            $settings     = get_option( 'woocommerce_' . $gw_id . '_settings', array() );
            $is_enabled   = isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
            $is_sandbox   = isset( $settings['sandbox'] ) && 'yes' === $settings['sandbox'];
            $is_direct    = isset( $settings['zaringate'] ) && 'yes' === $settings['zaringate'];
            $custom_title = $settings['title'] ?? ( 'پرداخت با ' . $gw['title'] );
            $custom_desc  = $settings['description'] ?? ( 'پرداخت امن اینترنتی از طریق ' . $gw['title'] );
            $auth_val     = $settings[ $gw['auth_field'] ] ?? '';
            $status_val   = $settings['success_status'] ?? 'processing';
        ?>
            <div class="paymentio-gw-card <?php echo $is_enabled ? 'enabled' : ''; ?>" id="card-<?php echo esc_attr( $gw['slug'] ); ?>" data-gateway="<?php echo esc_attr( $gw_id ); ?>">
                <div class="paymentio-gw-card-top">
                    <div class="paymentio-gw-brand">
                        <img src="<?php echo esc_url( PAYMENTIO_URL . 'assets/images/' . $gw['logo'] ); ?>" alt="<?php echo esc_attr( $gw['title'] ); ?>" class="paymentio-gw-logo" />
                        <div>
                            <h4><?php echo esc_html( $gw['title'] ); ?></h4>
                            <span class="paymentio-gw-subtitle"><?php echo esc_html( $gw['slug'] ); ?></span>
                        </div>
                    </div>

                    <!-- Toggle Switch -->
                    <div class="paymentio-switch-wrapper">
                        <label class="paymentio-switch" title="فعال یا غیرفعال سازی درگاه">
                            <input type="checkbox" class="paymentio-gw-toggle" data-gateway="<?php echo esc_attr( $gw_id ); ?>" <?php checked( $is_enabled, true ); ?> />
                            <span class="paymentio-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="paymentio-gw-card-body">
                    <p class="paymentio-gw-description"><?php echo esc_html( $gw['desc'] ); ?></p>

                    <div class="paymentio-gw-badges">
                        <?php if ( $is_enabled ) : ?>
                            <span class="paymentio-badge paymentio-badge-success">فعال در تسویه حساب</span>
                        <?php else : ?>
                            <span class="paymentio-badge paymentio-badge-secondary">غیرفعال</span>
                        <?php endif; ?>

                        <?php if ( $gw['has_sandbox'] && $is_sandbox ) : ?>
                            <span class="paymentio-badge paymentio-badge-warning">محیط آزمایشی (سندباکس)</span>
                        <?php endif; ?>

                        <?php if ( $gw['has_direct'] && $is_direct ) : ?>
                            <span class="paymentio-badge paymentio-badge-info">درگاه اختصاصی مستقیم</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="paymentio-gw-card-footer">
                    <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-primary paymentio-open-settings" data-target="modal-<?php echo esc_attr( $gw['slug'] ); ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                        پیکربندی تنظیمات
                    </button>
                    <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline paymentio-test-api" data-slug="<?php echo esc_attr( $gw['slug'] ); ?>">
                        <span class="dashicons dashicons-update"></span>
                        تست اتصال
                    </button>
                </div>
            </div>

            <!-- Modal for Gateway Settings -->
            <div class="paymentio-modal" id="modal-<?php echo esc_attr( $gw['slug'] ); ?>" style="display: none;">
                <div class="paymentio-modal-dialog">
                    <div class="paymentio-modal-header">
                        <h3>تنظیمات درگاه <?php echo esc_html( $gw['title'] ); ?></h3>
                        <button type="button" class="paymentio-modal-close">&times;</button>
                    </div>
                    <form class="paymentio-gw-form" data-gateway="<?php echo esc_attr( $gw_id ); ?>">
                        <div class="paymentio-modal-body">
                            <div class="paymentio-form-group">
                                <label><?php echo esc_html( $gw['auth_label'] ); ?> <span class="required">*</span></label>
                                <input type="<?php echo esc_attr( $gw['auth_type'] ); ?>" name="<?php echo esc_attr( $gw['auth_field'] ); ?>" value="<?php echo esc_attr( $auth_val ); ?>" class="paymentio-input" placeholder="<?php echo esc_attr( $gw['auth_hint'] ); ?>" />
                                <small><?php echo esc_html( $gw['auth_hint'] ); ?></small>
                            </div>

                            <div class="paymentio-form-group">
                                <label>عنوان درگاه در تسویه حساب</label>
                                <input type="text" name="title" value="<?php echo esc_attr( $custom_title ); ?>" class="paymentio-input" />
                            </div>

                            <div class="paymentio-form-group">
                                <label>توضیحات درگاه در تسویه حساب</label>
                                <textarea name="description" rows="3" class="paymentio-input"><?php echo esc_textarea( $custom_desc ); ?></textarea>
                            </div>

                            <?php if ( $gw['has_sandbox'] ) : ?>
                                <div class="paymentio-form-group paymentio-checkbox-group">
                                    <label>
                                        <input type="checkbox" name="sandbox" value="1" <?php checked( $is_sandbox, true ); ?> />
                                        فعال سازی حالت آزمایشی (سندباکس)
                                    </label>
                                    <small>جهت تست بدون جابجایی وجه واقعی</small>
                                </div>
                            <?php endif; ?>

                            <?php if ( $gw['has_direct'] ) : ?>
                                <div class="paymentio-form-group paymentio-checkbox-group">
                                    <label>
                                        <input type="checkbox" name="zaringate" value="1" <?php checked( $is_direct, true ); ?> />
                                        فعال سازی درگاه مستقیم زرین گیت
                                    </label>
                                    <small>انتقال مستقیم مشتری به شاپرک</small>
                                </div>
                            <?php endif; ?>

                            <div class="paymentio-form-group">
                                <label>وضعیت سفارش پس از پرداخت موفق</label>
                                <select name="success_status" class="paymentio-input">
                                    <option value="processing" <?php selected( $status_val, 'processing' ); ?>>در حال انجام (پیش فرض)</option>
                                    <option value="completed" <?php selected( $status_val, 'completed' ); ?>>تکمیل شده (محصولات دانلودی)</option>
                                </select>
                            </div>
                        </div>

                        <div class="paymentio-modal-footer">
                            <button type="submit" class="paymentio-btn paymentio-btn-primary">ذخیره تغییرات</button>
                            <button type="button" class="paymentio-btn paymentio-btn-outline paymentio-modal-close">بستن</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
