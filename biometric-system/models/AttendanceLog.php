<?php
require_once __DIR__ . '/../config/database.php';

class AttendanceLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO attendance_logs (machine_id, user_id, log_time, log_type, verify_type, temperature, mask_status, raw_data) 
                VALUES (:machine_id, :user_id, :log_time, :log_type, :verify_type, :temperature, :mask_status, :raw_data)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':machine_id' => $data['machine_id'],
            ':user_id' => $data['user_id'],
            ':log_time' => $data['log_time'],
            ':log_type' => $data['log_type'] ?? 'check_in',
            ':verify_type' => $data['verify_type'] ?? 'fingerprint',
            ':temperature' => $data['temperature'] ?? null,
            ':mask_status' => $data['mask_status'] ?? null,
            ':raw_data' => $data['raw_data'] ?? null
        ]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT al.*, m.name as machine_name, u.name as user_name 
                FROM attendance_logs al 
                LEFT JOIN machines m ON al.machine_id = m.id 
                LEFT JOIN users u ON al.user_id = u.user_id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['machine_id'])) {
            $sql .= " AND al.machine_id = :machine_id";
            $params[':machine_id'] = $filters['machine_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.log_time) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.log_time) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['log_type'])) {
            $sql .= " AND al.log_type = :log_type";
            $params[':log_type'] = $filters['log_type'];
        }
        
        $sql .= " ORDER BY al.log_time DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT al.*, m.name as machine_name, u.name as user_name 
                FROM attendance_logs al 
                LEFT JOIN machines m ON al.machine_id = m.id 
                LEFT JOIN users u ON al.user_id = u.user_id 
                WHERE al.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getStats($filters = []) {
        $sql = "SELECT 
                COUNT(*) as total_logs,
                COUNT(DISTINCT al.user_id) as unique_users,
                COUNT(DISTINCT al.machine_id) as active_machines,
                SUM(CASE WHEN al.log_type = 'check_in' THEN 1 ELSE 0 END) as check_ins,
                SUM(CASE WHEN al.log_type = 'check_out' THEN 1 ELSE 0 END) as check_outs
                FROM attendance_logs al WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.log_time) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.log_time) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function getDailyStats($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        $sql = "SELECT 
                m.name as machine_name,
                COUNT(*) as total_logs,
                COUNT(DISTINCT al.user_id) as unique_users,
                SUM(CASE WHEN al.log_type = 'check_in' THEN 1 ELSE 0 END) as check_ins,
                SUM(CASE WHEN al.log_type = 'check_out' THEN 1 ELSE 0 END) as check_outs
                FROM attendance_logs al 
                LEFT JOIN machines m ON al.machine_id = m.id 
                WHERE DATE(al.log_time) = :date 
                GROUP BY al.machine_id, m.name
                ORDER BY total_logs DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
    }
    
    public function exportToCsv($filters = []) {
        $logs = $this->getAll($filters);
        
        $filename = 'attendance_logs_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = __DIR__ . '/../exports/' . $filename;
        
        // Create exports directory if not exists
        if (!file_exists(__DIR__ . '/../exports')) {
            mkdir(__DIR__ . '/../exports', 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // CSV Header
        fputcsv($file, [
            'ID', 'Machine', 'User ID', 'User Name', 'Log Time', 
            'Log Type', 'Verify Type', 'Temperature', 'Mask Status'
        ]);
        
        // CSV Data
        foreach ($logs as $log) {
            fputcsv($file, [
                $log['id'],
                $log['machine_name'],
                $log['user_id'],
                $log['user_name'],
                $log['log_time'],
                $log['log_type'],
                $log['verify_type'],
                $log['temperature'],
                $log['mask_status'] ? 'Yes' : 'No'
            ]);
        }
        
        fclose($file);
        return $filename;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM attendance_logs WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function deleteByMachine($machineId) {
        $sql = "DELETE FROM attendance_logs WHERE machine_id = :machine_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':machine_id' => $machineId]);
    }
}
?>
