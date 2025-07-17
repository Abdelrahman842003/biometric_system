<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'biometric_system');
define('DB_USER', 'root');
define('DB_PASS', '842003..');
define('DB_CHARSET', 'utf8mb4');

// Security Configuration
define('SECRET_KEY', 'BiometricSystem2025SecretKey!@#');
define('ADMS_TOKEN', 'ADMS_Token_2025_Secure_BiometricSystem_#$%');

// IP Security (for public access)
define('ALLOW_ALL_IPS', true);  // Set to false in production
define('ENABLE_IP_WHITELIST', false);  // Set to true for strict IP control

// System Configuration
define('DEFAULT_PORT', 4370);
define('TIMEZONE', 'Asia/Riyadh');

// Set timezone
date_default_timezone_set(TIMEZONE);

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
?>
