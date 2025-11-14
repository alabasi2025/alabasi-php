/**
 * سندات القبض الذكية - JavaScript
 * Smart Receipt Vouchers
 */

let vouchers = [];
let accounts = [];
let currentVoucher = null;

// تحميل البيانات عند فتح الصفحة
document.addEventListener('DOMContentLoaded', function() {
    loadVouchers();
    loadAccounts();
    setDefaultDate();
});

// تحميل قائمة السندات
async function loadVouchers() {
    try {
        const response = await fetch('api/receipt-vouchers.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            vouchers = data.vouchers;
            renderVouchers();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('خطأ في تحميل السندات: ' + error.message);
    }
}

// تحميل قائمة الحسابات
async function loadAccounts() {
    try {
        const response = await fetch('api/receipt-vouchers.php?action=get_accounts');
        const data = await response.json();
        
        if (data.success) {
            accounts = data.accounts;
            populateAccountsDropdowns();
        }
    } catch (error) {
        console.error('خطأ في تحميل الحسابات:', error);
    }
}

// ملء القوائم المنسدلة للحسابات
function populateAccountsDropdowns() {
    const debitSelect = document.getElementById('debitAccountId');
    const creditSelect = document.getElementById('creditAccountId');
    
    // تنظيف القوائم
    debitSelect.innerHTML = '<option value="">-- اختر الحساب --</option>';
    creditSelect.innerHTML = '<option value="">-- اختر الحساب --</option>';
    
    // إضافة الحسابات
    accounts.forEach(account => {
        const option = `<option value="${account.id}">${account.code} - ${account.nameAr}</option>`;
        debitSelect.innerHTML += option;
        creditSelect.innerHTML += option;
    });
}

// عرض السندات في الجدول
function renderVouchers() {
    const tbody = document.getElementById('vouchersTableBody');
    
    if (vouchers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                    <p style="font-size: 18px;">📭</p>
                    <p>لا توجد سندات قبض</p>
                    <button class="btn btn-primary" onclick="openVoucherForm()" style="margin-top: 15px;">
                        ➕ إضافة سند قبض جديد
                    </button>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = vouchers.map(voucher => `
        <tr>
            <td><strong>${voucher.voucherNumber}</strong></td>
            <td>${formatDate(voucher.voucherDate)}</td>
            <td>${voucher.receivedFrom}</td>
            <td><strong>${formatMoney(voucher.amount)}</strong></td>
            <td>${getPaymentMethodText(voucher.paymentMethod)}</td>
            <td>${voucher.debitAccountName || '-'}</td>
            <td>${voucher.creditAccountName || '-'}</td>
            <td>${getStatusBadge(voucher.status)}</td>
            <td>
                <button class="btn-action btn-view" onclick="viewVoucher(${voucher.id})" title="عرض">
                    👁️
                </button>
                ${voucher.status === 'draft' ? `
                    <button class="btn-action btn-edit" onclick="editVoucher(${voucher.id})" title="تعديل">
                        ✏️
                    </button>
                    <button class="btn-action btn-post" onclick="postVoucher(${voucher.id})" title="ترحيل">
                        ✅
                    </button>
                    <button class="btn-action btn-delete" onclick="deleteVoucher(${voucher.id})" title="حذف">
                        🗑️
                    </button>
                ` : ''}
                <button class="btn-action btn-print" onclick="printVoucher(${voucher.id})" title="طباعة">
                    🖨️
                </button>
            </td>
        </tr>
    `).join('');
}

// فتح نموذج سند جديد
async function openVoucherForm() {
    currentVoucher = null;
    document.getElementById('voucherForm').reset();
    document.getElementById('modalTitle').textContent = 'سند قبض جديد';
    document.getElementById('voucherId').value = '';
    
    // الحصول على رقم السند التالي
    try {
        const response = await fetch('api/receipt-vouchers.php?action=get_next_number');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('voucherNumber').value = data.voucherNumber;
        }
    } catch (error) {
        console.error('خطأ في الحصول على رقم السند:', error);
    }
    
    setDefaultDate();
    document.getElementById('voucherModal').classList.add('active');
}

// إغلاق النموذج
function closeVoucherForm() {
    document.getElementById('voucherModal').classList.remove('active');
}

// تعيين التاريخ الافتراضي
function setDefaultDate() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('voucherDate').value = today;
}

