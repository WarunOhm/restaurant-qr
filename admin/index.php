<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

// ดึงข้อมูลสำหรับแดชบอร์ด
$stats = [];

// จำนวนโต๊ะทั้งหมดและสถานะ
$query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied
    FROM tables";
$result = mysqli_query($conn, $query);
$stats['tables'] = mysqli_fetch_assoc($result);

// จำนวนเมนูทั้งหมด
$query = "SELECT COUNT(*) as total FROM menus WHERE status = 'available'";
$result = mysqli_query($conn, $query);
$stats['menus'] = mysqli_fetch_assoc($result)['total'];

// จำนวนออเดอร์วันนี้
$query = "SELECT
    COUNT(*) as total,
    SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'";
$result = mysqli_query($conn, $query);
$stats['today'] = mysqli_fetch_assoc($result);

// จำนวนออเดอร์ที่กำลังทำอาหาร
$query = "SELECT COUNT(*) as total FROM orders WHERE status IN ('pending', 'cooking')";
$result = mysqli_query($conn, $query);
$stats['pending_orders'] = mysqli_fetch_assoc($result)['total'];

// ออเดอร์ล่าสุด
$query = "SELECT o.*, t.table_number
    FROM orders o
    LEFT JOIN tables t ON o.table_id = t.id
    ORDER BY o.created_at DESC
    LIMIT 5";
$recent_orders = mysqli_query($conn, $query);

// จำนวนพนักงาน
$query = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
$result = mysqli_query($conn, $query);
$stats['staff'] = mysqli_fetch_assoc($result)['total'];
?>

<div class="row g-4">
    <!-- Stat Cards -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">โต๊ะว่าง</p>
                        <h2 class="mb-0 fw-bold"><?php echo $stats['tables']['available']; ?>/<?php echo $stats['tables']['total']; ?></h2>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>พร้อมให้บริการ
                        </small>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-chair text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">ออเดอร์วันนี้</p>
                        <h2 class="mb-0 fw-bold"><?php echo $stats['today']['total'] ?? 0; ?></h2>
                        <small class="text-primary">
                            <i class="fas fa-shopping-cart me-1"></i>รายการ
                        </small>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-receipt text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">รายได้วันนี้</p>
                        <h2 class="mb-0 fw-bold">฿<?php echo formatMoney($stats['today']['revenue'] ?? 0); ?></h2>
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>บาท
                        </small>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fas fa-coins text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">กำลังทำอาหาร</p>
                        <h2 class="mb-0 fw-bold"><?php echo $stats['pending_orders']; ?></h2>
                        <small class="text-danger">
                            <i class="fas fa-fire me-1"></i>รายการ
                        </small>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="fas fa-concierge-bell text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- ออเดอร์ล่าสุด -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-clock me-2 text-primary"></i>ออเดอร์ล่าสุด
                    </h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">ดูทั้งหมด</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>เลขออเดอร์</th>
                                <th>โต๊ะ</th>
                                <th>ยอดเงิน</th>
                                <th>สถานะ</th>
                                <th>เวลา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                                <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo clean($order['order_number']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo clean($order['table_number']); ?></span>
                                        </td>
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
                                        <td>
                                            <small><?php echo thaiDate($order['created_at'], 'short'); ?></small>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        ยังไม่มีออเดอร์
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- สรุปข้อมูล -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>สรุปข้อมูล
                </h5>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <i class="fas fa-utensils text-primary me-2"></i>
                        <span>เมนูอาหารทั้งหมด</span>
                    </div>
                    <strong><?php echo $stats['menus']; ?></strong>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <i class="fas fa-chair text-success me-2"></i>
                        <span>โต๊ะทั้งหมด</span>
                    </div>
                    <strong><?php echo $stats['tables']['total']; ?></strong>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <i class="fas fa-users text-info me-2"></i>
                        <span>พนักงานทั้งหมด</span>
                    </div>
                    <strong><?php echo $stats['staff']; ?></strong>
                </div>

                <a href="settings.php" class="btn btn-outline-primary w-100 mt-3">
                    <i class="fas fa-cog me-2"></i>ตั้งค่าระบบ
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
