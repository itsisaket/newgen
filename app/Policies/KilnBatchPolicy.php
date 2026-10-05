<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\KilnBatch;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F03 - household-scoped, same shape as HouseholdBaselinePolicy/
 * FarmActivityPolicy. OWN_HOUSEHOLD may create (Blueprint's role table
 * explicitly lists "Kiln Log" as an Innovator right, and a Farmer running
 * their own kiln is the common case this whole module exists for).
 */
class KilnBatchPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, KilnBatch $kilnBatch): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $kilnBatch->household_id);
    }

    /**
     * $household is null for the coarse create()-page check and the real
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
}
