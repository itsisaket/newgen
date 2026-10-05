<?php

namespace App\Policies;

use App\Models\BranchDisposalRecord;
use App\Models\Plot;
use App\Models\Role;
use App\Models\User;
use App\Services\AreaScopeService;

/** CFP - การจัดการกิ่ง/เศษไม้: รูปแบบเดียวกับ HarvestRecordPolicy (ขอบเขตพื้นที่ผ่าน AreaScopeService) */
class BranchDisposalRecordPolicy
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function viewAny(User $user): bool
    {
        return ! $user->hasAnyRole(Role::READ_ONLY);
    }

    public function view(User $user, BranchDisposalRecord $record): bool
    {
        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return false;
        }

        return $this->areaScope->canAccessHousehold($user, $record->plot->farm->household_id);
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
