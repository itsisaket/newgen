<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * F04/Inventory master data (product TYPES) - same shape as
 * TechnologyPolicy, same simplification caveat.
 */
class ProductPolicy
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
