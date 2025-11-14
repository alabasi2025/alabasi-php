# ============================================
# سكريبت تحديث نظام الأباسي من GitHub
# Alabasi Accounting System - GitHub Update Script
# ============================================
# التاريخ: 2025-01-14
# الإصدار: 1.0
# ============================================

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  نظام الأباسي المحاسبي - التحديث من GitHub" -ForegroundColor Yellow
Write-Host "  Alabasi Accounting System - GitHub Update" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# المسار المحلي للمشروع
$ProjectPath = "D:\AAAAAA\xampp\htdocs\alabasi"

# التحقق من وجود المجلد
if (-Not (Test-Path $ProjectPath)) {
    Write-Host "❌ خطأ: المجلد غير موجود!" -ForegroundColor Red
    Write-Host "   المسار: $ProjectPath" -ForegroundColor Red
    Write-Host ""
    Write-Host "الرجاء التأكد من المسار الصحيح وإعادة المحاولة." -ForegroundColor Yellow
    Read-Host "اضغط Enter للخروج"
    exit
}

# الانتقال إلى مجلد المشروع
Write-Host "📁 الانتقال إلى مجلد المشروع..." -ForegroundColor Cyan
Set-Location $ProjectPath

# التحقق من وجود Git
Write-Host "🔍 التحقق من Git..." -ForegroundColor Cyan
$gitVersion = git --version 2>$null
if (-Not $gitVersion) {
    Write-Host "❌ خطأ: Git غير مثبت!" -ForegroundColor Red
    Write-Host ""
    Write-Host "الرجاء تثبيت Git من: https://git-scm.com/download/win" -ForegroundColor Yellow
    Read-Host "اضغط Enter للخروج"
    exit
}
Write-Host "✅ Git مثبت: $gitVersion" -ForegroundColor Green
Write-Host ""

# عرض الحالة الحالية
Write-Host "📊 الحالة الحالية:" -ForegroundColor Cyan
Write-Host "-------------------" -ForegroundColor Gray
git status --short
Write-Host ""

# سؤال المستخدم عن التحديث
Write-Host "⚠️  تحذير: سيتم استبدال جميع الملفات المحلية بالنسخة من GitHub!" -ForegroundColor Yellow
Write-Host "   تأكد من حفظ أي تعديلات مهمة قبل المتابعة." -ForegroundColor Yellow
Write-Host ""
$confirm = Read-Host "هل تريد المتابعة؟ (نعم/لا) [y/n]"

if ($confirm -ne "y" -and $confirm -ne "yes" -and $confirm -ne "نعم") {
    Write-Host ""
    Write-Host "❌ تم إلغاء التحديث." -ForegroundColor Red
    Read-Host "اضغط Enter للخروج"
    exit
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  جاري التحديث من GitHub..." -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# الخطوة 1: حفظ التعديلات المحلية (Stash)
Write-Host "📦 الخطوة 1/5: حفظ التعديلات المحلية..." -ForegroundColor Cyan
git stash save "Auto-stash before update - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ تم حفظ التعديلات المحلية" -ForegroundColor Green
} else {
    Write-Host "⚠️  لا توجد تعديلات محلية لحفظها" -ForegroundColor Yellow
}
Write-Host ""

# الخطوة 2: جلب آخر التحديثات
Write-Host "🌐 الخطوة 2/5: جلب آخر التحديثات من GitHub..." -ForegroundColor Cyan
git fetch origin master
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ تم جلب التحديثات بنجاح" -ForegroundColor Green
} else {
    Write-Host "❌ فشل جلب التحديثات!" -ForegroundColor Red
    Read-Host "اضغط Enter للخروج"
    exit
}
Write-Host ""

# الخطوة 3: إعادة تعيين الملفات المحلية
Write-Host "🔄 الخطوة 3/5: إعادة تعيين الملفات المحلية..." -ForegroundColor Cyan
git reset --hard origin/master
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ تم إعادة تعيين الملفات بنجاح" -ForegroundColor Green
} else {
    Write-Host "❌ فشل إعادة تعيين الملفات!" -ForegroundColor Red
    Read-Host "اضغط Enter للخروج"
    exit
}
Write-Host ""

