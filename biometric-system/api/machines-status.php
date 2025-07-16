<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';

try {
    $machineModel = new Machine();
    
    $machines = $machineModel->getAll();
    
    echo json_encode([
        'success' => true,
        'machines' => $machines
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
