<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Models\Account;

/**
 * @property string $id
 * @property string $division_id
 * @property string $account_id
 * @property string|null $alias_name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string $display_name
 * @property-read Division $division
 * @property-read Account $account
 *
 * @method static Builder|$this active()
 * @method static Builder|$this forDivision(string|Division $divisionId)
 * @method static Builder|$this forAccount(string|Account $accountId)
 *
 * @extends Model<DivisionAccount>
 */
class DivisionAccount extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'division_id',
        'account_id',
        'alias_name',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the division that owns this mapping.
     *
     * @return BelongsTo<Division,static>|static
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the account from Balance module.
     *
     * @return BelongsTo<Account,static>|static
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope to filter only active division accounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('is_active'), true);
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
     * Scope to filter by account.
     */
    public function scopeForAccount(Builder $query, string|Account $accountId): Builder
    {
        $accountId = $accountId instanceof Account ? $accountId->id : $accountId;

        return $query->where($query->qualifyColumn('account_id'), $accountId);
    }

    /**
     * Get display name (alias or account name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->alias_name ?: $this->account->name;
    }

    /**
     * Check if this is a liquid account (can transact).
     */
    public function isLiquid(): bool
    {
        return $this->account->isLiquid();
    }

    /**
     * Get account behavior.
     */
    public function getBehavior(): AccountBehavior
    {
        return $this->account->account_behavior;
    }
}
