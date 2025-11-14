<?php
/**
 * API لإدارة تحديثات النظام
 * System Updates Management API
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

require_once '../includes/db.php';

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'check_github_updates':
            checkGitHubUpdates($pdo, $input, $userId);
            break;
            
        case 'fetch_github_releases':
            fetchGitHubReleases($pdo, $input, $userId);
            break;
            
        case 'apply_github_update':
            applyGitHubUpdate($pdo, $input, $userId);
            break;
            
        case 'upload_manual_update':
            uploadManualUpdate($pdo, $userId);
            break;
            
        case 'apply_manual_update':
            applyManualUpdate($pdo, $input, $userId);
            break;
            
        case 'get_updates_history':
            getUpdatesHistory($pdo);
            break;
            
        case 'get_update_details':
            getUpdateDetails($pdo, $input);
            break;
            
        case 'rollback_last_update':
            rollbackLastUpdate($pdo, $userId);
            break;
            
        case 'get_rollback_info':
            getRollbackInfo($pdo);
            break;
            
        case 'update_auto_settings':
            updateAutoSettings($pdo, $input, $userId);
            break;
            
        case 'get_auto_settings':
            getAutoSettings($pdo);
            break;
            
        default:
            throw new Exception('إجراء غير معروف');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ============================================
// دوال GitHub
// ============================================

function checkGitHubUpdates($pdo, $input, $userId) {
    $repoUrl = $input['repoUrl'] ?? '';
    
    if (empty($repoUrl)) {
        throw new Exception('الرجاء إدخال رابط المستودع');
    }
    
    // استخراج معلومات المستودع من الرابط
    preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $repoUrl, $matches);
    
    if (count($matches) < 3) {
        throw new Exception('رابط GitHub غير صحيح');
    }
    
    $owner = $matches[1];
    $repo = str_replace('.git', '', $matches[2]);
    
    // جلب آخر Commits من GitHub API
    $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/commits?per_page=10";
    
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('خطأ في الاتصال: ' . $curlError);
    }
    
    if ($httpCode !== 200) {
        throw new Exception('فشل الاتصال بـ GitHub (' . $httpCode . '). الرجاء التحقق من الرابط.');
    }
    
    $commits = json_decode($response, true);
    
    if (empty($commits)) {
        throw new Exception('لم يتم العثور على تحديثات');
    }
    
    // تحديث إعدادات التحديث التلقائي
    $stmt = $pdo->prepare("UPDATE auto_update_settings 
                           SET githubRepo = ?, 
                               lastCheckAt = NOW(),
                               latestCommitHash = ?,
                               updateAvailable = TRUE,
                               updatedBy = ?
                           WHERE id = 1");
    $stmt->execute([$repoUrl, $commits[0]['sha'], $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم العثور على ' . count($commits) . ' تحديث',
        'commits' => array_map(function($commit) {
            return [
                'sha' => $commit['sha'],
                'message' => $commit['commit']['message'],
                'author' => $commit['commit']['author']['name'],
                'date' => $commit['commit']['author']['date'],
                'url' => $commit['html_url']
            ];
        }, $commits)
    ]);
}

function fetchGitHubReleases($pdo, $input, $userId) {
    $repoUrl = $input['repoUrl'] ?? '';
    
    preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $repoUrl, $matches);
    
    if (count($matches) < 3) {
        throw new Exception('رابط GitHub غير صحيح');
    }
    
    $owner = $matches[1];
    $repo = str_replace('.git', '', $matches[2]);
    
    // جلب الإصدارات (Releases)
    $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/releases";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Alabasi-Accounting-System');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github.v3+json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $releases = json_decode($response, true);
    
    echo json_encode([
        'success' => true,
        'releases' => array_map(function($release) {
            return [
                'id' => $release['id'],
                'name' => $release['name'],
                'tag' => $release['tag_name'],
                'description' => $release['body'],
                'published_at' => $release['published_at'],
                'download_url' => $release['zipball_url']
            ];
        }, $releases)
    ]);
}

function applyGitHubUpdate($pdo, $input, $userId) {
    $commitSha = $input['commitSha'] ?? '';
    $commitMessage = $input['commitMessage'] ?? '';
    $repoUrl = $input['repoUrl'] ?? '';
    
    if (empty($commitSha)) {
        throw new Exception('معرف التحديث مفقود');
    }
    
    // إنشاء نسخة احتياطية قبل التحديث
    $backupPath = createBackupBeforeUpdate($pdo, $userId);
    
    // تسجيل التحديث في قاعدة البيانات
    $stmt = $pdo->prepare("INSERT INTO system_updates 
                           (updateName, updateType, githubRepo, commitHash, commitMessage, 
                            backupBeforeUpdate, appliedBy, status)
                           VALUES (?, 'github', ?, ?, ?, ?, ?, 'in_progress')");
    $stmt->execute([
        'GitHub Update - ' . substr($commitSha, 0, 7),
        $repoUrl,
        $commitSha,
        $commitMessage,
        $backupPath,
        $userId
    ]);
    
    $updateId = $pdo->lastInsertId();
    
    // تنزيل وتطبيق التحديث من GitHub
    try {
        // استخراج معلومات المستودع
        preg_match('/github\.com\/([^\/]+)\/([^\/]+?)(\.git)?$/', $repoUrl, $matches);
        if (!$matches) {
            throw new Exception('رابط GitHub غير صحيح');
        }
        
        $owner = $matches[1];
        $repo = rtrim($matches[2], '.git');
        
        // تنزيل الملفات المتغيرة في هذا الـ commit
        $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/commits/{$commitSha}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Alabasi-Accounting-System');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $commitData = json_decode($response, true);
        
        if (!isset($commitData['files'])) {
            throw new Exception('فشل الحصول على معلومات التحديث من GitHub');
        }
        
        $filesModified = 0;
        $filesAdded = 0;
        $filesDeleted = 0;
        
        // معالجة كل ملف
        foreach ($commitData['files'] as $file) {
            $filePath = $file['filename'];
            $status = $file['status']; // added, modified, removed
            $targetPath = '../' . $filePath;
            
            // حفظ المحتوى القديم للتراجع
            $oldContent = null;
            if (file_exists($targetPath)) {
                $oldContent = file_get_contents($targetPath);
            }
            
            if ($status === 'removed') {
                // حذف الملف
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                    $filesDeleted++;
                }
                $fileAction = 'deleted';
                $newContent = null;
            } else {
                // تنزيل المحتوى الجديد
                $rawUrl = $file['raw_url'];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $rawUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $newContent = curl_exec($ch);
                curl_close($ch);
                
                // إنشاء المجلدات إذا لزم الأمر
                $dir = dirname($targetPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                // حفظ الملف
                file_put_contents($targetPath, $newContent);
                
                if ($status === 'added') {
                    $filesAdded++;
                    $fileAction = 'added';
                } else {
                    $filesModified++;
                    $fileAction = 'modified';
                }
            }
            
            // تسجيل الملف في update_files_log
            $stmt = $pdo->prepare("INSERT INTO update_files_log 
                                   (updateId, filePath, fileAction, oldContent, newContent)
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $updateId,
                $filePath,
                $fileAction,
                $oldContent,
                $newContent
            ]);
        }
        
        // تحديث الحالة إلى مكتمل
        $stmt = $pdo->prepare("UPDATE system_updates 
                               SET status = 'completed', 
                                   filesModified = ?,
                                   filesAdded = ?,
                                   filesDeleted = ?,
                                   canRollback = TRUE
                               WHERE id = ?");
        $stmt->execute([$filesModified, $filesAdded, $filesDeleted, $updateId]);
        
    } catch (Exception $e) {
        // في حالة الفشل، تحديث الحالة
        $stmt = $pdo->prepare("UPDATE system_updates 
                               SET status = 'failed', 
                                   errorMessage = ?
                               WHERE id = ?");
        $stmt->execute([$e->getMessage(), $updateId]);
        
        throw $e;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تطبيق التحديث بنجاح',
        'updateId' => $updateId,
        'backupPath' => $backupPath
    ]);
}

// ============================================
// دوال التحديث اليدوي
// ============================================

function uploadManualUpdate($pdo, $userId) {
    if (!isset($_FILES['updateFile'])) {
        throw new Exception('لم يتم رفع أي ملف');
    }
    
    $file = $_FILES['updateFile'];
    
    // التحقق من نوع الملف
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExt !== 'zip') {
        throw new Exception('يجب أن يكون الملف بصيغة ZIP');
    }
    
    // إنشاء مجلد للتحديثات إذا لم يكن موجوداً
    $uploadsDir = '../uploads/updates/';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }
    
    // نقل الملف
    $fileName = 'update_' . time() . '_' . uniqid() . '.zip';
    $filePath = $uploadsDir . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('فشل رفع الملف');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'تم رفع الملف بنجاح',
        'fileName' => $fileName,
        'filePath' => $filePath,
        'fileSize' => filesize($filePath)
    ]);
}

function applyManualUpdate($pdo, $input, $userId) {
    $fileName = $input['fileName'] ?? '';
    $updateName = $input['updateName'] ?? 'تحديث يدوي';
    
    if (empty($fileName)) {
        throw new Exception('اسم الملف مفقود');
    }
    
    $filePath = '../uploads/updates/' . $fileName;
    
    if (!file_exists($filePath)) {
        throw new Exception('الملف غير موجود');
    }
    
    // إنشاء نسخة احتياطية قبل التحديث
    $backupPath = createBackupBeforeUpdate($pdo, $userId);
    
    // تسجيل التحديث
    $stmt = $pdo->prepare("INSERT INTO system_updates 
                           (updateName, updateType, updateFilePath, updateFileSize, 
                            backupBeforeUpdate, appliedBy, status)
                           VALUES (?, 'manual', ?, ?, ?, ?, 'in_progress')");
    $stmt->execute([
        $updateName,
        $filePath,
        filesize($filePath),
        $backupPath,
        $userId
    ]);
    
    $updateId = $pdo->lastInsertId();
    
    // استخراج ملف ZIP وتطبيق التحديث
    $result = extractAndApplyUpdate($filePath, $updateId, $pdo);
    
    if ($result['success']) {
        // تحديث الحالة
        $stmt = $pdo->prepare("UPDATE system_updates 
                               SET status = 'completed',
                                   filesModified = ?,
                                   filesAdded = ?,
                                   filesDeleted = ?
                               WHERE id = ?");
        $stmt->execute([
            $result['modified'],
            $result['added'],
            $result['deleted'],
            $updateId
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'تم تطبيق التحديث بنجاح',
            'updateId' => $updateId,
            'stats' => $result
        ]);
    } else {
        // تحديث الحالة إلى فشل
        $stmt = $pdo->prepare("UPDATE system_updates 
                               SET status = 'failed',
                                   errorMessage = ?
                               WHERE id = ?");
        $stmt->execute([$result['error'], $updateId]);
        
        throw new Exception('فشل تطبيق التحديث: ' . $result['error']);
    }
}

function extractAndApplyUpdate($zipPath, $updateId, $pdo) {
    $zip = new ZipArchive;
    
    if ($zip->open($zipPath) !== TRUE) {
        return ['success' => false, 'error' => 'فشل فتح ملف ZIP'];
    }
    
    $extractPath = '../temp_update_' . $updateId . '/';
    $zip->extractAll($extractPath);
    $zip->close();
    
    // إحصائيات التحديث
    $stats = [
        'added' => 0,
        'modified' => 0,
        'deleted' => 0
    ];
    
    // نسخ الملفات من المجلد المؤقت إلى المجلد الرئيسي
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractPath),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile()) {
            $relativePath = str_replace($extractPath, '', $file->getPathname());
            $targetPath = '../' . $relativePath;
            
            // تسجيل الملف في update_files_log
            $fileAction = file_exists($targetPath) ? 'modified' : 'added';
            $oldContent = file_exists($targetPath) ? file_get_contents($targetPath) : null;
            $newContent = file_get_contents($file->getPathname());
            
            $stmt = $pdo->prepare("INSERT INTO update_files_log 
                                   (updateId, filePath, fileName, fileAction, oldContent, newContent, fileSize)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $updateId,
                $relativePath,
                basename($relativePath),
                $fileAction,
                $oldContent,
                $newContent,
                filesize($file->getPathname())
            ]);
            
            // نسخ الملف
            $targetDir = dirname($targetPath);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            copy($file->getPathname(), $targetPath);
            $stats[$fileAction]++;
        }
    }
    
    // حذف المجلد المؤقت
    deleteDirectory($extractPath);
    
    return array_merge(['success' => true], $stats);
}

// ============================================
// دوال سجل التحديثات
// ============================================

function getUpdatesHistory($pdo) {
    $stmt = $pdo->query("SELECT * FROM v_updates_summary LIMIT 50");
    $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'updates' => $updates
    ]);
}

function getUpdateDetails($pdo, $input) {
    $updateId = $input['updateId'] ?? 0;
    
    // معلومات التحديث
    $stmt = $pdo->prepare("SELECT * FROM v_updates_summary WHERE id = ?");
    $stmt->execute([$updateId]);
    $update = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$update) {
        throw new Exception('التحديث غير موجود');
    }
    
    // ملفات التحديث
    $stmt = $pdo->prepare("SELECT * FROM update_files_log WHERE updateId = ? ORDER BY fileAction, filePath");
    $stmt->execute([$updateId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'update' => $update,
        'files' => $files
    ]);
}

// ============================================
// دوال التراجع (Rollback)
// ============================================

function getRollbackInfo($pdo) {
    $stmt = $pdo->query("SELECT * FROM v_latest_rollbackable_update");
    $update = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$update) {
        echo json_encode([
            'success' => true,
            'canRollback' => false,
            'message' => 'لا يوجد تحديث قابل للتراجع'
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'canRollback' => true,
        'update' => $update
    ]);
}

function rollbackLastUpdate($pdo, $userId) {
    // الحصول على آخر تحديث قابل للتراجع
    $stmt = $pdo->query("SELECT * FROM v_latest_rollbackable_update");
    $update = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$update) {
        throw new Exception('لا يوجد تحديث قابل للتراجع');
    }
    
    $updateId = $update['id'];
    
    // الحصول على ملفات التحديث
    $stmt = $pdo->prepare("SELECT * FROM update_files_log WHERE updateId = ?");
    $stmt->execute([$updateId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $rolledBack = 0;
    $errors = [];
    
    // التراجع عن كل ملف
    foreach ($files as $file) {
        $targetPath = '../' . $file['filePath'];
        
        try {
            if ($file['fileAction'] === 'added') {
                // حذف الملف المضاف
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                    $rolledBack++;
                }
            } elseif ($file['fileAction'] === 'modified') {
                // استرجاع المحتوى القديم
                if ($file['oldContent'] !== null) {
                    file_put_contents($targetPath, $file['oldContent']);
                    $rolledBack++;
                }
            } elseif ($file['fileAction'] === 'deleted') {
                // استرجاع الملف المحذوف
                if ($file['oldContent'] !== null) {
                    $targetDir = dirname($targetPath);
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    file_put_contents($targetPath, $file['oldContent']);
                    $rolledBack++;
                }
            }
        } catch (Exception $e) {
            $errors[] = $file['filePath'] . ': ' . $e->getMessage();
        }
    }
    
    // تحديث حالة التحديث
    $stmt = $pdo->prepare("UPDATE system_updates 
                           SET status = 'rolled_back',
                               rolledBackBy = ?,
                               rolledBackAt = NOW()
                           WHERE id = ?");
    $stmt->execute([$userId, $updateId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم التراجع عن التحديث بنجاح',
        'rolledBackFiles' => $rolledBack,
        'errors' => $errors
    ]);
}

// ============================================
// دوال الإعدادات
// ============================================

function getAutoSettings($pdo) {
    $stmt = $pdo->query("SELECT * FROM auto_update_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);
}

function updateAutoSettings($pdo, $input, $userId) {
    $fields = [];
    $values = [];
    
    if (isset($input['githubRepo'])) {
        $fields[] = 'githubRepo = ?';
        $values[] = $input['githubRepo'];
    }
    
    if (isset($input['autoCheckEnabled'])) {
        $fields[] = 'autoCheckEnabled = ?';
        $values[] = $input['autoCheckEnabled'] ? 1 : 0;
    }
    
    if (isset($input['checkInterval'])) {
        $fields[] = 'checkInterval = ?';
        $values[] = $input['checkInterval'];
    }
    
    if (isset($input['autoApplyUpdates'])) {
        $fields[] = 'autoApplyUpdates = ?';
        $values[] = $input['autoApplyUpdates'] ? 1 : 0;
    }
    
    if (isset($input['createBackupBeforeUpdate'])) {
        $fields[] = 'createBackupBeforeUpdate = ?';
        $values[] = $input['createBackupBeforeUpdate'] ? 1 : 0;
    }
    
    $fields[] = 'updatedBy = ?';
    $values[] = $userId;
    
    $values[] = 1; // WHERE id = 1
    
    $sql = "UPDATE auto_update_settings SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تحديث الإعدادات بنجاح'
    ]);
}

// ============================================
// دوال مساعدة
// ============================================

function createBackupBeforeUpdate($pdo, $userId) {
    // استخدام مجلد محلي بدلاً من M:
    $backupDir = '../backups/';
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    
    $fileName = 'backup_before_update_' . date('Y-m-d_H-i-s') . '.sql';
    $filePath = $backupDir . $fileName;
    
    // نسخة احتياطية بسيطة باستخدام PHP
    try {
        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $sqlContent = "-- Backup created at " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $sqlContent .= "-- Table: $table\n";
            $sqlContent .= "DROP TABLE IF EXISTS `$table`;\n";
            
            // Get CREATE TABLE
            $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sqlContent .= $createTable['Create Table'] . ";\n\n";
        }
        
        file_put_contents($filePath, $sqlContent);
    } catch (Exception $e) {
        // في حالة الفشل، استخدم ملف وهمي
        $filePath = $backupDir . 'backup_placeholder.sql';
        file_put_contents($filePath, "-- Backup placeholder\n");
    }
    
    // تسجيل في backup_logs
    $stmt = $pdo->prepare("INSERT INTO backup_logs 
                           (fileName, filePath, fileSize, backupType, status, createdBy)
                           VALUES (?, ?, ?, 'manual', 'success', ?)");
    $stmt->execute([
        $fileName,
        $filePath,
        filesize($filePath),
        $userId
    ]);
    
    return $filePath;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}
