# 🚀 دليل نشر التحديثات من GitHub إلى Hostinger

**التاريخ:** 19 نوفمبر 2025  
**الموقع:** alabasi.es  
**المستودع:** https://github.com/alabasi2025/alabasi-php

---

## 📋 الخطوات المطلوبة

### 1️⃣ **الوصول إلى SSH**

1. افتح لوحة التحكم Hostinger: https://hpanel.hostinger.com/websites/alabasi.es
2. من القائمة الجانبية، اذهب إلى **متقدم** > **الوصول عبر SSH**
3. انسخ معلومات الاتصال (Hostname, Port, Username)

---

### 2️⃣ **الاتصال عبر SSH**

افتح Terminal (أو PuTTY على Windows) واكتب:

```bash
ssh u306850950@alabasi.es -p 65002
```

*(استبدل البيانات بمعلومات SSH الخاصة بك)*

---

### 3️⃣ **الانتقال إلى مجلد الموقع**

```bash
cd domains/alabasi.es/public_html
```

---

### 4️⃣ **التحقق من حالة Git**

```bash
git status
git remote -v
```

**إذا لم يكن Git مهيأ:**

```bash
git init
git remote add origin https://github.com/alabasi2025/alabasi-php.git
```

---

### 5️⃣ **سحب التحديثات من GitHub**

```bash
git fetch origin master
git reset --hard origin/master
```

**أو:**

```bash
git pull origin master --force
```

---

### 6️⃣ **تحديث ملف `.env`**

افتح ملف `.env`:

```bash
nano .env
```

**حدّث إعدادات قاعدة البيانات:**

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u306850950_alabasi_new1
DB_USERNAME=u306850950_alabasi1
DB_PASSWORD=Alabasi@2025
```

*(استخدم إحدى القواعد الجديدة الثلاث)*

احفظ بالضغط على `Ctrl+X` ثم `Y` ثم `Enter`.

---

### 7️⃣ **تثبيت الحزم**

```bash
composer install --no-dev --optimize-autoloader
```

---

### 8️⃣ **تشغيل Migrations**

```bash
php artisan migrate --force
```

---

### 9️⃣ **مسح وتحسين Cache**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

### 🔟 **نشر Telescope (اختياري)**

```bash
php artisan telescope:install
php artisan migrate
php artisan telescope:publish
```

---

### 1️⃣1️⃣ **ضبط الصلاحيات**

```bash
chmod -R 755 storage bootstrap/cache
chown -R u306850950:u306850950 storage bootstrap/cache
```

---

### 1️⃣2️⃣ **اختبار الموقع**

افتح المتصفح واذهب إلى:
- https://alabasi.es
- https://alabasi.es/admin/dashboard

---

## 🎯 **البديل: استخدام Git في Hostinger**

إذا كان Git مفعّلاً في لوحة التحكم:

1. اذهب إلى **متقدم** > **جيت**
2. أضف المستودع: `https://github.com/alabasi2025/alabasi-php.git`
3. اختر Branch: `master`
4. اضغط **Pull** أو **Deploy**

---

## ⚠️ **ملاحظات مهمة**

### **قواعد البيانات:**
- **القديمة:** `u306850950_alabasi_main` (لا تستخدمها)
- **الجديدة 1:** `u306850950_alabasi_new1` ✅
- **الجديدة 2:** `u306850950_alabasi_new2` ✅
- **الجديدة 3:** `u306850950_alabasi_new3` ✅

### **Middleware:**
قبل النشر، تأكد من إعادة `auth` middleware في `routes/admin.php`:

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // ...
});
```

### **النسخ الاحتياطي:**
قبل أي تحديث، اعمل نسخة احتياطية من:
- قاعدة البيانات القديمة
- ملفات الموقع

---

## 📞 **الدعم**

إذا واجهت أي مشاكل:
1. تحقق من سجل الأخطاء: `storage/logs/laravel.log`
2. تحقق من صلاحيات الملفات
3. تأكد من إعدادات `.env`

---

**تم بحمد الله**  
**Manus AI - 19 نوفمبر 2025**
