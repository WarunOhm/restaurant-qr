<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ตรวจสอบการล็อกอินและสิทธิ์
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'ไม่มีสิทธิ์เข้าถึง');
}

$action = $_POST['action'] ?? '';

// บันทึกหมวดหมู่
if ($action === 'save_category') {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id'] ?? '');
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_icon = mysqli_real_escape_string($conn, $_POST['category_icon'] ?? '🍽️');
    $category_sort = intval($_POST['category_sort'] ?? 0);

    if (empty($category_name)) {
        jsonResponse(false, 'กรุณากรอกชื่อหมวดหมู่');
    }

    if ($category_id) {
        // แก้ไข
        $query = "UPDATE categories SET
            name = '$category_name',
            icon = '$category_icon',
            sort_order = $category_sort
            WHERE id = $category_id";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'แก้ไขหมวดหมู่สำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการแก้ไขหมวดหมู่');
        }
    } else {
        // เพิ่มใหม่
        $query = "INSERT INTO categories (name, icon, sort_order)
            VALUES ('$category_name', '$category_icon', $category_sort)";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'เพิ่มหมวดหมู่สำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการเพิ่มหมวดหมู่');
        }
    }
}

// ลบหมวดหมู่
if ($action === 'delete_category') {
    $category_id = intval($_POST['category_id']);

    $query = "DELETE FROM categories WHERE id = $category_id";

    if (mysqli_query($conn, $query)) {
        jsonResponse(true, 'ลบหมวดหมู่สำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาดในการลบหมวดหมู่');
    }
}

// บันทึกเมนู
if ($action === 'save_menu') {
    $menu_id = mysqli_real_escape_string($conn, $_POST['menu_id'] ?? '');
    $menu_name = mysqli_real_escape_string($conn, $_POST['menu_name']);
    $menu_category = intval($_POST['menu_category']);
    $menu_description = mysqli_real_escape_string($conn, $_POST['menu_description'] ?? '');
    $menu_price = floatval($_POST['menu_price']);
    $menu_status = mysqli_real_escape_string($conn, $_POST['menu_status']);
    $menu_recommended = isset($_POST['menu_recommended']) ? 1 : 0;
    $current_image = mysqli_real_escape_string($conn, $_POST['current_image'] ?? 'default-food.jpg');

    if (empty($menu_name) || empty($menu_category) || $menu_price <= 0) {
        jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    $image_name = $current_image;

    // จัดการอัพโหลดรูปภาพ
    if (isset($_FILES['menu_image']) && $_FILES['menu_image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['menu_image'], 'menus');

        if ($upload_result['success']) {
            // ลบรูปเก่า (ถ้าไม่ใช่ default)
            if ($current_image && $current_image !== 'default-food.jpg') {
                deleteImage($current_image, 'menus');
            }
            $image_name = $upload_result['filename'];
        } else {
            jsonResponse(false, $upload_result['message']);
        }
    }

    if ($menu_id) {
        // แก้ไข
        $query = "UPDATE menus SET
            category_id = $menu_category,
            name = '$menu_name',
            description = '$menu_description',
            price = $menu_price,
            image = '$image_name',
            status = '$menu_status',
            is_recommended = $menu_recommended
            WHERE id = $menu_id";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'แก้ไขเมนูสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการแก้ไขเมนู');
        }
    } else {
        // เพิ่มใหม่
        $query = "INSERT INTO menus (category_id, name, description, price, image, status, is_recommended)
            VALUES ($menu_category, '$menu_name', '$menu_description', $menu_price, '$image_name', '$menu_status', $menu_recommended)";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'เพิ่มเมนูสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการเพิ่มเมนู');
        }
    }
}

// ลบเมนู
if ($action === 'delete_menu') {
    $menu_id = intval($_POST['menu_id']);

    // ดึงข้อมูลรูปภาพก่อนลบ
    $query = "SELECT image FROM menus WHERE id = $menu_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $menu = mysqli_fetch_assoc($result);

        // ลบรูปภาพ (ถ้าไม่ใช่ default)
        if ($menu['image'] && $menu['image'] !== 'default-food.jpg') {
            deleteImage($menu['image'], 'menus');
        }

        // ลบเมนู
        $query = "DELETE FROM menus WHERE id = $menu_id";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'ลบเมนูสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการลบเมนู');
        }
    } else {
        jsonResponse(false, 'ไม่พบเมนูที่ต้องการลบ');
    }
}

jsonResponse(false, 'ไม่พบคำสั่งที่ต้องการ');
