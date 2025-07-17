-- Create Database
CREATE DATABASE IF NOT EXISTS biometric_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biometric_system;

-- Admins Table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- Machines Table
CREATE TABLE machines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    ip_address VARCHAR(45) NOT NULL,
    serial_number VARCHAR(100),
    port INT DEFAULT 4370,
    adms_enabled TINYINT(1) DEFAULT 1,
    adms_key VARCHAR(255),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    model VARCHAR(100),
    firmware_version VARCHAR(50),
    users_count INT DEFAULT 0,
    last_sync TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ip_port (ip_address, port),
    INDEX idx_status (status)
);

-- Users Table (Biometric Users)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    position VARCHAR(100),
    card_number VARCHAR(50),
    fingerprint_template LONGTEXT,
    face_template LONGTEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (is_active)
);

-- User Machine Assignment
CREATE TABLE user_machines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    machine_id INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_machine (user_id, machine_id)
);

-- Attendance Logs Table
CREATE TABLE attendance_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    machine_id INT,
    user_id VARCHAR(20) NOT NULL,
    log_time TIMESTAMP NOT NULL,
    log_type ENUM('check_in', 'check_out', 'break_in', 'break_out') DEFAULT 'check_in',
    verify_type ENUM('fingerprint', 'face', 'password', 'fingerprint_face') DEFAULT 'fingerprint',
    temperature DECIMAL(4,1) NULL,
    mask_status TINYINT(1) NULL,
    raw_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    INDEX idx_machine_time (machine_id, log_time),
    INDEX idx_user_time (user_id, log_time),
    INDEX idx_log_type (log_type)
);

-- Commands Table
CREATE TABLE commands (
    id INT PRIMARY KEY AUTO_INCREMENT,
    machine_id INT,
    command_type ENUM('reboot', 'sync_time', 'clear_logs', 'add_user', 'delete_user', 'update_user', 'get_info') NOT NULL,
    command_data JSON,
    status ENUM('pending', 'sent', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    error_message TEXT,
    result_data JSON,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    INDEX idx_machine_status (machine_id, status),
    INDEX idx_status_created (status, created_at)
);

-- System Logs Table
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    target_type ENUM('machine', 'user', 'admin', 'system') NOT NULL,
    target_id INT,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_admin_action (admin_id, action),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created (created_at)
);

-- Insert Default Admin
INSERT INTO admins (username, password_hash, email, full_name) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@biometric.local', 'System Administrator');

-- Insert Sample Machine
INSERT INTO machines (name, location, ip_address, serial_number, adms_key) 
VALUES ('Main Entrance', 'Building A - Ground Floor', '192.168.1.100', 'ZK001234567890', 'sample-adms-key-123');

-- Insert Sample Users
INSERT INTO users (user_id, name, department, position, is_active) VALUES 
('001', 'أحمد محمد', 'تقنية المعلومات', 'مطور برمجيات', 1),
('002', 'فاطمة أحمد', 'الموارد البشرية', 'محاسب', 1),
('003', 'محمد علي', 'المبيعات', 'مدير مبيعات', 1);
