<?php
/**
 * صفحة القيود اليومية الذكية
 * Smart Journal Entries Page
 */

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = "القيود اليومية الذكية";
include 'includes/header.php';
?>

<style>
.journal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.journal-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.btn-add-journal {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-add-journal:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.journal-table {
    width: 100%;
    border-collapse: collapse;
}

.journal-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.journal-table th,
.journal-table td {
    padding: 1rem;
    text-align: right;
    border-bottom: 1px solid #e2e8f0;
}

.journal-table tbody tr:hover {
    background-color: #f7fafc;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-draft {
    background-color: #fef3c7;
    color: #92400e;
}

.badge-posted {
    background-color: #d1fae5;
    color: #065f46;
}

.badge-receipt {
    background-color: #dbeafe;
    color: #1e40af;
}

.badge-payment {
    background-color: #fce7f3;
    color: #9f1239;
}

.badge-manual {
    background-color: #e0e7ff;
    color: #3730a3;
}

.details-table {
    width: 100%;
    margin-top: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.details-table th {
    background-color: #f8fafc;
    padding: 0.75rem;
    text-align: right;
    font-weight: 600;
}

.details-table td {
    padding: 0.75rem;
    text-align: right;
    border-top: 1px solid #e2e8f0;
}

.add-line-btn {
    background-color: #10b981;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 1rem;
}

.remove-line-btn {
    background-color: #ef4444;
    color: white;
    border: none;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
}

.balance-box {
    background-color: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
    display: flex;
    justify-content: space-around;
}

.balance-item {
    text-align: center;
}

.balance-label {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.balance-value {
    font-size: 1.25rem;
    font-weight: bold;
}

.balance-balanced {
    color: #10b981;
}

.balance-unbalanced {
    color: #ef4444;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 2% auto;
    padding: 2rem;
    border-radius: 10px;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.close {
    font-size: 2rem;
    font-weight: bold;
    color: #64748b;
    cursor: pointer;
}

.close:hover {
    color: #ef4444;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #334155;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #667eea;
    color: white;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-secondary {
    background-color: #64748b;
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.action-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin: 0 0.25rem;
    font-size: 0.875rem;
}

.btn-view {
    background-color: #3b82f6;
    color: white;
}

.btn-edit {
    background-color: #f59e0b;
    color: white;
}

.btn-delete {
    background-color: #ef4444;
    color: white;
}

.btn-post {
    background-color: #10b981;
    color: white;
}
</style>

<div class="journal-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0; font-size: 2rem;">📝 القيود اليومية الذكية</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">إدارة القيود المحاسبية بذكاء</p>
        </div>
        <button class="btn-add-journal" onclick="openJournalModal()">
            ➕ قيد جديد
        </button>
    </div>
</div>

<div class="journal-card">
    <div style="margin-bottom: 1rem;">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 بحث في القيود..." onkeyup="searchJournals()">
    </div>

    <table class="journal-table" id="journalsTable">
        <thead>
            <tr>
                <th>رقم القيد</th>
                <th>التاريخ</th>
                <th>البيان</th>
                <th>المدين</th>
                <th>الدائن</th>
                <th>الحالة</th>
                <th>النوع</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody id="journalsTableBody">
            <tr>
                <td colspan="8" style="text-align: center; padding: 2rem;">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">جاري التحميل...</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal إضافة/تعديل قيد -->
<div id="journalModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">➕ قيد يومية جديد</h2>
            <span class="close" onclick="closeJournalModal()">&times;</span>
        </div>

        <form id="journalForm">
            <input type="hidden" id="journalId">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>التاريخ *</label>
                    <input type="date" id="journalDate" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>البيان *</label>
                    <input type="text" id="journalDescription" class="form-control" placeholder="وصف القيد" required>
                </div>
            </div>

            <div class="form-group">
                <label>تفاصيل القيد *</label>
                <table class="details-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">الحساب</th>
                            <th style="width: 30%;">البيان</th>
                            <th style="width: 15%;">مدين</th>
                            <th style="width: 15%;">دائن</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="journalDetailsTable">
                        <!-- سيتم إضافة الأسطر ديناميكياً -->
                    </tbody>
                </table>
                <button type="button" class="add-line-btn" onclick="addDetailLine()">
                    ➕ إضافة سطر
                </button>
            </div>

            <div class="balance-box" id="balanceBox">
                <div class="balance-item">
                    <div class="balance-label">إجمالي المدين</div>
                    <div class="balance-value" id="totalDebit">0.00</div>
                </div>
                <div class="balance-item">
                    <div class="balance-label">إجمالي الدائن</div>
                    <div class="balance-value" id="totalCredit">0.00</div>
                </div>
                <div class="balance-item">
                    <div class="balance-label">الفرق</div>
                    <div class="balance-value" id="difference">0.00</div>
                </div>
                <div class="balance-item">
                    <div class="balance-label">الحالة</div>
                    <div class="balance-value" id="balanceStatus">⚖️ متوازن</div>
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="closeJournalModal()">
                    ❌ إلغاء
                </button>
                <button type="button" class="btn btn-primary" onclick="saveJournal('draft')">
                    💾 حفظ كمسودة
                </button>
                <button type="button" class="btn btn-success" onclick="saveJournal('posted')">
                    ✅ حفظ وترحيل
                </button>
            </div>
        </form>
    </div>
</div>

<script src="js/journals-smart.js"></script>

<?php include 'includes/footer.php'; ?>
