/**
 * نظام الأباسي - المخزون
 * Inventory JavaScript
 */

const API_URL = '../../api/inventory/inventory.php';

// ============================================
// الأصناف
// ============================================

function loadItems() {
    const search = document.getElementById('searchInput')?.value || '';
    const categoryId = document.getElementById('categoryFilter')?.value || '';
    
    fetch(`${API_URL}?action=get_items&search=${search}&categoryId=${categoryId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayItems(data.data.items);
            }
        });
}

function displayItems(items) {
    const tbody = document.querySelector('#itemsTable tbody');
    tbody.innerHTML = items.map(item => `
        <tr>
            <td>${item.code}</td>
            <td>${item.nameAr}</td>
            <td>${item.categoryName || '-'}</td>
            <td>${item.unit}</td>
            <td>${parseFloat(item.purchasePrice).toFixed(2)}</td>
            <td>${parseFloat(item.salePrice).toFixed(2)}</td>
            <td>
                <button onclick="editItem(${item.id})">تعديل</button>
                <button onclick="deleteItem(${item.id})">حذف</button>
            </td>
        </tr>
    `).join('');
}

function showAddModal() {
    document.getElementById('itemId').value = '';
    document.getElementById('itemForm').reset();
    document.getElementById('modalTitle').textContent = 'إضافة صنف';
    document.getElementById('itemModal').style.display = 'block';
    loadCategories();
}

function editItem(id) {
    fetch(`${API_URL}?action=get_item&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = data.data.item;
                document.getElementById('itemId').value = item.id;
                document.getElementById('code').value = item.code;
                document.getElementById('nameAr').value = item.nameAr;
                document.getElementById('categoryId').value = item.categoryId;
                document.getElementById('unit').value = item.unit;
                document.getElementById('purchasePrice').value = item.purchasePrice;
                document.getElementById('salePrice').value = item.salePrice;
                document.getElementById('modalTitle').textContent = 'تعديل صنف';
                document.getElementById('itemModal').style.display = 'block';
                loadCategories();
            }
        });
}

function deleteItem(id) {
    if (!confirm('هل أنت متأكد من الحذف؟')) return;
    
    fetch(API_URL, {
        method: 'POST',
        body: new URLSearchParams({ action: 'delete_item', id })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) loadItems();
    });
}

function loadCategories() {
    fetch(`${API_URL}?action=get_categories`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('#categoryId, #categoryFilter');
                selects.forEach(select => {
                    if (select.id === 'categoryFilter') {
                        select.innerHTML = '<option value="">جميع الفئات</option>';
                    } else {
                        select.innerHTML = '<option value="">اختر الفئة</option>';
                    }
                    data.data.categories.forEach(cat => {
                        select.innerHTML += `<option value="${cat.id}">${cat.nameAr}</option>`;
                    });
                });
            }
        });
}

function closeModal() {
    document.getElementById('itemModal').style.display = 'none';
}

document.getElementById('itemForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const data = {
        id: document.getElementById('itemId').value,
        code: document.getElementById('code').value,
        nameAr: document.getElementById('nameAr').value,
        categoryId: document.getElementById('categoryId').value,
        unit: document.getElementById('unit').value,
        purchasePrice: document.getElementById('purchasePrice').value,
        salePrice: document.getElementById('salePrice').value
    };
    
    fetch(`${API_URL}?action=save_item`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeModal();
            loadItems();
        }
    });
});

// ============================================
// حركات المخزون
// ============================================

