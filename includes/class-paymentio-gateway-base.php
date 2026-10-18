<?php
/**
 * Paymentio Abstract Base Gateway Class
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Paymentio_Gateway_Base extends WC_Payment_Gateway {

    /**
     * Gateway slug identifier (e.g. zarinpal, zibal, payping)
     *
     * @var string
     */
    public $gateway_slug = '';

    /**
     * Sandbox mode flag
     *
     * @var string
     */
    public $sandbox = 'no';

    /**
     * Order success status
     *
     * @var string
     */
    public $success_status = 'processing';

    /**
     * Constructor
     */
    public function __construct() {
        $this->has_fields = false;
        $this->supports   = array( 'products' );

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled        = $this->get_option( 'enabled', 'no' );
        $this->title          = $this->get_option( 'title', $this->method_title );
        $this->description    = $this->get_option( 'description', $this->method_description );
        $this->sandbox        = $this->get_option( 'sandbox', 'no' );
        $this->success_status = $this->get_option( 'success_status', 'processing' );

        // Icon
        if ( ! empty( $this->icon_filename ) ) {
            $this->icon = PAYMENTIO_URL . 'assets/images/' . $this->icon_filename;
        }

        // Save admin options hook
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // Callback handler hook
        add_action( 'woocommerce_api_' . strtolower( $this->id ), array( $this, 'process_callback' ) );
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

        // In unified mode, individual gateways are hidden from checkout
        if ( ! is_admin() && 'unified' === get_option( 'paymentio_display_mode', 'separate' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get callback URL for this gateway
     *
     * @param int $order_id
     * @return string
     */
    public function get_callback_url( $order_id ) {
        return add_query_arg(
            array(
                'wc_order'   => $order_id,
                'paymentio'  => $this->gateway_slug,
            ),
            WC()->api_request_url( $this->id )
        );
    }

    /**
     * Build description text with placeholders
     *
     * @param WC_Order $order
     * @return string
     */
    public function get_order_description( $order ) {
        $order_id   = $order->get_id();
        $customer   = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
        $site_title = get_bloginfo( 'name' );

        return sprintf(
            'سفارش شماره %s در فروشگاه %s خریدار: %s',
            $order->get_order_number(),
            $site_title,
            $customer ?: 'مشتری'
        );
    }

    /**
     * Standard process payment initiating redirect
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

        // Call the provider specific initiation
        $result = $this->send_to_provider( $order );

        if ( is_wp_error( $result ) ) {
            $error_message = $result->get_error_message();
            $order->add_order_note( sprintf( 'خطا در هدایت به درگاه %s: %s', $this->title, $error_message ) );
            wc_add_notice( $error_message, 'error' );
            return array( 'result' => 'fail' );
        }

        return array(
            'result'   => 'success',
            'redirect' => $result['redirect_url'],
        );
    }

    /**
     * Abstract method to send payment request to provider
     *
     * @param WC_Order $order
     * @return array|WP_Error Array with 'redirect_url' and 'transaction_id' on success, WP_Error on failure
     */
    abstract public function send_to_provider( $order );

    /**
     * Abstract method to handle provider callback and verification
     */
    abstract public function process_callback();

    /**
     * Complete order successfully and record in database
     *
     * @param WC_Order $order
     * @param string $reference_id
     * @param string $card_number
     * @param string $transaction_id
     * @param string $custom_message
     */
    protected function handle_success( $order, $reference_id, $card_number = '', $transaction_id = '', $custom_message = '' ) {
        $order_id = $order->get_id();

        // Check if already processed
        if ( ! $order->is_paid() ) {
            $order->payment_complete( $reference_id );

            $note = sprintf( 'پرداخت با موفقیت از طریق درگاه %s انجام شد.', $this->title );
            if ( ! empty( $reference_id ) ) {
                $note .= ' | شماره پیگیری: ' . $reference_id;
            }
            if ( ! empty( $card_number ) ) {
                $note .= ' | شماره کارت: ' . $card_number;
            }
            $order->add_order_note( $note );

            // Update order status if custom status configured
            if ( ! empty( $this->success_status ) && 'processing' !== $this->success_status ) {
                $order->update_status( $this->success_status, 'تغییر وضعیت خودکار توسط پیمنتیو' );
            }

            // Save order meta
            $order->update_meta_data( '_paymentio_gateway', $this->gateway_slug );
            $order->update_meta_data( '_paymentio_reference_id', $reference_id );
            if ( ! empty( $card_number ) ) {
                $order->update_meta_data( '_paymentio_card_number', $card_number );
            }
            $order->save();
        }

        // Update database log
        Paymentio_DB::update( $transaction_id ?: $order_id, array(
            'status'       => 'completed',
            'reference_id' => $reference_id,
            'card_number'  => $card_number,
            'message'      => $custom_message ?: 'تراکنش با موفقیت تایید شد.',
        ) );

        // Empty cart
        if ( function_exists( 'WC' ) && isset( WC()->cart ) ) {
            WC()->cart->empty_cart();
        }

        $notice = sprintf( 'پرداخت شما با موفقیت انجام شد. کد پیگیری: %s', $reference_id );
        wc_add_notice( $notice, 'success' );

        wp_safe_redirect( $this->get_return_url( $order ) );
        exit;
    }

    /**
     * Handle payment failure
     *
     * @param WC_Order $order
     * @param string $error_message
     * @param string $transaction_id
     */
    protected function handle_failure( $order, $error_message = '', $transaction_id = '' ) {
        $order_id = $order->get_id();

        if ( ! $error_message ) {
            $error_message = 'تراکنش توسط کاربر لغو شد یا در بانک انجام نگرفت.';
        }

        $order->add_order_note( sprintf( 'خطا در پرداخت با درگاه %s: %s', $this->title, $error_message ) );
        $order->update_status( 'failed', $error_message );

        // Update database log
        Paymentio_DB::update( $transaction_id ?: $order_id, array(
            'status'  => 'failed',
            'message' => $error_message,
        ) );

        wc_add_notice( sprintf( 'پرداخت ناموفق بود: %s', $error_message ), 'error' );

        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}
