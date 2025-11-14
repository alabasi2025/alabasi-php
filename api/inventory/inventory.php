<?php
/**
 * نظام الأباسي - API المخزون
 * Inventory API
 */

define('ALABASI_SYSTEM', true);
require_once '../../config/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

// التحقق من تسجيل الدخول
if (!is_logged_in()) {
    json_error('يجب تسجيل الدخول أولاً');
}

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // ============================================
        // الأصناف
        // ============================================
        case 'get_items':
            $search = $_GET['search'] ?? '';
            $categoryId = $_GET['categoryId'] ?? '';
            
            $sql = "SELECT i.*, c.nameAr as categoryName 
                    FROM items i
                    LEFT JOIN item_categories c ON i.categoryId = c.id
                    WHERE 1=1";
            $params = [];
            
            if ($search) {
                $sql .= " AND (i.code LIKE ? OR i.nameAr LIKE ? OR i.barcode LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($categoryId) {
                $sql .= " AND i.categoryId = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY i.code";
            
            $items = $db->fetchAll($sql, $params);
            json_success('تم جلب الأصناف', ['items' => $items]);
            break;

        case 'get_item':
            $id = $_GET['id'] ?? 0;
            $item = $db->fetchOne("SELECT * FROM items WHERE id = ?", [$id]);
            
            if (!$item) {
                json_error('الصنف غير موجود');
            }
            
            json_success('تم جلب الصنف', ['item' => $item]);
            break;

        case 'save_item':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // التحقق من البيانات
            $required = ['code', 'nameAr', 'categoryId', 'unit', 'purchasePrice', 'salePrice'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    json_error("حقل {$field} مطلوب");
                }
            }
            
            // التحقق من تكرار الكود
            $existing = $db->fetchOne(
                "SELECT id FROM items WHERE code = ? AND id != ?",
                [$data['code'], $data['id'] ?? 0]
            );
            
            if ($existing) {
                json_error('كود الصنف مستخدم مسبقاً');
            }
            
            $itemData = [
                'code' => $data['code'],
                'nameAr' => $data['nameAr'],
                'nameEn' => $data['nameEn'] ?? null,
                'categoryId' => $data['categoryId'],
                'barcode' => $data['barcode'] ?? null,
                'unit' => $data['unit'],
                'purchasePrice' => $data['purchasePrice'],
                'salePrice' => $data['salePrice'],
                'minStock' => $data['minStock'] ?? 0,
                'maxStock' => $data['maxStock'] ?? 0,
                'reorderPoint' => $data['reorderPoint'] ?? 0,
                'description' => $data['description'] ?? null,
                'isActive' => $data['isActive'] ?? 1
            ];
            
            if (!empty($data['id'])) {
                // تحديث
                $db->update('items', $itemData, 'id = ?', [$data['id']]);
                log_activity('update', 'items', $data['id'], $itemData);
                json_success('تم تحديث الصنف بنجاح', ['id' => $data['id']]);
            } else {
                // إضافة
                $itemData['createdBy'] = get_user_id();
                $id = $db->insert('items', $itemData);
                log_activity('create', 'items', $id, $itemData);
                json_success('تم إضافة الصنف بنجاح', ['id' => $id]);
            }
            break;

        case 'delete_item':
            $id = $_POST['id'] ?? 0;
            
            // التحقق من وجود حركات
            $hasMovements = $db->fetchColumn(
                "SELECT COUNT(*) FROM stock_movements WHERE itemId = ?",
                [$id]
            );
            
            if ($hasMovements > 0) {
                json_error('لا يمكن حذف الصنف لوجود حركات مخزنية عليه');
            }
            
            $db->delete('items', 'id = ?', [$id]);
            log_activity('delete', 'items', $id);
            json_success('تم حذف الصنف بنجاح');
            break;

        // ============================================
        // الفئات
        // ============================================
        case 'get_categories':
            $categories = $db->fetchAll(
                "SELECT * FROM item_categories WHERE isActive = 1 ORDER BY code"
            );
            json_success('تم جلب الفئات', ['categories' => $categories]);
            break;

        // ============================================
        // المستودعات
        // ============================================
        case 'get_warehouses':
            $warehouses = $db->fetchAll(
                "SELECT * FROM warehouses WHERE isActive = 1 ORDER BY code"
            );
            json_success('تم جلب المستودعات', ['warehouses' => $warehouses]);
            break;

        // ============================================
        // حركات المخزون
        // ============================================
        case 'get_movements':
            $startDate = $_GET['startDate'] ?? date('Y-m-01');
            $endDate = $_GET['endDate'] ?? date('Y-m-d');
            $warehouseId = $_GET['warehouseId'] ?? '';
            $itemId = $_GET['itemId'] ?? '';
            
            $sql = "SELECT sm.*, i.nameAr as itemName, i.code as itemCode,
                           w.nameAr as warehouseName,
                           tw.nameAr as toWarehouseName,
                           u.nameAr as createdByName
                    FROM stock_movements sm
                    INNER JOIN items i ON sm.itemId = i.id
                    INNER JOIN warehouses w ON sm.warehouseId = w.id
                    LEFT JOIN warehouses tw ON sm.toWarehouseId = tw.id
                    LEFT JOIN users u ON sm.createdBy = u.id
                    WHERE sm.movementDate BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            
            if ($warehouseId) {
                $sql .= " AND (sm.warehouseId = ? OR sm.toWarehouseId = ?)";
                $params[] = $warehouseId;
                $params[] = $warehouseId;
            }
            
            if ($itemId) {
                $sql .= " AND sm.itemId = ?";
                $params[] = $itemId;
            }
            
            $sql .= " ORDER BY sm.movementDate DESC, sm.id DESC";
            
            $movements = $db->fetchAll($sql, $params);
            json_success('تم جلب الحركات', ['movements' => $movements]);
            break;

        case 'save_movement':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // التحقق من البيانات
            $required = ['movementType', 'movementDate', 'warehouseId', 'itemId', 'quantity'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    json_error("حقل {$field} مطلوب");
                }
            }
            
            if ($data['movementType'] === 'transfer' && empty($data['toWarehouseId'])) {
                json_error('يجب تحديد المستودع المستهدف للتحويل');
            }
            
            $db->beginTransaction();
            
            try {
                // إنشاء رقم الحركة
                $movementNumber = 'SM-' . date('Y') . '-' . str_pad(
                    $db->fetchColumn("SELECT COUNT(*) + 1 FROM stock_movements") ?? 1,
                    5, '0', STR_PAD_LEFT
                );
                
                // حفظ الحركة
                $movementData = [
                    'movementNumber' => $movementNumber,
                    'movementType' => $data['movementType'],
                    'movementDate' => $data['movementDate'],
                    'warehouseId' => $data['warehouseId'],
                    'toWarehouseId' => $data['toWarehouseId'] ?? null,
                    'itemId' => $data['itemId'],
                    'quantity' => $data['quantity'],
                    'unitPrice' => $data['unitPrice'] ?? 0,
                    'totalAmount' => ($data['quantity'] * ($data['unitPrice'] ?? 0)),
                    'notes' => $data['notes'] ?? null,
                    'createdBy' => get_user_id()
                ];
                
                $movementId = $db->insert('stock_movements', $movementData);
                
                // تحديث الرصيد
                update_inventory_balance($db, $data['warehouseId'], $data['itemId'], $data['movementType'], $data['quantity'], $data['unitPrice'] ?? 0);
                
                if ($data['movementType'] === 'transfer' && $data['toWarehouseId']) {
                    update_inventory_balance($db, $data['toWarehouseId'], $data['itemId'], 'in', $data['quantity'], $data['unitPrice'] ?? 0);
                }
                
                $db->commit();
                log_activity('create', 'stock_movements', $movementId, $movementData);
                
                json_success('تم حفظ الحركة بنجاح', ['id' => $movementId, 'movementNumber' => $movementNumber]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;

        // ============================================
        // رصيد المخزون
        // ============================================
        case 'get_balance':
            $warehouseId = $_GET['warehouseId'] ?? '';
            $itemId = $_GET['itemId'] ?? '';
            
            $sql = "SELECT ib.*, i.code as itemCode, i.nameAr as itemName, i.unit,
                           w.nameAr as warehouseName,
                           i.minStock, i.reorderPoint
                    FROM inventory_balance ib
                    INNER JOIN items i ON ib.itemId = i.id
                    INNER JOIN warehouses w ON ib.warehouseId = w.id
                    WHERE 1=1";
            $params = [];
            
            if ($warehouseId) {
                $sql .= " AND ib.warehouseId = ?";
                $params[] = $warehouseId;
            }
            
            if ($itemId) {
                $sql .= " AND ib.itemId = ?";
                $params[] = $itemId;
            }
            
            $sql .= " ORDER BY i.code";
            
            $balance = $db->fetchAll($sql, $params);
            json_success('تم جلب الرصيد', ['balance' => $balance]);
            break;

        default:
            json_error('عملية غير معروفة');
    }

} catch (Exception $e) {
    json_error('حدث خطأ: ' . $e->getMessage());
}

