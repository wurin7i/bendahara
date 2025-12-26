<?php

use App\Models\Division;
use App\Models\DivisionAccount;
use App\Services\DivisionBalanceService;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\JournalEntry;
use WuriN7i\Balance\Models\Transaction as BalanceTransaction;

uses()->group('services');

beforeEach(function () {
    $this->service = app(DivisionBalanceService::class);
});

test('calculates account balance for division', function () {
    $division = Division::factory()->create();
    $account = Account::factory()->assets()->create();

    DivisionAccount::create([
        'division_id' => $division->id,
        'account_id' => $account->id,
        'alias_name' => 'Kas',
        'is_active' => true,
    ]);

    // Create transaction for this division
    $transaction = BalanceTransaction::factory()->approved()->create([
        'division_id' => $division->id,
    ]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    $balance = $this->service->getAccountBalance($division, $account);

    expect($balance)->toBe(100000.0);
});

test('only includes transactions from same division', function () {
    $division1 = Division::factory()->create();
    $division2 = Division::factory()->create();
    $account = Account::factory()->assets()->create();

    // Map account to both divisions
    foreach ([$division1, $division2] as $division) {
        DivisionAccount::create([
            'division_id' => $division->id,
            'account_id' => $account->id,
            'alias_name' => 'Kas',
            'is_active' => true,
        ]);
    }

    // Transaction for division 1
    $tx1 = BalanceTransaction::factory()->approved()->create(['division_id' => $division1->id]);
    JournalEntry::factory()->create([
        'transaction_id' => $tx1->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    // Transaction for division 2
    $tx2 = BalanceTransaction::factory()->approved()->create(['division_id' => $division2->id]);
    JournalEntry::factory()->create([
        'transaction_id' => $tx2->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 50000,
    ]);

    $balance1 = $this->service->getAccountBalance($division1, $account);
    $balance2 = $this->service->getAccountBalance($division2, $account);

    expect($balance1)->toBe(100000.0)
        ->and($balance2)->toBe(50000.0);
});

test('calculates total assets', function () {
    $division = Division::factory()->create();
    $cashAccount = Account::factory()->assets()->create();
    $bankAccount = Account::factory()->assets()->create();

    foreach ([$cashAccount, $bankAccount] as $account) {
        DivisionAccount::create([
            'division_id' => $division->id,
            'account_id' => $account->id,
            'alias_name' => $account->name,
            'is_active' => true,
        ]);
    }

    $transaction = BalanceTransaction::factory()->approved()->create(['division_id' => $division->id]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $cashAccount->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 50000,
    ]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $bankAccount->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 30000,
    ]);

    $totalAssets = $this->service->getTotalAssets($division);

    expect($totalAssets)->toBe(80000.0);
});

test('gets division summary', function () {
    $division = Division::factory()->create(['name' => 'Acara', 'code' => 'ACR']);
    $assetAccount = Account::factory()->assets()->create();

    DivisionAccount::create([
        'division_id' => $division->id,
        'account_id' => $assetAccount->id,
        'alias_name' => 'Kas Acara',
        'is_active' => true,
    ]);

    $transaction = BalanceTransaction::factory()->approved()->create(['division_id' => $division->id]);
    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $assetAccount->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    $summary = $this->service->getDivisionSummary($division);

    expect($summary)
        ->toHaveKey('division_id')
        ->toHaveKey('division_name')
        ->toHaveKey('division_code')
        ->toHaveKey('liquid_accounts')
        ->and($summary['division_id'])->toBe($division->id)
        ->and($summary['division_name'])->toBe('Acara')
        ->and($summary['division_code'])->toBe('ACR')
        ->and($summary['total_assets'])->toBe(100000.0);
});
