<?php

namespace App\Policies;

use App\Models\Innovator;
use App\Models\KnowledgeTransfer;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F11 Knowledge Transfer (Sprint 5 round - see claude project doc
 * DRFIS-Sprint5-Design-16Sep.md ส่วน A for the full Role/Permission
 * Matrix reasoning). Staff-only create by default (Field Officer/
 * Researcher/District Officer/Project Admin/Super Admin) - a research
 * record of a training/transfer event, not a Farmer/Innovator self-entry
 * form, per the design doc's assumption #2 (adjustable later if the
 * project wants Innovators to log their own transfers).
 *
 * Read scoping mirrors InnovatorPolicy exactly: a no-household innovator
 * has no area to scope against, so its transfers are STAFF-only; a
 * household-linked innovator's transfers follow that household's area
 * scope (so the household's own Farmer/Innovator login can view, but
 * never create, its own transfer history).
 */
class KnowledgeTransferPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, KnowledgeTransfer $knowledgeTransfer): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        $innovator = $knowledgeTransfer->innovator;

        if ($innovator->household_id === null) {
            return $user->hasAnyRole(Role::STAFF);
        }

        return $this->areaScope->canAccessHousehold($user, $innovator->household_id);
    }

    /**
     * $innovator is null for the coarse create()-page check and the real
     * Innovator once store() knows which one was submitted.
     */
    public function create(User $user, ?Innovator $innovator = null): bool
    {
        if (! $user->hasAnyRole(Role::STAFF)) {
            return false;
        }

        if ($innovator === null || $innovator->household_id === null) {
            return true;
        }

        return $this->areaScope->canAccessHousehold($user, $innovator->household_id);
    }
}
