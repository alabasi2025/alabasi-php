<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AccountType as AccountTypeEnum;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'account_type_id',
        'analytical_account_type_id',
        'account_nature', // طبيعة الحساب
        'parent_id',
        'account_code',
        'code', // Alias for account_code
        'account_name',
        'name', // Alias for account_name
        'name_en',
        'type', // New: Using Enum
        'level',
        'balance',
        'is_active',
        'is_main',
        'is_analytical',
        'description',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
        'is_analytical' => 'boolean',
        'balance' => 'decimal:2',
        'type' => AccountTypeEnum::class, // Cast to Enum
    ];

    protected $appends = [
        'full_path',
        'full_code',
    ];

    /**
     * العلاقة مع المؤسسة
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * العلاقة مع نوع الحساب
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * العلاقة مع النوع التحليلي
     */
    public function analyticalAccountType()
    {
        return $this->belongsTo(AnalyticalAccountType::class);
    }

    /**
     * العلاقة مع الحساب الأب
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * العلاقة مع الحسابات الفرعية
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * العلاقة مع السندات
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * العلاقة مع تفاصيل القيود اليومية
     */
    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetail::class);
    }

    /**
     * Scope: الحسابات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: الحسابات غير النشطة
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: الحسابات الرئيسية فقط
     */
    public function scopeMainAccounts($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Scope: الحسابات الفرعية فقط
     */
    public function scopeSubAccounts($query)
    {
        return $query->where('is_main', false);
    }

    /**
     * Scope: الحسابات التحليلية
     */
    public function scopeAnalytical($query)
    {
        return $query->where('is_analytical', true);
    }

    /**
     * Scope: حسب المؤسسة
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: حسب نوع الحساب (using Enum)
     */
    public function scopeOfType($query, AccountTypeEnum|string $type)
    {
        if ($type instanceof AccountTypeEnum) {
            $type = $type->value;
        }
        return $query->where('type', $type);
    }

    /**
     * Scope: حسابات الأصول
     */
    public function scopeAssets($query)
    {
        return $query->where('type', AccountTypeEnum::ASSET->value);
    }

    /**
     * Scope: حسابات الخصوم
     */
    public function scopeLiabilities($query)
    {
        return $query->where('type', AccountTypeEnum::LIABILITY->value);
    }

    /**
     * Scope: حسابات حقوق الملكية
     */
    public function scopeEquity($query)
    {
        return $query->where('type', AccountTypeEnum::EQUITY->value);
    }

    /**
     * Scope: حسابات الإيرادات
     */
    public function scopeRevenues($query)
    {
        return $query->where('type', AccountTypeEnum::REVENUE->value);
    }

    /**
     * Scope: حسابات المصروفات
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', AccountTypeEnum::EXPENSE->value);
    }

    /**
     * Scope: حسب المستوى
     */
    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope: الحسابات الرئيسية فقط (ليس لها أب)
     */
    public function scopeRootAccounts($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: الحسابات الفرعية (لها أب)
     */
    public function scopeChildAccounts($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope: البحث في الحسابات
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('account_code', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('account_name', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('name_en', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: حسابات الميزانية (أصول + خصوم + حقوق ملكية)
     */
    public function scopeBalanceSheet($query)
    {
        return $query->whereIn('type', [
            AccountTypeEnum::ASSET->value,
            AccountTypeEnum::LIABILITY->value,
            AccountTypeEnum::EQUITY->value,
        ]);
    }

    /**
     * Scope: حسابات قائمة الدخل (إيرادات + مصروفات)
     */
    public function scopeIncomeStatement($query)
    {
        return $query->whereIn('type', [
            AccountTypeEnum::REVENUE->value,
            AccountTypeEnum::EXPENSE->value,
        ]);
    }

    /**
     * الحصول على المسار الكامل للحساب (الأب ← الابن ← الحفيد)
     */
    public function getFullPathAttribute()
    {
        $path = [$this->account_name ?? $this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->account_name ?? $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' ← ', $path);
    }

    /**
     * الحصول على الكود الكامل للحساب
     */
    public function getFullCodeAttribute()
    {
        $codes = [$this->account_code ?? $this->code];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($codes, $parent->account_code ?? $parent->code);
            $parent = $parent->parent;
        }
        
        return implode('-', $codes);
    }

    /**
     * الحصول على اللون حسب نوع الحساب
     */
    public function getColorAttribute()
    {
        if ($this->type instanceof AccountTypeEnum) {
            return $this->type->color();
        }
        return 'secondary';
    }

    /**
     * الحصول على الأيقونة حسب نوع الحساب
     */
    public function getIconAttribute()
    {
        if ($this->type instanceof AccountTypeEnum) {
            return $this->type->icon();
        }
        return 'bi-circle';
    }

    /**
     * الحصول على التسمية حسب نوع الحساب
     */
    public function getTypeLabelAttribute()
    {
        if ($this->type instanceof AccountTypeEnum) {
            return $this->type->label();
        }
        return '';
    }

    /**
     * التحقق من إمكانية الحذف
     */
    public function canDelete(): bool
    {
        return $this->children()->count() === 0 
            && $this->journalEntryDetails()->count() === 0;
    }

    /**
     * التحقق من إمكانية التعديل
     */
    public function canEdit(): bool
    {
        return true; // يمكن إضافة شروط إضافية
    }
}
