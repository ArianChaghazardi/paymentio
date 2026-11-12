<?php
/**
 * Paymentio Receipts Management View
 *
 * @package Paymentio
 * @author Arian Chaghazardi
 * @developer HectorTM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page     = 20;
$offset       = ( $current_page - 1 ) * $per_page;
$status       = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$search       = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

$stats = Paymentio_DB::get_receipts_stats();

$receipts_data = Paymentio_DB::get_receipts( array(
    'limit'   => $per_page,
    'offset'  => $offset,
    'status'  => $status,
    'search'  => $search,
    'orderby' => 'id',
    'order'   => 'DESC',
) );

$receipts    = $receipts_data['items'];
$total_items = $receipts_data['total'];
$total_pages = ceil( $total_items / $per_page );
?>

<div class="paymentio-receipts-container">
    <div class="paymentio-section-desc">
        <h3>مدیریت و تایید رسید های پرداخت کارت به کارت</h3>
        <p>مشاهده، بررسی و تایید یا رد فیش های واریزی ارسال شده توسط خریداران با قابلیت بزرگنمایی تصویر رسید، تطبیق کد پیگیری و تکمیل خودکار سفارش.</p>
    </div>

    <!-- Stats Cards -->
    <div class="paymentio-stats-grid" style="margin-bottom: 24px;">
        <div class="paymentio-stat-card">
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">رسید های در انتظار بررسی</div>
                <div class="paymentio-stat-value" style="color: #d97706;"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $stats['pending_count'] ) ); ?></div>
                <div class="paymentio-stat-hint">نیازمند اقدام و بررسی</div>
            </div>
            <div class="paymentio-stat-icon warning">
                <?php echo Paymentio_Helper::icon( 'clock', 22 ); ?>
            </div>
        </div>

        <div class="paymentio-stat-card">
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">رسید های تایید شده</div>
                <div class="paymentio-stat-value" style="color: #16a34a;"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $stats['approved_count'] ) ); ?></div>
                <div class="paymentio-stat-hint">پرداخت های قطعی و تحویل شده</div>
            </div>
            <div class="paymentio-stat-icon success">
                <?php echo Paymentio_Helper::icon( 'success', 22 ); ?>
            </div>
        </div>

        <div class="paymentio-stat-card">
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">رسید های رد شده</div>
                <div class="paymentio-stat-value" style="color: #dc2626;"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $stats['rejected_count'] ) ); ?></div>
                <div class="paymentio-stat-hint">عدم انطباق یا فیش نامعتبر</div>
            </div>
            <div class="paymentio-stat-icon danger">
                <?php echo Paymentio_Helper::icon( 'dismiss', 22 ); ?>
            </div>
        </div>

        <div class="paymentio-stat-card">
            <div class="paymentio-stat-data">
                <div class="paymentio-stat-label">مجموع مبالغ تایید شده</div>
                <div class="paymentio-stat-value"><?php echo esc_html( Paymentio_Helper::format_price( $stats['total_amount'], 'IRT' ) ); ?></div>
                <div class="paymentio-stat-hint">گردش مالی کارت به کارت</div>
            </div>
            <div class="paymentio-stat-icon primary">
                <?php echo Paymentio_Helper::icon( 'money', 22 ); ?>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="paymentio-card paymentio-filter-bar">
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="paymentio-filter-form">
            <input type="hidden" name="page" value="paymentio-receipts" />

            <div class="paymentio-filter-inputs">
                <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="جستجو بر اساس سفارش، کد پیگیری، شماره کارت، نام یا موبایل..." class="paymentio-input paymentio-search-input" />

                <select name="status" class="paymentio-input">
                    <option value="">همه وضعیت ها</option>
                    <option value="pending" <?php selected( $status, 'pending' ); ?>>در انتظار بررسی</option>
                    <option value="approved" <?php selected( $status, 'approved' ); ?>>تایید شده</option>
                    <option value="rejected" <?php selected( $status, 'rejected' ); ?>>رد شده</option>
                </select>

                <button type="submit" class="paymentio-btn paymentio-btn-primary">
                    <?php echo Paymentio_Helper::icon( 'search', 14 ); ?>
                    اعمال فیلتر
                </button>

                <?php if ( $status || $search ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=paymentio-receipts' ) ); ?>" class="paymentio-btn paymentio-btn-outline">پاکسازی فیلتر</a>
                <?php endif; ?>
            </div>

            <div class="paymentio-filter-count">
                مجموع: <strong><?php echo esc_html( Paymentio_Helper::to_persian_digits( $total_items ) ); ?></strong> رسید
            </div>
        </form>
    </div>

    <!-- Receipts Table -->
    <div class="paymentio-card" style="padding: 0; margin-top: 16px;">
        <div class="paymentio-card-body" style="padding: 0;">
            <?php if ( empty( $receipts ) ) : ?>
                <div style="padding: 48px; text-align: center; color: #94a3b8;">
                    <?php echo Paymentio_Helper::icon( 'receipt', 42 ); ?>
                    <p style="margin-top: 14px; font-size: 1rem; font-weight: 700; color: #64748b;">هیچ رسیدی با مشخصات وارد شده یافت نشد.</p>
                </div>
            <?php else : ?>
                <div class="paymentio-table-responsive">
                    <table class="paymentio-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">شناسه</th>
                                <th>سفارش</th>
                                <th>خریدار</th>
                                <th>مبلغ پرداختی</th>
                                <th>بانک و کارت مقصد</th>
                                <th>کد پیگیری</th>
                                <th>کارت مبدا</th>
                                <th style="text-align: center;">تصویر رسید</th>
                                <th>وضعیت</th>
                                <th>تاریخ ثبت</th>
                                <th style="text-align: center; width: 170px;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $receipts as $r ) :
                                $order_link = admin_url( 'post.php?post=' . $r->order_id . '&action=edit' );
                                if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
                                    $order_link = admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $r->order_id );
                                }
                            ?>
                                <tr id="receipt-row-<?php echo esc_attr( $r->id ); ?>">
                                    <td>
                                        <span class="paymentio-badge paymentio-badge-neutral">#<?php echo esc_html( Paymentio_Helper::to_persian_digits( $r->id ) ); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url( $order_link ); ?>" class="paymentio-order-link" target="_blank">
                                            <strong>#<?php echo esc_html( Paymentio_Helper::to_persian_digits( $r->order_id ) ); ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;"><?php echo esc_html( $r->customer_name ?: 'بدون نام' ); ?></div>
                                        <?php if ( ! empty( $r->customer_mobile ) ) : ?>
                                            <a href="tel:<?php echo esc_attr( $r->customer_mobile ); ?>" style="font-size: 11.5px; color: #64748b; direction: ltr; display: inline-block;">
                                                <?php echo esc_html( Paymentio_Helper::to_persian_digits( $r->customer_mobile ) ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong style="color: #0284c7; font-size: 13.5px;">
                                            <?php echo esc_html( Paymentio_Helper::format_price( $r->amount, $r->currency ) ); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><?php echo esc_html( $r->bank_name ?: 'بانک شتاب' ); ?></div>
                                        <?php if ( ! empty( $r->card_number ) ) : ?>
                                            <div style="font-family: monospace; font-size: 11px; color: #64748b; direction: ltr;">
                                                <?php echo esc_html( Paymentio_Helper::format_card_number( $r->card_number ) ); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $r->tracking_code ) ) : ?>
                                            <code class="paymentio-code" title="برای کپی کلیک کنید"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $r->tracking_code ) ); ?></code>
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 11px;">ثبت نشده</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $r->payer_card ) ) : ?>
                                            <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-weight: 700; color: #334155;"><?php echo esc_html( Paymentio_Helper::to_persian_digits( $r->payer_card ) ); ?></code>
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 11px;">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ( ! empty( $r->receipt_url ) ) : ?>
                                            <img src="<?php echo esc_url( $r->receipt_url ); ?>" alt="رسید" class="paymentio-table-thumb" onclick="openReceiptModal('<?php echo esc_url( $r->receipt_url ); ?>', '<?php echo esc_attr( $r->id ); ?>', '<?php echo esc_attr( $r->order_id ); ?>', '<?php echo esc_attr( $r->tracking_code ); ?>', '<?php echo esc_attr( $r->status ); ?>')" title="کلیک برای مشاهده اندازه کامل" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1.5px solid #cbd5e1; cursor: pointer; transition: transform 0.2s;" />
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 11px;">ندارد</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( 'approved' === $r->status ) : ?>
                                            <span class="paymentio-badge paymentio-badge-success">تایید شده</span>
                                        <?php elseif ( 'rejected' === $r->status ) : ?>
                                            <span class="paymentio-badge paymentio-badge-danger" title="<?php echo esc_attr( $r->rejection_reason ); ?>">رد شده</span>
                                        <?php else : ?>
                                            <span class="paymentio-badge paymentio-badge-warning">در انتظار بررسی</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 11.5px; color: #64748b;">
                                        <?php echo esc_html( Paymentio_Helper::to_persian_digits( $r->created_at ) ); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                            <?php if ( 'pending' === $r->status ) : ?>
                                                <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-success paymentio-btn-approve" data-id="<?php echo esc_attr( $r->id ); ?>" title="تایید پرداخت و تحویل محصول">
                                                    <?php echo Paymentio_Helper::icon( 'check-simple', 14 ); ?>
                                                    تایید
                                                </button>
                                                <button type="button" class="paymentio-btn paymentio-btn-sm paymentio-btn-danger paymentio-btn-reject" data-id="<?php echo esc_attr( $r->id ); ?>" title="رد رسید">
                                                    <?php echo Paymentio_Helper::icon( 'dismiss', 14 ); ?>
                                                    رد
                                                </button>
                                            <?php else : ?>
                                                <a href="<?php echo esc_url( $order_link ); ?>" class="paymentio-btn paymentio-btn-sm paymentio-btn-outline" target="_blank" title="مشاهده در سفارش">
                                                    <?php echo Paymentio_Helper::icon( 'eye', 14 ); ?>
                                                    سفارش
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="paymentio-pagination" style="padding: 16px 24px; display: flex; justify-content: center; gap: 6px;">
                        <?php
                        $base_url = add_query_arg( array(
                            'page'   => 'paymentio-receipts',
                            'status' => $status,
                            's'      => $search,
                        ), admin_url( 'admin.php' ) );

                        for ( $i = 1; $i <= $total_pages; $i++ ) :
                            $page_url = add_query_arg( 'paged', $i, $base_url );
                            $is_current = ( $i === $current_page );
                        ?>
                            <a href="<?php echo esc_url( $page_url ); ?>" class="paymentio-btn paymentio-btn-sm <?php echo $is_current ? 'paymentio-btn-primary' : 'paymentio-btn-outline'; ?>">
                                <?php echo esc_html( Paymentio_Helper::to_persian_digits( $i ) ); ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox Modal for Receipt Image -->
<div class="paymentio-modal" id="receipt-modal" style="display: none;">
    <div class="paymentio-modal-dialog" style="max-width: 640px;">
        <div class="paymentio-modal-header">
            <h3>بررسی تصویر رسید پرداخت</h3>
            <button type="button" class="paymentio-modal-close">&times;</button>
        </div>
        <div class="paymentio-modal-body" style="text-align: center; padding: 20px;">
            <img src="" id="modal-receipt-img" style="max-width: 100%; max-height: 480px; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 14px;" alt="رسید">
            <div id="modal-receipt-meta" style="text-align: right; font-size: 13px; color: #334155; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;"></div>
        </div>
        <div class="paymentio-modal-footer" id="modal-receipt-footer" style="display: flex; justify-content: space-between;">
            <button type="button" class="paymentio-btn paymentio-btn-outline paymentio-modal-close">بستن پنجره</button>
            <div id="modal-action-btns" style="display: flex; gap: 8px;"></div>
        </div>
    </div>
</div>

<script>
    function openReceiptModal(url, id, orderId, tracking, status) {
        var modal = jQuery('#receipt-modal');
        modal.find('#modal-receipt-img').attr('src', url);

        var metaHtml = '<div>شماره سفارش: <strong>#' + orderId + '</strong></div>';
        if (tracking) {
            metaHtml += '<div>کد پیگیری: <strong>' + tracking + '</strong></div>';
        }
        metaHtml += '<div>وضعیت فعلی: <strong>' + (status === 'approved' ? 'تایید شده' : (status === 'rejected' ? 'رد شده' : 'در انتظار بررسی')) + '</strong></div>';
        modal.find('#modal-receipt-meta').html(metaHtml);

        var actions = '';
        if (status === 'pending') {
            actions += '<button type="button" class="paymentio-btn paymentio-btn-success paymentio-btn-approve" data-id="' + id + '">تایید پرداخت</button>';
            actions += '<button type="button" class="paymentio-btn paymentio-btn-danger paymentio-btn-reject" data-id="' + id + '">رد رسید</button>';
        }
        modal.find('#modal-action-btns').html(actions);

        modal.css('display', 'flex').hide().fadeIn(200);
    }
</script>
