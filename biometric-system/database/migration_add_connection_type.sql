-- Migration to add connection_type to existing machines table
-- Run this SQL script to update existing database

-- Add connection_type column to machines table
ALTER TABLE machines 
ADD COLUMN connection_type ENUM('adms', 'public_ip') DEFAULT 'adms' 
AFTER port;

-- Add index for connection_type
ALTER TABLE machines 
ADD INDEX idx_connection_type (connection_type);

-- Update existing machines to set default connection_type based on adms_enabled
UPDATE machines 
SET connection_type = CASE 
    WHEN adms_enabled = 1 THEN 'adms' 
    ELSE 'public_ip' 
END;

-- Verify the migration
SELECT id, name, ip_address, connection_type, adms_enabled FROM machines;
