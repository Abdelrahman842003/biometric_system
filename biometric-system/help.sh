#!/bin/bash

# ملف مساعدة سريعة لتشغيل نظام البايومترك
# Quick Help for Biometric System

clear

echo "🎯 نظام إدارة أجهزة البايومترك"
echo "================================="
echo ""
echo "🚀 طرق التشغيل السريع:"
echo ""
echo "1️⃣  التشغيل المباشر (الأسهل):"
echo "   ./start.sh"
echo ""
echo "2️⃣  التثبيت مع التشغيل:"
echo "   ./run.sh"
echo ""
echo "3️⃣  تشغيل خادم PHP يدوياً:"
echo "   php -S localhost:8000"
echo ""
echo "================================="
echo ""
echo "❓ إذا ظهرت رسالة 'Permission denied':"
echo "   chmod +x start.sh"
echo "   chmod +x run.sh"
echo ""
echo "❓ إذا لم يعمل النظام:"
echo "   - تأكد من تثبيت PHP"
echo "   - تأكد من تثبيت MySQL/MariaDB"
echo "   - راجع ملف README.md للتفاصيل"
echo ""
echo "📞 للمساعدة:"
echo "   cat README.md"
echo ""
echo "================================="
echo ""

read -p "اضغط Enter للمتابعة..."
