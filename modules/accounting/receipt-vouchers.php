<?php
/**
 * صفحة سندات القبض الذكية
 * Smart Receipt Vouchers
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// التحقق من تسجيل الدخول
requireLogin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سندات القبض - نظام الأباسي الموحد</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .voucher-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group label .required {
            color: #dc3545;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .vouchers-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        .status-posted {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin: 0 2px;
        }
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-post {
            background: #28a745;
            color: white;
        }
        .btn-print {
            background: #6c757d;
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .modal-close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .modal-close:hover {
            color: #333;
        }
        .payment-method-fields {
            display: none;
        }
        .payment-method-fields.active {
            display: grid;
        }
    </style>
</head>
<body class="dashboard">
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <span style="font-size: 28px;">📊</span> نظام الأباسي الموحد
                </div>
                <div class="navbar-menu">
                    <a href="dashboard.php" class="nav-link">الرئيسية</a>
                    <a href="accounts.php" class="nav-link">الحسابات</a>
                    <a href="receipt-vouchers.php" class="nav-link active">سندات القبض</a>
                    <a href="payment-vouchers.php" class="nav-link">سندات الصرف</a>
                    <a href="journals.php" class="nav-link">القيود اليومية</a>
                    <a href="reports.php" class="nav-link">التقارير</a>
                </div>
                <div class="navbar-user">
                    <span class="user-name">👤 <?php echo getCurrentUserName(); ?></span>
                    <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- المحتوى الرئيسي -->
    <div class="container">
        <div class="dashboard-content">
            <div class="page-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 class="page-title">📥 سندات القبض</h1>
                        <p class="page-subtitle">إدارة سندات القبض والمقبوضات</p>
                    </div>
                    <button class="btn btn-primary" onclick="openVoucherForm()">
                        ➕ سند قبض جديد
                    </button>
                </div>
            </div>
            
            <!-- جدول السندات -->
            <div class="vouchers-table">
                <div class="table-header">
                    <h3 style="margin: 0;">قائمة سندات القبض</h3>
                    <div>
                        <input type="text" id="searchInput" placeholder="🔍 بحث..." 
                               style="padding: 8px 15px; border: none; border-radius: 20px; width: 250px;">
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table" id="vouchersTable">
                        <thead>
                            <tr>
                                <th>رقم السند</th>
                                <th>التاريخ</th>
                                <th>المستلم من</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>الحساب المدين</th>
                                <th>الحساب الدائن</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="vouchersTableBody">
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px;">
                                    <div class="spinner"></div>
                                    <p>جاري التحميل...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- نموذج إضافة/تعديل سند -->
    <div class="modal" id="voucherModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">سند قبض جديد</h2>
                <span class="modal-close" onclick="closeVoucherForm()">&times;</span>
            </div>
            
            <form id="voucherForm" onsubmit="saveVoucher(event)">
                <input type="hidden" id="voucherId" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>رقم السند <span class="required">*</span></label>
                        <input type="text" id="voucherNumber" name="voucherNumber" readonly 
                               style="background: #f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label>التاريخ <span class="required">*</span></label>
                        <input type="date" id="voucherDate" name="voucherDate" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>المستلم من <span class="required">*</span></label>
                        <input type="text" id="receivedFrom" name="receivedFrom" 
                               placeholder="اسم العميل أو الجهة" required>
                    </div>
                    <div class="form-group">
                        <label>المبلغ <span class="required">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" 
                               placeholder="0.00" required onchange="convertAmountToWords()">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>المبلغ بالحروف</label>
                    <input type="text" id="amountInWords" name="amountInWords" readonly
                           style="background: #f5f5f5;" placeholder="سيتم التحويل تلقائياً">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>طريقة الدفع <span class="required">*</span></label>
                        <select id="paymentMethod" name="paymentMethod" onchange="togglePaymentFields()">
                            <option value="cash">نقداً</option>
                            <option value="check">شيك</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row payment-method-fields" id="checkFields">
                    <div class="form-group">
                        <label>رقم الشيك</label>
                        <input type="text" id="checkNumber" name="checkNumber">
                    </div>
                    <div class="form-group">
                        <label>اسم البنك</label>
                        <input type="text" id="bankName" name="bankName">
                    </div>
                    <div class="form-group">
                        <label>تاريخ الشيك</label>
                        <input type="date" id="checkDate" name="checkDate">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>الحساب المدين (الصندوق/البنك) <span class="required">*</span></label>
                        <select id="debitAccountId" name="debitAccountId" required>
                            <option value="">-- اختر الحساب --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الحساب الدائن (العميل/الإيراد) <span class="required">*</span></label>
                        <select id="creditAccountId" name="creditAccountId" required>
                            <option value="">-- اختر الحساب --</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>البيان</label>
                    <textarea id="description" name="description" placeholder="وصف العملية"></textarea>
                </div>
                
                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea id="notes" name="notes" placeholder="ملاحظات إضافية"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeVoucherForm()">
                        إلغاء
                    </button>
                    <button type="submit" name="saveType" value="draft" class="btn btn-warning">
                        💾 حفظ كمسودة
                    </button>
                    <button type="submit" name="saveType" value="posted" class="btn btn-success">
                        ✅ حفظ وترحيل
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="js/receipt-vouchers.js"></script>
</body>
</html>
