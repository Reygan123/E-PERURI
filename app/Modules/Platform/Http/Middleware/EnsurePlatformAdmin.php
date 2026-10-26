<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Contracts\UserResolverInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function __construct(
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Allow super-admin and admin-sdm
        if ($this->userResolver->hasRole('super-admin', $user) || $this->userResolver->hasRole('admin-sdm', $user)) {
            return $next($request);
        }

        // Allow if user has any platform manage permission
        if (
            $this->userResolver->hasPermission('platform.org_unit.manage', $user) ||
            $this->userResolver->hasPermission('platform.position.manage', $user) ||
            $this->userResolver->hasPermission('platform.employee.manage', $user) ||
            $this->userResolver->hasPermission('platform.user.manage', $user)
        ) {
            return $next($request);
        }

        abort(403, 'Akses terbatas: Halaman Manajemen Platform Inti hanya diperuntukkan bagi Administrator Sistem dan Administrator SDM.');
    }
}
