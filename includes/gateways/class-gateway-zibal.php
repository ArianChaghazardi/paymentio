<?php
/**
 * Paymentio Zibal Gateway
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Gateway_Zibal extends Paymentio_Gateway_Base {

    /**
     * Merchant PIN
     *
     * @var string
     */
    public $merchant = '';

    /**
     * Icon filename
     *
     * @var string
     */
    public $icon_filename = 'zibal.png';

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'paymentio_zibal';
        $this->gateway_slug       = 'zibal';
        $this->method_title       = 'زیبال (پیمنتیو)';
        $this->method_description = 'درگاه پرداخت آنلاین زیبال با مسیردهی هوشمند و پایداری بالا در شبکه بانکی شتاب';

        parent::__construct();

        $this->merchant = trim( $this->get_option( 'merchant', '' ) );
    }

    /**
     * Define form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال سازی درگاه',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی درگاه زیبال برای پرداخت در تسویه حساب',
                'default' => 'no',
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'description' => 'این عنوان در صفحه تسویه حساب به خریدار نمایش داده می شود.',
                'default'     => 'پرداخت آنلاین با کارت های شتاب (زیبال)',
            ),
            'description' => array(
                'title'       => 'توضیحات درگاه',
                'type'        => 'textarea',
                'description' => 'توضیحاتی که زیر عنوان درگاه در تسویه حساب نمایش داده می شود.',
                'default'     => 'پرداخت امن اینترنتی به وسیله کلیه کارت های عضو شتاب از طریق درگاه پرداخت یار زیبال',
            ),
            'merchant' => array(
                'title'       => 'مرچنت کد (Merchant ID)',
                'type'        => 'text',
                'description' => 'کد پذیرنده دریافتی از پنل زیبال (یا zibal برای حالت آزمایشی)',
                'default'     => '',
            ),
            'sandbox' => array(
                'title'   => 'محیط آزمایشی (سندباکس)',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی حالت آزمایشی زیبال بدون کسر مبلغ از کارت',
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
     * Make request with fallback domain
     *
     * @param string $endpoint_path
     * @param array $payload
     * @return array|WP_Error
     */
    private function request_zibal( $endpoint_path, $payload ) {
        $domains = array( 'https://gateway.zibal.ir', 'https://gateway.zibal.io' );

        foreach ( $domains as $domain ) {
            $url      = $domain . $endpoint_path;
            $response = Paymentio_Helper::remote_post( $url, $payload );

            if ( ! is_wp_error( $response ) && isset( $response['code'] ) && 200 === (int) $response['code'] ) {
                return $response;
            }
        }

        return new WP_Error( 'zibal_connection_failed', 'برقراری ارتباط با سرورهای درگاه زیبال با خطا مواجه شد.' );
    }

    /**
     * Send payment request to Zibal
     *
     * @param WC_Order $order
     * @return array|WP_Error
     */
    public function send_to_provider( $order ) {
        $merchant = ( 'yes' === $this->sandbox ) ? 'zibal' : $this->merchant;

        if ( empty( $merchant ) ) {
            return new WP_Error( 'missing_merchant', 'کد مرچنت زیبال در تنظیمات وارد نشده است.' );
        }

        $amount       = Paymentio_Helper::to_rial( $order->get_total(), $order->get_currency() );
        $callback_url = $this->get_callback_url( $order->get_id() );
        $description  = $this->get_order_description( $order );
        $mobile       = $order->get_billing_phone();
        $email        = $order->get_billing_email();

        $payload = array(
            'merchant'    => $merchant,
            'amount'      => $amount,
            'callbackUrl' => $callback_url,
            'orderId'     => (string) $order->get_id(),
            'description' => $description,
        );

        if ( ! empty( $mobile ) ) {
            $payload['mobile'] = Paymentio_Helper::to_english_digits( $mobile );
        }
        if ( ! empty( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            $payload['email'] = $email;
        }

        $response = $this->request_zibal( '/v1/request', $payload );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = $response['data'];

        if ( isset( $data['result'] ) && 100 === (int) $data['result'] && ! empty( $data['trackId'] ) ) {
            $track_id = sanitize_text_field( $data['trackId'] );

            // Save trackId to order meta
            $order->update_meta_data( '_paymentio_zibal_track_id', $track_id );
            $order->save();

            // Insert transaction record into database
            Paymentio_DB::insert( array(
                'order_id'        => $order->get_id(),
                'gateway'         => $this->gateway_slug,
                'gateway_title'   => $this->title,
                'transaction_id'  => $track_id,
                'amount'          => Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() ),
                'currency'        => 'IRT',
                'status'          => 'pending',
                'customer_name'   => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
                'customer_mobile' => $mobile,
                'customer_email'  => $email,
                'customer_ip'     => Paymentio_Helper::get_client_ip(),
                'message'         => 'در انتظار پرداخت توسط کاربر در درگاه زیبال',
            ) );

            return array(
                'redirect_url'   => 'https://gateway.zibal.ir/start/' . $track_id,
                'transaction_id' => $track_id,
            );
        }

        $error_code    = $data['result'] ?? 'Unknown';
        $error_message = $this->get_error_message( $error_code );

        return new WP_Error( 'zibal_request_failed', $error_message );
    }

    /**
     * Handle callback verification from Zibal
     */
    public function process_callback() {
        $order_id = isset( $_GET['wc_order'] ) ? absint( $_GET['wc_order'] ) : 0;
        $track_id = isset( $_GET['trackId'] ) ? sanitize_text_field( $_GET['trackId'] ) : '';
        $success  = isset( $_GET['success'] ) ? sanitize_text_field( $_GET['success'] ) : '';
        $status   = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

        if ( ! $order_id || empty( $track_id ) ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        // Check user cancellation
        if ( '1' !== $success && '2' !== $status ) {
            $this->handle_failure( $order, 'پرداخت توسط کاربر در صفحه بانک لغو شد یا ناموفق بود.', $track_id );
            return;
        }

        // Check if already paid
        if ( $order->is_paid() ) {
            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }

        $merchant = ( 'yes' === $this->sandbox ) ? 'zibal' : $this->merchant;

        $payload = array(
            'merchant' => $merchant,
            'trackId'  => $track_id,
        );

        $response = $this->request_zibal( '/v1/verify', $payload );

        if ( is_wp_error( $response ) ) {
            $this->handle_failure( $order, 'خطا در ارتباط با سرور زیبال برای تایید تراکنش: ' . $response->get_error_message(), $track_id );
            return;
        }

        $data   = $response['data'];
        $result = (int) ( $data['result'] ?? 0 );

        if ( 100 === $result || 201 === $result ) {
            $ref_number   = sanitize_text_field( $data['refNumber'] ?? '' );
            $card_number  = sanitize_text_field( $data['cardNumber'] ?? '' );
            $msg          = ( 201 === $result ) ? 'تراکنش قبلا تایید شده بود.' : 'تراکنش با موفقیت در زیبال تایید شد.';

            $this->handle_success( $order, $ref_number ?: $track_id, $card_number, $track_id, $msg );
            return;
        }

        $error_message = $this->get_error_message( $result );
        $this->handle_failure( $order, $error_message, $track_id );
    }

    /**
     * Map Zibal error codes to Persian descriptions (NO HALF SPACE)
     *
     * @param int|string $code
     * @return string
     */
    private function get_error_message( $code ) {
        $errors = array(
            102 => 'کد مرچنت ارسالی در زیبال یافت نشد یا غیرفعال است',
            103 => 'درگاه فعال نیست یا دسترسی غیرفعال شده است',
            104 => 'تراکنش با این مشخصات یافت نشد',
            105 => 'مبلغ تراکنش نامعتبر است (حداقل مبلغ مجاز ۱۰۰۰ ریال است)',
            106 => 'آدرس بازگشت (CallbackUrl) نامعتبر است',
            113 => 'مبلغ تراکنش از سقف مجاز روزانه بیشتر است',
            202 => 'سفارش هنوز توسط کاربر پرداخت نشده است',
            203 => 'شناسه ارسالی برای تایید نامعتبر است',
        );

        return $errors[ (int) $code ] ?? ( 'خطای درگاه زیبال (کد خطا: ' . $code . ')' );
    }
}
