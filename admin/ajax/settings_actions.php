<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'ไม่มีสิทธิ์เข้าถึง');
}

$action = $_POST['action'] ?? '';

if ($action === 'save_settings') {
    $settings = [
        'restaurant_name' => $_POST['restaurant_name'] ?? '',
        'restaurant_address' => $_POST['restaurant_address'] ?? '',
        'restaurant_phone' => $_POST['restaurant_phone'] ?? '',
        'tax_percent' => $_POST['tax_percent'] ?? '7',
        'service_charge_percent' => $_POST['service_charge_percent'] ?? '0',
        'receipt_footer' => $_POST['receipt_footer'] ?? ''
    ];

    foreach ($settings as $key => $value) {
        updateSetting($conn, $key, $value);
    }

    jsonResponse(true, 'บันทึกการตั้งค่าสำเร็จ');
}

jsonResponse(false, 'ไม่พบคำสั่งที่ต้องการ');
