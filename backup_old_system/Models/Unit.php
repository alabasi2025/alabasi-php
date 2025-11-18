<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_code',
        'unit_name',
        'description',
        'address',
        'phone',
        'email',
        'manager_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * علاقة الوحدة مع المؤسسات (الوحدة تحتوي على مؤسسات متعددة)
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    /**
     * علاقة الوحدة مع الفروع
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * علاقة الوحدة مع الحسابات
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * علاقة الوحدة مع السندات
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * علاقة الوحدة مع القيود اليومية
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Scope للوحدات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
