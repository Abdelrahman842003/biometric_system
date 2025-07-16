<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AttendanceLog.php';
require_once __DIR__ . '/../models/Command.php';

try {
    $machineModel = new Machine();
    $userModel = new User();
    $attendanceModel = new AttendanceLog();
    $commandModel = new Command();
    
    // Get today's date
    $today = date('Y-m-d');
    
    // Get stats
    $machines = $machineModel->getAll();
    $totalMachines = count($machines);
    $activeMachines = count(array_filter($machines, function($m) { return $m['status'] === 'active'; }));
    
    $users = $userModel->getAll();
    $totalUsers = count($users);
    $activeUsers = count(array_filter($users, function($u) { return $u['is_active'] == 1; }));
    
    $todayAttendance = $attendanceModel->getAll(['date_from' => $today, 'date_to' => $today]);
    $todayAttendanceCount = count($todayAttendance);
    
    $pendingCommands = $commandModel->getAll(['status' => 'pending']);
    $pendingCommandsCount = count($pendingCommands);
    
    $stats = [
        'total_machines' => $totalMachines,
        'active_machines' => $activeMachines,
        'total_users' => $totalUsers,
        'active_users' => $activeUsers,
        'today_attendance' => $todayAttendanceCount,
        'pending_commands' => $pendingCommandsCount
    ];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
