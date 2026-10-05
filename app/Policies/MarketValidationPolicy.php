<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * F08 full - market_validations has no household/area to scope against
 * (it is the buyer's declared demand, not tied to any one seller - see
 * the migration's doc-comment), so this follows BuyerPolicy/
 * TechnologyPolicy's shape: staff-only create, everyone but READ_ONLY can
 * view.
 */
class MarketValidationPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(Role::STAFF);
    }
}
