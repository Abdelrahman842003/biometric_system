<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AttendanceLog.php';

try {
    $attendanceModel = new AttendanceLog();
    
    $limit = $_GET['limit'] ?? 10;
    
    $logs = $attendanceModel->getAll(['limit' => $limit]);
    
    echo json_encode([
        'success' => true,
        'logs' => $logs
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
