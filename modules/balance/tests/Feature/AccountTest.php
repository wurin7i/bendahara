<?php

use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\AccountCategory;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Models\Account;

uses()->group('models');

test('can create account', function () {
    $account = Account::factory()->create([
        'code' => '101',
        'name' => 'Cash',
        'category' => AccountCategory::Assets,
        'account_behavior' => AccountBehavior::FLEXIBLE,
    ]);

    expect($account->category)->toBe(AccountCategory::Assets)
        ->and($account->account_behavior)->toBe(AccountBehavior::FLEXIBLE);

    $this->assertDatabaseHas('accounts', [
        'code' => '101',
        'name' => 'Cash',
        'category' => 'Assets',
    ]);
});

test('account has normal balance', function () {
    $assetAccount = Account::factory()->assets()->create();
    $incomeAccount = Account::factory()->income()->create();

    expect($assetAccount->normalBalance())->toBe(EntryType::DEBIT)
        ->and($incomeAccount->normalBalance())->toBe(EntryType::CREDIT);
});

test('can filter accounts by category', function () {
    Account::factory()->assets()->count(3)->create();
    Account::factory()->income()->count(2)->create();
    Account::factory()->expenses()->create();

    $assets = Account::ofCategory(AccountCategory::Assets)->get();
    $income = Account::ofCategory(AccountCategory::Income)->get();

    expect($assets)->toHaveCount(3)
        ->and($income)->toHaveCount(2);
});

test('can filter liquid accounts', function () {
    Account::factory()->assets()->count(2)->create(['account_behavior' => AccountBehavior::FLEXIBLE]);
    Account::factory()->assets()->nonLiquid()->create();

    $liquidAccounts = Account::liquid()->get();

    expect($liquidAccounts)->toHaveCount(2)
        ->each->isLiquid()->toBeTrue();
});

test('credit only account behavior', function () {
    $account = Account::factory()->income()->creditOnly()->create();

    expect($account->canReceiveIncome())->toBeFalse()
        ->and($account->canMakeExpense())->toBeTrue();
});

test('flexible account behavior', function () {
    $account = Account::factory()->create(['account_behavior' => AccountBehavior::FLEXIBLE]);

    expect($account->canReceiveIncome())->toBeTrue()
        ->and($account->canMakeExpense())->toBeTrue();
});

test('account relationships', function () {
    $account = Account::factory()->create();

    expect($account->journalEntries())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});
