<?php

namespace App\Policies;

use App\Models\Innovator;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F07/F09/F11 Sprint 4 (see HouseholdPolicy's doc-comment for the shared
 * 15 ก.ย. authorization-round reasoning). Most innovators are reached via
 * their household's page, but a no-household innovator (Blueprint's
 * household_id-nullable case) has no area to scope against, so those rows
 * are STAFF-only - an Evaluator/Viewer/Farmer/Innovator login never
 * manages the master registry directly, only their own household's
 * linked row (view only, never create/edit - same as
 * InnovatorEvaluationPolicy).
 */
class InnovatorPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, Innovator $innovator): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        if ($innovator->household_id === null) {
            return $user->hasAnyRole(Role::STAFF);
        }

        return $this->areaScope->canAccessHousehold($user, $innovator->household_id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(Role::STAFF);
    }
}
