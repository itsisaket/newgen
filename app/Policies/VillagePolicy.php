<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;

class VillagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(Role::STAFF);
    }

    public function view(User $user, Village $village): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([Role::SUPER_ADMIN, Role::PROJECT_ADMIN, Role::FIELD_OFFICER, Role::DISTRICT_OFFICER]);
    }

    public function update(User $user, Village $village): bool
    {
        return $this->create($user);
    }
}
