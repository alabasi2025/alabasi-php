<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'nature',
        'description',
        'is_active',
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
     * العلاقة مع الحسابات
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Scope: الأنواع النشطة فقط
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
     * الحصول على اسم الطبيعة بالعربية
     */
    public function getNatureNameAttribute()
    {
        return $this->nature === 'debit' ? 'مدين' : 'دائن';
    }
}
