<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Command.php';
require_once __DIR__ . '/../models/Machine.php';
require_once __DIR__ . '/../sdk/ZKTeco.php';

class CommandProcessor {
    private $commandModel;
    private $machineModel;
    
    public function __construct() {
        $this->commandModel = new Command();
        $this->machineModel = new Machine();
    }
    
    public function processPendingCommands() {
        $commands = $this->commandModel->getPendingCommands();
        
        foreach ($commands as $command) {
            $this->processCommand($command);
        }
        
        return count($commands);
    }
    
    private function processCommand($command) {
        try {
            // Mark command as sent
            $this->commandModel->updateStatus($command['id'], 'sent');
            
            // Initialize SDK connection
            $zk = new ZKTeco($command['ip_address'], $command['port']);
            
            if (!$zk->connect()) {
                $this->commandModel->updateStatus($command['id'], 'failed', 'فشل في الاتصال بالجهاز');
                return false;
            }
            
            $result = false;
            $resultData = null;
            
            switch ($command['command_type']) {
                case 'reboot':
                    $result = $this->executeReboot($zk);
                    break;
                    
                case 'sync_time':
                    $result = $this->executeSyncTime($zk, $command);
                    break;
                    
                case 'clear_logs':
                    $result = $this->executeClearLogs($zk);
                    break;
                    
                case 'add_user':
                    $result = $this->executeAddUser($zk, $command);
                    break;
                    
                case 'delete_user':
                    $result = $this->executeDeleteUser($zk, $command);
                    break;
                    
                case 'get_info':
                    $result = $this->executeGetInfo($zk);
                    $resultData = $result;
                    break;
                    
                default:
                    throw new Exception('نوع أمر غير معروف: ' . $command['command_type']);
            }
            
            $zk->disconnect();
            
            if ($result) {
                $this->commandModel->updateStatus($command['id'], 'completed', null, $resultData);
                $this->logCommandExecution($command, 'نجح');
            } else {
                $this->commandModel->updateStatus($command['id'], 'failed', 'فشل في تنفيذ الأمر');
                $this->logCommandExecution($command, 'فشل');
            }
            
        } catch (Exception $e) {
            $this->commandModel->updateStatus($command['id'], 'failed', $e->getMessage());
            $this->logCommandExecution($command, 'خطأ: ' . $e->getMessage());
        }
    }
    
    private function executeReboot($zk) {
        return $zk->reboot();
    }
    
    private function executeSyncTime($zk, $command) {
        $commandData = json_decode($command['command_data'], true);
        $time = $commandData['time'] ?? null;
        
        if ($time) {
            $timestamp = strtotime($time);
            return $zk->setTime($timestamp);
        } else {
            return $zk->setTime(); // Current time
        }
    }
    
    private function executeClearLogs($zk) {
        return $zk->clearAttendanceLogs();
    }
    
    private function executeAddUser($zk, $command) {
        $userData = json_decode($command['command_data'], true);
        
        return $zk->addUser(
            $userData['user_id'],
            $userData['name'],
            $userData['fingerprint_template'] ?? '',
            $userData['face_template'] ?? ''
        );
    }
    
    private function executeDeleteUser($zk, $command) {
        $commandData = json_decode($command['command_data'], true);
        return $zk->deleteUser($commandData['user_id']);
    }
    
    private function executeGetInfo($zk) {
        $info = $zk->getDeviceInfo();
        
        if ($info) {
            // Update machine info in database
            // This could include firmware version, user count, etc.
            return $info;
        }
        
        return false;
    }
    
    private function logCommandExecution($command, $status) {
        $logFile = __DIR__ . '/../logs/commands_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . ' - ' . 
                   "Command {$command['id']} ({$command['command_type']}) " .
                   "for machine {$command['machine_name']} ({$command['ip_address']}): $status" . PHP_EOL;
        
        if (!file_exists(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $processor = new CommandProcessor();
    $processedCount = $processor->processPendingCommands();
    echo "Processed $processedCount commands\n";
}

// Web execution (for testing)
if (isset($_GET['process'])) {
    $processor = new CommandProcessor();
    $processedCount = $processor->processPendingCommands();
    echo json_encode(['processed' => $processedCount]);
}
?>
