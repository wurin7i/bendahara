<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WuriN7i\Balance\Database\Factories\TransactionFactory;
use WuriN7i\Balance\Enums\TransactionStatus;

/**
 * @property string $id
 * @property \Illuminate\Support\Carbon $date
 * @property string $description
 * @property float $total_amount
 * @property string|null $attachment_url
 * @property TransactionStatus $status
 * @property string|null $voucher_no
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<JournalEntry> $journalEntries
 * @property-read \Illuminate\Database\Eloquent\Collection<TransactionLog> $logs
 *
 * @method static Builder ofStatus(TransactionStatus $status)
 * @method static Builder approved()
 * @method static Builder draft()
 * @method static Builder pending()
 * @method static Builder rejected()
 * @method static Builder dateBetween($startDate, $endDate)
 */
class Transaction extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TransactionFactory
    {
        return TransactionFactory::new();
    }

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
        'status',
        'voucher_no',
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
        return $this->hasMany(TransactionLog::class)->recent();
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
        return (float) $this->journalEntries()->debits()->totalAmount();
    }

    /**
     * Get total credits from journal entries.
     */
    public function totalCredits(): float
    {
        return (float) $this->journalEntries()->credits()->totalAmount();
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
    public function scopeOfStatus(Builder $query, TransactionStatus $status): Builder
    {
        return $query->where($query->qualifyColumn('status'), $status);
    }

    /**
     * Scope to get only approved transactions.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $this->scopeOfStatus($query, TransactionStatus::APPROVED);
    }

    /**
     * Scope to get only draft transactions.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $this->scopeOfStatus($query, TransactionStatus::DRAFT);
    }

    /**
     * Scope to get only pending transactions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $this->scopeOfStatus($query, TransactionStatus::PENDING);
    }

    /**
     * Scope to get only rejected transactions.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $this->scopeOfStatus($query, TransactionStatus::REJECTED);
    }

    /**
     * Scope to get only void transactions.
     */
    public function scopeVoid(Builder $query): Builder
    {
        return $this->scopeOfStatus($query, TransactionStatus::VOID);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween($query->qualifyColumn('date'), [$startDate, $endDate]);
    }

    public function scopeDateFrom(Builder $query, $date): Builder
    {
        return $query->where($query->qualifyColumn('date'), '>=', $date);
    }

    public function scopeDateTo(Builder $query, $date): Builder
    {
        return $query->where($query->qualifyColumn('date'), '<=', $date);
    }
}
