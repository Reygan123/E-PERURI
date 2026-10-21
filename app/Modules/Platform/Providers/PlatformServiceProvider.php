<?php

namespace App\Modules\Platform\Providers;

use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service contract bindings will be registered here in Fase 4
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
