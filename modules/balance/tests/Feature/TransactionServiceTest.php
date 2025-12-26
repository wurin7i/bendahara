<?php

use WuriN7i\Balance\Contracts\ActorProviderInterface;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Enums\TransactionAction;
use WuriN7i\Balance\Enums\TransactionStatus;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\Transaction;
use WuriN7i\Balance\Services\TransactionService;

uses()->group('services');

beforeEach(function () {
    // Mock ActorProviderInterface
    $this->actorProvider = Mockery::mock(ActorProviderInterface::class);
    $this->actorProvider->shouldReceive('getCurrentActorId')->andReturn('test-actor-123');
    $this->actorProvider->shouldReceive('hasCurrentActor')->andReturn(true);

    app()->instance(ActorProviderInterface::class, $this->actorProvider);

    $this->service = new TransactionService($this->actorProvider);
});

test('creates transaction with journal entries', function () {
    $cashAccount = Account::factory()->assets()->create();
    $incomeAccount = Account::factory()->income()->create();

    $transactionData = [
        'date' => '2025-01-15',
        'description' => 'Penerimaan iuran',
        'total_amount' => 100000,
    ];

    $journalEntries = [
        [
            'account_id' => $cashAccount->id,
            'entry_type' => EntryType::DEBIT,
            'amount' => 100000,
        ],
        [
            'account_id' => $incomeAccount->id,
            'entry_type' => EntryType::CREDIT,
            'amount' => 100000,
        ],
    ];

    $transaction = $this->service->createTransaction($transactionData, $journalEntries);

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->status->toBe(TransactionStatus::DRAFT)
        ->and($transaction->journalEntries)->toHaveCount(2);

    // Check transaction log
    $this->assertDatabaseHas('transaction_logs', [
        'transaction_id' => $transaction->id,
        'action' => TransactionAction::SUBMIT->value,
    ]);
});

test('validates double entry rule', function () {
    $cashAccount = Account::factory()->assets()->create();
    $incomeAccount = Account::factory()->income()->create();

    $transactionData = [
        'date' => '2025-01-15',
        'description' => 'Invalid transaction',
        'total_amount' => 100000,
    ];

    // Unbalanced entries: 100,000 debit vs 50,000 credit
    $journalEntries = [
        [
            'account_id' => $cashAccount->id,
            'entry_type' => EntryType::DEBIT,
            'amount' => 100000,
        ],
        [
            'account_id' => $incomeAccount->id,
            'entry_type' => EntryType::CREDIT,
            'amount' => 50000,
        ],
    ];

    expect(fn() => $this->service->createTransaction($transactionData, $journalEntries))
        ->toThrow(Exception::class, 'Double-entry validation failed');
});

test('validates account behaviors', function () {
    $transitAccount = Account::factory()->assets()->create([
        'account_behavior' => AccountBehavior::TRANSIT_ONLY,
    ]);
    $cashAccount = Account::factory()->assets()->create();

    $transactionData = [
        'date' => '2025-01-15',
        'description' => 'Invalid transaction',
        'total_amount' => 100000,
    ];

    // TRANSIT_ONLY account cannot be credited
    $journalEntries = [
        [
            'account_id' => $cashAccount->id,
            'entry_type' => EntryType::DEBIT,
            'amount' => 100000,
        ],
        [
            'account_id' => $transitAccount->id,
            'entry_type' => EntryType::CREDIT,
            'amount' => 100000,
        ],
    ];

    expect(fn() => $this->service->createTransaction($transactionData, $journalEntries))
        ->toThrow(Exception::class, 'TRANSIT_ONLY');
});

test('rejects non liquid accounts', function () {
    $nonLiquidAccount = Account::factory()->assets()->nonLiquid()->create();
    $cashAccount = Account::factory()->assets()->create();

    $transactionData = [
        'date' => '2025-01-15',
        'description' => 'Invalid transaction',
        'total_amount' => 100000,
    ];

    $journalEntries = [
        [
            'account_id' => $nonLiquidAccount->id,
            'entry_type' => EntryType::DEBIT,
            'amount' => 100000,
        ],
        [
            'account_id' => $cashAccount->id,
            'entry_type' => EntryType::CREDIT,
            'amount' => 100000,
        ],
    ];

    expect(fn() => $this->service->createTransaction($transactionData, $journalEntries))
        ->toThrow(Exception::class, 'cannot be used in transactions');
});

test('can update draft transaction', function () {
    $cashAccount = Account::factory()->assets()->create();
    $incomeAccount = Account::factory()->income()->create();

    // Create initial transaction
    $transaction = Transaction::factory()->draft()->create([
        'description' => 'Original description',
    ]);

    $journalEntries = [
        [
            'account_id' => $cashAccount->id,
            'entry_type' => EntryType::DEBIT,
            'amount' => 100000,
        ],
        [
            'account_id' => $incomeAccount->id,
            'entry_type' => EntryType::CREDIT,
            'amount' => 100000,
        ],
    ];

    $updated = $this->service->updateTransaction(
        $transaction,
        ['description' => 'Updated description'],
        $journalEntries
    );

    expect($updated->description)->toBe('Updated description')
        ->and($updated->journalEntries)->toHaveCount(2);
});

test('cannot update approved transaction', function () {
    $transaction = Transaction::factory()->approved()->create();

    expect(fn() => $this->service->updateTransaction($transaction, [], []))
        ->toThrow(Exception::class, 'cannot be edited');
});

test('logs actor id', function () {
    $cashAccount = Account::factory()->assets()->create();
    $incomeAccount = Account::factory()->income()->create();

    $transactionData = [
        'date' => '2025-01-15',
        'description' => 'Test transaction',
        'total_amount' => 100000,
    ];

    $journalEntries = [
        [
            'account_id' => $cashAccount->id,
            'entry_type' => EntryType::DEBIT,
            'amount' => 100000,
        ],
        [
            'account_id' => $incomeAccount->id,
            'entry_type' => EntryType::CREDIT,
            'amount' => 100000,
        ],
    ];

    $transaction = $this->service->createTransaction($transactionData, $journalEntries);

    $this->assertDatabaseHas('transaction_logs', [
        'transaction_id' => $transaction->id,
        'actor_id' => 'test-actor-123',
    ]);
});
