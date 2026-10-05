<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * See HouseholdPolicy's doc-comment for the shared reasoning (15 ก.ย.
 * authorization round). create() takes the parent Household explicitly
 * (Blueprint's own Auto-chain flow always creates a Farm from a known
 * household_id) since a not-yet-created Farm has no household_id of its
 * own to check yet.
 */
class FarmPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, Farm $farm): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $farm->household_id);
    }

    /**
     * $household is null for the coarse create()-page check (before a
     * specific household has been chosen on the form) and the real
     * Household once store() knows which one was submitted.
     */
    public function create(User $user, ?Household $household = null): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        if ($household === null) {
            return true;
        }

        return $this->areaScope->canAccessHousehold($user, $household->id);
    }

    public function update(User $user, Farm $farm): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $farm->household_id);
    }
}
