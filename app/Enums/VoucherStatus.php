<?php

namespace App\Enums;

enum VoucherStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Get the Arabic label for the voucher status
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'مسودة',
            self::PENDING => 'قيد الانتظار',
            self::APPROVED => 'معتمد',
            self::REJECTED => 'مرفوض',
        };
    }

    /**
     * Get the color class for the voucher status
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }

    /**
     * Get the badge class for the voucher status
     */
    public function badge(): string
    {
        return 'badge bg-' . $this->color();
    }

    /**
     * Check if the voucher can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING]);
    }

    /**
     * Check if the voucher can be deleted
     */
    public function canDelete(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED]);
    }

    /**
     * Check if the voucher can be approved
     */
    public function canApprove(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if the voucher can be rejected
     */
    public function canReject(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Get all voucher statuses as array
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