# الخطوة 4: تنظيف الملفات غير المتتبعة
Write-Host "🧹 الخطوة 4/5: تنظيف الملفات غير المتتبعة..." -ForegroundColor Cyan
git clean -fd
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ تم التنظيف بنجاح" -ForegroundColor Green
} else {
    Write-Host "⚠️  تحذير: قد تكون هناك ملفات لم يتم تنظيفها" -ForegroundColor Yellow
}
Write-Host ""

# الخطوة 5: عرض معلومات آخر تحديث
Write-Host "📋 الخطوة 5/5: معلومات آخر تحديث:" -ForegroundColor Cyan
Write-Host "-----------------------------------" -ForegroundColor Gray
git log -1 --pretty=format:"%C(yellow)Commit:%C(reset) %h%n%C(cyan)التاريخ:%C(reset) %ad%n%C(green)المطور:%C(reset) %an%n%C(magenta)الرسالة:%C(reset)%n%s%n" --date=format:"%Y-%m-%d %H:%M:%S"
Write-Host ""

# عرض الملفات المحدثة
Write-Host "📂 الملفات المحدثة:" -ForegroundColor Cyan
Write-Host "-------------------" -ForegroundColor Gray
git diff --name-status HEAD@{1} HEAD 2>$null | ForEach-Object {
    $status = $_.Split()[0]
    $file = $_.Split()[1]
    
    switch ($status) {
        "A" { Write-Host "  ➕ $file" -ForegroundColor Green }
        "M" { Write-Host "  ✏️  $file" -ForegroundColor Yellow }
        "D" { Write-Host "  ➖ $file" -ForegroundColor Red }
        default { Write-Host "  📄 $file" -ForegroundColor Gray }
    }
}
Write-Host ""

# إحصائيات التحديث
Write-Host "📊 إحصائيات التحديث:" -ForegroundColor Cyan
Write-Host "--------------------" -ForegroundColor Gray
$stats = git diff --shortstat HEAD@{1} HEAD 2>$null
if ($stats) {
    Write-Host "  $stats" -ForegroundColor White
} else {
    Write-Host "  لا توجد تغييرات" -ForegroundColor Gray
}
Write-Host ""

# رسالة النجاح
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ✅ تم التحديث بنجاح!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# تذكير بتطبيق تحديثات قاعدة البيانات
Write-Host "⚠️  تذكير مهم:" -ForegroundColor Yellow
Write-Host "   إذا كان هناك ملف SQL جديد (مثل system_updates_schema.sql)،" -ForegroundColor Yellow
Write-Host "   يجب عليك تطبيقه على قاعدة البيانات يدوياً عبر phpMyAdmin." -ForegroundColor Yellow
Write-Host ""

# عرض الملفات الجديدة
$newFiles = git diff --name-only --diff-filter=A HEAD@{1} HEAD 2>$null | Where-Object { $_ -like "*.sql" }
if ($newFiles) {
    Write-Host "📄 ملفات SQL الجديدة التي تحتاج إلى تطبيق:" -ForegroundColor Cyan
    $newFiles | ForEach-Object {
        Write-Host "   - $_" -ForegroundColor White
    }
    Write-Host ""
}

# خيارات إضافية
Write-Host "🔧 خيارات إضافية:" -ForegroundColor Cyan
Write-Host "   1. افتح phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor White
Write-Host "   2. افتح المشروع: http://localhost/alabasi" -ForegroundColor White
Write-Host "   3. راجع دليل التحديثات: UPDATES_GUIDE.md" -ForegroundColor White
Write-Host ""

# سؤال عن فتح المشروع في المتصفح
$openBrowser = Read-Host "هل تريد فتح المشروع في المتصفح؟ (نعم/لا) [y/n]"
if ($openBrowser -eq "y" -or $openBrowser -eq "yes" -or $openBrowser -eq "نعم") {
    Start-Process "http://localhost/alabasi"
    Write-Host "✅ تم فتح المشروع في المتصفح" -ForegroundColor Green
}

Write-Host ""
Write-Host "شكراً لاستخدامك نظام الأباسي المحاسبي! 🎉" -ForegroundColor Green
Write-Host ""

Read-Host "اضغط Enter للخروج"
