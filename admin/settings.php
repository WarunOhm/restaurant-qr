<?php
$page_title = 'ตั้งค่าระบบ';
require_once 'includes/header.php';

$restaurant_name = getSetting($conn, 'restaurant_name', '');
$restaurant_address = getSetting($conn, 'restaurant_address', '');
$restaurant_phone = getSetting($conn, 'restaurant_phone', '');
$tax_percent = getSetting($conn, 'tax_percent', '7');
$service_charge_percent = getSetting($conn, 'service_charge_percent', '0');
$receipt_footer = getSetting($conn, 'receipt_footer', '');
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-4 fw-semibold">
                    <i class="fas fa-cog text-primary me-2"></i>ตั้งค่าร้านอาหาร
                </h5>

                <form id="settingsForm">
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">ข้อมูลร้าน</h6>

                        <div class="mb-3">
                            <label class="form-label">ชื่อร้าน</label>
                            <input type="text" class="form-control" name="restaurant_name" value="<?php echo clean($restaurant_name); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ที่อยู่</label>
                            <textarea class="form-control" name="restaurant_address" rows="2"><?php echo clean($restaurant_address); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" name="restaurant_phone" value="<?php echo clean($restaurant_phone); ?>">
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">การคำนวณราคา</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ภาษี VAT (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="tax_percent" value="<?php echo clean($tax_percent); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Service Charge (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="service_charge_percent" value="<?php echo clean($service_charge_percent); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">ใบเสร็จ</h6>

                        <div class="mb-3">
                            <label class="form-label">ข้อความท้ายใบเสร็จ</label>
                            <textarea class="form-control" name="receipt_footer" rows="2" placeholder="เช่น ขอบคุณที่ใช้บริการ"><?php echo clean($receipt_footer); ?></textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>บันทึกการตั้งค่า
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_settings');

    try {
        const response = await fetch('ajax/settings_actions.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Toast.fire({ icon: 'success', title: result.message });
        } else {
            Toast.fire({ icon: 'error', title: result.message });
        }
    } catch (error) {
        Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
