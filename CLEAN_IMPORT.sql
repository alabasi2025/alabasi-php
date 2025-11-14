-- ============================================
-- نظام الأباسي المحاسبي الموحد الشامل
-- Alabasi Unified Accounting System
-- ============================================
-- قاعدة بيانات موحدة تدمج أفضل ميزات من 278 جدول في 10 مشاريع
-- Database: alabasi_unified
-- Version: 2.0
-- Date: 2025-01-14
-- ============================================

-- إنشاء قاعدة البيانات

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
DROP TABLE IF EXISTS sales_returns;
DROP TABLE IF EXISTS sales_invoice_items;
DROP TABLE IF EXISTS sales_invoices;
DROP TABLE IF EXISTS quotation_items;
DROP TABLE IF EXISTS quotations;
DROP TABLE IF EXISTS customers;
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
-- القسم 8: المبيعات (6 جداول)
-- ============================================

-- جدول العملاء
CREATE TABLE customers (
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
    creditLimit DECIMAL(15,2) DEFAULT 0,
    currentBalance DECIMAL(15,2) DEFAULT 0,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (accountId) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول عروض الأسعار
CREATE TABLE quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotationNumber VARCHAR(50) NOT NULL UNIQUE,
    quotationDate DATE NOT NULL,
    customerId INT NOT NULL,
    totalAmount DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    netAmount DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'converted') DEFAULT 'pending',
    validUntil DATE,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (customerId) REFERENCES customers(id),
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_quotationNumber (quotationNumber),
    INDEX idx_quotationDate (quotationDate),
    INDEX idx_customerId (customerId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تفاصيل عروض الأسعار
CREATE TABLE quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotationId INT NOT NULL,
    itemId INT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unitPrice DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    totalAmount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (quotationId) REFERENCES quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (itemId) REFERENCES items(id),
    INDEX idx_quotationId (quotationId),
    INDEX idx_itemId (itemId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول فواتير المبيعات
CREATE TABLE sales_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceNumber VARCHAR(50) NOT NULL UNIQUE,
    invoiceDate DATE NOT NULL,
    customerId INT NOT NULL,
    warehouseId INT NOT NULL,
    quotationId INT,
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
    FOREIGN KEY (customerId) REFERENCES customers(id),
    FOREIGN KEY (warehouseId) REFERENCES warehouses(id),
    FOREIGN KEY (quotationId) REFERENCES quotations(id) ON DELETE SET NULL,
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (postedBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoiceNumber (invoiceNumber),
    INDEX idx_invoiceDate (invoiceDate),
    INDEX idx_customerId (customerId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تفاصيل فواتير المبيعات
CREATE TABLE sales_invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceId INT NOT NULL,
    itemId INT NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unitPrice DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    totalAmount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (invoiceId) REFERENCES sales_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (itemId) REFERENCES items(id),
    INDEX idx_invoiceId (invoiceId),
    INDEX idx_itemId (itemId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مرتجعات المبيعات
CREATE TABLE sales_returns (
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
    FOREIGN KEY (invoiceId) REFERENCES sales_invoices(id),
    FOREIGN KEY (entryId) REFERENCES journal_entries(id) ON DELETE SET NULL,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_returnNumber (returnNumber),
    INDEX idx_returnDate (returnDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
WHERE table_schema = 'alabasi_unified';
-- ============================================
-- المخطط الموسع - الوحدات الجديدة
-- Expanded Schema - New Modules
-- 21 جدول جديد + 3 جداول موجودة
-- ============================================

-- ============================================
-- 1. وحدة إدارة الطاقة (Energy Management Module)
-- 5 جداول
-- ============================================

-- محطات الطاقة
CREATE TABLE stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    stationType ENUM('electric', 'water', 'gas', 'solar') NOT NULL,
    location VARCHAR(500),
    capacity DECIMAL(15,2),
    branchId INT,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (branchId) REFERENCES branches(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_station_type (stationType),
    INDEX idx_station_branch (branchId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- الاشتراكات
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscriptionNumber VARCHAR(50) NOT NULL UNIQUE,
    stationId INT NOT NULL,
    customerId INT,
    customerName VARCHAR(200),
    customerPhone VARCHAR(20),
    customerAddress TEXT,
    subscriptionType ENUM('residential', 'commercial', 'industrial', 'government') NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE,
    status ENUM('active', 'suspended', 'terminated') DEFAULT 'active',
    meterNumber VARCHAR(50),
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (stationId) REFERENCES stations(id),
    FOREIGN KEY (customerId) REFERENCES customers(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_subscription_station (stationId),
    INDEX idx_subscription_customer (customerId),
    INDEX idx_subscription_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- قراءات العدادات
CREATE TABLE meter_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscriptionId INT NOT NULL,
    readingDate DATE NOT NULL,
    previousReading DECIMAL(15,2) NOT NULL DEFAULT 0,
    currentReading DECIMAL(15,2) NOT NULL,
    consumption DECIMAL(15,2) GENERATED ALWAYS AS (currentReading - previousReading) STORED,
    readingType ENUM('manual', 'automatic', 'estimated') DEFAULT 'manual',
    readerName VARCHAR(100),
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (subscriptionId) REFERENCES subscriptions(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_reading_subscription (subscriptionId),
    INDEX idx_reading_date (readingDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- فواتير الطاقة
CREATE TABLE energy_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceNumber VARCHAR(50) NOT NULL UNIQUE,
    subscriptionId INT NOT NULL,
    meterReadingId INT,
    invoiceDate DATE NOT NULL,
    dueDate DATE,
    consumption DECIMAL(15,2) NOT NULL,
    unitPrice DECIMAL(15,4) NOT NULL,
    consumptionAmount DECIMAL(15,2) GENERATED ALWAYS AS (consumption * unitPrice) STORED,
    fixedCharges DECIMAL(15,2) DEFAULT 0,
    taxes DECIMAL(15,2) DEFAULT 0,
    totalAmount DECIMAL(15,2) NOT NULL,
    paidAmount DECIMAL(15,2) DEFAULT 0,
    remainingAmount DECIMAL(15,2) GENERATED ALWAYS AS (totalAmount - paidAmount) STORED,
    status ENUM('draft', 'issued', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    paymentDate DATE,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (subscriptionId) REFERENCES subscriptions(id),
    FOREIGN KEY (meterReadingId) REFERENCES meter_readings(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_energy_invoice_subscription (subscriptionId),
    INDEX idx_energy_invoice_status (status),
    INDEX idx_energy_invoice_date (invoiceDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- تقارير الاستهلاك
CREATE TABLE consumption_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reportType ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
    stationId INT,
    subscriptionId INT,
    reportDate DATE NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    totalConsumption DECIMAL(15,2) NOT NULL,
    totalAmount DECIMAL(15,2) NOT NULL,
    averageConsumption DECIMAL(15,2),
    peakConsumption DECIMAL(15,2),
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (stationId) REFERENCES stations(id),
    FOREIGN KEY (subscriptionId) REFERENCES subscriptions(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    INDEX idx_report_type (reportType),
    INDEX idx_report_station (stationId),
    INDEX idx_report_date (reportDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. وحدة الفوترة (Billing Module)
-- 4 جداول
-- ============================================

-- دورات الفوترة
CREATE TABLE billing_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nameAr VARCHAR(200) NOT NULL,
    nameEn VARCHAR(200),
    cycleType ENUM('monthly', 'quarterly', 'semi-annual', 'annual') NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    dueDate DATE NOT NULL,
    status ENUM('open', 'closed', 'processing') DEFAULT 'open',
    totalInvoices INT DEFAULT 0,
    totalAmount DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_cycle_type (cycleType),
    INDEX idx_cycle_status (status),
    INDEX idx_cycle_dates (startDate, endDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- الفواتير العامة (مختلفة عن فواتير المبيعات/المشتريات)
CREATE TABLE general_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoiceNumber VARCHAR(50) NOT NULL UNIQUE,
    invoiceType ENUM('service', 'subscription', 'rental', 'utility', 'other') NOT NULL,
    billingCycleId INT,
    customerId INT,
    customerName VARCHAR(200),
    invoiceDate DATE NOT NULL,
    dueDate DATE NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    taxAmount DECIMAL(15,2) DEFAULT 0,
    discountAmount DECIMAL(15,2) DEFAULT 0,
    totalAmount DECIMAL(15,2) NOT NULL,
    paidAmount DECIMAL(15,2) DEFAULT 0,
    remainingAmount DECIMAL(15,2) GENERATED ALWAYS AS (totalAmount - paidAmount) STORED,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    paymentMethod ENUM('cash', 'check', 'bank_transfer', 'credit_card', 'online') NULL,
    paymentDate DATE,
    description TEXT,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (billingCycleId) REFERENCES billing_cycles(id),
    FOREIGN KEY (customerId) REFERENCES customers(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_general_invoice_type (invoiceType),
    INDEX idx_general_invoice_customer (customerId),
    INDEX idx_general_invoice_status (status),
    INDEX idx_general_invoice_date (invoiceDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- المدفوعات العامة
CREATE TABLE general_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paymentNumber VARCHAR(50) NOT NULL UNIQUE,
    invoiceId INT,
    invoiceType ENUM('sales', 'purchase', 'general', 'energy') NOT NULL,
    customerId INT,
    paymentDate DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paymentMethod ENUM('cash', 'check', 'bank_transfer', 'credit_card', 'online') NOT NULL,
    checkNumber VARCHAR(50),
    bankName VARCHAR(200),
    transactionReference VARCHAR(100),
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (customerId) REFERENCES customers(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_payment_invoice (invoiceId),
    INDEX idx_payment_customer (customerId),
    INDEX idx_payment_date (paymentDate),
    INDEX idx_payment_method (paymentMethod)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إشعارات الفوترة
CREATE TABLE billing_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notificationType ENUM('invoice_due', 'payment_received', 'overdue_reminder', 'payment_failed') NOT NULL,
    invoiceId INT,
    customerId INT,
    recipientEmail VARCHAR(320),
    recipientPhone VARCHAR(20),
    subject VARCHAR(500),
    message TEXT,
    sentDate TIMESTAMP,
    status ENUM('pending', 'sent', 'failed', 'read') DEFAULT 'pending',
    deliveryMethod ENUM('email', 'sms', 'both') NOT NULL,
    attempts INT DEFAULT 0,
    lastAttempt TIMESTAMP,
    errorMessage TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (customerId) REFERENCES customers(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    INDEX idx_notification_type (notificationType),
    INDEX idx_notification_customer (customerId),
    INDEX idx_notification_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. وحدة IoT (IoT Module)
-- 4 جداول
-- ============================================

-- أجهزة IoT
CREATE TABLE iot_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deviceCode VARCHAR(50) NOT NULL UNIQUE,
    deviceName VARCHAR(200) NOT NULL,
    deviceType ENUM('sensor', 'meter', 'controller', 'gateway', 'camera', 'other') NOT NULL,
    manufacturer VARCHAR(200),
    model VARCHAR(200),
    serialNumber VARCHAR(100) UNIQUE,
    macAddress VARCHAR(50),
    ipAddress VARCHAR(50),
    location VARCHAR(500),
    stationId INT,
    subscriptionId INT,
    status ENUM('active', 'inactive', 'maintenance', 'offline') DEFAULT 'active',
    lastOnline TIMESTAMP,
    installationDate DATE,
    warrantyExpiry DATE,
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (stationId) REFERENCES stations(id),
    FOREIGN KEY (subscriptionId) REFERENCES subscriptions(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_device_type (deviceType),
    INDEX idx_device_status (status),
    INDEX idx_device_station (stationId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- قراءات الأجهزة
CREATE TABLE device_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deviceId INT NOT NULL,
    readingTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    readingType VARCHAR(50) NOT NULL,
    readingValue DECIMAL(15,4) NOT NULL,
    unit VARCHAR(20),
    qualityIndicator ENUM('good', 'fair', 'poor', 'error') DEFAULT 'good',
    rawData JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deviceId) REFERENCES iot_devices(id),
    INDEX idx_reading_device (deviceId),
    INDEX idx_reading_time (readingTime),
    INDEX idx_reading_type (readingType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- تنبيهات الأجهزة
CREATE TABLE device_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deviceId INT NOT NULL,
    alertType ENUM('threshold_exceeded', 'device_offline', 'low_battery', 'malfunction', 'security', 'other') NOT NULL,
    severity ENUM('info', 'warning', 'critical', 'emergency') NOT NULL,
    alertMessage TEXT NOT NULL,
    alertTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    acknowledgedBy INT,
    acknowledgedAt TIMESTAMP,
    resolvedBy INT,
    resolvedAt TIMESTAMP,
    status ENUM('new', 'acknowledged', 'in_progress', 'resolved', 'ignored') DEFAULT 'new',
    resolution TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deviceId) REFERENCES iot_devices(id),
    FOREIGN KEY (acknowledgedBy) REFERENCES users(id),
    FOREIGN KEY (resolvedBy) REFERENCES users(id),
    INDEX idx_alert_device (deviceId),
    INDEX idx_alert_type (alertType),
    INDEX idx_alert_severity (severity),
    INDEX idx_alert_status (status),
    INDEX idx_alert_time (alertTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- أوامر الأجهزة
CREATE TABLE device_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deviceId INT NOT NULL,
    commandType ENUM('read', 'write', 'reset', 'calibrate', 'update', 'reboot', 'custom') NOT NULL,
    commandName VARCHAR(100) NOT NULL,
    commandParameters JSON,
    sentAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    executedAt TIMESTAMP,
    status ENUM('pending', 'sent', 'executed', 'failed', 'timeout') DEFAULT 'pending',
    responseData JSON,
    errorMessage TEXT,
    sentBy INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deviceId) REFERENCES iot_devices(id),
    FOREIGN KEY (sentBy) REFERENCES users(id),
    INDEX idx_command_device (deviceId),
    INDEX idx_command_type (commandType),
    INDEX idx_command_status (status),
    INDEX idx_command_sent (sentAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. وحدة الاتصالات (Communications Module)
-- 4 جداول
-- ============================================

-- الرسائل
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    messageType ENUM('internal', 'customer', 'supplier', 'broadcast') NOT NULL,
    fromUserId INT,
    toUserId INT,
    toCustomerId INT,
    toSupplierId INT,
    subject VARCHAR(500),
    messageBody TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('draft', 'sent', 'delivered', 'read', 'failed') DEFAULT 'draft',
    sentAt TIMESTAMP,
    deliveredAt TIMESTAMP,
    readAt TIMESTAMP,
    attachments JSON,
    parentMessageId INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (fromUserId) REFERENCES users(id),
    FOREIGN KEY (toUserId) REFERENCES users(id),
    FOREIGN KEY (toCustomerId) REFERENCES customers(id),
    FOREIGN KEY (toSupplierId) REFERENCES suppliers(id),
    FOREIGN KEY (parentMessageId) REFERENCES messages(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    INDEX idx_message_type (messageType),
    INDEX idx_message_from (fromUserId),
    INDEX idx_message_to (toUserId),
    INDEX idx_message_status (status),
    INDEX idx_message_sent (sentAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- الإشعارات
CREATE TABLE system_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notificationType ENUM('system', 'transaction', 'reminder', 'alert', 'approval', 'info') NOT NULL,
    userId INT NOT NULL,
    title VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    actionUrl VARCHAR(500),
    actionLabel VARCHAR(100),
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    status ENUM('unread', 'read', 'archived') DEFAULT 'unread',
    readAt TIMESTAMP,
    expiresAt TIMESTAMP,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id),
    INDEX idx_notification_user (userId),
    INDEX idx_notification_type (notificationType),
    INDEX idx_notification_status (status),
    INDEX idx_notification_created (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- سجل البريد الإلكتروني
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emailType ENUM('transactional', 'marketing', 'notification', 'alert', 'report') NOT NULL,
    fromEmail VARCHAR(320) NOT NULL,
    toEmail VARCHAR(320) NOT NULL,
    ccEmails TEXT,
    bccEmails TEXT,
    subject VARCHAR(500) NOT NULL,
    bodyHtml TEXT,
    bodyText TEXT,
    attachments JSON,
    status ENUM('queued', 'sent', 'delivered', 'bounced', 'failed', 'spam') DEFAULT 'queued',
    sentAt TIMESTAMP,
    deliveredAt TIMESTAMP,
    openedAt TIMESTAMP,
    clickedAt TIMESTAMP,
    bouncedAt TIMESTAMP,
    errorMessage TEXT,
    provider VARCHAR(100),
    messageId VARCHAR(200),
    attempts INT DEFAULT 0,
    lastAttempt TIMESTAMP,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id),
    INDEX idx_email_type (emailType),
    INDEX idx_email_to (toEmail),
    INDEX idx_email_status (status),
    INDEX idx_email_sent (sentAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- سجل الرسائل النصية
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    smsType ENUM('transactional', 'marketing', 'notification', 'alert', 'otp') NOT NULL,
    toPhone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'delivered', 'failed', 'rejected') DEFAULT 'queued',
    sentAt TIMESTAMP,
    deliveredAt TIMESTAMP,
    errorMessage TEXT,
    provider VARCHAR(100),
    messageId VARCHAR(200),
    cost DECIMAL(10,4),
    attempts INT DEFAULT 0,
    lastAttempt TIMESTAMP,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id),
    INDEX idx_sms_type (smsType),
    INDEX idx_sms_phone (toPhone),
    INDEX idx_sms_status (status),
    INDEX idx_sms_sent (sentAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. وحدة الخرائط (Maps Module)
-- 4 جداول
-- ============================================

-- المواقع الجغرافية
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    locationCode VARCHAR(50) NOT NULL UNIQUE,
    locationName VARCHAR(200) NOT NULL,
    locationType ENUM('branch', 'warehouse', 'station', 'customer', 'supplier', 'asset', 'other') NOT NULL,
    referenceId INT,
    referenceType VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    region VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Yemen',
    postalCode VARCHAR(20),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    altitude DECIMAL(10,2),
    accuracy DECIMAL(10,2),
    notes TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_location_type (locationType),
    INDEX idx_location_reference (referenceType, referenceId),
    INDEX idx_location_coordinates (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- طبقات الخريطة
CREATE TABLE map_layers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    layerName VARCHAR(200) NOT NULL,
    layerType ENUM('markers', 'polygons', 'routes', 'heatmap', 'custom') NOT NULL,
    description TEXT,
    layerData JSON NOT NULL,
    isVisible BOOLEAN DEFAULT TRUE,
    displayOrder INT DEFAULT 0,
    style JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_layer_type (layerType),
    INDEX idx_layer_visible (isVisible),
    INDEX idx_layer_order (displayOrder)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- الحدود الجغرافية (Geofences)
CREATE TABLE geo_fences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fenceName VARCHAR(200) NOT NULL,
    fenceType ENUM('circle', 'polygon', 'route') NOT NULL,
    centerLatitude DECIMAL(10,8),
    centerLongitude DECIMAL(11,8),
    radius DECIMAL(10,2),
    polygonCoordinates JSON,
    description TEXT,
    isActive BOOLEAN DEFAULT TRUE,
    alertOnEntry BOOLEAN DEFAULT FALSE,
    alertOnExit BOOLEAN DEFAULT FALSE,
    alertRecipients JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    createdBy INT,
    updatedBy INT,
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (updatedBy) REFERENCES users(id),
    INDEX idx_fence_type (fenceType),
    INDEX idx_fence_active (isActive),
    INDEX idx_fence_center (centerLatitude, centerLongitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- سجل التتبع
CREATE TABLE tracking_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trackingType ENUM('vehicle', 'asset', 'person', 'device') NOT NULL,
    referenceId INT NOT NULL,
    referenceType VARCHAR(50) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    altitude DECIMAL(10,2),
    speed DECIMAL(10,2),
    heading DECIMAL(5,2),
    accuracy DECIMAL(10,2),
    trackingTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    batteryLevel INT,
    signalStrength INT,
    additionalData JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tracking_type (trackingType),
    INDEX idx_tracking_reference (referenceType, referenceId),
    INDEX idx_tracking_time (trackingTime),
    INDEX idx_tracking_coordinates (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. وحدة الذكاء الاصطناعي (AI Module)
-- 3 جداول (موجودة بالفعل في بعض المشاريع)
-- ============================================

-- محادثات الذكاء الاصطناعي
CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    conversationTitle VARCHAR(500),
    startTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    endTime TIMESTAMP,
    status ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
    totalMessages INT DEFAULT 0,
    context JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id),
    INDEX idx_conversation_user (userId),
    INDEX idx_conversation_status (status),
    INDEX idx_conversation_start (startTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- سجل الأوامر
CREATE TABLE IF NOT EXISTS command_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversationId INT,
    userId INT NOT NULL,
    commandText TEXT NOT NULL,
    commandType VARCHAR(100),
    responseText TEXT,
    executionTime DECIMAL(10,3),
    success BOOLEAN DEFAULT TRUE,
    errorMessage TEXT,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversationId) REFERENCES ai_conversations(id),
    FOREIGN KEY (userId) REFERENCES users(id),
    INDEX idx_command_conversation (conversationId),
    INDEX idx_command_user (userId),
    INDEX idx_command_type (commandType),
    INDEX idx_command_created (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- الأنماط المتعلمة
CREATE TABLE IF NOT EXISTS learned_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patternType ENUM('user_behavior', 'transaction', 'query', 'error', 'optimization') NOT NULL,
    patternName VARCHAR(200) NOT NULL,
    patternData JSON NOT NULL,
    frequency INT DEFAULT 1,
    confidence DECIMAL(5,4) DEFAULT 0,
    lastOccurrence TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    isActive BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pattern_type (patternType),
    INDEX idx_pattern_active (isActive),
    INDEX idx_pattern_frequency (frequency),
    INDEX idx_pattern_confidence (confidence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- النهاية - 21 جدول جديد + 3 جداول AI
-- ============================================

-- إعادة تفعيل فحص المفاتيح الخارجية
SET FOREIGN_KEY_CHECKS = 1;
