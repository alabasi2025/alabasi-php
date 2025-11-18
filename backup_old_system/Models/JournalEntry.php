<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'entry_number',
        'entry_date',
        'description',
        'reference_number',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'approved_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2'
    ];

    public function details()
    {
        return $this->hasMany(JournalEntryDetail::class, 'journal_entry_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
