<?php

use App\Models\Division;
use App\Models\DivisionAccount;
use WuriN7i\Balance\Models\Account;

uses()->group('models');

test('can create division', function () {
    $division = Division::factory()->create([
        'name' => 'Acara',
        'code' => 'ACR',
        'description' => 'Divisi Acara',
        'is_active' => true,
    ]);

    expect($division)->toBeInstanceOf(Division::class)
        ->and($division->is_active)->toBeTrue();

    $this->assertDatabaseHas('divisions', [
        'name' => 'Acara',
        'code' => 'ACR',
        'is_active' => true,
    ]);
});

test('can filter active divisions', function () {
    Division::factory()->count(3)->create(['is_active' => true]);
    Division::factory()->count(2)->inactive()->create();

    $activeDivisions = Division::active()->get();

    expect($activeDivisions)->toHaveCount(3);

    $activeDivisions->each(function ($division) {
        expect($division->is_active)->toBeTrue();
    });
});

test('can filter by code', function () {
    Division::factory()->create(['code' => 'ACR']);
    Division::factory()->create(['code' => 'HMS']);

    $division = Division::byCode('ACR')->first();

    expect($division)
        ->not->toBeNull()
        ->code->toBe('ACR');
});

test('has division accounts relationship', function () {
    $division = Division::factory()->create();
    $account = Account::factory()->create();

    $divisionAccount = DivisionAccount::create([
        'division_id' => $division->id,
        'account_id' => $account->id,
        'alias_name' => 'Kas ' . $division->name,
        'is_active' => true,
    ]);

    expect($division->divisionAccounts)
        ->toHaveCount(1)
        ->first()->id->toBe($divisionAccount->id);
});

test('can check if division has account', function () {
    $division = Division::factory()->create();
    $account1 = Account::factory()->create();
    $account2 = Account::factory()->create();

    DivisionAccount::create([
        'division_id' => $division->id,
        'account_id' => $account1->id,
        'alias_name' => 'Test Account',
        'is_active' => true,
    ]);

    expect($division->hasAccount($account1->id))->toBeTrue()
        ->and($division->hasAccount($account2->id))->toBeFalse();
});

test('can get account alias', function () {
    $division = Division::factory()->create(['name' => 'Acara']);
    $account = Account::factory()->create(['name' => 'Kas']);

    DivisionAccount::create([
        'division_id' => $division->id,
        'account_id' => $account->id,
        'alias_name' => 'Kas Acara',
        'is_active' => true,
    ]);

    $alias = $division->getAccountAlias($account->id);

    expect($alias)->toBe('Kas Acara');
});

test('can access accounts through relationship', function () {
    $division = Division::factory()->create();
    $accounts = Account::factory()->count(3)->create();

    foreach ($accounts as $account) {
        DivisionAccount::create([
            'division_id' => $division->id,
            'account_id' => $account->id,
            'alias_name' => $account->name . ' ' . $division->name,
            'is_active' => true,
        ]);
    }

    expect($division->accounts)->toHaveCount(3);
});
