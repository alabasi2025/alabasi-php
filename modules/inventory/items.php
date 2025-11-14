<?php
define('ALABASI_SYSTEM', true);
require_once '../../config/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_login();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الأصناف - نظام الأباسي</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>📦 إدارة الأصناف</h1>
        <button onclick="showAddModal()" class="btn btn-primary">➕ إضافة صنف</button>
        
        <div class="filters">
            <input type="text" id="searchInput" placeholder="بحث..." onkeyup="loadItems()">
            <select id="categoryFilter" onchange="loadItems()">
                <option value="">جميع الفئات</option>
            </select>
        </div>
        
        <table id="itemsTable">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الفئة</th>
                    <th>الوحدة</th>
                    <th>سعر الشراء</th>
                    <th>سعر البيع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">إضافة صنف</h2>
            <form id="itemForm">
                <input type="hidden" id="itemId">
                <input type="text" id="code" placeholder="الكود" required>
                <input type="text" id="nameAr" placeholder="الاسم بالعربية" required>
                <select id="categoryId" required></select>
                <input type="text" id="unit" placeholder="الوحدة" required>
                <input type="number" id="purchasePrice" placeholder="سعر الشراء" step="0.01" required>
                <input type="number" id="salePrice" placeholder="سعر البيع" step="0.01" required>
                <button type="submit">حفظ</button>
                <button type="button" onclick="closeModal()">إلغاء</button>
            </form>
        </div>
    </div>
    
    <script src="../../assets/js/inventory.js"></script>
</body>
</html>
