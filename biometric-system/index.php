<?php
require_once __DIR__ . '/auth.php';

// If admin is logged in, redirect to dashboard
if (Auth::isLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit;
}

// If not logged in, redirect to login page
header('Location: login.php');
exit;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة أجهزة البايومترك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4263EB;
            --secondary: #00D68F;
            --dark: #101426;
            --darker: #070B19;
            --light: #E9ECEF;
            --gray: #6C757D;
            --success: #00D68F;
            --warning: #FFAA00;
            --danger: #FF5B5B;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
            color: white;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(66, 99, 235, 0.1) 0%, transparent 30%),
                radial-gradient(circle at 80% 70%, rgba(0, 214, 143, 0.1) 0%, transparent 30%),
                radial-gradient(circle at 10% 90%, rgba(66, 99, 235, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 10%, rgba(0, 214, 143, 0.15) 0%, transparent 40%);
            z-index: -1;
        }

        .container {
            width: 90%;
            max-width: 500px;
            padding: 0 20px;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .logo::after {
            content: "";
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--secondary);
            right: -15px;
            top: 8px;
        }

        .system-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .description {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .features-list {
            list-style: none;
            margin-bottom: 2rem;
            text-align: right;
        }

        .features-list li {
            padding: 0.5rem 0;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
        }

        .features-list li::before {
            content: "✓";
            color: var(--success);
            font-weight: bold;
            margin-left: 0.5rem;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-direction: column;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #3951d4);
            color: white;
            box-shadow: 0 10px 30px rgba(66, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(66, 99, 235, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .system-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .info-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .status-indicator {
            position: absolute;
            top: 2rem;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(0, 214, 143, 0.2);
            color: var(--success);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            border: 1px solid rgba(0, 214, 143, 0.3);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .footer-note {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-card {
                padding: 2rem 1.5rem;
            }

            .logo {
                font-size: 2rem;
            }

            .system-title {
                font-size: 1.25rem;
            }

            .btn-group {
                gap: 0.75rem;
            }

            .btn {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .status-indicator {
                position: static;
                margin-bottom: 1rem;
                justify-content: center;
            }
        }

        /* Loading animation for buttons */
        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn.loading::after {
            content: "";
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="status-indicator">
        <div class="status-dot"></div>
        النظام جاهز
    </div>

    <div class="container">
        <div class="welcome-card">
            <div class="logo">نظام البايومترك</div>
            <h2 class="system-title">إدارة أجهزة البصمة والوجه</h2>
            
            <p class="description">
                نظام شامل لإدارة أجهزة البايومترك مع دعم ADMS و SDK للتحكم الكامل عن بُعد
            </p>

            <ul class="features-list">
                <li>إدارة الأجهزة والمستخدمين</li>
                <li>استقبال سجلات الحضور تلقائياً</li>
                <li>التحكم عن بُعد عبر WAN</li>
                <li>تقارير وإحصائيات مفصلة</li>
                <li>أمان متقدم مع تشفير</li>
            </ul>

            <div class="btn-group">
                <a href="admin/dashboard.php" class="btn btn-primary" onclick="showLoading(this)" id="dashboardBtn">
                    <i class="fas fa-tachometer-alt"></i>
                    دخول لوحة التحكم
                </a>
                
                <a href="install.php" class="btn btn-secondary" id="installBtn" style="display: none;">
                    <i class="fas fa-database"></i>
                    تثبيت قاعدة البيانات
                </a>
                
                <a href="#" class="btn btn-secondary" onclick="showSystemInfo()">
                    <i class="fas fa-info-circle"></i>
                    معلومات النظام
                </a>
            </div>

            <div class="system-info" id="systemInfo" style="display: none;">
                <h3 style="color: var(--primary); margin-bottom: 1rem; text-align: center;">
                    <i class="fas fa-server"></i>
                    حالة النظام
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="info-label">قاعدة البيانات</div>
                        <div id="dbStatus" style="color: var(--success); font-weight: 600;">متصلة</div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="info-label">الأمان</div>
                        <div style="color: var(--success); font-weight: 600;">مفعل</div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-plug"></i>
                        </div>
                        <div class="info-label">ADMS</div>
                        <div style="color: var(--success); font-weight: 600;">جاهز</div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="info-label">SDK</div>
                        <div style="color: var(--success); font-weight: 600;">مثبت</div>
                    </div>
                </div>
            </div>

            <div class="footer-note">
                تم تطوير النظام وفقاً لأعلى معايير الأمان والأداء
            </div>
        </div>
    </div>

    <script>
        function showLoading(button) {
            button.classList.add('loading');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
        }

        function showSystemInfo() {
            const infoDiv = document.getElementById('systemInfo');
            if (infoDiv.style.display === 'none') {
                infoDiv.style.display = 'block';
                checkSystemStatus();
            } else {
                infoDiv.style.display = 'none';
            }
        }

        function checkSystemStatus() {
            // Check database connection
            fetch('api/system-status.php')
                .then(response => response.json())
                .then(data => {
                    const dbStatus = document.getElementById('dbStatus');
                    const dashboardBtn = document.getElementById('dashboardBtn');
                    const installBtn = document.getElementById('installBtn');
                    
                    if (data.database) {
                        dbStatus.textContent = 'متصلة';
                        dbStatus.style.color = 'var(--success)';
                        dashboardBtn.style.display = 'inline-flex';
                        installBtn.style.display = 'none';
                    } else {
                        dbStatus.textContent = 'غير متصلة - يلزم التثبيت';
                        dbStatus.style.color = 'var(--danger)';
                        dashboardBtn.style.display = 'none';
                        installBtn.style.display = 'inline-flex';
                    }
                })
                .catch(error => {
                    console.log('System status check:', error);
                    // Show install button if there's an error
                    const dashboardBtn = document.getElementById('dashboardBtn');
                    const installBtn = document.getElementById('installBtn');
                    dashboardBtn.style.display = 'none';
                    installBtn.style.display = 'inline-flex';
                });
        }

        // Smooth page transitions
        document.addEventListener('DOMContentLoaded', function() {
            // Add entrance animation delay
            const card = document.querySelector('.welcome-card');
            card.style.animation = 'slideUp 0.8s ease-out 0.3s both';
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target.classList.contains('btn')) {
                    e.target.click();
                }
            }
        });

        // Auto-refresh status indicator
        setInterval(function() {
            const dot = document.querySelector('.status-dot');
            dot.style.animation = 'none';
            setTimeout(() => {
                dot.style.animation = 'pulse 2s infinite';
            }, 10);
        }, 10000);
    </script>
</body>
</html>
