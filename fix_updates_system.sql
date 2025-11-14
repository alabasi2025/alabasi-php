-- ============================================
-- سكريبت إصلاح نظام التحديثات
-- Fix Updates System Script
-- ============================================
-- التاريخ: 2025-01-14
-- الإصدار: 1.0
-- ============================================

-- حذف الجداول القديمة إذا كانت موجودة (لإعادة البناء)
DROP TABLE IF EXISTS update_notifications;
DROP TABLE IF EXISTS update_files_log;
DROP TABLE IF EXISTS system_updates;
DROP TABLE IF EXISTS auto_update_settings;

-- ============================================
-- 1. جدول إعدادات التحديث التلقائي
-- ============================================

CREATE TABLE auto_update_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- إعدادات GitHub
    githubRepo VARCHAR(255) NOT NULL DEFAULT 'https://github.com/alabasi2025/alabasi-accounting-system' COMMENT 'رابط مستودع GitHub',
    githubBranch VARCHAR(100) DEFAULT 'master' COMMENT 'اسم الفرع',
    githubToken VARCHAR(255) NULL COMMENT 'رمز الوصول لـ GitHub (مشفر)',
    
    -- إعدادات التحديث التلقائي
    autoCheckEnabled BOOLEAN DEFAULT FALSE COMMENT 'تفعيل الفحص التلقائي',
    checkInterval INT DEFAULT 24 COMMENT 'فترة الفحص بالساعات',
    lastCheckAt TIMESTAMP NULL COMMENT 'آخر وقت فحص',
    
    -- إعدادات التطبيق
    autoApplyUpdates BOOLEAN DEFAULT FALSE COMMENT 'تطبيق التحديثات تلقائياً',
    createBackupBeforeUpdate BOOLEAN DEFAULT TRUE COMMENT 'إنشاء نسخة احتياطية قبل التحديث',
    
    -- معلومات آخر تحديث متاح
    latestVersion VARCHAR(50) NULL COMMENT 'آخر إصدار متاح',
    latestCommitHash VARCHAR(100) NULL COMMENT 'آخر Commit Hash',
    updateAvailable BOOLEAN DEFAULT FALSE COMMENT 'هل يوجد تحديث متاح',
    
    updatedBy INT NULL,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updatedBy) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='إعدادات التحديث التلقائي';

-- إدراج إعدادات افتراضية
INSERT INTO auto_update_settings (
    githubRepo, 
    githubBranch, 
    autoCheckEnabled, 
    checkInterval,
    createBackupBeforeUpdate
) VALUES (
    'https://github.com/alabasi2025/alabasi-accounting-system',
    'master',
    FALSE,
    24,
    TRUE
);

-- ============================================
-- 2. جدول سجل التحديثات
-- ============================================

