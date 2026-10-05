<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\Role;
use App\Models\TechnologyAssignment;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F12 allocation history - household-scoped like HouseholdPolicy. Unlike
 * KilnBatchPolicy/ProductUsagePolicy, create() is STAFF-only: deciding
 * which household gets which physical kiln/asset is a project decision,
 * not something a Farmer/Innovator initiates themselves (Blueprint's role
 * table lists "Kiln Log, Product Use, Transfer" as Innovator's rights,
 * not asset allocation).
 */
class TechnologyAssignmentPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, TechnologyAssignment $assignment): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $assignment->household_id);
    }

    public function create(User $user, ?Household $household = null): bool
    {
        if (! $user->hasAnyRole(Role::STAFF)) {
            return false;
        }

        if ($household === null) {
            return true;
        }

        return $this->areaScope->canAccessHousehold($user, $household->id);
    }
}
