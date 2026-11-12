<?php
/**
 * Paymentio Admin Management Class
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Admin {

    /**
     * Instance
     *
     * @var Paymentio_Admin|null
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Paymentio_Admin
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
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // AJAX handlers
        add_action( 'wp_ajax_paymentio_toggle_gateway', array( $this, 'ajax_toggle_gateway' ) );
        add_action( 'wp_ajax_paymentio_test_gateway', array( $this, 'ajax_test_gateway' ) );
        add_action( 'wp_ajax_paymentio_save_gateway_settings', array( $this, 'ajax_save_gateway_settings' ) );
        add_action( 'wp_ajax_paymentio_approve_receipt', array( $this, 'ajax_approve_receipt' ) );
        add_action( 'wp_ajax_paymentio_reject_receipt', array( $this, 'ajax_reject_receipt' ) );
        add_action( 'wp_ajax_paymentio_save_card_gateway_settings', array( $this, 'ajax_save_card_gateway_settings' ) );

        // WooCommerce Order Screen Metabox
        add_action( 'add_meta_boxes', array( $this, 'register_order_metabox' ) );
    }

    /**
     * Register Admin Menu
     */
    public function register_admin_menu() {
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#38bdf8"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>'
        );

        add_menu_page(
            'پیمنتیو',
            'پیمنتیو',
            'manage_woocommerce',
            'paymentio',
            array( $this, 'render_admin_page' ),
            $icon_svg,
            56
        );

        add_submenu_page(
            'paymentio',
            'پیشخوان - پیمنتیو',
            'پیشخوان',
            'manage_woocommerce',
            'paymentio',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'paymentio',
            'درگاه های پرداخت - پیمنتیو',
            'درگاه های پرداخت',
            'manage_woocommerce',
            'paymentio-gateways',
            array( $this, 'render_admin_page' )
        );

        $pending_count  = Paymentio_DB::get_pending_receipts_count();
        $receipts_label = 'رسید های پرداخت';
        if ( $pending_count > 0 ) {
            $receipts_label .= ' <span class="update-plugins count-' . $pending_count . '"><span class="plugin-count">' . Paymentio_Helper::to_persian_digits( $pending_count ) . '</span></span>';
        }

        add_submenu_page(
            'paymentio',
            'رسید های پرداخت - پیمنتیو',
            $receipts_label,
            'manage_woocommerce',
            'paymentio-receipts',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'paymentio',
            'تراکنش ها - پیمنتیو',
            'تراکنش ها',
            'manage_woocommerce',
            'paymentio-transactions',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'paymentio',
            'تنظیمات عمومی - پیمنتیو',
            'تنظیمات عمومی',
            'manage_woocommerce',
            'paymentio-settings',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'paymentio',
            'وضعیت سیستم - پیمنتیو',
            'وضعیت سیستم',
            'manage_woocommerce',
            'paymentio-health',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Enqueue Admin Assets
     *
     * @param string $hook
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'paymentio' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'paymentio-admin-style',
            PAYMENTIO_URL . 'assets/css/admin.css',
            array(),
            PAYMENTIO_VERSION
        );

        wp_enqueue_script(
            'paymentio-admin-script',
            PAYMENTIO_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            PAYMENTIO_VERSION,
            true
        );

        wp_localize_script( 'paymentio-admin-script', 'paymentioData', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'paymentio_admin_nonce' ),
        ) );
    }

    /**
     * Render Admin Pages
     */
    public function render_admin_page() {
        $page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'paymentio';
        $tab  = 'dashboard';

        if ( 'paymentio-gateways' === $page ) {
            $tab = 'gateways';
        } elseif ( 'paymentio-receipts' === $page ) {
            $tab = 'receipts';
        } elseif ( 'paymentio-transactions' === $page ) {
            $tab = 'transactions';
        } elseif ( 'paymentio-settings' === $page ) {
            $tab = 'settings';
        } elseif ( 'paymentio-health' === $page ) {
            $tab = 'health';
        }

        $view_file = PAYMENTIO_PATH . 'includes/admin/views/' . $tab . '.php';

        echo '<div class="paymentio-wrap" dir="rtl">';
        $this->render_header( $tab );

        if ( file_exists( $view_file ) ) {
            include $view_file;
        } else {
            echo '<div class="paymentio-card"><p>صفحه مورد نظر یافت نشد.</p></div>';
        }

        $this->render_footer();
        echo '</div>';
    }

    /**
     * Render header navigation
     *
     * @param string $active_tab
     */
    private function render_header( $active_tab ) {
        $pending_count      = Paymentio_DB::get_pending_receipts_count();
        $receipts_tab_title = 'رسید های پرداخت';
        if ( $pending_count > 0 ) {
            $receipts_tab_title .= ' (' . Paymentio_Helper::to_persian_digits( $pending_count ) . ')';
        }

        $tabs = array(
            'dashboard'    => array( 'title' => 'پیشخوان', 'url' => admin_url( 'admin.php?page=paymentio' ), 'icon' => 'dashboard' ),
            'gateways'     => array( 'title' => 'درگاه های پرداخت', 'url' => admin_url( 'admin.php?page=paymentio-gateways' ), 'icon' => 'gateways' ),
            'receipts'     => array( 'title' => $receipts_tab_title, 'url' => admin_url( 'admin.php?page=paymentio-receipts' ), 'icon' => 'receipt' ),
            'transactions' => array( 'title' => 'تراکنش ها', 'url' => admin_url( 'admin.php?page=paymentio-transactions' ), 'icon' => 'transactions' ),
            'settings'     => array( 'title' => 'تنظیمات عمومی', 'url' => admin_url( 'admin.php?page=paymentio-settings' ), 'icon' => 'settings' ),
            'health'       => array( 'title' => 'وضعیت سیستم و تست اتصال', 'url' => admin_url( 'admin.php?page=paymentio-health' ), 'icon' => 'health' ),
        );
        ?>
        <header class="paymentio-header">
            <div class="paymentio-header-inner">
                <div class="paymentio-brand">
                    <div class="paymentio-brand-icon">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    <div class="paymentio-brand-text">
                        <div class="paymentio-brand-title">
                            <h1>پیمنتیو</h1>
                            <span class="paymentio-brand-badge">Paymentio v<?php echo esc_html( PAYMENTIO_VERSION ); ?></span>
                        </div>
                        <p class="paymentio-brand-desc">
                            سیستم پیشرفته مدیریت درگاه های پرداخت، تسویه حساب هوشمند و تراکنش های ووکامرس
                        </p>
                    </div>
                </div>

                <div class="paymentio-header-meta">
                    <div class="paymentio-status-pill">
                        <span class="paymentio-pulse-dot"></span>
                        <span>سرویس درگاه های پرداخت فعال و آنلاین</span>
                    </div>
                    <div class="paymentio-author-badge">
                        <span>توسعه توسط</span>
                        <a href="https://arianchaghazardi.ir" target="_blank" rel="noopener">آرین چقازردی</a>
                        <span class="paymentio-sep">&bull;</span>
                        <a href="https://hectortm.ir" target="_blank" rel="noopener">هکتورتیم</a>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="paymentio-tabs paymentio-nav">
                <?php foreach ( $tabs as $id => $tab ) : ?>
                    <a href="<?php echo esc_url( $tab['url'] ); ?>" class="paymentio-tab-btn paymentio-nav-item <?php echo $active_tab === $id ? 'active' : ''; ?>">
                        <span class="paymentio-tab-icon"><?php echo Paymentio_Helper::icon( $tab['icon'], 18 ); ?></span>
                        <span class="paymentio-tab-text"><?php echo esc_html( $tab['title'] ); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </header>
        <?php
    }

    /**
     * Render footer
     */
    private function render_footer() {
        ?>
        <footer class="paymentio-footer">
            <div class="paymentio-footer-inner">
                <div class="paymentio-footer-left">
                    <span>پیمنتیو (Paymentio) &mdash; نسخه <?php echo esc_html( PAYMENTIO_VERSION ); ?></span>
                    <span class="paymentio-sep">|</span>
                    <span>سامانه جامع و هوشمند مدیریت درگاه های پرداخت و تراکنش های ووکامرس</span>
                </div>
                <div class="paymentio-footer-right">
                    <span>طراحی و توسعه توسط</span>
                    <a href="https://arianchaghazardi.ir" target="_blank" rel="noopener">آرین چقازردی</a>
                    <span class="paymentio-sep">&bull;</span>
                    <a href="https://hectortm.ir" target="_blank" rel="noopener">هکتورتیم</a>
                </div>
            </div>
        </footer>
        <?php
    }

    public function ajax_toggle_gateway() {
        check_ajax_referer( 'paymentio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی مجاز' ) );
        }

        $gateway_id = isset( $_POST['gateway_id'] ) ? sanitize_text_field( $_POST['gateway_id'] ) : '';
        $enabled    = isset( $_POST['enabled'] ) && '1' === (string) $_POST['enabled'] ? 'yes' : 'no';

        if ( empty( $gateway_id ) ) {
            wp_send_json_error( array( 'message' => 'شناسه درگاه نامعتبر است' ) );
        }

        $settings = get_option( 'woocommerce_' . $gateway_id . '_settings', array() );
        $settings['enabled'] = $enabled;
        update_option( 'woocommerce_' . $gateway_id . '_settings', $settings );

        wp_send_json_success( array(
            'message' => 'وضعیت درگاه با موفقیت تغییر کرد.',
            'enabled' => $enabled,
        ) );
    }

    /**
     * AJAX handler to test gateway API latency
     */
    public function ajax_test_gateway() {
        check_ajax_referer( 'paymentio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی مجاز' ) );
        }

        $gateway = isset( $_POST['gateway'] ) ? sanitize_text_field( $_POST['gateway'] ) : '';

        $urls = array(
            'zarinpal' => 'https://payment.zarinpal.com/pg/v4/payment/request.json',
            'zibal'    => 'https://gateway.zibal.ir/v1/request',
            'payping'  => 'https://api.payping.ir/v3/pay',
        );

        if ( ! isset( $urls[ $gateway ] ) ) {
            wp_send_json_error( array( 'message' => 'درگاه نامعتبر است' ) );
        }

        $result = Paymentio_Helper::ping_gateway( $urls[ $gateway ] );

        wp_send_json_success( $result );
    }

    /**
     * AJAX handler to save gateway settings
     */
    public function ajax_save_gateway_settings() {
        check_ajax_referer( 'paymentio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی مجاز' ) );
        }

        $gateway_id = isset( $_POST['gateway_id'] ) ? sanitize_text_field( $_POST['gateway_id'] ) : '';

        if ( empty( $gateway_id ) ) {
            wp_send_json_error( array( 'message' => 'شناسه درگاه نامعتبر است' ) );
        }

        $settings = get_option( 'woocommerce_' . $gateway_id . '_settings', array() );

        if ( isset( $_POST['title'] ) ) {
            $settings['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ) );
        }
        if ( isset( $_POST['description'] ) ) {
            $settings['description'] = sanitize_textarea_field( wp_unslash( $_POST['description'] ) );
        }
        if ( isset( $_POST['merchant_id'] ) ) {
            $settings['merchant_id'] = sanitize_text_field( wp_unslash( $_POST['merchant_id'] ) );
        }
        if ( isset( $_POST['merchant'] ) ) {
            $settings['merchant'] = sanitize_text_field( wp_unslash( $_POST['merchant'] ) );
        }
        if ( isset( $_POST['token'] ) ) {
            $settings['token'] = sanitize_text_field( wp_unslash( $_POST['token'] ) );
        }
        if ( isset( $_POST['sandbox'] ) ) {
            $settings['sandbox'] = '1' === (string) $_POST['sandbox'] ? 'yes' : 'no';
        }
        if ( isset( $_POST['zaringate'] ) ) {
            $settings['zaringate'] = '1' === (string) $_POST['zaringate'] ? 'yes' : 'no';
        }
        if ( isset( $_POST['success_status'] ) ) {
            $settings['success_status'] = sanitize_text_field( wp_unslash( $_POST['success_status'] ) );
        }

        update_option( 'woocommerce_' . $gateway_id . '_settings', $settings );

        wp_send_json_success( array(
            'message' => 'تنظیمات درگاه با موفقیت ذخیره شد.',
        ) );
    }

    /**
     * AJAX handler to approve card receipt
     */
    public function ajax_approve_receipt() {
        check_ajax_referer( 'paymentio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی مجاز' ) );
        }

        $receipt_id = isset( $_POST['receipt_id'] ) ? absint( $_POST['receipt_id'] ) : 0;
        if ( ! $receipt_id ) {
            wp_send_json_error( array( 'message' => 'شناسه رسید نامعتبر است' ) );
        }

        $result = Paymentio_DB::approve_receipt( $receipt_id, get_current_user_id() );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => 'رسید پرداخت با موفقیت تایید شد و سفارش به روزرسانی گردید.',
        ) );
    }

    /**
     * AJAX handler to reject card receipt
     */
    public function ajax_reject_receipt() {
        check_ajax_referer( 'paymentio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی مجاز' ) );
        }

        $receipt_id = isset( $_POST['receipt_id'] ) ? absint( $_POST['receipt_id'] ) : 0;
        $reason     = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

        if ( ! $receipt_id ) {
            wp_send_json_error( array( 'message' => 'شناسه رسید نامعتبر است' ) );
        }

        $result = Paymentio_DB::reject_receipt( $receipt_id, $reason, get_current_user_id() );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => 'رسید پرداخت رد شد و وضعیت سفارش به روزرسانی گردید.',
        ) );
    }

    /**
     * AJAX handler to save card gateway settings
     */
    public function ajax_save_card_gateway_settings() {
        check_ajax_referer( 'paymentio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی مجاز' ) );
        }

        $settings = get_option( 'woocommerce_paymentio_card_settings', array() );

        if ( isset( $_POST['title'] ) ) {
            $settings['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ) );
        }
        if ( isset( $_POST['description'] ) ) {
            $settings['description'] = sanitize_textarea_field( wp_unslash( $_POST['description'] ) );
        }
        if ( isset( $_POST['success_status'] ) ) {
            $settings['success_status'] = sanitize_text_field( wp_unslash( $_POST['success_status'] ) );
        }
        if ( isset( $_POST['max_file_size'] ) ) {
            $settings['max_file_size'] = absint( $_POST['max_file_size'] );
        }
        if ( isset( $_POST['rules_notes'] ) ) {
            $settings['rules_notes'] = sanitize_textarea_field( wp_unslash( $_POST['rules_notes'] ) );
        }

        // Process bank cards repeater
        if ( isset( $_POST['cards'] ) && is_array( $_POST['cards'] ) ) {
            $sanitized_cards = array();
            foreach ( $_POST['cards'] as $card ) {
                $num = Paymentio_Helper::clean_card_number( $card['card_number'] ?? '' );
                if ( ! empty( $num ) ) {
                    $bank_slug = sanitize_text_field( $card['bank_slug'] ?? 'other' );
                    $bank_name = sanitize_text_field( $card['bank_name'] ?? '' );

                    if ( empty( $bank_name ) || 'other' === $bank_slug ) {
                        $detected  = Paymentio_Helper::detect_bank( $num );
                        $bank_name = $detected['name'];
                        $bank_slug = $detected['slug'];
                    }

                    $raw_shaba    = sanitize_text_field( $card['shaba'] ?? '' );
                    $shaba_digits = preg_replace( '/[^0-9]/', '', $raw_shaba );
                    $clean_shaba  = ( empty( $shaba_digits ) || preg_match( '/^0+$/', $shaba_digits ) || 'IR...' === trim( $raw_shaba ) ) ? '' : $raw_shaba;

                    $sanitized_cards[] = array(
                        'bank_slug'   => $bank_slug,
                        'bank_name'   => $bank_name,
                        'card_number' => $num,
                        'card_holder' => sanitize_text_field( $card['card_holder'] ?? '' ),
                        'shaba'       => $clean_shaba,
                    );
                }
            }
            $settings['cards'] = $sanitized_cards;

            // Also keep first card in top level keys for backward compatibility
            if ( ! empty( $sanitized_cards ) ) {
                $settings['card_number'] = $sanitized_cards[0]['card_number'];
                $settings['card_holder'] = $sanitized_cards[0]['card_holder'];
                $settings['bank_name']   = $sanitized_cards[0]['bank_name'];
                $settings['shaba']       = $sanitized_cards[0]['shaba'];
            }
        }

        update_option( 'woocommerce_paymentio_card_settings', $settings );

        wp_send_json_success( array(
            'message' => 'تنظیمات درگاه کارت به کارت با موفقیت ذخیره شد.',
        ) );
    }

    /**
     * Register WooCommerce Order screen metabox
     */
    public function register_order_metabox() {
        $screens = array( 'shop_order' );
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
            $screens[] = wc_get_page_screen_id( 'shop-order' );
        }

        foreach ( $screens as $screen ) {
            add_meta_box(
                'paymentio_card_receipt_metabox',
                'رسید پرداخت کارت به کارت (پیمنتیو)',
                array( $this, 'render_order_metabox' ),
                $screen,
                'side',
                'high'
            );
        }
    }

    /**
     * Render WooCommerce Order screen metabox
     *
     * @param WP_Post|WC_Order $post_or_order_object
     */
    public function render_order_metabox( $post_or_order_object ) {
        $order = ( $post_or_order_object instanceof WC_Order ) ? $post_or_order_object : wc_get_order( $post_or_order_object->ID );
        if ( ! $order ) {
            echo '<p>سفارش یافت نشد.</p>';
            return;
        }

        if ( 'paymentio_card' !== $order->get_payment_method() ) {
            echo '<p style="color: #64748b; font-size: 13px; margin: 0;">این سفارش از طریق کارت به کارت ثبت نشده است (روش پرداخت: ' . esc_html( $order->get_payment_method_title() ) . ').</p>';
            return;
        }

        $order_id = $order->get_id();
        $receipt  = Paymentio_DB::get_receipt_by_order( $order_id );
        $pay_url  = add_query_arg(
            array(
                'paymentio_action' => 'card_pay',
                'order_id'         => $order_id,
                'order_key'        => $order->get_order_key(),
            ),
            home_url( '/' )
        );

        ?>
        <div class="paymentio-order-metabox" style="direction: rtl; text-align: right;">
            <?php if ( $receipt ) : ?>
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 12px; color: #64748b;">وضعیت رسید:</span>
                        <?php if ( 'approved' === $receipt->status ) : ?>
                            <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">تایید شده</span>
                        <?php elseif ( 'rejected' === $receipt->status ) : ?>
                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">رد شده</span>
                        <?php else : ?>
                            <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">در انتظار بررسی</span>
                        <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $receipt->receipt_url ) ) : ?>
                        <div style="text-align: center; margin: 10px 0;">
                            <a href="<?php echo esc_url( $receipt->receipt_url ); ?>" target="_blank" title="مشاهده اندازه کامل">
                                <img src="<?php echo esc_url( $receipt->receipt_url ); ?>" alt="تصویر رسید" style="max-width: 100%; max-height: 180px; border-radius: 8px; border: 1px solid #cbd5e1; object-fit: cover;">
                            </a>
                        </div>
                    <?php endif; ?>

                    <div style="font-size: 12px; line-height: 1.8; color: #334155; background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <?php if ( ! empty( $receipt->tracking_code ) ) : ?>
                            <div>کد پیگیری: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->tracking_code ) ); ?></strong></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $receipt->payer_card ) ) : ?>
                            <div>۴ رقم آخر کارت: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->payer_card ) ); ?></strong></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $receipt->bank_name ) ) : ?>
                            <div>بانک مقصد: <strong><?php echo esc_html( $receipt->bank_name ); ?></strong></div>
                        <?php endif; ?>
                        <div>تاریخ ارسال: <?php echo esc_html( Paymentio_Helper::to_persian_digits( $receipt->created_at ) ); ?></div>

                        <?php if ( ! empty( $receipt->customer_note ) ) : ?>
                            <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #cbd5e1; color: #475569;">
                                یادداشت مشتری: <?php echo esc_html( $receipt->customer_note ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( 'rejected' === $receipt->status && ! empty( $receipt->rejection_reason ) ) : ?>
                            <div style="margin-top: 6px; color: #b91c1c; font-weight: bold;">
                                علت رد: <?php echo esc_html( $receipt->rejection_reason ); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ( 'pending' === $receipt->status ) : ?>
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <button type="button" class="button button-primary paymentio-metabox-approve" data-id="<?php echo esc_attr( $receipt->id ); ?>" style="flex: 1; text-align: center; background: #16a34a; border-color: #15803d;">
                                تایید پرداخت
                            </button>
                            <button type="button" class="button paymentio-metabox-reject" data-id="<?php echo esc_attr( $receipt->id ); ?>" style="flex: 1; text-align: center; color: #dc2626;">
                                رد رسید
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div style="padding: 12px; background: #fefce8; border: 1px solid #fef08a; border-radius: 6px; margin-bottom: 10px;">
                    <p style="margin: 0 0 8px 0; font-size: 12px; color: #854d0e;">
                        رسیدی برای این سفارش بارگذاری نشده است.
                    </p>
                    <a href="<?php echo esc_url( $pay_url ); ?>" target="_blank" style="font-size: 11px; font-weight: bold; color: #0284c7; text-decoration: none;">
                        ورود به صفحه ثبت فیش &larr;
                    </a>
                </div>
            <?php endif; ?>

            <div style="padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 11px; text-align: left;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=paymentio-receipts' ) ); ?>" style="text-decoration: none;">
                    مشاهده همه رسید ها در پیمنتیو &larr;
                </a>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('.paymentio-metabox-approve').on('click', function(e) {
                    e.preventDefault();
                    if (!confirm('آیا از تایید این رسید پرداخت و تکمیل سفارش اطمینان دارید؟')) return;
                    var btn = $(this);
                    var receiptId = btn.data('id');
                    btn.prop('disabled', true).text('در حال ثبت...');

                    $.post(ajaxurl, {
                        action: 'paymentio_approve_receipt',
                        nonce: '<?php echo esc_js( wp_create_nonce( 'paymentio_admin_nonce' ) ); ?>',
                        receipt_id: receiptId
                    }, function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert(res.data.message || 'خطا در تایید');
                            btn.prop('disabled', false).text('تایید پرداخت');
                        }
                    });
                });

                $('.paymentio-metabox-reject').on('click', function(e) {
                    e.preventDefault();
                    var reason = prompt('لطفاً دلیل رد این رسید را وارد نمایید:');
                    if (reason === null) return;
                    var btn = $(this);
                    var receiptId = btn.data('id');
                    btn.prop('disabled', true).text('در حال ثبت...');

                    $.post(ajaxurl, {
                        action: 'paymentio_reject_receipt',
                        nonce: '<?php echo esc_js( wp_create_nonce( 'paymentio_admin_nonce' ) ); ?>',
                        receipt_id: receiptId,
                        reason: reason
                    }, function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert(res.data.message || 'خطا در رد رسید');
                            btn.prop('disabled', false).text('رد رسید');
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
