<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $machineId = $data['machine_id'] ?? null;
    
    if (!$machineId) {
        echo json_encode(['success' => false, 'message' => 'Machine ID is required']);
        exit;
    }
    
    $machineModel = new Machine();
    $machine = $machineModel->getById($machineId);
    
    if (!$machine) {
        echo json_encode(['success' => false, 'message' => 'Machine not found']);
        exit;
    }
    
    $result = $machineModel->testConnection($machine['ip_address'], $machine['port']);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
