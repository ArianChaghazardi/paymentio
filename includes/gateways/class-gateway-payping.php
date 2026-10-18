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
        $this->method_title       = 'پی پینگ';
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
                'default'     => 'پی پینگ',
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

        $payment_code = '';
        if ( ! empty( $data['paymentCode'] ) ) {
            $payment_code = sanitize_text_field( $data['paymentCode'] );
        } elseif ( ! empty( $data['code'] ) ) {
            $payment_code = sanitize_text_field( $data['code'] );
        }

        if ( ! empty( $payment_code ) ) {
            // Save PayPing code and clientRefId to order meta
            $order->update_meta_data( '_paymentio_payping_code', $payment_code );
            $order->update_meta_data( '_paymentio_payping_client_ref', $client_ref );
            $order->update_meta_data( '_paymentio_payping_amount', $amount );
            $order->save();

            // Insert transaction record into database
            $currency    = strtoupper( trim( $order->get_currency() ) );
            $db_amount   = ( 'IRR' === $currency ) ? Paymentio_Helper::to_rial( $order->get_total(), $currency ) : Paymentio_Helper::to_toman( $order->get_total(), $currency );
            $db_currency = ( 'IRR' === $currency ) ? 'IRR' : 'IRT';

            Paymentio_DB::insert( array(
                'order_id'        => $order->get_id(),
                'gateway'         => $this->gateway_slug,
                'gateway_title'   => $this->title,
                'transaction_id'  => $payment_code,
                'reference_id'    => $client_ref,
                'amount'          => $db_amount,
                'currency'        => $db_currency,
                'status'          => 'pending',
                'customer_name'   => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
                'customer_mobile' => $mobile,
                'customer_email'  => $email,
                'customer_ip'     => Paymentio_Helper::get_client_ip(),
                'message'         => 'در انتظار پرداخت توسط کاربر در درگاه پی پینگ',
            ) );

            $redirect_url = ! empty( $data['url'] ) ? esc_url_raw( $data['url'] ) : ( 'https://api.payping.ir/v3/pay/start/' . $payment_code );

            return array(
                'redirect_url'   => $redirect_url,
                'transaction_id' => $payment_code,
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

        // Parse return data from PayPing (handles both POST body and query parameters)
        $payment_ref_id = isset( $_REQUEST['paymentRefId'] ) ? sanitize_text_field( $_REQUEST['paymentRefId'] ) : ( $_REQUEST['refid'] ?? '' );
        $payment_code   = isset( $_REQUEST['paymentCode'] ) ? sanitize_text_field( $_REQUEST['paymentCode'] ) : ( $_REQUEST['code'] ?? $order->get_meta( '_paymentio_payping_code' ) );
        $card_pan       = isset( $_REQUEST['cardNumber'] ) ? sanitize_text_field( $_REQUEST['cardNumber'] ) : '';

        // Also check raw json data if present
        $raw_data = isset( $_REQUEST['data'] ) ? wp_unslash( $_REQUEST['data'] ) : '';
        if ( ! empty( $raw_data ) ) {
            $data = json_decode( $raw_data, true );
            if ( is_array( $data ) ) {
                if ( empty( $payment_ref_id ) && ! empty( $data['paymentRefId'] ) ) {
                    $payment_ref_id = sanitize_text_field( $data['paymentRefId'] );
                }
                if ( empty( $payment_code ) && ! empty( $data['paymentCode'] ) ) {
                    $payment_code = sanitize_text_field( $data['paymentCode'] );
                }
                if ( empty( $card_pan ) && ! empty( $data['cardNumber'] ) ) {
                    $card_pan = sanitize_text_field( $data['cardNumber'] );
                }
            }
        }

        // Check cancellation
        if ( isset( $_REQUEST['status'] ) && '0' === (string) $_REQUEST['status'] ) {
            $this->handle_failure( $order, 'پرداخت توسط شما در صفحه بانک پی پینگ لغو شد.', $payment_code );
            return;
        }

        if ( empty( $payment_ref_id ) && empty( $payment_code ) ) {
            $this->handle_failure( $order, 'اطلاعات بازگشتی از درگاه پی پینگ معتبر نیست.', $payment_code );
            return;
        }

        $saved_amount = $order->get_meta( '_paymentio_payping_amount' );
        $amount       = ! empty( $saved_amount ) ? (int) $saved_amount : Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() );
        $payload      = array(
            'paymentRefId' => (int) $payment_ref_id,
            'paymentCode'  => $payment_code,
            'amount'       => (int) $amount,
            'refId'        => $payment_ref_id,
        );

        $headers = array(
            'Authorization' => 'Bearer ' . $this->token,
        );

        $response = Paymentio_Helper::remote_post( 'https://api.payping.ir/v3/pay/verify', $payload, $headers );

        if ( is_wp_error( $response ) ) {
            $this->handle_failure( $order, 'خطا در ارتباط با سرور پی پینگ برای تایید تراکنش: ' . $response->get_error_message(), $payment_code );
            return;
        }

        $http_code = $response['code'];
        $res_data  = $response['data'];

        if ( 200 === $http_code ) {
            $verified_card = $res_data['cardNumber'] ?? $card_pan;
            $this->handle_success( $order, $payment_ref_id ?: $payment_code, $verified_card, $payment_code, 'تراکنش با موفقیت در پی پینگ تایید شد.' );
            return;
        }

        $error_message = 'تراکنش توسط درگاه پی پینگ تایید نشد (کد وضعیت: ' . $http_code . ')';
        if ( ! empty( $res_data['message'] ) ) {
            $error_message = $res_data['message'];
        } elseif ( ! empty( $res_data['detail'] ) ) {
            $error_message = $res_data['detail'];
        }

        $this->handle_failure( $order, $error_message, $payment_code );
    }
}
