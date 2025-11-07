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

    // สร้างบัญชีใหม่ (สถานะ inactive รอ Admin อนุมัติ)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, password, fullname, role, status)
              VALUES ('$username', '$hashed_password', '$fullname', 'staff', 'inactive')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'ลงทะเบียนสำเร็จ! รอผู้ดูแลระบบอนุมัติ']);
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

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts - Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .register-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .register-icon i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 20px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .form-control-icon {
            padding-left: 45px;
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            background: #e2e8f0;
            transition: all 0.3s;
        }

        .password-strength.weak {
            width: 33.33%;
            background: #ef4444;
        }

        .password-strength.medium {
            width: 66.66%;
            background: #f59e0b;
        }

        .password-strength.strong {
            width: 100%;
            background: #10b981;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            color: #64748b;
        }

        .feature-list li i {
            color: #667eea;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="register-card">
                    <div class="register-header">
                        <div class="register-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="mb-0 fw-bold">สมัครเข้าร่วมทีม</h2>
                        <p class="mb-0 mt-2">Restaurant QR - ระบบจัดการร้านอาหาร</p>
                    </div>

                    <div class="row g-0">
                        <!-- ฟอร์มลงทะเบียน -->
                        <div class="col-lg-7 p-4">
                            <h5 class="mb-4 fw-semibold text-gray-700">
                                <i class="fas fa-edit me-2 text-primary"></i>กรอกข้อมูลสมัครงาน
                            </h5>

                            <form id="registerForm">
                                <div class="mb-3 position-relative">
                                    <i class="fas fa-user input-icon"></i>
                                    <input
                                        type="text"
                                        class="form-control form-control-icon"
                                        name="username"
                                        id="username"
                                        placeholder="ชื่อผู้ใช้ (อย่างน้อย 4 ตัวอักษร)"
                                        required
                                        autocomplete="username"
                                    >
                                    <small class="text-muted">ใช้สำหรับเข้าสู่ระบบ</small>
                                </div>

                                <div class="mb-3 position-relative">
                                    <i class="fas fa-id-card input-icon"></i>
                                    <input
                                        type="text"
                                        class="form-control form-control-icon"
                                        name="fullname"
                                        id="fullname"
                                        placeholder="ชื่อ-นามสกุล (ภาษาไทย)"
                                        required
                                        autocomplete="name"
                                    >
                                </div>

                                <div class="mb-3 position-relative">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input
                                        type="password"
                                        class="form-control form-control-icon"
                                        name="password"
                                        id="password"
                                        placeholder="รหัสผ่าน (อย่างน้อย 6 ตัวอักษร)"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <div id="passwordStrength" class="password-strength"></div>
                                </div>

                                <div class="mb-3 position-relative">
                                    <i class="fas fa-check-circle input-icon"></i>
                                    <input
                                        type="password"
                                        class="form-control form-control-icon"
                                        name="confirm_password"
                                        id="confirm_password"
                                        placeholder="ยืนยันรหัสผ่านอีกครั้ง"
                                        required
                                        autocomplete="new-password"
                                    >
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                                    <label class="form-check-label" for="acceptTerms">
                                        ยอมรับ<a href="#" class="text-primary">เงื่อนไขการใช้งาน</a>และ<a href="#" class="text-primary">นโยบายความเป็นส่วนตัว</a>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-register w-100 mt-3">
                                    <i class="fas fa-user-plus me-2"></i>สมัครสมาชิก
                                </button>
                            </form>

                            <div class="text-center mt-4 pt-3 border-top">
                                <p class="mb-0">
                                    มีบัญชีอยู่แล้ว?
                                    <a href="login.php" class="text-primary fw-semibold">เข้าสู่ระบบ</a>
                                </p>
                            </div>
                        </div>

                        <!-- ข้อมูลเพิ่มเติม -->
                        <div class="col-lg-5 bg-light p-4">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-info-circle me-2 text-primary"></i>ข้อมูลสำคัญ
                            </h6>

                            <ul class="feature-list">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    บัญชีจะอยู่ในสถานะรอการอนุมัติ
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    ผู้ดูแลระบบจะตรวจสอบข้อมูล
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    เมื่ออนุมัติแล้วจะสามารถใช้งานได้
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    ข้อมูลของคุณจะถูกเก็บเป็นความลับ
                                </li>
                            </ul>

                            <div class="alert alert-info border-0 mt-4">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>เคล็ดลับ:</strong>
                                <ul class="mt-2 mb-0 ps-3">
                                    <li>ใช้ชื่อผู้ใช้ที่จำง่าย</li>
                                    <li>ตั้งรหัสผ่านที่ปลอดภัย</li>
                                    <li>ใส่ชื่อจริงเพื่อให้ตรวจสอบได้</li>
                                </ul>
                            </div>

                            <div class="mt-4 p-3 bg-white rounded">
                                <h6 class="fw-semibold mb-2">
                                    <i class="fas fa-shield-alt me-2 text-success"></i>ความปลอดภัย
                                </h6>
                                <small class="text-muted">
                                    ข้อมูลของคุณได้รับการเข้ารหัสและปกป้องด้วยมาตรฐานความปลอดภัยสูงสุด
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="fas fa-shield-alt me-1"></i>
                        Powered by Restaurant QR System v1.0
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password Strength Indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');

            if (password.length === 0) {
                strengthBar.className = 'password-strength';
            } else if (password.length < 6) {
                strengthBar.className = 'password-strength weak';
            } else if (password.length < 10) {
                strengthBar.className = 'password-strength medium';
            } else {
                strengthBar.className = 'password-strength strong';
            }
        });

        // Form Submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // ตรวจสอบรหัสผ่านตรงกัน
            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'รหัสผ่านไม่ตรงกัน',
                    text: 'กรุณาตรวจสอบรหัสผ่านอีกครั้ง',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const buttonText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังลงทะเบียน...';

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ลงทะเบียนสำเร็จ!',
                        html: `
                            <p class="mb-3">${result.message}</p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                คุณจะได้รับการแจ้งเตือนเมื่อบัญชีได้รับการอนุมัติ
                            </div>
                        `,
                        confirmButtonColor: '#667eea',
                        confirmButtonText: 'ไปหน้าเข้าสู่ระบบ'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ลงทะเบียนไม่สำเร็จ',
                        text: result.message,
                        confirmButtonColor: '#667eea'
                    });

                    button.disabled = false;
                    button.innerHTML = buttonText;
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์',
                    confirmButtonColor: '#667eea'
                });

                button.disabled = false;
                button.innerHTML = buttonText;
            }
        });

        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
        });
    </script>
</body>
</html>
