<?php

namespace WuriN7i\Balance\Enums;

enum AccountCategory: string
{
    case Assets = 'Assets';
    case Liabilities = 'Liabilities';
    case Equity = 'Equity';
    case Income = 'Income';
    case Expenses = 'Expenses';

    /**
     * Get the normal balance type for this category.
     * Assets and Expenses have debit normal balance.
     * Liabilities, Equity, and Income have credit normal balance.
     */
    public function normalBalance(): EntryType
    {
        return match ($this) {
            self::Assets, self::Expenses => EntryType::DEBIT,
            self::Liabilities, self::Equity, self::Income => EntryType::CREDIT,
        };
    }

    /**
     * Check if this category increases with debit.
     */
    public function increasesWithDebit(): bool
    {
        return $this->normalBalance() === EntryType::DEBIT;
    }

    /**
     * Check if this category increases with credit.
     */
    public function increasesWithCredit(): bool
    {
        return $this->normalBalance() === EntryType::CREDIT;
    }
}
