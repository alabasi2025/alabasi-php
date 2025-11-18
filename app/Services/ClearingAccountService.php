<?php

namespace App\Services;

use App\Models\Main\ClearingTransaction;
use App\Models\Main\Unit;
use App\Models\Main\Company as MainCompany;
use App\Models\Unit\Company;
use App\Models\Unit\Account;
use App\Models\Unit\JournalEntry;
use App\Models\Unit\JournalEntryDetail;
use Illuminate\Support\Facades\DB;
use Exception;

class ClearingAccountService
{
    /**
     * Create a transfer between two companies (inter-company or inter-unit).
     *
     * @param array $data
     * @return ClearingTransaction
     * @throws Exception
     */
    public function createTransfer(array $data): ClearingTransaction
    {
        // التحقق من البيانات المطلوبة
        $this->validateTransferData($data);

        // بدء المعاملة
        DB::connection('main')->beginTransaction();
        
        try {
            // تحديد نوع التحويل
            $transactionType = $data['source_unit_id'] === $data['target_unit_id'] 
                ? 'inter_company' 
                : 'inter_unit';

            // إنشاء سجل التحويل في القاعدة المركزية
            $clearingTransaction = ClearingTransaction::create([
                'transaction_type' => $transactionType,
                'source_unit_id' => $data['source_unit_id'],
                'source_company_id' => $data['source_company_id'],
                'target_unit_id' => $data['target_unit_id'],
                'target_company_id' => $data['target_company_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? 'تحويل بين ' . ($transactionType === 'inter_company' ? 'مؤسسات' : 'وحدات'),
                'status' => 'pending',
                'created_by' => $data['user_id'] ?? null,
            ]);

            // إنشاء القيد في المؤسسة المصدر
            $sourceJournalEntry = $this->createSourceJournalEntry(
                $clearingTransaction,
                $data
            );

            // إنشاء القيد في المؤسسة المستقبلة
            $targetJournalEntry = $this->createTargetJournalEntry(
                $clearingTransaction,
                $data
            );

            // تحديث معرفات القيود في سجل التحويل
            $clearingTransaction->update([
                'source_journal_entry_id' => $sourceJournalEntry->id,
                'target_journal_entry_id' => $targetJournalEntry->id,
                'status' => 'completed',
            ]);

            DB::connection('main')->commit();

            return $clearingTransaction;

        } catch (Exception $e) {
            DB::connection('main')->rollBack();
            throw new Exception('فشل إنشاء التحويل: ' . $e->getMessage());
        }
    }

    /**
     * Create the journal entry in the source company.
     *
     * @param ClearingTransaction $clearingTransaction
     * @param array $data
     * @return JournalEntry
     * @throws Exception
     */
    protected function createSourceJournalEntry(ClearingTransaction $clearingTransaction, array $data): JournalEntry
    {
        // الحصول على معلومات الوحدة المصدر
        $sourceUnit = Unit::find($data['source_unit_id']);
        $sourceConnection = $sourceUnit->database_name;

        DB::connection($sourceConnection)->beginTransaction();

        try {
            // البحث عن الحساب الوسيط المناسب
            // للتحويل بين مؤسسات: related_unit_id = source_unit_id
            $relatedUnitId = $clearingTransaction->transaction_type === 'inter_company' 
                ? $data['source_unit_id'] 
                : $data['target_unit_id'];
                
            $clearingAccount = $this->getClearingAccount(
                $sourceConnection,
                $data['source_company_id'],
                $clearingTransaction->transaction_type,
                $relatedUnitId,
                $data['target_company_id']
            );

            // البحث عن حساب المصدر (الصندوق أو البنك)
            $sourceAccount = Account::on($sourceConnection)
                ->where('id', $data['source_account_id'])
                ->where('company_id', $data['source_company_id'])
                ->firstOrFail();

            // إنشاء رقم القيد
            $entryNumber = $this->generateEntryNumber($sourceConnection, $data['source_company_id']);

            // إنشاء القيد
            $journalEntry = JournalEntry::on($sourceConnection)->create([
                'company_id' => $data['source_company_id'],
                'branch_id' => $data['source_branch_id'] ?? null,
                'entry_number' => $entryNumber,
                'entry_date' => $data['entry_date'] ?? now(),
                'description' => $data['description'] ?? 'تحويل إلى ' . $this->getTargetName($data),
                'entry_type' => 'clearing',
                'clearing_transaction_id' => $clearingTransaction->id,
                'status' => 'draft',
                'created_by' => $data['user_id'] ?? null,
            ]);

            // إنشاء تفاصيل القيد
            // السطر الأول: من الحساب المصدر (دائن)
            JournalEntryDetail::on($sourceConnection)->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $sourceAccount->id,
                'debit' => 0,
                'credit' => $data['amount'],
                'notes' => 'تحويل من ' . $sourceAccount->account_name,
                'line_order' => 1,
            ]);

            // السطر الثاني: إلى الحساب الوسيط (مدين)
            JournalEntryDetail::on($sourceConnection)->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $clearingAccount->id,
                'debit' => $data['amount'],
                'credit' => 0,
                'notes' => 'تحويل إلى ' . $clearingAccount->account_name,
                'line_order' => 2,
            ]);

            // ترحيل القيد
            $journalEntry->post($data['user_id'] ?? 1);

            DB::connection($sourceConnection)->commit();

            return $journalEntry;

        } catch (Exception $e) {
            DB::connection($sourceConnection)->rollBack();
            throw new Exception('فشل إنشاء القيد في المؤسسة المصدر: ' . $e->getMessage());
        }
    }

    /**
     * Create the journal entry in the target company.
     *
     * @param ClearingTransaction $clearingTransaction
     * @param array $data
     * @return JournalEntry
     * @throws Exception
     */
    protected function createTargetJournalEntry(ClearingTransaction $clearingTransaction, array $data): JournalEntry
    {
        // الحصول على معلومات الوحدة المستقبلة
        $targetUnit = Unit::find($data['target_unit_id']);
        $targetConnection = $targetUnit->database_name;

        DB::connection($targetConnection)->beginTransaction();

        try {
            // البحث عن الحساب الوسيط المناسب
            // للتحويل بين مؤسسات: related_unit_id = target_unit_id
            $relatedUnitId = $clearingTransaction->transaction_type === 'inter_company' 
                ? $data['target_unit_id'] 
                : $data['source_unit_id'];
                
            $clearingAccount = $this->getClearingAccount(
                $targetConnection,
                $data['target_company_id'],
                $clearingTransaction->transaction_type,
                $relatedUnitId,
                $data['source_company_id']
            );

            // البحث عن حساب الهدف (الصندوق أو البنك)
            $targetAccount = Account::on($targetConnection)
                ->where('id', $data['target_account_id'])
                ->where('company_id', $data['target_company_id'])
                ->firstOrFail();

            // إنشاء رقم القيد
            $entryNumber = $this->generateEntryNumber($targetConnection, $data['target_company_id']);

            // إنشاء القيد
            $journalEntry = JournalEntry::on($targetConnection)->create([
                'company_id' => $data['target_company_id'],
                'branch_id' => $data['target_branch_id'] ?? null,
                'entry_number' => $entryNumber,
                'entry_date' => $data['entry_date'] ?? now(),
                'description' => $data['description'] ?? 'تحويل من ' . $this->getSourceName($data),
                'entry_type' => 'clearing',
                'clearing_transaction_id' => $clearingTransaction->id,
                'status' => 'draft',
                'created_by' => $data['user_id'] ?? null,
            ]);

            // إنشاء تفاصيل القيد
            // السطر الأول: من الحساب الوسيط (دائن)
            JournalEntryDetail::on($targetConnection)->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $clearingAccount->id,
                'debit' => 0,
                'credit' => $data['amount'],
                'notes' => 'تحويل من ' . $clearingAccount->account_name,
                'line_order' => 1,
            ]);

            // السطر الثاني: إلى الحساب المستقبل (مدين)
            JournalEntryDetail::on($targetConnection)->create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $targetAccount->id,
                'debit' => $data['amount'],
                'credit' => 0,
                'notes' => 'تحويل إلى ' . $targetAccount->account_name,
                'line_order' => 2,
            ]);

            // ترحيل القيد
            $journalEntry->post($data['user_id'] ?? 1);

            DB::connection($targetConnection)->commit();

            return $journalEntry;

        } catch (Exception $e) {
            DB::connection($targetConnection)->rollBack();
            throw new Exception('فشل إنشاء القيد في المؤسسة المستقبلة: ' . $e->getMessage());
        }
    }

    /**
     * Get or create the appropriate clearing account.
     *
     * @param string $connection
     * @param int $companyId
     * @param string $clearingType
     * @param int $relatedUnitId
     * @param int $relatedCompanyId
     * @return Account
     */
    protected function getClearingAccount(
        string $connection,
        int $companyId,
        string $clearingType,
        int $relatedUnitId,
        int $relatedCompanyId
    ): Account {
        // البحث عن حساب وسيط موجود
        $account = Account::on($connection)
            ->where('company_id', $companyId)
            ->where('account_type', 'clearing')
            ->where('clearing_type', $clearingType)
            ->where('related_unit_id', $relatedUnitId)
            ->where('related_company_id', $relatedCompanyId)
            ->first();

        // إذا لم يوجد، إنشاء حساب وسيط جديد
        if (!$account) {
            $account = $this->createClearingAccount(
                $connection,
                $companyId,
                $clearingType,
                $relatedUnitId,
                $relatedCompanyId
            );
        }

        return $account;
    }

    /**
     * Create a new clearing account.
     *
     * @param string $connection
     * @param int $companyId
     * @param string $clearingType
     * @param int $relatedUnitId
     * @param int $relatedCompanyId
     * @return Account
     */
    protected function createClearingAccount(
        string $connection,
        int $companyId,
        string $clearingType,
        int $relatedUnitId,
        int $relatedCompanyId
    ): Account {
        // الحصول على اسم المؤسسة/الوحدة المرتبطة
        $relatedName = $this->getRelatedName($relatedUnitId, $relatedCompanyId);

        // توليد رقم الحساب
        $accountNumber = $this->generateClearingAccountNumber($connection, $companyId, $clearingType);

        // إنشاء الحساب
        return Account::on($connection)->create([
            'company_id' => $companyId,
            'account_number' => $accountNumber,
            'account_name' => 'حساب وسيط - ' . $relatedName,
            'description' => 'حساب وسيط للتحويلات مع ' . $relatedName,
            'account_type' => 'clearing',
            'clearing_type' => $clearingType,
            'related_unit_id' => $relatedUnitId,
            'related_company_id' => $relatedCompanyId,
            'account_nature' => 'debit',
            'is_active' => true,
            'is_system' => true,
        ]);
    }

    /**
     * Generate a clearing account number.
     *
     * @param string $connection
     * @param int $companyId
     * @param string $clearingType
     * @return string
     */
    protected function generateClearingAccountNumber(string $connection, int $companyId, string $clearingType): string
    {
        // نطاق الأرقام: 9000-9499 للـ inter_company، 9500-9999 للـ inter_unit
        $baseNumber = $clearingType === 'inter_company' ? 9000 : 9500;

        // البحث عن آخر رقم مستخدم
        $lastAccount = Account::on($connection)
            ->where('company_id', $companyId)
            ->where('account_type', 'clearing')
            ->where('clearing_type', $clearingType)
            ->orderBy('account_number', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = intval($lastAccount->account_number);
            return strval($lastNumber + 1);
        }

        return strval($baseNumber + 1);
    }

    /**
     * Generate a journal entry number.
     *
     * @param string $connection
     * @param int $companyId
     * @return string
     */
    protected function generateEntryNumber(string $connection, int $companyId): string
    {
        $year = date('Y');
        $prefix = "JE-{$year}-";

        $lastEntry = JournalEntry::on($connection)
            ->where('company_id', $companyId)
            ->where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = intval(str_replace($prefix, '', $lastEntry->entry_number));
            return $prefix . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return $prefix . '000001';
    }

    /**
     * Get the name of the related unit/company.
     *
     * @param int $unitId
     * @param int $companyId
     * @return string
     */
    protected function getRelatedName(int $unitId, int $companyId): string
    {
        $company = MainCompany::find($companyId);
        return $company ? $company->name : "مؤسسة #{$companyId}";
    }

    /**
     * Get the source name for description.
     *
     * @param array $data
     * @return string
     */
    protected function getSourceName(array $data): string
    {
        $company = MainCompany::find($data['source_company_id']);
        return $company ? $company->name : "مؤسسة #{$data['source_company_id']}";
    }

    /**
     * Get the target name for description.
     *
     * @param array $data
     * @return string
     */
    protected function getTargetName(array $data): string
    {
        $company = MainCompany::find($data['target_company_id']);
        return $company ? $company->name : "مؤسسة #{$data['target_company_id']}";
    }

    /**
     * Validate the transfer data.
     *
     * @param array $data
     * @return void
     * @throws Exception
     */
    protected function validateTransferData(array $data): void
    {
        $required = [
            'source_unit_id',
            'source_company_id',
            'source_account_id',
            'target_unit_id',
            'target_company_id',
            'target_account_id',
            'amount',
        ];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception("الحقل {$field} مطلوب");
            }
        }

        if ($data['amount'] <= 0) {
            throw new Exception('المبلغ يجب أن يكون أكبر من صفر');
        }

        if ($data['source_unit_id'] === $data['target_unit_id'] && 
            $data['source_company_id'] === $data['target_company_id']) {
            throw new Exception('لا يمكن التحويل من وإلى نفس المؤسسة');
        }
    }
}
