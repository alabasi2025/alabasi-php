<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit Model - نموذج الوحدات
 * 
 * يدعم Multi-tenancy ومحدث بميزات Laravel 11/12:
 * - Casts as Methods (Laravel 11)
 * - Strict Types (Laravel 12)
 * - Better Performance (Laravel 12)
 */
class Unit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'settings',
    ];

    /**
     * Get the attributes that should be cast.
     * 
     * ميزة Laravel 11: Casts as Methods بدلاً من Property
     * فائدة: Type Safety أفضل + IDE Support محسّن
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope للوحدات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للقاعدة المركزية
     */
    public function scopeCentral($query)
    {
        return $query->where('type', 'central');
    }

    /**
     * Scope لوحدات العمل
     */
    public function scopeBusiness($query)
    {
        return $query->where('type', 'business');
    }

    /**
     * العلاقة: الوحدة لديها عدة مؤسسات
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * العلاقة: الوحدة لديها عدة حسابات
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * العلاقة: الوحدة لديها عدة قيود
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * العلاقة: الوحدة لديها عدة مستخدمين
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * العلاقة: التحويلات الصادرة من الوحدة
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(ClearingTransaction::class, 'from_unit_id');
    }

    /**
     * العلاقة: التحويلات الواردة إلى الوحدة
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(ClearingTransaction::class, 'to_unit_id');
    }

    /**
     * Accessor: الحصول على اسم نوع الوحدة بالعربية
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'central' => 'القاعدة المركزية',
            'business' => 'وحدة عمل',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor: الحصول على حالة التفعيل بالعربية
     */
    public function getStatusNameAttribute(): string
    {
        return $this->is_active ? 'نشط' : 'غير نشط';
    }

    /**
     * Method: الحصول على إعداد معين
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Method: تعيين إعداد معين
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Method: التحقق من أن الوحدة هي القاعدة المركزية
     */
    public function isCentral(): bool
    {
        return $this->type === 'central';
    }

    /**
     * Method: التحقق من أن الوحدة هي وحدة عمل
     */
    public function isBusiness(): bool
    {
        return $this->type === 'business';
    }
}
