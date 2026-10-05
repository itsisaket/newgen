<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Change this password immediately after first login in any real
// deployment - this seeder is for local development only.
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', Role::SUPER_ADMIN)->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@drfis.local'],
            [
                'name' => 'DRFIS Super Admin',
                'password' => Hash::make('password'),
                'role_id' => $role?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($role) {
            $admin->assignRole($role);
        }
    }
}
