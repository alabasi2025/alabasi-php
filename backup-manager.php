<?php
/**
 * صفحة إدارة النسخ الاحتياطي والتحديثات
 * Backup Manager & System Updates Page
 */

session_start();
require_once 'includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'إدارة النسخ الاحتياطي والتحديثات';

// الحصول على معلومات المستخدم
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// الحصول على جداول النسخ الاحتياطي التلقائي
$schedules = [];
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS backup_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        backupPath TEXT NOT NULL,
        scheduleTime TIME NOT NULL,
        frequency ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
        isActive BOOLEAN DEFAULT TRUE,
        lastRun DATETIME NULL,
        nextRun DATETIME NULL,
        createdBy INT,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (createdBy) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $stmt = $pdo->query("SELECT bs.*, u.username as createdByName 
                         FROM backup_schedules bs 
                         LEFT JOIN users u ON bs.createdBy = u.id 
                         ORDER BY bs.scheduleTime");
    $schedules = $stmt->fetchAll();
} catch (Exception $e) {
    $schedules = [];
}

// الحصول على سجل النسخ الاحتياطي
$backupLogs = [];
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS backup_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fileName VARCHAR(255) NOT NULL,
        filePath TEXT NOT NULL,
        fileSize BIGINT,
        backupType ENUM('manual', 'scheduled') DEFAULT 'manual',
        status ENUM('success', 'failed') DEFAULT 'success',
        errorMessage TEXT NULL,
        createdBy INT,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (createdBy) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $stmt = $pdo->query("SELECT bl.*, u.username as createdByName 
                         FROM backup_logs bl 
                         LEFT JOIN users u ON bl.createdBy = u.id 
                         ORDER BY bl.createdAt DESC 
                         LIMIT 20");
    $backupLogs = $stmt->fetchAll();
} catch (Exception $e) {
    $backupLogs = [];
}

// الحصول على سجل التحديثات
$systemUpdates = [];
$canRollback = false;
$lastUpdate = null;
try {
    $stmt = $pdo->query("SELECT * FROM v_updates_summary ORDER BY appliedAt DESC LIMIT 10");
    $systemUpdates = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM v_latest_rollbackable_update");
    $lastUpdate = $stmt->fetch();
    $canRollback = !empty($lastUpdate);
} catch (Exception $e) {
    $systemUpdates = [];
}

// إحصائيات
$stats = [
    'total_backups' => count($backupLogs),
    'active_schedules' => count(array_filter($schedules, function($s) { return $s['isActive']; })),
    'success_rate' => 0,
    'total_size' => 0,
    'total_updates' => count($systemUpdates),
    'pending_updates' => 0
];

