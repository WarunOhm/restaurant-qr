<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// ตรวจสอบการล็อกอินและสิทธิ์
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// ดึงข้อมูลร้าน
$restaurant_name = getSetting($conn, 'restaurant_name', 'Restaurant QR');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - <?php echo clean($restaurant_name); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts - Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-minimal/minimal.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            font-family: 'Prompt', sans-serif;
        }

        body {
            background: #f8fafc;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            transition: all 0.3s;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar-header {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-logo i {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            padding: 1rem;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            margin-bottom: 0.5rem;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .menu-item.active {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .top-navbar {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .content-area {
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        .mobile-menu-btn {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-utensils"></i>
                <span>Admin Panel</span>
            </div>
            <small class="d-block mt-2 text-white-50">
                <i class="fas fa-user-shield me-1"></i>
                <?php echo clean($_SESSION['fullname']); ?>
            </small>
        </div>

        <div class="sidebar-menu">
            <a href="index.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>หน้าแรก</span>
            </a>

            <a href="menus.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'menus.php' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i>
                <span>จัดการเมนูอาหาร</span>
            </a>

            <a href="tables.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'active' : ''; ?>">
                <i class="fas fa-qrcode"></i>
                <span>จัดการโต๊ะ & QR</span>
            </a>

            <a href="orders.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-receipt"></i>
                <span>ดูบิลทั้งหมด</span>
            </a>

            <a href="staff.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>จัดการพนักงาน</span>
            </a>

            <a href="settings.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>ตั้งค่าระบบ</span>
            </a>

            <hr style="border-color: rgba(255,255,255,0.1);">

            <a href="../logout.php" class="menu-item text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>ออกจากระบบ</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <button class="btn mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0 d-inline-block"><?php echo $page_title ?? 'Dashboard'; ?></h5>
            </div>

            <div class="user-info">
                <div>
                    <small class="text-muted d-block">ผู้ดูแลระบบ</small>
                    <strong><?php echo clean($_SESSION['fullname']); ?></strong>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(mb_substr($_SESSION['fullname'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
