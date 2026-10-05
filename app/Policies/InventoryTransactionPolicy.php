<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\InventoryTransaction;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * Inventory ledger (Blueprint 8.3) - household-scoped view, same shape as
 * KilnBatchPolicy. "Transfer" is also an explicit Innovator right per
 * Blueprint's role table, so OWN_HOUSEHOLD may record a manual movement
 * (sale/transfer/loss) for their own household - 'adjustment' is
 * restricted further at the request-validation layer (StoreInventoryTransactionRequest)
 * and, for a balance-decreasing adjustment specifically, inside
 * InventoryLedgerService itself (Project Admin/Super Admin only).
 */
class InventoryTransactionPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, InventoryTransaction $transaction): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $transaction->household_id);
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
