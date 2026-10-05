<?php

namespace App\Policies;

use App\Models\AlpAssessment;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F07 - same shape/reasoning as InnovatorEvaluationPolicy: OWN_HOUSEHOLD
 * may view their own ALP history but never assess themselves.
 */
class AlpAssessmentPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, AlpAssessment $alpAssessment): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $alpAssessment->household_id);
    }

    public function create(User $user, ?Household $household = null): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY) || $user->hasAnyRole(Role::OWN_HOUSEHOLD)) {
            return false;
        }

        if ($household === null) {
            return true;
        }

        return $this->areaScope->canAccessHousehold($user, $household->id);
    }
}
