<?php
/**
 * نظام الأباسي المحاسبي الموحد
 * Helper Functions
 */

// ============================================
// دوال الأمان
// Security Functions
// ============================================

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// دوال الجلسة
// Session Functions
// ============================================

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
        exit;
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_name() {
    return $_SESSION['user_name'] ?? '';
}

function get_user_role() {
    return $_SESSION['user_role'] ?? '';
}

function has_permission($permission) {
    return in_array($permission, $_SESSION['permissions'] ?? []);
}

// ============================================
// دوال التنقل
// Navigation Functions
// ============================================

function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "<script>window.location.href='" . $url . "';</script>";
    }
    exit;
}

function current_url() {
    return $_SERVER['REQUEST_URI'];
}

function base_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function assets_url($path = '') {
    return ASSETS_URL . ltrim($path, '/');
}

// ============================================
// دوال التنسيق
// Formatting Functions
// ============================================

function format_number($number, $decimals = DECIMAL_PLACES) {
    return number_format($number, $decimals, '.', ',');
}

function format_currency($amount, $symbol = CURRENCY_SYMBOL) {
    return format_number($amount) . ' ' . $symbol;
}

function format_date($date, $format = DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = DATETIME_FORMAT) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    return date($format, strtotime($datetime));
}

function number_to_arabic_words($number) {
    $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
    $tens = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    $hundreds = ['', 'مائة', 'مئتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];
    
    if ($number == 0) return 'صفر';
    
    $result = '';
    
    // Thousands
    if ($number >= 1000) {
        $thousands = floor($number / 1000);
        if ($thousands == 1) {
            $result .= 'ألف ';
        } elseif ($thousands == 2) {
            $result .= 'ألفان ';
        } elseif ($thousands <= 10) {
            $result .= $ones[$thousands] . ' آلاف ';
        } else {
            $result .= $ones[$thousands] . ' ألف ';
        }
        $number %= 1000;
    }
    
    // Hundreds
    if ($number >= 100) {
        $result .= $hundreds[floor($number / 100)] . ' ';
        $number %= 100;
    }
    
    // Tens and ones
    if ($number >= 20) {
        $result .= $tens[floor($number / 10)] . ' ';
        $number %= 10;
    } elseif ($number >= 11) {
        $special = ['', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 
                    'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
        $result .= $special[$number - 10] . ' ';
        $number = 0;
    } elseif ($number == 10) {
        $result .= 'عشرة ';
        $number = 0;
    }
    
    if ($number > 0) {
        $result .= $ones[$number] . ' ';
    }
    
    return trim($result);
}

// ============================================
// دوال الرسائل
// Message Functions
// ============================================

function set_message($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type
    ];
}

function get_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function success_message($message) {
    set_message($message, 'success');
}

function error_message($message) {
    set_message($message, 'error');
}

function warning_message($message) {
    set_message($message, 'warning');
}

function info_message($message) {
    set_message($message, 'info');
}

// ============================================
// دوال التحقق
// Validation Functions
// ============================================

function validate_required($value, $field_name) {
    if (empty(trim($value))) {
        return "حقل {$field_name} مطلوب";
    }
    return true;
}

function validate_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "البريد الإلكتروني غير صحيح";
    }
    return true;
}

function validate_number($value, $field_name) {
    if (!is_numeric($value)) {
        return "حقل {$field_name} يجب أن يكون رقماً";
    }
    return true;
}

function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// ============================================
// دوال الملفات
// File Functions
// ============================================

function upload_file($file, $allowed_extensions = ALLOWED_EXTENSIONS) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطأ في رفع الملف'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح'];
    }
    
    $filename = uniqid() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename, 'path' => $destination];
    }
    
    return ['success' => false, 'message' => 'فشل حفظ الملف'];
}

function delete_file($filename) {
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// ============================================
// دوال السجلات
// Logging Functions
// ============================================

function log_activity($action, $table, $record_id, $details = []) {
    try {
        $db = Database::getInstance();
        $data = [
            'operationType' => $action,
            'tableName' => $table,
            'recordId' => $record_id,
            'action' => $action,
            'newData' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'userId' => get_user_id(),
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? '',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        $db->insert('operation_history', $data);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// ============================================
// دوال الترقيم التلقائي
// Auto-numbering Functions
// ============================================

function get_next_voucher_number($type, $unitId, $prefix) {
    $db = Database::getInstance();
    $year = date('Y');
    
    $db->beginTransaction();
    try {
        $sequence = $db->fetchOne(
            "SELECT * FROM voucher_sequences 
             WHERE voucherType = ? AND year = ? AND unitId = ? FOR UPDATE",
            [$type, $year, $unitId]
        );
        
        if (!$sequence) {
            $db->insert('voucher_sequences', [
                'voucherType' => $type,
                'prefix' => $prefix,
                'currentNumber' => 1,
                'year' => $year,
                'unitId' => $unitId
            ]);
            $number = 1;
        } else {
            $number = $sequence['currentNumber'] + 1;
            $db->update(
                'voucher_sequences',
                ['currentNumber' => $number],
                'id = ?',
                [$sequence['id']]
            );
        }
        
        $db->commit();
        
        return $prefix . '-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

// ============================================
// دوال JSON
// JSON Functions
// ============================================

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function json_success($message = 'تمت العملية بنجاح', $data = []) {
    json_response([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

function json_error($message = 'حدث خطأ', $errors = []) {
    json_response([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], 400);
}

// ============================================
// دوال أخرى
// Other Functions
// ============================================

function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($die) die();
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length / 2));
}
