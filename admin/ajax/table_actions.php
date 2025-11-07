<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ตรวจสอบการล็อกอินและสิทธิ์
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'ไม่มีสิทธิ์เข้าถึง');
}

$action = $_POST['action'] ?? '';

// บันทึกโต๊ะ
if ($action === 'save_table') {
    $table_id = mysqli_real_escape_string($conn, $_POST['table_id'] ?? '');
    $table_number = mysqli_real_escape_string($conn, $_POST['table_number']);
    $table_capacity = intval($_POST['table_capacity']);
    $table_status = mysqli_real_escape_string($conn, $_POST['table_status']);

    if (empty($table_number) || $table_capacity <= 0) {
        jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    if ($table_id) {
        // ตรวจสอบหมายเลขโต๊ะซ้ำ (ยกเว้นตัวเอง)
        $check = "SELECT id FROM tables WHERE table_number = '$table_number' AND id != $table_id";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            jsonResponse(false, 'หมายเลขโต๊ะนี้มีอยู่แล้ว');
        }

        // แก้ไข
        $query = "UPDATE tables SET
            table_number = '$table_number',
            capacity = $table_capacity,
            status = '$table_status'
            WHERE id = $table_id";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'แก้ไขโต๊ะสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการแก้ไขโต๊ะ');
        }
    } else {
        // ตรวจสอบหมายเลขโต๊ะซ้ำ
        $check = "SELECT id FROM tables WHERE table_number = '$table_number'";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            jsonResponse(false, 'หมายเลขโต๊ะนี้มีอยู่แล้ว');
        }

        // เพิ่มใหม่
        $query = "INSERT INTO tables (table_number, capacity, status)
            VALUES ('$table_number', $table_capacity, '$table_status')";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'เพิ่มโต๊ะสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการเพิ่มโต๊ะ');
        }
    }
}

// ลบโต๊ะ
if ($action === 'delete_table') {
    $table_id = intval($_POST['table_id']);

    // ตรวจสอบว่ามีออเดอร์ที่กำลังใช้โต๊ะอยู่หรือไม่
    $check = "SELECT id FROM orders WHERE table_id = $table_id AND status NOT IN ('paid', 'cancelled')";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        jsonResponse(false, 'ไม่สามารถลบโต๊ะที่มีออเดอร์อยู่ได้');
    }

    $query = "DELETE FROM tables WHERE id = $table_id";

    if (mysqli_query($conn, $query)) {
        jsonResponse(true, 'ลบโต๊ะสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาดในการลบโต๊ะ');
    }
}

jsonResponse(false, 'ไม่พบคำสั่งที่ต้องการ');
