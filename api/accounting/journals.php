<?php
/**
 * API لإدارة القيود اليومية الذكية
 * Smart Journal Entries API
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
            listJournals($pdo);
            break;
            
        case 'get':
            getJournal($pdo, $_GET['id'] ?? 0);
            break;
            
        case 'create':
            createJournal($pdo, $userId);
            break;
            
        case 'update':
            updateJournal($pdo, $userId);
            break;
            
        case 'delete':
            deleteJournal($pdo, $_POST['id'] ?? 0);
            break;
            
        case 'post':
            postJournal($pdo, $userId, $_POST['id'] ?? 0);
            break;
            
        case 'get_next_number':
            getNextJournalNumber($pdo);
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

function listJournals($pdo) {
    $journals = $pdo->query("
        SELECT 
            j.*,
            u.nameAr as createdByName,
            CASE 
                WHEN j.voucherType = 'receipt' THEN 'سند قبض'
                WHEN j.voucherType = 'payment' THEN 'سند صرف'
                ELSE 'قيد يدوي'
            END as voucherTypeText
        FROM journals j
        LEFT JOIN users u ON j.createdBy = u.id
        ORDER BY j.date DESC, j.createdAt DESC
    ")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'journals' => $journals
    ]);
}

function getJournal($pdo, $id) {
    // جلب القيد
    $stmt = $pdo->prepare("
        SELECT 
            j.*,
            u.nameAr as createdByName
        FROM journals j
        LEFT JOIN users u ON j.createdBy = u.id
        WHERE j.id = ?
    ");
    $stmt->execute([$id]);
    $journal = $stmt->fetch();
    
    if (!$journal) {
        throw new Exception('القيد غير موجود');
    }
    
    // جلب تفاصيل القيد
    $stmt = $pdo->prepare("
        SELECT 
            jd.*,
            a.code as accountCode,
            a.nameAr as accountName
        FROM journal_details jd
        LEFT JOIN accounts a ON jd.accountId = a.id
        WHERE jd.journalId = ?
        ORDER BY jd.id
    ");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    
    $journal['details'] = $details;
    
    echo json_encode([
        'success' => true,
        'journal' => $journal
    ]);
}

function createJournal($pdo, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات
    if (empty($data['date']) || empty($data['description']) || empty($data['details'])) {
        throw new Exception('يرجى ملء جميع الحقول المطلوبة');
    }
    
    // التحقق من التوازن
    $totalDebit = 0;
    $totalCredit = 0;
    foreach ($data['details'] as $detail) {
        $totalDebit += floatval($detail['debit'] ?? 0);
        $totalCredit += floatval($detail['credit'] ?? 0);
    }
    
    if (abs($totalDebit - $totalCredit) > 0.01) {
        throw new Exception('القيد غير متوازن. المدين: ' . $totalDebit . ' - الدائن: ' . $totalCredit);
    }
    
    $pdo->beginTransaction();
    
    try {
        // إدراج القيد
        $stmt = $pdo->prepare("
            INSERT INTO journals (
                date, description, totalDebit, totalCredit,
                status, createdBy
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['date'],
            $data['description'],
            $totalDebit,
            $totalCredit,
            $data['status'] ?? 'draft',
            $userId
        ]);
        
        $journalId = $pdo->lastInsertId();
        
        // إدراج تفاصيل القيد
        $stmt = $pdo->prepare("
            INSERT INTO journal_details (
                journalId, accountId, description, debit, credit
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($data['details'] as $detail) {
            $stmt->execute([
                $journalId,
                $detail['accountId'],
                $detail['description'] ?? $data['description'],
                floatval($detail['debit'] ?? 0),
                floatval($detail['credit'] ?? 0)
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء القيد بنجاح',
            'journalId' => $journalId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateJournal($pdo, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    
    // التحقق من وجود القيد
    $stmt = $pdo->prepare("SELECT status, voucherType FROM journals WHERE id = ?");
    $stmt->execute([$id]);
    $journal = $stmt->fetch();
    
    if (!$journal) {
        throw new Exception('القيد غير موجود');
    }
    
    if ($journal['status'] === 'posted') {
        throw new Exception('لا يمكن تعديل قيد مرحّل');
    }
    
    if ($journal['voucherType'] !== 'none') {
        throw new Exception('لا يمكن تعديل قيد مرتبط بسند');
    }
    
    // التحقق من التوازن
    $totalDebit = 0;
    $totalCredit = 0;
    foreach ($data['details'] as $detail) {
        $totalDebit += floatval($detail['debit'] ?? 0);
        $totalCredit += floatval($detail['credit'] ?? 0);
    }
    
    if (abs($totalDebit - $totalCredit) > 0.01) {
        throw new Exception('القيد غير متوازن');
    }
    
    $pdo->beginTransaction();
    
    try {
        // تحديث القيد
        $stmt = $pdo->prepare("
            UPDATE journals SET
                date = ?,
                description = ?,
                totalDebit = ?,
                totalCredit = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['date'],
            $data['description'],
            $totalDebit,
            $totalCredit,
            $id
        ]);
        
        // حذف التفاصيل القديمة
        $pdo->prepare("DELETE FROM journal_details WHERE journalId = ?")->execute([$id]);
        
        // إدراج التفاصيل الجديدة
        $stmt = $pdo->prepare("
            INSERT INTO journal_details (
                journalId, accountId, description, debit, credit
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($data['details'] as $detail) {
            $stmt->execute([
                $id,
                $detail['accountId'],
                $detail['description'] ?? $data['description'],
                floatval($detail['debit'] ?? 0),
                floatval($detail['credit'] ?? 0)
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث القيد بنجاح'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function deleteJournal($pdo, $id) {
    // التحقق من وجود القيد
    $stmt = $pdo->prepare("SELECT status, voucherType FROM journals WHERE id = ?");
    $stmt->execute([$id]);
    $journal = $stmt->fetch();
    
    if (!$journal) {
        throw new Exception('القيد غير موجود');
    }
    
    if ($journal['status'] === 'posted') {
        throw new Exception('لا يمكن حذف قيد مرحّل');
    }
    
    if ($journal['voucherType'] !== 'none') {
        throw new Exception('لا يمكن حذف قيد مرتبط بسند');
    }
    
    $pdo->beginTransaction();
    
    try {
        // حذف التفاصيل
        $pdo->prepare("DELETE FROM journal_details WHERE journalId = ?")->execute([$id]);
        
        // حذف القيد
        $pdo->prepare("DELETE FROM journals WHERE id = ?")->execute([$id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف القيد بنجاح'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function postJournal($pdo, $userId, $id) {
    // التحقق من وجود القيد
    $stmt = $pdo->prepare("SELECT status FROM journals WHERE id = ?");
    $stmt->execute([$id]);
    $journal = $stmt->fetch();
    
    if (!$journal) {
        throw new Exception('القيد غير موجود');
    }
    
    if ($journal['status'] === 'posted') {
        throw new Exception('القيد مرحّل مسبقاً');
    }
    
    // ترحيل القيد
    $stmt = $pdo->prepare("UPDATE journals SET status = 'posted' WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم ترحيل القيد بنجاح'
    ]);
}

function getNextJournalNumber($pdo) {
    $year = date('Y');
    $count = $pdo->query("SELECT COUNT(*) FROM journals WHERE YEAR(date) = $year")->fetchColumn();
    $number = 'JE-' . $year . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    
    echo json_encode([
        'success' => true,
        'journalNumber' => $number
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
