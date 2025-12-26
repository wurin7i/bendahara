<?php

namespace WuriN7i\Balance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\AccountCategory;
use WuriN7i\Balance\Models\Account;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('###'),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(AccountCategory::cases()),
            'account_behavior' => AccountBehavior::FLEXIBLE,
        ];
    }

    public function assets(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => AccountCategory::Assets,
        ]);
    }

    public function liabilities(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => AccountCategory::Liabilities,
        ]);
    }

    public function equity(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => AccountCategory::Equity,
        ]);
    }

    public function income(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => AccountCategory::Income,
        ]);
    }

    public function expenses(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => AccountCategory::Expenses,
        ]);
    }

    public function creditOnly(): static
    {
        return $this->state(fn(array $attributes) => [
            'account_behavior' => AccountBehavior::CREDIT_ONLY,
        ]);
    }

    public function transitOnly(): static
    {
        return $this->state(fn(array $attributes) => [
            'account_behavior' => AccountBehavior::TRANSIT_ONLY,
        ]);
    }

    public function nonLiquid(): static
    {
        return $this->state(fn(array $attributes) => [
            'account_behavior' => AccountBehavior::NON_LIQUID,
        ]);
    }
}
