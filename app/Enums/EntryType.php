<?php

namespace App\Enums;

enum EntryType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    /**
     * Get the Arabic label for the entry type
     */
    public function label(): string
    {
        return match($this) {
            self::DEBIT => 'مدين',
            self::CREDIT => 'دائن',
        };
    }

    /**
     * Get the short label for the entry type
     */
    public function shortLabel(): string
    {
        return match($this) {
            self::DEBIT => 'مدين',
            self::CREDIT => 'دائن',
        };
    }

    /**
     * Get the color class for the entry type
     */
    public function color(): string
    {
        return match($this) {
            self::DEBIT => 'success',
            self::CREDIT => 'danger',
        };
    }

    /**
     * Get the opposite entry type
     */
    public function opposite(): self
    {
        return match($this) {
            self::DEBIT => self::CREDIT,
            self::CREDIT => self::DEBIT,
        };
    }

    /**
     * Get all entry types as array
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
        ], self::cases());
    }
}
