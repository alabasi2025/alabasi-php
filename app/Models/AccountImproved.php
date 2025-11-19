<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Account Model - Laravel 12 Enhanced
 * 
 * @property int $id
 * @property int $unit_id
 * @property int $company_id
 * @property string $code
 * @property string $name
 * @property int|null $parent_id
 * @property string $type
 * @property int $level
 * @property bool $is_final
 * @property bool $is_active
 * @property float $opening_balance
 * @property float $current_balance
 */
class AccountImproved extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'company_id',
        'code',
        'name',
        'parent_id',
        'type',
        'level',
        'is_final',
        'is_active',
        'opening_balance',
        'current_balance',
    ];

    /**
     * Get the attributes that should be cast - Laravel 12 Style
     */
    protected function casts(): array
    {
        return [
            'is_final' => 'boolean',
            'is_active' => 'boolean',
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'level' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the account's full name with code - Laravel 12 Accessor
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->code} - {$this->name}",
        );
    }

    /**
     * Get the account's balance status - Laravel 12 Accessor
     */
    protected function balanceStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->current_balance > 0 ? 'دائن' : ($this->current_balance < 0 ? 'مدين' : 'متوازن'),
        );
    }

    /**
     * Scope a query to only include active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include final accounts
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope a query to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by level
     */
    public function scopeOfLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get the unit that owns the account
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the company that owns the account
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get the child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get the journal entry lines for the account
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Check if account is a parent account
     */
    public function isParent(): bool
    {
        return !$this->is_final && $this->children()->exists();
    }

    /**
     * Check if account can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->journalEntryLines()->exists() && !$this->children()->exists();
    }

    /**
     * Get account tree path
     */
    public function getTreePath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }
}
