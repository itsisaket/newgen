<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use App\Models\Role;
use App\Models\Tambon;
use App\Models\User;
use App\Models\UserAreaAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One example user per role (Blueprint section 4 - all 9 roles), so M01
 * (User & Security) and the Area Scope feature (Blueprint 4.1) have real
 * examples to look at instead of only the single Super Admin from
 * AdminUserSeeder. Two Field Officers are seeded specifically so the
 * "one user, multiple area assignments in different places" scenario
 * that Area Scope is designed for is actually visible in the UI.
 *
 * Change these passwords immediately after first login in any real
 * deployment - like AdminUserSeeder, this is for local development only.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('email', 'admin@drfis.local')->first();

        $users = [
            ['email' => 'project.admin@drfis.local', 'name' => 'ดร.สมศักดิ์ บริหารโครงการ', 'role' => Role::PROJECT_ADMIN],
            ['email' => 'researcher@drfis.local', 'name' => 'ดร.วิภา งานวิจัย', 'role' => Role::RESEARCHER],
            ['email' => 'field.officer1@drfis.local', 'name' => 'สมศรี ลงพื้นที่', 'role' => Role::FIELD_OFFICER],
            ['email' => 'field.officer2@drfis.local', 'name' => 'สมหมาย ลงพื้นที่', 'role' => Role::FIELD_OFFICER],
            ['email' => 'district.officer@drfis.local', 'name' => 'ประสาน พื้นที่ศรีสะเกษ', 'role' => Role::DISTRICT_OFFICER],
            ['email' => 'innovator@drfis.local', 'name' => 'นวัตกร ชุมชนดี', 'role' => Role::INNOVATOR],
            ['email' => 'farmer@drfis.local', 'name' => 'เกษตรกร ทดลองระบบ', 'role' => Role::FARMER],
            ['email' => 'evaluator@drfis.local', 'name' => 'ผู้ประเมิน ภายนอก', 'role' => Role::EVALUATOR],
            ['email' => 'viewer@drfis.local', 'name' => 'ผู้ชม รายงาน', 'role' => Role::VIEWER],
        ];

        $created = [];

        foreach ($users as $row) {
            $role = Role::where('name', $row['role'])->first();

            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $role?->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if ($role) {
                $user->assignRole($role);
            }

            $created[$row['email']] = $user;
        }

        if (! $superAdmin) {
            return;
        }

        // AreaScopeService::hasFullAccess() already treats Super Admin as
        // full-access by role, regardless of this row - but seed it too
        // for data completeness/consistency with how Project Admin and
        // Researcher get their full access (a real SCOPE_ALL row), and so
        // `php artisan db:seed` leaves no doubt for anyone inspecting the
        // user_area_assignments table directly.
        UserAreaAssignment::firstOrCreate(
            [
                'user_id' => $superAdmin->id,
                'scope_type' => UserAreaAssignment::SCOPE_ALL,
                'scope_id' => null,
            ],
            ['created_by' => $superAdmin->id]
        );

        // Area Scope examples (Blueprint 4.1). Project Admin/Researcher see
        // everything per the role table (section 4); Field Officers and
        // District Officer are scoped to a specific area to demonstrate
        // the actual filtering scenario the feature exists for. Innovator/
        // Farmer/Evaluator/Viewer are left unassigned - the role table
        // doesn't describe them as geographically scoped.
        $sisaket = Province::where('name_th', 'ศรีสะเกษ')->first();

        $bakDong = Tambon::where('name_th', 'บักดอง')
            ->whereHas('district', fn ($q) => $q->where('province_id', $sisaket?->id))
            ->first();

        $phuPhaMok = Tambon::where('name_th', 'ภูผาหมอก')
            ->whereHas('district', fn ($q) => $q->where('province_id', $sisaket?->id))
            ->first();

        $siRattana = District::where('name_th', 'ศรีรัตนะ')
            ->where('province_id', $sisaket?->id)
            ->first();

        $assignments = [
            ['email' => 'project.admin@drfis.local', 'scope_type' => UserAreaAssignment::SCOPE_ALL, 'scope_id' => null],
            ['email' => 'researcher@drfis.local', 'scope_type' => UserAreaAssignment::SCOPE_ALL, 'scope_id' => null],
            ['email' => 'field.officer1@drfis.local', 'scope_type' => UserAreaAssignment::SCOPE_TAMBON, 'scope_id' => $bakDong?->id],
            ['email' => 'field.officer2@drfis.local', 'scope_type' => UserAreaAssignment::SCOPE_TAMBON, 'scope_id' => $phuPhaMok?->id],
            ['email' => 'district.officer@drfis.local', 'scope_type' => UserAreaAssignment::SCOPE_DISTRICT, 'scope_id' => $siRattana?->id],
        ];

        foreach ($assignments as $row) {
            $user = $created[$row['email']] ?? null;

            if (! $user || ($row['scope_id'] === null && $row['scope_type'] !== UserAreaAssignment::SCOPE_ALL)) {
                continue;
            }

            UserAreaAssignment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'scope_type' => $row['scope_type'],
                    'scope_id' => $row['scope_id'],
                ],
                ['created_by' => $superAdmin->id]
            );
        }
    }
}
