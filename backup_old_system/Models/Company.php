<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'company_code',
        'company_name',
        'description',
        'address',
        'phone',
        'email',
        'tax_number',
        'registration_number',
        'director_name',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * علاقة المؤسسة مع الوحدة (المؤسسة تنتمي إلى وحدة واحدة)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * علاقة المؤسسة مع الفروع
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * علاقة المؤسسة مع الحسابات
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * علاقة المؤسسة مع السندات
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * علاقة المؤسسة مع القيود اليومية
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * علاقة المؤسسة مع أنواع الحسابات
     */
    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }

    /**
     * علاقة المؤسسة مع أنواع الحسابات التحليلية
     */
    public function analyticalAccountTypes()
    {
        return $this->hasMany(AnalyticalAccountType::class);
    }

    /**
     * Scope للمؤسسات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
