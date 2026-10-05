<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\InnovatorEvaluation;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F09-lite (see HouseholdPolicy's doc-comment for the shared reasoning -
 * 15 ก.ย. authorization round). Unlike HouseholdBaseline/FarmActivity,
 * OWN_HOUSEHOLD may only VIEW their own evaluation history, never create
 * one - a pass/fail gate that promotes a Farmer to นวัตกรชุมชน has to be
 * recorded by someone evaluating them, not by the account being
 * evaluated grading itself.
 */
class InnovatorEvaluationPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, InnovatorEvaluation $innovatorEvaluation): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $innovatorEvaluation->household_id);
    }

    /**
     * $household is null for the coarse create()-page check and the real
     * Household once store() knows which one was submitted.
     */
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
