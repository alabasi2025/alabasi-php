<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Enums\VoucherType;
use App\Enums\VoucherStatus;
use App\Enums\EntryType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherService
{
    /**
     * Create a new voucher
     */
    public function create(array $data): Voucher
    {
        DB::beginTransaction();
        
        try {
            // Generate voucher number if not provided
            if (!isset($data['voucher_number'])) {
                $data['voucher_number'] = $this->generateVoucherNumber(
                    $data['company_id'],
                    VoucherType::from($data['type'])
                );
            }
            
            // Set default status
            if (!isset($data['status'])) {
                $data['status'] = VoucherStatus::DRAFT->value;
            }
            
            // Create voucher
            $voucher = Voucher::create($data);
            
            // Log activity
            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $data])
                ->log('تم إنشاء سند جديد');
            
            DB::commit();
            
            return $voucher;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create voucher', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing voucher
     */
    public function update(Voucher $voucher, array $data): Voucher
    {
        DB::beginTransaction();
        
        try {
            // Check if voucher can be edited
            $status = VoucherStatus::from($voucher->status);
            if (!$status->canEdit()) {
                throw new \Exception('لا يمكن تعديل سند ' . $status->label());
            }
            
            $oldData = $voucher->toArray();
            
            // Update voucher
            $voucher->update($data);
            
            // Log activity
            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $data])
                ->log('تم تعديل السند');
            
            DB::commit();
            
            return $voucher->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update voucher', ['error' => $e->getMessage(), 'voucher_id' => $voucher->id]);
            throw $e;
        }
    }

    /**
     * Delete a voucher
     */
    public function delete(Voucher $voucher): bool
    {
        DB::beginTransaction();
        
        try {
            // Check if voucher can be deleted
            $status = VoucherStatus::from($voucher->status);
            if (!$status->canDelete()) {
                throw new \Exception('لا يمكن حذف سند ' . $status->label());
            }
            
            // Delete associated journal entry if exists
            if ($voucher->journalEntry) {
                $voucher->journalEntry->details()->delete();
                $voucher->journalEntry->delete();
            }
            
            // Log activity before deletion
            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $voucher->toArray()])
                ->log('تم حذف السند');
            
            // Delete voucher
            $voucher->delete();
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete voucher', ['error' => $e->getMessage(), 'voucher_id' => $voucher->id]);
            throw $e;
        }
    }

    /**
     * Submit voucher for approval
     */
    public function submit(Voucher $voucher): Voucher
    {
        DB::beginTransaction();
        
        try {
            // Check current status
            $status = VoucherStatus::from($voucher->status);
            if ($status !== VoucherStatus::DRAFT) {
                throw new \Exception('يمكن تقديم المسودات فقط للاعتماد');
            }
            
            // Update status
            $voucher->update([
                'status' => VoucherStatus::PENDING->value,
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
            ]);
            
            // Log activity
            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->log('تم تقديم السند للاعتماد');
            
            DB::commit();
            
            return $voucher->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit voucher', ['error' => $e->getMessage(), 'voucher_id' => $voucher->id]);
            throw $e;
        }
    }

    /**
     * Approve a voucher
     */
    public function approve(Voucher $voucher, ?string $notes = null): Voucher
    {
        DB::beginTransaction();
        
        try {
            // Check if voucher can be approved
            $status = VoucherStatus::from($voucher->status);
            if (!$status->canApprove()) {
                throw new \Exception('لا يمكن اعتماد سند ' . $status->label());
            }
            
            // Update status
            $voucher->update([
                'status' => VoucherStatus::APPROVED->value,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'approval_notes' => $notes,
            ]);
            
            // Create journal entry
            $this->createJournalEntry($voucher);
            
            // Log activity
            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->withProperties(['notes' => $notes])
                ->log('تم اعتماد السند');
            
            DB::commit();
            
            return $voucher->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve voucher', ['error' => $e->getMessage(), 'voucher_id' => $voucher->id]);
            throw $e;
        }
    }

    /**
     * Reject a voucher
     */
    public function reject(Voucher $voucher, string $reason): Voucher
    {
        DB::beginTransaction();
        
        try {
            // Check if voucher can be rejected
            $status = VoucherStatus::from($voucher->status);
            if (!$status->canReject()) {
                throw new \Exception('لا يمكن رفض سند ' . $status->label());
            }
            
            // Update status
            $voucher->update([
                'status' => VoucherStatus::REJECTED->value,
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $reason,
            ]);
            
            // Log activity
            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->withProperties(['reason' => $reason])
                ->log('تم رفض السند');
            
            DB::commit();
            
            return $voucher->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject voucher', ['error' => $e->getMessage(), 'voucher_id' => $voucher->id]);
            throw $e;
        }
    }

    /**
     * Create journal entry from voucher
     */
    private function createJournalEntry(Voucher $voucher): JournalEntry
    {
        $voucherType = VoucherType::from($voucher->type);
        
        // Create journal entry
        $journalEntry = JournalEntry::create([
            'company_id' => $voucher->company_id,
            'entry_number' => $this->generateEntryNumber($voucher->company_id),
            'entry_date' => $voucher->voucher_date,
            'description' => $voucher->description,
            'reference_type' => 'voucher',
            'reference_id' => $voucher->id,
        ]);
        
        // Create entry details based on voucher type
        if ($voucherType === VoucherType::RECEIPT) {
            // Receipt: Debit Cash/Bank, Credit Account
            JournalEntryDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $voucher->cash_account_id ?? $voucher->bank_account_id,
                'type' => EntryType::DEBIT->value,
                'amount' => $voucher->amount,
            ]);
            
            JournalEntryDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $voucher->account_id,
                'type' => EntryType::CREDIT->value,
                'amount' => $voucher->amount,
            ]);
        } else {
            // Payment: Debit Account, Credit Cash/Bank
            JournalEntryDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $voucher->account_id,
                'type' => EntryType::DEBIT->value,
                'amount' => $voucher->amount,
            ]);
            
            JournalEntryDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $voucher->cash_account_id ?? $voucher->bank_account_id,
                'type' => EntryType::CREDIT->value,
                'amount' => $voucher->amount,
            ]);
        }
        
        return $journalEntry;
    }

    /**
     * Generate voucher number
     */
    private function generateVoucherNumber(int $companyId, VoucherType $type): string
    {
        $prefix = $type === VoucherType::RECEIPT ? 'RCV' : 'PAY';
        $year = date('Y');
        
        $lastVoucher = Voucher::where('company_id', $companyId)
            ->where('type', $type->value)
            ->whereYear('created_at', $year)
            ->orderBy('voucher_number', 'desc')
            ->first();
        
        if (!$lastVoucher) {
            $sequence = 1;
        } else {
            // Extract sequence from last voucher number
            preg_match('/(\d+)$/', $lastVoucher->voucher_number, $matches);
            $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        
        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }

    /**
     * Generate journal entry number
     */
    private function generateEntryNumber(int $companyId): string
    {
        $year = date('Y');
        
        $lastEntry = JournalEntry::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->orderBy('entry_number', 'desc')
            ->first();
        
        if (!$lastEntry) {
            $sequence = 1;
        } else {
            preg_match('/(\d+)$/', $lastEntry->entry_number, $matches);
            $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        
        return sprintf('JE-%s-%05d', $year, $sequence);
    }
}
