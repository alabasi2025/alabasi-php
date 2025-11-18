<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\VoucherType;
use App\Enums\VoucherStatus;
use App\Enums\PaymentMethod;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'type', // Alias using Enum
        'payment_method',
        'voucher_date',
        'amount',
        'currency',
        'beneficiary_name',
        'analytical_account_id',
        'account_id',
        'cash_account_id',
        'bank_account_id',
        'description',
        'notes',
        'unit_id',
        'company_id',
        'branch_id',
        'status',
        'journal_entry_id',
        'reference_number',
        'check_number',
        'check_date',
        'created_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'check_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'amount' => 'decimal:2',
        'type' => VoucherType::class, // Cast to Enum
        'status' => VoucherStatus::class, // Cast to Enum
        'payment_method' => PaymentMethod::class, // Cast to Enum
    ];

    protected $appends = [
        'type_label',
        'status_label',
        'status_badge',
        'payment_method_label',
    ];

    /**
     * العلاقة مع الحساب
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * العلاقة مع حساب الصندوق
     */
    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    /**
     * العلاقة مع حساب البنك
     */
    public function bankAccount()
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    /**
     * العلاقة مع الحساب التحليلي
     */
    public function analyticalAccount()
    {
        return $this->belongsTo(AnalyticalAccount::class, 'analytical_account_id');
    }

    /**
     * العلاقة مع المنشئ
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع من قدم السند
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * العلاقة مع المعتمد
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * العلاقة مع من رفض السند
     */
    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * العلاقة مع الوحدة
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * العلاقة مع المؤسسة
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * العلاقة مع الفرع
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * العلاقة مع القيد اليومي
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Scope: سندات الصرف
     */
    public function scopePayment($query)
    {
        return $query->where('type', VoucherType::PAYMENT->value);
    }

    /**
     * Scope: سندات القبض
     */
    public function scopeReceipt($query)
    {
        return $query->where('type', VoucherType::RECEIPT->value);
    }

    /**
     * Scope: الدفع النقدي
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', PaymentMethod::CASH->value);
    }

    /**
     * Scope: الدفع البنكي
     */
    public function scopeBank($query)
    {
        return $query->where('payment_method', PaymentMethod::BANK->value);
    }

    /**
     * Scope: الدفع بشيك
     */
    public function scopeCheck($query)
    {
        return $query->where('payment_method', PaymentMethod::CHECK->value);
    }

    /**
     * Scope: التحويل البنكي
     */
    public function scopeTransfer($query)
    {
        return $query->where('payment_method', PaymentMethod::TRANSFER->value);
    }

    /**
     * Scope: المعتمدة
     */
    public function scopeApproved($query)
    {
        return $query->where('status', VoucherStatus::APPROVED->value);
    }

    /**
     * Scope: قيد الانتظار
     */
    public function scopePending($query)
    {
        return $query->where('status', VoucherStatus::PENDING->value);
    }

    /**
     * Scope: المسودات
     */
    public function scopeDraft($query)
    {
        return $query->where('status', VoucherStatus::DRAFT->value);
    }

    /**
     * Scope: المرفوضة
     */
    public function scopeRejected($query)
    {
        return $query->where('status', VoucherStatus::REJECTED->value);
    }

    /**
     * Scope: حسب المؤسسة
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: حسب الفرع
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope: حسب الفترة
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('voucher_date', [$from, $to]);
    }

    /**
     * Scope: البحث
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('voucher_number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('beneficiary_name', 'like', "%{$search}%")
              ->orWhere('reference_number', 'like', "%{$search}%")
              ->orWhere('check_number', 'like', "%{$search}%");
        });
    }

    /**
     * الحصول على تسمية النوع
     */
    public function getTypeLabelAttribute()
    {
        if ($this->type instanceof VoucherType) {
            return $this->type->label();
        }
        return '';
    }

    /**
     * الحصول على تسمية الحالة
     */
    public function getStatusLabelAttribute()
    {
        if ($this->status instanceof VoucherStatus) {
            return $this->status->label();
        }
        return '';
    }

    /**
     * الحصول على badge الحالة
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->status instanceof VoucherStatus) {
            return $this->status->badge();
        }
        return 'badge bg-secondary';
    }

    /**
     * الحصول على تسمية طريقة الدفع
     */
    public function getPaymentMethodLabelAttribute()
    {
        if ($this->payment_method instanceof PaymentMethod) {
            return $this->payment_method->label();
        }
        return '';
    }

    /**
     * التحقق من إمكانية التعديل
     */
    public function canEdit(): bool
    {
        if ($this->status instanceof VoucherStatus) {
            return $this->status->canEdit();
        }
        return false;
    }

    /**
     * التحقق من إمكانية الحذف
     */
    public function canDelete(): bool
    {
        if ($this->status instanceof VoucherStatus) {
            return $this->status->canDelete();
        }
        return false;
    }

    /**
     * التحقق من إمكانية الاعتماد
     */
    public function canApprove(): bool
    {
        if ($this->status instanceof VoucherStatus) {
            return $this->status->canApprove();
        }
        return false;
    }

    /**
     * التحقق من إمكانية الرفض
     */
    public function canReject(): bool
    {
        if ($this->status instanceof VoucherStatus) {
            return $this->status->canReject();
        }
        return false;
    }
}
