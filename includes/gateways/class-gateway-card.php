<?php
/**
 * Paymentio Card to Card Payment Gateway
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Gateway_Card extends WC_Payment_Gateway {

    /**
     * Gateway slug
     *
     * @var string
     */
    public $gateway_slug = 'card';

    /**
     * Success order status after admin approval
     *
     * @var string
     */
    public $success_status = 'processing';

    /**
     * Max upload file size in MB
     *
     * @var int
     */
    public $max_file_size = 5;

    /**
     * Rules and guidelines text
     *
     * @var string
     */
    public $rules_notes = '';

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'paymentio_card';
        $this->has_fields         = false;
        $this->method_title       = 'کارت به کارت (پیمنتیو)';
        $this->method_description = 'امکان پرداخت از طریق واریز به کارت بانکی و بارگذاری تصویر رسید پرداخت توسط مشتری با تایید مدیریت.';
        $this->supports           = array( 'products' );

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled        = $this->get_option( 'enabled', 'no' );
        $this->title          = $this->get_option( 'title', 'کارت به کارت (انتقال مستقیم بانکی)' );
        $this->description    = $this->get_option( 'description', 'پرداخت از طریق واریز به شماره کارت بانکی و بارگذاری تصویر فیش واریزی' );
        $this->success_status = $this->get_option( 'success_status', 'processing' );
        $this->max_file_size  = absint( $this->get_option( 'max_file_size', 5 ) );
        $this->rules_notes    = $this->get_option( 'rules_notes', Paymentio_Helper::get_default_card_rules() );

        // Icon
        $this->icon = PAYMENTIO_URL . 'assets/images/card.png';

        // Save admin options
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // Thank you page and Order details hooks
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'render_thankyou_notice' ) );
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'render_order_details_notice' ), 20 );
    }

    /**
     * Initialize form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال سازی درگاه کارت به کارت',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی امکان پرداخت کارت به کارت در صفحه تسویه حساب',
                'default' => 'no',
            ),
            'title' => array(
                'title'       => 'عنوان درگاه در تسویه حساب',
                'type'        => 'text',
                'description' => 'عنوانی که در لیست روش های پرداخت تسویه حساب نمایش داده می شود.',
                'default'     => 'کارت به کارت (انتقال مستقیم بانکی)',
            ),
            'description' => array(
                'title'       => 'توضیحات درگاه',
                'type'        => 'textarea',
                'description' => 'توضیحات زیر عنوان درگاه در تسویه حساب',
                'default'     => 'پرداخت از طریق واریز به شماره کارت بانکی و بارگذاری تصویر فیش واریزی جهت تایید و فعال سازی سفارش',
            ),
            'success_status' => array(
                'title'       => 'وضعیت سفارش پس از تایید رسید',
                'type'        => 'select',
                'description' => 'وضعیتی که پس از تایید رسید توسط مدیر، برای سفارش ثبت می شود.',
                'default'     => 'processing',
                'options'     => array(
                    'processing' => 'در حال انجام (Processing - مناسب محصولات دانلودی یا فیزیکی)',
                    'completed'  => 'تکمیل شده (Completed - مناسب تحویل فوری فایل های دانلودی)',
                ),
            ),
            'max_file_size' => array(
                'title'       => 'حداکثر حجم مجاز تصویر رسید (مگابایت)',
                'type'        => 'select',
                'description' => 'بیشترین حجم فایل تصویر که کاربر مجاز به بارگذاری آن است.',
                'default'     => '5',
                'options'     => array(
                    '2'  => '۲ مگابایت',
                    '5'  => '۵ مگابایت (پیشنهادی استاندارد)',
                    '10' => '۱۰ مگابایت',
                ),
            ),
            'bank_name' => array(
                'title'       => 'نام بانک اصلی',
                'type'        => 'text',
                'description' => 'مثال: بانک سامان، بانک ملی، بانک ملت',
                'default'     => '',
            ),
            'card_number' => array(
                'title'       => 'شماره کارت اصلی (۱۶ رقم)',
                'type'        => 'text',
                'description' => 'شماره کارت ۱۶ رقمی مقصد جهت واریز مشتری',
                'default'     => '',
            ),
            'card_holder' => array(
                'title'       => 'نام و نام خانوادگی صاحب حساب',
                'type'        => 'text',
                'description' => 'نام دارنده کارت مقصد',
                'default'     => '',
            ),
            'shaba' => array(
                'title'       => 'شماره شبا (اختیاری)',
                'type'        => 'text',
                'description' => 'شماره شبا بدون IR یا به همراه IR',
                'default'     => '',
            ),
            'rules_notes' => array(
                'title'       => 'قوانین و راهنمای کارت به کارت',
                'type'        => 'textarea',
                'description' => 'متن راهنما و قوانینی که در صفحه پرداخت و ثبت رسید به خریدار نمایش داده می شود.',
                'default'     => Paymentio_Helper::get_default_card_rules(),
                'css'         => 'min-height: 120px;',
            ),
        );
    }

    /**
     * Check if gateway is available for checkout
     *
     * @return bool
     */
    public function is_available() {
        $is_available = parent::is_available();
        if ( ! $is_available ) {
            return false;
        }

        $cards = $this->get_configured_cards();
        if ( empty( $cards ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get configured bank cards (supports both multi-card array and legacy single fields)
     *
     * @return array
     */
    public function get_configured_cards() {
        $cards = $this->get_option( 'cards', array() );

        if ( ! empty( $cards ) && is_array( $cards ) ) {
            $valid_cards = array();
            foreach ( $cards as $card ) {
                $clean_num = Paymentio_Helper::clean_card_number( $card['card_number'] ?? '' );
                if ( ! empty( $clean_num ) ) {
                    $raw_shaba    = sanitize_text_field( $card['shaba'] ?? '' );
                    $shaba_digits = preg_replace( '/[^0-9]/', '', $raw_shaba );
                    $clean_shaba  = ( empty( $shaba_digits ) || preg_match( '/^0+$/', $shaba_digits ) || 'IR...' === trim( $raw_shaba ) ) ? '' : $raw_shaba;

                    $valid_cards[] = array(
                        'bank_slug'   => sanitize_text_field( $card['bank_slug'] ?? 'other' ),
                        'bank_name'   => sanitize_text_field( $card['bank_name'] ?? 'بانک عضو شتاب' ),
                        'card_number' => $clean_num,
                        'card_holder' => sanitize_text_field( $card['card_holder'] ?? '' ),
                        'shaba'       => $clean_shaba,
                    );
                }
            }
            if ( ! empty( $valid_cards ) ) {
                return $valid_cards;
            }
        }

        // Fallback to single card options
        $single_num = Paymentio_Helper::clean_card_number( $this->get_option( 'card_number', '' ) );
        if ( ! empty( $single_num ) ) {
            $bank_name = $this->get_option( 'bank_name', '' );
            if ( empty( $bank_name ) ) {
                $detected  = Paymentio_Helper::detect_bank( $single_num );
                $bank_name = $detected['name'];
                $bank_slug = $detected['slug'];
            } else {
                $detected  = Paymentio_Helper::detect_bank( $single_num );
                $bank_slug = $detected['slug'];
            }

            $raw_shaba    = sanitize_text_field( $this->get_option( 'shaba', '' ) );
            $shaba_digits = preg_replace( '/[^0-9]/', '', $raw_shaba );
            $clean_shaba  = ( empty( $shaba_digits ) || preg_match( '/^0+$/', $shaba_digits ) || 'IR...' === trim( $raw_shaba ) ) ? '' : $raw_shaba;

            return array(
                array(
                    'bank_slug'   => $bank_slug,
                    'bank_name'   => $bank_name,
                    'card_number' => $single_num,
                    'card_holder' => $this->get_option( 'card_holder', '' ),
                    'shaba'       => $clean_shaba,
                ),
            );
        }

        return array();
    }

    /**
     * Process Payment
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wc_add_notice( 'سفارش مورد نظر یافت نشد.', 'error' );
            return array( 'result' => 'fail' );
        }

        // Set order to on-hold status
        $order->update_status(
            'on-hold',
            'سفارش با روش کارت به کارت ثبت شد. در انتظار واریز وجه و ثبت تصویر رسید پرداخت توسط مشتری.'
        );

        // Record initial transaction record in Paymentio DB
        $total_amount = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
        $customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );

        Paymentio_DB::insert( array(
            'order_id'        => $order_id,
            'gateway'         => 'card',
            'gateway_title'   => $this->title,
            'transaction_id'  => 'CARD-' . $order_id . '-' . wp_rand( 1000, 9999 ),
            'reference_id'    => '',
            'amount'          => $total_amount,
            'currency'        => 'IRT',
            'status'          => 'pending',
            'customer_name'   => $customer_name ?: 'مشتری',
            'customer_mobile' => $order->get_billing_phone(),
            'customer_email'  => $order->get_billing_email(),
            'customer_ip'     => Paymentio_Helper::get_client_ip(),
            'message'         => 'سفارش در انتظار واریز کارت به کارت و ارسال رسید پرداخت است.',
        ) );

        // Empty cart
        if ( function_exists( 'WC' ) && isset( WC()->cart ) ) {
            WC()->cart->empty_cart();
        }

        // Build redirect URL to dedicated payment page
        $pay_url = add_query_arg(
            array(
                'paymentio_action' => 'card_pay',
                'order_id'         => $order->get_id(),
                'order_key'        => $order->get_order_key(),
            ),
            home_url( '/' )
        );

        return array(
            'result'   => 'success',
            'redirect' => $pay_url,
        );
    }

    /**
     * Render notice on WooCommerce Thank You page
     *
     * @param int $order_id
     */
    public function render_thankyou_notice( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $receipt = Paymentio_DB::get_receipt_by_order( $order_id );
        $pay_url = add_query_arg(
            array(
                'paymentio_action' => 'card_pay',
                'order_id'         => $order->get_id(),
                'order_key'        => $order->get_order_key(),
            ),
            home_url( '/' )
        );

        ?>
        <div class="paymentio-card-thankyou-box" style="margin: 24px 0; padding: 22px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; direction: rtl; text-align: right; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.08);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h3 style="margin: 0 0 8px 0; color: #166534; font-size: 1.15rem; font-weight: 800;">
                        روش پرداخت: کارت به کارت بانکی
                    </h3>
                    <?php if ( $receipt && 'approved' === $receipt->status ) : ?>
                        <p style="margin: 0; color: #15803d; font-size: 0.92rem;">
                            رسید پرداخت شما تایید شده و سفارش در وضعیت تکمیل / آماده ارسال قرار دارد.
                        </p>
                    <?php elseif ( $receipt && 'pending' === $receipt->status ) : ?>
                        <p style="margin: 0; color: #854d0e; font-size: 0.92rem;">
                            رسید پرداخت شما دریافت شد و هم اکنون در صف بررسی توسط پشتیبانی سایت قرار دارد.
                        </p>
                    <?php else : ?>
                        <p style="margin: 0; color: #374151; font-size: 0.92rem;">
                            برای مشاهده مشخصات شماره کارت و بارگذاری تصویر فیش واریزی، لطفاً وارد صفحه اختصاصی پرداخت شوید.
                        </p>
                    <?php endif; ?>
                </div>

                <a href="<?php echo esc_url( $pay_url ); ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #0284c7; color: #ffffff; padding: 12px 22px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 0.95rem; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                    <?php echo Paymentio_Helper::icon( 'card', 18 ); ?>
                    <span>ورود به صفحه پرداخت و ثبت فیش</span>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render notice inside WooCommerce Order Details view (My Account)
     *
     * @param WC_Order $order
     */
    public function render_order_details_notice( $order ) {
        if ( ! $order || $this->id !== $order->get_payment_method() ) {
            return;
        }

        $order_id = $order->get_id();
        $receipt  = Paymentio_DB::get_receipt_by_order( $order_id );
        $pay_url  = add_query_arg(
            array(
                'paymentio_action' => 'card_pay',
                'order_id'         => $order->get_id(),
                'order_key'        => $order->get_order_key(),
            ),
            home_url( '/' )
        );

        ?>
        <section class="woocommerce-customer-details paymentio-order-card-box" style="margin-top: 28px; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; direction: rtl;">
            <h2 class="woocommerce-column__title" style="margin-top: 0; font-size: 1.1rem; color: #0f172a; font-weight: 700;">
                وضعیت پرداخت کارت به کارت
            </h2>

            <?php if ( $receipt ) : ?>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 12px;">
                    <div>
                        <div style="font-size: 0.92rem; color: #475569;">
                            وضعیت رسید:
                            <?php if ( 'approved' === $receipt->status ) : ?>
                                <strong style="color: #16a34a;">تایید شده</strong>
                            <?php elseif ( 'rejected' === $receipt->status ) : ?>
                                <strong style="color: #dc2626;">رد شده (علت: <?php echo esc_html( $receipt->rejection_reason ); ?>)</strong>
                            <?php else : ?>
                                <strong style="color: #d97706;">در انتظار بررسی مدیریت</strong>
                            <?php endif; ?>
                        </div>
                        <?php if ( ! empty( $receipt->tracking_code ) ) : ?>
                            <div style="font-size: 0.88rem; color: #64748b; margin-top: 4px;">
                                کد پیگیری ثبت شده: <code><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->tracking_code ) ); ?></code>
                            </div>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo esc_url( $pay_url ); ?>" style="padding: 10px 18px; background: #0f172a; color: #ffffff; border-radius: 8px; text-decoration: none; font-size: 0.88rem; font-weight: 600;">
                        مشاهده یا ثبت مجدد رسید
                    </a>
                </div>
            <?php else : ?>
                <p style="color: #64748b; font-size: 0.9rem; margin: 8px 0 16px 0;">
                    رسید پرداخت هنوز ثبت نشده است. برای واریز به کارت و ارسال عکس رسید روی دکمه زیر کلیک فرمایید:
                </p>
                <a href="<?php echo esc_url( $pay_url ); ?>" style="display: inline-block; padding: 10px 20px; background: #0284c7; color: #ffffff; border-radius: 8px; text-decoration: none; font-size: 0.92rem; font-weight: 700;">
                    پرداخت و ثبت تصویر رسید
                </a>
            <?php endif; ?>
        </section>
        <?php
    }
}
