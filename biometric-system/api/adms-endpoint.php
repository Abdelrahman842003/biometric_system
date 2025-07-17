<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../models/AttendanceLog.php';

// Security check - validate ADMS token
function validateAdmsToken($token) {
    return hash_equals(ADMS_TOKEN, $token);
}

// IP Whitelist security (optional - for public IP access)
function checkIPWhitelist() {
    $allowedIPs = [
        '127.0.0.1',           // localhost
        '192.168.1.0/24',      // local network
        // '203.123.45.67',    // Add specific device IPs here
    ];
    
    $clientIP = $_SERVER['REMOTE_ADDR'];
    
    // For development, allow all IPs
    if (defined('ALLOW_ALL_IPS') && ALLOW_ALL_IPS) {
        return true;
    }
    
    foreach ($allowedIPs as $allowedIP) {
        if (strpos($allowedIP, '/') !== false) {
            // CIDR notation
            if (ip_in_range($clientIP, $allowedIP)) {
                return true;
            }
        } else {
            // Exact IP match
            if ($clientIP === $allowedIP) {
                return true;
            }
        }
    }
    
    return false;
}

function ip_in_range($ip, $cidr) {
    list($subnet, $mask) = explode('/', $cidr);
    return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet);
}

// Log system activity
function logActivity($message, $data = []) {
    $logFile = __DIR__ . '/../logs/adms_' . date('Y-m-d') . '.log';
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . ' - ' . json_encode($data) . PHP_EOL;
    
    if (!file_exists(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Main ADMS handler
try {
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get request data
    $rawInput = file_get_contents('php://input');
    $requestData = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE && !empty($rawInput)) {
        // Try to parse as URL encoded data
        parse_str($rawInput, $requestData);
    }
    
    // Merge with GET/POST data
    $requestData = array_merge($_GET, $_POST, $requestData ?: []);
    
    logActivity('ADMS Request', [
        'method' => $method,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'data' => $requestData
    ]);
    
    // Validate token if provided
    $token = $requestData['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!empty($token) && !validateAdmsToken($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        logActivity('Unauthorized ADMS access', ['token' => $token]);
        exit;
    }
    
    // Check IP whitelist
    if (!checkIPWhitelist()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - IP not allowed']);
        logActivity('Forbidden ADMS access', ['ip' => $_SERVER['REMOTE_ADDR']]);
        exit;
    }
    
    // Initialize models
    $machineModel = new Machine();
    $attendanceModel = new AttendanceLog();
    
    // Route based on action
    $action = $requestData['action'] ?? 'attendance';
    
    switch ($action) {
        case 'attendance':
            handleAttendanceLog($requestData, $machineModel, $attendanceModel);
            break;
            
        case 'heartbeat':
            handleHeartbeat($requestData, $machineModel);
            break;
            
        case 'status':
            handleStatusUpdate($requestData, $machineModel);
            break;
            
        case 'register':
            handleMachineRegistration($requestData, $machineModel);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
            logActivity('Unknown ADMS action', ['action' => $action]);
            exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    logActivity('ADMS Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}

function handleAttendanceLog($data, $machineModel, $attendanceModel) {
    // Required fields for attendance log
    $required = ['machine_ip', 'user_id', 'timestamp'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Find machine by IP
    $machine = $machineModel->getActiveByIp($data['machine_ip']);
    if (!$machine) {
        http_response_code(404);
        echo json_encode(['error' => 'Machine not found or inactive']);
        logActivity('Machine not found', ['ip' => $data['machine_ip']]);
        return;
    }
    
    // Parse timestamp
    $logTime = date('Y-m-d H:i:s', strtotime($data['timestamp']));
    if (!$logTime) {
        $logTime = date('Y-m-d H:i:s');
    }
    
    // Determine log type
    $logType = 'check_in';
    if (isset($data['log_type'])) {
        $logType = $data['log_type'];
    } elseif (isset($data['type'])) {
        $logType = $data['type'];
    } elseif (isset($data['punch_state'])) {
        $logType = ($data['punch_state'] == 0) ? 'check_in' : 'check_out';
    }
    
    // Determine verify type with basic 4 types only
    $verifyType = 'fingerprint';
    if (isset($data['verify_type'])) {
        $verifyType = $data['verify_type'];
    } elseif (isset($data['verify_mode'])) {
        // Basic mapping for 4 verification modes only
        switch ($data['verify_mode']) {
            case 1: $verifyType = 'fingerprint'; break;
            case 2: $verifyType = 'password'; break;
            case 3: $verifyType = 'password'; break; // Card as manual
            case 4: $verifyType = 'face'; break;
            case 5: $verifyType = 'fingerprint_face'; break; // Dual biometric
            default: $verifyType = 'fingerprint';
        }
    } elseif (isset($data['verification_method'])) {
        // Direct mapping from device
        $allowedTypes = ['fingerprint', 'face', 'password', 'fingerprint_face'];
        $verifyType = in_array($data['verification_method'], $allowedTypes) 
                     ? $data['verification_method'] 
                     : 'fingerprint';
    }
    
    // Create attendance log
    $logData = [
        'machine_id' => $machine['id'],
        'user_id' => $data['user_id'],
        'log_time' => $logTime,
        'log_type' => $logType,
        'verify_type' => $verifyType,
        'temperature' => $data['temperature'] ?? null,
        'mask_status' => isset($data['mask']) ? ($data['mask'] ? 1 : 0) : null,
        'raw_data' => json_encode($data)
    ];
    
    if ($attendanceModel->create($logData)) {
        // Update machine last sync
        $machineModel->updateLastSync($machine['id']);
        
        echo json_encode(['success' => true, 'message' => 'Attendance log recorded']);
        logActivity('Attendance log created', [
            'machine_id' => $machine['id'],
            'user_id' => $data['user_id'],
            'log_type' => $logType
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save attendance log']);
    }
}

function handleHeartbeat($data, $machineModel) {
    $machineIp = $data['machine_ip'] ?? $data['ip'] ?? '';
    
    if (empty($machineIp)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing machine IP']);
        return;
    }
    
    $machine = $machineModel->getActiveByIp($machineIp);
    if (!$machine) {
        http_response_code(404);
        echo json_encode(['error' => 'Machine not found']);
        return;
    }
    
    // Update last sync time
    $machineModel->updateLastSync($machine['id']);
    
    echo json_encode([
        'success' => true,
        'server_time' => date('Y-m-d H:i:s'),
        'machine_id' => $machine['id']
    ]);
    
    logActivity('Heartbeat received', ['machine_id' => $machine['id']]);
}

function handleStatusUpdate($data, $machineModel) {
    $machineIp = $data['machine_ip'] ?? $data['ip'] ?? '';
    
    if (empty($machineIp)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing machine IP']);
        return;
    }
    
    $machine = $machineModel->getActiveByIp($machineIp);
    if (!$machine) {
        http_response_code(404);
        echo json_encode(['error' => 'Machine not found']);
        return;
    }
    
    // Update machine info if provided
    $updates = [];
    if (isset($data['users_count'])) {
        $machineModel->updateUsersCount($machine['id'], $data['users_count']);
    }
    
    // Update last sync
    $machineModel->updateLastSync($machine['id']);
    
    echo json_encode(['success' => true, 'message' => 'Status updated']);
    logActivity('Status update', ['machine_id' => $machine['id'], 'data' => $data]);
}

function handleMachineRegistration($data, $machineModel) {
    $required = ['name', 'ip', 'serial_number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Check if machine already exists
    $existing = $machineModel->getActiveByIp($data['ip']);
    if ($existing) {
        echo json_encode([
            'success' => true,
            'message' => 'Machine already registered',
            'machine_id' => $existing['id']
        ]);
        return;
    }
    
    // Create new machine
    $machineData = [
        'name' => $data['name'],
        'location' => $data['location'] ?? '',
        'ip_address' => $data['ip'],
        'serial_number' => $data['serial_number'],
        'port' => $data['port'] ?? DEFAULT_PORT,
        'adms_enabled' => 1,
        'adms_key' => $data['adms_key'] ?? '',
        'status' => 'active'
    ];
    
    if ($machineModel->create($machineData)) {
        echo json_encode(['success' => true, 'message' => 'Machine registered successfully']);
        logActivity('Machine registered', $machineData);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register machine']);
    }
}
?>
