<?php

namespace App\Modules\Platform\Providers;

use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;
use App\Modules\Platform\Policies\EmployeePolicy;
use App\Modules\Platform\Policies\OrgUnitPolicy;
use App\Modules\Platform\Policies\PositionPolicy;
use App\Modules\Platform\Policies\UserPolicy;
use App\Modules\Platform\Services\AuditService;
use App\Modules\Platform\Services\DirectoryService;
use App\Modules\Platform\Services\FilesService;
use App\Modules\Platform\Services\NotificationService;
use App\Modules\Platform\Services\NumberingService;
use App\Modules\Platform\Services\UserResolverService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DirectoryServiceInterface::class, DirectoryService::class);
        $this->app->singleton(NumberingServiceInterface::class, NumberingService::class);
        $this->app->singleton(FilesServiceInterface::class, FilesService::class);
        $this->app->singleton(AuditServiceInterface::class, AuditService::class);
        $this->app->singleton(NotificationServiceInterface::class, NotificationService::class);
        $this->app->singleton(UserResolverInterface::class, UserResolverService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Gate::policy(OrgUnit::class, OrgUnitPolicy::class);
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
