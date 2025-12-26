<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use WuriN7i\Balance\Contracts\ActorProviderInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind ActorProvider implementation for Balance module
        $this->app->singleton(ActorProviderInterface::class, UserActorProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
