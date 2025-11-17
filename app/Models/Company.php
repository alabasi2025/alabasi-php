<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
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
     * علاقة المؤسسة مع الوحدات
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * علاقة المؤسسة مع الفروع (عبر الوحدات)
     */
    public function branches()
    {
        return $this->hasManyThrough(Branch::class, Unit::class);
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
     * Scope للمؤسسات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
