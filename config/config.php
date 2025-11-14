<?php
/**
 * نظام الأباسي المحاسبي الموحد الشامل
 * Alabasi Unified Accounting System
 * 
 * ملف التكوين الرئيسي
 * Main Configuration File
 */

// منع الوصول المباشر
if (!defined('ALABASI_SYSTEM')) {
    die('Direct access not permitted');
}

// ============================================
// إعدادات قاعدة البيانات
// Database Settings
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'alabasi_unified_complete');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// إعدادات النظام
// System Settings
// ============================================
define('SYSTEM_NAME_AR', 'نظام الأباسي المحاسبي الموحد');
define('SYSTEM_NAME_EN', 'Alabasi Unified Accounting System');
define('SYSTEM_VERSION', '2.0.0');
define('SYSTEM_TIMEZONE', 'Asia/Baghdad');

// ============================================
// إعدادات الجلسة
// Session Settings
// ============================================
define('SESSION_NAME', 'ALABASI_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_PATH', '/');
define('SESSION_SECURE', false); // Set to true for HTTPS
define('SESSION_HTTPONLY', true);

// ============================================
// إعدادات الأمان
// Security Settings
// ============================================
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// ============================================
// إعدادات الملفات
// File Settings
// ============================================
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// ============================================
// إعدادات التطبيق
// Application Settings
// ============================================
define('BASE_URL', 'http://localhost/alabasi/unified-system/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('DEFAULT_LANGUAGE', 'ar');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('CURRENCY_SYMBOL', 'د.ع');
define('DECIMAL_PLACES', 2);

// ============================================
// إعدادات الترقيم
// Numbering Settings
// ============================================
define('RECEIPT_VOUCHER_PREFIX', 'RV');
define('PAYMENT_VOUCHER_PREFIX', 'PV');
define('JOURNAL_ENTRY_PREFIX', 'JE');
define('INVOICE_PREFIX', 'INV');
define('PURCHASE_ORDER_PREFIX', 'PO');

// ============================================
// إعدادات التقارير
// Report Settings
// ============================================
define('REPORT_ITEMS_PER_PAGE', 50);
define('EXPORT_FORMATS', ['pdf', 'excel', 'csv']);

// ============================================
// إعدادات السجلات
// Logging Settings
// ============================================
define('LOG_DIR', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'info'); // debug, info, warning, error
define('LOG_MAX_FILES', 30);

// ============================================
// إعدادات البريد الإلكتروني
// Email Settings
// ============================================
define('MAIL_FROM', 'noreply@alabasi.com');
define('MAIL_FROM_NAME', SYSTEM_NAME_EN);
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USER', '');
define('MAIL_SMTP_PASS', '');
define('MAIL_SMTP_SECURE', 'tls');

// ============================================
// وضع التطوير
// Development Mode
// ============================================
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
define('ERROR_REPORTING_LEVEL', E_ALL);

// تطبيق الإعدادات
if (DEBUG_MODE) {
    error_reporting(ERROR_REPORTING_LEVEL);
    ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// تعيين المنطقة الزمنية
date_default_timezone_set(SYSTEM_TIMEZONE);

// إنشاء المجلدات المطلوبة
$required_dirs = [UPLOAD_DIR, LOG_DIR];
foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
