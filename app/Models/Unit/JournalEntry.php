<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
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
        'branch_id',
        'entry_number',
        'entry_date',
        'description',
        'entry_type',
        'clearing_transaction_id',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];

    /**
     * Get the company that owns the journal entry.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the journal entry.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the details for the journal entry.
     */
    public function details()
    {
        return $this->hasMany(JournalEntryDetail::class)->orderBy('line_order');
    }

    /**
     * Check if this is a clearing entry.
     *
     * @return bool
     */
    public function isClearingEntry(): bool
    {
        return $this->entry_type === 'clearing';
    }

    /**
     * Check if the entry is posted.
     *
     * @return bool
     */
    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Check if the entry is balanced.
     *
     * @return bool
     */
    public function isBalanced(): bool
    {
        $totalDebit = $this->details()->sum('debit');
        $totalCredit = $this->details()->sum('credit');
        
        return abs($totalDebit - $totalCredit) < 0.01; // للتعامل مع أخطاء الفاصلة العشرية
    }

    /**
     * Get the total debit amount.
     *
     * @return float
     */
    public function getTotalDebit(): float
    {
        return $this->details()->sum('debit');
    }

    /**
     * Get the total credit amount.
     *
     * @return float
     */
    public function getTotalCredit(): float
    {
        return $this->details()->sum('credit');
    }

    /**
     * Post the journal entry.
     *
     * @param int $userId
     * @return bool
     */
    public function post(int $userId): bool
    {
        if (!$this->isBalanced()) {
            throw new \Exception('القيد غير متوازن. مجموع المدين يجب أن يساوي مجموع الدائن.');
        }

        $this->status = 'posted';
        $this->posted_by = $userId;
        $this->posted_at = now();
        
        return $this->save();
    }

    /**
     * Cancel the journal entry.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        if ($this->isPosted()) {
            throw new \Exception('لا يمكن إلغاء قيد تم ترحيله. يجب إنشاء قيد عكسي.');
        }

        $this->status = 'cancelled';
        return $this->save();
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
