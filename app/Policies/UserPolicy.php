<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * M01 - User & Security (Blueprint section 4: "Super Admin - ผู้ใช้ สิทธิ์
 * Master Data Backup Audit"). Managing users/roles/area-scope is Super
 * Admin only - Project Admin gets everything else (approve data, see all
 * areas) but not user management itself.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }

    public function manageAreaAssignments(User $user, User $model): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }
}
