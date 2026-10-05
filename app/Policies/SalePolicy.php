<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F08 full - household-scoped, same shape as KilnBatchPolicy. OWN_HOUSEHOLD
 * may create (a household selling its own durian/bioproduct is the normal
 * case, same reasoning as Kiln Log).
 */
class SalePolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, Sale $sale): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $sale->seller_household_id);
    }

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
