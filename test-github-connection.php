<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار الاتصال بـ GitHub</title>
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
        .test-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-right: 4px solid #667eea;
        }
        .test-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
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
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            margin: 5px;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 اختبار الاتصال بـ GitHub API</h1>
        
        <?php
        // ============================================
        // 1. اختبار cURL
        // ============================================
        echo '<div class="test-section">';
        echo '<div class="test-title">1️⃣ اختبار cURL</div>';
        
        if (function_exists('curl_version')) {
            $version = curl_version();
            echo '<span class="success">✅ cURL مفعل</span><br>';
            echo '<span class="info">الإصدار: ' . $version['version'] . '</span><br>';
            echo '<span class="info">SSL: ' . $version['ssl_version'] . '</span><br>';
            
            // عرض البروتوكولات المدعومة
            if (isset($version['protocols'])) {
                echo '<span class="info">البروتوكولات: ' . implode(', ', $version['protocols']) . '</span>';
            }
        } else {
            echo '<span class="error">❌ cURL غير مفعل!</span><br>';
            echo '<span class="info">الرجاء تفعيل extension=curl في php.ini</span>';
        }
        echo '</div>';
        
        // ============================================
        // 2. اختبار الاتصال بـ GitHub API
        // ============================================
        echo '<div class="test-section">';
        echo '<div class="test-title">2️⃣ اختبار الاتصال بـ GitHub API</div>';
        
        $repoUrl = 'https://github.com/alabasi2025/alabasi-accounting-system';
        preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $repoUrl, $matches);
        
        if (count($matches) >= 3) {
            $owner = $matches[1];
            $repo = str_replace('.git', '', $matches[2]);
            $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/commits?per_page=3";
            
            echo '<span class="info">📍 الرابط: ' . $apiUrl . '</span><br><br>';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Alabasi-Accounting-System');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/vnd.github.v3+json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $startTime = microtime(true);
            $response = curl_exec($ch);
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);
            
            echo '<span class="info">⏱️ وقت التنفيذ: ' . $executionTime . ' ms</span><br>';
            echo '<span class="info">📊 HTTP Code: ' . $httpCode . '</span><br><br>';
            
            if ($curlError) {
                echo '<span class="error">❌ خطأ cURL: ' . $curlError . '</span>';
            } elseif ($httpCode === 200) {
                echo '<span class="success">✅ الاتصال ناجح!</span><br><br>';
                
                $commits = json_decode($response, true);
                
                if (is_array($commits) && count($commits) > 0) {
                    echo '<span class="success">✅ تم جلب ' . count($commits) . ' تحديثات</span><br><br>';
                    
                    echo '<div class="test-title">آخر 3 تحديثات:</div>';
                    foreach ($commits as $index => $commit) {
                        echo '<div style="background: white; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                        echo '<strong>' . ($index + 1) . '. ' . htmlspecialchars($commit['commit']['message']) . '</strong><br>';
                        echo '<small>👤 ' . htmlspecialchars($commit['commit']['author']['name']) . ' • ';
                        echo '📅 ' . date('Y-m-d H:i', strtotime($commit['commit']['author']['date'])) . ' • ';
                        echo '🔖 ' . substr($commit['sha'], 0, 7) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<span class="error">❌ فشل تحليل البيانات</span>';
                }
            } elseif ($httpCode === 403) {
                echo '<span class="error">❌ تم تجاوز حد الطلبات (Rate Limit)</span><br>';
                echo '<span class="info">الرجاء المحاولة بعد قليل</span>';
            } elseif ($httpCode === 404) {
                echo '<span class="error">❌ المستودع غير موجود</span>';
            } else {
                echo '<span class="error">❌ خطأ HTTP: ' . $httpCode . '</span>';
            }
        } else {
            echo '<span class="error">❌ رابط GitHub غير صحيح</span>';
        }
        echo '</div>';
        
        // ============================================
        // 3. اختبار قاعدة البيانات
        // ============================================
        echo '<div class="test-section">';
        echo '<div class="test-title">3️⃣ اختبار قاعدة البيانات</div>';
        
        try {
            require_once 'includes/db.php';
            
            // التحقق من جدول auto_update_settings
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM auto_update_settings");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                echo '<span class="success">✅ جدول auto_update_settings موجود ويحتوي على بيانات</span><br>';
                
                $stmt = $pdo->query("SELECT * FROM auto_update_settings LIMIT 1");
                $settings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo '<span class="info">📍 المستودع: ' . htmlspecialchars($settings['githubRepo']) . '</span><br>';
                echo '<span class="info">🌿 الفرع: ' . htmlspecialchars($settings['githubBranch']) . '</span><br>';
                echo '<span class="info">🔄 الفحص التلقائي: ' . ($settings['autoCheckEnabled'] ? 'مفعل' : 'معطل') . '</span>';
            } else {
                echo '<span class="error">❌ جدول auto_update_settings فارغ!</span><br>';
                echo '<span class="info">الرجاء تنفيذ ملف fix_updates_system.sql</span>';
            }
            
            // التحقق من جدول system_updates
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_updates");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<br><span class="info">📊 عدد التحديثات المسجلة: ' . $result['count'] . '</span>';
            
        } catch (Exception $e) {
            echo '<span class="error">❌ خطأ في قاعدة البيانات: ' . $e->getMessage() . '</span>';
        }
        echo '</div>';
        
        // ============================================
        // 4. معلومات PHP
        // ============================================
        echo '<div class="test-section">';
        echo '<div class="test-title">4️⃣ معلومات PHP</div>';
        echo '<span class="info">إصدار PHP: ' . phpversion() . '</span><br>';
        echo '<span class="info">allow_url_fopen: ' . (ini_get('allow_url_fopen') ? 'مفعل' : 'معطل') . '</span><br>';
        echo '<span class="info">max_execution_time: ' . ini_get('max_execution_time') . ' ثانية</span><br>';
        echo '<span class="info">memory_limit: ' . ini_get('memory_limit') . '</span>';
        echo '</div>';
        
        // ============================================
        // 5. التوصيات
        // ============================================
        echo '<div class="test-section">';
        echo '<div class="test-title">5️⃣ التوصيات</div>';
        
        if (!function_exists('curl_version')) {
            echo '<span class="badge badge-danger">⚠️ فعّل cURL في php.ini</span><br>';
        }
        
        if (isset($httpCode) && $httpCode !== 200) {
            echo '<span class="badge badge-danger">⚠️ تحقق من اتصال الإنترنت</span><br>';
        }
        
        if (isset($result) && $result['count'] == 0) {
            echo '<span class="badge badge-danger">⚠️ نفّذ ملف fix_updates_system.sql</span><br>';
        }
        
        if (function_exists('curl_version') && isset($httpCode) && $httpCode === 200 && isset($result) && $result['count'] > 0) {
            echo '<span class="badge badge-success">✅ جميع الاختبارات ناجحة! النظام جاهز للعمل</span>';
        }
        
        echo '</div>';
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="backup-manager.php" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; display: inline-block;">
                العودة إلى صفحة التحديثات
            </a>
        </div>
    </div>
</body>
</html>
