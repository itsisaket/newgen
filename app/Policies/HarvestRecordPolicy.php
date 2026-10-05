<?php

namespace App\Policies;

use App\Models\HarvestRecord;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F14/F08-lite - see FarmActivityPolicy's doc-comment for the shared
 * reasoning (15 ก.ย. authorization round, applied to this new module from
 * day one so it never opens the same gap Household/Farm/F02 briefly did).
 */
class HarvestRecordPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, HarvestRecord $harvestRecord): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $harvestRecord->plot->farm->household_id);
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
