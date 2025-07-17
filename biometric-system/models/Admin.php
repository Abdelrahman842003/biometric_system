<?php
require_once __DIR__ . '/../config/database.php';

class Admin {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM admins WHERE username = :username AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Update last login
            $this->updateLastLogin($admin['id']);
            return $admin;
        }
        
        return false;
    }
    
    public function updateLastLogin($adminId) {
        $sql = "UPDATE admins SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $adminId]);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM admins WHERE id = :id AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function createAdmin($data) {
        $sql = "INSERT INTO admins (username, password_hash, email, full_name, is_active) 
                VALUES (:username, :password_hash, :email, :full_name, :is_active)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $data['username'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':email' => $data['email'] ?? '',
            ':full_name' => $data['full_name'] ?? '',
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }
    
    public function getAll() {
        $sql = "SELECT id, username, email, full_name, created_at, last_login, is_active 
                FROM admins ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
