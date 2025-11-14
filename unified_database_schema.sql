-- ============================================
-- نظام الأباسي المحاسبي الموحد الشامل
-- Alabasi Unified Accounting System
-- ============================================
-- قاعدة بيانات موحدة تدمج أفضل ميزات من 278 جدول في 10 مشاريع
-- Database: alabasi_unified_complete
-- Version: 2.0
-- Date: 2025-01-14
-- ============================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS alabasi_unified_complete CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alabasi_unified_complete;

-- تعطيل فحص المفاتيح الخارجية مؤقتاً
SET FOREIGN_KEY_CHECKS = 0;

-- حذف الجداول القديمة إن وجدت
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS cost_center_distribution;
DROP TABLE IF EXISTS journal_entry_lines;
DROP TABLE IF EXISTS journal_entries;
DROP TABLE IF EXISTS payment_vouchers;
DROP TABLE IF EXISTS receipt_vouchers;
DROP TABLE IF EXISTS voucher_sequences;
DROP TABLE IF EXISTS account_postings;
DROP TABLE IF EXISTS operation_history;
DROP TABLE IF EXISTS analytical_accounts;
DROP TABLE IF EXISTS analytical_account_types;
DROP TABLE IF EXISTS account_currencies;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS account_types;
DROP TABLE IF EXISTS clearing_accounts;
DROP TABLE IF EXISTS exchange_rates;
DROP TABLE IF EXISTS currencies;
DROP TABLE IF EXISTS cost_centers;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS accounting_units;
DROP TABLE IF EXISTS inventory_adjustments;
DROP TABLE IF EXISTS inventory_balance;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS item_categories;
DROP TABLE IF EXISTS warehouses;
DROP TABLE IF EXISTS purchase_returns;
DROP TABLE IF EXISTS purchase_invoice_items;
DROP TABLE IF EXISTS purchase_invoices;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS salaries;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS training_courses;
DROP TABLE IF EXISTS employee_performance;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS asset_maintenance;
DROP TABLE IF EXISTS asset_depreciation;
DROP TABLE IF EXISTS assets;
DROP TABLE IF EXISTS asset_categories;
DROP TABLE IF EXISTS accounting_periods;
DROP TABLE IF EXISTS cash_bank_transactions;
DROP TABLE IF EXISTS bank_accounts;
DROP TABLE IF EXISTS cash_boxes;

-- إعادة تفعيل فحص المفاتيح الخارجية
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- القسم 1: المستخدمين والصلاحيات (5 جداول)
-- ============================================

