<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WuriN7i\Balance\Database\Factories\AccountFactory;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\AccountCategory;
use WuriN7i\Balance\Enums\EntryType;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property AccountCategory $category
 * @property AccountBehavior $account_behavior
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<JournalEntry> $journalEntries
 *
 * @method static Builder ofCategory(AccountCategory $accountCategory)
 * @method static Builder ofBehavior(AccountBehavior $accoutBehavior)
 * @method static Builder liquid()
 */
class Account extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'account_behavior',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'category' => AccountCategory::class,
            'account_behavior' => AccountBehavior::class,
        ];
    }

    /**
     * Get all journal entries for this account.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get the normal balance type for this account.
     */
    public function normalBalance(): EntryType
    {
        return $this->category->normalBalance();
    }

    /**
     * Check if this account can receive income.
     */
    public function canReceiveIncome(): bool
    {
        return $this->account_behavior->canReceiveIncome();
    }

    /**
     * Check if this account can make expense.
     */
    public function canMakeExpense(): bool
    {
        return $this->account_behavior->canMakeExpense();
    }

    /**
     * Check if this account is liquid.
     */
    public function isLiquid(): bool
    {
        return $this->account_behavior->isLiquid();
    }

    /**
     * Scope to filter by category.
     */
    public function scopeOfCategory(Builder $query, AccountCategory $category): Builder
    {
        return $query->where($query->qualifyColumn('category'), $category);
    }

    /**
     * Scope to filter by behavior.
     */
    public function scopeOfBehavior(Builder $query, AccountBehavior $behavior): Builder
    {
        return $query->where($query->qualifyColumn('account_behavior'), $behavior);
    }

    /**
     * Scope to get only liquid accounts.
     */
    public function scopeLiquid(Builder $query): Builder
    {
        return $query->whereNot($query->qualifyColumn('account_behavior'), AccountBehavior::NON_LIQUID);
    }
}
