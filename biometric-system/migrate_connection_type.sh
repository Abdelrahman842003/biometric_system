#!/bin/bash

# Migration Script to add connection_type to machines table
# Run this script to update existing database schema

echo "🔧 Applying database migration for connection_type..."

# Check if MySQL is available
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL not found. Please install MySQL first."
    exit 1
fi

# Prompt for database credentials
read -p "Database name [biometric_system]: " DB_NAME
DB_NAME=${DB_NAME:-biometric_system}

read -p "Database user [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -p "Database host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

echo -n "Database password: "
read -s DB_PASS
echo ""

# Apply migration
echo "📊 Adding connection_type column..."

mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
-- Add connection_type column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'machines' 
     AND table_schema = DATABASE() 
     AND column_name = 'connection_type') > 0,
    'SELECT "Column connection_type already exists" as status',
    'ALTER TABLE machines ADD COLUMN connection_type ENUM(\'adms\', \'public_ip\') DEFAULT \'adms\' AFTER port'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE table_name = 'machines' 
     AND table_schema = DATABASE() 
     AND index_name = 'idx_connection_type') > 0,
    'SELECT "Index idx_connection_type already exists" as status',
    'ALTER TABLE machines ADD INDEX idx_connection_type (connection_type)'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing machines based on adms_enabled
UPDATE machines 
SET connection_type = CASE 
    WHEN adms_enabled = 1 THEN 'adms' 
    ELSE 'public_ip' 
END 
WHERE connection_type IS NULL OR connection_type = '';

SELECT 'Migration completed successfully!' as status;
SELECT COUNT(*) as total_machines, connection_type 
FROM machines 
GROUP BY connection_type;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Migration completed successfully!"
    echo "🎯 You can now use the new connection type feature in admin/machines.php"
else
    echo "❌ Migration failed. Please check the error messages above."
    exit 1
fi
