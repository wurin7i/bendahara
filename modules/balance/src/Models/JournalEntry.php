<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WuriN7i\Balance\Database\Factories\JournalEntryFactory;
use WuriN7i\Balance\Enums\EntryType;

/**
 * @property string $id
 * @property string $transaction_id
 * @property string $account_id
 * @property EntryType $entry_type
 * @property float $amount
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Transaction $transaction
 * @property-read Account $account
 *
 * @method static Builder ofType(EntryType $type)
 * @method static Builder debits()
 * @method static Builder credits()
 * @method static Builder forAccount(string|Account $accountId)
 * @method static Builder approved()
 * @method static float totalAmount()
 */
class JournalEntry extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): JournalEntryFactory
    {
        return JournalEntryFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'journal_entries';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'transaction_id',
        'account_id',
        'entry_type',
        'amount',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'entry_type' => EntryType::class,
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the transaction that owns this journal entry.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the account that this entry belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Check if this entry is a debit.
     */
    public function isDebit(): bool
    {
        return $this->entry_type === EntryType::DEBIT;
    }

    /**
     * Check if this entry is a credit.
     */
    public function isCredit(): bool
    {
        return $this->entry_type === EntryType::CREDIT;
    }

    /**
     * Get the signed amount based on entry type.
     * Positive for debit, negative for credit.
     */
    public function signedAmount(): float
    {
        return $this->amount * $this->entry_type->multiplier();
    }

    /**
     * Check if this entry increases the account balance.
     */
    public function increasesAccount(): bool
    {
        return $this->entry_type->increases($this->account->category);
    }

    /**
     * Check if this entry decreases the account balance.
     */
    public function decreasesAccount(): bool
    {
        return $this->entry_type->decreases($this->account->category);
    }

    /**
     * Scope to filter by entry type.
     */
    public function scopeOfType(Builder $query, EntryType $type): Builder
    {
        return $query->where($query->qualifyColumn('entry_type'), $type);
    }

    /**
     * Scope to get only debits.
     */
    public function scopeDebits(Builder $query): Builder
    {
        return $this->scopeOfType($query, EntryType::DEBIT);
    }

    /**
     * Scope to get only credits.
     */
    public function scopeCredits(Builder $query): Builder
    {
        return $this->scopeOfType($query, EntryType::CREDIT);
    }

    /**
     * Scope to filter by account.
     */
    public function scopeForAccount(Builder $query, string|Account $accountId): Builder
    {
        $accountId = $accountId instanceof Account ? $accountId->id : $accountId;

        return $query->where($query->qualifyColumn('account_id'), $accountId);
    }

    /**
     * Scope to get only entries from approved transactions.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereHas('transaction', fn(Builder $q) => $q->approved());
    }

    public function scopeTotalAmount(Builder $query): float
    {
        return $query->sum($query->qualifyColumn('amount'));
    }
}
