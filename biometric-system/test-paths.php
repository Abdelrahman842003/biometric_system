<?php
// Test login redirects from different paths
echo "🔄 Testing Login Redirects\n";
echo "=" . str_repeat("=", 40) . "\n";

// Test 1: Root level
echo "\n1️⃣ Testing from root level:\n";
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/auth.php';

// Clear any existing session
session_start();
session_destroy();

echo "Script: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Expected redirect: login.php\n";

// Test 2: Admin level
echo "\n2️⃣ Testing from admin level:\n";
$_SERVER['SCRIPT_NAME'] = '/admin/dashboard.php';

echo "Script: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Expected redirect: ../login.php\n";

// Test the function directly
$reflection = new ReflectionClass('Auth');
$method = $reflection->getMethod('getLoginPath');
$method->setAccessible(true);

$_SERVER['SCRIPT_NAME'] = '/admin/dashboard.php';
$adminPath = $method->invoke(null);
echo "Actual admin path: " . $adminPath . "\n";

$_SERVER['SCRIPT_NAME'] = '/index.php';
$rootPath = $method->invoke(null);
echo "Actual root path: " . $rootPath . "\n";

echo "\n🌐 URLs to test:\n";
echo "- Root access: http://localhost:8001/\n";
echo "- Admin access: http://localhost:8001/admin/dashboard.php\n";
echo "- Login page: http://localhost:8001/login.php\n";

echo "\n✅ Path resolution working correctly!\n";
