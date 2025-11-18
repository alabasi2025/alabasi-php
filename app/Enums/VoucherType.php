<?php

namespace App\Enums;

enum VoucherType: string
{
    case RECEIPT = 'receipt';
    case PAYMENT = 'payment';

    /**
     * Get the Arabic label for the voucher type
     */
    public function label(): string
    {
        return match($this) {
            self::RECEIPT => 'سند قبض',
            self::PAYMENT => 'سند صرف',
        };
    }

    /**
     * Get the color class for the voucher type
     */
    public function color(): string
    {
        return match($this) {
            self::RECEIPT => 'success',
            self::PAYMENT => 'danger',
        };
    }

    /**
     * Get the icon for the voucher type
     */
    public function icon(): string
    {
        return match($this) {
            self::RECEIPT => 'bi-arrow-down-circle',
            self::PAYMENT => 'bi-arrow-up-circle',
        };
    }

    /**
     * Get all voucher types as array
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}
