<?php

namespace App\Enums;

enum AccountType: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';

    /**
     * Get the Arabic label for the account type
     */
    public function label(): string
    {
        return match($this) {
            self::ASSET => 'أصول',
            self::LIABILITY => 'خصوم',
            self::EQUITY => 'حقوق ملكية',
            self::REVENUE => 'إيرادات',
            self::EXPENSE => 'مصروفات',
        };
    }

    /**
     * Get the color class for the account type
     */
    public function color(): string
    {
        return match($this) {
            self::ASSET => 'primary',
            self::LIABILITY => 'danger',
            self::EQUITY => 'success',
            self::REVENUE => 'info',
            self::EXPENSE => 'warning',
        };
    }

    /**
     * Get the icon for the account type
     */
    public function icon(): string
    {
        return match($this) {
            self::ASSET => 'bi-building',
            self::LIABILITY => 'bi-credit-card',
            self::EQUITY => 'bi-person-circle',
            self::REVENUE => 'bi-arrow-down-circle',
            self::EXPENSE => 'bi-arrow-up-circle',
        };
    }

    /**
     * Get the normal balance side (debit or credit)
     */
    public function normalBalance(): string
    {
        return match($this) {
            self::ASSET, self::EXPENSE => 'debit',
            self::LIABILITY, self::EQUITY, self::REVENUE => 'credit',
        };
    }

    /**
     * Check if the account type is a balance sheet account
     */
    public function isBalanceSheet(): bool
    {
        return in_array($this, [self::ASSET, self::LIABILITY, self::EQUITY]);
    }

    /**
     * Check if the account type is an income statement account
     */
    public function isIncomeStatement(): bool
    {
        return in_array($this, [self::REVENUE, self::EXPENSE]);
    }

    /**
     * Get all account types as array
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
            'icon' => $case->icon(),
            'normal_balance' => $case->normalBalance(),
        ], self::cases());
    }
}
