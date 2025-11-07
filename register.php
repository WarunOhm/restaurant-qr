<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// ถ้าล็อกอินอยู่แล้วให้ redirect
if (isLoggedIn()) {
    $redirect_url = isAdmin() ? 'admin/index.php' : 'staff/index.php';
    redirect($redirect_url);
}

// ประมวลผลการลงทะเบียน
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);

    // Validation
    if (empty($username) || empty($password) || empty($fullname)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit();
    }

    if (strlen($username) < 4) {
        echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้ต้องมีอย่างน้อย 4 ตัวอักษร']);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านไม่ตรงกัน']);
        exit();
    }

    // ตรวจสอบ username ซ้ำ
    $check_query = "SELECT id FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว']);
        exit();
    }

    // สร้างบัญชีใหม่ (สถานะ active เข้าใช้งานได้เลย)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, password, fullname, role, status)
              VALUES ('$username', '$hashed_password', '$fullname', 'staff', 'active')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'ลงทะเบียนสำเร็จ! กำลังพาคุณเข้าสู่ระบบ...']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลงทะเบียน']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน - Restaurant QR</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            max-width: 450px;
            width: 100%;
        }

        .register-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .register-header {
            padding: 3rem 2rem 2rem;
            text-align: center;
            background: white;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }

        .logo-circle i {
            font-size: 2rem;
            color: white;
        }

        .register-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .register-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: #3B82F6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .password-strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e2e8f0;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .password-strength-fill.weak {
            width: 33.33%;
            background: #ef4444;
        }

        .password-strength-fill.medium {
            width: 66.66%;
            background: #f59e0b;
        }

        .password-strength-fill.strong {
            width: 100%;
            background: #10b981;
        }

        .strength-text {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            color: #64748b;
        }

        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .login-link {
            text-align: center;
            padding: 1.5rem 2rem 2rem;
            background: #f8fafc;
        }

        .login-link a {
            color: #3B82F6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: #2563EB;
        }

        .feature-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #eff6ff;
            color: #3B82F6;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {
            .register-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .register-header h1 {
                font-size: 1.5rem;
            }

            .register-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo-circle">
                    <i class="fas fa-utensils"></i>
                </div>
                <h1>เริ่มต้นใช้งาน</h1>
                <p>สร้างบัญชีพนักงานใหม่</p>
            </div>

            <div class="register-body">
                <div class="feature-badge">
                    <i class="fas fa-bolt"></i>
                    <span>สมัครแล้วเข้าใช้งานได้ทันที</span>
                </div>

                <form id="registerForm">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user me-1"></i>ชื่อผู้ใช้
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            name="username"
                            id="username"
                            placeholder="อย่างน้อย 4 ตัวอักษร"
                            required
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card me-1"></i>ชื่อ-นามสกุล
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            name="fullname"
                            id="fullname"
                            placeholder="ชื่อจริงของคุณ"
                            required
                            autocomplete="name"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock me-1"></i>รหัสผ่าน
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            name="password"
                            id="password"
                            placeholder="อย่างน้อย 6 ตัวอักษร"
                            required
                            autocomplete="new-password"
                        >
                        <div class="password-strength-bar">
                            <div id="strengthFill" class="password-strength-fill"></div>
                        </div>
                        <div id="strengthText" class="strength-text"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-check-circle me-1"></i>ยืนยันรหัสผ่าน
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="พิมพ์รหัสผ่านอีกครั้ง"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus me-2"></i>สร้างบัญชี
                    </button>
                </form>
            </div>

            <div class="login-link">
                มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

    <script>
        // Password Strength
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const length = password.length;

            strengthFill.className = 'password-strength-fill';

            if (length === 0) {
                strengthText.textContent = '';
            } else if (length < 6) {
                strengthFill.classList.add('weak');
                strengthText.textContent = 'รหัสผ่านอ่อนแอ';
                strengthText.style.color = '#ef4444';
            } else if (length < 10) {
                strengthFill.classList.add('medium');
                strengthText.textContent = 'รหัสผ่านปานกลาง';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthFill.classList.add('strong');
                strengthText.textContent = 'รหัสผ่านแข็งแรง';
                strengthText.style.color = '#10b981';
            }
        });

        // Username validation (lowercase only)
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'รหัสผ่านไม่ตรงกัน',
                    text: 'กรุณาตรวจสอบรหัสผ่านอีกครั้ง',
                    confirmButtonColor: '#3B82F6'
                });
                return;
            }

            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const buttonText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังสร้างบัญชี...';

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: result.message,
                        confirmButtonColor: '#3B82F6',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: result.message,
                        confirmButtonColor: '#3B82F6'
                    });

                    button.disabled = false;
                    button.innerHTML = buttonText;
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์',
                    confirmButtonColor: '#3B82F6'
                });

                button.disabled = false;
                button.innerHTML = buttonText;
            }
        });
    </script>
</body>
</html>
