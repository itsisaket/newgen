<?php

namespace App\Policies;

use App\Models\DemonstrationPlot;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/**
 * F05 - the plot ENROLLMENT is household-scoped through its Plot (same
 * shape as PlotPolicy/FarmActivityPolicy), unlike
 * DemonstrationComparisonGroupPolicy which has no household to scope
 * against. create() is staff-only (not Role::OWN_HOUSEHOLD) - see the
 * design doc's "3b" group reasoning (F05/F11/F15 are research records a
 * project team member enters, not a Farmer/Innovator self-entry form).
 */
class DemonstrationPlotPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, DemonstrationPlot $demonstrationPlot): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $demonstrationPlot->plot->household()?->id);
    }

    /**
     * $plot is null for the coarse create()-page check and the real Plot
     * once store() knows which one was submitted (same pattern as
     * PlotPolicy::create() / FarmActivityPolicy::create()).
     */
    public function create(User $user, ?Plot $plot = null): bool
    {
        if (! $user->hasAnyRole(Role::STAFF)) {
            return false;
        }

        if ($plot === null) {
            return true;
        }

        $householdId = $plot->household()?->id;

        return $householdId !== null && $this->areaScope->canAccessHousehold($user, $householdId);
    }
}
