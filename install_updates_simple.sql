-- ============================================
-- تثبيت نظام التحديثات - نسخة مبسطة
-- ============================================

-- تعطيل فحص Foreign Keys
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- حذف الجداول القديمة
DROP TABLE IF EXISTS update_notifications;
DROP TABLE IF EXISTS update_files_log;
DROP TABLE IF EXISTS system_updates;
DROP TABLE IF EXISTS auto_update_settings;

-- إعادة تفعيل فحص Foreign Keys
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- جدول 1: إعدادات التحديث التلقائي
-- ============================================
CREATE TABLE auto_update_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    githubRepo VARCHAR(255) NOT NULL,
    githubBranch VARCHAR(100) DEFAULT 'main',
    githubToken VARCHAR(255) NULL,
    autoCheckEnabled BOOLEAN DEFAULT FALSE,
    checkInterval INT DEFAULT 24,
    lastCheckAt TIMESTAMP NULL,
    autoApplyUpdates BOOLEAN DEFAULT FALSE,
    createBackupBeforeUpdate BOOLEAN DEFAULT TRUE,
    latestVersion VARCHAR(50) NULL,
    latestCommitHash VARCHAR(100) NULL,
    updateAvailable BOOLEAN DEFAULT FALSE,
    updatedBy INT NULL,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول 2: سجل التحديثات
-- ============================================
CREATE TABLE system_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    updateType ENUM('github', 'manual', 'auto') NOT NULL,
    version VARCHAR(50) NULL,
    commitHash VARCHAR(100) NULL,
    commitMessage TEXT NULL,
    commitAuthor VARCHAR(255) NULL,
    commitDate TIMESTAMP NULL,
    githubUrl VARCHAR(500) NULL,
    zipFilePath VARCHAR(500) NULL,
    backupPath VARCHAR(500) NULL,
    status ENUM('pending', 'in_progress', 'completed', 'failed', 'rolled_back') DEFAULT 'pending',
    errorMessage TEXT NULL,
    filesAffectedCount INT DEFAULT 0,
    canRollback BOOLEAN DEFAULT FALSE,
    appliedBy INT NOT NULL,
    appliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rolledBackBy INT NULL,
    rolledBackAt TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_update_type (updateType),
    INDEX idx_applied_at (appliedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول 3: سجل الملفات المتأثرة
-- ============================================
CREATE TABLE update_files_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    updateId INT NOT NULL,
    filePath VARCHAR(500) NOT NULL,
    fileAction ENUM('added', 'modified', 'deleted') NOT NULL,
    oldContent LONGTEXT NULL,
    newContent LONGTEXT NULL,
    oldHash VARCHAR(64) NULL,
    newHash VARCHAR(64) NULL,
    fileSize INT NULL,
    INDEX idx_update_id (updateId),
    INDEX idx_file_action (fileAction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول 4: إشعارات التحديثات
-- ============================================
CREATE TABLE update_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    updateId INT NULL,
    notificationType ENUM('update_available', 'update_completed', 'update_failed', 'rollback_completed') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    isRead BOOLEAN DEFAULT FALSE,
    readAt TIMESTAMP NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (userId),
    INDEX idx_is_read (isRead),
    INDEX idx_created_at (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- إدراج بيانات افتراضية
-- ============================================
INSERT INTO auto_update_settings (githubRepo, githubBranch, autoCheckEnabled, checkInterval)
VALUES ('https://github.com/alabasi2025/alabasi-accounting-system', 'master', FALSE, 24);
