<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\JournalEntry;
use WuriN7i\Balance\Models\Transaction;

/**
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Collection<DivisionAccount> $divisionAccounts
 * @property-read Collection<DivisionAccount> $activeDivisionAccounts
 * @property-read Collection<Transaction> $transactions
 * @property-read Collection<JournalEntry> $journalEntries
 * @property-read Collection<Account> $accounts
 *
 * @method static Builder|static active()
 * @method static Builder|static byCode(string $code)
 *
 * @extends Model<Division>
 */
class Division extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
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
     * Get all division accounts (saku) for this division.
     *
     * @return HasMany<DivisionAccount,static>|static
     */
    public function divisionAccounts(): HasMany
    {
        return $this->hasMany(DivisionAccount::class);
    }

    /**
     * Get all active division accounts.
     *
     * @return HasMany<DivisionAccount,static>|static
     */
    public function activeDivisionAccounts(): HasMany
    {
        return $this->divisionAccounts()->active();
    }

    /**
     * Get all transactions for this division.
     *
     * @return HasMany<Transaction,static>|static
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all journal entries through transactions.
     *
     * @return HasManyThrough<JournalEntry,Transaction,static>|static
     */
    public function journalEntries(): HasManyThrough
    {
        return $this->hasManyThrough(
            JournalEntry::class,
            Transaction::class,
            'division_id', // Foreign key on transactions table
            'transaction_id', // Foreign key on journal_entries table
            'id', // Local key on divisions table
            'id' // Local key on transactions table
        );
    }

    /**
     * Get all accounts accessible by this division.
     *
     * Returns accounts that have been mapped to this division
     * through the division_accounts pivot table.
     *
     * @return HasManyThrough<Account,DivisionAccount,static>|static
     */
    public function accounts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Account::class,
            DivisionAccount::class,
            'division_id', // Foreign key on division_accounts table
            'id', // Foreign key on accounts table
            'id', // Local key on divisions table
            'account_id' // Local key on division_accounts table
        );
    }

    /**
     * Scope to filter only active divisions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('is_active'), true);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where($query->qualifyColumn('code'), $code);
    }

    /**
     * Check if an account is available for this division.
     */
    public function hasAccount(string|Account $accountId): bool
    {
        return $this->divisionAccounts()
            ->forAccount($accountId)
            ->active()
            ->exists();
    }

    /**
     * Get the alias name for an account in this division.
     */
    public function getAccountAlias(string|Account $accountId): ?string
    {
        $divisionAccount = $this->divisionAccounts()
            ->forAccount($accountId)
            ->first();

        return $divisionAccount?->alias_name;
    }
}
