#!/bin/bash

# فحص شامل لمسارات المشروع
echo "🔍 فحص مسارات نظام إدارة أجهزة البايومترك"
echo "=============================================="
echo ""

# التحقق من الهيكل الأساسي
echo "📁 فحص الهيكل الأساسي:"
dirs=("config" "models" "api" "admin" "cron" "database" "sdk")

for dir in "${dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "   ✅ $dir"
    else
        echo "   ❌ $dir مفقود"
    fi
done

echo ""

# التحقق من الملفات الرئيسية
echo "📄 فحص الملفات الرئيسية:"
files=(
    "index.php"
    "install.php" 
    "setup.php"
    "config/database.php"
    "models/Machine.php"
    "models/User.php"
    "models/AttendanceLog.php"
    "models/Command.php"
    "api/system-status.php"
    "api/adms-endpoint.php"
    "admin/dashboard.php"
    "admin/machines.php"
    "sdk/ZKTeco.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✅ $file"
    else
        echo "   ❌ $file مفقود"
    fi
done

echo ""

# فحص صحة المسارات في الملفات
echo "🔗 فحص صحة المسارات النسبية:"

# فحص مسارات require_once
echo "   فحص مسارات require_once..."
if grep -r "require_once '../" . 2>/dev/null | grep -v "Binary file" | head -1 >/dev/null; then
    echo "   ⚠️  تم العثور على مسارات نسبية قديمة:"
    grep -r "require_once '../" . 2>/dev/null | grep -v "Binary file" | head -5
else
    echo "   ✅ جميع مسارات require_once صحيحة"
fi

echo ""

# فحص روابط localhost
echo "   فحص روابط localhost..."
old_links=$(grep -r "http://localhost/" . 2>/dev/null | grep -v "Binary file" | grep -v ":8000" | wc -l)
if [ $old_links -gt 0 ]; then
    echo "   ⚠️  تم العثور على $old_links رابط يحتاج تحديث"
    grep -r "http://localhost/" . 2>/dev/null | grep -v "Binary file" | grep -v ":8000" | head -3
else
    echo "   ✅ جميع روابط localhost محدثة"
fi

echo ""
echo "=============================================="
echo "✅ تم الانتهاء من فحص المسارات"
echo ""
echo "💡 للتشغيل: ./start.sh"
echo "🔧 للتثبيت: ./run.sh"
echo ""
