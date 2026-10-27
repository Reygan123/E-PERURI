<?php

namespace App\Modules\Layanan\Providers;

use App\Modules\Layanan\Models\WorkOrder;
use App\Modules\Layanan\Policies\WorkOrderPolicy;
use App\Modules\Layanan\Services\WorkOrderService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class LayananServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WorkOrderService::class);
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

        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
    }
}
