<?php

namespace App\Modules\Sdm\Providers;

use App\Modules\Sdm\Models\JobDescription;
use App\Modules\Sdm\Policies\JobDescriptionPolicy;
use App\Modules\Sdm\Services\JobDescriptionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SdmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JobDescriptionService::class);
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

        Gate::policy(JobDescription::class, JobDescriptionPolicy::class);
    }
}
