<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

// Blueprint section 4 / Appendix E: module/action permission matrix for all
// nine roles. Policies still add record-level and geographic checks; these
// permissions describe the coarse capability assigned to each role.
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Role::ALL as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $modules = [
            'households', 'farms', 'plots', 'baselines', 'activities', 'harvests',
            'technologies', 'inventory', 'market', 'impact', 'alp', 'competency',
            'transfers', 'carbon', 'evidence', 'reports', 'users', 'audit',
        ];
        $actions = ['view', 'create', 'update', 'verify', 'approve', 'export'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        $all = Permission::all();
        Role::findByName(Role::SUPER_ADMIN)->syncPermissions($all);
        Role::findByName(Role::PROJECT_ADMIN)->syncPermissions($all->reject(fn ($p) => str_starts_with($p->name, 'users.')));
        Role::findByName(Role::RESEARCHER)->syncPermissions($all->filter(fn ($p) => ! str_starts_with($p->name, 'users.') && ! str_ends_with($p->name, '.approve')));
        Role::findByName(Role::FIELD_OFFICER)->syncPermissions($all->filter(fn ($p) => in_array(explode('.', $p->name)[1], ['view', 'create', 'update'], true)));
        Role::findByName(Role::DISTRICT_OFFICER)->syncPermissions($all->filter(fn ($p) => in_array(explode('.', $p->name)[1], ['view', 'verify', 'export'], true)));
        Role::findByName(Role::INNOVATOR)->syncPermissions($all->filter(fn ($p) => in_array(explode('.', $p->name)[0], ['activities', 'harvests', 'inventory', 'transfers', 'evidence', 'reports'], true) && in_array(explode('.', $p->name)[1], ['view', 'create', 'update'], true)));
        Role::findByName(Role::FARMER)->syncPermissions($all->filter(fn ($p) => in_array(explode('.', $p->name)[0], ['households', 'farms', 'plots', 'activities', 'harvests', 'evidence', 'reports'], true) && in_array(explode('.', $p->name)[1], ['view', 'create', 'update'], true)));
        Role::findByName(Role::EVALUATOR)->syncPermissions($all->filter(fn ($p) => in_array($p->name, ['reports.view', 'reports.export', 'evidence.view'], true)));
        Role::findByName(Role::VIEWER)->syncPermissions($all->filter(fn ($p) => $p->name === 'reports.view'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