-- جدول المستخدمين
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    avatarUrl VARCHAR(500),
    isActive BOOLEAN DEFAULT TRUE,
    lastLogin TIMESTAMP NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الأدوار
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    description TEXT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الصلاحيات
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    module VARCHAR(50) NOT NULL,
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ربط المستخدمين بالأدوار
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    roleId INT NOT NULL,
    assignedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assignedBy INT,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (roleId) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assignedBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (userId, roleId),
    INDEX idx_userId (userId),
    INDEX idx_roleId (roleId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ربط الأدوار بالصلاحيات
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roleId INT NOT NULL,
    permissionId INT NOT NULL,
    grantedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grantedBy INT,
    FOREIGN KEY (roleId) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permissionId) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (grantedBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_role_permission (roleId, permissionId),
    INDEX idx_roleId (roleId),
    INDEX idx_permissionId (permissionId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 2: الهيكل التنظيمي (5 جداول)
-- ============================================

-- جدول الوحدات المحاسبية
CREATE TABLE accounting_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    description TEXT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المؤسسات
CREATE TABLE organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    description TEXT,
    taxNumber VARCHAR(50),
    commercialRegister VARCHAR(50),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(200),
    logoUrl VARCHAR(500),
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_unitId (unitId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الفروع
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizationId INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    managerId INT,
    isMain BOOLEAN DEFAULT FALSE,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (managerId) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_organizationId (organizationId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الأقسام
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branchId INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    managerId INT,
    parentId INT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (branchId) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (managerId) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parentId) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_branchId (branchId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مراكز التكلفة
CREATE TABLE cost_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    parentId INT,
    level INT DEFAULT 1,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (parentId) REFERENCES cost_centers(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 3: العملات (3 جداول)
-- ============================================

-- جدول العملات
CREATE TABLE currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    symbol VARCHAR(10),
    isBaseCurrency BOOLEAN DEFAULT FALSE,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول أسعار الصرف
CREATE TABLE exchange_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currencyId INT NOT NULL,
    rate DECIMAL(15,6) NOT NULL,
    effectiveDate DATE NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (currencyId) REFERENCES currencies(id) ON DELETE CASCADE,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_currencyId (currencyId),
    INDEX idx_effectiveDate (effectiveDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول حسابات المقاصة (للعملات)
CREATE TABLE clearing_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currencyId INT NOT NULL,
    accountId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (currencyId) REFERENCES currencies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_currency_account (currencyId, accountId),
    INDEX idx_currencyId (currencyId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 4: دليل الحسابات (6 جداول)
-- ============================================

-- جدول أنواع الحسابات
CREATE TABLE account_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    category ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    normalBalance ENUM('debit', 'credit') NOT NULL,
    isActive BOOLEAN DEFAULT TRUE,
    INDEX idx_code (code),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول دليل الحسابات
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    accountTypeId INT NOT NULL,
    parentId INT,
    level INT DEFAULT 1,
    isParent BOOLEAN DEFAULT FALSE,
    allowPosting BOOLEAN DEFAULT TRUE,
    currencyId INT,
    isActive BOOLEAN DEFAULT TRUE,
    openingBalance DECIMAL(15,2) DEFAULT 0,
    openingBalanceType ENUM('debit', 'credit') DEFAULT 'debit',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (accountTypeId) REFERENCES account_types(id),
    FOREIGN KEY (parentId) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (currencyId) REFERENCES currencies(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_unit_code (unitId, code),
    INDEX idx_code (code),
    INDEX idx_unitId (unitId),
    INDEX idx_parentId (parentId),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول أنواع الحسابات التحليلية
CREATE TABLE analytical_account_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    description TEXT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الحسابات التحليلية
CREATE TABLE analytical_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    typeId INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    parentId INT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (typeId) REFERENCES analytical_account_types(id) ON DELETE CASCADE,
    FOREIGN KEY (parentId) REFERENCES analytical_accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_typeId (typeId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ربط الحسابات بالعملات
CREATE TABLE account_currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accountId INT NOT NULL,
    currencyId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (currencyId) REFERENCES currencies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_account_currency (accountId, currencyId),
    INDEX idx_accountId (accountId),
    INDEX idx_currencyId (currencyId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- سيتم إكمال باقي الجداول في الجزء الثاني...
-- ============================================

-- ============================================
-- القسم 5: القيود والسندات (8 جداول)
-- ============================================

-- جدول تسلسل أرقام السندات
CREATE TABLE voucher_sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucherType ENUM('receipt', 'payment', 'journal') NOT NULL,
    prefix VARCHAR(10) NOT NULL,
    currentNumber INT DEFAULT 0,
    year INT NOT NULL,
    unitId INT NOT NULL,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    UNIQUE KEY unique_type_year_unit (voucherType, year, unitId),
    INDEX idx_voucherType (voucherType),
    INDEX idx_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول القيود اليومية
CREATE TABLE journal_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    entryNumber VARCHAR(50) NOT NULL,
    entryDate DATE NOT NULL,
    description TEXT NOT NULL,
    referenceType VARCHAR(50),
    referenceId INT,
    status ENUM('draft', 'posted', 'reversed') DEFAULT 'draft',
    periodId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    postedAt TIMESTAMP NULL,
    postedBy INT,
    reversedAt TIMESTAMP NULL,
    reversedBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (postedBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reversedBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_unit_number (unitId, entryNumber),
    INDEX idx_entryNumber (entryNumber),
    INDEX idx_entryDate (entryDate),
    INDEX idx_status (status),
    INDEX idx_referenceType (referenceType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تفاصيل القيود
CREATE TABLE journal_entry_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entryId INT NOT NULL,
    lineNumber INT NOT NULL,
    accountId INT NOT NULL,
    analyticalAccountId INT,
    description TEXT,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    currencyId INT,
    exchangeRate DECIMAL(15,6) DEFAULT 1,
    debitForeign DECIMAL(15,2) DEFAULT 0,
    creditForeign DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (accountId) REFERENCES accounts(id),
    FOREIGN KEY (analyticalAccountId) REFERENCES analytical_accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (currencyId) REFERENCES currencies(id) ON DELETE SET NULL,
    INDEX idx_entryId (entryId),
    INDEX idx_accountId (accountId),
    INDEX idx_lineNumber (lineNumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول توزيع مراكز التكلفة
CREATE TABLE cost_center_distribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entryLineId INT NOT NULL,
    costCenterId INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (entryLineId) REFERENCES journal_entry_lines(id) ON DELETE CASCADE,
    FOREIGN KEY (costCenterId) REFERENCES cost_centers(id),
    INDEX idx_entryLineId (entryLineId),
    INDEX idx_costCenterId (costCenterId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سندات القبض
CREATE TABLE receipt_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    voucherNumber VARCHAR(50) NOT NULL,
    voucherDate DATE NOT NULL,
    receivedFrom VARCHAR(200) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    amountInWords VARCHAR(500),
    paymentMethod ENUM('cash', 'check', 'transfer', 'other') DEFAULT 'cash',
    checkNumber VARCHAR(50),
    checkDate DATE,
    checkBank VARCHAR(100),
    transferReference VARCHAR(100),
    debitAccountId INT NOT NULL,
    creditAccountId INT NOT NULL,
    description TEXT,
    status ENUM('draft', 'posted') DEFAULT 'draft',
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    postedAt TIMESTAMP NULL,
    postedBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (debitAccountId) REFERENCES accounts(id),
    FOREIGN KEY (creditAccountId) REFERENCES accounts(id),
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (postedBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_unit_number (unitId, voucherNumber),
    INDEX idx_voucherNumber (voucherNumber),
    INDEX idx_voucherDate (voucherDate),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سندات الصرف
CREATE TABLE payment_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    voucherNumber VARCHAR(50) NOT NULL,
    voucherDate DATE NOT NULL,
    paidTo VARCHAR(200) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    amountInWords VARCHAR(500),
    paymentMethod ENUM('cash', 'check', 'transfer', 'other') DEFAULT 'cash',
    checkNumber VARCHAR(50),
    checkDate DATE,
    checkBank VARCHAR(100),
    transferReference VARCHAR(100),
    debitAccountId INT NOT NULL,
    creditAccountId INT NOT NULL,
    description TEXT,
    status ENUM('draft', 'posted') DEFAULT 'draft',
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    postedAt TIMESTAMP NULL,
    postedBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (debitAccountId) REFERENCES accounts(id),
    FOREIGN KEY (creditAccountId) REFERENCES accounts(id),
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (postedBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_unit_number (unitId, voucherNumber),
    INDEX idx_voucherNumber (voucherNumber),
    INDEX idx_voucherDate (voucherDate),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ترحيلات الحسابات
CREATE TABLE account_postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accountId INT NOT NULL,
    entryId INT NOT NULL,
    entryLineId INT NOT NULL,
    postingDate DATE NOT NULL,
    description TEXT,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) DEFAULT 0,
    balanceType ENUM('debit', 'credit') DEFAULT 'debit',
    FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (entryLineId) REFERENCES journal_entry_lines(id) ON DELETE CASCADE,
    INDEX idx_accountId (accountId),
    INDEX idx_entryId (entryId),
    INDEX idx_postingDate (postingDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سجل العمليات
CREATE TABLE operation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operationType VARCHAR(50) NOT NULL,
    tableName VARCHAR(50) NOT NULL,
    recordId INT NOT NULL,
    action ENUM('create', 'update', 'delete', 'post', 'reverse') NOT NULL,
    oldData JSON,
    newData JSON,
    userId INT,
    ipAddress VARCHAR(45),
    userAgent VARCHAR(500),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_operationType (operationType),
    INDEX idx_tableName (tableName),
    INDEX idx_recordId (recordId),
    INDEX idx_action (action),
    INDEX idx_createdAt (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 6: الفترات المحاسبية والصناديق (3 جداول)
-- ============================================

-- جدول الفترات المحاسبية
CREATE TABLE accounting_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    nameAr VARCHAR(100) NOT NULL,
    nameEn VARCHAR(100),
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    status ENUM('open', 'closed', 'locked') DEFAULT 'open',
    closedAt TIMESTAMP NULL,
    closedBy INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (closedBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_unit_code (unitId, code),
    INDEX idx_unitId (unitId),
    INDEX idx_status (status),
    INDEX idx_dates (startDate, endDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الصناديق
CREATE TABLE cash_boxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    accountId INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    branchId INT,
    responsiblePerson VARCHAR(100),
    initialBalance DECIMAL(15,2) DEFAULT 0,
    currentBalance DECIMAL(15,2) DEFAULT 0,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (accountId) REFERENCES accounts(id),
    FOREIGN KEY (branchId) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_unitId (unitId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الحسابات البنكية
CREATE TABLE bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unitId INT NOT NULL,
    accountId INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    bankName VARCHAR(100),
    accountNumber VARCHAR(50),
    iban VARCHAR(50),
    swiftCode VARCHAR(20),
    branchName VARCHAR(100),
    initialBalance DECIMAL(15,2) DEFAULT 0,
    currentBalance DECIMAL(15,2) DEFAULT 0,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (unitId) REFERENCES accounting_units(id) ON DELETE CASCADE,
    FOREIGN KEY (accountId) REFERENCES accounts(id),
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_unitId (unitId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول معاملات الصناديق والبنوك
CREATE TABLE cash_bank_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transactionType ENUM('cash_in', 'cash_out', 'transfer') NOT NULL,
    transactionDate DATE NOT NULL,
    fromCashBoxId INT,
    fromBankAccountId INT,
    toCashBoxId INT,
    toBankAccountId INT,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    referenceNumber VARCHAR(50),
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (fromCashBoxId) REFERENCES cash_boxes(id) ON DELETE SET NULL,
    FOREIGN KEY (fromBankAccountId) REFERENCES bank_accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (toCashBoxId) REFERENCES cash_boxes(id) ON DELETE SET NULL,
    FOREIGN KEY (toBankAccountId) REFERENCES bank_accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transactionType (transactionType),
    INDEX idx_transactionDate (transactionDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 7: المخزون (6 جداول)
-- ============================================

-- جدول المستودعات
CREATE TABLE warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    branchId INT NOT NULL,
    address TEXT,
    managerId INT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (branchId) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (managerId) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_branchId (branchId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول فئات الأصناف
CREATE TABLE item_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    parentId INT,
    accountId INT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parentId) REFERENCES item_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_parentId (parentId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الأصناف
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    categoryId INT NOT NULL,
    barcode VARCHAR(50),
    unit VARCHAR(50) NOT NULL,
    purchasePrice DECIMAL(15,2) NOT NULL,
    salePrice DECIMAL(15,2) NOT NULL,
    minStock INT DEFAULT 0,
    maxStock INT DEFAULT 0,
    reorderPoint INT DEFAULT 0,
    imageUrl VARCHAR(500),
    description TEXT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (categoryId) REFERENCES item_categories(id),
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_barcode (barcode),
    INDEX idx_categoryId (categoryId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول حركات المخزون
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movementNumber VARCHAR(50) NOT NULL UNIQUE,
    movementType ENUM('in', 'out', 'transfer', 'adjustment') NOT NULL,
    movementDate DATE NOT NULL,
    warehouseId INT NOT NULL,
    toWarehouseId INT,
    itemId INT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unitPrice DECIMAL(15,2),
    totalAmount DECIMAL(15,2),
    referenceType VARCHAR(50),
    referenceId INT,
    notes TEXT,
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (warehouseId) REFERENCES warehouses(id),
    FOREIGN KEY (toWarehouseId) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (itemId) REFERENCES items(id),
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_movementNumber (movementNumber),
    INDEX idx_movementType (movementType),
    INDEX idx_movementDate (movementDate),
    INDEX idx_warehouseId (warehouseId),
    INDEX idx_itemId (itemId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول رصيد المخزون
CREATE TABLE inventory_balance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouseId INT NOT NULL,
    itemId INT NOT NULL,
    quantity DECIMAL(15,3) DEFAULT 0,
    averageCost DECIMAL(15,2) DEFAULT 0,
    totalValue DECIMAL(15,2) DEFAULT 0,
    lastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouseId) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (itemId) REFERENCES items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_warehouse_item (warehouseId, itemId),
    INDEX idx_warehouseId (warehouseId),
    INDEX idx_itemId (itemId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تسويات المخزون
CREATE TABLE inventory_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adjustmentNumber VARCHAR(50) NOT NULL UNIQUE,
    adjustmentDate DATE NOT NULL,
    warehouseId INT NOT NULL,
    itemId INT NOT NULL,
    systemQuantity DECIMAL(15,3) NOT NULL,
    actualQuantity DECIMAL(15,3) NOT NULL,
    difference DECIMAL(15,3) NOT NULL,
    reason TEXT,
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (warehouseId) REFERENCES warehouses(id),
    FOREIGN KEY (itemId) REFERENCES items(id),
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_adjustmentNumber (adjustmentNumber),
    INDEX idx_adjustmentDate (adjustmentDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- سيتم إكمال باقي الجداول في الجزء الثالث...
-- (المبيعات، المشتريات، الموارد البشرية، الأصول)
-- ============================================

-- ============================================
-- القسم 9: المشتريات (6 جداول)
-- ============================================

-- جدول الموردين
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    accountId INT,
    phone VARCHAR(20),
    mobile VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    taxNumber VARCHAR(50),
    currentBalance DECIMAL(15,2) DEFAULT 0,
    rating INT DEFAULT 0,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول أوامر الشراء
CREATE TABLE purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber VARCHAR(50) NOT NULL UNIQUE,
    orderDate DATE NOT NULL,
    supplierId INT NOT NULL,
    totalAmount DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    netAmount DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'approved', 'received', 'cancelled') DEFAULT 'pending',
    deliveryDate DATE,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (supplierId) REFERENCES suppliers(id),
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_orderNumber (orderNumber),
    INDEX idx_orderDate (orderDate),
    INDEX idx_supplierId (supplierId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تفاصيل أوامر الشراء
CREATE TABLE purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orderId INT NOT NULL,
    itemId INT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unitPrice DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    totalAmount DECIMAL(15,2) NOT NULL,
    receivedQuantity DECIMAL(15,3) DEFAULT 0,
    FOREIGN KEY (orderId) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (itemId) REFERENCES items(id),
    INDEX idx_orderId (orderId),
    INDEX idx_itemId (itemId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول فواتير المشتريات
CREATE TABLE purchase_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceNumber VARCHAR(50) NOT NULL UNIQUE,
    invoiceDate DATE NOT NULL,
    supplierId INT NOT NULL,
    warehouseId INT NOT NULL,
    orderId INT,
    totalAmount DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    netAmount DECIMAL(15,2) NOT NULL,
    paidAmount DECIMAL(15,2) DEFAULT 0,
    remainingAmount DECIMAL(15,2) NOT NULL,
    paymentStatus ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    status ENUM('draft', 'posted') DEFAULT 'draft',
    entryId INT,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    postedAt TIMESTAMP NULL,
    postedBy INT,
    FOREIGN KEY (supplierId) REFERENCES suppliers(id),
    FOREIGN KEY (warehouseId) REFERENCES warehouses(id),
    FOREIGN KEY (orderId) REFERENCES purchase_orders(id) ON DELETE SET NULL,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (postedBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoiceNumber (invoiceNumber),
    INDEX idx_invoiceDate (invoiceDate),
    INDEX idx_supplierId (supplierId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تفاصيل فواتير المشتريات
CREATE TABLE purchase_invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceId INT NOT NULL,
    itemId INT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unitPrice DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    totalAmount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (invoiceId) REFERENCES purchase_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (itemId) REFERENCES items(id),
    INDEX idx_invoiceId (invoiceId),
    INDEX idx_itemId (itemId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مرتجعات المشتريات
CREATE TABLE purchase_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    returnNumber VARCHAR(50) NOT NULL UNIQUE,
    returnDate DATE NOT NULL,
    invoiceId INT NOT NULL,
    totalAmount DECIMAL(15,2) NOT NULL,
    reason TEXT,
    status ENUM('draft', 'posted') DEFAULT 'draft',
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (invoiceId) REFERENCES purchase_invoices(id),
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_returnNumber (returnNumber),
    INDEX idx_returnDate (returnDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 10: الموارد البشرية (5 جداول)
-- ============================================

-- جدول الموظفين
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    departmentId INT,
    jobTitle VARCHAR(100),
    hireDate DATE NOT NULL,
    salary DECIMAL(15,2) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    nationalId VARCHAR(50),
    passportNumber VARCHAR(50),
    emergencyContact VARCHAR(200),
    emergencyPhone VARCHAR(20),
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (departmentId) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_userId (userId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الحضور والانصراف
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employeeId INT NOT NULL,
    attendanceDate DATE NOT NULL,
    checkIn TIME,
    checkOut TIME,
    workingHours DECIMAL(5,2),
    overtimeHours DECIMAL(5,2) DEFAULT 0,
    status ENUM('present', 'absent', 'late', 'leave', 'holiday') DEFAULT 'present',
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employeeId) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employeeId, attendanceDate),
    INDEX idx_employeeId (employeeId),
    INDEX idx_attendanceDate (attendanceDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الرواتب
CREATE TABLE salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employeeId INT NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    basicSalary DECIMAL(15,2) NOT NULL,
    allowances DECIMAL(15,2) DEFAULT 0,
    overtime DECIMAL(15,2) DEFAULT 0,
    deductions DECIMAL(15,2) DEFAULT 0,
    netSalary DECIMAL(15,2) NOT NULL,
    paymentDate DATE,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    entryId INT,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (employeeId) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_employee_month_year (employeeId, month, year),
    INDEX idx_employeeId (employeeId),
    INDEX idx_month_year (month, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تقييم الأداء
CREATE TABLE employee_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employeeId INT NOT NULL,
    evaluationDate DATE NOT NULL,
    evaluatorId INT NOT NULL,
    performanceScore INT NOT NULL,
    strengths TEXT,
    weaknesses TEXT,
    recommendations TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employeeId) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluatorId) REFERENCES users(id),
    INDEX idx_employeeId (employeeId),
    INDEX idx_evaluationDate (evaluationDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الدورات التدريبية
CREATE TABLE training_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    courseName VARCHAR(200) NOT NULL,
    employeeId INT NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    cost DECIMAL(15,2) DEFAULT 0,
    provider VARCHAR(200),
    certificate VARCHAR(500),
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (employeeId) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employeeId (employeeId),
    INDEX idx_startDate (startDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- القسم 11: الأصول الثابتة (4 جداول)
-- ============================================

-- جدول فئات الأصول
CREATE TABLE asset_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    accountId INT,
    depreciationAccountId INT,
    depreciationMethod ENUM('straight_line', 'declining_balance', 'units_of_production') DEFAULT 'straight_line',
    usefulLife INT DEFAULT 5,
    salvageValue DECIMAL(15,2) DEFAULT 0,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (depreciationAccountId) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الأصول الثابتة
CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    categoryId INT NOT NULL,
    purchaseDate DATE NOT NULL,
    purchaseCost DECIMAL(15,2) NOT NULL,
    salvageValue DECIMAL(15,2) DEFAULT 0,
    usefulLife INT NOT NULL,
    currentValue DECIMAL(15,2) NOT NULL,
    accumulatedDepreciation DECIMAL(15,2) DEFAULT 0,
    location VARCHAR(200),
    serialNumber VARCHAR(100),
    supplierId INT,
    status ENUM('active', 'disposed', 'sold') DEFAULT 'active',
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (categoryId) REFERENCES asset_categories(id),
    FOREIGN KEY (supplierId) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_categoryId (categoryId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول استهلاك الأصول
CREATE TABLE asset_depreciation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assetId INT NOT NULL,
    depreciationDate DATE NOT NULL,
    depreciationAmount DECIMAL(15,2) NOT NULL,
    accumulatedDepreciation DECIMAL(15,2) NOT NULL,
    bookValue DECIMAL(15,2) NOT NULL,
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (assetId) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_assetId (assetId),
    INDEX idx_depreciationDate (depreciationDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول صيانة الأصول
CREATE TABLE asset_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assetId INT NOT NULL,
    maintenanceDate DATE NOT NULL,
    maintenanceType ENUM('preventive', 'corrective', 'emergency') NOT NULL,
    cost DECIMAL(15,2) NOT NULL,
    provider VARCHAR(200),
    description TEXT,
    nextMaintenanceDate DATE,
    entryId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (assetId) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_assetId (assetId),
    INDEX idx_maintenanceDate (maintenanceDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- إضافة Foreign Keys المتبقية
-- ============================================

ALTER TABLE clearing_accounts ADD FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE CASCADE;
ALTER TABLE journal_entries ADD FOREIGN KEY (periodId) REFERENCES accounting_periods(id) ON DELETE SET NULL;

-- ============================================
-- إنشاء Views مفيدة
-- ============================================

-- View: رصيد الحسابات
CREATE OR REPLACE VIEW account_balances AS
SELECT 
    a.id,
    a.code,
    a.nameAr,
    a.nameEn,
    a.unitId,
    COALESCE(SUM(ap.debit), 0) - COALESCE(SUM(ap.credit), 0) AS balance,
    CASE 
        WHEN COALESCE(SUM(ap.debit), 0) > COALESCE(SUM(ap.credit), 0) THEN 'debit'
        ELSE 'credit'
    END AS balanceType
FROM accounts a
LEFT JOIN account_postings ap ON a.id = ap.accountId
GROUP BY a.id, a.code, a.nameAr, a.nameEn, a.unitId;

-- View: رصيد العملاء
CREATE OR REPLACE VIEW customer_balances AS
SELECT 
    c.id,
    c.code,
    c.nameAr,
    COALESCE(SUM(si.netAmount), 0) - COALESCE(SUM(si.paidAmount), 0) AS balance
FROM customers c
LEFT JOIN sales_invoices si ON c.id = si.customerId AND si.status = 'posted'
GROUP BY c.id, c.code, c.nameAr;

-- View: رصيد الموردين
CREATE OR REPLACE VIEW supplier_balances AS
SELECT 
    s.id,
    s.code,
    s.nameAr,
    COALESCE(SUM(pi.netAmount), 0) - COALESCE(SUM(pi.paidAmount), 0) AS balance
FROM suppliers s
LEFT JOIN purchase_invoices pi ON s.id = pi.supplierId AND pi.status = 'posted'
GROUP BY s.id, s.code, s.nameAr;

-- ============================================
-- إدراج بيانات أساسية
-- ============================================

-- إدراج المستخدم الافتراضي (admin)
INSERT INTO users (username, password, nameAr, nameEn, email, isActive) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المدير', 'Administrator', 'admin@alabasi.com', TRUE);

-- إدراج الأدوار الأساسية
INSERT INTO roles (code, nameAr, nameEn, description) VALUES
('admin', 'مدير النظام', 'System Administrator', 'Full access to all system features'),
('accountant', 'محاسب', 'Accountant', 'Access to accounting features'),
('warehouse_manager', 'مدير مستودع', 'Warehouse Manager', 'Access to inventory features'),
('sales_manager', 'مدير مبيعات', 'Sales Manager', 'Access to sales features'),
('purchase_manager', 'مدير مشتريات', 'Purchase Manager', 'Access to purchase features'),
('hr_manager', 'مدير موارد بشرية', 'HR Manager', 'Access to HR features');

-- إدراج العملات الأساسية
INSERT INTO currencies (code, nameAr, nameEn, symbol, isBaseCurrency) VALUES
('IQD', 'دينار عراقي', 'Iraqi Dinar', 'د.ع', TRUE),
('USD', 'دولار أمريكي', 'US Dollar', '$', FALSE),
('EUR', 'يورو', 'Euro', '€', FALSE);

-- إدراج أنواع الحسابات
INSERT INTO account_types (code, nameAr, nameEn, category, normalBalance) VALUES
('asset', 'أصول', 'Assets', 'asset', 'debit'),
('liability', 'خصوم', 'Liabilities', 'liability', 'credit'),
('equity', 'حقوق ملكية', 'Equity', 'equity', 'credit'),
('revenue', 'إيرادات', 'Revenue', 'revenue', 'credit'),
('expense', 'مصروفات', 'Expenses', 'expense', 'debit');

-- ============================================
-- انتهى إنشاء قاعدة البيانات
-- ============================================

-- إعادة تفعيل فحص المفاتيح الخارجية
SET FOREIGN_KEY_CHECKS = 1;

-- عرض ملخص الجداول
SELECT 
    COUNT(*) AS total_tables,
    SUM(CASE WHEN table_name LIKE '%user%' OR table_name LIKE '%role%' OR table_name LIKE '%permission%' THEN 1 ELSE 0 END) AS auth_tables,
    SUM(CASE WHEN table_name LIKE '%account%' THEN 1 ELSE 0 END) AS accounting_tables,
    SUM(CASE WHEN table_name LIKE '%item%' OR table_name LIKE '%warehouse%' OR table_name LIKE '%stock%' THEN 1 ELSE 0 END) AS inventory_tables,
    SUM(CASE WHEN table_name LIKE '%sales%' OR table_name LIKE '%customer%' THEN 1 ELSE 0 END) AS sales_tables,
    SUM(CASE WHEN table_name LIKE '%purchase%' OR table_name LIKE '%supplier%' THEN 1 ELSE 0 END) AS purchase_tables,
    SUM(CASE WHEN table_name LIKE '%employee%' OR table_name LIKE '%attendance%' OR table_name LIKE '%salary%' THEN 1 ELSE 0 END) AS hr_tables,
    SUM(CASE WHEN table_name LIKE '%asset%' THEN 1 ELSE 0 END) AS asset_tables
FROM information_schema.tables 
WHERE table_schema = 'alabasi_unified_complete';
