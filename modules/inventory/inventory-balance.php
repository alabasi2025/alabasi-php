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
    <title>رصيد المخزون - نظام الأباسي</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>📊 رصيد المخزون</h1>
        <button onclick="window.print()" class="btn btn-secondary">🖨️ طباعة</button>
        
        <div class="filters">
            <select id="warehouseFilter" onchange="loadBalance()">
                <option value="">جميع المستودعات</option>
            </select>
        </div>
        
        <table id="balanceTable">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الصنف</th>
                    <th>المستودع</th>
                    <th>الكمية</th>
                    <th>التكلفة المتوسطة</th>
                    <th>القيمة الإجمالية</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="5">الإجمالي</th>
                    <th id="totalValue">0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <script src="../../assets/js/inventory.js"></script>
</body>
</html>
