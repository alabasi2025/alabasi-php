<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Branch extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
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
     * علاقة الفرع مع المؤسسة
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * علاقة الفرع مع الوحدة
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
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