CREATE TABLE system_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- معلومات التحديث
    updateName VARCHAR(255) NOT NULL COMMENT 'اسم التحديث',
    version VARCHAR(50) NULL COMMENT 'رقم الإصدار',
    updateType ENUM('github', 'manual', 'rollback') NOT NULL COMMENT 'نوع التحديث',
    
    -- معلومات GitHub (إذا كان من GitHub)
    githubRepo VARCHAR(255) NULL COMMENT 'رابط المستودع',
    commitHash VARCHAR(100) NULL COMMENT 'Commit Hash',
    commitMessage TEXT NULL COMMENT 'رسالة الـ Commit',
    
    -- معلومات الملف (إذا كان يدوي)
    uploadedFilePath VARCHAR(500) NULL COMMENT 'مسار الملف المرفوع',
    uploadedFileSize BIGINT NULL COMMENT 'حجم الملف بالبايت',
    
    -- حالة التحديث
    status ENUM('pending', 'in_progress', 'completed', 'failed', 'rolled_back') DEFAULT 'pending',
    errorMessage TEXT NULL COMMENT 'رسالة الخطأ إذا فشل',
    
    -- إحصائيات الملفات
    filesModified INT DEFAULT 0 COMMENT 'عدد الملفات المعدلة',
    filesAdded INT DEFAULT 0 COMMENT 'عدد الملفات المضافة',
    filesDeleted INT DEFAULT 0 COMMENT 'عدد الملفات المحذوفة',
    
    -- النسخ الاحتياطي
    backupBeforeUpdate VARCHAR(500) NULL COMMENT 'مسار النسخة الاحتياطية قبل التحديث',
    
    -- إمكانية التراجع
    canRollback BOOLEAN DEFAULT FALSE COMMENT 'هل يمكن التراجع عن هذا التحديث',
    
    -- معلومات التطبيق والتراجع
    appliedBy INT NOT NULL COMMENT 'المستخدم الذي طبق التحديث',
    appliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'وقت تطبيق التحديث',
    rolledBackBy INT NULL COMMENT 'المستخدم الذي تراجع عن التحديث',
    rolledBackAt TIMESTAMP NULL COMMENT 'وقت التراجع',
    
    FOREIGN KEY (appliedBy) REFERENCES users(id),
    FOREIGN KEY (rolledBackBy) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_update_type (updateType),
    INDEX idx_applied_at (appliedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل التحديثات المطبقة على النظام';

-- ============================================
-- 3. جدول تفاصيل ملفات التحديث
-- ============================================

CREATE TABLE update_files_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    updateId INT NOT NULL COMMENT 'معرف التحديث',
    filePath VARCHAR(500) NOT NULL COMMENT 'مسار الملف',
    fileAction ENUM('added', 'modified', 'deleted') NOT NULL COMMENT 'نوع العملية',
    
    -- محتوى الملف (للتراجع)
    oldContent LONGTEXT NULL COMMENT 'المحتوى القديم (قبل التحديث)',
    newContent LONGTEXT NULL COMMENT 'المحتوى الجديد (بعد التحديث)',
    
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updateId) REFERENCES system_updates(id) ON DELETE CASCADE,
    
    INDEX idx_update_id (updateId),
    INDEX idx_file_action (fileAction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل تفصيلي لكل ملف تم تعديله في التحديثات';

-- ============================================
-- 4. جدول إشعارات التحديثات
-- ============================================

CREATE TABLE update_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    userId INT NOT NULL COMMENT 'المستخدم المستهدف',
    updateId INT NULL COMMENT 'معرف التحديث المرتبط',
    
    notificationType ENUM('update_available', 'update_completed', 'update_failed', 'rollback_completed') NOT NULL,
    title VARCHAR(255) NOT NULL COMMENT 'عنوان الإشعار',
    message TEXT NOT NULL COMMENT 'نص الإشعار',
    
    isRead BOOLEAN DEFAULT FALSE COMMENT 'هل تم قراءة الإشعار',
    readAt TIMESTAMP NULL COMMENT 'وقت القراءة',
    
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (updateId) REFERENCES system_updates(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (userId),
    INDEX idx_is_read (isRead),
    INDEX idx_created_at (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='إشعارات التحديثات للمستخدمين';

-- ============================================
-- 5. Views (عروض) مفيدة
-- ============================================

-- عرض لآخر تحديث قابل للتراجع
CREATE OR REPLACE VIEW v_latest_rollbackable_update AS
SELECT 
    u.*,
    COALESCE(applier.fullName, applier.username) AS appliedByName,
    COALESCE(rollbacker.fullName, rollbacker.username) AS rolledBackByName
FROM system_updates u
LEFT JOIN users applier ON u.appliedBy = applier.id
LEFT JOIN users rollbacker ON u.rolledBackBy = rollbacker.id
WHERE u.canRollback = TRUE 
  AND u.status = 'completed'
ORDER BY u.appliedAt DESC
LIMIT 1;

-- عرض لإحصائيات التحديثات
CREATE OR REPLACE VIEW v_updates_statistics AS
SELECT 
    COUNT(*) AS totalUpdates,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completedUpdates,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failedUpdates,
    SUM(CASE WHEN status = 'rolled_back' THEN 1 ELSE 0 END) AS rolledBackUpdates,
    SUM(CASE WHEN canRollback = TRUE AND status = 'completed' THEN 1 ELSE 0 END) AS rollbackableUpdates,
    SUM(filesModified) AS totalFilesModified,
    SUM(filesAdded) AS totalFilesAdded,
    SUM(filesDeleted) AS totalFilesDeleted
FROM system_updates;

-- ============================================
-- تم بنجاح!
-- ============================================

SELECT 'تم إنشاء جداول نظام التحديثات بنجاح!' AS message;
SELECT * FROM auto_update_settings;
