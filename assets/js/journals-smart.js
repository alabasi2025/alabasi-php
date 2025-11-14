/**
 * JavaScript للقيود اليومية الذكية
 * Smart Journals JavaScript
 */

let accounts = [];
let currentJournalId = null;

// تحميل البيانات عند فتح الصفحة
document.addEventListener('DOMContentLoaded', function() {
    loadAccounts();
    loadJournals();
    
    // تعيين التاريخ الحالي
    document.getElementById('journalDate').valueAsDate = new Date();
});

// تحميل الحسابات
async function loadAccounts() {
    try {
        const response = await fetch('api/journals.php?action=get_accounts');
        const data = await response.json();
        
        if (data.success) {
            accounts = data.accounts;
        }
    } catch (error) {
        console.error('خطأ في تحميل الحسابات:', error);
    }
}

// تحميل القيود
async function loadJournals() {
    try {
        const response = await fetch('api/journals.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            displayJournals(data.journals);
        }
    } catch (error) {
        console.error('خطأ في تحميل القيود:', error);
        showError('فشل تحميل القيود');
    }
}

// عرض القيود
function displayJournals(journals) {
    const tbody = document.getElementById('journalsTableBody');
    
    if (journals.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 2rem; color: #64748b;">
                    لا توجد قيود يومية
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = journals.map(journal => `
        <tr>
            <td>${journal.id}</td>
            <td>${journal.date}</td>
            <td>${journal.description || '-'}</td>
            <td>${parseFloat(journal.totalDebit).toFixed(2)}</td>
            <td>${parseFloat(journal.totalCredit).toFixed(2)}</td>
            <td>
                <span class="badge badge-${journal.status === 'posted' ? 'posted' : 'draft'}">
                    ${journal.status === 'posted' ? '✅ مرحّل' : '📝 مسودة'}
                </span>
            </td>
            <td>
                <span class="badge badge-${journal.voucherType === 'receipt' ? 'receipt' : journal.voucherType === 'payment' ? 'payment' : 'manual'}">
                    ${journal.voucherTypeText}
                </span>
            </td>
            <td>
                <button class="action-btn btn-view" onclick="viewJournal(${journal.id})" title="عرض">
                    👁️
                </button>
                ${journal.status === 'draft' && journal.voucherType === 'none' ? `
                    <button class="action-btn btn-edit" onclick="editJournal(${journal.id})" title="تعديل">
                        ✏️
                    </button>
                    <button class="action-btn btn-delete" onclick="deleteJournal(${journal.id})" title="حذف">
                        🗑️
                    </button>
                    <button class="action-btn btn-post" onclick="postJournal(${journal.id})" title="ترحيل">
                        ✅
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

// فتح نافذة إضافة قيد
function openJournalModal() {
    currentJournalId = null;
    document.getElementById('modalTitle').textContent = '➕ قيد يومية جديد';
    document.getElementById('journalForm').reset();
    document.getElementById('journalId').value = '';
    document.getElementById('journalDate').valueAsDate = new Date();
    
    // إضافة سطرين افتراضيين
    document.getElementById('journalDetailsTable').innerHTML = '';
    addDetailLine();
    addDetailLine();
    
    document.getElementById('journalModal').style.display = 'block';
}

// إغلاق النافذة
function closeJournalModal() {
    document.getElementById('journalModal').style.display = 'none';
}

// إضافة سطر تفاصيل
function addDetailLine() {
    const tbody = document.getElementById('journalDetailsTable');
    const row = tbody.insertRow();
    
    row.innerHTML = `
        <td>
            <select class="form-control detail-account" onchange="calculateBalance()" required>
                <option value="">-- اختر الحساب --</option>
                ${accounts.map(acc => `
                    <option value="${acc.id}">${acc.code} - ${acc.nameAr}</option>
                `).join('')}
            </select>
        </td>
        <td>
            <input type="text" class="form-control detail-description" placeholder="البيان">
        </td>
        <td>
            <input type="number" class="form-control detail-debit" step="0.01" min="0" value="0" 
                   onchange="handleDebitChange(this)" oninput="calculateBalance()">
        </td>
        <td>
            <input type="number" class="form-control detail-credit" step="0.01" min="0" value="0" 
                   onchange="handleCreditChange(this)" oninput="calculateBalance()">
        </td>
        <td>
            <button type="button" class="remove-line-btn" onclick="removeDetailLine(this)" title="حذف">
                ❌
            </button>
        </td>
    `;
}

// حذف سطر
function removeDetailLine(btn) {
    const row = btn.closest('tr');
    row.remove();
    calculateBalance();
}

// عند إدخال مبلغ مدين، صفّر الدائن
function handleDebitChange(input) {
    const row = input.closest('tr');
    const creditInput = row.querySelector('.detail-credit');
    if (parseFloat(input.value) > 0) {
        creditInput.value = 0;
    }
    calculateBalance();
}

// عند إدخال مبلغ دائن، صفّر المدين
function handleCreditChange(input) {
    const row = input.closest('tr');
    const debitInput = row.querySelector('.detail-debit');
    if (parseFloat(input.value) > 0) {
        debitInput.value = 0;
    }
    calculateBalance();
}

// حساب التوازن
function calculateBalance() {
    const debitInputs = document.querySelectorAll('.detail-debit');
    const creditInputs = document.querySelectorAll('.detail-credit');
    
    let totalDebit = 0;
    let totalCredit = 0;
    
    debitInputs.forEach(input => {
        totalDebit += parseFloat(input.value) || 0;
    });
    
    creditInputs.forEach(input => {
        totalCredit += parseFloat(input.value) || 0;
    });
    
    const difference = Math.abs(totalDebit - totalCredit);
    const isBalanced = difference < 0.01;
    
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
    document.getElementById('difference').textContent = difference.toFixed(2);
    
    const statusElement = document.getElementById('balanceStatus');
    if (isBalanced) {
        statusElement.textContent = '✅ متوازن';
        statusElement.className = 'balance-value balance-balanced';
    } else {
        statusElement.textContent = '❌ غير متوازن';
        statusElement.className = 'balance-value balance-unbalanced';
    }
}

// حفظ القيد
async function saveJournal(status) {
    const date = document.getElementById('journalDate').value;
    const description = document.getElementById('journalDescription').value;
    
    if (!date || !description) {
        showError('يرجى ملء جميع الحقول المطلوبة');
        return;
    }
    
    // جمع التفاصيل
    const details = [];
    const rows = document.querySelectorAll('#journalDetailsTable tr');
    
    rows.forEach(row => {
        const accountId = row.querySelector('.detail-account').value;
        const desc = row.querySelector('.detail-description').value;
        const debit = parseFloat(row.querySelector('.detail-debit').value) || 0;
        const credit = parseFloat(row.querySelector('.detail-credit').value) || 0;
        
        if (accountId && (debit > 0 || credit > 0)) {
            details.push({
                accountId: parseInt(accountId),
                description: desc || description,
                debit: debit,
                credit: credit
            });
        }
    });
    
    if (details.length < 2) {
        showError('يجب إضافة سطرين على الأقل');
        return;
    }
    
    // التحقق من التوازن
    const totalDebit = details.reduce((sum, d) => sum + d.debit, 0);
    const totalCredit = details.reduce((sum, d) => sum + d.credit, 0);
    
    if (Math.abs(totalDebit - totalCredit) > 0.01) {
        showError('القيد غير متوازن. المدين: ' + totalDebit.toFixed(2) + ' - الدائن: ' + totalCredit.toFixed(2));
        return;
    }
    
    const journalData = {
        date: date,
        description: description,
        details: details,
        status: status
    };
    
    const journalId = document.getElementById('journalId').value;
    const url = journalId ? 'api/journals.php?action=update' : 'api/journals.php?action=create';
    
    if (journalId) {
        journalData.id = parseInt(journalId);
    }
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(journalData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            closeJournalModal();
            loadJournals();
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('خطأ:', error);
        showError('فشل حفظ القيد');
    }
}

// عرض قيد
async function viewJournal(id) {
    try {
        const response = await fetch(`api/journals.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const journal = data.journal;
            alert(`
القيد رقم: ${journal.id}
التاريخ: ${journal.date}
البيان: ${journal.description}
المدين: ${parseFloat(journal.totalDebit).toFixed(2)}
الدائن: ${parseFloat(journal.totalCredit).toFixed(2)}
الحالة: ${journal.status === 'posted' ? 'مرحّل' : 'مسودة'}

التفاصيل:
${journal.details.map(d => `
- ${d.accountCode} - ${d.accountName}
  مدين: ${parseFloat(d.debit).toFixed(2)} | دائن: ${parseFloat(d.credit).toFixed(2)}
`).join('\n')}
            `);
        }
    } catch (error) {
        console.error('خطأ:', error);
        showError('فشل عرض القيد');
    }
}

// تعديل قيد
async function editJournal(id) {
    try {
        const response = await fetch(`api/journals.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const journal = data.journal;
            
            document.getElementById('modalTitle').textContent = '✏️ تعديل قيد يومية';
            document.getElementById('journalId').value = journal.id;
            document.getElementById('journalDate').value = journal.date;
            document.getElementById('journalDescription').value = journal.description;
            
            // إضافة التفاصيل
            document.getElementById('journalDetailsTable').innerHTML = '';
            journal.details.forEach(detail => {
                addDetailLine();
                const lastRow = document.querySelector('#journalDetailsTable tr:last-child');
                lastRow.querySelector('.detail-account').value = detail.accountId;
                lastRow.querySelector('.detail-description').value = detail.description;
                lastRow.querySelector('.detail-debit').value = parseFloat(detail.debit);
                lastRow.querySelector('.detail-credit').value = parseFloat(detail.credit);
            });
            
            calculateBalance();
            document.getElementById('journalModal').style.display = 'block';
        }
    } catch (error) {
        console.error('خطأ:', error);
        showError('فشل تحميل القيد');
    }
}

// حذف قيد
async function deleteJournal(id) {
    if (!confirm('هل أنت متأكد من حذف هذا القيد؟')) {
        return;
    }
    
    try {
        const response = await fetch('api/journals.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            loadJournals();
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('خطأ:', error);
        showError('فشل حذف القيد');
    }
}

// ترحيل قيد
async function postJournal(id) {
    if (!confirm('هل أنت متأكد من ترحيل هذا القيد؟')) {
        return;
    }
    
    try {
        const response = await fetch('api/journals.php?action=post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            loadJournals();
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('خطأ:', error);
        showError('فشل ترحيل القيد');
    }
}

// بحث في القيود
function searchJournals() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('journalsTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
    }
}

// رسائل النجاح والخطأ
function showSuccess(message) {
    alert('✅ ' + message);
}

function showError(message) {
    alert('❌ ' + message);
}

// إغلاق النافذة عند الضغط خارجها
window.onclick = function(event) {
    const modal = document.getElementById('journalModal');
    if (event.target === modal) {
        closeJournalModal();
    }
}
