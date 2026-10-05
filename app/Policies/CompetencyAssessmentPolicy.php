<?php

namespace App\Policies;

use App\Models\CompetencyAssessment;
use App\Models\Innovator;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F09 - unlike InnovatorEvaluationPolicy/AlpAssessmentPolicy, OWN_HOUSEHOLD
 * (a Farmer/Innovator login) MAY create a competency_assessments row, but
 * only a SELF-assessment of their OWN linked innovator record (Blueprint
 * 12.2: "ใช้ทั้ง Self-assessment กับ Observer assessment ตามความเหมาะสม") - an
 * Innovator can score themselves, they just can't record an "observer"
 * assessment or score anyone else. CompetencyAssessmentController::store()
 * enforces the self/own-innovator part server-side (never trusts posted
 * assessor_type/assessor_id/innovator_id for that distinction).
 */
class CompetencyAssessmentPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, CompetencyAssessment $competencyAssessment): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->canAccessInnovator($user, $competencyAssessment->innovator);
    }

    /**
     * $innovator is null for the coarse create()-page check and the real
     * Innovator once store() knows which one was submitted.
     */
    public function create(User $user, ?Innovator $innovator = null): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        if ($innovator === null) {
            return true;
        }

        if ($user->hasAnyRole(Role::OWN_HOUSEHOLD)) {
            // Self-assessment only, of their own linked innovator record.
            return $innovator->household_id !== null && $innovator->household_id === $user->household?->id;
        }

        return $this->canAccessInnovator($user, $innovator);
    }

    private function canAccessInnovator(User $user, ?Innovator $innovator): bool
    {
        if (! $innovator) {
            return false;
        }

        if ($innovator->household_id === null) {
            return $user->hasAnyRole(Role::STAFF);
        }

        return $this->areaScope->canAccessHousehold($user, $innovator->household_id);
    }
}
