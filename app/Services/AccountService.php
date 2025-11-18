<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Enums\AccountType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountService
{
    /**
     * Create a new account
     */
    public function create(array $data): Account
    {
        DB::beginTransaction();
        
        try {
            // Validate hierarchy
            if (isset($data['parent_id'])) {
                $this->validateParentAccount($data['parent_id'], $data['company_id']);
            }
            
            // Generate code if not provided
            if (!isset($data['code'])) {
                $data['code'] = $this->generateAccountCode($data['company_id'], $data['parent_id'] ?? null);
            }
            
            // Determine level
            $data['level'] = $this->calculateLevel($data['parent_id'] ?? null);
            
            // Create account
            $account = Account::create($data);
            
            // Log activity
            activity()
                ->performedOn($account)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $data])
                ->log('تم إنشاء حساب جديد');
            
            DB::commit();
            
            return $account;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create account', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing account
     */
    public function update(Account $account, array $data): Account
    {
        DB::beginTransaction();
        
        try {
            $oldData = $account->toArray();
            
            // Validate hierarchy if parent changed
            if (isset($data['parent_id']) && $data['parent_id'] != $account->parent_id) {
                $this->validateParentAccount($data['parent_id'], $account->company_id, $account->id);
                $data['level'] = $this->calculateLevel($data['parent_id']);
            }
            
            // Update account
            $account->update($data);
            
            // Log activity
            activity()
                ->performedOn($account)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $data])
                ->log('تم تعديل الحساب');
            
            DB::commit();
            
            return $account->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update account', ['error' => $e->getMessage(), 'account_id' => $account->id]);
            throw $e;
        }
    }

    /**
     * Delete an account
     */
    public function delete(Account $account): bool
    {
        DB::beginTransaction();
        
        try {
            // Check if account has children
            if ($account->children()->count() > 0) {
                throw new \Exception('لا يمكن حذف حساب له حسابات فرعية');
            }
            
            // Check if account has transactions
            if ($account->journalEntryDetails()->count() > 0) {
                throw new \Exception('لا يمكن حذف حساب له حركات');
            }
            
            // Log activity before deletion
            activity()
                ->performedOn($account)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $account->toArray()])
                ->log('تم حذف الحساب');
            
            // Delete account
            $account->delete();
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete account', ['error' => $e->getMessage(), 'account_id' => $account->id]);
            throw $e;
        }
    }

    /**
     * Get account tree for a company
     */
    public function getAccountTree(int $companyId): Collection
    {
        $accounts = Account::where('company_id', $companyId)
            ->with('children.children.children.children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();
        
        return $accounts;
    }

    /**
     * Get account hierarchy path
     */
    public function getAccountPath(Account $account): array
    {
        $path = [];
        $current = $account;
        
        while ($current) {
            array_unshift($path, [
                'id' => $current->id,
                'code' => $current->code,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }
        
        return $path;
    }

    /**
     * Calculate account balance
     */
    public function calculateBalance(Account $account, ?\DateTime $date = null): float
    {
        $query = $account->journalEntryDetails();
        
        if ($date) {
            $query->whereDate('created_at', '<=', $date);
        }
        
        $debit = $query->where('type', 'debit')->sum('amount');
        $credit = $query->where('type', 'credit')->sum('amount');
        
        // Determine balance based on account type
        $accountType = AccountType::from($account->type);
        
        if ($accountType->normalBalance() === 'debit') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }

    /**
     * Import accounts from array
     */
    public function importAccounts(array $accounts, int $companyId): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];
        
        DB::beginTransaction();
        
        try {
            foreach ($accounts as $accountData) {
                try {
                    $accountData['company_id'] = $companyId;
                    $this->create($accountData);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'account' => $accountData['code'] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Export accounts to array
     */
    public function exportAccounts(int $companyId): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->orderBy('code')
            ->get();
        
        return $accounts->map(function ($account) {
            return [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'level' => $account->level,
                'parent_code' => $account->parent?->code,
                'balance' => $account->balance,
                'is_active' => $account->is_active,
            ];
        })->toArray();
    }

    /**
     * Validate parent account
     */
    private function validateParentAccount(int $parentId, int $companyId, ?int $accountId = null): void
    {
        $parent = Account::find($parentId);
        
        if (!$parent) {
            throw new \Exception('الحساب الأب غير موجود');
        }
        
        if ($parent->company_id != $companyId) {
            throw new \Exception('الحساب الأب ينتمي لمؤسسة أخرى');
        }
        
        if ($parent->level >= 5) {
            throw new \Exception('لا يمكن إضافة حسابات فرعية لحساب من المستوى الخامس');
        }
        
        // Check circular reference
        if ($accountId && $this->isCircularReference($parentId, $accountId)) {
            throw new \Exception('لا يمكن جعل الحساب الفرعي أباً للحساب الأصلي');
        }
    }

    /**
     * Check for circular reference
     */
    private function isCircularReference(int $parentId, int $accountId): bool
    {
        $current = Account::find($parentId);
        
        while ($current) {
            if ($current->id == $accountId) {
                return true;
            }
            $current = $current->parent;
        }
        
        return false;
    }

    /**
     * Calculate account level
     */
    private function calculateLevel(?int $parentId): int
    {
        if (!$parentId) {
            return 1;
        }
        
        $parent = Account::find($parentId);
        return $parent ? $parent->level + 1 : 1;
    }

    /**
     * Generate account code
     */
    private function generateAccountCode(int $companyId, ?int $parentId): string
    {
        if (!$parentId) {
            // Root level account
            $lastAccount = Account::where('company_id', $companyId)
                ->whereNull('parent_id')
                ->orderBy('code', 'desc')
                ->first();
            
            if (!$lastAccount) {
                return '1000';
            }
            
            return (string) ((int) $lastAccount->code + 1000);
        }
        
        // Child account
        $parent = Account::find($parentId);
        $lastChild = $parent->children()->orderBy('code', 'desc')->first();
        
        if (!$lastChild) {
            return $parent->code . '01';
        }
        
        $lastCode = (int) substr($lastChild->code, strlen($parent->code));
        $newCode = str_pad($lastCode + 1, 2, '0', STR_PAD_LEFT);
        
        return $parent->code . $newCode;
    }
}
