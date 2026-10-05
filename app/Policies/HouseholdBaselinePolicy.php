<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\HouseholdBaseline;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F01 (see HouseholdPolicy's doc-comment for the shared reasoning - 15
 * ก.ย. authorization round). Blueprint Appendix D's own Workflow
 * Transition Matrix names "Field Officer, Farmer, Innovator ตามฟอร์ม" as
 * valid owners of a new Draft record, so OWN_HOUSEHOLD is allowed to
 * create/update a baseline for their own household, not just view one -
 * WorkflowService/the controller's `abort_unless(status === draft)` guard
 * still stop anyone editing past Draft.
 */
class HouseholdBaselinePolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, HouseholdBaseline $householdBaseline): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $householdBaseline->household_id);
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

    public function update(User $user, HouseholdBaseline $householdBaseline): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $householdBaseline->household_id);
    }
}
