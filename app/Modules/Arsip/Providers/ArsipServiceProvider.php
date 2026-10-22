<?php

namespace App\Modules\Arsip\Providers;

use Illuminate\Support\ServiceProvider;

class ArsipServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service bindings will be registered in Phase 2
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__.'/../routes.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
