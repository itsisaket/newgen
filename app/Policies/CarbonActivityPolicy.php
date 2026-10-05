<?php

namespace App\Policies;

use App\Models\CarbonActivity;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F15 - Carbon Activity Monitoring (Sprint 5 round - see
 * DRFIS-Sprint5-Design-16Sep.md ส่วน A, "กลุ่ม 3b F05/F11/F15"). Same
 * household-area-scope shape as KilnBatchPolicy, with ONE deliberate
 * difference: create() is STAFF-only, NOT Role::OWN_HOUSEHOLD - F15 is a
 * research/project record of a carbon-reduction activity, not a Farmer's
 * own daily-use form (unlike F03 Kiln Log, which Blueprint's role table
 * explicitly gives to Innovator). A household's own Farmer/Innovator
 * login can still VIEW its own carbon activities (read-only) via
 * AreaScopeService::canAccessHousehold(), same as every other module.
 *
 * Verify/Approve/Reject go through WorkflowService, not a Policy method
 * here (see CarbonActivityController) - same convention as KilnBatch:
 * WorkflowService::verify()/approve() already gate on
 * Researcher/District Officer/Super Admin and Project Admin/Super Admin
 * respectively, matching the design doc's "3b" matrix row exactly.
 */
class CarbonActivityPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, CarbonActivity $carbonActivity): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $carbonActivity->household_id);
    }

    /**
     * $household is null for the coarse create()-page check and the real
     * Household once store() knows which one was submitted.
     */
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
