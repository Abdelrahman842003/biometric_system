<?php
require_once __DIR__ . '/auth.php';

// If already logged in, redirect to dashboard
if (Auth::isLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle logout message
if (isset($_GET['logout'])) {
    $success = 'تم تسجيل الخروج بنجاح';
}

// Handle timeout message
if (isset($_GET['timeout'])) {
    $error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        if (Auth::login($username, $password)) {
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام البايومترك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
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
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header .logo {
            background: linear-gradient(135deg, #667eea, #764ba2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
        }

        .login-header h1 {
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .login-header p {
            color: #7f8c8d;
            margin: 0;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: right;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        .form-control:focus {
            outline: none !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            font-family: 'Tajawal', sans-serif;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        .footer-text {
            margin-top: 2rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .default-credentials {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #495057;
        }

        .default-credentials h4 {
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .default-credentials p {
            margin: 0.25rem 0;
        }

        .default-credentials strong {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-fingerprint"></i>
            </div>
            <h1>نظام البايومترك</h1>
            <p>تسجيل دخول المدير</p>
        </div>

        <div class="default-credentials">
            <h4>المعلومات الافتراضية:</h4>
            <p><strong>المستخدم:</strong> admin</p>
            <p><strong>كلمة المرور:</strong> admin123</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">اسم المستخدم</label>
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       name="username" 
                       placeholder="أدخل اسم المستخدم" 
                       required 
                       autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">كلمة المرور</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="أدخل كلمة المرور" 
                       required 
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                تسجيل الدخول
            </button>
        </form>

        <div class="footer-text">
            <p>&copy; 2025 نظام البايومترك. جميع الحقوق محفوظة.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on username field
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            // Make sure fields are editable
            usernameField.removeAttribute('readonly');
            usernameField.removeAttribute('disabled');
            passwordField.removeAttribute('readonly');
            passwordField.removeAttribute('disabled');
            
            // Focus on username
            usernameField.focus();
            
            // Test password field
            passwordField.addEventListener('click', function() {
                console.log('Password field clicked');
                this.focus();
            });
            
            passwordField.addEventListener('focus', function() {
                console.log('Password field focused');
            });
            
            passwordField.addEventListener('input', function() {
                console.log('Password field input:', this.value.length, 'characters');
            });
        });

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-login');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحقق...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
