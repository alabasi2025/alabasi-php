<?php
/**
 * API لإدارة سندات الصرف
 * Payment Vouchers API
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'list':
            listVouchers($pdo);
            break;
            
        case 'get':
            getVoucher($pdo, $_GET['id'] ?? 0);
            break;
            
        case 'create':
            createVoucher($pdo, $userId);
            break;
            
        case 'update':
            updateVoucher($pdo, $userId);
            break;
            
        case 'delete':
            deleteVoucher($pdo, $_POST['id'] ?? 0);
            break;
            
        case 'post':
            postVoucher($pdo, $userId, $_POST['id'] ?? 0);
            break;
            
        case 'get_next_number':
            getNextVoucherNumber($pdo);
            break;
            
        case 'get_accounts':
            getAccounts($pdo);
            break;
            
        default:
            throw new Exception('إجراء غير معروف');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ============================================
// الدوال
// ============================================

function listVouchers($pdo) {
    $vouchers = $pdo->query("
        SELECT 
            pv.*,
            da.nameAr as debitAccountName,
            ca.nameAr as creditAccountName,
            u.nameAr as createdByName,
            pu.nameAr as postedByName
        FROM payment_vouchers pv
        LEFT JOIN accounts da ON pv.debitAccountId = da.id
        LEFT JOIN accounts ca ON pv.creditAccountId = ca.id
        LEFT JOIN users u ON pv.createdBy = u.id
        LEFT JOIN users pu ON pv.postedBy = pu.id
        ORDER BY pv.voucherDate DESC, pv.createdAt DESC
    ")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'vouchers' => $vouchers
    ]);
}

function getVoucher($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT 
            pv.*,
            da.nameAr as debitAccountName,
            ca.nameAr as creditAccountName
        FROM payment_vouchers pv
        LEFT JOIN accounts da ON pv.debitAccountId = da.id
        LEFT JOIN accounts ca ON pv.creditAccountId = ca.id
        WHERE pv.id = ?
    ");
    $stmt->execute([$id]);
    $voucher = $stmt->fetch();
    
    if (!$voucher) {
        throw new Exception('السند غير موجود');
    }
    
    echo json_encode([
        'success' => true,
        'voucher' => $voucher
    ]);
}

function createVoucher($pdo, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات
    if (empty($data['voucherDate']) || empty($data['paidTo']) || 
        empty($data['amount']) || empty($data['debitAccountId']) || 
        empty($data['creditAccountId'])) {
        throw new Exception('يرجى ملء جميع الحقول المطلوبة');
    }
    
    $pdo->beginTransaction();
    
    try {
        // الحصول على رقم السند التلقائي
        $voucherNumber = generateVoucherNumber($pdo, 'payment');
        
        // إدراج السند
        $stmt = $pdo->prepare("
            INSERT INTO payment_vouchers (
                voucherNumber, voucherDate, paidTo, amount, amountInWords,
                paymentMethod, checkNumber, bankName, checkDate,
                debitAccountId, creditAccountId, description, notes,
                status, unitId, companyId, branchId, createdBy
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $voucherNumber,
            $data['voucherDate'],
            $data['paidTo'],
            $data['amount'],
            $data['amountInWords'] ?? null,
            $data['paymentMethod'] ?? 'cash',
            $data['checkNumber'] ?? null,
            $data['bankName'] ?? null,
            $data['checkDate'] ?? null,
            $data['debitAccountId'],
            $data['creditAccountId'],
            $data['description'] ?? null,
            $data['notes'] ?? null,
            $data['status'] ?? 'draft',
            $data['unitId'] ?? null,
            $data['companyId'] ?? null,
            $data['branchId'] ?? null,
            $userId
        ]);
        
        $voucherId = $pdo->lastInsertId();
        
        // إذا كانت الحالة "مرحّل"، إنشاء قيد محاسبي
        if (($data['status'] ?? 'draft') === 'posted') {
            $journalId = createJournalEntry($pdo, $voucherId, $data, $userId, 'payment');
            
            // تحديث السند بربطه بالقيد
            $pdo->prepare("UPDATE payment_vouchers SET journalId = ?, postedAt = NOW(), postedBy = ? WHERE id = ?")
                ->execute([$journalId, $userId, $voucherId]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء سند الصرف بنجاح',
            'voucherId' => $voucherId,
            'voucherNumber' => $voucherNumber
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateVoucher($pdo, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    
    // التحقق من وجود السند
    $stmt = $pdo->prepare("SELECT status FROM payment_vouchers WHERE id = ?");
    $stmt->execute([$id]);
    $voucher = $stmt->fetch();
    
    if (!$voucher) {
        throw new Exception('السند غير موجود');
    }
    
    if ($voucher['status'] === 'posted') {
        throw new Exception('لا يمكن تعديل سند مرحّل');
    }
    
    // تحديث السند
    $stmt = $pdo->prepare("
        UPDATE payment_vouchers SET
            voucherDate = ?,
            paidTo = ?,
            amount = ?,
            amountInWords = ?,
            paymentMethod = ?,
            checkNumber = ?,
            bankName = ?,
            checkDate = ?,
            debitAccountId = ?,
            creditAccountId = ?,
            description = ?,
            notes = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['voucherDate'],
        $data['paidTo'],
        $data['amount'],
        $data['amountInWords'] ?? null,
        $data['paymentMethod'] ?? 'cash',
        $data['checkNumber'] ?? null,
        $data['bankName'] ?? null,
        $data['checkDate'] ?? null,
        $data['debitAccountId'],
        $data['creditAccountId'],
        $data['description'] ?? null,
        $data['notes'] ?? null,
        $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تحديث السند بنجاح'
    ]);
}

function deleteVoucher($pdo, $id) {
    // التحقق من وجود السند
    $stmt = $pdo->prepare("SELECT status, journalId FROM payment_vouchers WHERE id = ?");
    $stmt->execute([$id]);
    $voucher = $stmt->fetch();
    
    if (!$voucher) {
        throw new Exception('السند غير موجود');
    }
    
    if ($voucher['status'] === 'posted') {
        throw new Exception('لا يمكن حذف سند مرحّل. يجب إلغاء الترحيل أولاً');
    }
    
    // حذف السند
    $stmt = $pdo->prepare("DELETE FROM payment_vouchers WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم حذف السند بنجاح'
    ]);
}

function postVoucher($pdo, $userId, $id) {
    $pdo->beginTransaction();
    
    try {
        // جلب بيانات السند
        $stmt = $pdo->prepare("SELECT * FROM payment_vouchers WHERE id = ?");
        $stmt->execute([$id]);
        $voucher = $stmt->fetch();
        
        if (!$voucher) {
            throw new Exception('السند غير موجود');
        }
        
        if ($voucher['status'] === 'posted') {
            throw new Exception('السند مرحّل مسبقاً');
        }
        
        // إنشاء قيد محاسبي
        $journalId = createJournalEntry($pdo, $id, $voucher, $userId, 'payment');
        
        // تحديث حالة السند
        $stmt = $pdo->prepare("
            UPDATE payment_vouchers 
            SET status = 'posted', journalId = ?, postedAt = NOW(), postedBy = ?
            WHERE id = ?
        ");
        $stmt->execute([$journalId, $userId, $id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم ترحيل السند بنجاح',
            'journalId' => $journalId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function createJournalEntry($pdo, $voucherId, $data, $userId, $type) {
    // إنشاء قيد محاسبي
    $description = "سند صرف رقم: " . ($data['voucherNumber'] ?? $voucherId);
    if (!empty($data['description'])) {
        $description .= " - " . $data['description'];
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO journals (
            date, description, totalDebit, totalCredit, 
            status, voucherType, voucherId, createdBy
        ) VALUES (?, ?, ?, ?, 'posted', ?, ?, ?)
    ");
    
    $amount = is_array($data) ? $data['amount'] : $data->amount;
    
    $stmt->execute([
        is_array($data) ? $data['voucherDate'] : $data->voucherDate,
        $description,
        $amount,
        $amount,
        $type,
        $voucherId,
        $userId
    ]);
    
    $journalId = $pdo->lastInsertId();
    
    // إضافة تفاصيل القيد
    $stmt = $pdo->prepare("
        INSERT INTO journal_details (journalId, accountId, description, debit, credit)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $debitAccountId = is_array($data) ? $data['debitAccountId'] : $data->debitAccountId;
    $creditAccountId = is_array($data) ? $data['creditAccountId'] : $data->creditAccountId;
    
    // السطر المدين
    $stmt->execute([
        $journalId,
        $debitAccountId,
        $description,
        $amount,
        0
    ]);
    
    // السطر الدائن
    $stmt->execute([
        $journalId,
        $creditAccountId,
        $description,
        0,
        $amount
    ]);
    
    return $journalId;
}

function generateVoucherNumber($pdo, $type) {
    $year = date('Y');
    
    // الحصول على الرقم التالي
    $stmt = $pdo->prepare("
        SELECT prefix, currentNumber 
        FROM voucher_sequences 
        WHERE voucherType = ? AND year = ?
    ");
    $stmt->execute([$type, $year]);
    $sequence = $stmt->fetch();
    
    if (!$sequence) {
        // إنشاء تسلسل جديد للسنة الجديدة
        $prefix = $type === 'receipt' ? 'RV' : 'PV';
        $pdo->prepare("INSERT INTO voucher_sequences (voucherType, prefix, currentNumber, year) VALUES (?, ?, 1, ?)")
            ->execute([$type, $prefix, $year]);
        $number = 1;
    } else {
        $number = $sequence['currentNumber'];
        $prefix = $sequence['prefix'];
    }
    
    // تحديث الرقم
    $pdo->prepare("UPDATE voucher_sequences SET currentNumber = currentNumber + 1 WHERE voucherType = ? AND year = ?")
        ->execute([$type, $year]);
    
    return $prefix . '-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
}

function getNextVoucherNumber($pdo) {
    $number = generateVoucherNumber($pdo, 'payment');
    
    // إرجاع الرقم للخلف (لم يتم الحفظ بعد)
    $pdo->prepare("UPDATE voucher_sequences SET currentNumber = currentNumber - 1 WHERE voucherType = 'payment' AND year = ?")
        ->execute([date('Y')]);
    
    echo json_encode([
        'success' => true,
        'voucherNumber' => $number
    ]);
}

function getAccounts($pdo) {
    $accounts = $pdo->query("
        SELECT id, code, nameAr, accountType, accountNature
        FROM accounts
        WHERE isActive = 1
        ORDER BY code
    ")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'accounts' => $accounts
    ]);
}
