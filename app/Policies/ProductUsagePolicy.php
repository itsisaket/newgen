<?php

namespace App\Policies;

use App\Models\Plot;
use App\Models\ProductUsage;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F04 - plot-scoped like FarmActivityPolicy/HarvestRecordPolicy.
 * OWN_HOUSEHOLD may create ("Product Use" is an explicit Innovator right
 * per Blueprint's role table, same reasoning as KilnBatchPolicy).
 */
class ProductUsagePolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, ProductUsage $productUsage): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $productUsage->plot->farm->household_id);
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
