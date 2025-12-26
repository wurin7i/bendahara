<?php

namespace WuriN7i\Balance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WuriN7i\Balance\Enums\TransactionStatus;
use WuriN7i\Balance\Models\Transaction;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'description' => fake()->sentence(),
            'total_amount' => fake()->randomFloat(2, 1000, 100000),
            'voucher_no' => null,
            'attachment_url' => fake()->optional()->url(),
            'status' => TransactionStatus::DRAFT,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TransactionStatus::DRAFT,
            'voucher_no' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TransactionStatus::PENDING,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TransactionStatus::APPROVED,
            'voucher_no' => 'VCH-' . date('Y') . '-' . sprintf('%05d', $this->faker->unique()->numberBetween(1, 99999)),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TransactionStatus::REJECTED,
        ]);
    }
}
