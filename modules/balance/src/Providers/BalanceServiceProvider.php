<?php

namespace WuriN7i\Balance\Providers;

use Illuminate\Support\ServiceProvider;
use WuriN7i\Balance\Contracts\ActorProviderInterface;
use WuriN7i\Balance\Contracts\BalanceCalculatorInterface;
use WuriN7i\Balance\Contracts\VoucherGeneratorInterface;
use WuriN7i\Balance\Services\BalanceCalculator;
use WuriN7i\Balance\Services\VoucherGenerator;

class BalanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(BalanceCalculatorInterface::class, BalanceCalculator::class);
        $this->app->bind(VoucherGeneratorInterface::class, VoucherGenerator::class);

        // ActorProviderInterface will be bound by the application layer (Bendahara)
        // This allows Balance to remain decoupled from User model
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations from Balance module
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Optionally publish migrations if Balance becomes a package
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'balance-migrations');
        }
    }
}
