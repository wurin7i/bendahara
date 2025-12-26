<?php

use WuriN7i\Balance\Enums\TransactionStatus;
use WuriN7i\Balance\Models\Transaction;

uses()->group('models');

test('can create transaction', function () {
    $transaction = Transaction::factory()->create([
        'date' => '2025-01-15',
        'description' => 'Test transaction',
        'total_amount' => 10000.00,
        'status' => TransactionStatus::DRAFT,
    ]);

    expect($transaction->status)->toBe(TransactionStatus::DRAFT);

    $this->assertDatabaseHas('transactions', [
        'description' => 'Test transaction',
        'total_amount' => '10000.00',
        'status' => 'DRAFT',
    ]);
});

test('draft transaction is editable', function () {
    $transaction = Transaction::factory()->draft()->create();

    expect($transaction->isEditable())->toBeTrue()
        ->and($transaction->isFinal())->toBeFalse()
        ->and($transaction->affectsBalance())->toBeFalse();
});

test('approved transaction is final', function () {
    $transaction = Transaction::factory()->approved()->create();

    expect($transaction->isEditable())->toBeFalse()
        ->and($transaction->isFinal())->toBeTrue()
        ->and($transaction->affectsBalance())->toBeTrue()
        ->and($transaction->voucher_no)->not->toBeNull();
});

test('can check status transitions', function () {
    $draft = Transaction::factory()->draft()->create();

    expect($draft->canTransitionTo(TransactionStatus::PENDING))->toBeTrue()
        ->and($draft->canTransitionTo(TransactionStatus::APPROVED))->toBeFalse();
});

test('can filter transactions by status', function () {
    Transaction::factory()->draft()->count(3)->create();
    Transaction::factory()->approved()->count(2)->create();
    Transaction::factory()->rejected()->create();

    $drafts = Transaction::draft()->get();
    $approved = Transaction::approved()->get();

    expect($drafts)->toHaveCount(3)
        ->and($approved)->toHaveCount(2);
});

test('transaction relationships', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->journalEntries())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($transaction->logs())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('can filter transactions by date range', function () {
    Transaction::factory()->create(['date' => '2025-01-01']);
    Transaction::factory()->create(['date' => '2025-01-15']);
    Transaction::factory()->create(['date' => '2025-02-01']);

    $transactions = Transaction::dateBetween('2025-01-01', '2025-01-31')->get();

    expect($transactions)->toHaveCount(2);
});
