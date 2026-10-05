<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * F12 physical equipment registry - see TechnologyPolicy's doc-comment for
 * the shared reasoning. Not household-scoped either (an unassigned asset
 * has no household yet) - allocation to a household is
 * TechnologyAssignmentPolicy's job, not this one's.
 */
class TechnologyAssetPolicy
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
