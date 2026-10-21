<?php

namespace App\Modules\Platform\Policies;

use App\Models\User;
use App\Modules\Platform\Contracts\UserResolverInterface;

class UserPolicy
{
    public function __construct(
        protected UserResolverInterface $userResolver
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.user.manage', $user);
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $this->userResolver->hasPermission('platform.user.manage', $user);
    }

    public function create(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.user.manage', $user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->userResolver->hasPermission('platform.user.manage', $user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->userResolver->hasPermission('platform.user.manage', $user);
    }
}
