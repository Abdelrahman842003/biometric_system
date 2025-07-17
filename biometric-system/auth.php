<?php
// Authentication and Session Management
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Admin.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private static $adminModel = null;
    
    private static function getAdminModel() {
        if (self::$adminModel === null) {
            self::$adminModel = new Admin();
        }
        return self::$adminModel;
    }
    
    public static function login($username, $password) {
        $admin = self::getAdminModel()->authenticate($username, $password);
        
        if ($admin) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['login_time'] = time();
            
            // Log login activity
            self::logActivity('login', 'Admin logged in successfully');
            
            return true;
        }
        
        return false;
    }
    
    public static function logout() {
        if (self::isLoggedIn()) {
            self::logActivity('logout', 'Admin logged out');
        }
        
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            // Get the correct path to login.php based on current location
            $loginPath = self::getLoginPath();
            header('Location: ' . $loginPath);
            exit;
        }
        
        // Check session timeout (24 hours)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
            self::logout();
            $loginPath = self::getLoginPath();
            header('Location: ' . $loginPath . '?timeout=1');
            exit;
        }
    }
    
    private static function getLoginPath() {
        // Check if we're in admin folder or root
        $currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
        if ($currentDir === 'admin') {
            return '../login.php';
        }
        return 'login.php';
    }
    
    public static function getCurrentAdmin() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return self::getAdminModel()->getById($_SESSION['admin_id']);
    }
    
    public static function getAdminName() {
        return $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'المدير';
    }
    
    private static function logActivity($action, $message) {
        $logFile = __DIR__ . '/logs/auth_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . ' - ' . $action . ' - ' . $message . 
                   ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . 
                   ' - User: ' . ($_SESSION['admin_username'] ?? 'unknown') . PHP_EOL;
        
        if (!file_exists(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
