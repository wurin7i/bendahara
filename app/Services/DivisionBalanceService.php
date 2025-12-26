<?php

namespace App\Services;

use App\Models\Division;
use WuriN7i\Balance\Contracts\BalanceCalculatorInterface;
use WuriN7i\Balance\Enums\AccountCategory;
use WuriN7i\Balance\Models\Account;

class DivisionBalanceService
{
    public function __construct(
        private BalanceCalculatorInterface $balanceCalculator
    ) {}

    /**
     * Calculate balance for specific account in a division.
     */
    public function getAccountBalance(Division $division, Account $account): float
    {
        return $this->balanceCalculator->getBalance(
            accountId: $account->id,
            filters: ['division_id' => $division->id]
        );
    }

    /**
     * Get all account balances for a division.
     */
    public function getDivisionBalances(Division $division): array
    {
        $accounts = $division->accounts()->get();
        $balances = [];

        foreach ($accounts as $account) {
            $divisionAccount = $division->divisionAccounts()
                ->forAccount($account->id)
                ->first();

            $balances[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'alias_name' => $divisionAccount?->alias_name,
                'display_name' => $divisionAccount?->display_name ?? $account->name,
                'balance' => $this->getAccountBalance($division, $account),
                'behavior' => $account->account_behavior->value,
                'category' => $account->category->value,
            ];
        }

        return $balances;
    }

    /**
     * Get all liquid accounts (saku) balances for a division.
     */
    public function getLiquidBalances(Division $division): array
    {
        $accounts = $division->accounts()->liquid()->get();
        $balances = [];

        foreach ($accounts as $account) {
            $divisionAccount = $division->divisionAccounts()
                ->forAccount($account->id)
                ->first();

            $balances[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'alias_name' => $divisionAccount?->alias_name,
                'display_name' => $divisionAccount?->display_name ?? $account->name,
                'balance' => $this->getAccountBalance($division, $account),
            ];
        }

        return $balances;
    }

    /**
     * Get total assets for a division.
     */
    public function getTotalAssets(Division $division): float
    {
        $accounts = $division->accounts()
            ->ofCategory(AccountCategory::Assets)
            ->get();

        $total = 0;
        foreach ($accounts as $account) {
            $total += $this->getAccountBalance($division, $account);
        }

        return $total;
    }

    /**
     * Get total liabilities for a division.
     */
    public function getTotalLiabilities(Division $division): float
    {
        $accounts = $division->accounts()
            ->ofCategory(AccountCategory::Liabilities)
            ->get();

        $total = 0;
        foreach ($accounts as $account) {
            $total += $this->getAccountBalance($division, $account);
        }

        return $total;
    }

    /**
     * Get net position (assets - liabilities) for a division.
     */
    public function getNetPosition(Division $division): float
    {
        return $this->getTotalAssets($division) - $this->getTotalLiabilities($division);
    }

    /**
     * Get summary of division financial position.
     */
    public function getDivisionSummary(Division $division): array
    {
        return [
            'division_id' => $division->id,
            'division_name' => $division->name,
            'division_code' => $division->code,
            'total_assets' => $this->getTotalAssets($division),
            'total_liabilities' => $this->getTotalLiabilities($division),
            'net_position' => $this->getNetPosition($division),
            'liquid_accounts' => $this->getLiquidBalances($division),
        ];
    }
}
