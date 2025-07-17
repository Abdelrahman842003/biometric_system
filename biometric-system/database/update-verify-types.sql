-- Update verify_type enum to support more verification methods
-- Migration script to enhance verification types

USE biometric_system;

-- Update the verify_type column to support more verification methods
ALTER TABLE attendance_logs 
MODIFY COLUMN verify_type ENUM(
    'fingerprint', 
    'face', 
    'card', 
    'password', 
    'fingerprint_password', 
    'fingerprint_card', 
    'face_password', 
    'face_card',
    'palm',
    'iris'
) DEFAULT 'fingerprint';

-- Add comment to explain the verification types
ALTER TABLE attendance_logs 
MODIFY COLUMN verify_type ENUM(
    'fingerprint', 
    'face', 
    'card', 
    'password', 
    'fingerprint_password', 
    'fingerprint_card', 
    'face_password', 
    'face_card',
    'palm',
    'iris'
) DEFAULT 'fingerprint' COMMENT 'Verification method used for attendance logging';

COMMIT;
