<?php
/**
 * Paymentio Transactions View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page     = 20;
$offset       = ( $current_page - 1 ) * $per_page;
$status       = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$gateway      = isset( $_GET['gateway'] ) ? sanitize_text_field( $_GET['gateway'] ) : '';
$search       = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

$tx_data = Paymentio_DB::get_transactions( array(
    'limit'   => $per_page,
    'offset'  => $offset,
    'status'  => $status,
    'gateway' => $gateway,
    'search'  => $search,
) );

$transactions = $tx_data['items'];
$total_items  = $tx_data['total'];
$total_pages  = ceil( $total_items / $per_page );
?>

<div class="paymentio-transactions-container">
    <div class="paymentio-section-desc">
        <h3>گزارش و تاریخچه تراکنش ها</h3>
        <p>مشاهده و رهگیری کلیه پرداخت های انجام شده از طریق درگاه های پیمنتیو با جزییات کامل فنی، شماره پیگیری و اطلاعات خریدار.</p>
    </div>

    <!-- Filters Bar -->
    <div class="paymentio-card paymentio-filter-bar">
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="paymentio-filter-form">
            <input type="hidden" name="page" value="paymentio-transactions" />

            <div class="paymentio-filter-inputs">
                <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="جستجو بر اساس سفارش، کد پیگیری، نام یا شماره تماس..." class="paymentio-input paymentio-search-input" />

                <select name="status" class="paymentio-input">
                    <option value="">همه وضعیت ها</option>
                    <option value="completed" <?php selected( $status, 'completed' ); ?>>تراکنش های موفق</option>
                    <option value="failed" <?php selected( $status, 'failed' ); ?>>تراکنش های ناموفق</option>
                    <option value="pending" <?php selected( $status, 'pending' ); ?>>در انتظار پرداخت</option>
                </select>

                <select name="gateway" class="paymentio-input">
                    <option value="">همه درگاه ها</option>
                    <option value="zarinpal" <?php selected( $gateway, 'zarinpal' ); ?>>زرین پال</option>
                    <option value="zibal" <?php selected( $gateway, 'zibal' ); ?>>زیبال</option>
                    <option value="payping" <?php selected( $gateway, 'payping' ); ?>>پی پینگ</option>
                </select>

                <button type="submit" class="paymentio-btn paymentio-btn-primary">اعمال فیلتر</button>
                <?php if ( $status || $gateway || $search ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=paymentio-transactions' ) ); ?>" class="paymentio-btn paymentio-btn-outline">پاکسازی فیلتر</a>
                <?php endif; ?>
            </div>

            <div class="paymentio-filter-count">
                مجموع: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $total_items ) ); ?></strong> تراکنش
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="paymentio-card" style="padding: 0; margin-top: 16px;">
        <div class="paymentio-card-body" style="padding: 0;">
            <?php if ( empty( $transactions ) ) : ?>
                <div style="padding: 40px; text-align: center; color: #94a3b8;">
                    <span class="dashicons dashicons-search" style="font-size: 36px; width: 36px; height: 36px;"></span>
                    <p>هیچ تراکنشی با مشخصات وارد شده یافت نشد.</p>
                </div>
            <?php else : ?>
                <div class="paymentio-table-responsive">
                    <table class="paymentio-table">
                    <thead>
                        <tr>
                            <th>شناسه</th>
                            <th>سفارش</th>
                            <th>درگاه</th>
                            <th>مبلغ</th>
                            <th>کد پیگیری (RefID)</th>
                            <th>شناسه درگاه (Authority / TrackId)</th>
                            <th>شماره کارت</th>
                            <th>خریدار</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $transactions as $tx ) : ?>
                            <tr>
                                <td><?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->id ) ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $tx->order_id . '&action=edit' ) ); ?>" class="paymentio-link">
                                        #<?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->order_id ) ); ?>
                                    </a>
                                </td>
                                <td><strong><?php echo esc_html( $tx->gateway_title ); ?></strong></td>
                                <td><strong><?php echo esc_html( Paymentio_Helper::format_price( $tx->amount, $tx->currency ) ); ?></strong></td>
                                <td>
                                    <?php if ( $tx->reference_id ) : ?>
                                        <code class="paymentio-code" title="کلیک برای کپی"><?php echo esc_html( $tx->reference_id ); ?></code>
                                    <?php else : ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $tx->transaction_id ) : ?>
                                        <span style="font-family: monospace; font-size: 11px; color: #64748b;"><?php echo esc_html( $tx->transaction_id ); ?></span>
                                    <?php else : ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $tx->card_number ) : ?>
                                        <span class="paymentio-card-pan"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->card_number ) ); ?></span>
                                    <?php else : ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo esc_html( $tx->customer_name ?: 'مشتری مهمان' ); ?></div>
                                    <?php if ( $tx->customer_mobile ) : ?>
                                        <small style="color: #64748b;"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->customer_mobile ) ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( 'completed' === $tx->status ) : ?>
                                        <span class="paymentio-badge paymentio-badge-success">موفق</span>
                                    <?php elseif ( 'pending' === $tx->status ) : ?>
                                        <span class="paymentio-badge paymentio-badge-warning">در انتظار پرداخت</span>
                                    <?php else : ?>
                                        <span class="paymentio-badge paymentio-badge-danger" title="<?php echo esc_attr( $tx->message ); ?>">ناموفق</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo esc_html( Paymentio_Helper::to_persian_digits( $tx->created_at ) ); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <!-- Pagination -->
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="paymentio-pagination">
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => '&laquo; قبلی',
                            'next_text' => 'بعدی &raquo;',
                            'total'     => $total_pages,
                            'current'   => $current_page,
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
