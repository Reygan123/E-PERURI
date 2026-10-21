<?php

namespace App\Modules\Platform\Policies;

use App\Models\User;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\Position;

class PositionPolicy
{
    public function __construct(
        protected UserResolverInterface $userResolver
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.position.view', $user);
    }

    public function view(User $user, Position $position): bool
    {
        return $this->userResolver->hasPermission('platform.position.view', $user);
    }

    public function create(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.position.manage', $user);
    }

    public function update(User $user, Position $position): bool
    {
        return $this->userResolver->hasPermission('platform.position.manage', $user);
    }

    public function delete(User $user, Position $position): bool
    {
        return $this->userResolver->hasPermission('platform.position.manage', $user);
    }
}