// إظهار/إخفاء حقول طريقة الدفع
function togglePaymentFields() {
    const method = document.getElementById('paymentMethod').value;
    const checkFields = document.getElementById('checkFields');
    
    if (method === 'check' || method === 'bank_transfer') {
        checkFields.classList.add('active');
    } else {
        checkFields.classList.remove('active');
    }
}

// تحويل المبلغ إلى كلمات (مبسط)
function convertAmountToWords() {
    const amount = parseFloat(document.getElementById('amount').value);
    
    if (isNaN(amount) || amount <= 0) {
        document.getElementById('amountInWords').value = '';
        return;
    }
    
    // تحويل بسيط (يمكن تحسينه)
    const words = numberToArabicWords(amount);
    document.getElementById('amountInWords').value = words;
}

// دالة مساعدة لتحويل الأرقام إلى كلمات عربية (مبسطة)
function numberToArabicWords(num) {
    if (num === 0) return 'صفر';
    
    const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
    const tens = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];
    
    // تحويل مبسط للأرقام حتى 999,999
    let result = '';
    let integer = Math.floor(num);
    let decimal = Math.round((num - integer) * 100);
    
    if (integer >= 1000) {
        const thousands = Math.floor(integer / 1000);
        result += numberToArabicWords(thousands) + ' ألف ';
        integer = integer % 1000;
    }
    
    if (integer >= 100) {
        result += hundreds[Math.floor(integer / 100)] + ' ';
        integer = integer % 100;
    }
    
    if (integer >= 10) {
        result += tens[Math.floor(integer / 10)] + ' ';
        integer = integer % 10;
    }
    
    if (integer > 0) {
        result += ones[integer] + ' ';
    }
    
    result += 'ريال';
    
    if (decimal > 0) {
        result += ' و ' + decimal + ' هللة';
    }
    
    return result.trim();
}

// حفظ السند
async function saveVoucher(event) {
    event.preventDefault();
    
    const submitButton = event.submitter;
    const saveType = submitButton.value;
    
    const formData = {
        voucherDate: document.getElementById('voucherDate').value,
        receivedFrom: document.getElementById('receivedFrom').value,
        amount: parseFloat(document.getElementById('amount').value),
        amountInWords: document.getElementById('amountInWords').value,
        paymentMethod: document.getElementById('paymentMethod').value,
        checkNumber: document.getElementById('checkNumber').value,
        bankName: document.getElementById('bankName').value,
        checkDate: document.getElementById('checkDate').value || null,
        debitAccountId: parseInt(document.getElementById('debitAccountId').value),
        creditAccountId: parseInt(document.getElementById('creditAccountId').value),
        description: document.getElementById('description').value,
        notes: document.getElementById('notes').value,
        status: saveType,
        voucherNumber: document.getElementById('voucherNumber').value
    };
    
    const voucherId = document.getElementById('voucherId').value;
    if (voucherId) {
        formData.id = parseInt(voucherId);
    }
    
    try {
        const action = voucherId ? 'update' : 'create';
        const response = await fetch(`api/receipt-vouchers.php?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            closeVoucherForm();
            loadVouchers();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('خطأ في حفظ السند: ' + error.message);
    }
}

// تعديل سند
async function editVoucher(id) {
    try {
        const response = await fetch(`api/receipt-vouchers.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const voucher = data.voucher;
            currentVoucher = voucher;
            
            document.getElementById('modalTitle').textContent = 'تعديل سند القبض';
            document.getElementById('voucherId').value = voucher.id;
            document.getElementById('voucherNumber').value = voucher.voucherNumber;
            document.getElementById('voucherDate').value = voucher.voucherDate;
            document.getElementById('receivedFrom').value = voucher.receivedFrom;
            document.getElementById('amount').value = voucher.amount;
            document.getElementById('amountInWords').value = voucher.amountInWords || '';
            document.getElementById('paymentMethod').value = voucher.paymentMethod;
            document.getElementById('checkNumber').value = voucher.checkNumber || '';
            document.getElementById('bankName').value = voucher.bankName || '';
            document.getElementById('checkDate').value = voucher.checkDate || '';
            document.getElementById('debitAccountId').value = voucher.debitAccountId;
            document.getElementById('creditAccountId').value = voucher.creditAccountId;
            document.getElementById('description').value = voucher.description || '';
            document.getElementById('notes').value = voucher.notes || '';
            
            togglePaymentFields();
            document.getElementById('voucherModal').classList.add('active');
        }
    } catch (error) {
        showError('خطأ في تحميل السند: ' + error.message);
    }
}

