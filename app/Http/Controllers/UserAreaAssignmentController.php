<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserAreaAssignmentRequest;
use App\Models\User;
use App\Models\UserAreaAssignment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Blueprint section 4.1 - lets a Super Admin grant a user one or more
 * geographic/project scopes. A user can hold several rows here (e.g. a
 * District Officer covering multiple tambons in the same district).
 */
class UserAreaAssignmentController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreUserAreaAssignmentRequest $request, User $user)
    {
        $this->authorize('manageAreaAssignments', $user);

        $data = $request->validated();

        $scopeId = match ($data['scope_type']) {
            'province' => $data['province_id'] ?? null,
            'district' => $data['district_id'] ?? null,
            'tambon' => $data['tambon_id'] ?? null,
            'farmer_group' => $data['farmer_group_id'] ?? null,
            default => null,
        };

        $user->areaAssignments()->create([
            'scope_type' => $data['scope_type'],
            'scope_id' => $scopeId,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', 'เพิ่มขอบเขตพื้นที่ให้ผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(User $user, UserAreaAssignment $assignment)
    {
        $this->authorize('manageAreaAssignments', $user);

        abort_unless($assignment->user_id === $user->id, 404);

        $assignment->delete();

        return back()->with('status', 'นำขอบเขตพื้นที่ออกแล้ว');
    }
}
