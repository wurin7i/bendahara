<?php

namespace WuriN7i\Balance\Enums;

enum TransactionAction: string
{
    case SUBMIT = 'SUBMIT';
    case EDIT = 'EDIT';
    case APPROVE = 'APPROVE';
    case REJECT = 'REJECT';
    case VOID = 'VOID';

    /**
     * Check if this action requires a comment.
     */
    public function requiresComment(): bool
    {
        return match ($this) {
            self::REJECT, self::VOID => true,
            self::SUBMIT, self::EDIT, self::APPROVE => false,
        };
    }

    /**
     * Get the status change associated with this action.
     */
    public function resultingStatus(): ?TransactionStatus
    {
        return match ($this) {
            self::SUBMIT => TransactionStatus::PENDING,
            self::APPROVE => TransactionStatus::APPROVED,
            self::REJECT => TransactionStatus::REJECTED,
            self::VOID => TransactionStatus::VOID,
            self::EDIT => null, // EDIT doesn't change status
        };
    }

    /**
     * Check if action can be performed on given status.
     */
    public function canPerformOn(TransactionStatus $status): bool
    {
        return match ($this) {
            self::SUBMIT => $status === TransactionStatus::DRAFT || $status === TransactionStatus::REJECTED,
            self::EDIT => $status->isEditable(),
            self::APPROVE => $status === TransactionStatus::PENDING,
            self::REJECT => $status === TransactionStatus::PENDING,
            self::VOID => $status === TransactionStatus::APPROVED,
        };
    }

    /**
     * Get description of the action.
     */
    public function description(): string
    {
        return match ($this) {
            self::SUBMIT => 'Submit transaction for approval',
            self::EDIT => 'Edit transaction details',
            self::APPROVE => 'Approve transaction',
            self::REJECT => 'Reject transaction with reason',
            self::VOID => 'Void approved transaction',
        };
    }
}
