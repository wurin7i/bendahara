<?php

namespace WuriN7i\Balance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WuriN7i\Balance\Enums\EntryType;
use WuriN7i\Balance\Models\Account;
use WuriN7i\Balance\Models\JournalEntry;
use WuriN7i\Balance\Models\Transaction;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'account_id' => Account::factory(),
            'entry_type' => $this->faker->randomElement(EntryType::cases()),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
        ];
    }

    public function debit(): static
    {
        return $this->state(fn(array $attributes) => [
            'entry_type' => EntryType::DEBIT,
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn(array $attributes) => [
            'entry_type' => EntryType::CREDIT,
        ]);
    }
}
