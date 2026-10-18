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
     * Create or update database tables
     */
    public static function create_tables() {
        global $wpdb;

        $table_name      = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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

        $wpdb->query( $sql );
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
}
