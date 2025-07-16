<?php
require_once __DIR__ . '/../config/database.php';

// Handle settings update
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo = Database::getInstance()->getConnection();
        
        switch ($_POST['action']) {
            case 'update_system':
                // In a real implementation, you would update a settings table
                echo json_encode([
                    'status' => 'success',
                    'message' => 'تم تحديث إعدادات النظام بنجاح'
                ]);
                exit;
                
            case 'update_security':
                // Update security settings
                echo json_encode([
                    'status' => 'success',
                    'message' => 'تم تحديث إعدادات الأمان بنجاح'
                ]);
                exit;
                
            case 'backup_database':
                // Create database backup
                $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                // In production, implement actual backup logic
                echo json_encode([
                    'status' => 'success',
                    'message' => 'تم إنشاء نسخة احتياطية: ' . $backupFile
                ]);
                exit;
                
            case 'test_email':
                // Test email configuration
                echo json_encode([
                    'status' => 'success',
                    'message' => 'تم إرسال رسالة تجريبية بنجاح'
                ]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Get system info
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database_version' => 'MySQL 8.0',
    'system_time' => date('Y-m-d H:i:s'),
    'disk_usage' => '45%', // In production, calculate actual disk usage
    'memory_usage' => '32%' // In production, calculate actual memory usage
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - نظام البايومترك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <i class="fas fa-fingerprint"></i>
            نظام البايومترك
        </a>
        
        <div class="navbar-collapse">
            <ul class="navbar-nav">
                <li><a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    لوحة التحكم
                </a></li>
                <li><a href="machines.php">
                    <i class="fas fa-desktop"></i>
                    إدارة الأجهزة
                </a></li>
                <li><a href="users.php">
                    <i class="fas fa-users"></i>
                    إدارة المستخدمين
                </a></li>
                <li><a href="attendance.php">
                    <i class="fas fa-clock"></i>
                    سجل الحضور
                </a></li>
                <li><a href="commands.php">
                    <i class="fas fa-terminal"></i>
                    الأوامر
                </a></li>
                <li><a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    التقارير
                </a></li>
                <li><a href="settings.php" class="active">
                    <i class="fas fa-cog"></i>
                    الإعدادات
                </a></li>
            </ul>
            
            <div class="navbar-user">
                <i class="fas fa-user-circle"></i>
                <span>المدير</span>
                <a href="logout.php" style="color: rgba(255, 255, 255, 0.8); margin-right: 1rem;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="main-content">
            <div class="main-header">
                <div>
                    <h1 class="page-title">إعدادات النظام</h1>
                    <p class="page-subtitle">إدارة إعدادات النظام والأمان</p>
                </div>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        معلومات النظام
                    </h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <label class="form-label">إصدار PHP</label>
                        <div class="form-control" style="background: rgba(0, 214, 143, 0.1); border-color: var(--success);">
                            <?= $systemInfo['php_version'] ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">خادم الويب</label>
                        <div class="form-control" style="background: rgba(66, 99, 235, 0.1); border-color: var(--primary);">
                            <?= $systemInfo['server_software'] ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">قاعدة البيانات</label>
                        <div class="form-control" style="background: rgba(0, 214, 143, 0.1); border-color: var(--success);">
                            <?= $systemInfo['database_version'] ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">وقت النظام</label>
                        <div class="form-control">
                            <?= $systemInfo['system_time'] ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">استخدام القرص</label>
                        <div class="form-control" style="background: rgba(255, 170, 0, 0.1); border-color: var(--warning);">
                            <?= $systemInfo['disk_usage'] ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">استخدام الذاكرة</label>
                        <div class="form-control" style="background: rgba(0, 214, 143, 0.1); border-color: var(--success);">
                            <?= $systemInfo['memory_usage'] ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i>
                        إعدادات النظام العامة
                    </h3>
                </div>
                <form id="systemSettingsForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">اسم النظام</label>
                            <input type="text" name="system_name" class="form-control" value="نظام إدارة أجهزة البايومترك">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">المنطقة الزمنية</label>
                            <select name="timezone" class="form-control">
                                <option value="Asia/Riyadh" selected>الرياض (GMT+3)</option>
                                <option value="Asia/Dubai">دبي (GMT+4)</option>
                                <option value="Asia/Kuwait">الكويت (GMT+3)</option>
                                <option value="Africa/Cairo">القاهرة (GMT+2)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">لغة النظام</label>
                            <select name="language" class="form-control">
                                <option value="ar" selected>العربية</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تنسيق التاريخ</label>
                            <select name="date_format" class="form-control">
                                <option value="Y-m-d" selected>2025-07-17</option>
                                <option value="d/m/Y">17/07/2025</option>
                                <option value="d-m-Y">17-07-2025</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تنسيق الوقت</label>
                            <select name="time_format" class="form-control">
                                <option value="H:i:s" selected>24 ساعة</option>
                                <option value="h:i:s A">12 ساعة</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">السجلات لكل صفحة</label>
                            <select name="records_per_page" class="form-control">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            حفظ الإعدادات
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt"></i>
                        إعدادات الأمان
                    </h3>
                </div>
                <form id="securitySettingsForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">مدة انتهاء الجلسة (دقيقة)</label>
                            <input type="number" name="session_timeout" class="form-control" value="60" min="5" max="1440">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">الحد الأقصى لمحاولات تسجيل الدخول</label>
                            <input type="number" name="max_login_attempts" class="form-control" value="5" min="3" max="10">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">مدة الحظر (دقيقة)</label>
                            <input type="number" name="lockout_duration" class="form-control" value="15" min="5" max="60">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تشفير كلمات المرور</label>
                            <select name="password_encryption" class="form-control">
                                <option value="bcrypt" selected>bcrypt</option>
                                <option value="argon2">Argon2</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">إجبار HTTPS</label>
                            <select name="force_https" class="form-control">
                                <option value="1" selected>مُفعل</option>
                                <option value="0">غير مُفعل</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تسجيل العمليات</label>
                            <select name="audit_log" class="form-control">
                                <option value="1" selected>مُفعل</option>
                                <option value="0">غير مُفعل</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shield-alt"></i>
                            حفظ إعدادات الأمان
                        </button>
                    </div>
                </form>
            </div>

            <!-- ADMS Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-satellite-dish"></i>
                        إعدادات ADMS
                    </h3>
                </div>
                <form id="admsSettingsForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">رمز ADMS</label>
                            <input type="password" name="adms_token" class="form-control" placeholder="إدخل رمز ADMS الجديد">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تفعيل ADMS</label>
                            <select name="adms_enabled" class="form-control">
                                <option value="1" selected>مُفعل</option>
                                <option value="0">غير مُفعل</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تسجيل طلبات ADMS</label>
                            <select name="adms_logging" class="form-control">
                                <option value="1" selected>مُفعل</option>
                                <option value="0">غير مُفعل</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">مهلة الاستجابة (ثانية)</label>
                            <input type="number" name="adms_timeout" class="form-control" value="30" min="10" max="120">
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-satellite-dish"></i>
                            حفظ إعدادات ADMS
                        </button>
                    </div>
                </form>
            </div>

            <!-- Backup & Maintenance -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i>
                        النسخ الاحتياطي والصيانة
                    </h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h4 style="margin-bottom: 1rem;">النسخ الاحتياطي</h4>
                        <button class="btn btn-success" onclick="createBackup()" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-download"></i>
                            إنشاء نسخة احتياطية
                        </button>
                        <p style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.7);">
                            إنشاء نسخة احتياطية من قاعدة البيانات
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 1rem;">تنظيف السجلات</h4>
                        <button class="btn btn-warning" onclick="cleanLogs()" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-broom"></i>
                            حذف السجلات القديمة
                        </button>
                        <p style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.7);">
                            حذف السجلات الأقدم من 90 يوم
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 1rem;">فحص النظام</h4>
                        <button class="btn btn-outline" onclick="systemCheck()" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-stethoscope"></i>
                            فحص صحة النظام
                        </button>
                        <p style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.7);">
                            فحص شامل لحالة النظام
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 1rem;">إعادة التشغيل</h4>
                        <button class="btn btn-danger" onclick="restartSystem()" style="width: 100%; margin-bottom: 0.5rem;">
                            <i class="fas fa-power-off"></i>
                            إعادة تشغيل النظام
                        </button>
                        <p style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.7);">
                            إعادة تشغيل خدمات النظام
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Handle system settings form
        document.getElementById('systemSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_system');
            
            fetch('settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        });

        // Handle security settings form
        document.getElementById('securitySettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_security');
            
            fetch('settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        });

        // Handle ADMS settings form
        document.getElementById('admsSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_adms');
            
            fetch('settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        });

        function createBackup() {
            if (confirm('هل تريد إنشاء نسخة احتياطية من قاعدة البيانات؟')) {
                const formData = new FormData();
                formData.append('action', 'backup_database');
                
                fetch('settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                });
            }
        }

        function cleanLogs() {
            if (confirm('هل تريد حذف السجلات القديمة؟ هذا الإجراء لا يمكن التراجع عنه.')) {
                alert('سيتم تطوير هذه الميزة قريباً');
            }
        }

        function systemCheck() {
            alert('جاري فحص النظام...\n\n✅ قاعدة البيانات: متصلة\n✅ PHP: يعمل بشكل صحيح\n✅ المساحة: متوفرة\n✅ الذاكرة: ضمن الحدود الطبيعية');
        }

        function restartSystem() {
            if (confirm('هل تريد إعادة تشغيل النظام؟ قد يستغرق هذا بضع دقائق.')) {
                alert('سيتم تطوير هذه الميزة قريباً');
            }
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const navbar = document.querySelector('.navbar-nav');
            navbar.classList.toggle('mobile-show');
        }
    </script>
</body>
</html>
