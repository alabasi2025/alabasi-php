<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
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
        'name',
        'code',
        'address',
        'phone',
        'manager_name',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the branch.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the journal entries for the branch.
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
