<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Machine ID is required']);
    exit;
}

try {
    $machineModel = new Machine();
    $machine = $machineModel->getById($_GET['id']);
    
    if (!$machine) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Machine not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'machine' => $machine
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
