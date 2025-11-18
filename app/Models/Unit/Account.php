<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
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
        'company_id',
        'account_number',
        'account_name',
        'description',
        'account_type',
        'clearing_type',
        'related_unit_id',
        'related_company_id',
        'account_nature',
        'parent_account_id',
        'level',
        'is_active',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Get the company that owns the account.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the parent account.
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get the child accounts.
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Get the journal entry details for this account.
     */
    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetail::class);
    }

    /**
     * Check if this is a clearing account.
     *
     * @return bool
     */
    public function isClearingAccount(): bool
    {
        return $this->account_type === 'clearing';
    }

    /**
     * Check if this is an inter-company clearing account.
     *
     * @return bool
     */
    public function isInterCompanyClearingAccount(): bool
    {
        return $this->isClearingAccount() && $this->clearing_type === 'inter_company';
    }

    /**
     * Check if this is an inter-unit clearing account.
     *
     * @return bool
     */
    public function isInterUnitClearingAccount(): bool
    {
        return $this->isClearingAccount() && $this->clearing_type === 'inter_unit';
    }

    /**
     * Get the balance of this account.
     *
     * @return float
     */
    public function getBalance(): float
    {
        $details = $this->journalEntryDetails()
            ->whereHas('journalEntry', function ($query) {
                $query->where('status', 'posted');
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = $details->total_debit ?? 0;
        $totalCredit = $details->total_credit ?? 0;

        if ($this->account_nature === 'debit') {
            return $totalDebit - $totalCredit;
        } else {
            return $totalCredit - $totalDebit;
        }
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
