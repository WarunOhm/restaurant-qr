<?php
/**
 * ไฟล์ Functions สำหรับใช้งานร่วมกัน
 */

// เริ่มต้น session ถ้ายังไม่เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ฟังก์ชันตรวจสอบการล็อกอิน
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * ฟังก์ชันตรวจสอบสิทธิ์ผู้ใช้
 */
function checkRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['user_role'] === $role;
}

/**
 * ฟังก์ชันตรวจสอบว่าเป็น Admin หรือไม่
 */
function isAdmin() {
    return checkRole('admin');
}

/**
 * ฟังก์ชันตรวจสอบว่าเป็น Staff หรือไม่
 */
function isStaff() {
    return checkRole('staff');
}

/**
 * ฟังก์ชัน redirect
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * ฟังก์ชันป้องกัน XSS
 */
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย
 */
function thaiDate($datetime, $format = 'full') {
    $thai_months = [
        '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
        '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
        '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
    ];

    $thai_months_full = [
        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
        '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
        '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
    ];

    $timestamp = strtotime($datetime);
    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year = date('Y', $timestamp) + 543;
    $time = date('H:i', $timestamp);

    if ($format === 'full') {
        return "$day {$thai_months_full[$month]} $year $time น.";
    } elseif ($format === 'short') {
        return "$day {$thai_months[$month]} $year";
    } elseif ($format === 'date_only') {
        return "$day {$thai_months_full[$month]} $year";
    } else {
        return "$day {$thai_months[$month]} $year $time";
    }
}

/**
 * ฟังก์ชันจัดรูปแบบตัวเลข (เงิน)
 */
function formatMoney($amount) {
    return number_format($amount, 2);
}

/**
 * ฟังก์ชันสร้างหมายเลขออเดอร์
 */
function generateOrderNumber() {
    return 'ORD' . date('Ymd') . sprintf('%04d', rand(1, 9999));
}

/**
 * ฟังก์ชันอัพโหลดรูปภาพ
 */
function uploadImage($file, $folder = 'menus') {
    $target_dir = "../uploads/$folder/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;

    // ตรวจสอบว่าเป็นรูปภาพจริง
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'ไฟล์ไม่ใช่รูปภาพ'];
    }

    // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'ขนาดไฟล์ใหญ่เกินไป (ไม่เกิน 5MB)'];
    }

    // อนุญาตเฉพาะไฟล์บางประเภท
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "webp") {
        return ['success' => false, 'message' => 'อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG และ WEBP'];
    }

    // อัพโหลดไฟล์
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัพโหลด'];
    }
}

/**
 * ฟังก์ชันลบรูปภาพ
 */
function deleteImage($filename, $folder = 'menus') {
    $file_path = "../uploads/$folder/$filename";
    if (file_exists($file_path) && $filename != 'default-food.jpg') {
        unlink($file_path);
        return true;
    }
    return false;
}

/**
 * ฟังก์ชันดึงข้อมูลการตั้งค่าร้าน
 */
function getSetting($conn, $key, $default = '') {
    $key = mysqli_real_escape_string($conn, $key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$key' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }

    return $default;
}

/**
 * ฟังก์ชันอัพเดทการตั้งค่าร้าน
 */
function updateSetting($conn, $key, $value) {
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);

    $query = "INSERT INTO settings (setting_key, setting_value)
              VALUES ('$key', '$value')
              ON DUPLICATE KEY UPDATE setting_value = '$value'";

    return mysqli_query($conn, $query);
}

/**
 * ฟังก์ชันสร้าง JSON Response
 */
function jsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
