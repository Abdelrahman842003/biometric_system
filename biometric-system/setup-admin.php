<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Admin.php';

echo "🔐 Setting up default admin account...\n";

try {
    $db = Database::getInstance()->getConnection();
    $adminModel = new Admin();
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE username = :username");
    $stmt->execute([':username' => 'admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        // Update existing admin password
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admins SET password_hash = :password WHERE username = :username");
        $result = $stmt->execute([
            ':password' => $newPassword,
            ':username' => 'admin'
        ]);
        
        if ($result) {
            echo "✅ Default admin password updated!\n";
        } else {
            echo "❌ Failed to update admin password\n";
        }
    } else {
        // Create new admin
        $result = $adminModel->createAdmin([
            'username' => 'admin',
            'password' => 'admin123',
            'full_name' => 'مدير النظام',
            'email' => 'admin@biometric.local',
            'is_active' => 1
        ]);
        
        if ($result) {
            echo "✅ Default admin account created!\n";
        } else {
            echo "❌ Failed to create admin account\n";
        }
    }
    
    echo "\n📋 Login credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "\n🌐 Access the system at: http://localhost:8001/\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
