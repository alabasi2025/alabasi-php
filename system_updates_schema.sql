-- ============================================
-- جداول نظام التحديثات (System Updates)
-- ============================================
-- تاريخ الإنشاء: 2025-01-14
-- الوصف: جداول لإدارة تحديثات النظام من GitHub أو يدوياً مع إمكانية الرجوع للإصدارات السابقة
-- ============================================

-- جدول سجل التحديثات
CREATE TABLE IF NOT EXISTS system_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- معلومات التحديث
    updateName VARCHAR(255) NOT NULL COMMENT 'اسم التحديث',
    version VARCHAR(50) NULL COMMENT 'رقم الإصدار',
    updateType ENUM('github', 'manual', 'rollback') DEFAULT 'manual' COMMENT 'نوع التحديث',
    
    -- معلومات GitHub (إذا كان من GitHub)
    githubRepo VARCHAR(255) NULL COMMENT 'رابط مستودع GitHub',
    commitHash VARCHAR(100) NULL COMMENT 'رمز الـ Commit',
    commitMessage TEXT NULL COMMENT 'رسالة الـ Commit',
    
    -- معلومات الملف
    updateFilePath TEXT NULL COMMENT 'مسار ملف التحديث (ZIP)',
    updateFileSize BIGINT NULL COMMENT 'حجم الملف بالبايت',
    
    -- تفاصيل التحديث
    filesModified INT DEFAULT 0 COMMENT 'عدد الملفات المعدلة',
    filesAdded INT DEFAULT 0 COMMENT 'عدد الملفات المضافة',
    filesDeleted INT DEFAULT 0 COMMENT 'عدد الملفات المحذوفة',
    updateDetails TEXT NULL COMMENT 'تفاصيل التحديث (JSON)',
    
    -- حالة التحديث
    status ENUM('pending', 'in_progress', 'completed', 'failed', 'rolled_back') DEFAULT 'pending',
    errorMessage TEXT NULL COMMENT 'رسالة الخطأ إن وجدت',
    
    -- النسخة الاحتياطية قبل التحديث
    backupBeforeUpdate VARCHAR(255) NULL COMMENT 'مسار النسخة الاحتياطية قبل التحديث',
    canRollback BOOLEAN DEFAULT TRUE COMMENT 'هل يمكن التراجع عن هذا التحديث',
    
    -- معلومات المستخدم
    appliedBy INT NOT NULL COMMENT 'المستخدم الذي طبق التحديث',
    appliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'وقت تطبيق التحديث',
    
    -- معلومات التراجع
    rolledBackBy INT NULL COMMENT 'المستخدم الذي تراجع عن التحديث',
    rolledBackAt TIMESTAMP NULL COMMENT 'وقت التراجع',
    
    -- الطوابع الزمنية
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (appliedBy) REFERENCES users(id),
    FOREIGN KEY (rolledBackBy) REFERENCES users(id),
    
    -- الفهارس
    INDEX idx_status (status),
    INDEX idx_updateType (updateType),
    INDEX idx_appliedAt (appliedAt),
    INDEX idx_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل تحديثات النظام';

