-- ============================================
-- نظام السندات والقيود الذكية
-- Smart Vouchers & Journal Entries System
-- ============================================

-- جدول سندات القبض (Receipt Vouchers)
CREATE TABLE IF NOT EXISTS receipt_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucherNumber VARCHAR(50) NOT NULL UNIQUE COMMENT 'رقم السند',
    voucherDate DATE NOT NULL COMMENT 'تاريخ السند',
    
    -- معلومات الدفع
    receivedFrom VARCHAR(255) NOT NULL COMMENT 'المستلم من',
    amount DECIMAL(15,2) NOT NULL COMMENT 'المبلغ',
    amountInWords VARCHAR(500) COMMENT 'المبلغ بالحروف',
    paymentMethod ENUM('cash', 'check', 'bank_transfer', 'other') DEFAULT 'cash' COMMENT 'طريقة الدفع',
    
    -- تفاصيل الشيك/التحويل
    checkNumber VARCHAR(100) COMMENT 'رقم الشيك',
    bankName VARCHAR(255) COMMENT 'اسم البنك',
    checkDate DATE COMMENT 'تاريخ الشيك',
    
    -- الحسابات المحاسبية
    debitAccountId INT NOT NULL COMMENT 'الحساب المدين (الصندوق/البنك)',
    creditAccountId INT NOT NULL COMMENT 'الحساب الدائن (العميل/الإيراد)',
    
    -- ربط بالقيد المحاسبي
    journalId INT COMMENT 'رقم القيد المحاسبي المرتبط',
    
    -- معلومات إضافية
    description TEXT COMMENT 'البيان',
    notes TEXT COMMENT 'ملاحظات',
    
    -- حالة السند
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft' COMMENT 'حالة السند',
    
    -- معلومات التتبع
    unitId INT COMMENT 'الوحدة',
    companyId INT COMMENT 'المؤسسة',
    branchId INT COMMENT 'الفرع',
    
    createdBy INT NOT NULL COMMENT 'المستخدم المنشئ',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    postedAt TIMESTAMP NULL COMMENT 'تاريخ الترحيل',
    postedBy INT COMMENT 'المستخدم الذي رحّل السند',
    
    FOREIGN KEY (debitAccountId) REFERENCES accounts(id),
    FOREIGN KEY (creditAccountId) REFERENCES accounts(id),
    FOREIGN KEY (journalId) REFERENCES journals(id),
    FOREIGN KEY (unitId) REFERENCES units(id),
    FOREIGN KEY (companyId) REFERENCES companies(id),
    FOREIGN KEY (branchId) REFERENCES branches(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (postedBy) REFERENCES users(id),
    
    INDEX idx_voucher_number (voucherNumber),
    INDEX idx_voucher_date (voucherDate),
    INDEX idx_status (status),
    INDEX idx_debit_account (debitAccountId),
    INDEX idx_credit_account (creditAccountId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='سندات القبض';

-- جدول سندات الصرف (Payment Vouchers)
CREATE TABLE IF NOT EXISTS payment_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucherNumber VARCHAR(50) NOT NULL UNIQUE COMMENT 'رقم السند',
    voucherDate DATE NOT NULL COMMENT 'تاريخ السند',
    
    -- معلومات الدفع
    paidTo VARCHAR(255) NOT NULL COMMENT 'المدفوع إلى',
    amount DECIMAL(15,2) NOT NULL COMMENT 'المبلغ',
    amountInWords VARCHAR(500) COMMENT 'المبلغ بالحروف',
    paymentMethod ENUM('cash', 'check', 'bank_transfer', 'other') DEFAULT 'cash' COMMENT 'طريقة الدفع',
    
    -- تفاصيل الشيك/التحويل
    checkNumber VARCHAR(100) COMMENT 'رقم الشيك',
    bankName VARCHAR(255) COMMENT 'اسم البنك',
    checkDate DATE COMMENT 'تاريخ الشيك',
    
    -- الحسابات المحاسبية
    debitAccountId INT NOT NULL COMMENT 'الحساب المدين (المورد/المصروف)',
    creditAccountId INT NOT NULL COMMENT 'الحساب الدائن (الصندوق/البنك)',
    
    -- ربط بالقيد المحاسبي
    journalId INT COMMENT 'رقم القيد المحاسبي المرتبط',
    
    -- معلومات إضافية
    description TEXT COMMENT 'البيان',
    notes TEXT COMMENT 'ملاحظات',
    
    -- حالة السند
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft' COMMENT 'حالة السند',
    
    -- معلومات التتبع
    unitId INT COMMENT 'الوحدة',
    companyId INT COMMENT 'المؤسسة',
    branchId INT COMMENT 'الفرع',
    
    createdBy INT NOT NULL COMMENT 'المستخدم المنشئ',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    postedAt TIMESTAMP NULL COMMENT 'تاريخ الترحيل',
    postedBy INT COMMENT 'المستخدم الذي رحّل السند',
    
    FOREIGN KEY (debitAccountId) REFERENCES accounts(id),
    FOREIGN KEY (creditAccountId) REFERENCES accounts(id),
    FOREIGN KEY (journalId) REFERENCES journals(id),
    FOREIGN KEY (unitId) REFERENCES units(id),
    FOREIGN KEY (companyId) REFERENCES companies(id),
    FOREIGN KEY (branchId) REFERENCES branches(id),
    FOREIGN KEY (createdBy) REFERENCES users(id),
    FOREIGN KEY (postedBy) REFERENCES users(id),
    
    INDEX idx_voucher_number (voucherNumber),
    INDEX idx_voucher_date (voucherDate),
    INDEX idx_status (status),
    INDEX idx_debit_account (debitAccountId),
    INDEX idx_credit_account (creditAccountId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='سندات الصرف';

-- جدول تفاصيل القيود (Journal Entry Details)
-- هذا الجدول موجود مسبقاً لكن سنتأكد من وجوده
CREATE TABLE IF NOT EXISTS journal_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journalId INT NOT NULL COMMENT 'رقم القيد',
    accountId INT NOT NULL COMMENT 'رقم الحساب',
    description TEXT COMMENT 'البيان',
    debit DECIMAL(15,2) DEFAULT 0 COMMENT 'المبلغ المدين',
    credit DECIMAL(15,2) DEFAULT 0 COMMENT 'المبلغ الدائن',
    
    -- معلومات إضافية
    analyticalAccountId INT COMMENT 'الحساب التحليلي',
    costCenterId INT COMMENT 'مركز التكلفة',
    
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (journalId) REFERENCES journals(id) ON DELETE CASCADE,
    FOREIGN KEY (accountId) REFERENCES accounts(id),
    FOREIGN KEY (analyticalAccountId) REFERENCES analytical_accounts(id),
    
    INDEX idx_journal (journalId),
    INDEX idx_account (accountId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تفاصيل القيود اليومية';

-- إضافة حقل لربط القيد بالسند في جدول journals
ALTER TABLE journals 
ADD COLUMN IF NOT EXISTS voucherType ENUM('none', 'receipt', 'payment') DEFAULT 'none' COMMENT 'نوع السند المرتبط',
ADD COLUMN IF NOT EXISTS voucherId INT COMMENT 'رقم السند المرتبط';

-- جدول أرقام السندات التلقائية
CREATE TABLE IF NOT EXISTS voucher_sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucherType ENUM('receipt', 'payment') NOT NULL UNIQUE,
    prefix VARCHAR(10) NOT NULL COMMENT 'بادئة الرقم',
    currentNumber INT NOT NULL DEFAULT 1 COMMENT 'الرقم الحالي',
    year INT NOT NULL COMMENT 'السنة',
    
    UNIQUE KEY unique_type_year (voucherType, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تسلسل أرقام السندات';

-- إدراج القيم الافتراضية لأرقام السندات
INSERT INTO voucher_sequences (voucherType, prefix, currentNumber, year) 
VALUES 
    ('receipt', 'RV', 1, YEAR(CURDATE())),
    ('payment', 'PV', 1, YEAR(CURDATE()))
ON DUPLICATE KEY UPDATE currentNumber = currentNumber;

-- ============================================
-- انتهى إنشاء الجداول
-- ============================================
