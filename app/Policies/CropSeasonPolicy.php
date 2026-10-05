<?php

namespace App\Policies;

use App\Models\CropSeason;
use App\Models\Role;
use App\Models\User;

/**
 * Crop Season master data (ฤดูผลิต) - used everywhere as a dropdown filter
 * (households.production-cost/economic-impact, F13 farm activities, F08
 * harvest records, etc.) and as the date-range anchor
 * EconomicImpactService matches KilnBatch costs against. Until this
 * screen existed there was no CRUD UI for it at all (seeded only - see
 * DRFIS-Workflow-Menu-Analysis-23Sep.md ข้อ 3/4).
 *
 * STRICTER than TechnologyPolicy's "any STAFF role" rule, same reasoning
 * as EmissionFactorPolicy: a wrong start/end date or an accidentally
 * "active" season here silently skews EVERY household's F02/F06 figures
 * (which season's activities/sales get counted), so create/update is
 * Super Admin/Project Admin only.
 */
class CropSeasonPolicy
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
        return $user->hasAnyRole([Role::SUPER_ADMIN, Role::PROJECT_ADMIN]);
    }

    public function update(User $user): bool
    {
        return $user->hasAnyRole([Role::SUPER_ADMIN, Role::PROJECT_ADMIN]);
    }
}
