<?php
/**
 * تثبيت نظام السندات والقيود الذكية
 * Install Smart Vouchers & Journal Entries System
 */

require_once 'includes/db.php';

$results = [];
$hasErrors = false;

try {
    // قراءة ملف SQL
    $sqlFile = __DIR__ . '/install_smart_vouchers.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception('ملف SQL غير موجود');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // تقسيم الاستعلامات
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    // تنفيذ كل استعلام
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
            
            // استخراج اسم الجدول من الاستعلام
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                $results[] = [
                    'success' => true,
                    'message' => "✅ تم إنشاء جدول: $tableName"
                ];
            } elseif (preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                $results[] = [
                    'success' => true,
                    'message' => "✅ تم تعديل جدول: $tableName"
                ];
            } elseif (preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                $results[] = [
                    'success' => true,
                    'message' => "✅ تم إدراج بيانات في: $tableName"
                ];
            } else {
                $results[] = [
                    'success' => true,
                    'message' => "✅ تم تنفيذ استعلام #" . ($index + 1)
                ];
            }
        } catch (PDOException $e) {
            // تجاهل أخطاء "already exists" و "Duplicate column"
            if (strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate column') !== false ||
                strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $results[] = [
                    'success' => true,
                    'message' => "⚠️ العنصر موجود مسبقاً (تم التجاوز)"
                ];
            } else {
                $hasErrors = true;
                $results[] = [
                    'success' => false,
                    'message' => "❌ خطأ: " . $e->getMessage()
                ];
            }
        }
    }
    
    // التحقق من الجداول المنشأة
    $tables = ['receipt_vouchers', 'payment_vouchers', 'journal_details', 'voucher_sequences'];
    $results[] = ['success' => true, 'message' => "\n--- التحقق من الجداول ---"];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $results[] = [
                'success' => true,
                'message' => "✅ جدول $table موجود ($count صف)"
            ];
        } else {
            $hasErrors = true;
            $results[] = [
                'success' => false,
                'message' => "❌ جدول $table غير موجود"
            ];
        }
    }
    
} catch (Exception $e) {
    $hasErrors = true;
    $results[] = [
        'success' => false,
        'message' => "❌ خطأ عام: " . $e->getMessage()
    ];
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت نظام السندات الذكية</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .install-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .result-item {
            padding: 12px 20px;
            margin: 8px 0;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .result-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .result-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: <?php echo $hasErrors ? '#fff3cd' : '#d1ecf1'; ?>;
            border-radius: 8px;
            text-align: center;
        }
        .summary h2 {
            margin: 0 0 10px 0;
            color: <?php echo $hasErrors ? '#856404' : '#0c5460'; ?>;
        }
        .btn-continue {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .btn-continue:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1 style="text-align: center; color: #333; margin-bottom: 30px;">
            🚀 تثبيت نظام السندات والقيود الذكية
        </h1>
        
        <div class="results">
            <?php foreach ($results as $result): ?>
                <div class="result-item <?php echo $result['success'] ? 'result-success' : 'result-error'; ?>">
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="summary">
            <?php if (!$hasErrors): ?>
                <h2>✅ تم التثبيت بنجاح!</h2>
                <p>تم إنشاء جميع الجداول المطلوبة بنجاح</p>
                <p>يمكنك الآن استخدام نظام السندات والقيود الذكية</p>
                <a href="receipt-vouchers.php" class="btn-continue">📥 سندات القبض</a>
                <a href="payment-vouchers.php" class="btn-continue">📤 سندات الصرف</a>
                <a href="journals.php" class="btn-continue">📝 القيود اليومية</a>
            <?php else: ?>
                <h2>⚠️ التثبيت مكتمل مع بعض التحذيرات</h2>
                <p>يرجى مراجعة الأخطاء أعلاه</p>
                <a href="install-smart-vouchers.php" class="btn-continue">🔄 إعادة المحاولة</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
