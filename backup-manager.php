<?php
/**
 * صفحة إدارة النسخ الاحتياطي
 * Backup Manager Page
 */

session_start();
require_once 'includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'إدارة النسخ الاحتياطي';

// الحصول على معلومات المستخدم
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// الحصول على جداول النسخ الاحتياطي التلقائي
$schedules = [];
try {
    // إنشاء جدول الجداول الزمنية إذا لم يكن موجوداً
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
    // إنشاء جدول السجل إذا لم يكن موجوداً
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

// إحصائيات
$stats = [
    'total_backups' => count($backupLogs),
    'active_schedules' => count(array_filter($schedules, function($s) { return $s['isActive']; })),
    'success_rate' => 0,
    'total_size' => 0
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
        }
        
        .schedule-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }
        
        .log-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .log-item.success {
            border-left: 4px solid #28a745;
        }
        
        .log-item.failed {
            border-left: 4px solid #dc3545;
        }
        
        .badge-active {
            background: #28a745;
        }
        
        .badge-inactive {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-database"></i> <?php echo $pageTitle; ?></h1>
                    <p class="mb-0 mt-2">إدارة النسخ الاحتياطي اليدوي والتلقائي</p>
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
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['total_backups']; ?></h3>
                    <p class="text-muted mb-0">إجمالي النسخ</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['active_schedules']; ?></h3>
                    <p class="text-muted mb-0">جداول نشطة</p>
                    </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-0"><?php echo $stats['success_rate']; ?>%</h3>
                    <p class="text-muted mb-0">معدل النجاح</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-hdd"></i>
                    </div>
                    <h3 class="mb-0"><?php echo number_format($stats['total_size'] / 1024 / 1024, 2); ?> MB</h3>
                    <p class="text-muted mb-0">الحجم الإجمالي</p>
                </div>
            </div>
        </div>

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

        <!-- النسخ الاحتياطي التلقائي -->
        <div class="backup-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0"><i class="fas fa-calendar-alt"></i> جدولة النسخ التلقائي</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                    <i class="fas fa-plus"></i> إضافة جدول جديد
                </button>
            </div>

            <?php if (empty($schedules)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> لا توجد جداول نسخ احتياطي تلقائي. اضغط "إضافة جدول جديد" لإنشاء واحد.
                </div>
            <?php else: ?>
                <?php foreach ($schedules as $schedule): ?>
                    <div class="schedule-card">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">
                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($schedule['name']); ?>
                                    <?php if ($schedule['isActive']): ?>
                                        <span class="badge badge-active">نشط</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">غير نشط</span>
                                    <?php endif; ?>
                                </h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($schedule['backupPath']); ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">الوقت:</small>
                                <p class="mb-0"><strong><?php echo date('h:i A', strtotime($schedule['scheduleTime'])); ?></strong></p>
                                <small class="text-muted">التكرار: <?php 
                                    $freq = ['daily' => 'يومي', 'weekly' => 'أسبوعي', 'monthly' => 'شهري'];
                                    echo $freq[$schedule['frequency']]; 
                                ?></small>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-sm btn-warning" onclick="editSchedule(<?php echo $schedule['id']; ?>)">
                                    <i class="fas fa-edit"></i> تعديل
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- سجل النسخ الاحتياطي -->
        <div class="backup-section">
            <h2 class="section-title"><i class="fas fa-history"></i> سجل النسخ الاحتياطي</h2>
            
            <?php if (empty($backupLogs)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> لا توجد نسخ احتياطية بعد.
                </div>
            <?php else: ?>
                <?php foreach ($backupLogs as $log): ?>
                    <div class="log-item <?php echo $log['status']; ?>">
                        <div>
                            <h6 class="mb-1">
                                <?php if ($log['status'] === 'success'): ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($log['fileName']); ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($log['createdByName']); ?> •
                                <i class="fas fa-calendar"></i> <?php echo date('Y-m-d h:i A', strtotime($log['createdAt'])); ?> •
                                <i class="fas fa-hdd"></i> <?php echo number_format($log['fileSize'] / 1024, 2); ?> KB
                            </small>
                        </div>
                        <div>
                            <span class="badge <?php echo $log['backupType'] === 'manual' ? 'bg-primary' : 'bg-success'; ?>">
                                <?php echo $log['backupType'] === 'manual' ? 'يدوي' : 'تلقائي'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal إضافة جدول -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> إضافة جدول نسخ احتياطي</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm">
                        <div class="mb-3">
                            <label class="form-label">اسم الجدول</label>
                            <input type="text" class="form-control" id="scheduleName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">مسار حفظ النسخة</label>
                            <input type="text" class="form-control" id="scheduleBackupPath" 
                                   placeholder="M:\النسخ الاحتياطي" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">وقت التنفيذ</label>
                            <input type="time" class="form-control" id="scheduleTime" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">التكرار</label>
                            <select class="form-select" id="scheduleFrequency">
                                <option value="daily">يومي</option>
                                <option value="weekly">أسبوعي</option>
                                <option value="monthly">شهري</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scheduleActive" checked>
                            <label class="form-check-label" for="scheduleActive">
                                تفعيل الجدول
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
        
        function saveSchedule() {
            const data = {
                action: 'add_schedule',
                name: document.getElementById('scheduleName').value,
                backupPath: document.getElementById('scheduleBackupPath').value,
                scheduleTime: document.getElementById('scheduleTime').value,
                frequency: document.getElementById('scheduleFrequency').value,
                isActive: document.getElementById('scheduleActive').checked
            };
            
            fetch('api/backup-manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('تم بنجاح!', 'تم إضافة الجدول', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            });
        }
        
        function deleteSchedule(id) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'سيتم حذف هذا الجدول نهائياً',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('api/backup-manager.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'delete_schedule',
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('تم الحذف!', 'تم حذف الجدول بنجاح', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('خطأ', data.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
