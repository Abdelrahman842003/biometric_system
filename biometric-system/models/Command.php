<?php
require_once __DIR__ . '/../config/database.php';

class Command {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO commands (machine_id, command_type, command_data, status) 
                VALUES (:machine_id, :command_type, :command_data, :status)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':machine_id' => $data['machine_id'],
            ':command_type' => $data['command_type'],
            ':command_data' => json_encode($data['command_data'] ?? []),
            ':status' => $data['status'] ?? 'pending'
        ]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT c.*, m.name as machine_name, m.ip_address 
                FROM commands c 
                LEFT JOIN machines m ON c.machine_id = m.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['machine_id'])) {
            $sql .= " AND c.machine_id = :machine_id";
            $params[':machine_id'] = $filters['machine_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['command_type'])) {
            $sql .= " AND c.command_type = :command_type";
            $params[':command_type'] = $filters['command_type'];
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT c.*, m.name as machine_name, m.ip_address 
                FROM commands c 
                LEFT JOIN machines m ON c.machine_id = m.id 
                WHERE c.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getPendingCommands() {
        $sql = "SELECT c.*, m.name as machine_name, m.ip_address, m.port 
                FROM commands c 
                JOIN machines m ON c.machine_id = m.id 
                WHERE c.status = 'pending' AND m.status = 'active'
                ORDER BY c.created_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status, $errorMessage = null, $resultData = null) {
        $sql = "UPDATE commands SET 
                status = :status,
                error_message = :error_message,
                result_data = :result_data";
        
        if ($status === 'sent') {
            $sql .= ", sent_at = CURRENT_TIMESTAMP";
        } elseif ($status === 'completed' || $status === 'failed') {
            $sql .= ", completed_at = CURRENT_TIMESTAMP";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':error_message' => $errorMessage,
            ':result_data' => $resultData ? json_encode($resultData) : null
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM commands WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function createRebootCommand($machineId) {
        return $this->create([
            'machine_id' => $machineId,
            'command_type' => 'reboot',
            'command_data' => []
        ]);
    }
    
    public function createSyncTimeCommand($machineId) {
        return $this->create([
            'machine_id' => $machineId,
            'command_type' => 'sync_time',
            'command_data' => ['time' => date('Y-m-d H:i:s')]
        ]);
    }
    
    public function createClearLogsCommand($machineId) {
        return $this->create([
            'machine_id' => $machineId,
            'command_type' => 'clear_logs',
            'command_data' => []
        ]);
    }
    
    public function createAddUserCommand($machineId, $userData) {
        return $this->create([
            'machine_id' => $machineId,
            'command_type' => 'add_user',
            'command_data' => $userData
        ]);
    }
    
    public function createDeleteUserCommand($machineId, $userId) {
        return $this->create([
            'machine_id' => $machineId,
            'command_type' => 'delete_user',
            'command_data' => ['user_id' => $userId]
        ]);
    }
    
    public function createGetInfoCommand($machineId) {
        return $this->create([
            'machine_id' => $machineId,
            'command_type' => 'get_info',
            'command_data' => []
        ]);
    }
    
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_commands,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_commands,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_commands,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_commands
                FROM commands";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
?>
