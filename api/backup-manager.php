<?php
/**
 * API إدارة النسخ الاحتياطي
 * Backup Manager API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'create_manual_backup':
            createManualBackup($pdo, $userId, $input);
            break;
            
        case 'add_schedule':
            addSchedule($pdo, $userId, $input);
            break;
            
        case 'delete_schedule':
            deleteSchedule($pdo, $input);
            break;
            
        case 'toggle_schedule':
            toggleSchedule($pdo, $input);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * إنشاء نسخة احتياطية يدوية
 */
function createManualBackup($pdo, $userId, $input) {
    $backupPath = $input['backupPath'] ?? '';
    
    if (empty($backupPath)) {
        echo json_encode(['success' => false, 'message' => 'مسار النسخة الاحتياطية مطلوب']);
        return;
    }
    
    // تنظيف المسار
    $backupPath = rtrim($backupPath, '\\/');
    
    // إنشاء اسم الملف
    $timestamp = date('Y-m-d_H-i-s');
    $fileName = "alabasi_backup_{$timestamp}.sql";
    $fullPath = $backupPath . DIRECTORY_SEPARATOR . $fileName;
    
    // الحصول على إعدادات قاعدة البيانات من ملف db.php
    $dbConfig = getDatabaseConfig();
    
    // إنشاء النسخة الاحتياطية باستخدام mysqldump
    $result = createBackupFile($dbConfig, $fullPath);
    
    if ($result['success']) {
        // حفظ السجل في قاعدة البيانات
        $stmt = $pdo->prepare("INSERT INTO backup_logs 
            (fileName, filePath, fileSize, backupType, status, createdBy) 
            VALUES (?, ?, ?, 'manual', 'success', ?)");
        $stmt->execute([
            $fileName,
            $fullPath,
            $result['fileSize'],
            $userId
        ]);
        
        echo json_encode([
            'success' => true,
            'fileName' => $fileName,
            'filePath' => $fullPath,
            'fileSize' => $result['fileSize']
        ]);
    } else {
        // حفظ سجل الفشل
        $stmt = $pdo->prepare("INSERT INTO backup_logs 
            (fileName, filePath, fileSize, backupType, status, errorMessage, createdBy) 
            VALUES (?, ?, 0, 'manual', 'failed', ?, ?)");
        $stmt->execute([
            $fileName,
            $fullPath,
            $result['error'],
            $userId
        ]);
        
        echo json_encode([
            'success' => false,
            'message' => $result['error']
        ]);
    }
}

/**
 * إضافة جدول نسخ احتياطي تلقائي
 */
function addSchedule($pdo, $userId, $input) {
    $name = $input['name'] ?? '';
    $backupPath = $input['backupPath'] ?? '';
    $scheduleTime = $input['scheduleTime'] ?? '';
    $frequency = $input['frequency'] ?? 'daily';
    $isActive = $input['isActive'] ?? true;
    
    if (empty($name) || empty($backupPath) || empty($scheduleTime)) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
        return;
    }
    
    // حساب موعد التنفيذ التالي
    $nextRun = calculateNextRun($scheduleTime, $frequency);
    
    $stmt = $pdo->prepare("INSERT INTO backup_schedules 
        (name, backupPath, scheduleTime, frequency, isActive, nextRun, createdBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $name,
        $backupPath,
        $scheduleTime,
        $frequency,
        $isActive ? 1 : 0,
        $nextRun,
        $userId
    ]);
    
    echo json_encode(['success' => true, 'message' => 'تم إضافة الجدول بنجاح']);
}

/**
 * حذف جدول
 */
function deleteSchedule($pdo, $input) {
    $id = $input['id'] ?? 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM backup_schedules WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
}

/**
 * تفعيل/تعطيل جدول
 */
function toggleSchedule($pdo, $input) {
    $id = $input['id'] ?? 0;
    $isActive = $input['isActive'] ?? false;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE backup_schedules SET isActive = ? WHERE id = ?");
    $stmt->execute([$isActive ? 1 : 0, $id]);
    
    echo json_encode(['success' => true, 'message' => 'تم التحديث بنجاح']);
}

/**
 * الحصول على إعدادات قاعدة البيانات
 */
function getDatabaseConfig() {
    // استخراج معلومات الاتصال من DSN
    global $pdo;
    
    // القيم الافتراضية
    $config = [
        'host' => 'localhost',
        'dbname' => 'alabasi_unified',
        'user' => 'root',
        'password' => ''
    ];
    
    return $config;
}

/**
 * إنشاء ملف النسخة الاحتياطية
 */
function createBackupFile($dbConfig, $fullPath) {
    // مسار mysqldump (افتراضي لـ XAMPP على Windows)
    $mysqldumpPath = 'D:\\XAMPP\\mysql\\bin\\mysqldump.exe';
    
    // إذا كان على Linux
    if (PHP_OS_FAMILY !== 'Windows') {
        $mysqldumpPath = 'mysqldump';
    }
    
    // التحقق من وجود mysqldump
    if (PHP_OS_FAMILY === 'Windows' && !file_exists($mysqldumpPath)) {
        // محاولة البحث في مسارات أخرى
        $possiblePaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\XAMPP\\mysql\\bin\\mysqldump.exe',
            'D:\\xampp\\mysql\\bin\\mysqldump.exe'
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $mysqldumpPath = $path;
                break;
            }
        }
    }
    
    // بناء الأمر
    $command = sprintf(
        '"%s" --user=%s --password=%s --host=%s --single-transaction --routines --triggers --events --add-drop-table --databases %s --result-file="%s" 2>&1',
        $mysqldumpPath,
        $dbConfig['user'],
        $dbConfig['password'],
        $dbConfig['host'],
        $dbConfig['dbname'],
        $fullPath
    );
    
    // تنفيذ الأمر
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($fullPath)) {
        $fileSize = filesize($fullPath);
        return [
            'success' => true,
            'fileSize' => $fileSize
        ];
    } else {
        $error = implode("\n", $output);
        return [
            'success' => false,
            'error' => $error ?: 'فشل إنشاء النسخة الاحتياطية'
        ];
    }
}

/**
 * حساب موعد التنفيذ التالي
 */
function calculateNextRun($scheduleTime, $frequency) {
    $now = new DateTime();
    $scheduled = new DateTime($scheduleTime);
    
    // تعيين الوقت لليوم الحالي
    $nextRun = new DateTime();
    $nextRun->setTime($scheduled->format('H'), $scheduled->format('i'));
    
    // إذا كان الوقت قد مضى اليوم، انتقل للغد
    if ($nextRun <= $now) {
        switch ($frequency) {
            case 'daily':
                $nextRun->modify('+1 day');
                break;
            case 'weekly':
                $nextRun->modify('+1 week');
                break;
            case 'monthly':
                $nextRun->modify('+1 month');
                break;
        }
    }
    
    return $nextRun->format('Y-m-d H:i:s');
}
