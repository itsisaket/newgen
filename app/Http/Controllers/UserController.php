<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\FarmerGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;

/**
 * M01 - User & Security (Blueprint section 4 / Appendix A "ผู้ใช้งานและสิทธิ์").
 * Every action here is gated by UserPolicy (Super Admin only) - see
 * app/Policies/UserPolicy.php. Area-scope assignment (Blueprint 4.1) is
 * handled by the sibling UserAreaAssignmentController and rendered on
 * the edit screen.
 */
class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('primaryRole')->orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        $user->syncRoles([$role]);

        // M01 audit trail (Blueprint section 4 "ทุกการเปลี่ยนแปลงบัญชีผู้ใช้ต้อง
        // มีการบันทึก Audit Log") - never a password/hash, only the fields an
        // admin can see on the users list/edit screens anyway.
        AuditLog::create([
            'user_id' => $request->user()->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'create',
            'old_values' => null,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $role->name,
                'status' => $user->status,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.edit', $user)->with('status', 'สร้างผู้ใช้เรียบร้อยแล้ว');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $user->load(['areaAssignments.createdBy']);

        return view('users.edit', [
            'targetUser' => $user,
            'roles' => Role::orderBy('name')->get(),
            'provinces' => \App\Models\Province::orderBy('name_th')->get(),
            'districts' => \App\Models\District::orderBy('name_th')->get(),
            'tambons' => \App\Models\Tambon::orderBy('name_th')->get(),
            'farmerGroups' => FarmerGroup::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        // 23 ก.ย. round: never let a save leave the system with zero
        // active Super Admin accounts. AdminUserSeeder only ever creates
        // exactly one by default, M01 itself is Super-Admin-only per
        // UserPolicy, and there is no self-service recovery path once
        // nobody holds the role - fixing it afterwards needs direct
        // database access, which this project's own history (see the
        // password-reset request earlier - no php/mysql client reachable
        // from the assistant's side either) shows isn't always readily
        // available. Applies whether editing your own account or someone
        // else's, and to a role change away from Super Admin as much as
        // a status change to inactive - both have the exact same effect.
        $newRole = Role::findOrFail($data['role_id']);
        $wouldStayActiveSuperAdmin = $newRole->name === Role::SUPER_ADMIN && $data['status'] === 'active';

        if (! $wouldStayActiveSuperAdmin && $user->status === 'active' && $user->hasRole(Role::SUPER_ADMIN)) {
            $otherActiveSuperAdminExists = User::where('id', '!=', $user->id)
                ->where('status', 'active')
                ->whereHas('roles', fn ($q) => $q->where('name', Role::SUPER_ADMIN))
                ->exists();

            if (! $otherActiveSuperAdminExists) {
                return back()
                    ->withInput()
                    ->with('status', 'ไม่สามารถบันทึกได้: ต้องมีบัญชี Super Admin ที่ใช้งานอยู่ (active) เหลืออย่างน้อย 1 บัญชีเสมอ เพื่อป้องกันการล็อกทุกคนออกจากระบบ');
            }
        }

        // Snapshot before the change - AuditLog.old_values below - taken
        // via the previously-loaded role relation rather than a second
        // query, since role_id alone doesn't say what changed in plain
        // Thai on the audit trail.
        $oldRole = $user->primaryRole?->name ?? $user->role_id;
        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $oldRole,
            'status' => $user->status,
        ];
        $passwordChanged = ! empty($data['password']);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        if ($passwordChanged) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // Reuse $newRole computed above for the Super Admin safety check
        // instead of re-querying it a second time.
        $user->syncRoles([$newRole]);
        $role = $newRole;

        // M01 audit trail (Blueprint section 4) - same reasoning as
        // store(): never a password/hash, just a `password_changed` flag
        // so the trail still shows a credential reset happened.
        AuditLog::create([
            'user_id' => $request->user()->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'update',
            'old_values' => $before,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $role->name,
                'status' => $user->status,
                'password_changed' => $passwordChanged,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.edit', $user)->with('status', 'บันทึกข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }
}
