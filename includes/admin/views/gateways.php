<?php
/**
 * Paymentio Gateways View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$gateways_data = array(
    'paymentio_card' => array(
        'slug'        => 'card',
        'title'       => 'کارت به کارت',
        'desc'        => 'واریز مستقیم به شماره کارت بانکی، بارگذاری اسکرین شات رسید و تایید سریع توسط مدیریت فروشگاه',
        'logo'        => 'card.svg',
        'docs_url'    => '',
        'auth_label'  => '',
        'auth_field'  => '',
        'auth_type'   => '',
        'auth_hint'   => '',
        'has_sandbox' => false,
        'has_direct'  => false,
        'is_card'     => true,
    ),
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

$iranian_banks = Paymentio_Helper::get_iranian_banks();
?>

<div class="paymentio-gateways-container">
    <div class="paymentio-section-desc">
        <h3>مدیریت و پیکربندی درگاه های پرداخت</h3>
        <p>در این بخش می توانید روش های پرداخت آنلاین و کارت به کارت را فعال یا غیرفعال کنید، مشخصات حساب و کارت های بانکی را تنظیم نمایید و تنظیمات درگاه ها را مدیریت کنید.</p>
    </div>

    <div class="paymentio-gateways-grid">
        <?php foreach ( $gateways_data as $gw_id => $gw ) :
            $settings     = get_option( 'woocommerce_' . $gw_id . '_settings', array() );
            $is_enabled   = isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
            $is_sandbox   = isset( $settings['sandbox'] ) && 'yes' === $settings['sandbox'];
            $is_direct    = isset( $settings['zaringate'] ) && 'yes' === $settings['zaringate'];
            $is_card      = ! empty( $gw['is_card'] );
            $custom_title = $settings['title'] ?? ( $is_card ? 'کارت به کارت (انتقال مستقیم بانکی)' : ( 'پرداخت با ' . $gw['title'] ) );
            $custom_desc  = $settings['description'] ?? ( $is_card ? 'پرداخت از طریق واریز به شماره کارت بانکی و بارگذاری تصویر فیش واریزی' : ( 'پرداخت امن اینترنتی از طریق ' . $gw['title'] ) );
            $auth_val     = ! empty( $gw['auth_field'] ) ? ( $settings[ $gw['auth_field'] ] ?? '' ) : '';
            $status_val   = $settings['success_status'] ?? 'processing';
            $max_file_size = $settings['max_file_size'] ?? '5';
            $rules_notes  = $settings['rules_notes'] ?? Paymentio_Helper::get_default_card_rules();

            // Card accounts setup
            $cards = $settings['cards'] ?? array();
            if ( empty( $cards ) && $is_card ) {
                if ( ! empty( $settings['card_number'] ) ) {
                    $cards = array(
                        array(
                            'bank_slug'   => $settings['bank_slug'] ?? 'other',
                            'bank_name'   => $settings['bank_name'] ?? 'بانک عضو شتاب',
                            'card_number' => $settings['card_number'] ?? '',
                            'card_holder' => $settings['card_holder'] ?? '',
                            'shaba'       => $settings['shaba'] ?? '',
                        )
                    );
                } else {
                    $cards = array(
                        array(
                            'bank_slug'   => 'saman',
                            'bank_name'   => 'بانک سامان',
                            'card_number' => '',
                            'card_holder' => '',
                            'shaba'       => '',
                        )
                    );
                }
            }
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

                <div class="paymentio-gw-description">
                    <p><?php echo esc_html( $gw['desc'] ); ?></p>
                </div>

                <div class="paymentio-gw-badges">
                    <span class="paymentio-gw-badge <?php echo $is_enabled ? 'paymentio-badge-active' : 'paymentio-badge-inactive'; ?>">
                        <?php echo $is_enabled ? 'فعال در فروشگاه' : 'غیرفعال'; ?>
                    </span>

                    <?php if ( $is_card ) : ?>
                        <span class="paymentio-pill paymentio-pill-primary">انتقال دستی / تایید با فیش</span>
                    <?php endif; ?>

                    <?php if ( $gw['has_sandbox'] && $is_sandbox ) : ?>
                        <span class="paymentio-pill paymentio-pill-warning">حالت سندباکس</span>
                    <?php endif; ?>

                    <?php if ( $gw['has_direct'] && $is_direct ) : ?>
                        <span class="paymentio-badge paymentio-badge-info">درگاه اختصاصی مستقیم</span>
                    <?php endif; ?>
                </div>

                <div class="paymentio-gw-card-footer">
                    <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-primary paymentio-open-settings" data-target="modal-<?php echo esc_attr( $gw['slug'] ); ?>">
                        <?php echo Paymentio_Helper::icon( 'settings', 15 ); ?>
                        پیکربندی تنظیمات
                    </button>
                    <?php if ( $is_card ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=paymentio-receipts' ) ); ?>" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline">
                            <?php echo Paymentio_Helper::icon( 'receipt', 15 ); ?>
                            مشاهده رسید ها
                        </a>
                    <?php else : ?>
                        <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline paymentio-test-api" data-slug="<?php echo esc_attr( $gw['slug'] ); ?>">
                            <?php echo Paymentio_Helper::icon( 'refresh', 15 ); ?>
                            تست اتصال
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal for Gateway Settings -->
            <div class="paymentio-modal" id="modal-<?php echo esc_attr( $gw['slug'] ); ?>" style="display: none;">
                <div class="paymentio-modal-dialog" style="<?php echo $is_card ? 'max-width: 680px;' : ''; ?>">
                    <div class="paymentio-modal-header">
                        <h3>تنظیمات درگاه <?php echo esc_html( $gw['title'] ); ?></h3>
                        <button type="button" class="paymentio-modal-close">&times;</button>
                    </div>

                    <?php if ( $is_card ) : ?>
                        <!-- Dedicated Card to Card Form -->
                        <form class="paymentio-card-gw-form" data-gateway="<?php echo esc_attr( $gw_id ); ?>">
                            <div class="paymentio-modal-body">
                                <div class="paymentio-form-group">
                                    <label>عنوان درگاه در تسویه حساب</label>
                                    <input type="text" name="title" value="<?php echo esc_attr( $custom_title ); ?>" class="paymentio-input" />
                                    <small>عنوانی که مشتری در لیست روش های پرداخت انتخاب می کند.</small>
                                </div>

                                <div class="paymentio-form-group">
                                    <label>توضیحات درگاه در تسویه حساب</label>
                                    <textarea name="description" rows="2" class="paymentio-input"><?php echo esc_textarea( $custom_desc ); ?></textarea>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                    <div class="paymentio-form-group">
                                        <label>وضعیت سفارش پس از تایید رسید</label>
                                        <select name="success_status" class="paymentio-input">
                                            <option value="processing" <?php selected( $status_val, 'processing' ); ?>>در حال انجام (Processing)</option>
                                            <option value="completed" <?php selected( $status_val, 'completed' ); ?>>تکمیل شده (Completed - تحویل فوری فایل)</option>
                                        </select>
                                    </div>

                                    <div class="paymentio-form-group">
                                        <label>حداکثر حجم مجاز تصویر فیش</label>
                                        <select name="max_file_size" class="paymentio-input">
                                            <option value="2" <?php selected( (string) $max_file_size, '2' ); ?>>۲ مگابایت</option>
                                            <option value="5" <?php selected( (string) $max_file_size, '5' ); ?>>۵ مگابایت (پیشنهادی استاندارد)</option>
                                            <option value="10" <?php selected( (string) $max_file_size, '10' ); ?>>۱۰ مگابایت</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Cards Repeater -->
                                <div class="paymentio-form-group" style="margin-top: 10px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <label style="font-weight: 800; font-size: 14px; color: #0f172a;">شماره کارت ها و اطلاعات حساب بانکی</label>
                                        <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline paymentio-add-card-row">
                                            + افزودن کارت جدید
                                        </button>
                                    </div>

                                    <div id="paymentio-cards-container">
                                        <?php foreach ( $cards as $i => $c ) : ?>
                                            <div class="paymentio-card-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 12px; position: relative;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                    <strong style="font-size: 13px; color: #0284c7;">کارت بانکی شماره <?php echo esc_html( Paymentio_Helper::to_persian_digits( $i + 1 ) ); ?></strong>
                                                    <?php if ( count( $cards ) > 1 ) : ?>
                                                        <button type="button" class="paymentio-remove-card-row" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 12px; font-weight: bold;">حذف این کارت &times;</button>
                                                    <?php endif; ?>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                                    <div>
                                                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">انتخاب بانک:</label>
                                                        <select name="cards[<?php echo esc_attr( $i ); ?>][bank_slug]" class="paymentio-input paymentio-bank-select">
                                                            <?php foreach ( $iranian_banks as $bslug => $binfo ) : ?>
                                                                <option value="<?php echo esc_attr( $bslug ); ?>" data-name="<?php echo esc_attr( $binfo['name'] ); ?>" <?php selected( $c['bank_slug'] ?? '', $bslug ); ?>>
                                                                    <?php echo esc_html( $binfo['name'] ); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <input type="hidden" name="cards[<?php echo esc_attr( $i ); ?>][bank_name]" class="paymentio-bank-name-hidden" value="<?php echo esc_attr( $c['bank_name'] ?? '' ); ?>" />
                                                    </div>

                                                    <div>
                                                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">شماره کارت (۱۶ رقم):</label>
                                                        <input type="text" name="cards[<?php echo esc_attr( $i ); ?>][card_number]" value="<?php echo esc_attr( $c['card_number'] ?? '' ); ?>" class="paymentio-input paymentio-card-num-input" placeholder="۶۲۱۹۸۶۱۰..." maxlength="19" dir="ltr" />
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                                    <div>
                                                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">نام صاحب حساب:</label>
                                                        <input type="text" name="cards[<?php echo esc_attr( $i ); ?>][card_holder]" value="<?php echo esc_attr( $c['card_holder'] ?? '' ); ?>" class="paymentio-input" placeholder="نام و نام خانوادگی دارنده کارت" />
                                                    </div>

                                                    <div>
                                                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">شماره شبا (اختیاری):</label>
                                                        <input type="text" name="cards[<?php echo esc_attr( $i ); ?>][shaba]" value="<?php echo esc_attr( $c['shaba'] ?? '' ); ?>" class="paymentio-input" placeholder="IR..." dir="ltr" />
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="paymentio-form-group">
                                    <label>متن قوانین و راهنمای کارت به کارت</label>
                                    <textarea name="rules_notes" rows="4" class="paymentio-input"><?php echo esc_textarea( $rules_notes ); ?></textarea>
                                    <small>این متن در صفحه پرداخت و بارگذاری فیش به کاربر نمایش داده خواهد شد.</small>
                                </div>
                            </div>

                            <div class="paymentio-modal-footer">
                                <button type="submit" class="paymentio-btn paymentio-btn-primary">
                                    <?php echo Paymentio_Helper::icon( 'check', 16 ); ?>
                                    ذخیره تنظیمات کارت به کارت
                                </button>
                                <button type="button" class="paymentio-btn paymentio-btn-outline paymentio-modal-close">بستن</button>
                            </div>
                        </form>
                    <?php else : ?>
                        <!-- Standard Online Gateway Form -->
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
                                <button type="submit" class="paymentio-btn paymentio-btn-primary">
                                    <?php echo Paymentio_Helper::icon( 'check', 16 ); ?>
                                    ذخیره تغییرات
                                </button>
                                <button type="button" class="paymentio-btn paymentio-btn-outline paymentio-modal-close">بستن</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