function loadMovements() {
    const startDate = document.getElementById('startDate')?.value || '';
    const endDate = document.getElementById('endDate')?.value || '';
    const warehouseId = document.getElementById('warehouseFilter')?.value || '';
    
    fetch(`${API_URL}?action=get_movements&startDate=${startDate}&endDate=${endDate}&warehouseId=${warehouseId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayMovements(data.data.movements);
            }
        });
}

function displayMovements(movements) {
    const tbody = document.querySelector('#movementsTable tbody');
    const types = { in: 'إدخال', out: 'إخراج', transfer: 'تحويل' };
    
    tbody.innerHTML = movements.map(m => `
        <tr>
            <td>${m.movementNumber}</td>
            <td>${m.movementDate}</td>
            <td>${types[m.movementType]}</td>
            <td>${m.itemCode} - ${m.itemName}</td>
            <td>${m.warehouseName}${m.toWarehouseName ? ' → ' + m.toWarehouseName : ''}</td>
            <td>${parseFloat(m.quantity).toFixed(2)}</td>
            <td>${parseFloat(m.totalAmount).toFixed(2)}</td>
        </tr>
    `).join('');
}

function showAddMovementModal() {
    document.getElementById('movementForm').reset();
    document.getElementById('movementModal').style.display = 'block';
    loadWarehouses();
    loadItemsForSelect();
}

function closeMovementModal() {
    document.getElementById('movementModal').style.display = 'none';
}

function toggleTransferFields() {
    const type = document.getElementById('movementType').value;
    const toWarehouse = document.getElementById('toWarehouseId');
    toWarehouse.style.display = type === 'transfer' ? 'block' : 'none';
}

function loadWarehouses() {
    fetch(`${API_URL}?action=get_warehouses`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('#warehouseId, #toWarehouseId, #warehouseFilter');
                selects.forEach(select => {
                    if (select.id === 'warehouseFilter') {
                        select.innerHTML = '<option value="">جميع المستودعات</option>';
                    } else {
                        select.innerHTML = '<option value="">اختر المستودع</option>';
                    }
                    data.data.warehouses.forEach(w => {
                        select.innerHTML += `<option value="${w.id}">${w.nameAr}</option>`;
                    });
                });
            }
        });
}

function loadItemsForSelect() {
    fetch(`${API_URL}?action=get_items`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('itemId');
                select.innerHTML = '<option value="">اختر الصنف</option>';
                data.data.items.forEach(item => {
                    select.innerHTML += `<option value="${item.id}">${item.code} - ${item.nameAr}</option>`;
                });
            }
        });
}

document.getElementById('movementForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const data = {
        movementType: document.getElementById('movementType').value,
        movementDate: document.getElementById('movementDate').value,
        warehouseId: document.getElementById('warehouseId').value,
        toWarehouseId: document.getElementById('toWarehouseId').value,
        itemId: document.getElementById('itemId').value,
        quantity: document.getElementById('quantity').value,
        unitPrice: document.getElementById('unitPrice').value,
        notes: document.getElementById('notes').value
    };
    
    fetch(`${API_URL}?action=save_movement`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeMovementModal();
            loadMovements();
        }
    });
});

// ============================================
// رصيد المخزون
// ============================================

function loadBalance() {
    const warehouseId = document.getElementById('warehouseFilter')?.value || '';
    
    fetch(`${API_URL}?action=get_balance&warehouseId=${warehouseId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayBalance(data.data.balance);
            }
        });
}

function displayBalance(balance) {
    const tbody = document.querySelector('#balanceTable tbody');
    let total = 0;
    
    tbody.innerHTML = balance.map(b => {
        total += parseFloat(b.totalValue);
        const status = b.quantity <= b.minStock ? '⚠️ نقص' : '✅ جيد';
        return `
            <tr>
                <td>${b.itemCode}</td>
                <td>${b.itemName}</td>
                <td>${b.warehouseName}</td>
                <td>${parseFloat(b.quantity).toFixed(2)} ${b.unit}</td>
                <td>${parseFloat(b.averageCost).toFixed(2)}</td>
                <td>${parseFloat(b.totalValue).toFixed(2)}</td>
                <td>${status}</td>
            </tr>
        `;
    }).join('');
    
    document.getElementById('totalValue').textContent = total.toFixed(2);
}

// ============================================
// التحميل التلقائي
// ============================================

window.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('itemsTable')) {
        loadItems();
        loadCategories();
    }
    if (document.getElementById('movementsTable')) {
        loadMovements();
        loadWarehouses();
    }
    if (document.getElementById('balanceTable')) {
        loadBalance();
        loadWarehouses();
    }
});
