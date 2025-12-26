<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WuriN7i\Balance\Models\Transaction as BalanceTransaction;

/**
 * @property string|null $division_id
 * @property-read string $division_name
 * @property-read ?Division $division
 *
 * @method static Builder|$this forDivision(string|Division $divisionId)
 * @method static Builder|$this forActiveDivision()
 *
 * @extends BalanceTransaction
 */
class Transaction extends BalanceTransaction
{
    /**
     * The attributes that are mass assignable (appended to parent).
     */
    protected $fillable = [
        'division_id',
        'date',
        'description',
        'total_amount',
        'attachment_url',
        'voucher_no',
        'status',
    ];

    /**
     * Get the division that owns this transaction.
     *
     * @return BelongsTo<Division,static>|static
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Scope to filter by division.
     */
    public function scopeForDivision(Builder $query, string|Division $divisionId): Builder
    {
        $divisionId = $divisionId instanceof Division ? $divisionId->id : $divisionId;

        return $query->where($query->qualifyColumn('division_id'), $divisionId);
    }

    /**
     * Scope to filter by active division.
     */
    public function scopeForActiveDivision(Builder $query): Builder
    {
        return $query->whereHas('division', fn (Builder $q) => $q->active());
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest($query->qualifyColumn('date'));
    }

    /**
     * Check if transaction belongs to a division.
     */
    public function hasDivision(): bool
    {
        return ! is_null($this->division_id);
    }

    /**
     * Get division name (or "Global" if no division).
     */
    public function getDivisionNameAttribute(): string
    {
        return $this->division?->name ?? 'Global';
    }
}
