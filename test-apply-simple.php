<?php
/**
 * اختبار تطبيق تحديث بسيط
 */
session_start();
$_SESSION['user_id'] = 1; // تسجيل دخول وهمي

require_once 'includes/db.php';

echo "<h1>اختبار تطبيق التحديث</h1>";

try {
    // الحصول على آخر commit
    $repoUrl = 'https://github.com/alabasi2025/alabasi-accounting-system';
    preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $repoUrl, $matches);
    $owner = $matches[1];
    $repo = str_replace('.git', '', $matches[2]);
    
    $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/commits?per_page=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Test');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $commits = json_decode($response, true);
    $latestCommit = $commits[0];
    
    echo "<h2>آخر commit:</h2>";
    echo "<pre>";
    echo "SHA: " . $latestCommit['sha'] . "\n";
    echo "Message: " . $latestCommit['commit']['message'] . "\n";
    echo "Date: " . $latestCommit['commit']['author']['date'] . "\n";
    echo "</pre>";
    
    // الحصول على تفاصيل الـ commit
    $commitSha = $latestCommit['sha'];
    $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/commits/{$commitSha}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Test');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h2>HTTP Code: $httpCode</h2>";
    
    if ($httpCode !== 200) {
        throw new Exception("فشل الحصول على تفاصيل الـ commit: HTTP $httpCode");
    }
    
    $commitData = json_decode($response, true);
    
    if (!isset($commitData['files'])) {
        throw new Exception('لا توجد ملفات في هذا الـ commit');
    }
    
    echo "<h2>الملفات المتغيرة (" . count($commitData['files']) . "):</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>الملف</th><th>الحالة</th><th>الحجم</th><th>المسار الكامل</th></tr>";
    
    $projectRoot = dirname(__FILE__);
    
    foreach ($commitData['files'] as $file) {
        $filePath = $file['filename'];
        $status = $file['status'];
        $targetPath = $projectRoot . '/' . $filePath;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($filePath) . "</td>";
        echo "<td>" . htmlspecialchars($status) . "</td>";
        echo "<td>" . ($file['changes'] ?? 0) . " تغيير</td>";
        echo "<td style='font-size: 10px;'>" . htmlspecialchars($targetPath) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>✅ الاختبار نجح!</h2>";
    echo "<p>الآن يمكنك تطبيق التحديث من صفحة backup-manager.php</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ خطأ:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
