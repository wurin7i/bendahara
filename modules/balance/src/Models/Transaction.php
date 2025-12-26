<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Enums\TransactionStatus;

class Transaction extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'date',
        'description',
        'total_amount',
        'attachment_url',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'voucher_no', // Auto-generated after approval
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_amount' => 'decimal:2',
            'status' => TransactionStatus::class,
        ];
    }

    /**
     * Get all journal entries for this transaction.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get all logs for this transaction.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLog::class)->orderBy('created_at');
    }

    /**
     * Check if transaction can be edited.
     */
    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /**
     * Check if transaction is final (immutable).
     */
    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Check if transaction affects balance calculation.
     */
    public function affectsBalance(): bool
    {
        return $this->status->affectsBalance();
    }

    /**
     * Check if transaction can transition to target status.
     */
    public function canTransitionTo(TransactionStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }

    /**
     * Get total debits from journal entries.
     */
    public function totalDebits(): float
    {
        return (float) $this->journalEntries()
            ->where('entry_type', EntryType::DEBIT)
            ->sum('amount');
    }

    /**
     * Get total credits from journal entries.
     */
    public function totalCredits(): float
    {
        return (float) $this->journalEntries()
            ->where('entry_type', EntryType::CREDIT)
            ->sum('amount');
    }

    /**
     * Check if journal entries are balanced (debits = credits).
     */
    public function isBalanced(): bool
    {
        return abs($this->totalDebits() - $this->totalCredits()) < 0.01;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeOfStatus($query, TransactionStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get only approved transactions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', TransactionStatus::APPROVED);
    }

    /**
     * Scope to get only draft transactions.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', TransactionStatus::DRAFT);
    }

    /**
     * Scope to get only pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::PENDING);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
