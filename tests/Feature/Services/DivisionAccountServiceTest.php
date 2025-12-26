<?php

use App\Models\Division;
use App\Models\DivisionAccount;
use App\Services\DivisionAccountService;
use WuriN7i\Balance\Models\Account;

uses()->group('services');

beforeEach(function () {
    $this->service = new DivisionAccountService;
});

test('can map account to division', function () {
    $division = Division::factory()->create();
    $account = Account::factory()->create();

    $divisionAccount = $this->service->mapAccount(
        division: $division,
        account: $account,
        aliasName: 'Kas ' . $division->name,
        isActive: true
    );

    expect($divisionAccount)
        ->toBeInstanceOf(DivisionAccount::class)
        ->division_id->toBe($division->id)
        ->account_id->toBe($account->id)
        ->alias_name->toBe('Kas ' . $division->name);
});

test('can map multiple accounts', function () {
    $division = Division::factory()->create();
    $accounts = Account::factory()->count(3)->create();

    $mappings = $accounts->map(fn($account) => [
        'account_id' => $account->id,
        'alias_name' => $account->name . ' ' . $division->name,
        'is_active' => true,
    ])->toArray();

    $divisionAccounts = $this->service->mapAccounts($division, $mappings);

    expect($divisionAccounts)->toHaveCount(3);
});

test('can unmap account', function () {
    $division = Division::factory()->create();
    $account = Account::factory()->create();

    $this->service->mapAccount($division, $account, 'Test Account');

    expect($this->service->isMapped($division, $account))->toBeTrue();

    $result = $this->service->unmapAccount($division, $account);

    expect($result)->toBeTrue()
        ->and($this->service->isMapped($division, $account))->toBeFalse();
});

test('can update alias', function () {
    $division = Division::factory()->create();
    $account = Account::factory()->create();

    $this->service->mapAccount($division, $account, 'Old Name');

    $updated = $this->service->updateAlias($division, $account, 'New Name');

    expect($updated->alias_name)->toBe('New Name');
});

test('can set active status', function () {
    $division = Division::factory()->create();
    $account = Account::factory()->create();

    $divisionAccount = $this->service->mapAccount($division, $account, 'Test');

    expect($divisionAccount->is_active)->toBeTrue();

    $updated = $this->service->setActive($division, $account, false);

    expect($updated->is_active)->toBeFalse();
});

test('can get active accounts', function () {
    $division = Division::factory()->create();
    $accounts = Account::factory()->count(3)->create();

    foreach ($accounts as $index => $account) {
        $this->service->mapAccount(
            division: $division,
            account: $account,
            aliasName: 'Account ' . $index,
            isActive: $index < 2 // First 2 active, last one inactive
        );
    }

    $activeAccounts = $this->service->getActiveAccounts($division);

    expect($activeAccounts)->toHaveCount(2);
});

test('can get liquid accounts', function () {
    $division = Division::factory()->create();
    $liquidAccount = Account::factory()->assets()->create();
    $nonLiquidAccount = Account::factory()->assets()->nonLiquid()->create();

    $this->service->mapAccount($division, $liquidAccount, 'Liquid');
    $this->service->mapAccount($division, $nonLiquidAccount, 'Non-Liquid');

    $liquidAccounts = $this->service->getLiquidAccounts($division);

    expect($liquidAccounts)->toHaveCount(1);
});

test('can check if mapped', function () {
    $division = Division::factory()->create();
    $mappedAccount = Account::factory()->create();
    $unmappedAccount = Account::factory()->create();

    $this->service->mapAccount($division, $mappedAccount, 'Mapped Account');

    expect($this->service->isMapped($division, $mappedAccount))->toBeTrue()
        ->and($this->service->isMapped($division, $unmappedAccount))->toBeFalse();
});

test('can get all mappings', function () {
    $division = Division::factory()->create();
    $accounts = Account::factory()->count(3)->create();

    foreach ($accounts as $account) {
        $this->service->mapAccount($division, $account, $account->name);
    }

    $mappings = $this->service->getAllMappings($division);

    expect($mappings)->toHaveCount(3);

    foreach ($mappings as $mapping) {
        expect($mapping)->toBeArray()
            ->toHaveKey('account_id')
            ->toHaveKey('account_code')
            ->toHaveKey('alias_name');
    }
});
