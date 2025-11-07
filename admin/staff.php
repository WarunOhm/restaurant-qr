<?php
$page_title = 'จัดการพนักงาน';
require_once 'includes/header.php';

// นับจำนวนพนักงานที่รอการอนุมัติ
$pending_query = "SELECT COUNT(*) as total FROM users WHERE status = 'inactive'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_count = mysqli_fetch_assoc($pending_result)['total'];

// ดึงข้อมูลพนักงานทั้งหมด (แบ่งตามสถานะ)
$staff_query = "SELECT * FROM users ORDER BY
    CASE WHEN status = 'inactive' THEN 0 ELSE 1 END,
    role ASC,
    created_at DESC";
$staff = mysqli_query($conn, $staff_query);
?>

<div class="row g-4">
    <!-- แจ้งเตือนพนักงานรออนุมัติ -->
    <?php if ($pending_count > 0): ?>
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-semibold">มีพนักงานรอการอนุมัติ!</h6>
                <p class="mb-0">พบพนักงาน <strong><?php echo $pending_count; ?> คน</strong> ที่รอการอนุมัติเข้าใช้งานระบบ</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-users text-primary me-2"></i>จัดการพนักงาน
                    </h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staffModal" onclick="resetStaffForm()">
                        <i class="fas fa-user-plus me-2"></i>เพิ่มพนักงาน
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อผู้ใช้</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>บทบาท</th>
                                <th>สถานะ</th>
                                <th>วันที่สร้าง</th>
                                <th width="200">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($staff)): ?>
                                <tr <?php echo $user['status'] === 'inactive' ? 'class="table-warning"' : ''; ?>>
                                    <td>
                                        <strong><?php echo clean($user['username']); ?></strong>
                                        <?php if ($user['status'] === 'inactive'): ?>
                                            <span class="badge bg-warning text-dark ms-2">
                                                <i class="fas fa-clock"></i> ใหม่
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo clean($user['fullname']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-shield-alt"></i> ผู้ดูแลระบบ
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-user"></i> พนักงาน
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> ใช้งาน
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock"></i> รออนุมัติ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo thaiDate($user['created_at'], 'short'); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'inactive'): ?>
                                            <!-- ปุ่มสำหรับพนักงานรออนุมัติ -->
                                            <button class="btn btn-sm btn-success" onclick="approveStaff(<?php echo $user['id']; ?>, '<?php echo clean($user['fullname']); ?>')" title="อนุมัติ">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectStaff(<?php echo $user['id']; ?>, '<?php echo clean($user['fullname']); ?>')" title="ปฏิเสธ">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php else: ?>
                                            <!-- ปุ่มปกติสำหรับพนักงานที่อนุมัติแล้ว -->
                                            <button class="btn btn-sm btn-outline-primary" onclick='editStaff(<?php echo json_encode($user); ?>)' title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteStaff(<?php echo $user['id']; ?>)" title="ลบ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal พนักงาน -->
<div class="modal fade" id="staffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staffModalTitle">เพิ่มพนักงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="staffForm">
                <input type="hidden" name="user_id" id="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="fullname" id="fullname" required>
                    </div>
                    <div class="mb-3" id="passwordField">
                        <label class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" id="password">
                        <small class="text-muted">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">บทบาท</label>
                        <select class="form-select" name="role" id="role">
                            <option value="staff">พนักงาน</option>
                            <option value="admin">ผู้ดูแลระบบ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" name="status" id="status">
                            <option value="active">ใช้งาน</option>
                            <option value="inactive">ปิดใช้งาน</option>
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

<script>
function resetStaffForm() {
    document.getElementById('staffForm').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('staffModalTitle').textContent = 'เพิ่มพนักงาน';
}

function editStaff(user) {
    document.getElementById('user_id').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('fullname').value = user.fullname;
    document.getElementById('role').value = user.role;
    document.getElementById('status').value = user.status;
    document.getElementById('password').required = false;
    document.getElementById('password').value = '';
    document.getElementById('staffModalTitle').textContent = 'แก้ไขข้อมูลพนักงาน';

    const modal = new bootstrap.Modal(document.getElementById('staffModal'));
    modal.show();
}

document.getElementById('staffForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_staff');

    try {
        const response = await fetch('ajax/staff_actions.php', {
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

async function approveStaff(id, fullname) {
    const result = await Swal.fire({
        title: 'อนุมัติพนักงาน',
        html: `คุณต้องการอนุมัติ<br><strong>${fullname}</strong><br>ให้เข้าใช้งานระบบ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-check me-2"></i>อนุมัติ',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'approve_staff');
        formData.append('user_id', id);

        try {
            const response = await fetch('ajax/staff_actions.php', {
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

async function rejectStaff(id, fullname) {
    const result = await Swal.fire({
        title: 'ปฏิเสธการสมัคร',
        html: `คุณต้องการปฏิเสธการสมัครของ<br><strong>${fullname}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-times me-2"></i>ปฏิเสธ',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_staff');
        formData.append('user_id', id);

        try {
            const response = await fetch('ajax/staff_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.fire({ icon: 'success', title: 'ปฏิเสธการสมัครสำเร็จ' });
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.fire({ icon: 'error', title: data.message });
            }
        } catch (error) {
            Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
        }
    }
}

async function deleteStaff(id) {
    const result = await confirmDelete('คุณต้องการลบพนักงานคนนี้?');

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_staff');
        formData.append('user_id', id);

        try {
            const response = await fetch('ajax/staff_actions.php', {
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
</script>

<?php require_once 'includes/footer.php'; ?>
