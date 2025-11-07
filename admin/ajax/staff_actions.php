<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'ไม่มีสิทธิ์เข้าถึง');
}

$action = $_POST['action'] ?? '';

if ($action === 'save_staff') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $password = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (empty($username) || empty($fullname)) {
        jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    if ($user_id) {
        // ตรวจสอบชื่อผู้ใช้ซ้ำ
        $check = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            jsonResponse(false, 'ชื่อผู้ใช้นี้มีอยู่แล้ว');
        }

        // แก้ไข
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET
                username = '$username',
                fullname = '$fullname',
                password = '$hashed_password',
                role = '$role',
                status = '$status'
                WHERE id = $user_id";
        } else {
            $query = "UPDATE users SET
                username = '$username',
                fullname = '$fullname',
                role = '$role',
                status = '$status'
                WHERE id = $user_id";
        }

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'แก้ไขข้อมูลพนักงานสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล');
        }
    } else {
        if (empty($password)) {
            jsonResponse(false, 'กรุณากรอกรหัสผ่าน');
        }

        // ตรวจสอบชื่อผู้ใช้ซ้ำ
        $check = "SELECT id FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            jsonResponse(false, 'ชื่อผู้ใช้นี้มีอยู่แล้ว');
        }

        // เพิ่มใหม่
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, fullname, role, status)
            VALUES ('$username', '$hashed_password', '$fullname', '$role', '$status')";

        if (mysqli_query($conn, $query)) {
            jsonResponse(true, 'เพิ่มพนักงานสำเร็จ');
        } else {
            jsonResponse(false, 'เกิดข้อผิดพลาดในการเพิ่มพนักงาน');
        }
    }
}

if ($action === 'delete_staff') {
    $user_id = intval($_POST['user_id']);

    // ป้องกันการลบตัวเอง
    if ($user_id == $_SESSION['user_id']) {
        jsonResponse(false, 'ไม่สามารถลบบัญชีของตัวเองได้');
    }

    $query = "DELETE FROM users WHERE id = $user_id";

    if (mysqli_query($conn, $query)) {
        jsonResponse(true, 'ลบพนักงานสำเร็จ');
    } else {
        jsonResponse(false, 'เกิดข้อผิดพลาดในการลบพนักงาน');
    }
}

jsonResponse(false, 'ไม่พบคำสั่งที่ต้องการ');