// ============================================
// دوال مساعدة
// ============================================

function update_inventory_balance($db, $warehouseId, $itemId, $movementType, $quantity, $unitPrice) {
    // جلب الرصيد الحالي
    $balance = $db->fetchOne(
        "SELECT * FROM inventory_balance WHERE warehouseId = ? AND itemId = ?",
        [$warehouseId, $itemId]
    );
    
    if (!$balance) {
        // إنشاء رصيد جديد
        $db->insert('inventory_balance', [
            'warehouseId' => $warehouseId,
            'itemId' => $itemId,
            'quantity' => ($movementType === 'in' || $movementType === 'adjustment') ? $quantity : -$quantity,
            'averageCost' => $unitPrice,
            'totalValue' => $quantity * $unitPrice
        ]);
    } else {
        // تحديث الرصيد
        $oldQty = $balance['quantity'];
        $oldCost = $balance['averageCost'];
        
        if ($movementType === 'in') {
            $newQty = $oldQty + $quantity;
            $newCost = (($oldQty * $oldCost) + ($quantity * $unitPrice)) / $newQty;
        } else {
            $newQty = $oldQty - $quantity;
            $newCost = $oldCost;
        }
        
        $db->update('inventory_balance', [
            'quantity' => $newQty,
            'averageCost' => $newCost,
            'totalValue' => $newQty * $newCost
        ], 'id = ?', [$balance['id']]);
    }
}
