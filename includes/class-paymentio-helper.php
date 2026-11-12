<?php
/**
 * Paymentio Helper Utilities
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Helper {

    /**
     * Get 100% local inline SVG icon (zero external CDNs or libraries)
     *
     * @param string $name
     * @param int $size
     * @param string $class
     * @return string
     */
    public static function icon( $name, $size = 20, $class = '' ) {
        $s = (int) $size;
        $c = esc_attr( $class );

        switch ( $name ) {
            case 'check':
            case 'yes':
            case 'success':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';

            case 'money':
            case 'sale':
            case 'wallet':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M7 15h0M2 9.5h20"></path></svg>';

            case 'dismiss':
            case 'failed':
            case 'alert':
            case 'danger':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

            case 'shield':
            case 'gateways-active':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>';

            case 'dashboard':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>';

            case 'cart':
            case 'gateways':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>';

            case 'transactions':
            case 'list':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>';

            case 'settings':
            case 'gear':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';

            case 'health':
            case 'activity':
            case 'heart':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>';

            case 'refresh':
            case 'update':
            case 'ping':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>';

            case 'search':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';

            case 'database':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>';

            case 'randomize':
            case 'route':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><polyline points="16 3 21 3 21 8"></polyline><line x1="4" y1="20" x2="21" y2="3"></line><polyline points="21 16 21 21 16 21"></polyline><line x1="15" y1="15" x2="21" y2="21"></line><line x1="4" y1="4" x2="9" y2="9"></line></svg>';

            case 'sliders':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>';

            case 'check-simple':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><polyline points="20 6 9 17 4 12"></polyline></svg>';

            case 'info':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';

            case 'layers':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>';

            case 'card':
            case 'credit-card':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><rect x="1" y="4" width="22" height="16" rx="3" ry="3"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';

            case 'receipt':
            case 'invoice':
            case 'file-text':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';

            case 'copy':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';

            case 'upload':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>';

            case 'bank':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 6 12 3 19 6"></polyline><line x1="4" y1="10" x2="4" y2="21"></line><line x1="20" y1="10" x2="20" y2="21"></line><line x1="8" y1="14" x2="8" y2="17"></line><line x1="12" y1="14" x2="12" y2="17"></line><line x1="16" y1="14" x2="16" y2="17"></line></svg>';

            case 'eye':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';

            case 'external':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';

            case 'clock':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';

            case 'camera':
                return '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $c . '"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>';

            default:
                return '';
        }
    }

    /**
     * Convert amount to Toman based on order currency
     *
     * @param float|int $amount
     * @param string $currency
     * @return int
     */
    public static function to_toman( $amount, $currency = 'IRT' ) {
        $amount   = (float) $amount;
        $currency = strtoupper( trim( $currency ) );

        switch ( $currency ) {
            case 'IRR':
                return (int) round( $amount / 10 );
            case 'IRHR':
                return (int) round( $amount * 100 );
            case 'IRHT':
                return (int) round( $amount * 1000 );
            case 'IRT':
            case 'TOMAN':
            default:
                return (int) round( $amount );
        }
    }

    /**
     * Convert amount to Rial based on order currency
     *
     * @param float|int $amount
     * @param string $currency
     * @return int
     */
    public static function to_rial( $amount, $currency = 'IRT' ) {
        $amount   = (float) $amount;
        $currency = strtoupper( trim( $currency ) );

        switch ( $currency ) {
            case 'IRR':
                return (int) round( $amount );
            case 'IRHR':
                return (int) round( $amount * 1000 );
            case 'IRHT':
                return (int) round( $amount * 10000 );
            case 'IRT':
            case 'TOMAN':
            default:
                return (int) round( $amount * 10 );
        }
    }

    /**
     * Convert English digits to Persian digits without any half-space
     *
     * @param string|int $number
     * @return string
     */
    public static function to_persian_digits( $number ) {
        $persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
        $english = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
        return str_replace( $english, $persian, (string) $number );
    }

    /**
     * Convert Persian/Arabic digits to English digits
     *
     * @param string $string
     * @return string
     */
    public static function to_english_digits( $string ) {
        $persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
        $english = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
        return str_replace( $persian, $english, (string) $string );
    }

    /**
     * Format price with currency label using full spaces
     *
     * @param float|int $amount
     * @param string $currency
     * @return string
     */
    public static function format_price( $amount, $currency = 'IRT' ) {
        $formatted = number_format( (float) $amount );
        $persian   = self::to_persian_digits( $formatted );
        $unit      = ( strtoupper( $currency ) === 'IRR' ) ? 'ریال' : 'تومان';
        return $persian . ' ' . $unit;
    }

    /**
     * Get client IP address safely
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        );

        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
                $ip  = trim( $ips[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Get server public outbound IP address
     *
     * @return string
     */
    public static function get_server_ip() {
        $cached = get_transient( 'paymentio_server_ip' );
        if ( ! empty( $cached ) && filter_var( $cached, FILTER_VALIDATE_IP ) ) {
            return $cached;
        }

        $server_ip = $_SERVER['SERVER_ADDR'] ?? '';
        if ( ! empty( $server_ip ) && filter_var( $server_ip, FILTER_VALIDATE_IP ) && '127.0.0.1' !== $server_ip ) {
            set_transient( 'paymentio_server_ip', $server_ip, DAY_IN_SECONDS );
            return $server_ip;
        }

        $response = wp_safe_remote_get( 'https://api.ipify.org', array( 'timeout' => 4 ) );
        if ( ! is_wp_error( $response ) ) {
            $ip = trim( wp_remote_retrieve_body( $response ) );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                set_transient( 'paymentio_server_ip', $ip, DAY_IN_SECONDS );
                return $ip;
            }
        }

        return $server_ip ?: '127.0.0.1';
    }

    /**
     * Perform HTTP POST request with timeout and fallback
     *
     * @param string $url
     * @param array $payload
     * @param array $headers
     * @param int $timeout
     * @return array|WP_Error
     */
    public static function remote_post( $url, $payload = array(), $headers = array(), $timeout = 15 ) {
        $default_headers = array(
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'User-Agent'   => 'Paymentio/1.0.1; WordPress/' . get_bloginfo( 'version' ),
        );

        $merged_headers = wp_parse_args( $headers, $default_headers );

        $response = wp_safe_remote_post( $url, array(
            'body'        => wp_json_encode( $payload ),
            'headers'     => $merged_headers,
            'timeout'     => $timeout,
            'sslverify'   => false,
            'httpversion' => '1.1',
        ) );

        if ( is_wp_error( $response ) ) {
            // Fallback to cURL if wp_remote fails
            if ( function_exists( 'curl_init' ) ) {
                return self::curl_fallback( $url, $payload, $merged_headers, $timeout );
            }
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return array(
            'code' => $code,
            'body' => $body,
            'data' => $data,
        );
    }

    /**
     * Direct cURL fallback when standard WP HTTP encounters transport failure
     *
     * @param string $url
     * @param array $payload
     * @param array $headers
     * @param int $timeout
     * @return array|WP_Error
     */
    private static function curl_fallback( $url, $payload, $headers, $timeout ) {
        $ch = curl_init( $url );

        $curl_headers = array();
        foreach ( $headers as $key => $val ) {
            $curl_headers[] = "$key: $val";
        }

        curl_setopt_array( $ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => wp_json_encode( $payload ),
            CURLOPT_HTTPHEADER     => $curl_headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ) );

        $body  = curl_exec( $ch );
        $code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $error = curl_error( $ch );
        curl_close( $ch );

        if ( $error || empty( $body ) ) {
            return new WP_Error( 'curl_error', $error ?: 'پاسخی از سرور درگاه دریافت نشد.' );
        }

        return array(
            'code' => $code,
            'body' => $body,
            'data' => json_decode( $body, true ),
        );
    }

    /**
     * Test latency / response time of an API URL in milliseconds
     *
     * @param string $url
     * @return array
     */
    public static function ping_gateway( $url ) {
        $start_time = microtime( true );
        $response   = wp_safe_remote_get( $url, array(
            'timeout'   => 5,
            'sslverify' => false,
        ) );
        $duration   = round( ( microtime( true ) - $start_time ) * 1000 );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'latency' => $duration,
                'message' => $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        return array(
            'success' => $code >= 200 && $code < 500,
            'code'    => $code,
            'latency' => $duration,
            'message' => 'اتصال با موفقیت برقرار شد.',
        );
    }

    /**
     * Get Iranian Banks list
     *
     * @return array
     */
    public static function get_iranian_banks() {
        return array(
            'melli'          => array( 'name' => 'بانک ملی ایران', 'bins' => array( '603799' ) ),
            'mellat'         => array( 'name' => 'بانک ملت', 'bins' => array( '610433', '991975' ) ),
            'saderat'        => array( 'name' => 'بانک صادرات ایران', 'bins' => array( '603769' ) ),
            'tejarat'        => array( 'name' => 'بانک تجارت', 'bins' => array( '585983', '627353' ) ),
            'sepah'          => array( 'name' => 'بانک سپه', 'bins' => array( '589210' ) ),
            'keshavarzi'     => array( 'name' => 'بانک کشاورزی', 'bins' => array( '603770', '639217' ) ),
            'maskan'         => array( 'name' => 'بانک مسکن', 'bins' => array( '628023' ) ),
            'saman'          => array( 'name' => 'بانک سامان', 'bins' => array( '621986' ) ),
            'pasargad'       => array( 'name' => 'بانک پاسارگاد', 'bins' => array( '502229', '639347' ) ),
            'parsian'        => array( 'name' => 'بانک پارسیان', 'bins' => array( '622106', '639194', '627884' ) ),
            'ayandeh'        => array( 'name' => 'بانک آینده', 'bins' => array( '636214' ) ),
            'shahr'          => array( 'name' => 'بانک شهر', 'bins' => array( '504706', '502806' ) ),
            'sina'           => array( 'name' => 'بانک سینا', 'bins' => array( '639346' ) ),
            'refah'          => array( 'name' => 'بانک رفاه کارگران', 'bins' => array( '589463' ) ),
            'mehr_iran'      => array( 'name' => 'بانک قرض الحسنه مهر ایران', 'bins' => array( '606373' ) ),
            'resalat'        => array( 'name' => 'بانک قرض الحسنه رسالت', 'bins' => array( '504172' ) ),
            'blubank'        => array( 'name' => 'بلوبانک (سامان)', 'bins' => array( '621986' ) ),
            'gardeshgari'    => array( 'name' => 'بانک گردشگری', 'bins' => array( '505416' ) ),
            'eghtesad_novin' => array( 'name' => 'بانک اقتصاد نوین', 'bins' => array( '627412' ) ),
            'karafarin'      => array( 'name' => 'بانک کارآفرین', 'bins' => array( '627488', '502910' ) ),
            'middle_east'    => array( 'name' => 'بانک خاورمیانه', 'bins' => array( '585947' ) ),
            'postbank'       => array( 'name' => 'پست بانک ایران', 'bins' => array( '627760' ) ),
            'day'            => array( 'name' => 'بانک دی', 'bins' => array( '502938' ) ),
            'iran_zamin'     => array( 'name' => 'بانک ایران زمین', 'bins' => array( '505785' ) ),
            'sarmayeh'       => array( 'name' => 'بانک سرمایه', 'bins' => array( '639607' ) ),
            'tosee_taavon'   => array( 'name' => 'بانک توسعه تعاون', 'bins' => array( '502908' ) ),
            'sanat_madan'    => array( 'name' => 'بانک صنعت و معدن', 'bins' => array( '627961' ) ),
            'other'          => array( 'name' => 'سایر بانک ها', 'bins' => array() ),
        );
    }

    /**
     * Clean card number (convert to English digits, remove non-digits)
     *
     * @param string $card_number
     * @return string
     */
    public static function clean_card_number( $card_number ) {
        $english = self::to_english_digits( $card_number );
        return preg_replace( '/\D/', '', $english );
    }

    /**
     * Format 16-digit card number in groups of 4 with Persian digits
     *
     * @param string $card_number
     * @param string $separator
     * @return string
     */
    public static function format_card_number( $card_number, $separator = ' - ' ) {
        $clean = self::clean_card_number( $card_number );
        if ( strlen( $clean ) !== 16 ) {
            return self::to_persian_digits( $card_number );
        }
        $parts = str_split( $clean, 4 );
        $formatted = implode( $separator, $parts );
        return self::to_persian_digits( $formatted );
    }

    /**
     * Mask middle digits of a 16-digit card number
     *
     * @param string $card_number
     * @return string
     */
    public static function mask_card_number( $card_number ) {
        $clean = self::clean_card_number( $card_number );
        if ( strlen( $clean ) < 16 ) {
            return self::to_persian_digits( $card_number );
        }
        $first = substr( $clean, 0, 4 );
        $last  = substr( $clean, 12, 4 );
        return self::to_persian_digits( $first ) . ' - **** - **** - ' . self::to_persian_digits( $last );
    }

    /**
     * Detect bank from card number BIN
     *
     * @param string $card_number
     * @return array Array with slug and name
     */
    public static function detect_bank( $card_number ) {
        $clean = self::clean_card_number( $card_number );
        $bin   = substr( $clean, 0, 6 );
        $banks = self::get_iranian_banks();

        foreach ( $banks as $slug => $bank ) {
            if ( in_array( $bin, $bank['bins'], true ) ) {
                return array(
                    'slug' => $slug,
                    'name' => $bank['name'],
                );
            }
        }

        return array(
            'slug' => 'other',
            'name' => 'بانک عضو شتاب',
        );
    }

    /**
     * Default card to card guidelines and rules text (100% zero ZWNJ)
     *
     * @return string
     */
    public static function get_default_card_rules() {
        return "۱. لطفاً مبلغ دقیق صورتحساب را به شماره کارت اعلام شده واریز نمایید.
۲. پس از انجام انتقال، تصویر واضح یا اسکرین شات از رسید تراکنش تهیه کنید.
۳. اطلاعات رسید شامل شماره پیگیری، تاریخ، ساعت و چهار رقم آخر کارت مبدا باید کاملاً خوانا باشد.
۴. تصویر رسید را در فرم بارگذاری کرده و دکمه تایید پرداخت را کلیک کنید.
۵. سفارش شما پس از بررسی و تایید توسط پشتیبانی، در سریع ترین زمان ممکن فعال و تکمیل خواهد شد.";
    }
}
