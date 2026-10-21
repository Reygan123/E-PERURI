<?php

namespace App\Modules\Platform\Policies;

use App\Models\User;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\NumberingFormat;

class NumberingFormatPolicy
{
    public function __construct(
        protected UserResolverInterface $userResolver
    ) {}

    public function viewAny(?User $user = null): bool
    {
        return $this->userResolver->hasPermission('platform.numbering.manage', $user)
            || $this->userResolver->hasRole('super-admin', $user);
    }

    public function view(?User $user, NumberingFormat $format): bool
    {
        return $this->userResolver->hasPermission('platform.numbering.manage', $user)
            || $this->userResolver->hasRole('super-admin', $user);
    }

    public function create(?User $user = null): bool
    {
        return $this->userResolver->hasPermission('platform.numbering.manage', $user)
            || $this->userResolver->hasRole('super-admin', $user);
    }

    public function update(?User $user, NumberingFormat $format): bool
    {
        return $this->userResolver->hasPermission('platform.numbering.manage', $user)
            || $this->userResolver->hasRole('super-admin', $user);
    }

    public function delete(?User $user, NumberingFormat $format): bool
    {
        return $this->userResolver->hasRole('super-admin', $user);
    }
}
