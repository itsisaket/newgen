<?php

namespace App\Policies;

use App\Models\EmissionFactor;
use App\Models\Role;
use App\Models\User;

/**
 * F15 master data. STRICTER than TechnologyPolicy's "any STAFF role" rule
 * on purpose: a wrong factor_value here silently skews EVERY household's
 * CO2e figure calculated against it (unlike a technology type name, which
 * is cosmetic), so create is Super Admin/Project Admin only - matches the
 * Sprint 5 design doc's "กลุ่ม 1 Master/Setup data" rule (M = Super
 * Admin/Project Admin only), applied here more strictly than the
 * already-shipped Technology/Product master data was.
 */
class EmissionFactorPolicy
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
        return $user->hasAnyRole([Role::SUPER_ADMIN, Role::PROJECT_ADMIN]);
    }
}
