<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * F08 full - buyers master data, same shape as TechnologyPolicy - not
 * area-scoped (a buyer isn't owned by any one household/area), staff-only
 * create.
 */
class BuyerPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(Role::STAFF);
    }
}
