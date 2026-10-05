<?php

namespace App\Policies;

use App\Models\DemonstrationComparisonGroup;
use App\Models\Role;
use App\Models\User;

/**
 * F05 - the experiment/comparison group itself has no household_id of its
 * own (a single experiment can span plots across many households/areas -
 * see the migration's doc-comment), so unlike DemonstrationPlotPolicy
 * there is no AreaScopeService check here - staff-only, same coarse rule
 * as the design doc's "3b" group (Sprint5-Design-16Sep.md ส่วน A).
 * update() gates the complete() status transition (active -> completed).
 */
class DemonstrationComparisonGroupPolicy
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

    public function update(User $user, DemonstrationComparisonGroup $demonstrationComparisonGroup): bool
    {
        return $user->hasAnyRole(Role::STAFF);
    }
}
