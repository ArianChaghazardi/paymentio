<?php
/**
 * Paymentio Core Plugin Class
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio {

    /**
     * Singleton instance
     *
     * @var Paymentio|null
     */
    private static $instance = null;

    /**
     * Loaded gateway instances
     *
     * @var array
     */
    private $gateways = array();

    /**
     * Get singleton instance
     *
     * @return Paymentio
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once PAYMENTIO_PATH . 'includes/class-paymentio-db.php';
        require_once PAYMENTIO_PATH . 'includes/class-paymentio-helper.php';
        require_once PAYMENTIO_PATH . 'includes/class-paymentio-gateway-base.php';

        // Gateway classes
        require_once PAYMENTIO_PATH . 'includes/gateways/class-gateway-zarinpal.php';
        require_once PAYMENTIO_PATH . 'includes/gateways/class-gateway-zibal.php';
        require_once PAYMENTIO_PATH . 'includes/gateways/class-gateway-payping.php';
        require_once PAYMENTIO_PATH . 'includes/gateways/class-gateway-unified.php';
        require_once PAYMENTIO_PATH . 'includes/gateways/class-gateway-card.php';

        // Admin
        if ( is_admin() ) {
            require_once PAYMENTIO_PATH . 'includes/admin/class-paymentio-admin.php';
            Paymentio_Admin::instance();
        }
    }

    /**
     * Initialize WooCommerce hooks
     */
    private function init_hooks() {
        // Register gateways into WooCommerce
        add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateways' ) );

        // Enqueue checkout styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

        // Filter available gateways in checkout based on display mode
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_available_gateways' ) );

        // Currency Management (Toman / Rial)
        add_filter( 'woocommerce_currencies', array( $this, 'add_persian_currencies' ) );
        add_filter( 'woocommerce_currency_symbol', array( $this, 'add_persian_currency_symbols' ), 10, 2 );
        add_filter( 'wc_price', array( $this, 'format_persian_price' ), 10, 4 );

        // Gateway Referrer-Policy preservation for external bank redirects
        add_action( 'wp_head', array( $this, 'add_referrer_policy_meta' ), 1 );
        add_action( 'template_redirect', array( $this, 'send_referrer_policy_header' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_gateway_redirect' ), 5 );
        add_action( 'template_redirect', array( $this, 'maybe_render_card_payment_page' ), 5 );

        // Add action links to plugin list
        // add_filter( 'plugin_action_links_' . plugin_basename( PAYMENTIO_FILE ), array( $this, 'plugin_action_links' ) );
    }

    /**
     * Handle intermediate payment gateway redirection to ensure browser passes valid referrer header
     */
    public function maybe_render_gateway_redirect() {
        if ( ! isset( $_GET['paymentio_action'] ) || 'redirect_zarinpal' !== $_GET['paymentio_action'] ) {
            return;
        }

        $order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
        $order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( wp_unslash( $_GET['order_key'] ) ) : '';

        if ( ! $order_id || empty( $order_key ) ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_order_key() !== $order_key ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $pay_url = $order->get_meta( '_paymentio_zarinpal_pay_url' );
        if ( empty( $pay_url ) ) {
            $authority = $order->get_meta( '_paymentio_zarinpal_authority' );
            if ( ! empty( $authority ) ) {
                $pay_url = 'https://payment.zarinpal.com/pg/StartPay/' . $authority;
            } else {
                wp_safe_redirect( $order->get_checkout_payment_url() );
                exit;
            }
        }

        // Clean output buffers
        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }

        if ( ! headers_sent() ) {
            header( 'Content-Type: text/html; charset=UTF-8' );
            header( 'Referrer-Policy: unsafe-url' );
        }

        ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="unsafe-url">
    <title>در حال انتقال به درگاه پرداخت زرین پال</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            direction: rtl;
        }
        .paymentio-redirect-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.07);
            border: 1px solid #e2e8f0;
            padding: 40px 32px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
        }
        .paymentio-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top-color: #facc15;
            border-right-color: #ca8a04;
            border-radius: 50%;
            animation: paymentio-spin 0.8s linear infinite;
            margin: 0 auto 24px auto;
        }
        @keyframes paymentio-spin {
            to { transform: rotate(360deg); }
        }
        h2 {
            color: #0f172a;
            font-size: 19px;
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        p {
            color: #64748b;
            font-size: 14px;
            margin: 0 0 28px 0;
            line-height: 1.6;
        }
        .paymentio-btn {
            display: block;
            width: 100%;
            background: #facc15;
            color: #713f12;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .paymentio-btn:hover {
            background: #eab308;
            color: #422006;
        }
    </style>
</head>
<body>
    <div class="paymentio-redirect-card">
        <div class="paymentio-spinner"></div>
        <h2>در حال اتصال به درگاه زرین پال</h2>
        <p>لطفاً چند لحظه صبر کنید، در حال انتقال امن به شبکه پرداخت شاپرک هستید...</p>
        <form id="paymentio_zarinpal_form" action="<?php echo esc_url( $pay_url ); ?>" method="GET">
            <button type="submit" class="paymentio-btn">ورود به صفحه پرداخت</button>
        </form>
    </div>
    <script>
        setTimeout(function() {
            var form = document.getElementById("paymentio_zarinpal_form");
            if (form) {
                form.submit();
            }
        }, 80);
    </script>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Handle dedicated card to card payment page and receipt upload
     */
    public function maybe_render_card_payment_page() {
        if ( ! isset( $_GET['paymentio_action'] ) || 'card_pay' !== $_GET['paymentio_action'] ) {
            return;
        }

        $order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
        $order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( wp_unslash( $_GET['order_key'] ) ) : '';

        if ( ! $order_id || empty( $order_key ) ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_order_key() !== $order_key ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        // Handle receipt submission POST
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['paymentio_submit_receipt'] ) ) {
            if ( ! isset( $_POST['paymentio_receipt_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['paymentio_receipt_nonce'] ) ), 'paymentio_upload_receipt_' . $order_id ) ) {
                wp_die( 'خطای امنیتی: نشست شما منقضی شده است. لطفاً صفحه را رفرش فرمایید.', 'خطای امنیتی', array( 'response' => 403 ) );
            }

            if ( empty( $_FILES['receipt_image']['name'] ) || UPLOAD_ERR_OK !== $_FILES['receipt_image']['error'] ) {
                wc_add_notice( 'لطفاً فایل تصویر رسید را انتخاب فرمایید.', 'error' );
            } else {
                $card_settings = get_option( 'woocommerce_paymentio_card_settings', array() );
                $max_mb        = absint( $card_settings['max_file_size'] ?? 5 );
                $max_bytes     = $max_mb * 1024 * 1024;

                if ( $_FILES['receipt_image']['size'] > $max_bytes ) {
                    wc_add_notice( sprintf( 'حجم فایل بیشتر از حد مجاز (%s مگابایت) است.', Paymentio_Helper::to_persian_digits( $max_mb ) ), 'error' );
                } else {
                    $file_tmp  = $_FILES['receipt_image']['tmp_name'];
                    $file_name = sanitize_file_name( $_FILES['receipt_image']['name'] );
                    $ext       = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
                    $allowed   = array( 'jpg', 'jpeg', 'png', 'webp' );

                    // Check MIME type via finfo
                    $mime = '';
                    if ( function_exists( 'finfo_open' ) ) {
                        $finfo = finfo_open( FILEINFO_MIME_TYPE );
                        $mime  = finfo_file( $finfo, $file_tmp );
                        finfo_close( $finfo );
                    } elseif ( function_exists( 'mime_content_type' ) ) {
                        $mime = mime_content_type( $file_tmp );
                    }

                    $allowed_mimes = array( 'image/jpeg', 'image/png', 'image/webp' );

                    if ( ! in_array( $ext, $allowed, true ) || ( ! empty( $mime ) && ! in_array( $mime, $allowed_mimes, true ) ) ) {
                        wc_add_notice( 'فرمت فایل مجاز نیست. لطفاً تصویر با فرمت JPG، PNG یا WEBP بارگذاری کنید.', 'error' );
                    } else {
                        $upload_dir = wp_upload_dir();
                        $target_dir = $upload_dir['basedir'] . '/paymentio-receipts';

                        if ( ! file_exists( $target_dir ) ) {
                            wp_mkdir_p( $target_dir );
                            @file_put_contents( $target_dir . '/index.php', '<?php // Silence is golden' );
                            @file_put_contents( $target_dir . '/.htaccess', "<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|php8|phps|cgi|pl|exe)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\nOptions -Indexes" );
                        }

                        $safe_filename = 'receipt_' . $order_id . '_' . wp_generate_password( 12, false, false ) . '.' . $ext;
                        $target_path   = $target_dir . '/' . $safe_filename;
                        $target_url    = $upload_dir['baseurl'] . '/paymentio-receipts/' . $safe_filename;

                        if ( move_uploaded_file( $file_tmp, $target_path ) ) {
                            $tracking_code = sanitize_text_field( wp_unslash( $_POST['tracking_code'] ?? '' ) );
                            $payer_card    = Paymentio_Helper::clean_card_number( sanitize_text_field( wp_unslash( $_POST['payer_card'] ?? '' ) ) );
                            $customer_note = sanitize_textarea_field( wp_unslash( $_POST['customer_note'] ?? '' ) );
                            $card_number   = sanitize_text_field( wp_unslash( $_POST['card_number'] ?? '' ) );
                            $card_holder   = sanitize_text_field( wp_unslash( $_POST['card_holder'] ?? '' ) );
                            $bank_name     = sanitize_text_field( wp_unslash( $_POST['bank_name'] ?? '' ) );

                            $receipt_id = Paymentio_DB::insert_receipt( array(
                                'order_id'         => $order_id,
                                'amount'           => Paymentio_Helper::to_toman( $order->get_total(), $order->get_currency() ),
                                'currency'         => 'IRT',
                                'card_number'      => $card_number,
                                'card_holder'      => $card_holder,
                                'bank_name'        => $bank_name,
                                'payer_card'       => $payer_card,
                                'tracking_code'    => $tracking_code,
                                'receipt_url'      => $target_url,
                                'receipt_path'     => $target_path,
                                'receipt_filename' => $safe_filename,
                                'receipt_filesize' => (int) $_FILES['receipt_image']['size'],
                                'customer_name'    => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
                                'customer_mobile'  => $order->get_billing_phone(),
                                'customer_email'   => $order->get_billing_email(),
                                'customer_note'    => $customer_note,
                                'status'           => 'pending',
                            ) );

                            // Update order meta
                            $order->update_meta_data( '_paymentio_receipt_id', $receipt_id );
                            $order->update_meta_data( '_paymentio_receipt_url', $target_url );
                            $order->update_meta_data( '_paymentio_tracking_code', $tracking_code );
                            $order->update_meta_data( '_paymentio_payer_card', $payer_card );
                            $order->update_meta_data( '_paymentio_receipt_status', 'pending' );
                            $order->save();

                            // Order note
                            $note = sprintf(
                                'رسید پرداخت کارت به کارت توسط مشتری بارگذاری شد. کد پیگیری: %s | کارت مبدا: %s',
                                $tracking_code ?: 'ثبت نشده',
                                $payer_card ?: 'ثبت نشده'
                            );
                            $order->add_order_note( $note );

                            // Update transaction record
                            $tx = Paymentio_DB::get_by_order_id( $order_id );
                            if ( $tx ) {
                                Paymentio_DB::update( $tx->id, array(
                                    'reference_id' => $tracking_code,
                                    'card_number'  => $payer_card ?: $card_number,
                                    'message'      => 'رسید پرداخت توسط مشتری ثبت شد و در انتظار تایید است.',
                                ) );
                            }

                            // Redirect to refresh page and show status
                            wp_safe_redirect( add_query_arg(
                                array(
                                    'paymentio_action' => 'card_pay',
                                    'order_id'         => $order_id,
                                    'order_key'        => $order_key,
                                    'submitted'        => '1',
                                ),
                                home_url( '/' )
                            ) );
                            exit;
                        } else {
                            wc_add_notice( 'خطا در بارگذاری فایل. لطفاً مجدداً تلاش فرمایید.', 'error' );
                        }
                    }
                }
            }
        }

        // Clean output buffers
        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }

        if ( ! headers_sent() ) {
            header( 'Content-Type: text/html; charset=UTF-8' );
        }

        require_once PAYMENTIO_PATH . 'includes/templates/card-payment-page.php';
        exit;
    }

    /**
     * Add Referrer-Policy meta tag on checkout and payment pages
     */
    public function add_referrer_policy_meta() {
        if ( ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) ) {
            echo '<meta name="referrer" content="strict-origin-when-cross-origin" />' . "\n";
        }
    }

    /**
     * Send Referrer-Policy HTTP header on checkout and payment pages
     */
    public function send_referrer_policy_header() {
        if ( ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) ) {
            if ( ! headers_sent() ) {
                header( 'Referrer-Policy: strict-origin-when-cross-origin' );
            }
        }
    }

    /**
     * Register payment gateways into WooCommerce
     *
     * @param array $methods
     * @return array
     */
    public function register_gateways( $methods ) {
        $mode = get_option( 'paymentio_display_mode', 'separate' );

        // Always register sub-gateways so their webhook/API callbacks work
        $methods[] = 'Paymentio_Gateway_Zarinpal';
        $methods[] = 'Paymentio_Gateway_Zibal';
        $methods[] = 'Paymentio_Gateway_Payping';
        $methods[] = 'Paymentio_Gateway_Unified';
        $methods[] = 'Paymentio_Gateway_Card';

        return $methods;
    }

    /**
     * Enqueue frontend scripts on checkout
     */
    public function enqueue_frontend_scripts() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }

        wp_enqueue_style(
            'paymentio-checkout-style',
            PAYMENTIO_URL . 'assets/css/checkout.css',
            array(),
            PAYMENTIO_VERSION
        );

        wp_enqueue_script(
            'paymentio-checkout-script',
            PAYMENTIO_URL . 'assets/js/checkout.js',
            array( 'jquery' ),
            PAYMENTIO_VERSION,
            true
        );
    }

    /**
     * Add settings link to plugins list table
     *
     * @param array $links
     * @return array
     */
    public function plugin_action_links( $links ) {
        $custom_links = array(
            '<a href="' . esc_url( admin_url( 'admin.php?page=paymentio' ) ) . '">مدیریت پیمنتیو</a>',
            '<a href="' . esc_url( admin_url( 'admin.php?page=paymentio-gateways' ) ) . '">تنظیمات درگاه ها</a>',
        );
        return array_merge( $custom_links, $links );
    }

    /**
     * Get active sub gateways
     *
     * @return array Array of gateway objects indexed by slug
     */
    public function get_active_sub_gateways() {
        $active   = array();
        $gateways = array(
            'zarinpal' => new Paymentio_Gateway_Zarinpal(),
            'zibal'    => new Paymentio_Gateway_Zibal(),
            'payping'  => new Paymentio_Gateway_Payping(),
        );

        foreach ( $gateways as $slug => $gw ) {
            if ( 'yes' === $gw->enabled ) {
                $active[ $slug ] = $gw;
            }
        }

        return $active;
    }

    /**
     * Filter available gateways based on Paymentio display mode
     *
     * @param array $gateways
     * @return array
     */
    public function filter_available_gateways( $gateways ) {
        if ( is_admin() ) {
            return $gateways;
        }

        $mode = get_option( 'paymentio_display_mode', 'separate' );

        if ( 'unified' === $mode ) {
            // In unified mode, hide individual gateways so only unified is shown
            unset( $gateways['paymentio_zarinpal'] );
            unset( $gateways['paymentio_zibal'] );
            unset( $gateways['paymentio_payping'] );
        } else {
            // In separate mode, hide unified gateway so only enabled individual gateways are shown
            unset( $gateways['paymentio_unified'] );
        }

        return $gateways;
    }

    /**
     * Add Persian currencies to WooCommerce
     *
     * @param array $currencies
     * @return array
     */
    public function add_persian_currencies( $currencies ) {
        $currencies['IRT']  = 'تومان ایران';
        $currencies['IRR']  = 'ریال ایران';
        $currencies['IRHT'] = 'هزار تومان ایران';
        $currencies['IRHR'] = 'هزار ریال ایران';
        return $currencies;
    }

    /**
     * Add symbols for Persian currencies
     *
     * @param string $symbol
     * @param string $currency
     * @return string
     */
    public function add_persian_currency_symbols( $symbol, $currency ) {
        switch ( $currency ) {
            case 'IRT':
                return 'تومان';
            case 'IRR':
                return 'ریال';
            case 'IRHT':
                return 'هزار تومان';
            case 'IRHR':
                return 'هزار ریال';
        }
        return $symbol;
    }

    /**
     * Format WooCommerce price with Persian digits
     *
     * @param string $return
     * @param float $price
     * @param array $args
     * @param float $unformatted_price
     * @return string
     */
    public function format_persian_price( $return, $price, $args, $unformatted_price ) {
        return Paymentio_Helper::to_persian_digits( $return );
    }
}
