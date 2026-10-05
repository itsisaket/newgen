<?php

namespace App\Policies;

use App\Models\DurianPhenologyRecord;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F14-lite phenology log - same shape as HarvestRecordPolicy/
 * FarmActivityPolicy. No update()/destroy - an observation, once logged,
 * is a historical record (same convention as F13's "no destroy").
 */
class DurianPhenologyRecordPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, DurianPhenologyRecord $record): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $record->plot->farm->household_id);
    }

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