-- جدول تفاصيل ملفات التحديث
CREATE TABLE IF NOT EXISTS update_files_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    updateId INT NOT NULL COMMENT 'معرف التحديث',
    
    -- معلومات الملف
    filePath VARCHAR(500) NOT NULL COMMENT 'مسار الملف',
    fileName VARCHAR(255) NOT NULL COMMENT 'اسم الملف',
    fileAction ENUM('added', 'modified', 'deleted') NOT NULL COMMENT 'نوع العملية',
    
    -- محتوى الملف (للتراجع)
    oldContent LONGTEXT NULL COMMENT 'المحتوى القديم (قبل التحديث)',
    newContent LONGTEXT NULL COMMENT 'المحتوى الجديد (بعد التحديث)',
    
    -- معلومات إضافية
    fileSize BIGINT NULL COMMENT 'حجم الملف',
    fileMd5 VARCHAR(32) NULL COMMENT 'MD5 Hash للملف',
    
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updateId) REFERENCES system_updates(id) ON DELETE CASCADE,
    INDEX idx_updateId (updateId),
    INDEX idx_fileAction (fileAction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل تفاصيل ملفات التحديثات';

-- جدول إعدادات التحديث التلقائي
CREATE TABLE IF NOT EXISTS auto_update_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- إعدادات GitHub
    githubRepo VARCHAR(255) NOT NULL COMMENT 'رابط مستودع GitHub',
    githubBranch VARCHAR(100) DEFAULT 'main' COMMENT 'اسم الفرع',
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
    
    FOREIGN KEY (updatedBy) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='إعدادات التحديث التلقائي';

-- إدراج إعدادات افتراضية
INSERT INTO auto_update_settings (githubRepo, githubBranch, autoCheckEnabled, checkInterval)
VALUES ('https://github.com/alabasi2025/alabasi-accounting-system', 'main', FALSE, 24)
ON DUPLICATE KEY UPDATE githubRepo = githubRepo;

-- جدول إشعارات التحديثات
CREATE TABLE IF NOT EXISTS update_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    updateId INT NULL COMMENT 'معرف التحديث المرتبط',
    
    notificationType ENUM('update_available', 'update_applied', 'update_failed', 'rollback_completed') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    isRead BOOLEAN DEFAULT FALSE,
    readBy INT NULL,
    readAt TIMESTAMP NULL,
    
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updateId) REFERENCES system_updates(id) ON DELETE SET NULL,
    FOREIGN KEY (readBy) REFERENCES users(id),
    INDEX idx_isRead (isRead),
    INDEX idx_createdAt (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='إشعارات التحديثات';

-- ============================================
-- Views (طرق العرض)
-- ============================================

-- عرض ملخص التحديثات
CREATE OR REPLACE VIEW v_updates_summary AS
SELECT 
    su.id,
    su.updateName,
    su.version,
    su.updateType,
    su.status,
    su.filesModified,
    su.filesAdded,
    su.filesDeleted,
    su.appliedAt,
    su.canRollback,
    u.username as appliedByName,
    CASE 
        WHEN su.status = 'rolled_back' THEN ru.username
        ELSE NULL
    END as rolledBackByName,
    su.rolledBackAt
FROM system_updates su
LEFT JOIN users u ON su.appliedBy = u.id
LEFT JOIN users ru ON su.rolledBackBy = ru.id
ORDER BY su.appliedAt DESC;

-- عرض آخر تحديث قابل للتراجع
CREATE OR REPLACE VIEW v_latest_rollbackable_update AS
SELECT 
    su.*,
    u.username as appliedByName
FROM system_updates su
LEFT JOIN users u ON su.appliedBy = u.id
WHERE su.status = 'completed' 
  AND su.canRollback = TRUE
ORDER BY su.appliedAt DESC
LIMIT 1;

-- ============================================
-- Stored Procedures (الإجراءات المخزنة)
-- ============================================

DELIMITER //

-- إجراء لتسجيل تحديث جديد
CREATE PROCEDURE IF NOT EXISTS sp_log_update(
    IN p_updateName VARCHAR(255),
    IN p_version VARCHAR(50),
    IN p_updateType ENUM('github', 'manual', 'rollback'),
    IN p_appliedBy INT,
    OUT p_updateId INT
)
BEGIN
    INSERT INTO system_updates (updateName, version, updateType, appliedBy, status)
    VALUES (p_updateName, p_version, p_updateType, p_appliedBy, 'pending');
    
    SET p_updateId = LAST_INSERT_ID();
END //

-- إجراء لتحديث حالة التحديث
CREATE PROCEDURE IF NOT EXISTS sp_update_status(
    IN p_updateId INT,
    IN p_status ENUM('pending', 'in_progress', 'completed', 'failed', 'rolled_back'),
    IN p_errorMessage TEXT
)
BEGIN
    UPDATE system_updates 
    SET status = p_status,
        errorMessage = p_errorMessage
    WHERE id = p_updateId;
END //

-- إجراء للتراجع عن تحديث
CREATE PROCEDURE IF NOT EXISTS sp_rollback_update(
    IN p_updateId INT,
    IN p_userId INT
)
BEGIN
    UPDATE system_updates 
    SET status = 'rolled_back',
        rolledBackBy = p_userId,
        rolledBackAt = NOW()
    WHERE id = p_updateId;
END //

DELIMITER ;

-- ============================================
-- إحصائيات أولية
-- ============================================

SELECT 'تم إنشاء جداول نظام التحديثات بنجاح!' as message;
SELECT COUNT(*) as total_updates FROM system_updates;
SELECT COUNT(*) as pending_updates FROM system_updates WHERE status = 'pending';
SELECT COUNT(*) as completed_updates FROM system_updates WHERE status = 'completed';
