<?php

namespace WuriN7i\Balance\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use WuriN7i\Balance\Contracts\BalanceCalculatorInterface;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Enums\TransactionStatus;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\JournalEntry;

class BalanceCalculator implements BalanceCalculatorInterface
{
    /**
     * Calculate the balance for a specific account.
     *
     * @param string $accountId The account UUID
     * @param array $filters Additional filters (e.g., ['division_id' => 'xxx', 'date_from' => '2024-01-01'])
     * @return float The calculated balance
     */
    public function getBalance(string $accountId, array $filters = []): float
    {
        $breakdown = $this->getBalanceBreakdown($accountId, $filters);
        return $breakdown['balance'];
    }

    /**
     * Calculate balances for multiple accounts at once.
     *
     * @param array $accountIds Array of account UUIDs (empty array = all accounts)
     * @param array $filters Additional filters
     * @return array Array of [account_id => balance]
     */
    public function getAccountBalances(array $accountIds = [], array $filters = []): array
    {
        if (empty($accountIds)) {
            $accountIds = Account::pluck('id')->toArray();
        }

        $balances = [];
        foreach ($accountIds as $accountId) {
            $balances[$accountId] = $this->getBalance($accountId, $filters);
        }

        return $balances;
    }

    /**
     * Get detailed balance breakdown for an account.
     *
     * @param string $accountId The account UUID
     * @param array $filters Additional filters
     * @return array ['debits' => float, 'credits' => float, 'balance' => float]
     */
    public function getBalanceBreakdown(string $accountId, array $filters = []): array
    {
        $account = Account::findOrFail($accountId);
        $entries = $this->getAccountEntries($accountId, $filters);

        $totalDebits = $entries->where('entry_type', EntryType::DEBIT)->sum('amount');
        $totalCredits = $entries->where('entry_type', EntryType::CREDIT)->sum('amount');

        // Calculate balance based on account category
        // Assets & Expenses: Debit increases, Credit decreases
        // Liabilities, Equity, Income: Credit increases, Debit decreases
        $balance = $account->category->increasesWithDebit()
            ? $totalDebits - $totalCredits
            : $totalCredits - $totalDebits;

        return [
            'debits' => (float) $totalDebits,
            'credits' => (float) $totalCredits,
            'balance' => (float) $balance,
        ];
    }

    /**
     * Get all journal entries that affect an account's balance.
     *
     * @param string $accountId The account UUID
     * @param array $filters Additional filters
     * @return Collection Collection of JournalEntry models
     */
    public function getAccountEntries(string $accountId, array $filters = []): Collection
    {
        $query = JournalEntry::query()
            ->where('account_id', $accountId)
            ->whereHas('transaction', function ($q) use ($filters) {
                // Only include APPROVED transactions in balance calculation
                $q->where('status', TransactionStatus::APPROVED);

                // Apply date filters
                if (isset($filters['date_from'])) {
                    $q->where('date', '>=', $filters['date_from']);
                }
                if (isset($filters['date_to'])) {
                    $q->where('date', '<=', $filters['date_to']);
                }

                // Apply custom filters (e.g., division_id for Bendahara)
                foreach ($filters as $key => $value) {
                    if (!in_array($key, ['date_from', 'date_to']) && $value !== null) {
                        $q->where($key, $value);
                    }
                }
            })
            ->with(['transaction', 'account']);

        return $query->get();
    }

    /**
     * Get balance summary for all accounts grouped by category.
     *
     * @param array $filters Additional filters
     * @return array
     */
    public function getBalanceSummaryByCategory(array $filters = []): array
    {
        $accounts = Account::all();
        $summary = [];

        foreach ($accounts as $account) {
            $category = $account->category->value;

            if (!isset($summary[$category])) {
                $summary[$category] = [
                    'total_balance' => 0,
                    'accounts' => [],
                ];
            }

            $balance = $this->getBalance($account->id, $filters);

            $summary[$category]['accounts'][] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];

            $summary[$category]['total_balance'] += $balance;
        }

        return $summary;
    }

    /**
     * Calculate total assets.
     *
     * @param array $filters Additional filters
     * @return float
     */
    public function getTotalAssets(array $filters = []): float
    {
        $assetAccounts = Account::where('category', 'Assets')->pluck('id');
        $balances = $this->getAccountBalances($assetAccounts->toArray(), $filters);

        return array_sum($balances);
    }

    /**
     * Calculate total liabilities.
     *
     * @param array $filters Additional filters
     * @return float
     */
    public function getTotalLiabilities(array $filters = []): float
    {
        $liabilityAccounts = Account::where('category', 'Liabilities')->pluck('id');
        $balances = $this->getAccountBalances($liabilityAccounts->toArray(), $filters);

        return array_sum($balances);
    }

    /**
     * Calculate equity (Assets - Liabilities).
     *
     * @param array $filters Additional filters
     * @return float
     */
    public function getEquity(array $filters = []): float
    {
        return $this->getTotalAssets($filters) - $this->getTotalLiabilities($filters);
    }
}
