<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     * سيتم تحديده ديناميكياً حسب الوحدة المختارة
     *
     * @var string|null
     */
    protected $connection = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'legal_name',
        'tax_number',
        'commercial_registration',
        'address',
        'phone',
        'email',
        'website',
        'fiscal_year_start',
        'fiscal_year_end',
        'currency',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
    ];

    /**
     * Get the branches for the company.
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the accounts for the company.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the journal entries for the company.
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Set the database connection for this model.
     *
     * @param string $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
