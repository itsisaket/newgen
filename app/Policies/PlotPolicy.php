<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * See HouseholdPolicy's doc-comment for the shared reasoning (15 ก.ย.
 * authorization round). create() takes the parent Farm (a Plot always
 * comes from a known farm_id, per the Auto-chain flow and the plots
 * create/edit forms) since a not-yet-created Plot has no farm to resolve
 * a household through yet.
 */
class PlotPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, Plot $plot): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $plot->farm->household_id);
    }

    /**
     * $farm is null for the coarse create()-page check (before a specific
     * farm has been chosen on the form) and the real Farm once store()
     * knows which one was submitted.
     */
    public function create(User $user, ?Farm $farm = null): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        if ($farm === null) {
            return true;
        }

        return $this->areaScope->canAccessHousehold($user, $farm->household_id);
    }

    public function update(User $user, Plot $plot): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $plot->farm->household_id);
    }
}
