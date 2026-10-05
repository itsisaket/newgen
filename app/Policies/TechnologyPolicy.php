<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Technology;
use App\Models\User;

/**
 * F12 master data (technology TYPES, not physical assets - see
 * TechnologyAssetPolicy). Not household-scoped - a technology type
 * (e.g. "เตาผลิตถ่านชีวภาพ") isn't owned by any one area. Staff-only, same
 * READ_ONLY exclusion as every other module from the 15 ก.ย. round.
 *
 * Simplification: every STAFF role (Field/District Officer included) can
 * define new technology types here, not just Project Admin/Super Admin -
 * this is a coarse rule, same caveat as Role::STAFF/OWN_HOUSEHOLD/
 * READ_ONLY elsewhere (Blueprint Appendix E's full per-action Permission
 * Matrix is still pending sign-off).
 */
class TechnologyPolicy
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
