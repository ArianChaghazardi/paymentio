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

        // Add action links to plugin list
        add_filter( 'plugin_action_links_' . plugin_basename( PAYMENTIO_FILE ), array( $this, 'plugin_action_links' ) );
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
}
