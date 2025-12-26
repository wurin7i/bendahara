<?php

namespace WuriN7i\Balance\Enums;

enum EntryType: string
{
    case DEBIT = 'DEBIT';
    case CREDIT = 'CREDIT';

    /**
     * Get the opposite entry type.
     */
    public function opposite(): self
    {
        return match ($this) {
            self::DEBIT => self::CREDIT,
            self::CREDIT => self::DEBIT,
        };
    }

    /**
     * Get multiplier for balance calculation.
     * Debit adds (+1), Credit subtracts (-1) for debit-normal accounts.
     */
    public function multiplier(): int
    {
        return match ($this) {
            self::DEBIT => 1,
            self::CREDIT => -1,
        };
    }

    /**
     * Check if this entry type increases the given account category.
     */
    public function increases(AccountCategory $category): bool
    {
        return match ($this) {
            self::DEBIT => $category->increasesWithDebit(),
            self::CREDIT => $category->increasesWithCredit(),
        };
    }

    /**
     * Check if this entry type decreases the given account category.
     */
    public function decreases(AccountCategory $category): bool
    {
        return !$this->increases($category);
    }
}
