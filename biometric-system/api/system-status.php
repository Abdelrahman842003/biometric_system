<?php
header('Content-Type: application/json');

try {
    // Check database connection
    require_once __DIR__ . '/../config/database.php';
    
    $database = false;
    $adms = false;
    $sdk = false;
    $security = false;
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT 1");
        $database = $stmt !== false;
    } catch (Exception $e) {
        $database = false;
    }
    
    // Check ADMS endpoint
    $adms = file_exists('api/adms-endpoint.php');
    
    // Check SDK
    $sdk = file_exists('sdk/ZKTeco.php');
    
    // Check security (HTTPS, token, etc.)
    $security = !empty(ADMS_TOKEN) && !empty(SECRET_KEY);
    
    // System info
    $info = [
        'database' => $database,
        'adms' => $adms,
        'sdk' => $sdk,
        'security' => $security,
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s'),
        'system_status' => $database && $adms && $sdk && $security ? 'healthy' : 'warning'
    ];
    
    echo json_encode($info);
    
} catch (Exception $e) {
    echo json_encode([
        'database' => false,
        'adms' => false,
        'sdk' => false,
        'security' => false,
        'error' => $e->getMessage(),
        'system_status' => 'error'
    ]);
}
?>
