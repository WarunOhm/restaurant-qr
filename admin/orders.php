<?php
$page_title = 'ดูบิลทั้งหมด';
require_once 'includes/header.php';

$filter = $_GET['filter'] ?? 'all';
$where = "";

if ($filter === 'today') {
    $where = "WHERE DATE(o.created_at) = CURDATE()";
} elseif ($filter === 'pending') {
    $where = "WHERE o.status IN ('pending', 'cooking', 'served')";
} elseif ($filter === 'paid') {
    $where = "WHERE o.status = 'paid'";
}

$orders_query = "SELECT o.*, t.table_number
    FROM orders o
    LEFT JOIN tables t ON o.table_id = t.id
    $where
    ORDER BY o.created_at DESC
    LIMIT 100";
$orders = mysqli_query($conn, $orders_query);
?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-receipt text-primary me-2"></i>ดูบิลทั้งหมด
                    </h5>
                    <div class="btn-group">
                        <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">ทั้งหมด</a>
                        <a href="?filter=today" class="btn btn-sm <?php echo $filter === 'today' ? 'btn-primary' : 'btn-outline-primary'; ?>">วันนี้</a>
                        <a href="?filter=pending" class="btn btn-sm <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">รอดำเนินการ</a>
                        <a href="?filter=paid" class="btn btn-sm <?php echo $filter === 'paid' ? 'btn-primary' : 'btn-outline-primary'; ?>">ชำระแล้ว</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>เลขออเดอร์</th>
                                <th>โต๊ะ</th>
                                <th>ยอดเงิน</th>
                                <th>สถานะ</th>
                                <th>เวลาสั่ง</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($orders) > 0): ?>
                                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                    <tr>
                                        <td><strong><?php echo clean($order['order_number']); ?></strong></td>
                                        <td><span class="badge bg-secondary"><?php echo clean($order['table_number']); ?></span></td>
                                        <td>฿<?php echo formatMoney($order['total_amount']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pending' => 'warning',
                                                'cooking' => 'info',
                                                'served' => 'primary',
                                                'paid' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_text = [
                                                'pending' => 'รอดำเนินการ',
                                                'cooking' => 'กำลังทำอาหาร',
                                                'served' => 'เสิร์ฟแล้ว',
                                                'paid' => 'ชำระเงินแล้ว',
                                                'cancelled' => 'ยกเลิก'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_class[$order['status']]; ?>">
                                                <?php echo $status_text[$order['status']]; ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo thaiDate($order['created_at']); ?></small></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-eye"></i> ดูรายละเอียด
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        ไม่พบบิล
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrder(orderId) {
    window.open(`view_order.php?id=${orderId}`, '_blank', 'width=800,height=600');
}
</script>

<?php require_once 'includes/footer.php'; ?>
