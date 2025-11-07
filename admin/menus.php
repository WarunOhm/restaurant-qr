<?php
$page_title = 'จัดการเมนูอาหาร';
require_once 'includes/header.php';

// ดึงหมวดหมู่ทั้งหมด
$categories_query = "SELECT * FROM categories ORDER BY sort_order ASC, id ASC";
$categories = mysqli_query($conn, $categories_query);

// ดึงเมนูทั้งหมดพร้อมหมวดหมู่
$menus_query = "SELECT m.*, c.name as category_name
    FROM menus m
    LEFT JOIN categories c ON m.category_id = c.id
    ORDER BY c.sort_order ASC, m.id DESC";
$menus = mysqli_query($conn, $menus_query);
?>

<div class="row g-4">
    <!-- ส่วนหมวดหมู่ -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold">
                    <i class="fas fa-folder text-primary me-2"></i>หมวดหมู่อาหาร
                </h5>

                <button class="btn btn-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCategoryForm()">
                    <i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่ใหม่
                </button>

                <div id="categoriesList">
                    <?php
                    mysqli_data_seek($categories, 0);
                    while ($cat = mysqli_fetch_assoc($categories)):
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded hover-bg">
                            <div>
                                <span class="fs-4 me-2"><?php echo clean($cat['icon']); ?></span>
                                <strong><?php echo clean($cat['name']); ?></strong>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" onclick='editCategory(<?php echo json_encode($cat); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนเมนูอาหาร -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-utensils text-primary me-2"></i>เมนูอาหาร
                    </h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal" onclick="resetMenuForm()">
                        <i class="fas fa-plus me-2"></i>เพิ่มเมนูใหม่
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="80">รูป</th>
                                <th>ชื่อเมนู</th>
                                <th>หมวดหมู่</th>
                                <th width="100">ราคา</th>
                                <th width="100">สถานะ</th>
                                <th width="120">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($menus) > 0): ?>
                                <?php while ($menu = mysqli_fetch_assoc($menus)): ?>
                                    <tr>
                                        <td>
                                            <img src="../uploads/menus/<?php echo clean($menu['image']); ?>"
                                                 alt="<?php echo clean($menu['name']); ?>"
                                                 class="rounded"
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                        </td>
                                        <td>
                                            <strong><?php echo clean($menu['name']); ?></strong>
                                            <?php if ($menu['is_recommended']): ?>
                                                <span class="badge bg-warning text-dark ms-1">แนะนำ</span>
                                            <?php endif; ?>
                                            <?php if ($menu['description']): ?>
                                                <br><small class="text-muted"><?php echo clean($menu['description']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo clean($menu['category_name']); ?></span>
                                        </td>
                                        <td>
                                            <strong>฿<?php echo formatMoney($menu['price']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($menu['status'] == 'available'): ?>
                                                <span class="badge bg-success">มีจำหน่าย</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">หมด</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick='editMenu(<?php echo json_encode($menu); ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteMenu(<?php echo $menu['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        ยังไม่มีเมนูอาหาร
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

<!-- Modal หมวดหมู่ -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">เพิ่มหมวดหมู่ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <input type="hidden" name="category_id" id="category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="category_name" id="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ไอคอน</label>
                        <input type="text" class="form-control" name="category_icon" id="category_icon" placeholder="🍽️">
                        <small class="text-muted">คุณสามารถใส่ emoji หรือทิ้งว่างไว้</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลำดับการแสดง</label>
                        <input type="number" class="form-control" name="category_sort" id="category_sort" value="0">
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

<!-- Modal เมนู -->
<div class="modal fade" id="menuModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuModalTitle">เพิ่มเมนูใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="menuForm" enctype="multipart/form-data">
                <input type="hidden" name="menu_id" id="menu_id">
                <input type="hidden" name="current_image" id="current_image">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ชื่อเมนู <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="menu_name" id="menu_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <select class="form-select" name="menu_category" id="menu_category" required>
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php
                                    mysqli_data_seek($categories, 0);
                                    while ($cat = mysqli_fetch_assoc($categories)):
                                    ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo clean($cat['icon'] . ' ' . $cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" name="menu_description" id="menu_description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="menu_price" id="menu_price" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" name="menu_status" id="menu_status">
                                    <option value="available">มีจำหน่าย</option>
                                    <option value="unavailable">หมด</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รูปภาพเมนู</label>
                        <input type="file" class="form-control" name="menu_image" id="menu_image" accept="image/*">
                        <small class="text-muted">รองรับ JPG, JPEG, PNG, WEBP (ไม่เกิน 5MB)</small>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="menu_recommended" id="menu_recommended" value="1">
                        <label class="form-check-label" for="menu_recommended">
                            เมนูแนะนำ
                        </label>
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

<style>
.hover-bg:hover {
    background-color: #f8f9fa;
}
</style>

<script>
// Category Functions
function resetCategoryForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('categoryModalTitle').textContent = 'เพิ่มหมวดหมู่ใหม่';
}

function editCategory(category) {
    document.getElementById('category_id').value = category.id;
    document.getElementById('category_name').value = category.name;
    document.getElementById('category_icon').value = category.icon;
    document.getElementById('category_sort').value = category.sort_order;
    document.getElementById('categoryModalTitle').textContent = 'แก้ไขหมวดหมู่';

    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
}

document.getElementById('categoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_category');

    try {
        const response = await fetch('ajax/menu_actions.php', {
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

async function deleteCategory(id) {
    const result = await confirmDelete('คุณต้องการลบหมวดหมู่นี้? (เมนูในหมวดหมู่นี้จะถูกลบด้วย)');

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_category');
        formData.append('category_id', id);

        try {
            const response = await fetch('ajax/menu_actions.php', {
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

// Menu Functions
function resetMenuForm() {
    document.getElementById('menuForm').reset();
    document.getElementById('menu_id').value = '';
    document.getElementById('current_image').value = '';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('menuModalTitle').textContent = 'เพิ่มเมนูใหม่';
}

function editMenu(menu) {
    document.getElementById('menu_id').value = menu.id;
    document.getElementById('menu_name').value = menu.name;
    document.getElementById('menu_category').value = menu.category_id;
    document.getElementById('menu_description').value = menu.description || '';
    document.getElementById('menu_price').value = menu.price;
    document.getElementById('menu_status').value = menu.status;
    document.getElementById('menu_recommended').checked = menu.is_recommended == 1;
    document.getElementById('current_image').value = menu.image;
    document.getElementById('menuModalTitle').textContent = 'แก้ไขเมนู';

    if (menu.image) {
        document.getElementById('imagePreview').innerHTML =
            `<img src="../uploads/menus/${menu.image}" class="img-thumbnail" style="max-width: 200px;">`;
    }

    const modal = new bootstrap.Modal(document.getElementById('menuModal'));
    modal.show();
}

document.getElementById('menu_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML =
                `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">`;
        }
        reader.readAsDataURL(file);
    }
});

document.getElementById('menuForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_menu');

    try {
        const response = await fetch('ajax/menu_actions.php', {
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

async function deleteMenu(id) {
    const result = await confirmDelete('คุณต้องการลบเมนูนี้?');

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_menu');
        formData.append('menu_id', id);

        try {
            const response = await fetch('ajax/menu_actions.php', {
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
