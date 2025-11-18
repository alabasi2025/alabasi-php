<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
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
        'national_id',
        'job_title',
        'salary',
        'hire_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'hire_date' => 'date',
    ];

    /**
     * العلاقة مع الحساب
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
