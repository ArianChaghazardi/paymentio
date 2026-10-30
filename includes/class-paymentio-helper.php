<?php
/**
 * Paymentio Helper Utilities
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_Helper {

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
            'User-Agent'   => 'Paymentio/1.0.0; WordPress/' . get_bloginfo( 'version' ),
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
