<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * 15 ก.ย. authorization round (see DRFIS-Progress-Review-15Sep.md in the
 * project). Before this, `households` had zero Policy - only the `auth`
 * middleware, so any logged-in user (including a Farmer login, one of
 * which now exists per household) could see/edit every household in the
 * system. This restricts it to: full visibility for staff within their
 * AreaScopeService scope, a Farmer/Innovator to exactly their own linked
 * household, and no access at all for Evaluator/Viewer (Blueprint section
 * 4 scopes them to Dashboard/Evidence/Reports, not this registry screen).
 */
class HouseholdPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, Household $household): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $household->id);
    }

    /**
     * Registering a brand-new household is staff work (Field Officer and
     * above, within their area) - a Farmer/Innovator already has exactly
     * the one household their login is tied to and doesn't register a
     * second one; Evaluator/Viewer are read-only.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(Role::STAFF);
    }

    public function update(User $user, Household $household): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $household->id);
    }
}
