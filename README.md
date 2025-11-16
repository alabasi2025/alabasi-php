# 🧮 نظام الأباسي المحاسبي | Al-Abasi Accounting System

<div align="center">

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Status](https://img.shields.io/badge/status-active-success.svg)

**نظام محاسبي ذكي متكامل مع تحليل مدعوم بالذكاء الاصطناعي**

[العربية](#ar) | [English](#en) | [التوثيق](https://docs.alabasi.es) | [Demo](https://demo.alabasi.es)

</div>

---

## 📋 جدول المحتويات

- [نظرة عامة](#-نظرة-عامة)
- [المميزات](#-المميزات)
- [المتطلبات](#-المتطلبات)
- [التثبيت](#-التثبيت)
- [التكوين](#-التكوين)
- [الاستخدام](#-الاستخدام)
- [التكامل مع Manous API](#-التكامل-مع-manous-api)
- [النشر](#-النشر)
- [المساهمة](#-المساهمة)
- [الترخيص](#-الترخيص)

---

## 🌟 نظرة عامة

**نظام الأباسي المحاسبي** هو نظام محاسبة شامل ومتطور مصمم خصيصاً للشركات والمؤسسات العربية. يجمع النظام بين الوظائف المحاسبية التقليدية وقوة الذكاء الاصطناعي لتقديم تحليلات ذكية وتقارير متقدمة.

### ✨ ما يميز النظام

- 🤖 **ذكاء اصطناعي متقدم**: تحليل ذكي للبيانات المحاسبية باستخدام Manous API
- 📊 **تقارير تفاعلية**: تقارير مالية شاملة مع رسوم بيانية تفاعلية
- 🔒 **أمان عالي**: حماية متقدمة للبيانات المالية الحساسة
- 🌐 **واجهة عربية**: واجهة مستخدم عربية بالكامل مع دعم RTL
- 📱 **متجاوب**: يعمل بسلاسة على جميع الأجهزة

---

## 🚀 المميزات

### المحاسبة الأساسية
- ✅ إدارة الحسابات والدليل المحاسبي
- ✅ القيود المحاسبية (يدوية وتلقائية)
- ✅ دفتر اليومية والأستاذ العام
- ✅ ميزان المراجعة
- ✅ القوائم المالية (الميزانية، الدخل، التدفقات النقدية)

### الإدارة المالية
- 💰 إدارة الفواتير (مبيعات ومشتريات)
- 💳 إدارة المدفوعات والمقبوضات
- 🏦 إدارة الحسابات البنكية
- 📈 تتبع المصروفات والإيرادات
- 💵 إدارة العملات المتعددة

### التحليل الذكي (Manous AI)
- 🧠 تحليل تلقائي للبيانات المالية
- 📊 اقتراح تقارير مخصصة
- 🔍 اكتشاف الأنماط الغير عادية
- 📈 التنبؤ بالتدفقات النقدية
- 💡 توصيات ذكية لتحسين الأداء المالي

### التقارير والتحليلات
- 📑 تقارير مالية معيارية
- 📊 تحليل الربحية والسيولة
- 📈 مؤشرات الأداء المالي (KPIs)
- 🎯 تقارير مخصصة حسب الحاجة
- 📤 تصدير بصيغ متعددة (PDF, Excel, CSV)

### الإدارة والأمان
- 👥 إدارة المستخدمين والصلاحيات
- 🔐 مصادقة ثنائية (2FA)
- 📝 سجل النشاطات والتدقيق
- 🔄 نسخ احتياطي تلقائي
- 🛡️ تشفير البيانات الحساسة

---

## 💻 المتطلبات

### متطلبات الخادم
- PHP >= 8.2
- MySQL >= 8.0 أو MariaDB >= 10.6
- Apache 2.4+ أو Nginx 1.18+
- SSL Certificate (موصى به)

### امتدادات PHP المطلوبة
```
- mbstring
- xml
- ctype
- json
- pdo
- pdo_mysql
- openssl
- curl
- gd
- zip
```

### أدوات التطوير (اختيارية)
- Composer
- Git
- Node.js & npm (للأصول الأمامية)

---

## 📦 التثبيت

### 1. استنساخ المستودع

```bash
# عبر SSH
git clone git@github.com:alabasi2025/alabasi-accounting-system.git

# أو عبر HTTPS
git clone https://github.com/alabasi2025/alabasi-accounting-system.git

cd alabasi-accounting-system
```

### 2. تثبيت الاعتماديات

```bash
# إذا كنت تستخدم Composer
composer install --no-dev --optimize-autoloader
```

### 3. إعداد قاعدة البيانات

```bash
# إنشاء قاعدة البيانات
mysql -u root -p -e "CREATE DATABASE alabasi_accounting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# استيراد البنية
mysql -u root -p alabasi_accounting < database/schema.sql

# استيراد البيانات الأولية
mysql -u root -p alabasi_accounting < database/seed.sql
```

### 4. تكوين النظام

```bash
# نسخ ملف التكوين
cp config.example.php config.php

# تحرير الإعدادات
nano config.php
```

### 5. ضبط الصلاحيات

```bash
# صلاحيات الملفات
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# صلاحيات المجلدات القابلة للكتابة
chmod 777 logs uploads cache sessions backups

# حماية ملفات التكوين
chmod 600 config.php
```

---

## ⚙️ التكوين

### ملف config.php

```php
<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'alabasi_accounting');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// إعدادات النظام
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'نظام الأباسي المحاسبي');

// مفتاح التشفير (يجب تغييره)
define('ENCRYPTION_KEY', 'your-secret-key-here');
?>
```

### ملف .env (اختياري)

```env
# قاعدة البيانات
DB_HOST=localhost
DB_NAME=alabasi_accounting
DB_USER=your_username
DB_PASS=your_password

# Manous API
MANOUS_API_KEY=your_manous_api_key_here

# البريد الإلكتروني
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your_smtp_password

# الأمان
ENCRYPTION_KEY=your-encryption-key
SESSION_LIFETIME=3600
```

---

## 🤖 التكامل مع Manous API

### إعداد Manous API

1. احصل على مفتاح API من [Manus.im](https://manus.im)
2. أضف المفتاح إلى ملف `.env`:

```env
MANOUS_API_KEY=your_api_key_here
```

### استخدام Manous API

```php
<?php
require_once 'manous.php';

// إنشاء instance
$manous = new ManousAPI(getenv('MANOUS_API_KEY'));

// تحليل البيانات المحاسبية
$result = $manous->analyzeAccountingData($accountingData, 'profitability');

// اقتراح التقارير
$reports = $manous->suggestReports($data);

// التنبؤ بالتدفقات النقدية
$forecast = $manous->predictCashFlow($historicalData, 6);

// طرح سؤال محاسبي
$answer = $manous->askAccountingQuestion('ما هو الفرق بين الأصول المتداولة والثابتة؟');
?>
```

### أمثلة متقدمة

```php
<?php
// تحليل القيود المحاسبية
$entries = [
    ['date' => '2025-01-15', 'debit' => 1000, 'credit' => 1000, 'description' => 'مشتريات بضاعة'],
    // المزيد من القيود...
];
$analysis = $manous->analyzeJournalEntries($entries);

// اكتشاف الأنماط الغير عادية
$anomalies = $manous->detectAnomalies($transactions);

// توليد تقرير مالي ذكي
$report = $manous->generateFinancialReport('income_statement', $data, 'Q1 2025');
?>
```

---

## 🚀 النشر

### النشر على Hostinger

#### الطريقة 1: عبر Git Integration

1. اذهب إلى Hostinger → Advanced → Git
2. أضف المستودع:
   - **Repository URL**: `git@github.com:alabasi2025/alabasi-accounting-system.git`
   - **Branch**: `main`
   - **Deploy Path**: `/public_html`
3. أضف مفتاح SSH إلى GitHub
4. انقر على **Deploy**

#### الطريقة 2: عبر GitHub Actions

1. أضف Secrets إلى GitHub Repository:
   ```
   FTP_SERVER=ftp.yourdomain.com
   FTP_USERNAME=your_username
   FTP_PASSWORD=your_password
   SSH_HOST=your_host
   SSH_USERNAME=your_ssh_user
   SSH_PRIVATE_KEY=your_private_key
   SSH_PORT=22
   ```

2. Push إلى branch `main`:
   ```bash
   git add .
   git commit -m "Deploy to production"
   git push origin main
   ```

3. سيتم النشر تلقائياً عبر GitHub Actions

### النشر على VPS

```bash
# الاتصال بالخادم
ssh user@your-server-ip

# استنساخ المستودع
cd /var/www
git clone git@github.com:alabasi2025/alabasi-accounting-system.git alabasi

# إعداد النظام
cd alabasi
composer install --no-dev
cp config.example.php config.php
nano config.php

# ضبط الصلاحيات
chown -R www-data:www-data .
chmod -R 755 .
chmod 777 logs uploads cache sessions backups

# إعداد Apache/Nginx
# ... (حسب الخادم)
```

---

## 📚 الاستخدام

### تسجيل الدخول الأول

```
URL: https://yourdomain.com/login.php
Username: admin
Password: admin123
```

⚠️ **مهم**: غيّر كلمة المرور فوراً بعد أول تسجيل دخول!

### إنشاء قيد محاسبي

```php
<?php
// مثال على إنشاء قيد محاسبي
$entry = [
    'date' => '2025-01-15',
    'description' => 'مشتريات بضاعة نقداً',
    'entries' => [
        ['account_id' => 101, 'debit' => 5000, 'credit' => 0], // المشتريات
        ['account_id' => 201, 'debit' => 0, 'credit' => 5000]  // الصندوق
    ]
];

$journal->createEntry($entry);
?>
```

### توليد تقرير مالي

```php
<?php
// توليد قائمة الدخل
$report = new IncomeStatement();
$report->setPeriod('2025-01-01', '2025-12-31');
$result = $report->generate();

// تصدير إلى PDF
$report->exportToPDF('income_statement_2025.pdf');
?>
```

---

## 🤝 المساهمة

نرحب بمساهماتكم! يرجى اتباع الخطوات التالية:

1. Fork المستودع
2. إنشاء branch جديد (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push إلى Branch (`git push origin feature/amazing-feature`)
5. فتح Pull Request

### معايير الكود

- اتبع PSR-12 لكتابة PHP
- اكتب تعليقات واضحة بالعربية والإنجليزية
- اختبر الكود قبل الـ commit
- حدّث التوثيق عند الحاجة

---

## 📄 الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).

---

## 📞 الدعم والتواصل

- 📧 البريد الإلكتروني: support@alabasi.es
- 🌐 الموقع: [alabasi.es](https://alabasi.es)
- 📚 التوثيق: [docs.alabasi.es](https://docs.alabasi.es)
- 🐛 الإبلاغ عن مشاكل: [GitHub Issues](https://github.com/alabasi2025/alabasi-accounting-system/issues)

---

## 🙏 شكر وتقدير

- [Manous AI](https://manus.im) - للتحليل الذكي
- [Hostinger](https://hostinger.com) - للاستضافة
- المجتمع العربي المفتوح المصدر

---

<div align="center">

**صُنع بـ ❤️ في السعودية**

© 2025 نظام الأباسي المحاسبي. جميع الحقوق محفوظة.

</div>
