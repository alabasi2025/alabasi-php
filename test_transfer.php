<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ClearingAccountService;
use App\Models\Unit\Account;
use App\Models\Unit\JournalEntry;
use App\Models\Main\ClearingTransaction;

echo "🚀 اختبار نظام الحسابات الوسيطة\n";
echo str_repeat("=", 60) . "\n\n";

try {
    $clearingService = new ClearingAccountService();
    
    // البحث عن حساب الصندوق في كلا المؤسستين
    $sourceAccount = Account::on('unit_2')->where('company_id', 1)->where('account_number', '1110')->first();
    $targetAccount = Account::on('unit_2')->where('company_id', 2)->where('account_number', '1110')->first();
    
    if (!$sourceAccount || !$targetAccount) {
        echo "❌ خطأ: لم يتم العثور على حسابات الصندوق\n";
        exit(1);
    }
    
    echo "📊 معلومات قبل التحويل:\n";
    echo "   - رصيد صندوق أعمال الموظفين: " . number_format($sourceAccount->getBalance(), 2) . " ريال\n";
    echo "   - رصيد صندوق أعمال المحاسب: " . number_format($targetAccount->getBalance(), 2) . " ريال\n\n";
    
    echo "💸 إنشاء تحويل 5000 ريال...\n";
    
    $transfer = $clearingService->createTransfer([
        'source_unit_id' => 1,  // وحدة الحديدة
        'source_company_id' => 1,  // أعمال الموظفين
        'source_account_id' => $sourceAccount->id,
        'source_branch_id' => 1,
        
        'target_unit_id' => 1,  // نفس الوحدة
        'target_company_id' => 2,  // أعمال المحاسب
        'target_account_id' => $targetAccount->id,
        'target_branch_id' => 2,
        
        'amount' => 5000,
        'description' => 'تحويل تجريبي بين المؤسسات',
        'entry_date' => date('Y-m-d'),
        'user_id' => 1,
    ]);
    
    echo "✅ تم إنشاء التحويل برقم: " . $transfer->id . "\n\n";
    
    // تحديث الأرصدة
    $sourceAccount = $sourceAccount->fresh();
    $targetAccount = $targetAccount->fresh();
    
    echo "📊 معلومات بعد التحويل:\n";
    echo "   - رصيد صندوق أعمال الموظفين: " . number_format($sourceAccount->getBalance(), 2) . " ريال\n";
    echo "   - رصيد صندوق أعمال المحاسب: " . number_format($targetAccount->getBalance(), 2) . " ريال\n\n";
    
    // عرض القيود
    // الحصول على معلومات التحويل من القاعدة المركزية
    $clearingTrans = ClearingTransaction::on('main')->find($transfer->id);
    $sourceEntry = JournalEntry::on('unit_2')->find($clearingTrans->source_entry_id);
    $targetEntry = JournalEntry::on('unit_2')->find($clearingTrans->target_entry_id);
    
    echo "📝 القيد في أعمال الموظفين:\n";
    echo "   - رقم القيد: " . $sourceEntry->entry_number . "\n";
    echo "   - التاريخ: " . $sourceEntry->entry_date . "\n";
    echo "   - الحالة: " . ($sourceEntry->is_posted ? 'مرحل' : 'غير مرحل') . "\n";
    echo "   - التفاصيل:\n";
    foreach ($sourceEntry->details as $detail) {
        $account = Account::on('unit_2')->find($detail->account_id);
        echo "      * " . $account->account_name . " (" . $account->account_number . ")\n";
        echo "        مدين: " . number_format($detail->debit, 2) . " - دائن: " . number_format($detail->credit, 2) . "\n";
    }
    echo "\n";
    
    echo "📝 القيد في أعمال المحاسب:\n";
    echo "   - رقم القيد: " . $targetEntry->entry_number . "\n";
    echo "   - التاريخ: " . $targetEntry->entry_date . "\n";
    echo "   - الحالة: " . ($targetEntry->is_posted ? 'مرحل' : 'غير مرحل') . "\n";
    echo "   - التفاصيل:\n";
    foreach ($targetEntry->details as $detail) {
        $account = Account::on('unit_2')->find($detail->account_id);
        echo "      * " . $account->account_name . " (" . $account->account_number . ")\n";
        echo "        مدين: " . number_format($detail->debit, 2) . " - دائن: " . number_format($detail->credit, 2) . "\n";
    }
    echo "\n";
    
    // عرض معلومات التحويل في القاعدة المركزية
    $clearingTrans = ClearingTransaction::on('main')->find($transfer->id);
    echo "📋 معلومات التحويل في القاعدة المركزية:\n";
    echo "   - النوع: " . $clearingTrans->transfer_type . "\n";
    echo "   - المبلغ: " . number_format($clearingTrans->amount, 2) . " ريال\n";
    echo "   - الحالة: " . $clearingTrans->status . "\n";
    echo "   - التاريخ: " . $clearingTrans->transfer_date . "\n\n";
    
    echo "🎉 الاختبار نجح بالكامل!\n";
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
