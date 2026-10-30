<?php
/**
 * Paymentio Admin Management Class
 *
 * @package Paymentio
 * @author Arian Chaghazardi
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
        $tabs = array(
            'dashboard'    => array( 'title' => 'پیشخوان', 'url' => admin_url( 'admin.php?page=paymentio' ), 'icon' => 'dashicons-dashboard' ),
            'gateways'     => array( 'title' => 'درگاه های پرداخت', 'url' => admin_url( 'admin.php?page=paymentio-gateways' ), 'icon' => 'dashicons-cart' ),
            'transactions' => array( 'title' => 'تراکنش ها', 'url' => admin_url( 'admin.php?page=paymentio-transactions' ), 'icon' => 'dashicons-list-view' ),
            'settings'     => array( 'title' => 'تنظیمات عمومی', 'url' => admin_url( 'admin.php?page=paymentio-settings' ), 'icon' => 'dashicons-admin-generic' ),
            'health'       => array( 'title' => 'وضعیت سیستم و تست اتصال', 'url' => admin_url( 'admin.php?page=paymentio-health' ), 'icon' => 'dashicons-heart' ),
        );
        ?>
        <div class="paymentio-header">
            <div class="paymentio-header-top">
                <div class="paymentio-brand">
                    <div class="paymentio-logo-badge">P</div>
                    <div>
                        <h1 class="paymentio-title">پیمنتیو <span>Paymentio</span></h1>
                        <p class="paymentio-subtitle">مدیریت یکپارچه و هوشمند درگاه های پرداخت ووکامرس</p>
                    </div>
                </div>
                <div class="paymentio-author-badge">
                    <span>توسعه دهنده: <strong>آرین چقازردی</strong> در <strong>هکتورتیم</strong></span>
                    <a href="https://hectortm.ir" target="_blank" class="paymentio-link">hectortm.ir</a>
                </div>
            </div>

            <nav class="paymentio-nav">
                <?php foreach ( $tabs as $id => $tab ) : ?>
                    <a href="<?php echo esc_url( $tab['url'] ); ?>" class="paymentio-nav-item <?php echo $active_tab === $id ? 'active' : ''; ?>">
                        <span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
                        <?php echo esc_html( $tab['title'] ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <?php
    }

    /**
     * Render footer
     */
    private function render_footer() {
        ?>
        <div class="paymentio-footer">
            <p>افزونه پرداخت ووکامرس <strong>پیمنتیو</strong> نسخه <?php echo esc_html( PAYMENTIO_VERSION ); ?> | طراحی شده توسط <a href="https://arianchaghazardi.ir" target="_blank">آرین چقازردی</a> در <a href="https://hectortm.ir" target="_blank">هکتورتیم (HectorTM)</a></p>
        </div>
        <?php
    }

    /**
     * AJAX handler to toggle gateway status
     */
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
}
