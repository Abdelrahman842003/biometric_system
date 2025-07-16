<?php
/**
 * Database Schema Installer
 * هذا الملف ينشئ قاعدة البيانات والجداول تلقائياً
 */

// تضمين إعدادات قاعدة البيانات
require_once 'config/database.php';

class SchemaInstaller {
    private $pdo;
    
    public function __construct() {
        echo "🚀 بدء تطبيق schema قاعدة البيانات...\n\n";
    }
    
    public function install() {
        try {
            // الاتصال بـ MySQL بدون تحديد قاعدة البيانات
            $this->connectToMySQL();
            
            // إنشاء قاعدة البيانات
            $this->createDatabase();
            
            // إنشاء الجداول
            $this->createTables();
            
            // إدخال البيانات الأساسية
            $this->insertDefaultData();
            
            echo "✅ تم تطبيق schema بنجاح!\n";
            echo "📋 معلومات قاعدة البيانات:\n";
            echo "   - اسم قاعدة البيانات: " . DB_NAME . "\n";
            echo "   - خادم قاعدة البيانات: " . DB_HOST . "\n";
            echo "   - اسم المستخدم: " . DB_USER . "\n\n";
            echo "🔑 بيانات الدخول الافتراضية:\n";
            echo "   - اسم المستخدم: admin\n";
            echo "   - كلمة المرور: password\n\n";
            echo "🎯 يمكنك الآن الدخول إلى النظام من:\n";
            echo "   " . "http://localhost:8000/\n\n";
            
        } catch (Exception $e) {
            echo "❌ خطأ في تطبيق schema: " . $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }
    
    private function connectToMySQL() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            echo "✅ تم الاتصال بخادم MySQL بنجاح\n";
        } catch (PDOException $e) {
            throw new Exception("فشل الاتصال بخادم MySQL: " . $e->getMessage());
        }
    }
    
    private function createDatabase() {
        try {
            $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $this->pdo->exec($sql);
            
            // التبديل لقاعدة البيانات الجديدة
            $this->pdo->exec("USE " . DB_NAME);
            echo "✅ تم إنشاء قاعدة البيانات: " . DB_NAME . "\n";
        } catch (PDOException $e) {
            throw new Exception("فشل إنشاء قاعدة البيانات: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $tables = [
            'admins' => "
                CREATE TABLE IF NOT EXISTS admins (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    email VARCHAR(100),
                    full_name VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_login TIMESTAMP NULL,
                    is_active TINYINT(1) DEFAULT 1
                )
            ",
            
            'machines' => "
                CREATE TABLE IF NOT EXISTS machines (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    location VARCHAR(200),
                    ip_address VARCHAR(45) NOT NULL,
                    serial_number VARCHAR(100),
                    port INT DEFAULT 4370,
                    adms_enabled TINYINT(1) DEFAULT 1,
                    adms_key VARCHAR(255),
                    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
                    model VARCHAR(100),
                    firmware_version VARCHAR(50),
                    users_count INT DEFAULT 0,
                    last_sync TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_ip_port (ip_address, port),
                    INDEX idx_status (status)
                )
            ",
            
            'users' => "
                CREATE TABLE IF NOT EXISTS users (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id VARCHAR(20) UNIQUE NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    department VARCHAR(100),
                    position VARCHAR(100),
                    card_number VARCHAR(50),
                    fingerprint_template LONGTEXT,
                    face_template LONGTEXT,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (is_active)
                )
            ",
            
            'user_machines' => "
                CREATE TABLE IF NOT EXISTS user_machines (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT,
                    machine_id INT,
                    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_user_machine (user_id, machine_id)
                )
            ",
            
            'attendance_logs' => "
                CREATE TABLE IF NOT EXISTS attendance_logs (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    machine_id INT,
                    user_id VARCHAR(20) NOT NULL,
                    log_time TIMESTAMP NOT NULL,
                    log_type ENUM('check_in', 'check_out', 'break_in', 'break_out') DEFAULT 'check_in',
                    verify_type ENUM('fingerprint', 'face', 'card', 'password') DEFAULT 'fingerprint',
                    temperature DECIMAL(4,1) NULL,
                    mask_status TINYINT(1) NULL,
                    raw_data TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
                    INDEX idx_machine_time (machine_id, log_time),
                    INDEX idx_user_time (user_id, log_time),
                    INDEX idx_log_type (log_type)
                )
            ",
            
            'commands' => "
                CREATE TABLE IF NOT EXISTS commands (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    machine_id INT,
                    command_type ENUM('reboot', 'sync_time', 'clear_logs', 'add_user', 'delete_user', 'update_user', 'get_info') NOT NULL,
                    command_data JSON,
                    status ENUM('pending', 'sent', 'completed', 'failed') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    sent_at TIMESTAMP NULL,
                    completed_at TIMESTAMP NULL,
                    error_message TEXT,
                    result_data JSON,
                    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
                    INDEX idx_machine_status (machine_id, status),
                    INDEX idx_status_created (status, created_at)
                )
            ",
            
            'system_logs' => "
                CREATE TABLE IF NOT EXISTS system_logs (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    admin_id INT,
                    action VARCHAR(100) NOT NULL,
                    target_type ENUM('machine', 'user', 'admin', 'system') NOT NULL,
                    target_id INT,
                    details JSON,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
                    INDEX idx_admin_action (admin_id, action),
                    INDEX idx_target (target_type, target_id),
                    INDEX idx_created (created_at)
                )
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $this->pdo->exec($sql);
                echo "✅ تم إنشاء جدول: $tableName\n";
            } catch (PDOException $e) {
                throw new Exception("فشل إنشاء جدول $tableName: " . $e->getMessage());
            }
        }
    }
    
    private function insertDefaultData() {
        try {
            // فحص إذا كان المشرف الافتراضي موجود
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
            $stmt->execute();
            $adminExists = $stmt->fetchColumn() > 0;
            
            if (!$adminExists) {
                // إدخال المشرف الافتراضي
                $sql = "INSERT INTO admins (username, password_hash, email, full_name) 
                        VALUES ('admin', :password_hash, 'admin@biometric.local', 'System Administrator')";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'password_hash' => password_hash('password', PASSWORD_DEFAULT)
                ]);
                echo "✅ تم إنشاء المشرف الافتراضي\n";
            } else {
                echo "ℹ️  المشرف الافتراضي موجود مسبقاً\n";
            }
            
            // فحص إذا كان الجهاز النموذجي موجود
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM machines WHERE ip_address = '192.168.1.100'");
            $stmt->execute();
            $machineExists = $stmt->fetchColumn() > 0;
            
            if (!$machineExists) {
                // إدخال جهاز نموذجي
                $sql = "INSERT INTO machines (name, location, ip_address, serial_number, adms_key) 
                        VALUES ('Main Entrance', 'Building A - Ground Floor', '192.168.1.100', 'ZK001234567890', 'sample-adms-key-123')";
                $this->pdo->exec($sql);
                echo "✅ تم إنشاء جهاز نموذجي\n";
            } else {
                echo "ℹ️  الجهاز النموذجي موجود مسبقاً\n";
            }
            
            // إدخال بعض المستخدمين النموذجيين
            $sampleUsers = [
                ['001', 'أحمد محمد', 'تقنية المعلومات', 'مطور'],
                ['002', 'فاطمة علي', 'الموارد البشرية', 'محاسبة'],
                ['003', 'محمد سالم', 'الأمن', 'حارس أمن']
            ];
            
            foreach ($sampleUsers as $user) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
                $stmt->execute([$user[0]]);
                
                if ($stmt->fetchColumn() == 0) {
                    $sql = "INSERT INTO users (user_id, name, department, position) VALUES (?, ?, ?, ?)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($user);
                }
            }
            echo "✅ تم إنشاء المستخدمين النموذجيين\n";
            
        } catch (PDOException $e) {
            throw new Exception("فشل إدخال البيانات الافتراضية: " . $e->getMessage());
        }
    }
    
    public function checkInstallation() {
        try {
            $db = Database::getInstance()->getConnection();
            
            // فحص الجداول
            $tables = ['admins', 'machines', 'users', 'user_machines', 'attendance_logs', 'commands', 'system_logs'];
            $existingTables = [];
            
            foreach ($tables as $table) {
                $stmt = $db->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->fetch()) {
                    $existingTables[] = $table;
                }
            }
            
            return [
                'database_exists' => true,
                'tables_count' => count($existingTables),
                'expected_tables' => count($tables),
                'missing_tables' => array_diff($tables, $existingTables),
                'existing_tables' => $existingTables
            ];
            
        } catch (Exception $e) {
            return [
                'database_exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// تشغيل المثبت
if (php_sapi_name() === 'cli') {
    // تشغيل من سطر الأوامر
    $installer = new SchemaInstaller();
    $installer->install();
} elseif (isset($_GET['install'])) {
    // تشغيل من المتصفح
    header('Content-Type: text/plain; charset=utf-8');
    $installer = new SchemaInstaller();
    $installer->install();
} elseif (isset($_GET['check'])) {
    // فحص حالة التثبيت
    header('Content-Type: application/json');
    $installer = new SchemaInstaller();
    $status = $installer->checkInstallation();
    echo json_encode($status, JSON_UNESCAPED_UNICODE);
} else {
    // عرض واجهة التثبيت
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تثبيت قاعدة البيانات</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            h1 { color: #333; text-align: center; }
            .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px; }
            .btn:hover { background: #005a87; }
            .status { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
            .success { background: #d4edda; color: #155724; }
            .error { background: #f8d7da; color: #721c24; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🗄️ تثبيت قاعدة بيانات النظام</h1>
            
            <div class="status">
                <h3>معلومات الاتصال:</h3>
                <p><strong>خادم قاعدة البيانات:</strong> <?= DB_HOST ?></p>
                <p><strong>اسم قاعدة البيانات:</strong> <?= DB_NAME ?></p>
                <p><strong>اسم المستخدم:</strong> <?= DB_USER ?></p>
            </div>
            
            <div id="status"></div>
            
            <div style="text-align: center;">
                <button class="btn" onclick="checkInstallation()">فحص حالة التثبيت</button>
                <button class="btn" onclick="installDatabase()">تثبيت قاعدة البيانات</button>
            </div>
            
            <div id="output"></div>
        </div>
        
        <script>
            function checkInstallation() {
                document.getElementById('status').innerHTML = '<p>جاري فحص حالة التثبيت...</p>';
                
                fetch('?check=1')
                    .then(response => response.json())
                    .then(data => {
                        let html = '<div class="status">';
                        if (data.database_exists) {
                            html += '<h3 class="success">✅ قاعدة البيانات موجودة</h3>';
                            html += '<p><strong>الجداول الموجودة:</strong> ' + data.tables_count + ' من أصل ' + data.expected_tables + '</p>';
                            
                            if (data.missing_tables.length > 0) {
                                html += '<p class="error"><strong>الجداول المفقودة:</strong> ' + data.missing_tables.join(', ') + '</p>';
                            } else {
                                html += '<p class="success">✅ جميع الجداول موجودة</p>';
                            }
                        } else {
                            html += '<p class="error">❌ قاعدة البيانات غير موجودة أو هناك مشكلة في الاتصال</p>';
                            if (data.error) {
                                html += '<p class="error">خطأ: ' + data.error + '</p>';
                            }
                        }
                        html += '</div>';
                        document.getElementById('status').innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById('status').innerHTML = '<p class="error">خطأ في فحص التثبيت: ' + error + '</p>';
                    });
            }
            
            function installDatabase() {
                document.getElementById('output').innerHTML = '<p>جاري تثبيت قاعدة البيانات...</p>';
                
                fetch('?install=1')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('output').innerHTML = '<pre>' + data + '</pre>';
                        checkInstallation();
                    })
                    .catch(error => {
                        document.getElementById('output').innerHTML = '<p class="error">خطأ في التثبيت: ' + error + '</p>';
                    });
            }
            
            // فحص تلقائي عند تحميل الصفحة
            checkInstallation();
        </script>
    </body>
    </html>
    <?php
}
?>
