<?php

namespace App\Http\Middleware;

use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $canAccessPlatform = false;

        if ($user) {
            /** @var UserResolverInterface $resolver */
            $resolver = app(UserResolverInterface::class);
            $canAccessPlatform = $resolver->hasRole('super-admin', $user) || $resolver->hasRole('admin-sdm', $user);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'can_access_platform' => $canAccessPlatform,
                'unread_notifications_count' => fn () => $user ? app(NotificationServiceInterface::class)->getUnreadCount($user->id) : 0,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
