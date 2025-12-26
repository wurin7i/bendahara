<?php

namespace WuriN7i\Balance\Enums;

enum AccountBehavior: string
{
    case FLEXIBLE = 'FLEXIBLE';
    case TRANSIT_ONLY = 'TRANSIT_ONLY';
    case CREDIT_ONLY = 'CREDIT_ONLY';
    case NON_LIQUID = 'NON_LIQUID';

    /**
     * Check if account can be used for income transactions.
     */
    public function canReceiveIncome(): bool
    {
        return match ($this) {
            self::FLEXIBLE, self::TRANSIT_ONLY => true,
            self::CREDIT_ONLY, self::NON_LIQUID => false,
        };
    }

    /**
     * Check if account can be used for expense transactions.
     */
    public function canMakeExpense(): bool
    {
        return match ($this) {
            self::FLEXIBLE, self::CREDIT_ONLY => true,
            self::TRANSIT_ONLY, self::NON_LIQUID => false,
        };
    }

    /**
     * Check if account is liquid (can have transactions).
     */
    public function isLiquid(): bool
    {
        return $this !== self::NON_LIQUID;
    }

    /**
     * Get description of the behavior.
     */
    public function description(): string
    {
        return match ($this) {
            self::FLEXIBLE => 'Can be used for both income and expenses',
            self::TRANSIT_ONLY => 'Only for receiving income (e.g., QRIS)',
            self::CREDIT_ONLY => 'Only for expenses (e.g., Debt/Hutang)',
            self::NON_LIQUID => 'Non-liquid account (e.g., Capital/Modal)',
        };
    }
}
