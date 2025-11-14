<?php
/**
 * تثبيت نظام التحديثات تلقائياً
 * Auto Install Updates System
 */

// منع الوصول المباشر من غير localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('Access denied. This script can only be run from localhost.');
}

require_once 'includes/db.php';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت نظام التحديثات</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-right: 4px solid #667eea;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
        }
        pre {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            direction: ltr;
            text-align: left;
            font-size: 12px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .progress {
            background: #e9ecef;
            border-radius: 10px;
            height: 30px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 تثبيت نظام التحديثات</h1>
        
        <?php
        if (!isset($_POST['install'])) {
            // عرض معلومات قبل التثبيت
            ?>
            <div class="step">
                <h3>📋 ما الذي سيتم تثبيته:</h3>
                <ul>
                    <li>✅ جدول <code>auto_update_settings</code> - إعدادات التحديث التلقائي</li>
                    <li>✅ جدول <code>system_updates</code> - سجل التحديثات</li>
                    <li>✅ جدول <code>update_files_log</code> - تفاصيل الملفات</li>
                    <li>✅ جدول <code>update_notifications</code> - الإشعارات</li>
                    <li>✅ Views مفيدة للاستعلامات</li>
                    <li>✅ بيانات افتراضية</li>
                </ul>
            </div>
            
            <div class="step">
                <h3>⚠️ تحذير:</h3>
                <p class="warning">سيتم حذف الجداول القديمة إذا كانت موجودة وإعادة إنشائها من جديد!</p>
                <p class="info">تأكد من عمل نسخة احتياطية لقاعدة البيانات قبل المتابعة.</p>
            </div>
            
            <div style="text-align: center;">
                <form method="POST">
                    <button type="submit" name="install" class="btn">
                        🚀 ابدأ التثبيت
                    </button>
                    <a href="backup-manager.php" class="btn" style="background: #6c757d;">
                        ❌ إلغاء
                    </a>
                </form>
            </div>
            <?php
        } else {
            // تنفيذ التثبيت
            echo '<div class="progress"><div class="progress-bar" style="width: 0%" id="progressBar">0%</div></div>';
            
            $steps = [];
            $totalSteps = 0;
            $completedSteps = 0;
            
            try {
                // قراءة ملف SQL
                $sqlFile = __DIR__ . '/fix_updates_system.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception('ملف fix_updates_system.sql غير موجود!');
                }
                
                $sql = file_get_contents($sqlFile);
                
                // تقسيم الاستعلامات
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && 
                               !preg_match('/^--/', $stmt) && 
                               $stmt !== 'SELECT \'تم إنشاء جداول نظام التحديثات بنجاح!\' AS message' &&
                               $stmt !== 'SELECT * FROM auto_update_settings';
                    }
                );
                
                $totalSteps = count($statements);
                
                echo '<script>document.getElementById("progressBar").style.width = "10%"; document.getElementById("progressBar").textContent = "10%";</script>';
                flush();
                
                // تنفيذ كل استعلام
                foreach ($statements as $index => $statement) {
                    $statement = trim($statement);
                    if (empty($statement)) continue;
                    
                    try {
                        $pdo->exec($statement);
                        $completedSteps++;
                        
                        // تحديد نوع العملية
                        if (preg_match('/DROP TABLE/i', $statement)) {
                            $steps[] = ['type' => 'success', 'message' => '🗑️ حذف جدول قديم'];
                        } elseif (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
                            $steps[] = ['type' => 'success', 'message' => '✅ إنشاء جدول: ' . $matches[1]];
                        } elseif (preg_match('/CREATE.*VIEW\s+(\w+)/i', $statement, $matches)) {
                            $steps[] = ['type' => 'success', 'message' => '👁️ إنشاء View: ' . $matches[1]];
                        } elseif (preg_match('/INSERT INTO/i', $statement)) {
                            $steps[] = ['type' => 'success', 'message' => '📝 إدراج بيانات افتراضية'];
                        }
                        
                        // تحديث شريط التقدم
                        $progress = round(($completedSteps / $totalSteps) * 100);
                        echo '<script>document.getElementById("progressBar").style.width = "' . $progress . '%"; document.getElementById("progressBar").textContent = "' . $progress . '%";</script>';
                        flush();
                        
                    } catch (PDOException $e) {
                        // تجاهل أخطاء DROP TABLE إذا كان الجدول غير موجود
                        if (strpos($e->getMessage(), 'Unknown table') === false) {
                            $steps[] = ['type' => 'error', 'message' => '❌ خطأ: ' . $e->getMessage()];
                        }
                    }
                }
                
                echo '<script>document.getElementById("progressBar").style.width = "100%"; document.getElementById("progressBar").textContent = "100%";</script>';
                
                // عرض النتائج
                echo '<div class="step">';
                echo '<h3>📊 نتائج التثبيت:</h3>';
                foreach ($steps as $step) {
                    $class = $step['type'];
                    echo '<div class="' . $class . '">' . $step['message'] . '</div>';
                }
                echo '</div>';
                
                // التحقق من النجاح
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM auto_update_settings");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['count'] > 0) {
                    echo '<div class="step">';
                    echo '<h3 class="success">🎉 تم التثبيت بنجاح!</h3>';
                    echo '<p>تم إنشاء جميع الجداول والبيانات بنجاح.</p>';
                    echo '<p class="info">يمكنك الآن استخدام نظام التحديثات.</p>';
                    echo '</div>';
                    
                    echo '<div style="text-align: center;">';
                    echo '<a href="test-github-connection.php" class="btn">🔍 اختبار النظام</a>';
                    echo '<a href="backup-manager.php" class="btn">📦 صفحة التحديثات</a>';
                    echo '</div>';
                } else {
                    throw new Exception('فشل التحقق من التثبيت');
                }
                
            } catch (Exception $e) {
                echo '<div class="step">';
                echo '<h3 class="error">❌ فشل التثبيت</h3>';
                echo '<p class="error">' . $e->getMessage() . '</p>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
                echo '</div>';
                
                echo '<div style="text-align: center;">';
                echo '<form method="POST">';
                echo '<button type="submit" name="install" class="btn">🔄 إعادة المحاولة</button>';
                echo '</form>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
