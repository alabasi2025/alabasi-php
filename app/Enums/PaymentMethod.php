<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK = 'bank';
    case CHECK = 'check';
    case TRANSFER = 'transfer';

    /**
     * Get the Arabic label for the payment method
     */
    public function label(): string
    {
        return match($this) {
            self::CASH => 'نقدي',
            self::BANK => 'بنكي',
            self::CHECK => 'شيك',
            self::TRANSFER => 'تحويل',
        };
    }

    /**
     * Get the icon for the payment method
     */
    public function icon(): string
    {
        return match($this) {
            self::CASH => 'bi-cash-stack',
            self::BANK => 'bi-bank',
            self::CHECK => 'bi-receipt',
            self::TRANSFER => 'bi-arrow-left-right',
        };
    }

    /**
     * Get all payment methods as array
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}
