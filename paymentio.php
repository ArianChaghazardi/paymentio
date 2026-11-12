<?php
/**
 * Plugin Name: پیمنتیو | سامانه جامع مدیریت درگاه های پرداخت ووکامرس
 * Plugin URI: https://hectortm.ir/paymentio
 * Description: افزونه جامع و مدرن درگاه های پرداخت ووکامرس، پرداخت کارت به کارت و مدیریت فیش های واریزی، هدایت خودکار بانکی و گزارش هوشمند تراکنش ها.
 * Version: 1.1.0
 * Author: آرین چقازردی
 * Author URI: https://arianchaghazardi.ir
 * Developer: هکتورتیم
 * Developer URI: https://hectortm.ir
 * Text Domain: paymentio
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 11.2
 *
 * @package Paymentio
 * @author آرین چقازردی
 * @developer هکتورتیم
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Plugin Constants
define( 'PAYMENTIO_VERSION', '1.1.0' );
define( 'PAYMENTIO_FILE', __FILE__ );
define( 'PAYMENTIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'PAYMENTIO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, 'paymentio_activate_plugin' );
function paymentio_activate_plugin() {
    require_once PAYMENTIO_PATH . 'includes/class-paymentio-db.php';
    Paymentio_DB::create_tables();

    // Default options if not exists
    if ( false === get_option( 'paymentio_display_mode' ) ) {
        update_option( 'paymentio_display_mode', 'separate' );
    }
    if ( false === get_option( 'paymentio_default_gateway' ) ) {
        update_option( 'paymentio_default_gateway', 'zarinpal' );
    }
    if ( false === get_option( 'paymentio_auto_failover' ) ) {
        update_option( 'paymentio_auto_failover', 'yes' );
    }
    if ( false === get_option( 'paymentio_currency' ) ) {
        update_option( 'paymentio_currency', 'IRT' );
        update_option( 'woocommerce_currency', 'IRT' );
    }
}

/**
 * Declare WooCommerce HPOS and Feature Compatibility
 */
add_action( 'before_woocommerce_init', 'paymentio_declare_compatibility' );
function paymentio_declare_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PAYMENTIO_FILE, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', PAYMENTIO_FILE, true );
    }
}

/**
 * Check requirements and bootstrap plugin
 */
add_action( 'plugins_loaded', 'paymentio_bootstrap', 11 );
function paymentio_bootstrap() {
    // Check WooCommerce Dependency
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'paymentio_woocommerce_missing_notice' );
        return;
    }

    // Auto-update database tables on version change
    if ( get_option( 'paymentio_version' ) !== PAYMENTIO_VERSION ) {
        require_once PAYMENTIO_PATH . 'includes/class-paymentio-db.php';
        Paymentio_DB::create_tables();
        update_option( 'paymentio_version', PAYMENTIO_VERSION );
    }

    // Load Core Plugin
    require_once PAYMENTIO_PATH . 'includes/class-paymentio.php';
    Paymentio::instance();
}

/**
 * WooCommerce Missing Admin Notice
 */
function paymentio_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error is-dismissible" dir="rtl">
        <p>
            <strong>افزونه پیمنتیو (Paymentio):</strong> برای استفاده از این افزونه، فروشگاه ساز ووکامرس (WooCommerce) باید نصب و فعال باشد.
        </p>
    </div>
    <?php
}
