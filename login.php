<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// ถ้าล็อกอินอยู่แล้วให้ redirect
if (isLoggedIn()) {
    $redirect_url = isAdmin() ? 'admin/index.php' : 'staff/index.php';
    redirect($redirect_url);
}

// ประมวลผลการล็อกอิน
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' AND status = 'active' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['user_role'] = $user['role'];

            $redirect_url = $user['role'] === 'admin' ? 'admin/index.php' : 'staff/index.php';

            echo json_encode(['success' => true, 'redirect' => $redirect_url]);
            exit();
        }
    }

    echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Restaurant QR</title>

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

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .login-icon {
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

        .login-icon i {
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

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .wave {
            animation: wave 3s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-20deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h2 class="mb-0 fw-bold">Restaurant QR</h2>
                        <p class="mb-0 mt-2">ระบบจัดการร้านอาหาร</p>
                    </div>

                    <div class="p-4">
                        <h5 class="text-center mb-4 fw-semibold text-gray-700">
                            เข้าสู่ระบบ <span class="wave">👋</span>
                        </h5>

                        <form id="loginForm">
                            <div class="mb-3 position-relative">
                                <i class="fas fa-user input-icon"></i>
                                <input
                                    type="text"
                                    class="form-control form-control-icon"
                                    name="username"
                                    placeholder="ชื่อผู้ใช้"
                                    required
                                    autocomplete="username"
                                >
                            </div>

                            <div class="mb-3 position-relative">
                                <i class="fas fa-lock input-icon"></i>
                                <input
                                    type="password"
                                    class="form-control form-control-icon"
                                    name="password"
                                    placeholder="รหัสผ่าน"
                                    required
                                    autocomplete="current-password"
                                >
                            </div>

                            <button type="submit" class="btn btn-login w-100 mt-3">
                                <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                ใช้งานได้เฉพาะแอดมินและพนักงานเท่านั้น
                            </small>
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
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const buttonText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังเข้าสู่ระบบ...';

            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });

                    Toast.fire({
                        icon: 'success',
                        title: 'เข้าสู่ระบบสำเร็จ!'
                    }).then(() => {
                        window.location.href = result.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เข้าสู่ระบบไม่สำเร็จ',
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
    </script>
</body>
</html>
