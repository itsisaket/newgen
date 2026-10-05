<?php

namespace App\Policies;

use App\Models\FarmActivity;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F13 (see HouseholdPolicy's doc-comment for the shared reasoning - 15
 * ก.ย. authorization round). Like HouseholdBaselinePolicy, OWN_HOUSEHOLD
 * may create - Blueprint Appendix D explicitly lists Farmer/Innovator as
 * valid Draft owners for the field forms. There is no update() here
 * because FarmActivityController itself has no edit/update action (a
 * logged activity is immutable outside the submit/verify/approve/reject
 * workflow, same as F13's "no destroy" convention) - view() also gates
 * who may attach Evidence to an activity (see EvidenceController).
 */
class FarmActivityPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, FarmActivity $farmActivity): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $farmActivity->plot->farm->household_id);
    }

    /**
     * $plot is null for the coarse create()-page check and the real Plot
     * once store() knows which one was submitted.
     */
    public function create(User $user, ?Plot $plot = null): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        if ($plot === null) {
            return true;
        }

        return $this->areaScope->canAccessHousehold($user, $plot->farm->household_id);
    }
}
