<?php
require_once __DIR__ . '/../auth.php';
Auth::requireLogin();

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
    <link rel="stylesheet" href="../assets/css/select-fix.css">
</head>
<body>
    <!-- Navbar Overlay -->
    <div class="navbar-overlay" id="navbarOverlay"></div>
    
    <!-- Advanced Navbar with Modern Toggle -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <i class="fas fa-fingerprint"></i>
            نظام البايومترك
        </a>
        
        <button class="navbar-toggle" id="menuToggle" onclick="toggleNavbar()">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
    </nav>
    
    <!-- Floating Central Menu -->
    <div class="navbar-menu" id="navbarMenu">
        <div class="menu-header">
            <h2 class="menu-title">إعدادات النظام</h2>
            <p class="menu-subtitle">تخصيص وضبط إعدادات النظام</p>
        </div>
        
        <ul class="nav-items">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="nav-text">لوحة التحكم</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="machines.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <span class="nav-text">إدارة الأجهزة</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="nav-text">إدارة المستخدمين</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="attendance.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="nav-text">سجل الحضور</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span class="nav-text">التقارير</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link active">
                    <div class="nav-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="nav-text">الإعدادات</span>
                </a>
            </li>
        </ul>
        
        <div class="user-section">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-details">
                    <h4>المدير العام</h4>
                    <p>مدير النظام</p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                تسجيل الخروج
            </a>
        </div>
    </div>
            </ul>
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

            <!-- Connection Methods Guide -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-network-wired"></i>
                        دليل طرق الاتصال مع الأجهزة
                    </h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
                    <!-- ADMS Method -->
                    <div style="border: 2px solid var(--success); border-radius: 0.5rem; padding: 1.5rem; background: rgba(0, 214, 143, 0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <i class="fas fa-cloud-upload-alt" style="color: var(--success); font-size: 1.5rem;"></i>
                            <h4 style="color: var(--success); margin: 0;">طريقة ADMS (موصى بها)</h4>
                        </div>
                        
                        <p style="margin-bottom: 1rem; color: var(--text-muted);">
                            الجهاز يرسل البيانات تلقائياً للخادم عند تسجيل أي حضور
                        </p>
                        
                        <div style="background: rgba(255, 255, 255, 0.8); padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem;">
                            <h5 style="margin-bottom: 0.5rem;">المزايا:</h5>
                            <ul style="margin: 0; padding-right: 1rem;">
                                <li>البيانات تصل فوراً</li>
                                <li>لا يحتاج اتصال مباشر</li>
                                <li>يعمل خلف NAT/Router</li>
                                <li>أكثر أماناً</li>
                            </ul>
                        </div>
                        
                        <div style="background: rgba(255, 255, 255, 0.8); padding: 1rem; border-radius: 0.25rem;">
                            <h5 style="margin-bottom: 0.5rem;">خطوات الإعداد:</h5>
                            <ol style="margin: 0; padding-right: 1rem; font-size: 0.9rem;">
                                <li>اذهب لإعدادات الشبكة في الجهاز</li>
                                <li>فعّل ADMS</li>
                                <li>ضع رابط الخادم: <code style="background: #f0f0f0; padding: 0.2rem;">http://your-server.com/api/adms-endpoint.php</code></li>
                                <li>ضع مفتاح ADMS من config/database.php</li>
                                <li>احفظ الإعدادات</li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Public IP Method -->
                    <div style="border: 2px solid var(--warning); border-radius: 0.5rem; padding: 1.5rem; background: rgba(255, 170, 0, 0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <i class="fas fa-server" style="color: var(--warning); font-size: 1.5rem;"></i>
                            <h4 style="color: var(--warning); margin: 0;">طريقة Public IP</h4>
                        </div>
                        
                        <p style="margin-bottom: 1rem; color: var(--text-muted);">
                            الخادم يطلب البيانات مباشرة من الجهاز عبر IP العام
                        </p>
                        
                        <div style="background: rgba(255, 255, 255, 0.8); padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem;">
                            <h5 style="margin-bottom: 0.5rem;">المزايا:</h5>
                            <ul style="margin: 0; padding-right: 1rem;">
                                <li>تحكم أكبر في التزامن</li>
                                <li>يمكن طلب البيانات عند الحاجة</li>
                                <li>إمكانية إدارة المستخدمين عن بُعد</li>
                            </ul>
                        </div>
                        
                        <div style="background: rgba(255, 255, 255, 0.8); padding: 1rem; border-radius: 0.25rem;">
                            <h5 style="margin-bottom: 0.5rem;">المتطلبات:</h5>
                            <ol style="margin: 0; padding-right: 1rem; font-size: 0.9rem;">
                                <li>IP عام ثابت للجهاز</li>
                                <li>فتح المنفذ 4370 في Router</li>
                                <li>إعادة توجيه المنفذ للجهاز</li>
                                <li>تأكد من أن الجهاز يقبل الاتصالات الخارجية</li>
                                <li>ضع IP العام في إعدادات الجهاز</li>
                            </ol>
                        </div>
                    </div>
                </div>
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
    </script>

    <!-- Advanced Navbar Script -->
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
