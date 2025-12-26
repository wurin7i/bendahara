<?php

use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\JournalEntry;
use WuriN7i\Balance\Models\Transaction;
use WuriN7i\Balance\Services\BalanceCalculator;

uses()->group('services');

beforeEach(function () {
    $this->calculator = new BalanceCalculator;
});

test('calculates asset account balance correctly', function () {
    // Create asset account (increases with debit)
    $account = Account::factory()->assets()->create();
    $transaction = Transaction::factory()->approved()->create();

    // Add 100,000 debit and 30,000 credit
    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::CREDIT,
        'amount' => 30000,
    ]);

    $balance = $this->calculator->getBalance($account->id);

    // Asset: Debit - Credit = 100,000 - 30,000 = 70,000
    expect($balance)->toBe(70000.0);
});

test('calculates income account balance correctly', function () {
    // Create income account (increases with credit)
    $account = Account::factory()->income()->create();
    $transaction = Transaction::factory()->approved()->create();

    // Add 50,000 credit and 10,000 debit
    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::CREDIT,
        'amount' => 50000,
    ]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 10000,
    ]);

    $balance = $this->calculator->getBalance($account->id);

    // Income: Credit - Debit = 50,000 - 10,000 = 40,000
    expect($balance)->toBe(40000.0);
});

test('only approved transactions affect balance', function () {
    $account = Account::factory()->assets()->create();

    // Approved transaction
    $approvedTx = Transaction::factory()->approved()->create();
    JournalEntry::factory()->create([
        'transaction_id' => $approvedTx->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    // Draft transaction (should not affect balance)
    $draftTx = Transaction::factory()->draft()->create();
    JournalEntry::factory()->create([
        'transaction_id' => $draftTx->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 50000,
    ]);

    $balance = $this->calculator->getBalance($account->id);

    // Only approved transaction should count
    expect($balance)->toBe(100000.0);
});

test('gets balance breakdown', function () {
    $account = Account::factory()->assets()->create();
    $transaction = Transaction::factory()->approved()->create();

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::CREDIT,
        'amount' => 30000,
    ]);

    $breakdown = $this->calculator->getBalanceBreakdown($account->id);

    expect($breakdown)
        ->toHaveKey('debits')
        ->toHaveKey('credits')
        ->toHaveKey('balance')
        ->and($breakdown['debits'])->toBe(100000.0)
        ->and($breakdown['credits'])->toBe(30000.0)
        ->and($breakdown['balance'])->toBe(70000.0);
});

test('can get multiple account balances', function () {
    $account1 = Account::factory()->assets()->create();
    $account2 = Account::factory()->income()->create();
    $transaction = Transaction::factory()->approved()->create();

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account1->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 100000,
    ]);

    JournalEntry::factory()->create([
        'transaction_id' => $transaction->id,
        'account_id' => $account2->id,
        'entry_type' => EntryType::CREDIT,
        'amount' => 100000,
    ]);

    $balances = $this->calculator->getAccountBalances([$account1->id, $account2->id]);

    expect($balances)
        ->toHaveKey($account1->id)
        ->toHaveKey($account2->id)
        ->and($balances[$account1->id])->toBe(100000.0)
        ->and($balances[$account2->id])->toBe(100000.0);
});

test('filters balance by date range', function () {
    $account = Account::factory()->assets()->create();

    // Transaction in January
    $tx1 = Transaction::factory()->approved()->create(['date' => '2025-01-15']);
    JournalEntry::factory()->create([
        'transaction_id' => $tx1->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 50000,
    ]);

    // Transaction in February
    $tx2 = Transaction::factory()->approved()->create(['date' => '2025-02-15']);
    JournalEntry::factory()->create([
        'transaction_id' => $tx2->id,
        'account_id' => $account->id,
        'entry_type' => EntryType::DEBIT,
        'amount' => 30000,
    ]);

    // Get balance for January only
    $balance = $this->calculator->getBalance($account->id, [
        'date_from' => '2025-01-01',
        'date_to' => '2025-01-31',
    ]);

    expect($balance)->toBe(50000.0);
});
