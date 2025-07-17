<?php
require_once __DIR__ . '/../config/database.php';

class Machine {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO machines (name, location, ip_address, serial_number, port, adms_enabled, adms_key, status) 
                VALUES (:name, :location, :ip_address, :serial_number, :port, :adms_enabled, :adms_key, :status)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':location' => $data['location'],
            ':ip_address' => $data['ip_address'],
            ':serial_number' => $data['serial_number'],
            ':port' => $data['port'] ?? DEFAULT_PORT,
            ':adms_enabled' => $data['adms_enabled'] ?? 1,
            ':adms_key' => $data['adms_key'] ?? '',
            ':status' => $data['status'] ?? 'active'
        ]);
    }
    
    public function getAll() {
        $sql = "SELECT * FROM machines ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM machines WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        try {
            $sql = "UPDATE machines SET 
                    name = :name, 
                    location = :location, 
                    ip_address = :ip_address, 
                    serial_number = :serial_number, 
                    port = :port, 
                    adms_enabled = :adms_enabled, 
                    adms_key = :adms_key, 
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $params = [
                ':id' => $id,
                ':name' => $data['name'],
                ':location' => $data['location'] ?: '',
                ':ip_address' => $data['ip_address'],
                ':serial_number' => $data['serial_number'] ?: '',
                ':port' => $data['port'] ?: 4370,
                ':adms_enabled' => $data['adms_enabled'] ?: 0,
                ':adms_key' => $data['adms_key'] ?: '',
                ':status' => $data['status'] ?: 'active'
            ];
            
            $result = $stmt->execute($params);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Machine update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        $sql = "DELETE FROM machines WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function testConnection($ip, $port) {
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
        if ($connection) {
            fclose($connection);
            return ['success' => true, 'message' => 'Connection successful'];
        } else {
            return ['success' => false, 'message' => "Connection failed: $errstr ($errno)"];
        }
    }
    
    public function updateLastSync($id) {
        $sql = "UPDATE machines SET last_sync = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function updateUsersCount($id, $count) {
        $sql = "UPDATE machines SET users_count = :count WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':count' => $count]);
    }
    
    public function getActiveByIp($ip) {
        $sql = "SELECT * FROM machines WHERE ip_address = :ip AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ip' => $ip]);
        return $stmt->fetch();
    }
}
?>
