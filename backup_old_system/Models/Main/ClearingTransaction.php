<?php

namespace App\Models\Main;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClearingTransaction extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_type',
        'source_unit_id',
        'source_company_id',
        'source_journal_entry_id',
        'target_unit_id',
        'target_company_id',
        'target_journal_entry_id',
        'amount',
        'description',
        'status',
        'created_by',
        'approved_at',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the source unit.
     */
    public function sourceUnit()
    {
        return $this->belongsTo(Unit::class, 'source_unit_id');
    }

    /**
     * Get the target unit.
     */
    public function targetUnit()
    {
        return $this->belongsTo(Unit::class, 'target_unit_id');
    }

    /**
     * Get the source company.
     */
    public function sourceCompany()
    {
        return $this->belongsTo(Company::class, 'source_company_id');
    }

    /**
     * Get the target company.
     */
    public function targetCompany()
    {
        return $this->belongsTo(Company::class, 'target_company_id');
    }

    /**
     * Check if this is an inter-company transaction.
     *
     * @return bool
     */
    public function isInterCompany(): bool
    {
        return $this->transaction_type === 'inter_company';
    }

    /**
     * Check if this is an inter-unit transaction.
     *
     * @return bool
     */
    public function isInterUnit(): bool
    {
        return $this->transaction_type === 'inter_unit';
    }

    /**
     * Check if the transaction is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the transaction is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark the transaction as completed.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Mark the transaction as cancelled.
     *
     * @return bool
     */
    public function markAsCancelled(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }
}
