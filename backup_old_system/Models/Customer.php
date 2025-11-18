<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'account_id',
        'phone',
        'email',
        'address',
        'tax_number',
        'commercial_register',
        'credit_limit',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
    ];

    /**
     * العلاقة مع الحساب
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
