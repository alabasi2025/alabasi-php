<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'account_type_id',
        'analytical_account_type_id',
        'parent_id',
        'account_code',
        'account_name',
        'level',
        'is_active',
        'is_main',
        'description',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
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
     * العلاقة مع القيود اليومية
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Scope: الحسابات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Scope: حسب المؤسسة
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: حسب نوع الحساب
     */
    public function scopeOfType($query, $accountTypeId)
    {
        return $query->where('account_type_id', $accountTypeId);
    }

    /**
     * Scope: حسب النوع التحليلي
     */
    public function scopeOfAnalyticalType($query, $analyticalTypeId)
    {
        return $query->where('analytical_account_type_id', $analyticalTypeId);
    }

    /**
     * Scope: الحسابات الرئيسية فقط (ليس لها أب)
     */
    public function scopeRootAccounts($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * الحصول على المسار الكامل للحساب (الأب ← الابن ← الحفيد)
     */
    public function getFullPathAttribute()
    {
        $path = [$this->account_name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->account_name);
            $parent = $parent->parent;
        }
        
        return implode(' ← ', $path);
    }

    /**
     * الحصول على الكود الكامل للحساب
     */
    public function getFullCodeAttribute()
    {
        $codes = [$this->account_code];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($codes, $parent->account_code);
            $parent = $parent->parent;
        }
        
        return implode('-', $codes);
    }
}
