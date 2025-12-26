<?php

namespace WuriN7i\Balance\Services;

use Illuminate\Support\Facades\DB;
use WuriN7i\Balance\Contracts\ActorProviderInterface;
use WuriN7i\Balance\Contracts\VoucherGeneratorInterface;
use WuriN7i\Balance\Enums\TransactionAction;
use WuriN7i\Balance\Enums\TransactionStatus;
use WuriN7i\Balance\Models\Transaction;
use WuriN7i\Balance\Models\TransactionLog;

class ApprovalWorkflow
{
    public function __construct(
        private ActorProviderInterface $actorProvider,
        private VoucherGeneratorInterface $voucherGenerator
    ) {}

    /**
     * Submit a transaction for approval (DRAFT -> PENDING).
     *
     * @throws \Exception
     */
    public function submit(Transaction $transaction): void
    {
        if (! $transaction->status->canTransitionTo(TransactionStatus::PENDING)) {
            throw new \Exception(
                "Cannot submit transaction from {$transaction->status->value} status"
            );
        }

        // Validate transaction is balanced
        if (! $transaction->isBalanced()) {
            throw new \Exception('Transaction journal entries are not balanced');
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => TransactionStatus::PENDING]);

            $this->logAction($transaction, TransactionAction::SUBMIT);
        });
    }

    /**
     * Approve a transaction (PENDING -> APPROVED).
     *
     * @throws \Exception
     */
    public function approve(Transaction $transaction): void
    {
        if (! $transaction->status->canTransitionTo(TransactionStatus::APPROVED)) {
            throw new \Exception(
                "Cannot approve transaction from {$transaction->status->value} status"
            );
        }

        DB::transaction(function () use ($transaction) {
            // Generate voucher number
            $voucherNo = $this->voucherGenerator->generate($transaction);

            // Update transaction
            $transaction->update([
                'status' => TransactionStatus::APPROVED,
                'voucher_no' => $voucherNo,
            ]);

            $this->logAction(
                $transaction,
                TransactionAction::APPROVE,
                "Approved with voucher number: {$voucherNo}"
            );
        });
    }

    /**
     * Reject a transaction (PENDING -> REJECTED).
     *
     * @throws \Exception
     */
    public function reject(Transaction $transaction, string $reason): void
    {
        if (! $transaction->status->canTransitionTo(TransactionStatus::REJECTED)) {
            throw new \Exception(
                "Cannot reject transaction from {$transaction->status->value} status"
            );
        }

        if (empty(trim($reason))) {
            throw new \Exception('Rejection reason is required');
        }

        DB::transaction(function () use ($transaction, $reason) {
            $transaction->update(['status' => TransactionStatus::REJECTED]);

            $this->logAction($transaction, TransactionAction::REJECT, $reason);
        });
    }

    /**
     * Void an approved transaction (APPROVED -> VOID).
     *
     * @throws \Exception
     */
    public function void(Transaction $transaction, string $reason): void
    {
        if (! $transaction->status->canTransitionTo(TransactionStatus::VOID)) {
            throw new \Exception(
                "Cannot void transaction from {$transaction->status->value} status"
            );
        }

        if (empty(trim($reason))) {
            throw new \Exception('Void reason is required');
        }

        DB::transaction(function () use ($transaction, $reason) {
            $transaction->update(['status' => TransactionStatus::VOID]);

            $this->logAction($transaction, TransactionAction::VOID, $reason);
        });
    }

    /**
     * Check if an action can be performed on a transaction.
     */
    public function canPerformAction(Transaction $transaction, TransactionAction $action): bool
    {
        return $action->canPerformOn($transaction->status);
    }

    /**
     * Get the list of allowed actions for a transaction.
     *
     * @return array Array of TransactionAction
     */
    public function getAllowedActions(Transaction $transaction): array
    {
        $actions = [];

        foreach (TransactionAction::cases() as $action) {
            if ($this->canPerformAction($transaction, $action)) {
                $actions[] = $action;
            }
        }

        return $actions;
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
