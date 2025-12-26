<?php

namespace WuriN7i\Balance\Enums;

enum TransactionStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case VOID = 'VOID';

    /**
     * Check if transaction can be edited.
     */
    public function isEditable(): bool
    {
        return match ($this) {
            self::DRAFT, self::REJECTED => true,
            self::PENDING, self::APPROVED, self::VOID => false,
        };
    }

    /**
     * Check if transaction is final (immutable).
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::APPROVED, self::VOID => true,
            self::DRAFT, self::PENDING, self::REJECTED => false,
        };
    }

    /**
     * Check if transaction affects balance calculation.
     */
    public function affectsBalance(): bool
    {
        return $this === self::APPROVED;
    }

    /**
     * Get allowed next statuses.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::PENDING],
            self::PENDING => [self::APPROVED, self::REJECTED],
            self::APPROVED => [self::VOID],
            self::REJECTED => [self::PENDING],
            self::VOID => [],
        };
    }

    /**
     * Check if transition to target status is allowed.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Get color representation for UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'yellow',
            self::PENDING => 'orange',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::VOID => 'gray',
        };
    }
}
