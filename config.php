<?php
/**
 * ملف التكوين الرئيسي لنظام الأباسي المحاسبي
 * Main Configuration File for Alabasi Accounting System
 * 
 * @package AlAbasiAccounting
 * @version 1.0.0
 * @author alabasi2025
 */

// منع الوصول المباشر
if (!defined('ALABASI_SYSTEM')) {
    die('Direct access not permitted');
}

// ===================================
// إعدادات قاعدة البيانات
// Database Configuration
// ===================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'u306850950_alabasi');
define('DB_USER', 'u306850950_alabasi');
define('DB_PASS', 'Alabasi@2025#Secure');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// ===================================
// إعدادات النظام
// System Configuration
// ===================================

define('SITE_URL', 'https://alabasi.es');
define('SITE_NAME', 'نظام الأباسي المحاسبي');
define('SITE_NAME_EN', 'Al-Abasi Accounting System');
define('ADMIN_EMAIL', 'admin@alabasi.es');

// المسارات
define('ROOT_PATH', dirname(__FILE__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// ===================================
// إعدادات الأمان
// Security Configuration
// ===================================

define('SESSION_LIFETIME', 3600); // ساعة واحدة
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 دقيقة

// مفتاح التشفير (يجب تغييره في الإنتاج)
define('ENCRYPTION_KEY', 'AlAbasiSecureKey2025!@#$%^&*()');

// ===================================
// إعدادات اللغة والمنطقة
// Language & Locale Configuration
// ===================================

define('DEFAULT_LANGUAGE', 'ar');
define('DEFAULT_TIMEZONE', 'Asia/Riyadh');
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// ===================================
// إعدادات العرض
// Display Configuration
// ===================================

define('ITEMS_PER_PAGE', 20);
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx');

// ===================================
// إعدادات البريد الإلكتروني
// Email Configuration
// ===================================

define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@alabasi.es');
define('SMTP_PASS', ''); // يجب تعيينها في ملف .env
define('SMTP_SECURE', 'tls');
define('MAIL_FROM_NAME', 'نظام الأباسي المحاسبي');

// ===================================
// إعدادات التطوير
// Development Configuration
// ===================================

define('DEBUG_MODE', false); // تعيين إلى false في الإنتاج
define('DISPLAY_ERRORS', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', LOGS_PATH . '/error.log');

// ===================================
// إعدادات النسخ الاحتياطي
// Backup Configuration
// ===================================

define('BACKUP_PATH', ROOT_PATH . '/backups');
define('AUTO_BACKUP', true);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly

// ===================================
// إعدادات API
// API Configuration
// ===================================

define('API_ENABLED', true);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // طلبات في الساعة

// ===================================
// تهيئة المنطقة الزمنية
// Set Timezone
// ===================================

date_default_timezone_set(DEFAULT_TIMEZONE);

// ===================================
// تهيئة معالجة الأخطاء
// Error Handling Setup
// ===================================

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}

if (LOG_ERRORS) {
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// ===================================
// تحميل ملف البيئة (إن وجد)
// Load Environment Variables
// ===================================

if (file_exists(ROOT_PATH . '/.env')) {
    $env = parse_ini_file(ROOT_PATH . '/.env');
    foreach ($env as $key => $value) {
        if (!defined($key)) {
            define($key, $value);
        }
    }
}

// ===================================
// دوال مساعدة
// Helper Functions
// ===================================

/**
 * الحصول على قيمة من التكوين
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * التحقق من وضع التطوير
 */
function is_debug() {
    return defined('DEBUG_MODE') && DEBUG_MODE === true;
}

/**
 * تسجيل رسالة خطأ
 */
function log_error($message, $context = []) {
    if (LOG_ERRORS) {
        $timestamp = date(DATETIME_FORMAT);
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$timestamp] $message $contextStr" . PHP_EOL;
        error_log($logMessage, 3, ERROR_LOG_FILE);
    }
}

/**
 * تسجيل نشاط المستخدم
 */
function log_activity($user_id, $action, $details = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $action, $details, $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        log_error('Failed to log activity: ' . $e->getMessage());
    }
}

?>