if (!empty($backupLogs)) {
    $successCount = count(array_filter($backupLogs, function($log) { return $log['status'] === 'success'; }));
    $stats['success_rate'] = round(($successCount / count($backupLogs)) * 100);
    $stats['total_size'] = array_sum(array_column($backupLogs, 'fileSize'));
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - نظام الأباسي المحاسبي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 1rem;
        }
        
        .backup-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .backup-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .backup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #667eea;
        }
        
        .nav-tabs .nav-link {
            color: #667eea;
            font-weight: 600;
            border: none;
            padding: 1rem 2rem;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .update-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #667eea;
        }
        
        .update-item.completed {
            border-left-color: #28a745;
        }
        
        .update-item.failed {
            border-left-color: #dc3545;
        }
        
        .update-item.rolled_back {
            border-left-color: #ffc107;
        }
        
        .commit-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #17a2b8;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .commit-card:hover {
            background: #e9ecef;
            transform: translateX(-5px);
        }
        
        .rollback-alert {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-database"></i> <?php echo $pageTitle; ?></h1>
                    <p class="mb-0 mt-2">إدارة النسخ الاحتياطي والتحديثات التلقائية واليدوية</p>
                </div>
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-right"></i> العودة للرئيسية
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- الإحصائيات -->
        <div class="row">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['total_backups']; ?></h3>
                    <p class="text-muted mb-0">إجمالي النسخ</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['active_schedules']; ?></h3>
                    <p class="text-muted mb-0">جداول نشطة</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['success_rate']; ?>%</h3>
                    <p class="text-muted mb-0">معدل النجاح</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-hdd"></i>
                    </div>
                    <h3 class="mb-0"><?php echo number_format($stats['total_size'] / 1024 / 1024, 2); ?> MB</h3>
                    <p class="text-muted mb-0">الحجم الإجمالي</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['total_updates']; ?></h3>
                    <p class="text-muted mb-0">التحديثات</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $canRollback ? '1' : '0'; ?></h3>
                    <p class="text-muted mb-0">قابل للتراجع</p>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button">
                    <i class="fas fa-database"></i> النسخ الاحتياطي
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="updates-tab" data-bs-toggle="tab" data-bs-target="#updates" type="button">
                    <i class="fas fa-sync-alt"></i> التحديثات
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
                    <i class="fas fa-history"></i> السجل
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="mainTabsContent">
            <!-- النسخ الاحتياطي -->
            <div class="tab-pane fade show active" id="backup" role="tabpanel">
                <!-- النسخ الاحتياطي اليدوي -->
                <div class="backup-section">
                    <h2 class="section-title"><i class="fas fa-hand-pointer"></i> نسخ احتياطي يدوي</h2>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-folder"></i> مسار حفظ النسخة الاحتياطية</label>
                                <input type="text" class="form-control" id="manualBackupPath" 
                                       placeholder="مثال: M:\النسخ الاحتياطي" 
                                       value="M:\النسخ الاحتياطي">
                                <small class="form-text text-muted">
                                    اختر مجلد على قرص خارجي (USB) أو أي مكان آمن
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn backup-btn w-100" onclick="createManualBackup()">
                                <i class="fas fa-download"></i> إنشاء نسخة احتياطية الآن
                            </button>
                        </div>
                    </div>
                </div>

                <!-- سجل النسخ الاحتياطي -->
                <div class="backup-section">
                    <h2 class="section-title"><i class="fas fa-history"></i> سجل النسخ الاحتياطي</h2>
                    
                    <?php if (empty($backupLogs)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> لا توجد نسخ احتياطية بعد.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم الملف</th>
                                        <th>الحجم</th>
                                        <th>النوع</th>
                                        <th>المستخدم</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backupLogs as $log): ?>
                                        <tr>
                                            <td><i class="fas fa-file-archive"></i> <?php echo htmlspecialchars($log['fileName']); ?></td>
                                            <td><?php echo number_format($log['fileSize'] / 1024, 2); ?> KB</td>
                                            <td>
                                                <span class="badge <?php echo $log['backupType'] === 'manual' ? 'bg-primary' : 'bg-success'; ?>">
                                                    <?php echo $log['backupType'] === 'manual' ? 'يدوي' : 'تلقائي'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['createdByName']); ?></td>
                                            <td><?php echo date('Y-m-d h:i A', strtotime($log['createdAt'])); ?></td>
                                            <td>
                                                <?php if ($log['status'] === 'success'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> نجح</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="fas fa-times"></i> فشل</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- التحديثات -->
            <div class="tab-pane fade" id="updates" role="tabpanel">
                <!-- تنبيه التراجع -->
                <?php if ($canRollback): ?>
                    <div class="rollback-alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><i class="fas fa-exclamation-triangle"></i> يمكنك التراجع عن آخر تحديث</h5>
                                <p class="mb-0">
                                    التحديث: <?php echo htmlspecialchars($lastUpdate['updateName']); ?> 
                                    (<?php echo date('Y-m-d H:i', strtotime($lastUpdate['appliedAt'])); ?>)
                                </p>
                            </div>
                            <button class="btn btn-light" onclick="rollbackLastUpdate()">
                                <i class="fas fa-undo"></i> التراجع الآن
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- التحديث من GitHub -->
                <div class="backup-section">
                    <h2 class="section-title"><i class="fab fa-github"></i> التحديث من GitHub</h2>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">رابط مستودع GitHub</label>
                                <input type="text" class="form-control" id="githubRepoUrl" 
                                       placeholder="https://github.com/username/repository"
                                       value="https://github.com/alabasi2025/alabasi-accounting-system">
                                <small class="form-text text-muted">
                                    أدخل رابط المستودع للبحث عن التحديثات المتاحة
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn backup-btn w-100" onclick="checkGitHubUpdates()">
                                <i class="fas fa-search"></i> البحث عن تحديثات
                            </button>
                        </div>
                    </div>

                    <!-- قائمة التحديثات المتاحة -->
                    <div id="availableUpdates" class="mt-4" style="display: none;">
                        <h5><i class="fas fa-list"></i> التحديثات المتاحة</h5>
                        <div id="updatesList"></div>
                    </div>
                </div>

                <!-- التحديث اليدوي -->
                <div class="backup-section">
                    <h2 class="section-title"><i class="fas fa-upload"></i> التحديث اليدوي</h2>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">رفع ملف التحديث (ZIP)</label>
                                <input type="file" class="form-control" id="manualUpdateFile" accept=".zip">
                                <small class="form-text text-muted">
                                    اختر ملف ZIP يحتوي على ملفات التحديث
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">اسم التحديث</label>
                                <input type="text" class="form-control" id="manualUpdateName" 
                                       placeholder="مثال: تحديث إصلاح الأخطاء v1.2">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn backup-btn w-100" onclick="uploadAndApplyManualUpdate()">
                                <i class="fas fa-upload"></i> رفع وتطبيق التحديث
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- السجل -->
            <div class="tab-pane fade" id="history" role="tabpanel">
                <div class="backup-section">
                    <h2 class="section-title"><i class="fas fa-history"></i> سجل التحديثات</h2>
                    
                    <?php if (empty($systemUpdates)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> لا توجد تحديثات مطبقة بعد.
                        </div>
                    <?php else: ?>
                        <?php foreach ($systemUpdates as $update): ?>
                            <div class="update-item <?php echo $update['status']; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php
                                            $statusIcons = [
                                                'completed' => '<i class="fas fa-check-circle text-success"></i>',
                                                'failed' => '<i class="fas fa-times-circle text-danger"></i>',
                                                'rolled_back' => '<i class="fas fa-undo text-warning"></i>',
                                                'in_progress' => '<i class="fas fa-spinner fa-spin text-info"></i>',
                                                'pending' => '<i class="fas fa-clock text-secondary"></i>'
                                            ];
                                            echo $statusIcons[$update['status']] ?? '';
                                            ?>
                                            <?php echo htmlspecialchars($update['updateName']); ?>
                                            <?php if ($update['version']): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($update['version']); ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($update['appliedByName']); ?> •
                                            <i class="fas fa-calendar"></i> <?php echo date('Y-m-d h:i A', strtotime($update['appliedAt'])); ?>
                                        </small>
                                        <?php if ($update['filesModified'] > 0 || $update['filesAdded'] > 0 || $update['filesDeleted'] > 0): ?>
                                            <div class="mt-2">
                                                <small>
                                                    <?php if ($update['filesAdded'] > 0): ?>
                                                        <span class="badge bg-success">+<?php echo $update['filesAdded']; ?> مضاف</span>
                                                    <?php endif; ?>
                                                    <?php if ($update['filesModified'] > 0): ?>
                                                        <span class="badge bg-warning">~<?php echo $update['filesModified']; ?> معدل</span>
                                                    <?php endif; ?>
                                                    <?php if ($update['filesDeleted'] > 0): ?>
                                                        <span class="badge bg-danger">-<?php echo $update['filesDeleted']; ?> محذوف</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($update['status'] === 'rolled_back' && $update['rolledBackByName']): ?>
                                            <div class="mt-1">
                                                <small class="text-warning">
                                                    <i class="fas fa-undo"></i> تم التراجع بواسطة <?php echo htmlspecialchars($update['rolledBackByName']); ?>
                                                    في <?php echo date('Y-m-d h:i A', strtotime($update['rolledBackAt'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-info" onclick="viewUpdateDetails(<?php echo $update['id']; ?>)">
                                            <i class="fas fa-eye"></i> التفاصيل
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal تفاصيل التحديث -->
    <div class="modal fade" id="updateDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> تفاصيل التحديث</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updateDetailsContent">
                    <!-- سيتم ملؤه ديناميكياً -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ============================================
        // دوال النسخ الاحتياطي
        // ============================================
        
        function createManualBackup() {
            const backupPath = document.getElementById('manualBackupPath').value;
            
            if (!backupPath) {
                Swal.fire('خطأ', 'الرجاء إدخال مسار حفظ النسخة الاحتياطية', 'error');
                return;
            }
            
            Swal.fire({
                title: 'جاري إنشاء النسخة الاحتياطية...',
                html: 'الرجاء الانتظار...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('api/backup-manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create_manual_backup',
                    backupPath: backupPath
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح!',
                        html: `تم إنشاء النسخة الاحتياطية<br><small>${data.fileName}</small>`,
                        confirmButtonText: 'حسناً'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('خطأ', 'حدث خطأ أثناء إنشاء النسخة الاحتياطية', 'error');
            });
        }

        // ============================================
        // دوال التحديث من GitHub
        // ============================================
        
        function checkGitHubUpdates() {
            const repoUrl = document.getElementById('githubRepoUrl').value;
            
            if (!repoUrl) {
                Swal.fire('خطأ', 'الرجاء إدخال رابط المستودع', 'error');
                return;
            }
            
            Swal.fire({
                title: 'جاري البحث عن التحديثات...',
                html: 'الرجاء الانتظار...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('api/system-updates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'check_github_updates',
                    repoUrl: repoUrl
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    displayAvailableUpdates(data.commits);
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('خطأ', 'حدث خطأ أثناء البحث عن التحديثات', 'error');
            });
        }
        
        function displayAvailableUpdates(commits) {
            const container = document.getElementById('availableUpdates');
            const list = document.getElementById('updatesList');
            
            list.innerHTML = '';
            
            commits.forEach(commit => {
                const card = document.createElement('div');
                card.className = 'commit-card';
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex: 1;">
                            <h6 class="mb-1">${escapeHtml(commit.message)}</h6>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> ${escapeHtml(commit.author)} •
                                <i class="fas fa-calendar"></i> ${new Date(commit.date).toLocaleString('ar-EG')} •
                                <i class="fas fa-code-branch"></i> ${commit.sha.substring(0, 7)}
                            </small>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="applyGitHubUpdate('${commit.sha}', '${escapeHtml(commit.message)}')">
                            <i class="fas fa-download"></i> تطبيق
                        </button>
                    </div>
                `;
                list.appendChild(card);
            });
            
            container.style.display = 'block';
        }
        
        function applyGitHubUpdate(commitSha, commitMessage) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                html: `سيتم تطبيق التحديث التالي:<br><strong>${commitMessage}</strong><br><br>سيتم إنشاء نسخة احتياطية تلقائياً قبل التحديث.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، طبق التحديث',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'جاري تطبيق التحديث...',
                        html: 'الرجاء الانتظار...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const repoUrl = document.getElementById('githubRepoUrl').value;
                    
                    fetch('api/system-updates.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'apply_github_update',
                            commitSha: commitSha,
                            commitMessage: commitMessage,
                            repoUrl: repoUrl
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم بنجاح!',
                                html: 'تم تطبيق التحديث بنجاح',
                                confirmButtonText: 'حسناً'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('خطأ', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('خطأ', 'حدث خطأ أثناء تطبيق التحديث', 'error');
                    });
                }
            });
        }

        // ============================================
        // دوال التحديث اليدوي
        // ============================================
        
        function uploadAndApplyManualUpdate() {
            const fileInput = document.getElementById('manualUpdateFile');
            const updateName = document.getElementById('manualUpdateName').value;
            
            if (!fileInput.files[0]) {
                Swal.fire('خطأ', 'الرجاء اختيار ملف ZIP', 'error');
                return;
            }
            
            if (!updateName) {
                Swal.fire('خطأ', 'الرجاء إدخال اسم التحديث', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('updateFile', fileInput.files[0]);
            
            Swal.fire({
                title: 'جاري رفع الملف...',
                html: 'الرجاء الانتظار...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // رفع الملف أولاً
            fetch('api/system-updates.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // تطبيق التحديث
                    return fetch('api/system-updates.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'apply_manual_update',
                            fileName: data.fileName,
                            updateName: updateName
                        })
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح!',
                        html: `تم تطبيق التحديث بنجاح<br>
                               <small>
                                   مضاف: ${data.stats.added} | 
                                   معدل: ${data.stats.modified} | 
                                   محذوف: ${data.stats.deleted}
                               </small>`,
                        confirmButtonText: 'حسناً'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('خطأ', error.message || 'حدث خطأ أثناء تطبيق التحديث', 'error');
            });
        }

        // ============================================
        // دوال التراجع
        // ============================================
        
        function rollbackLastUpdate() {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                html: 'سيتم التراجع عن آخر تحديث واستعادة الملفات السابقة.<br><strong>هذا الإجراء لا يمكن التراجع عنه!</strong>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'نعم، تراجع عن التحديث',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'جاري التراجع...',
                        html: 'الرجاء الانتظار...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch('api/system-updates.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'rollback_last_update'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم بنجاح!',
                                html: `تم التراجع عن التحديث بنجاح<br><small>تم استعادة ${data.rolledBackFiles} ملف</small>`,
                                confirmButtonText: 'حسناً'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('خطأ', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('خطأ', 'حدث خطأ أثناء التراجع عن التحديث', 'error');
                    });
                }
            });
        }

        // ============================================
        // دوال عرض التفاصيل
        // ============================================
        
        function viewUpdateDetails(updateId) {
            fetch('api/system-updates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get_update_details',
                    updateId: updateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUpdateDetails(data.update, data.files);
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            });
        }
        
        function displayUpdateDetails(update, files) {
            const content = document.getElementById('updateDetailsContent');
            
            let filesHtml = '';
            if (files.length > 0) {
                const grouped = {
                    added: files.filter(f => f.fileAction === 'added'),
                    modified: files.filter(f => f.fileAction === 'modified'),
                    deleted: files.filter(f => f.fileAction === 'deleted')
                };
                
                filesHtml = '<h6 class="mt-3">الملفات المتأثرة:</h6>';
                
                if (grouped.added.length > 0) {
                    filesHtml += '<div class="mb-2"><strong class="text-success">ملفات مضافة:</strong><ul>';
                    grouped.added.forEach(f => {
                        filesHtml += `<li><code>${escapeHtml(f.filePath)}</code></li>`;
                    });
                    filesHtml += '</ul></div>';
                }
                
                if (grouped.modified.length > 0) {
                    filesHtml += '<div class="mb-2"><strong class="text-warning">ملفات معدلة:</strong><ul>';
                    grouped.modified.forEach(f => {
                        filesHtml += `<li><code>${escapeHtml(f.filePath)}</code></li>`;
                    });
                    filesHtml += '</ul></div>';
                }
                
                if (grouped.deleted.length > 0) {
                    filesHtml += '<div class="mb-2"><strong class="text-danger">ملفات محذوفة:</strong><ul>';
                    grouped.deleted.forEach(f => {
                        filesHtml += `<li><code>${escapeHtml(f.filePath)}</code></li>`;
                    });
                    filesHtml += '</ul></div>';
                }
            }
            
            content.innerHTML = `
                <div>
                    <h5>${escapeHtml(update.updateName)}</h5>
                    <p><strong>النوع:</strong> ${update.updateType === 'github' ? 'GitHub' : 'يدوي'}</p>
                    <p><strong>الحالة:</strong> ${getStatusBadge(update.status)}</p>
                    <p><strong>تم التطبيق بواسطة:</strong> ${escapeHtml(update.appliedByName)}</p>
                    <p><strong>التاريخ:</strong> ${new Date(update.appliedAt).toLocaleString('ar-EG')}</p>
                    ${filesHtml}
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('updateDetailsModal'));
            modal.show();
        }
        
        function getStatusBadge(status) {
            const badges = {
                'completed': '<span class="badge bg-success">مكتمل</span>',
                'failed': '<span class="badge bg-danger">فشل</span>',
                'rolled_back': '<span class="badge bg-warning">تم التراجع</span>',
                'in_progress': '<span class="badge bg-info">قيد التنفيذ</span>',
                'pending': '<span class="badge bg-secondary">معلق</span>'
            };
            return badges[status] || status;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
