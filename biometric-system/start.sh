#!/bin/bash

# تشغيل نظام إدارة أجهزة البايومترك
# Start Biometric System

clear

echo "🚀 بدء تشغيل نظام إدارة أجهزة البايومترك"
echo "=========================================="
echo ""

# التحقق من وجود PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP غير مثبت على النظام"
    echo "يرجى تثبيت PHP أولاً"
    exit 1
fi

# عرض معلومات PHP
php_version=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1-2)
echo "✅ تم العثور على PHP $php_version"
echo ""

# التحقق من الامتدادات المطلوبة
echo "🔍 فحص الامتدادات المطلوبة..."

required_extensions=("curl" "json" "pdo" "pdo_mysql" "openssl" "mbstring")
missing_extensions=()

for ext in "${required_extensions[@]}"; do
    if php -m | grep -q "^$ext$"; then
        echo "   ✅ $ext"
    else
        echo "   ❌ $ext"
        missing_extensions+=("$ext")
    fi
done

echo ""

if [ ${#missing_extensions[@]} -ne 0 ]; then
    echo "⚠️  تحذير: بعض الامتدادات مفقودة:"
    for ext in "${missing_extensions[@]}"; do
        echo "   - $ext"
    done
    echo ""
    echo "قد يؤثر ذلك على عمل النظام. يفضل تثبيت الامتدادات المفقودة."
    echo ""
    read -p "هل تريد المتابعة؟ [y/N]: " continue_anyway
    if [[ ! $continue_anyway =~ ^[Yy]$ ]]; then
        echo "تم إلغاء التشغيل."
        exit 1
    fi
    echo ""
fi

# البحث عن منفذ متاح
port=8000
while lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; do
    ((port++))
done

echo "🌐 تشغيل الخادم على المنفذ $port..."
echo ""

# بدء الخادم
echo "📍 يمكنك الوصول للنظام من:"
echo "   http://localhost:$port/"
echo "   http://127.0.0.1:$port/"
echo ""
echo "🔑 لإيقاف الخادم: اضغط Ctrl+C"
echo ""
echo "=========================================="
echo ""

# تشغيل خادم PHP
php -S localhost:$port

echo ""
echo "تم إيقاف الخادم."