// حذف سند
async function deleteVoucher(id) {
    if (!confirm('هل أنت متأكد من حذف هذا السند؟')) {
        return;
    }
    
    try {
        const response = await fetch('api/receipt-vouchers.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            loadVouchers();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('خطأ في حذف السند: ' + error.message);
    }
}

// ترحيل سند
async function postVoucher(id) {
    if (!confirm('هل أنت متأكد من ترحيل هذا السند؟ لن تتمكن من تعديله بعد الترحيل.')) {
        return;
    }
    
    try {
        const response = await fetch('api/receipt-vouchers.php?action=post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message + ' - رقم القيد: ' + data.journalId);
            loadVouchers();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('خطأ في ترحيل السند: ' + error.message);
    }
}

// عرض سند
function viewVoucher(id) {
    // سيتم إضافتها لاحقاً
    alert('سيتم إضافة صفحة عرض تفاصيل السند قريباً');
}

// طباعة سند
function printVoucher(id) {
    window.open(`print-receipt-voucher.php?id=${id}`, '_blank');
}

// البحث في الجدول
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    const filtered = vouchers.filter(v => 
        v.voucherNumber.toLowerCase().includes(searchTerm) ||
        v.receivedFrom.toLowerCase().includes(searchTerm) ||
        v.amount.toString().includes(searchTerm) ||
        (v.debitAccountName && v.debitAccountName.toLowerCase().includes(searchTerm)) ||
        (v.creditAccountName && v.creditAccountName.toLowerCase().includes(searchTerm))
    );
    
    const tbody = document.getElementById('vouchersTableBody');
    tbody.innerHTML = filtered.map(voucher => `
        <tr>
            <td><strong>${voucher.voucherNumber}</strong></td>
            <td>${formatDate(voucher.voucherDate)}</td>
            <td>${voucher.receivedFrom}</td>
            <td><strong>${formatMoney(voucher.amount)}</strong></td>
            <td>${getPaymentMethodText(voucher.paymentMethod)}</td>
            <td>${voucher.debitAccountName || '-'}</td>
            <td>${voucher.creditAccountName || '-'}</td>
            <td>${getStatusBadge(voucher.status)}</td>
            <td>
                <button class="btn-action btn-view" onclick="viewVoucher(${voucher.id})" title="عرض">👁️</button>
                ${voucher.status === 'draft' ? `
                    <button class="btn-action btn-edit" onclick="editVoucher(${voucher.id})" title="تعديل">✏️</button>
                    <button class="btn-action btn-post" onclick="postVoucher(${voucher.id})" title="ترحيل">✅</button>
                    <button class="btn-action btn-delete" onclick="deleteVoucher(${voucher.id})" title="حذف">🗑️</button>
                ` : ''}
                <button class="btn-action btn-print" onclick="printVoucher(${voucher.id})" title="طباعة">🖨️</button>
            </td>
        </tr>
    `).join('');
});

// دوال مساعدة
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-SA');
}

function formatMoney(amount) {
    return new Intl.NumberFormat('ar-SA', {
        style: 'currency',
        currency: 'SAR'
    }).format(amount);
}

function getPaymentMethodText(method) {
    const methods = {
        'cash': 'نقداً',
        'check': 'شيك',
        'bank_transfer': 'تحويل بنكي',
        'other': 'أخرى'
    };
    return methods[method] || method;
}

function getStatusBadge(status) {
    const statuses = {
        'draft': '<span class="status-badge status-draft">مسودة</span>',
        'posted': '<span class="status-badge status-posted">مرحّل</span>',
        'cancelled': '<span class="status-badge status-cancelled">ملغى</span>'
    };
    return statuses[status] || status;
}

function showSuccess(message) {
    alert('✅ ' + message);
}

function showError(message) {
    alert('❌ ' + message);
}
