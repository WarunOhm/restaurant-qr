<?php
/**
 * การเชื่อมต่อฐานข้อมูล
 * ใช้ MySQLi (ไม่ใช้ PDO ตามที่กำหนด)
 */

// ข้อมูลการเชื่อมต่อฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurant_qr');

// สร้างการเชื่อมต่อ
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}

// ตั้งค่า charset เป็น utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// ตั้งค่า timezone เป็นเวลาไทย
date_default_timezone_set('Asia/Bangkok');
