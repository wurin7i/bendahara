<?php

use WuriN7i\Balance\Enums\AccountCategory;
use WuriN7i\Balance\Enums\EntryType;

test('assets has debit normal balance', function () {
    $category = AccountCategory::Assets;

    expect($category->normalBalance())->toBe(EntryType::DEBIT)
        ->and($category->increasesWithDebit())->toBeTrue()
        ->and($category->increasesWithCredit())->toBeFalse();
});

test('expenses has debit normal balance', function () {
    $category = AccountCategory::Expenses;

    expect($category->normalBalance())->toBe(EntryType::DEBIT)
        ->and($category->increasesWithDebit())->toBeTrue()
        ->and($category->increasesWithCredit())->toBeFalse();
});

test('liabilities has credit normal balance', function () {
    $category = AccountCategory::Liabilities;

    expect($category->normalBalance())->toBe(EntryType::CREDIT)
        ->and($category->increasesWithDebit())->toBeFalse()
        ->and($category->increasesWithCredit())->toBeTrue();
});

test('equity has credit normal balance', function () {
    $category = AccountCategory::Equity;

    expect($category->normalBalance())->toBe(EntryType::CREDIT)
        ->and($category->increasesWithDebit())->toBeFalse()
        ->and($category->increasesWithCredit())->toBeTrue();
});

test('income has credit normal balance', function () {
    $category = AccountCategory::Income;

    expect($category->normalBalance())->toBe(EntryType::CREDIT)
        ->and($category->increasesWithDebit())->toBeFalse()
        ->and($category->increasesWithCredit())->toBeTrue();
});
