<?php
/**
 * Paymentio Database and Transactions Manager
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paymentio_DB {

    /**
     * Get table name with WordPress prefix
     *
     * @return string
     */
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'paymentio_transactions';
    }

    /**
     * Get receipts table name with WordPress prefix
     *
     * @return string
     */
    public static function receipts_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'paymentio_receipts';
    }

    /**
     * Create or update database tables
     */
    public static function create_tables() {
        global $wpdb;

        $table_name      = self::table_name();
        $receipts_table  = self::receipts_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql_tx = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL DEFAULT 0,
            gateway varchar(50) NOT NULL DEFAULT '',
            gateway_title varchar(100) NOT NULL DEFAULT '',
            transaction_id varchar(191) NOT NULL DEFAULT '',
            reference_id varchar(191) NOT NULL DEFAULT '',
            amount decimal(15,2) NOT NULL DEFAULT 0.00,
            currency varchar(10) NOT NULL DEFAULT 'IRT',
            status varchar(30) NOT NULL DEFAULT 'pending',
            card_number varchar(50) NOT NULL DEFAULT '',
            customer_name varchar(191) NOT NULL DEFAULT '',
            customer_mobile varchar(50) NOT NULL DEFAULT '',
            customer_email varchar(191) NOT NULL DEFAULT '',
            customer_ip varchar(50) NOT NULL DEFAULT '',
            message text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY transaction_id (transaction_id),
            KEY reference_id (reference_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_receipts = "CREATE TABLE IF NOT EXISTS $receipts_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL DEFAULT 0,
            amount decimal(15,2) NOT NULL DEFAULT 0.00,
            currency varchar(10) NOT NULL DEFAULT 'IRT',
            card_number varchar(50) NOT NULL DEFAULT '',
            card_holder varchar(191) NOT NULL DEFAULT '',
            bank_name varchar(100) NOT NULL DEFAULT '',
            payer_card varchar(50) NOT NULL DEFAULT '',
            tracking_code varchar(191) NOT NULL DEFAULT '',
            receipt_url text NOT NULL,
            receipt_path text NOT NULL,
            receipt_filename varchar(255) NOT NULL DEFAULT '',
            receipt_filesize int(11) NOT NULL DEFAULT 0,
            customer_name varchar(191) NOT NULL DEFAULT '',
            customer_mobile varchar(50) NOT NULL DEFAULT '',
            customer_email varchar(191) NOT NULL DEFAULT '',
            customer_note text NULL,
            status varchar(30) NOT NULL DEFAULT 'pending',
            rejection_reason text NULL,
            reviewed_by bigint(20) unsigned NOT NULL DEFAULT 0,
            reviewed_at datetime NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY status (status),
            KEY tracking_code (tracking_code),
            KEY created_at (created_at)
        ) $charset_collate;";

        $wpdb->query( $sql_tx );
        $wpdb->query( $sql_receipts );
    }

    /**
     * Insert a new transaction record
     *
     * @param array $data
     * @return int|false
     */
    public static function insert( $data ) {
        global $wpdb;

        $defaults = array(
            'order_id'        => 0,
            'gateway'         => '',
            'gateway_title'   => '',
            'transaction_id'  => '',
            'reference_id'    => '',
            'amount'          => 0,
            'currency'        => 'IRT',
            'status'          => 'pending',
            'card_number'     => '',
            'customer_name'   => '',
            'customer_mobile' => '',
            'customer_email'  => '',
            'customer_ip'     => '',
            'message'         => '',
            'created_at'      => current_time( 'mysql' ),
            'updated_at'      => current_time( 'mysql' ),
        );

        $record = wp_parse_args( $data, $defaults );

        $result = $wpdb->insert( self::table_name(), $record );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update an existing transaction record by ID or transaction_id
     *
     * @param int|string $identifier ID (int) or transaction_id (string)
     * @param array $data
     * @return bool
     */
    public static function update( $identifier, $data ) {
        global $wpdb;

        $data['updated_at'] = current_time( 'mysql' );

        if ( is_numeric( $identifier ) ) {
            $where = array( 'id' => absint( $identifier ) );
        } else {
            $where = array( 'transaction_id' => sanitize_text_field( $identifier ) );
        }

        $result = $wpdb->update( self::table_name(), $data, $where );

        return false !== $result;
    }

    /**
     * Get transaction by transaction_id
     *
     * @param string $transaction_id
     * @return object|null
     */
    public static function get_by_transaction_id( $transaction_id ) {
        global $wpdb;
        $table = self::table_name();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE transaction_id = %s ORDER BY id DESC LIMIT 1", $transaction_id ) );
    }

    /**
     * Get transaction by order_id
     *
     * @param int $order_id
     * @return object|null
     */
    public static function get_by_order_id( $order_id ) {
        global $wpdb;
        $table = self::table_name();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %d ORDER BY id DESC LIMIT 1", $order_id ) );
    }

    /**
     * Get transactions list with filters and pagination
     *
     * @param array $args
     * @return array
     */
    public static function get_transactions( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'limit'   => 20,
            'offset'  => 0,
            'orderby' => 'id',
            'order'   => 'DESC',
            'status'  => '',
            'gateway' => '',
            'search'  => '',
        );

        $params = wp_parse_args( $args, $defaults );
        $table  = self::table_name();
        $where  = array( '1=1' );
        $values = array();

        if ( ! empty( $params['status'] ) ) {
            $where[]  = 'status = %s';
            $values[] = sanitize_text_field( $params['status'] );
        }

        if ( ! empty( $params['gateway'] ) ) {
            $where[]  = 'gateway = %s';
            $values[] = sanitize_text_field( $params['gateway'] );
        }

        if ( ! empty( $params['search'] ) ) {
            $search_like = '%' . $wpdb->esc_like( sanitize_text_field( $params['search'] ) ) . '%';
            $where[]     = '(order_id LIKE %s OR transaction_id LIKE %s OR reference_id LIKE %s OR customer_name LIKE %s OR customer_mobile LIKE %s)';
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
        }

        $where_clause = implode( ' AND ', $where );
        $orderby      = in_array( strtolower( $params['orderby'] ), array( 'id', 'amount', 'created_at', 'status' ), true ) ? $params['orderby'] : 'id';
        $order        = strtoupper( $params['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $limit        = absint( $params['limit'] );
        $offset       = absint( $params['offset'] );

        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT $limit OFFSET $offset";

        if ( ! empty( $values ) ) {
            $query = $wpdb->prepare( $query, $values );
        }

        $items = $wpdb->get_results( $query );

        // Count total
        $count_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        if ( ! empty( $values ) ) {
            $count_query = $wpdb->prepare( $count_query, $values );
        }
        $total = (int) $wpdb->get_var( $count_query );

        return array(
            'items' => $items,
            'total' => $total,
        );
    }

    /**
     * Get dashboard summary statistics
     *
     * @return array
     */
    public static function get_stats() {
        global $wpdb;
        $table = self::table_name();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return array(
                'total_count'    => 0,
                'success_count'  => 0,
                'failed_count'   => 0,
                'total_amount'   => 0,
            );
        }

        $success_data = $wpdb->get_row( "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount FROM $table WHERE status = 'completed'" );
        $failed_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status IN ('failed', 'cancelled')" );
        $total_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

        return array(
            'total_count'   => $total_count,
            'success_count' => (int) ( $success_data->count ?? 0 ),
            'failed_count'  => $failed_count,
            'total_amount'  => (float) ( $success_data->total_amount ?? 0 ),
        );
    }

    /**
     * Insert a new card-to-card receipt record
     *
     * @param array $data
     * @return int|false
     */
    public static function insert_receipt( $data ) {
        global $wpdb;

        $defaults = array(
            'order_id'         => 0,
            'amount'           => 0,
            'currency'         => 'IRT',
            'card_number'      => '',
            'card_holder'      => '',
            'bank_name'        => '',
            'payer_card'       => '',
            'tracking_code'    => '',
            'receipt_url'      => '',
            'receipt_path'     => '',
            'receipt_filename' => '',
            'receipt_filesize' => 0,
            'customer_name'    => '',
            'customer_mobile'  => '',
            'customer_email'   => '',
            'customer_note'    => '',
            'status'           => 'pending',
            'rejection_reason' => '',
            'reviewed_by'      => 0,
            'reviewed_at'      => null,
            'created_at'       => current_time( 'mysql' ),
            'updated_at'       => current_time( 'mysql' ),
        );

        $record = wp_parse_args( $data, $defaults );
        $result = $wpdb->insert( self::receipts_table_name(), $record );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update an existing receipt record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update_receipt( $id, $data ) {
        global $wpdb;

        $data['updated_at'] = current_time( 'mysql' );
        $where              = array( 'id' => absint( $id ) );
        $result             = $wpdb->update( self::receipts_table_name(), $data, $where );

        return false !== $result;
    }

    /**
     * Get receipt record by ID
     *
     * @param int $id
     * @return object|null
     */
    public static function get_receipt( $id ) {
        global $wpdb;
        $table = self::receipts_table_name();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d LIMIT 1", absint( $id ) ) );
    }

    /**
     * Get receipt record by Order ID
     *
     * @param int $order_id
     * @return object|null
     */
    public static function get_receipt_by_order( $order_id ) {
        global $wpdb;
        $table = self::receipts_table_name();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %d ORDER BY id DESC LIMIT 1", absint( $order_id ) ) );
    }

    /**
     * Get count of pending receipts
     *
     * @return int
     */
    public static function get_pending_receipts_count() {
        global $wpdb;
        $table = self::receipts_table_name();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return 0;
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'pending'" );
    }

    /**
     * Get receipts summary statistics
     *
     * @return array
     */
    public static function get_receipts_stats() {
        global $wpdb;
        $table = self::receipts_table_name();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return array(
                'total_count'    => 0,
                'pending_count'  => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'total_amount'   => 0,
            );
        }

        $pending_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'pending'" );
        $approved_data  = $wpdb->get_row( "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount FROM $table WHERE status = 'approved'" );
        $rejected_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'rejected'" );
        $total_count    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

        return array(
            'total_count'    => $total_count,
            'pending_count'  => $pending_count,
            'approved_count' => (int) ( $approved_data->count ?? 0 ),
            'rejected_count' => $rejected_count,
            'total_amount'   => (float) ( $approved_data->total_amount ?? 0 ),
        );
    }

    /**
     * Get receipts list with filters and pagination
     *
     * @param array $args
     * @return array
     */
    public static function get_receipts( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'limit'   => 20,
            'offset'  => 0,
            'orderby' => 'id',
            'order'   => 'DESC',
            'status'  => '',
            'search'  => '',
        );

        $params = wp_parse_args( $args, $defaults );
        $table  = self::receipts_table_name();
        $where  = array( '1=1' );
        $values = array();

        if ( ! empty( $params['status'] ) ) {
            $where[]  = 'status = %s';
            $values[] = sanitize_text_field( $params['status'] );
        }

        if ( ! empty( $params['search'] ) ) {
            $search_like = '%' . $wpdb->esc_like( sanitize_text_field( $params['search'] ) ) . '%';
            $where[]     = '(order_id LIKE %s OR tracking_code LIKE %s OR payer_card LIKE %s OR card_number LIKE %s OR customer_name LIKE %s OR customer_mobile LIKE %s)';
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
            $values[]    = $search_like;
        }

        $where_clause = implode( ' AND ', $where );
        $orderby      = in_array( strtolower( $params['orderby'] ), array( 'id', 'amount', 'created_at', 'status' ), true ) ? $params['orderby'] : 'id';
        $order        = strtoupper( $params['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $limit        = absint( $params['limit'] );
        $offset       = absint( $params['offset'] );

        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT $limit OFFSET $offset";

        if ( ! empty( $values ) ) {
            $query = $wpdb->prepare( $query, $values );
        }

        $items = $wpdb->get_results( $query );

        $count_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        if ( ! empty( $values ) ) {
            $count_query = $wpdb->prepare( $count_query, $values );
        }
        $total = (int) $wpdb->get_var( $count_query );

        return array(
            'items' => $items,
            'total' => $total,
        );
    }

    /**
     * Approve a card-to-card receipt and complete order
     *
     * @param int $receipt_id
     * @param int $admin_user_id
     * @return true|WP_Error
     */
    public static function approve_receipt( $receipt_id, $admin_user_id = 0 ) {
        $receipt = self::get_receipt( $receipt_id );
        if ( ! $receipt ) {
            return new WP_Error( 'not_found', 'رسید مورد نظر یافت نشد.' );
        }

        $order = wc_get_order( $receipt->order_id );
        if ( ! $order ) {
            return new WP_Error( 'order_not_found', 'سفارش مربوط به این رسید یافت نشد.' );
        }

        $admin_user = $admin_user_id ? get_userdata( $admin_user_id ) : wp_get_current_user();
        $admin_name = $admin_user && ! empty( $admin_user->display_name ) ? $admin_user->display_name : 'مدیر سایت';

        // 1. Update receipt record
        self::update_receipt( $receipt_id, array(
            'status'           => 'approved',
            'rejection_reason' => '',
            'reviewed_by'      => $admin_user_id ?: get_current_user_id(),
            'reviewed_at'      => current_time( 'mysql' ),
        ) );

        // 2. Mark order payment complete in WooCommerce
        $tracking_code = ! empty( $receipt->tracking_code ) ? $receipt->tracking_code : ( 'CARD-' . $receipt->order_id );

        $note = sprintf(
            'رسید پرداخت کارت به کارت توسط %s تایید شد. مبلغ: %s | کد پیگیری: %s | کارت مبدا: %s',
            $admin_name,
            Paymentio_Helper::format_price( $receipt->amount, $receipt->currency ),
            $receipt->tracking_code ?: 'ثبت نشده',
            $receipt->payer_card ?: 'ثبت نشده'
        );
        $order->add_order_note( $note );

        // Get gateway success status setting
        $card_settings  = get_option( 'woocommerce_paymentio_card_settings', array() );
        $success_status = $card_settings['success_status'] ?? 'processing';

        // Trigger payment_complete to deliver downloads and reduce stock
        $order->payment_complete( $tracking_code );

        if ( ! empty( $success_status ) && 'processing' !== $success_status ) {
            $order->update_status( $success_status, 'تغییر وضعیت توسط تایید پرداخت پیمنتیو' );
        }

        // Save order metadata
        $order->update_meta_data( '_paymentio_receipt_status', 'approved' );
        $order->update_meta_data( '_paymentio_receipt_approved_by', $admin_name );
        $order->update_meta_data( '_paymentio_receipt_approved_at', current_time( 'mysql' ) );
        $order->save();

        // 3. Update transaction record in paymentio_transactions
        $tx = self::get_by_order_id( $receipt->order_id );
        if ( $tx ) {
            self::update( $tx->id, array(
                'status'       => 'completed',
                'reference_id' => $tracking_code,
                'card_number'  => $receipt->payer_card ?: $receipt->card_number,
                'message'      => 'رسید کارت به کارت با موفقیت تایید شد.',
            ) );
        }

        return true;
    }

    /**
     * Reject a card-to-card receipt
     *
     * @param int $receipt_id
     * @param string $reason
     * @param int $admin_user_id
     * @return true|WP_Error
     */
    public static function reject_receipt( $receipt_id, $reason = '', $admin_user_id = 0 ) {
        $receipt = self::get_receipt( $receipt_id );
        if ( ! $receipt ) {
            return new WP_Error( 'not_found', 'رسید مورد نظر یافت نشد.' );
        }

        $order = wc_get_order( $receipt->order_id );
        if ( ! $order ) {
            return new WP_Error( 'order_not_found', 'سفارش مربوط به این رسید یافت نشد.' );
        }

        $admin_user  = $admin_user_id ? get_userdata( $admin_user_id ) : wp_get_current_user();
        $admin_name  = $admin_user && ! empty( $admin_user->display_name ) ? $admin_user->display_name : 'مدیر سایت';
        $reason_text = ! empty( $reason ) ? sanitize_text_field( $reason ) : 'عدم تطابق اطلاعات یا ناخوانا بودن رسید';

        // 1. Update receipt record
        self::update_receipt( $receipt_id, array(
            'status'           => 'rejected',
            'rejection_reason' => $reason_text,
            'reviewed_by'      => $admin_user_id ?: get_current_user_id(),
            'reviewed_at'      => current_time( 'mysql' ),
        ) );

        // 2. Add order note and update status
        $note = sprintf(
            'رسید پرداخت کارت به کارت توسط %s رد شد. دلیل: %s',
            $admin_name,
            $reason_text
        );
        $order->add_order_note( $note );
        $order->update_status( 'on-hold', 'رسید پرداخت کارت به کارت تایید نشد: ' . $reason_text );

        // Save order metadata
        $order->update_meta_data( '_paymentio_receipt_status', 'rejected' );
        $order->update_meta_data( '_paymentio_rejection_reason', $reason_text );
        $order->update_meta_data( '_paymentio_receipt_rejected_by', $admin_name );
        $order->update_meta_data( '_paymentio_receipt_rejected_at', current_time( 'mysql' ) );
        $order->save();

        // 3. Update transaction record
        $tx = self::get_by_order_id( $receipt->order_id );
        if ( $tx ) {
            self::update( $tx->id, array(
                'status'  => 'failed',
                'message' => 'رسید کارت به کارت رد شد: ' . $reason_text,
            ) );
        }

        return true;
    }
}
