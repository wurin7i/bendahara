#!/usr/bin/env php
<?php

/**
 * Bendahara Phase 2 Integration Test
 *
 * This script verifies that the Bendahara multi-division layer
 * properly integrates with the Balance accounting module.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Division;
use App\Models\DivisionAccount;
use App\Services\DivisionAccountService;
use App\Services\DivisionBalanceService;
use App\Services\DivisionTransactionService;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Models\Account;

echo "\n🧪 Bendahara Phase 2 Integration Test\n";
echo "=====================================\n\n";

// Test 1: Verify divisions exist
echo "📋 Test 1: Check divisions...\n";
$divisions = Division::active()->get();
echo '   ✓ Found '.$divisions->count()." active divisions\n";
foreach ($divisions as $division) {
    echo "     - {$division->code}: {$division->name}\n";
}

// Test 2: Verify division accounts mapping
echo "\n📋 Test 2: Check division account mappings...\n";
$divisionAccounts = DivisionAccount::active()->count();
echo "   ✓ Found {$divisionAccounts} active division account mappings\n";

$division = Division::byCode('ACARA')->first();
if ($division) {
    echo "\n   Division: {$division->name}\n";
    $mappings = $division->divisionAccounts()->with('account')->get();
    foreach ($mappings as $mapping) {
        echo "     - {$mapping->display_name} (Base: {$mapping->account->name})\n";
    }
}

// Test 3: Create test transaction
echo "\n📋 Test 3: Create division transaction...\n";
try {
    $divisionTransactionService = app(DivisionTransactionService::class);
    $division = Division::byCode('ACARA')->first();

    // Get Kas and Bank accounts
    $kas = Account::where('code', '101')->first();
    $bank = Account::where('code', '102')->first();

    // Create a simple transfer: Kas -> Bank (Rp 100,000)
    $transaction = $divisionTransactionService->createForDivision(
        division: $division,
        description: 'Test Transfer - Setor Kas ke Bank',
        entries: [
            [
                'account_id' => $bank->id,
                'entry_type' => EntryType::DEBIT,
                'amount' => 100000,
            ],
            [
                'account_id' => $kas->id,
                'entry_type' => EntryType::CREDIT,
                'amount' => 100000,
            ],
        ]
    );

    echo "   ✓ Transaction created: {$transaction->id}\n";
    $transaction = $transaction->fresh();  // Refresh to get division_id
    echo '     Division ID: '.($transaction->division_id ?: 'NULL')."\n";
    echo "     Status: {$transaction->status->value}\n";
    echo '     Balanced: '.($transaction->isBalanced() ? 'YES' : 'NO')."\n";

    // Submit the transaction first (DRAFT -> PENDING)
    $approvalWorkflow = app(\WuriN7i\Balance\Services\ApprovalWorkflow::class);
    $approvalWorkflow->submit($transaction);
    $transaction = $transaction->fresh();
    echo "   ✓ Transaction submitted (Status: {$transaction->status->value})\n";

    // Then approve it (PENDING -> APPROVED)
    $approvalWorkflow->approve($transaction);
    $transaction = $transaction->fresh();
    echo "   ✓ Transaction approved\n";
    echo "     Voucher: {$transaction->voucher_no}\n";
} catch (\Exception $e) {
    echo '   ✗ Error: '.$e->getMessage()."\n";
}

// Test 4: Calculate division balances
echo "\n📋 Test 4: Calculate division balances...\n";
try {
    $balanceService = app(DivisionBalanceService::class);
    $division = Division::byCode('ACARA')->first();

    $summary = $balanceService->getDivisionSummary($division);
    echo "   Division: {$summary['division_name']}\n";
    echo '   Total Assets: Rp '.number_format($summary['total_assets'], 2)."\n";
    echo '   Total Liabilities: Rp '.number_format($summary['total_liabilities'], 2)."\n";
    echo '   Net Position: Rp '.number_format($summary['net_position'], 2)."\n";

    echo "\n   Liquid Accounts (Saku):\n";
    foreach ($summary['liquid_accounts'] as $account) {
        echo "     - {$account['display_name']}: Rp ".number_format($account['balance'], 2)."\n";
    }
} catch (\Exception $e) {
    echo '   ✗ Error: '.$e->getMessage()."\n";
}

// Test 5: Verify relationships
echo "\n📋 Test 5: Test model relationships...\n";
$division = Division::byCode('HUMAS')->first();
echo "   Division: {$division->name}\n";
echo '   Has accounts: '.($division->accounts->count() > 0 ? 'YES' : 'NO')."\n";
echo '   Transactions count: '.$division->transactions()->count()."\n";
echo '   Journal entries count: '.$division->journalEntries()->count()."\n";

// Test 6: Division account service
echo "\n📋 Test 6: Test DivisionAccountService...\n";
$divisionAccountService = app(DivisionAccountService::class);
$division = Division::byCode('PUSAT')->first();
$piutang = Account::where('code', '104')->first();

// Map Piutang to Pusat division
$mapping = $divisionAccountService->mapAccount(
    division: $division,
    account: $piutang,
    aliasName: 'Tagihan Pusat'
);
echo "   ✓ Mapped account: {$mapping->display_name}\n";

// Verify mapping
$isMapped = $divisionAccountService->isMapped($division, $piutang);
echo '   ✓ Verified mapping: '.($isMapped ? 'YES' : 'NO')."\n";

echo "\n✅ All tests completed!\n\n";
