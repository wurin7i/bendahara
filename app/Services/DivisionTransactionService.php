<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use WuriN7i\Balance\Contracts\ActorProviderInterface;
use WuriN7i\Balance\Contracts\VoucherGeneratorInterface;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\Transaction as BalanceTransaction;
use WuriN7i\Balance\Services\TransactionService;

class DivisionTransactionService extends TransactionService
{
    public function __construct(
        ActorProviderInterface $actorProvider,
        VoucherGeneratorInterface $voucherGenerator,
        private DivisionAccountService $divisionAccountService
    ) {
        parent::__construct($actorProvider, $voucherGenerator);
    }

    /**
     * Create a transaction for a specific division.
     *
     * @param  Division  $division  The division creating the transaction
     * @param  string  $description  Transaction description
     * @param  array  $entries  Journal entries [['account_id' => '...', 'entry_type' => EntryType, 'amount' => 123.45], ...]
     * @param  string|null  $transactionDate  Optional transaction date (defaults to today)
     *
     * @throws \Exception
     */
    public function createForDivision(
        Division $division,
        string $description,
        array $entries,
        ?string $transactionDate = null
    ): BalanceTransaction {
        // Validate that all accounts are mapped to this division
        foreach ($entries as $entry) {
            $account = Account::find($entry['account_id']);

            if (! $this->divisionAccountService->isMapped($division, $account)) {
                throw new \Exception("Account {$entry['account_id']} is not mapped to division {$division->name}");
            }
        }

        // Calculate total amount
        $totalAmount = collect($entries)->sum('amount');

        // Create transaction using parent service
        $transaction = $this->createTransaction(
            data: [
                'date' => $transactionDate ?? now()->format('Y-m-d'),
                'description' => $description,
                'total_amount' => $totalAmount,
            ],
            journalEntries: $entries
        );

        // Add division_id to the transaction
        $transaction->update(['division_id' => $division->id]);

        return $transaction->fresh();
    }

    /**
     * Create a simple transfer between two accounts in a division.
     *
     * @param  Division  $division  The division making the transfer
     * @param  string  $fromAccountId  Account to debit (source)
     * @param  string  $toAccountId  Account to credit (destination)
     * @param  float  $amount  Transfer amount
     * @param  string  $description  Transfer description
     * @param  string|null  $transactionDate  Optional transaction date
     */
    public function createTransfer(
        Division $division,
        string $fromAccountId,
        string $toAccountId,
        float $amount,
        string $description,
        ?string $transactionDate = null
    ): BalanceTransaction {
        return $this->createForDivision(
            division: $division,
            description: $description,
            entries: [
                [
                    'account_id' => $toAccountId,
                    'entry_type' => EntryType::DEBIT,
                    'amount' => $amount,
                ],
                [
                    'account_id' => $fromAccountId,
                    'entry_type' => EntryType::CREDIT,
                    'amount' => $amount,
                ],
            ],
            transactionDate: $transactionDate
        );
    }

    /**
     * Get all transactions for a division.
     */
    public function getDivisionTransactions(Division $division, array $filters = []): Collection
    {
        $query = Transaction::forDivision($division->id)
            ->with(['journalEntries.account', 'logs']);

        // Apply status filter
        if (isset($filters['status'])) {
            $query->ofStatus($filters['status']);
        }

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->dateFrom($filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->dateTo($filters['date_to']);
        }

        return $query->recent()->get();
    }

    /**
     * Get transaction statistics for a division.
     */
    public function getDivisionStats(Division $division): array
    {
        $baseQuery = Transaction::forDivision($division->id);

        return [
            'total' => $baseQuery->count(),
            'pending' => $baseQuery->pending()->count(),
            'approved' => $baseQuery->approved()->count(),
            'rejected' => $baseQuery->rejected()->count(),
        ];
    }
}
