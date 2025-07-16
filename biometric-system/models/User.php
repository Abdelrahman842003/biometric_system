<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (user_id, name, department, position, card_number, fingerprint_template, face_template, is_active, is_admin) 
                VALUES (:user_id, :name, :department, :position, :card_number, :fingerprint_template, :face_template, :is_active, :is_admin)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':department' => $data['department'] ?? '',
            ':position' => $data['position'] ?? '',
            ':card_number' => $data['card_number'] ?? '',
            ':fingerprint_template' => $data['fingerprint_template'] ?? '',
            ':face_template' => $data['face_template'] ?? '',
            ':is_active' => $data['is_active'] ?? 1,
            ':is_admin' => $data['is_admin'] ?? 0
        ]);
    }
    
    public function getAll() {
        $sql = "SELECT u.*, 
                COUNT(um.machine_id) as assigned_machines,
                GROUP_CONCAT(m.name SEPARATOR ', ') as machine_names
                FROM users u 
                LEFT JOIN user_machines um ON u.id = um.user_id 
                LEFT JOIN machines m ON um.machine_id = m.id
                GROUP BY u.id 
                ORDER BY u.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getByUserId($userId) {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET 
                user_id = :user_id,
                name = :name, 
                department = :department, 
                position = :position, 
                card_number = :card_number, 
                fingerprint_template = :fingerprint_template, 
                face_template = :face_template, 
                is_active = :is_active,
                is_admin = :is_admin,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':department' => $data['department'] ?? '',
            ':position' => $data['position'] ?? '',
            ':card_number' => $data['card_number'] ?? '',
            ':fingerprint_template' => $data['fingerprint_template'] ?? '',
            ':face_template' => $data['face_template'] ?? '',
            ':is_active' => $data['is_active'] ?? 1,
            ':is_admin' => $data['is_admin'] ?? 0
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function assignToMachine($userId, $machineId) {
        $sql = "INSERT INTO user_machines (user_id, machine_id) VALUES (:user_id, :machine_id) 
                ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':machine_id' => $machineId]);
    }
    
    public function removeFromMachine($userId, $machineId) {
        $sql = "DELETE FROM user_machines WHERE user_id = :user_id AND machine_id = :machine_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':machine_id' => $machineId]);
    }
    
    public function getUserMachines($userId) {
        $sql = "SELECT m.* FROM machines m 
                JOIN user_machines um ON m.id = um.machine_id 
                WHERE um.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getMachineUsers($machineId) {
        $sql = "SELECT u.* FROM users u 
                JOIN user_machines um ON u.id = um.user_id 
                WHERE um.machine_id = :machine_id AND u.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':machine_id' => $machineId]);
        return $stmt->fetchAll();
    }
}
?>
