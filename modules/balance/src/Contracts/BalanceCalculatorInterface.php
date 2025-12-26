<?php

namespace WuriN7i\Balance\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface for calculating account balances.
 * 
 * This interface allows the application layer to provide custom balance calculation
 * logic, such as filtering by division, date range, or other criteria.
 */
interface BalanceCalculatorInterface
{
    /**
     * Calculate the balance for a specific account.
     * 
     * @param string $accountId The account UUID
     * @param array $filters Additional filters (e.g., ['division_id' => 'xxx', 'date_from' => '2024-01-01'])
     * @return float The calculated balance
     */
    public function getBalance(string $accountId, array $filters = []): float;

    /**
     * Calculate balances for multiple accounts at once.
     * 
     * @param array $accountIds Array of account UUIDs (empty array = all accounts)
     * @param array $filters Additional filters
     * @return array Array of [account_id => balance]
     */
    public function getAccountBalances(array $accountIds = [], array $filters = []): array;

    /**
     * Get detailed balance breakdown for an account.
     * 
     * Returns total debits, total credits, and net balance.
     * 
     * @param string $accountId The account UUID
     * @param array $filters Additional filters
     * @return array ['debits' => float, 'credits' => float, 'balance' => float]
     */
    public function getBalanceBreakdown(string $accountId, array $filters = []): array;

    /**
     * Get all journal entries that affect an account's balance.
     * 
     * @param string $accountId The account UUID
     * @param array $filters Additional filters
     * @return Collection Collection of JournalEntry models
     */
    public function getAccountEntries(string $accountId, array $filters = []): Collection;
}
