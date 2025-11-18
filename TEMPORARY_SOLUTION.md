# 🔄 الحل المؤقت: قاعدة بيانات واحدة مع Prefixes

## 📋 نظرة عامة

بسبب عدم توفر صلاحية CREATE DATABASE، تم تعديل النظام ليعمل مع **قاعدة بيانات واحدة** باستخدام **table prefixes** لكل وحدة.

---

## 🗄️ بنية الجداول

### القاعدة: `u306850950_alabasi`

```
الجداول المركزية (prefix: main_):
├── main_units
├── main_companies
├── main_clearing_transactions
└── main_backup_logs

جداول وحدة الحديدة (prefix: unit2_):
├── unit2_companies
├── unit2_branches
├── unit2_accounts
├── unit2_journal_entries
└── unit2_journal_entry_details

جداول وحدة العباسي (prefix: unit3_):
├── unit3_companies
├── unit3_branches
├── unit3_accounts
├── unit3_journal_entries
└── unit3_journal_entry_details
```

---

## ⚙️ كيف يعمل؟

### 1. الـ Connections في Laravel:
```php
'main' => [
    'database' => 'u306850950_alabasi',
    'prefix' => 'main_',
],

'unit_2' => [
    'database' => 'u306850950_alabasi',
    'prefix' => 'unit2_',
],

'unit_3' => [
    'database' => 'u306850950_alabasi',
    'prefix' => 'unit3_',
],
```

### 2. الـ Models تستخدم نفس الـ Connection:
```php
// Model للقاعدة المركزية
protected $connection = 'main';
// الجدول الفعلي: main_units

// Model لوحدة الحديدة
protected $connection = 'unit_2';
// الجدول الفعلي: unit2_companies
```

---

## ✅ المميزات

1. ✅ **يعمل فوراً** - لا يحتاج صلاحيات خاصة
2. ✅ **نفس الكود** - لا تغيير في الـ Models أو Services
3. ✅ **عزل منطقي** - كل وحدة لها جداولها الخاصة
4. ✅ **قابل للترحيل** - يمكن نقله لقواعد منفصلة لاحقاً

---

## ⚠️ العيوب

1. ⚠️ **أداء أقل قليلاً** - جميع الجداول في قاعدة واحدة
2. ⚠️ **صعوبة النقل** - نقل وحدة لخادم آخر يحتاج script خاص
3. ⚠️ **النسخ الاحتياطي** - نسخة واحدة لكل شيء

---

## 🚀 خطوات التنفيذ

### 1. على الخادم المحلي (للاختبار):
```bash
cd /home/ubuntu/alabasi-php

# تشغيل migrations للقاعدة المركزية
php artisan migrate --path=database/migrations/main --database=main

# تشغيل migrations لوحدة الحديدة
php artisan migrate --path=database/migrations/units --database=unit_2

# تشغيل migrations لوحدة العباسي
php artisan migrate --path=database/migrations/units --database=unit_3

# تشغيل Seeders
php artisan db:seed --class=MainDatabaseSeeder
```

### 2. على الخادم السحابي:
```bash
# الاتصال بالخادم
ssh u306850950@82.29.157.218 -p 65002 -i alabasi_ssh_private_key

# الانتقال للمجلد
cd /home/u306850950/domains/alabasi.es/public_html

# سحب التحديثات
git pull origin master

# تحديث composer
composer install --no-dev --optimize-autoloader

# تشغيل migrations
php artisan migrate --path=database/migrations/main --database=main
php artisan migrate --path=database/migrations/units --database=unit_2
php artisan migrate --path=database/migrations/units --database=unit_3

# تشغيل Seeders
php artisan db:seed --class=MainDatabaseSeeder
```

---

## 🔄 الترحيل المستقبلي

عندما تتوفر إمكانية إنشاء قواعد منفصلة:

### 1. إنشاء القواعد الجديدة:
```sql
CREATE DATABASE u306850950_alabasi_main;
CREATE DATABASE u306850950_alabasi_unit_2;
CREATE DATABASE u306850950_alabasi_unit_3;
```

### 2. تشغيل script الترحيل:
```bash
php artisan migrate:separate-databases
```

سيقوم الـ script بـ:
- ✅ نسخ الجداول من القاعدة الواحدة
- ✅ نقل البيانات لقواعد منفصلة
- ✅ تحديث ملف .env
- ✅ اختبار الاتصالات

---

## 📊 مقارنة الحلول

| الميزة | قاعدة واحدة (الحالي) | قواعد منفصلة (المستقبل) |
|--------|---------------------|------------------------|
| سهولة التنفيذ | ✅ فوري | ⏳ يحتاج صلاحيات |
| الأداء | ⚠️ جيد | ✅ ممتاز |
| العزل | ⚠️ منطقي | ✅ فيزيائي |
| النقل | ⚠️ معقد | ✅ سهل |
| النسخ الاحتياطي | ⚠️ شامل | ✅ انتقائي |

---

## 💡 توصيات

### للاستخدام الحالي:
1. ✅ استخدم هذا الحل للبدء فوراً
2. ✅ اختبر جميع الميزات
3. ✅ أنشئ بيانات تجريبية

### للمستقبل:
1. 📌 اطلب من Hostinger تفعيل صلاحية CREATE DATABASE
2. 📌 أو انتقل لخطة أعلى تدعم قواعد متعددة
3. 📌 استخدم script الترحيل للانتقال السلس

---

## 🐛 حل المشاكل

### المشكلة: خطأ في Migrations
```
SQLSTATE[42S01]: Base table or view already exists
```
**الحل:**
```bash
php artisan migrate:reset --database=main
php artisan migrate --path=database/migrations/main --database=main
```

### المشكلة: Prefix غير صحيح
```
Table 'u306850950_alabasi.units' doesn't exist
```
**الحل:** تأكد من استخدام الـ connection الصحيح في الـ Model:
```php
protected $connection = 'main'; // وليس 'mysql'
```

---

**تاريخ الإنشاء:** 2025-01-18  
**الحالة:** ✅ جاهز للتنفيذ  
**النوع:** حل مؤقت قابل للترحيل
