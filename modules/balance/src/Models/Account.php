<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\AccountCategory;

class Account extends Model
{
    use HasUuids;

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
    public function normalBalance(): string
    {
        return $this->category->normalBalance()->value;
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
    public function scopeOfCategory($query, AccountCategory $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by behavior.
     */
    public function scopeOfBehavior($query, AccountBehavior $behavior)
    {
        return $query->where('account_behavior', $behavior);
    }

    /**
     * Scope to get only liquid accounts.
     */
    public function scopeLiquid($query)
    {
        return $query->where('account_behavior', '!=', AccountBehavior::NON_LIQUID);
    }
}
