<?php

namespace App\Modules\Arsip\Providers;

use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Policies\LetterPolicy;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Letter::class, LetterPolicy::class);
    }
}
