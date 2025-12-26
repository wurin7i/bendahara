<?php

namespace App\Services;

use App\Models\Division;
use App\Models\DivisionAccount;
use Illuminate\Support\Collection;
use WuriN7i\Balance\Models\Account;

class DivisionAccountService
{
    /**
     * Map an account to a division (create saku).
     */
    public function mapAccount(
        Division $division,
        Account $account,
        ?string $aliasName = null,
        bool $isActive = true
    ): DivisionAccount {
        return DivisionAccount::updateOrCreate(
            [
                'division_id' => $division->id,
                'account_id' => $account->id,
            ],
            [
                'alias_name' => $aliasName,
                'is_active' => $isActive,
            ]
        );
    }

    /**
     * Map multiple accounts to a division.
     */
    public function mapAccounts(Division $division, array $accountMappings): Collection
    {
        $divisionAccounts = collect();

        foreach ($accountMappings as $mapping) {
            $account = Account::find($mapping['account_id']);
            if ($account) {
                $divisionAccounts->push(
                    $this->mapAccount(
                        division: $division,
                        account: $account,
                        aliasName: $mapping['alias_name'] ?? null,
                        isActive: $mapping['is_active'] ?? true
                    )
                );
            }
        }

        return $divisionAccounts;
    }

    /**
     * Unmap an account from a division.
     */
    public function unmapAccount(Division $division, Account $account): bool
    {
        return DivisionAccount::forDivision($division->id)
            ->forAccount($account->id)
            ->delete() > 0;
    }

    /**
     * Update alias name for a division account.
     */
    public function updateAlias(
        Division $division,
        Account $account,
        ?string $aliasName
    ): DivisionAccount {
        $divisionAccount = DivisionAccount::forDivision($division->id)
            ->forAccount($account->id)
            ->firstOrFail();

        $divisionAccount->update(['alias_name' => $aliasName]);

        return $divisionAccount->fresh();
    }

    /**
     * Activate or deactivate a division account.
     */
    public function setActive(
        Division $division,
        Account $account,
        bool $isActive
    ): DivisionAccount {
        $divisionAccount = DivisionAccount::forDivision($division->id)
            ->forAccount($account->id)
            ->firstOrFail();

        $divisionAccount->update(['is_active' => $isActive]);

        return $divisionAccount->fresh();
    }

    /**
     * Get all active accounts for a division.
     */
    public function getActiveAccounts(Division $division): Collection
    {
        return $division->activeDivisionAccounts()->with('account')->get();
    }

    /**
     * Get all liquid accounts (saku) for a division.
     */
    public function getLiquidAccounts(Division $division): Collection
    {
        return $division->activeDivisionAccounts()
            ->whereHas('account', fn($q) => $q->liquid())
            ->with('account')
            ->get();
    }

    /**
     * Check if account is mapped to division.
     */
    public function isMapped(Division $division, Account $account): bool
    {
        return DivisionAccount::forDivision($division->id)
            ->forAccount($account->id)
            ->exists();
    }

    /**
     * Get division account mapping.
     */
    public function getMapping(Division $division, Account $account): ?DivisionAccount
    {
        return DivisionAccount::forDivision($division->id)
            ->forAccount($account->id)
            ->first();
    }

    /**
     * Get all mappings for a division with account details.
     */
    public function getAllMappings(Division $division): Collection
    {
        return $division->divisionAccounts()
            ->with('account')
            ->get()
            ->map(function (DivisionAccount $divisionAccount) {
                return [
                    'id' => $divisionAccount->id,
                    'account_id' => $divisionAccount->account_id,
                    'account_code' => $divisionAccount->account->code,
                    'account_name' => $divisionAccount->account->name,
                    'alias_name' => $divisionAccount->alias_name,
                    'display_name' => $divisionAccount->display_name,
                    'is_active' => $divisionAccount->is_active,
                    'account_behavior' => $divisionAccount->account->account_behavior->value,
                    'account_category' => $divisionAccount->account->account_category->value,
                ];
            });
    }
}
