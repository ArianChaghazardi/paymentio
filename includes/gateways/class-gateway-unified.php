<?php
/**
 * Paymentio Unified Smart Gateway
 * Allows single checkout entry with sub-gateway selector and smart routing
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Gateway_Unified extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'paymentio_unified';
        $this->has_fields         = true;
        $this->method_title       = 'درگاه یکپارچه هوشمند (پیمنتیو)';
        $this->method_description = 'تجمیع تمام درگاه های فعال در یک درگاه واحد در صفحه تسویه حساب با امکان انتخاب مستقیم یا هدایت هوشمند';

        $this->init_form_fields();
        $this->init_settings();

        $mode              = get_option( 'paymentio_display_mode', 'separate' );
        $this->enabled     = ( 'unified' === $mode ) ? $this->get_option( 'enabled', 'yes' ) : 'no';
        $this->title       = $this->get_option( 'title', 'پرداخت اینترنتی امن' );
        $this->description = $this->get_option( 'description', 'پرداخت با کلیه کارت های عضو شبکه شتاب به وسیله درگاه امن بانکی' );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Save admin options and sync paymentio_display_mode
     */
    public function process_admin_options() {
        $result  = parent::process_admin_options();
        $enabled = $this->get_option( 'enabled', 'no' );
        update_option( 'paymentio_display_mode', ( 'yes' === $enabled ) ? 'unified' : 'separate' );
        return $result;
    }

    /**
     * Define form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال سازی درگاه یکپارچه',
                'type'    => 'checkbox',
                'label'   => 'فعال سازی نمایش تک درگاه هوشمند به جای چند درگاه مجزا در تسویه حساب',
                'default' => 'no',
            ),
            'title' => array(
                'title'       => 'عنوان درگاه در تسویه حساب',
                'type'        => 'text',
                'description' => 'عنوانی که در لیست روش های پرداخت تسویه حساب نمایش داده می شود.',
                'default'     => 'پرداخت اینترنتی امن (شتاب)',
            ),
            'description' => array(
                'title'       => 'توضیحات درگاه',
                'type'        => 'textarea',
                'description' => 'توضیحات زیر عنوان درگاه در تسویه حساب',
                'default'     => 'اتصال به درگاه امن پرداخت با کلیه کارت های عضو شبکه بانکی شتاب',
            ),
            'default_gateway' => array(
                'title'       => 'درگاه پیش فرض اولیه',
                'type'        => 'select',
                'description' => 'درگاهی که به صورت پیش فرض انتخاب شده است',
                'default'     => 'zarinpal',
                'options'     => array(
                    'zarinpal' => 'زرین پال',
                    'zibal'    => 'زیبال',
                    'payping'  => 'پی پینگ',
                ),
            ),
            'allow_user_choice' => array(
                'title'   => 'امکان انتخاب درگاه توسط خریدار',
                'type'    => 'checkbox',
                'label'   => 'نمایش دکمه های گرافیکی درگاه ها برای انتخاب توسط مشتری در فرم تسویه حساب',
                'default' => 'yes',
            ),
        );
    }

    /**
     * Render checkout fields / gateway selector
     */
    public function payment_fields() {
        if ( ! empty( $this->description ) ) {
            echo '<p class="paymentio-unified-desc">' . esc_html( $this->description ) . '</p>';
        }

        $allow_choice    = 'yes' === $this->get_option( 'allow_user_choice', 'yes' );
        $default_gateway = $this->get_option( 'default_gateway', 'zarinpal' );
        $active_gateways = Paymentio::instance()->get_active_sub_gateways();

        if ( empty( $active_gateways ) ) {
            echo '<p style="color:#ef4444;">هیچ درگاهی در پیمنتیو فعال نشده است. لطفاً با مدیر سایت تماس بگیرید.</p>';
            return;
        }

        if ( ! $allow_choice ) {
            $primary = reset( $active_gateways );
            echo '<input type="hidden" name="paymentio_selected_subgateway" value="' . esc_attr( $primary->gateway_slug ) . '" />';
            return;
        }

        echo '<div class="paymentio-gateway-selector">';
        echo '<div class="paymentio-selector-title">درگاه پرداخت مورد نظر خود را انتخاب کنید:</div>';
        echo '<div class="paymentio-badges-wrapper">';

        $is_first = true;
        foreach ( $active_gateways as $slug => $gateway_obj ) {
            $is_checked = ( $slug === $default_gateway ) || ( $is_first && ! isset( $active_gateways[ $default_gateway ] ) );
            $checked_attr = $is_checked ? 'checked="checked"' : '';
            $active_class = $is_checked ? ' active' : '';

            echo '<label class="paymentio-badge-card' . esc_attr( $active_class ) . '">';
            echo '<input type="radio" name="paymentio_selected_subgateway" value="' . esc_attr( $slug ) . '" ' . $checked_attr . ' />';
            if ( ! empty( $gateway_obj->icon ) ) {
                echo '<img src="' . esc_url( $gateway_obj->icon ) . '" alt="' . esc_attr( $gateway_obj->title ) . '" />';
            }
            echo '<span class="paymentio-badge-name">' . esc_html( $gateway_obj->method_title ) . '</span>';
            echo '</label>';

            $is_first = false;
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Process payment by delegating to chosen sub-gateway
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        $selected_slug   = isset( $_POST['paymentio_selected_subgateway'] ) ? sanitize_text_field( wp_unslash( $_POST['paymentio_selected_subgateway'] ) ) : '';
        $active_gateways = Paymentio::instance()->get_active_sub_gateways();

        if ( empty( $selected_slug ) || ! isset( $active_gateways[ $selected_slug ] ) ) {
            // Fallback to default
            $default_slug = $this->get_option( 'default_gateway', 'zarinpal' );
            if ( isset( $active_gateways[ $default_slug ] ) ) {
                $selected_slug = $default_slug;
            } else {
                $first = reset( $active_gateways );
                $selected_slug = $first ? $first->gateway_slug : '';
            }
        }

        if ( empty( $selected_slug ) || ! isset( $active_gateways[ $selected_slug ] ) ) {
            wc_add_notice( 'درگاه پرداختی برای تکمیل سفارش یافت نشد.', 'error' );
            return array( 'result' => 'fail' );
        }

        $chosen_gateway = $active_gateways[ $selected_slug ];

        // Delegate to chosen sub-gateway
        $result = $chosen_gateway->send_to_provider( wc_get_order( $order_id ) );

        if ( is_wp_error( $result ) ) {
            $error_message = $result->get_error_message();

            // Smart failover: try next active gateway if available!
            foreach ( $active_gateways as $slug => $sub_gw ) {
                if ( $slug === $selected_slug ) {
                    continue;
                }
                $failover_res = $sub_gw->send_to_provider( wc_get_order( $order_id ) );
                if ( ! is_wp_error( $failover_res ) ) {
                    return array(
                        'result'   => 'success',
                        'redirect' => $failover_res['redirect_url'],
                    );
                }
            }

            wc_add_notice( $error_message, 'error' );
            return array( 'result' => 'fail' );
        }

        return array(
            'result'   => 'success',
            'redirect' => $result['redirect_url'],
        );
    }
}
