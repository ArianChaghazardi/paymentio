<?php
/**
 * Paymentio PayPing Gateway
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Gateway_Payping extends Paymentio_Gateway_Base {

    /**
     * Bearer Token
     *
     * @var string
     */
    public $token = '';

    /**
     * Icon filename
     *
     * @var string
     */
    public $icon_filename = 'payping.png';

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'paymentio_payping';
        $this->gateway_slug       = 'payping';
        $this->method_title       = 'پی پینگ (پیمنتیو)';
        $this->method_description = 'درگاه پرداخت آنلاین پی پینگ با پشتیبانی از پروتکل پرداخت مدرن و امنیت بالا';

        parent::__construct();

        $this->token = trim( $this->get_option( 'token', '' ) );
    }

    /**
     * Define form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال سازی درگاه',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی درگاه پی پینگ برای پرداخت در تسویه حساب',
                'default' => 'no',
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'description' => 'این عنوان در صفحه تسویه حساب به خریدار نمایش داده می شود.',
                'default'     => 'پرداخت آنلاین با کارت های شتاب (پی پینگ)',
            ),
            'description' => array(
                'title'       => 'توضیحات درگاه',
                'type'        => 'textarea',
                'description' => 'توضیحاتی که زیر عنوان درگاه در تسویه حساب نمایش داده می شود.',
                'default'     => 'پرداخت امن اینترنتی به وسیله کلیه کارت های عضو شتاب از طریق درگاه پرداخت یار پی پینگ',
            ),
            'token' => array(
                'title'       => 'توکن اختصاصی (Bearer Token)',
                'type'        => 'password',
                'description' => 'توکن احراز هویت دریافتی از پنل کاربری پی پینگ',
                'default'     => '',
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
     * Send payment request to PayPing
     *
     * @param WC_Order $order
     * @return array|WP_Error
     */
    public function send_to_provider( $order ) {
        if ( empty( $this->token ) ) {
            return new WP_Error( 'missing_token', 'توکن درگاه پی پینگ در تنظیمات وارد نشده است.' );
        }

        $amount       = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
        $callback_url = $this->get_callback_url( $order->get_id() );
        $description  = $this->get_order_description( $order );
        $mobile       = $order->get_billing_phone();
        $email        = $order->get_billing_email();
        $client_ref   = (string) $order->get_id() . '_' . time();

        $payer_identity = '';
        if ( ! empty( $mobile ) && preg_match( '/^09[0-9]{9}$/', Paymentio_Helper::to_english_digits( $mobile ) ) ) {
            $payer_identity = Paymentio_Helper::to_english_digits( $mobile );
        } elseif ( ! empty( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            $payer_identity = $email;
        }

        $payload = array(
            'amount'        => $amount,
            'payerIdentity' => $payer_identity,
            'payerName'     => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
            'description'   => $description,
            'returnUrl'     => $callback_url,
            'clientRefId'   => $client_ref,
        );

        $headers = array(
            'Authorization' => 'Bearer ' . $this->token,
        );

        $response = Paymentio_Helper::remote_post( 'https://api.payping.ir/v3/pay', $payload, $headers );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = $response['data'];

        if ( isset( $data['code'] ) && ! empty( $data['code'] ) ) {
            $code = sanitize_text_field( $data['code'] );

            // Save PayPing code and clientRefId to order meta
            $order->update_meta_data( '_paymentio_payping_code', $code );
            $order->update_meta_data( '_paymentio_payping_client_ref', $client_ref );
            $order->save();

            // Insert transaction record into database
            Paymentio_DB::insert( array(
                'order_id'        => $order->get_id(),
                'gateway'         => $this->gateway_slug,
                'gateway_title'   => $this->title,
                'transaction_id'  => $code,
                'reference_id'    => $client_ref,
                'amount'          => $amount,
                'currency'        => 'IRT',
                'status'          => 'pending',
                'customer_name'   => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
                'customer_mobile' => $mobile,
                'customer_email'  => $email,
                'customer_ip'     => Paymentio_Helper::get_client_ip(),
                'message'         => 'در انتظار پرداخت توسط کاربر در درگاه پی پینگ',
            ) );

            return array(
                'redirect_url'   => 'https://api.payping.ir/v3/pay/start/' . $code,
                'transaction_id' => $code,
            );
        }

        $error_message = 'خطا در ایجاد تراکنش در پی پینگ';
        if ( ! empty( $data ) && is_array( $data ) ) {
            if ( isset( $data['detail'] ) ) {
                $error_message = $data['detail'];
            } elseif ( isset( $data['message'] ) ) {
                $error_message = $data['message'];
            } else {
                $messages = array();
                foreach ( $data as $field => $errors ) {
                    if ( is_array( $errors ) ) {
                        $messages[] = implode( ' - ', $errors );
                    }
                }
                if ( ! empty( $messages ) ) {
                    $error_message = implode( ' | ', $messages );
                }
            }
        }

        return new WP_Error( 'payping_request_failed', $error_message );
    }

    /**
     * Handle callback verification from PayPing
     */
    public function process_callback() {
        $order_id = isset( $_GET['wc_order'] ) ? absint( $_GET['wc_order'] ) : 0;

        if ( ! $order_id ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        // Check if already paid
        if ( $order->is_paid() ) {
            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }

        // Parse return data from PayPing
        $raw_data = isset( $_REQUEST['data'] ) ? wp_unslash( $_REQUEST['data'] ) : '';
        $data     = json_decode( $raw_data, true ) ?: array();
        $code     = $order->get_meta( '_paymentio_payping_code' );

        $ref_id   = isset( $data['paymentRefId'] ) ? sanitize_text_field( $data['paymentRefId'] ) : ( $_REQUEST['refid'] ?? '' );
        $card_pan = isset( $data['cardNumber'] ) ? sanitize_text_field( $data['cardNumber'] ) : '';

        // Check cancellation
        if ( isset( $_REQUEST['status'] ) && '0' === (string) $_REQUEST['status'] ) {
            $this->handle_failure( $order, 'پرداخت توسط شما در صفحه بانک پی پینگ لغو شد.', $code );
            return;
        }

        if ( empty( $ref_id ) && empty( $code ) ) {
            $this->handle_failure( $order, 'اطلاعات بازگشتی از درگاه پی پینگ معتبر نیست.', $code );
            return;
        }

        $amount  = Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
        $payload = array(
            'refId'  => $ref_id,
            'amount' => $amount,
        );

        $headers = array(
            'Authorization' => 'Bearer ' . $this->token,
        );

        $response = Paymentio_Helper::remote_post( 'https://api.payping.ir/v3/pay/verify', $payload, $headers );

        if ( is_wp_error( $response ) ) {
            $this->handle_failure( $order, 'خطا در ارتباط با سرور پی پینگ برای تایید تراکنش: ' . $response->get_error_message(), $code );
            return;
        }

        $http_code = $response['code'];
        $res_data  = $response['data'];

        if ( 200 === $http_code ) {
            $verified_card = $res_data['cardNumber'] ?? $card_pan;
            $this->handle_success( $order, $ref_id ?: $code, $verified_card, $code, 'تراکنش با موفقیت در پی پینگ تایید شد.' );
            return;
        }

        $error_message = 'تراکنش توسط درگاه پی پینگ تایید نشد (کد وضعیت: ' . $http_code . ')';
        if ( ! empty( $res_data['message'] ) ) {
            $error_message = $res_data['message'];
        } elseif ( ! empty( $res_data['detail'] ) ) {
            $error_message = $res_data['detail'];
        }

        $this->handle_failure( $order, $error_message, $code );
    }
}
