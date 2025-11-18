<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticalAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'analytical_account_type_id',
        'account_id',
        'code',
        'name',
        'description',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع المؤسسة
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * العلاقة مع نوع الحساب التحليلي
     */
    public function analyticalAccountType()
    {
        return $this->belongsTo(AnalyticalAccountType::class);
    }

    /**
     * العلاقة مع الحساب الفرعي من الدليل
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * العلاقة مع السندات
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * Scope: الحسابات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: حسب المؤسسة
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: حسب نوع الحساب التحليلي
     */
    public function scopeOfType($query, $typeId)
    {
        return $query->where('analytical_account_type_id', $typeId);
    }

    /**
     * الحصول على الاسم الكامل مع النوع
     */
    public function getFullNameAttribute()
    {
        return $this->analyticalAccountType->name . ' - ' . $this->name;
    }
}
