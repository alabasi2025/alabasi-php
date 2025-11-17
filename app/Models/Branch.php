<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'branch_code',
        'branch_name',
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
     * علاقة الفرع مع الوحدة
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * علاقة الفرع مع المؤسسة (عبر الوحدة)
     */
    public function company()
    {
        return $this->hasOneThrough(Company::class, Unit::class, 'id', 'id', 'unit_id', 'company_id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
