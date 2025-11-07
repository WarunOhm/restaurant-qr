<?php
$page_title = 'จัดการโต๊ะ & QR Code';
require_once 'includes/header.php';

// ดึงข้อมูลโต๊ะทั้งหมด
$tables_query = "SELECT * FROM tables ORDER BY table_number ASC";
$tables = mysqli_query($conn, $tables_query);

// URL พื้นฐานสำหรับ QR Code
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$customer_url = $base_url . dirname(dirname($_SERVER['PHP_SELF'])) . '/customer/';
?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-qrcode text-primary me-2"></i>จัดการโต๊ะ & QR Code
                    </h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tableModal" onclick="resetTableForm()">
                        <i class="fas fa-plus me-2"></i>เพิ่มโต๊ะใหม่
                    </button>
                </div>

                <div class="row g-3">
                    <?php if (mysqli_num_rows($tables) > 0): ?>
                        <?php while ($table = mysqli_fetch_assoc($tables)): ?>
                            <?php
                            $status_class = [
                                'available' => 'success',
                                'occupied' => 'danger',
                                'reserved' => 'warning'
                            ];
                            $status_text = [
                                'available' => 'ว่าง',
                                'occupied' => 'มีลูกค้า',
                                'reserved' => 'จอง'
                            ];
                            $status_icon = [
                                'available' => 'check-circle',
                                'occupied' => 'user-friends',
                                'reserved' => 'clock'
                            ];
                            ?>
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <div class="bg-<?php echo $status_class[$table['status']]; ?> bg-opacity-10 p-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                <i class="fas fa-chair text-<?php echo $status_class[$table['status']]; ?> fa-2x"></i>
                                            </div>
                                        </div>

                                        <h4 class="fw-bold mb-1">โต๊ะ <?php echo clean($table['table_number']); ?></h4>

                                        <span class="badge bg-<?php echo $status_class[$table['status']]; ?> mb-2">
                                            <i class="fas fa-<?php echo $status_icon[$table['status']]; ?> me-1"></i>
                                            <?php echo $status_text[$table['status']]; ?>
                                        </span>

                                        <p class="text-muted mb-3">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $table['capacity']; ?> ที่นั่ง
                                        </p>

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="showQRCode('<?php echo $table['table_number']; ?>', '<?php echo $customer_url . '?table=' . $table['id']; ?>')">
                                                <i class="fas fa-qrcode me-1"></i>ดู QR Code
                                            </button>

                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-secondary btn-sm" onclick='editTable(<?php echo json_encode($table); ?>)'>
                                                    <i class="fas fa-edit"></i> แก้ไข
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteTable(<?php echo $table['id']; ?>)">
                                                    <i class="fas fa-trash"></i> ลบ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <h5>ยังไม่มีโต๊ะ</h5>
                                <p>เริ่มต้นด้วยการเพิ่มโต๊ะใหม่</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่ม/แก้ไขโต๊ะ -->
<div class="modal fade" id="tableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tableModalTitle">เพิ่มโต๊ะใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tableForm">
                <input type="hidden" name="table_id" id="table_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">หมายเลขโต๊ะ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="table_number" id="table_number" placeholder="เช่น A1, B2, 101" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">จำนวนที่นั่ง <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="table_capacity" id="table_capacity" min="1" value="4" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" name="table_status" id="table_status">
                            <option value="available">ว่าง</option>
                            <option value="occupied">มีลูกค้า</option>
                            <option value="reserved">จอง</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal แสดง QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalTitle">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeDisplay" class="mb-3"></div>
                <p class="text-muted mb-3" id="qrUrl"></p>
                <button class="btn btn-primary" onclick="downloadQRCode()">
                    <i class="fas fa-download me-2"></i>ดาวน์โหลด QR Code
                </button>
                <button class="btn btn-outline-primary" onclick="printQRCode()">
                    <i class="fas fa-print me-2"></i>พิมพ์ QR Code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Generator Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
let currentQRCode = null;
let currentTableNumber = '';
let currentQRUrl = '';

function resetTableForm() {
    document.getElementById('tableForm').reset();
    document.getElementById('table_id').value = '';
    document.getElementById('tableModalTitle').textContent = 'เพิ่มโต๊ะใหม่';
}

function editTable(table) {
    document.getElementById('table_id').value = table.id;
    document.getElementById('table_number').value = table.table_number;
    document.getElementById('table_capacity').value = table.capacity;
    document.getElementById('table_status').value = table.status;
    document.getElementById('tableModalTitle').textContent = 'แก้ไขโต๊ะ';

    const modal = new bootstrap.Modal(document.getElementById('tableModal'));
    modal.show();
}

document.getElementById('tableForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_table');

    try {
        const response = await fetch('ajax/table_actions.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Toast.fire({ icon: 'success', title: result.message });
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.fire({ icon: 'error', title: result.message });
        }
    } catch (error) {
        Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
    }
});

async function deleteTable(id) {
    const result = await confirmDelete('คุณต้องการลบโต๊ะนี้?');

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_table');
        formData.append('table_id', id);

        try {
            const response = await fetch('ajax/table_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.fire({ icon: 'success', title: data.message });
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.fire({ icon: 'error', title: data.message });
            }
        } catch (error) {
            Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
        }
    }
}

function showQRCode(tableNumber, url) {
    currentTableNumber = tableNumber;
    currentQRUrl = url;

    document.getElementById('qrModalTitle').textContent = `QR Code - โต๊ะ ${tableNumber}`;
    document.getElementById('qrUrl').textContent = url;

    const qrDisplay = document.getElementById('qrCodeDisplay');
    qrDisplay.innerHTML = '';

    currentQRCode = new QRCode(qrDisplay, {
        text: url,
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
}

function downloadQRCode() {
    const canvas = document.querySelector('#qrCodeDisplay canvas');
    if (canvas) {
        const link = document.createElement('a');
        link.download = `table-${currentTableNumber}-qrcode.png`;
        link.href = canvas.toDataURL();
        link.click();

        Toast.fire({ icon: 'success', title: 'ดาวน์โหลด QR Code สำเร็จ' });
    }
}

function printQRCode() {
    const qrDisplay = document.getElementById('qrCodeDisplay').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');

    printWindow.document.write(`
        <html>
        <head>
            <title>QR Code - โต๊ะ ${currentTableNumber}</title>
            <style>
                body {
                    font-family: 'Prompt', sans-serif;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                h1 { margin: 20px 0; }
                .qr-container { text-align: center; }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <h1>โต๊ะ ${currentTableNumber}</h1>
                ${qrDisplay}
                <p style="margin-top: 20px;">สแกน QR Code เพื่อสั่งอาหาร</p>
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();

    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}
</script>

<?php require_once 'includes/footer.php'; ?>
