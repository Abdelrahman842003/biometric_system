#!/usr/bin/env php
<?php
/**
 * Quick Database Setup
 * تطبيق سريع لـ schema قاعدة البيانات
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║              🗄️  نظام إدارة أجهزة البايومترك                ║\n";
echo "║                    Database Schema Installer                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// التأكد من وجود ملف التكوين
if (!file_exists(__DIR__ . '/config/database.php')) {
    echo "❌ خطأ: ملف التكوين غير موجود (config/database.php)\n";
    echo "يرجى التأكد من إعداد ملف التكوين أولاً.\n\n";
    exit(1);
}

// تضمين ملف التثبيت
require_once __DIR__ . '/install.php';

try {
    $installer = new SchemaInstaller();
    $success = $installer->install();
    
    if ($success) {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                       ✅ تم التثبيت بنجاح!                   ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "🚀 الخطوات التالية:\n";
        echo "1. افتح المتصفح واذهب إلى: http://localhost:8000/\n";
        echo "2. استخدم بيانات الدخول: admin / password\n";
        echo "3. قم بتغيير كلمة المرور بعد أول تسجيل دخول\n";
        echo "4. أضف أجهزة البايومترك من لوحة التحكم\n\n";
        
        exit(0);
    } else {
        echo "❌ فشل في تطبيق schema\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
