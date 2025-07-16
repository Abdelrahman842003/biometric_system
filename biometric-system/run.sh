#!/bin/bash

# Biometric System Quick Setup
echo "🔧 إعداد نظام إدارة أجهزة البايومترك"
echo "======================================"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP غير مثبت. يرجى تثبيت PHP أولاً."
    exit 1
fi

# Check if config file exists
if [ ! -f "config/database.php" ]; then
    echo "❌ ملف التكوين غير موجود. يرجى إعداد config/database.php أولاً."
    exit 1
fi

echo "✅ PHP موجود"
echo "✅ ملف التكوين موجود"
echo ""

# Run database setup
echo "🗄️ تطبيق schema قاعدة البيانات..."
php setup.php

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 تم إعداد النظام بنجاح!"
    echo ""
    echo "📍 يمكنك الآن الوصول للنظام من:"
    echo "   http://localhost/biometric-system/"
    echo ""
    echo "💡 إذا لم يعمل الرابط أعلاه، جرب:"
    echo "   - تشغيل PHP من داخل مجلد المشروع: php -S localhost:8000"
    echo "   - ثم الدخول على: http://localhost:8000/"
    echo ""
    echo "🔑 بيانات الدخول الافتراضية:"
    echo "   Username: admin"
    echo "   Password: password"
    echo ""
    echo "⚠️  تذكر تغيير كلمة المرور بعد أول تسجيل دخول!"
else
    echo "❌ فشل في إعداد النظام"
    exit 1
fi
