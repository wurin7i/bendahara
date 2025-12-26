<?php

namespace WuriN7i\Balance\Services;

use Illuminate\Support\Facades\DB;
use WuriN7i\Balance\Contracts\ActorProviderInterface;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Enums\TransactionAction;
use WuriN7i\Balance\Enums\TransactionStatus;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\JournalEntry;
use WuriN7i\Balance\Models\Transaction;
use WuriN7i\Balance\Models\TransactionLog;

class TransactionService
{
    public function __construct(
        private ActorProviderInterface $actorProvider
    ) {}

    /**
     * Create a new transaction with journal entries.
     *
     * @param  array  $data  Transaction data ['date', 'description', 'total_amount', 'attachment_url']
     * @param  array  $journalEntries  Array of ['account_id', 'entry_type', 'amount']
     *
     * @throws \Exception
     */
    public function createTransaction(array $data, array $journalEntries): Transaction
    {
        // Validate double-entry
        $this->validateDoubleEntry($journalEntries);

        // Validate account behaviors
        $this->validateAccountBehaviors($journalEntries);

        return DB::transaction(function () use ($data, $journalEntries) {
            // Create transaction
            $transaction = Transaction::create([
                'date' => $data['date'],
                'description' => $data['description'],
                'total_amount' => $data['total_amount'],
                'attachment_url' => $data['attachment_url'] ?? null,
                'status' => TransactionStatus::DRAFT,
            ]);

            // Create journal entries
            foreach ($journalEntries as $entry) {
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $entry['account_id'],
                    'entry_type' => $entry['entry_type'],
                    'amount' => $entry['amount'],
                ]);
            }

            // Log the creation
            $this->logAction($transaction, TransactionAction::SUBMIT, 'Transaction created');

            return $transaction->fresh(['journalEntries', 'logs']);
        });
    }

    /**
     * Update an existing transaction.
     *
     * @param  array  $data  Transaction data
     * @param  array  $journalEntries  New journal entries
     *
     * @throws \Exception
     */
    public function updateTransaction(Transaction $transaction, array $data, array $journalEntries): Transaction
    {
        // Check if transaction is editable
        if (! $transaction->isEditable()) {
            throw new \Exception("Transaction cannot be edited in {$transaction->status->value} status");
        }

        // Validate double-entry
        $this->validateDoubleEntry($journalEntries);

        // Validate account behaviors
        $this->validateAccountBehaviors($journalEntries);

        return DB::transaction(function () use ($transaction, $data, $journalEntries) {
            // Update transaction
            $transaction->update([
                'date' => $data['date'] ?? $transaction->date,
                'description' => $data['description'] ?? $transaction->description,
                'total_amount' => $data['total_amount'] ?? $transaction->total_amount,
                'attachment_url' => $data['attachment_url'] ?? $transaction->attachment_url,
            ]);

            // Delete old journal entries
            $transaction->journalEntries()->delete();

            // Create new journal entries
            foreach ($journalEntries as $entry) {
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $entry['account_id'],
                    'entry_type' => $entry['entry_type'],
                    'amount' => $entry['amount'],
                ]);
            }

            // Log the edit
            $this->logAction($transaction, TransactionAction::EDIT, 'Transaction updated');

            return $transaction->fresh(['journalEntries', 'logs']);
        });
    }

    /**
     * Validate that debits equal credits (double-entry rule).
     *
     * @throws \Exception
     */
    public function validateDoubleEntry(array $journalEntries): void
    {
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($journalEntries as $entry) {
            $entryType = $entry['entry_type'] instanceof EntryType
                ? $entry['entry_type']
                : EntryType::from($entry['entry_type']);

            if ($entryType === EntryType::DEBIT) {
                $totalDebits += $entry['amount'];
            } else {
                $totalCredits += $entry['amount'];
            }
        }

        // Allow small floating point differences
        if (abs($totalDebits - $totalCredits) > 0.01) {
            throw new \Exception(
                "Double-entry validation failed: Debits ({$totalDebits}) must equal Credits ({$totalCredits})"
            );
        }
    }

    /**
     * Validate account behaviors for the given entries.
     *
     * @throws \Exception
     */
    protected function validateAccountBehaviors(array $journalEntries): void
    {
        $accountIds = array_column($journalEntries, 'account_id');
        $accounts = Account::whereIn('id', $accountIds)->get()->keyBy('id');

        foreach ($journalEntries as $entry) {
            $account = $accounts->get($entry['account_id']);

            if (! $account) {
                throw new \Exception("Account {$entry['account_id']} not found");
            }

            // Check if account is liquid
            if (! $account->isLiquid()) {
                throw new \Exception(
                    "Account '{$account->name}' ({$account->account_behavior->value}) cannot be used in transactions"
                );
            }

            $entryType = $entry['entry_type'] instanceof EntryType
                ? $entry['entry_type']
                : EntryType::from($entry['entry_type']);

            // Validate behavior rules based on entry type
            // For income: DEBIT to cash/bank account, CREDIT to income account
            // For expense: DEBIT to expense account, CREDIT to cash/bank account

            // TRANSIT_ONLY accounts can only be debited (receive money)
            if (
                $account->account_behavior === AccountBehavior::TRANSIT_ONLY
                && $entryType === EntryType::CREDIT
            ) {
                throw new \Exception(
                    "Account '{$account->name}' (TRANSIT_ONLY) can only receive income (DEBIT entry)"
                );
            }

            // CREDIT_ONLY accounts can only be credited (debt/liabilities)
            if (
                $account->account_behavior === AccountBehavior::CREDIT_ONLY
                && $entryType === EntryType::DEBIT
            ) {
                throw new \Exception(
                    "Account '{$account->name}' (CREDIT_ONLY) can only be used for expenses (CREDIT entry)"
                );
            }
        }
    }

    /**
     * Log an action on a transaction.
     */
    protected function logAction(Transaction $transaction, TransactionAction $action, ?string $comment = null): void
    {
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'actor_id' => $this->actorProvider->getCurrentActorId(),
            'action' => $action,
            'comment' => $comment,
        ]);
    }
}
