#!/bin/bash

# عرض حالة النظام ومعلومات التشغيل
# System Status and Running Information

clear

echo "📊 حالة نظام إدارة أجهزة البايومترك"
echo "===================================="
echo ""

# فحص حالة الخادم
echo "🔍 فحص حالة الخادم..."
if lsof -i :8000 >/dev/null 2>&1; then
    echo "✅ الخادم يعمل على المنفذ 8000"
    echo "🌐 الرابط: http://localhost:8000/"
    echo ""
    
    # فحص استجابة الخادم
    if curl -s http://localhost:8000/ >/dev/null 2>&1; then
        echo "✅ النظام يستجيب بشكل صحيح"
    else
        echo "⚠️  الخادم يعمل لكن قد تكون هناك مشكلة في الاستجابة"
    fi
else
    echo "❌ الخادم غير مُشغل"
    echo ""
    echo "💡 لتشغيل النظام:"
    echo "   ./start.sh"
    echo "   أو"
    echo "   php -S localhost:8000"
fi

echo ""
echo "===================================="
echo ""

# فحص قاعدة البيانات إذا كان الخادم يعمل
if lsof -i :8000 >/dev/null 2>&1; then
    echo "🔍 فحص حالة قاعدة البيانات..."
    
    # محاولة الوصول لنقطة فحص حالة النظام
    if curl -s http://localhost:8000/api/system-status.php 2>/dev/null | grep -q "database"; then
        echo "✅ قاعدة البيانات متصلة"
    else
        echo "⚠️  قد تحتاج لتثبيت قاعدة البيانات"
        echo "💡 اذهب إلى: http://localhost:8000/install.php"
    fi
    
    echo ""
    echo "🎯 روابط مهمة:"
    echo "   الصفحة الرئيسية: http://localhost:8000/"
    echo "   لوحة التحكم: http://localhost:8000/admin/dashboard.php"
    echo "   تثبيت قاعدة البيانات: http://localhost:8000/install.php"
fi

echo ""
echo "===================================="
echo ""
echo "📝 للمساعدة: ./help.sh"
echo "🔄 لإعادة تشغيل النظام: ./start.sh"
echo ""
