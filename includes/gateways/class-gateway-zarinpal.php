<?php
/**
 * Paymentio Zarinpal Gateway
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Gateway_Zarinpal extends Paymentio_Gateway_Base {

    /**
     * Merchant ID
     *
     * @var string
     */
    public $merchant_id = '';

    /**
     * ZarinGate flag
     *
     * @var string
     */
    public $zaringate = 'no';

    /**
     * Icon filename
     *
     * @var string
     */
    public $icon_filename = 'zarinpal.png';

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'paymentio_zarinpal';
        $this->gateway_slug       = 'zarinpal';
        $this->method_title       = 'زرین پال (پیمنتیو)';
        $this->method_description = 'درگاه پرداخت آنلاین زرین پال با پشتیبانی از کلیه کارت های عضو شتاب و زرین گیت';

        parent::__construct();

        $this->merchant_id = trim( $this->get_option( 'merchant_id', '' ) );
        $this->zaringate   = $this->get_option( 'zaringate', 'no' );
    }

    /**
     * Define form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال سازی درگاه',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی درگاه زرین پال برای پرداخت در تسویه حساب',
                'default' => 'no',
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'description' => 'این عنوان در صفحه تسویه حساب به خریدار نمایش داده می شود.',
                'default'     => 'پرداخت آنلاین با کارت های شتاب (زرین پال)',
            ),
            'description' => array(
                'title'       => 'توضیحات درگاه',
                'type'        => 'textarea',
                'description' => 'توضیحاتی که زیر عنوان درگاه در تسویه حساب نمایش داده می شود.',
                'default'     => 'پرداخت امن اینترنتی به وسیله کلیه کارت های عضو شتاب از طریق درگاه زرین پال',
            ),
            'merchant_id' => array(
                'title'       => 'مرچنت کد (Merchant ID)',
                'type'        => 'text',
                'description' => 'کد پذیرنده ۳۶ کاراکتری دریافتی از پنل زرین پال',
                'default'     => '',
            ),
            'sandbox' => array(
                'title'   => 'محیط آزمایشی (سندباکس)',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی حالت تست و آزمایش زرین پال بدون کسر وجه واقعی',
                'default' => 'no',
            ),
            'zaringate' => array(
                'title'   => 'زرین گیت (درگاه اختصاصی)',
                'type'    => 'checkbox',
                'label'   => 'انتقال مستقیم کاربر به صفحه شاپرک بدون توقف در صفحه زرین پال (نیاز به تایید زرین گیت در حساب شما دارد)',
                'default' => 'no',
            ),
            'success_status' => array(
                'title'       => 'وضعیت سفارش پس از پرداخت موفق',
                'type'        => 'select',
                'description' => 'وضعیت سفارش بعد از تکمیل موفقیت آمیز تراکنش',
                'default'     => 'processing',
                'options'     => array(
                    'processing' => 'در حال انجام (پیش فرض ووکامرس)',
                    'completed'  => 'تکمیل شده (مناسب محصولات دانلودی)',
                ),
            ),
        );
    }

    /**
     * Get API Endpoints
     *
     * @return array
     */
    private function get_endpoints() {
        $is_sandbox = ( 'yes' === $this->sandbox );

        if ( $is_sandbox ) {
            return array(
                'request' => 'https://sandbox.zarinpal.com/pg/v4/payment/request.json',
                'pay'     => 'https://sandbox.zarinpal.com/pg/StartPay/',
                'verify'  => 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json',
            );
        }

        return array(
            'request' => 'https://payment.zarinpal.com/pg/v4/payment/request.json',
            'pay'     => 'https://payment.zarinpal.com/pg/StartPay/',
            'verify'  => 'https://payment.zarinpal.com/pg/v4/payment/verify.json',
        );
    }

    /**
     * Send payment request to Zarinpal
     *
     * @param WC_Order $order
     * @return array|WP_Error
     */
    public function send_to_provider( $order ) {
        $merchant = ( 'yes' === $this->sandbox ) ? 'sandbox' : $this->merchant_id;

        if ( empty( $merchant ) ) {
            return new WP_Error( 'missing_merchant', 'کد مرچنت زرین پال در تنظیمات وارد نشده است.' );
        }

        $amount       = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
        $callback_url = $this->get_callback_url( $order->get_id() );
        $description  = $this->get_order_description( $order );
        $mobile       = $order->get_billing_phone();
        $email        = $order->get_billing_email();
        $endpoints    = $this->get_endpoints();

        $metadata = array();
        if ( ! empty( $mobile ) ) {
            $metadata['mobile'] = Paymentio_Helper::to_english_digits( $mobile );
        }
        if ( ! empty( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            $metadata['email'] = $email;
        }

        $payload = array(
            'merchant_id'  => $merchant,
            'amount'       => $amount,
            'callback_url' => $callback_url,
            'description'  => $description,
            'metadata'     => (object) $metadata,
        );

        $response = Paymentio_Helper::remote_post( $endpoints['request'], $payload );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = $response['data'];

        if ( isset( $data['data']['code'] ) && 100 === (int) $data['data']['code'] ) {
            $authority = sanitize_text_field( $data['data']['authority'] );

            // Save authority to order meta
            $order->update_meta_data( '_paymentio_zarinpal_authority', $authority );
            $order->save();

            // Insert transaction record into database
            Paymentio_DB::insert( array(
                'order_id'        => $order->get_id(),
                'gateway'         => $this->gateway_slug,
                'gateway_title'   => $this->title,
                'transaction_id'  => $authority,
                'amount'          => $amount,
                'currency'        => 'IRT',
                'status'          => 'pending',
                'customer_name'   => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
                'customer_mobile' => $mobile,
                'customer_email'  => $email,
                'customer_ip'     => Paymentio_Helper::get_client_ip(),
                'message'         => 'در انتظار پرداخت توسط کاربر در درگاه زرین پال',
            ) );

            $redirect_url = $endpoints['pay'] . $authority;
            if ( 'yes' === $this->zaringate && 'yes' !== $this->sandbox ) {
                $redirect_url .= '/ZarinGate';
            }

            return array(
                'redirect_url'   => $redirect_url,
                'transaction_id' => $authority,
            );
        }

        $error_code    = $data['errors']['code'] ?? ( $data['data']['code'] ?? 'Unknown' );
        $error_message = $this->get_error_message( $error_code );

        return new WP_Error( 'zarinpal_request_failed', $error_message );
    }

    /**
     * Handle callback verification from Zarinpal
     */
    public function process_callback() {
        $order_id  = isset( $_GET['wc_order'] ) ? absint( $_GET['wc_order'] ) : 0;
        $authority = isset( $_GET['Authority'] ) ? sanitize_text_field( $_GET['Authority'] ) : '';
        $status    = isset( $_GET['Status'] ) ? sanitize_text_field( $_GET['Status'] ) : '';

        if ( ! $order_id || empty( $authority ) ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        // Check user cancellation
        if ( 'OK' !== strtoupper( $status ) ) {
            $this->handle_failure( $order, 'پرداخت توسط کاربر در صفحه بانک لغو شد.', $authority );
            return;
        }

        // Check if already paid
        if ( $order->is_paid() ) {
            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }

        $amount    = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
        $merchant  = ( 'yes' === $this->sandbox ) ? 'sandbox' : $this->merchant_id;
        $endpoints = $this->get_endpoints();

        $payload = array(
            'merchant_id' => $merchant,
            'amount'      => $amount,
            'authority'   => $authority,
        );

        $response = Paymentio_Helper::remote_post( $endpoints['verify'], $payload );

        if ( is_wp_error( $response ) ) {
            $this->handle_failure( $order, 'خطا در ارتباط با سرور زرین پال برای تایید تراکنش: ' . $response->get_error_message(), $authority );
            return;
        }

        $data = $response['data'];
        $code = (int) ( $data['data']['code'] ?? 0 );

        if ( 100 === $code || 101 === $code ) {
            $ref_id   = sanitize_text_field( $data['data']['ref_id'] ?? '' );
            $card_pan = sanitize_text_field( $data['data']['card_pan'] ?? '' );
            $msg      = ( 101 === $code ) ? 'تراکنش قبلا تایید شده بود.' : 'تراکنش با موفقیت تایید شد.';

            $this->handle_success( $order, $ref_id, $card_pan, $authority, $msg );
            return;
        }

        $error_code    = $data['errors']['code'] ?? $code;
        $error_message = $this->get_error_message( $error_code );

        $this->handle_failure( $order, $error_message, $authority );
    }

    /**
     * Map Zarinpal error codes to Persian descriptions (NO HALF SPACE)
     *
     * @param int|string $code
     * @return string
     */
    private function get_error_message( $code ) {
        $errors = array(
            -9   => 'خطای اعتبارسنجی مقادیر ارسالی به درگاه زرین پال',
            -10  => 'آی پی یا مرچنت کد ارسالی صحیح نیست یا درگاه تایید نشده است',
            -11  => 'مرچنت کد فعال نیست یا درخواست اولیه ناقص است',
            -12  => 'تلاش بیش از حد مجاز در یک بازه زمانی کوتاه',
            -15  => 'ترمینال پرداخت شما به حالت تعلیق درآمده است',
            -16  => 'سطح دسترسی پذیرنده مجاز به انجام این عملیات نیست',
            -30  => 'پذیرنده اجازه دسترسی به شناسه پرداخت مستقیم را ندارد',
            -31  => 'تراکنش ناموفق است و اطلاعات پرداخت ثبت نگردید',
            -32  => 'اطلاعات ارسالی با شرایط پذیرنده مطابقت ندارد',
            -33  => 'رقم مبلغ تراکنش با مبلغ پرداخت شده مطابقت ندارد',
            -34  => 'سقف مجاز تراکنش روزانه یا ماهانه پذیرنده رد شده است',
            -35  => 'فرمت شماره کارت پرداخت کننده نامعتبر است',
            -40  => 'امکان دسترسی به این متد در زرین پال وجود ندارد',
            -50  => 'مبلغ پرداخت شده با مقدار تایید شده مغایرت دارد',
            -51  => 'تراکنش ناموفق است یا توسط کاربر لغو گردید',
            -52  => 'خطای غیرمنتظره در پردازش بانکی رخ داده است',
            -53  => 'شناسه اتوریتی ارسالی نامعتبر است یا منقضی شده است',
            -54  => 'درخواست پرداخت مورد نظر قبلا ارجاع داده شده است',
        );

        return $errors[ (int) $code ] ?? ( 'خطای درگاه زرین پال (کد خطا: ' . $code . ')' );
    }
}
