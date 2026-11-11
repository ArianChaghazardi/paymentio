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
}
