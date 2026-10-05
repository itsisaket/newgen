<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInnovatorEvaluationRequest;
use App\Models\Household;
use App\Models\Innovator;
use App\Models\InnovatorEvaluation;
use App\Models\Role;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F09-lite - Innovator Evaluation. See the migration's doc-comment
 * (2026_09_15_000012_create_innovator_evaluations_table.php) for why this
 * is a simplified stand-in for the Blueprint's full F07/F09 modules.
 *
 * No Draft/Submitted/Verified/Approved workflow (unlike F01/F13) - a
 * result is recorded directly by whoever is logged in and evaluating.
 *
 * Authorization (15 ก.ย. round): see InnovatorEvaluationPolicy - notably,
 * a Farmer/Innovator can view their own evaluation history but never
 * create one (they can't evaluate themselves).
 */
class InnovatorEvaluationController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', InnovatorEvaluation::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $evaluations = InnovatorEvaluation::with(['household', 'evaluator'])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->latest('evaluation_date')
            ->paginate(20);

        return view('innovator-evaluations.index', compact('evaluations'));
    }

    public function create(Request $request, AreaScopeService $areaScope)
    {
        $this->authorize('create', InnovatorEvaluation::class);

        $householdIds = $areaScope->householdIdsFor($request->user());

        return view('innovator-evaluations.create', [
            'households' => Household::query()
                ->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))
                ->orderBy('head_name')
                ->get(['id', 'head_name', 'household_code']),
            // Pre-select when arriving from a household's "+ เพิ่มการประเมิน
            // นวัตกรชุมชน" shortcut (households/show.blade.php).
            'selectedHouseholdId' => $request->integer('household_id') ?: null,
        ]);
    }

    public function store(StoreInnovatorEvaluationRequest $request)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [InnovatorEvaluation::class, $household]);

        $data = $request->validated();
        $data['evaluated_by'] = $request->user()->id;

        $evaluation = InnovatorEvaluation::create($data);

        $statusMessage = 'บันทึกผลการประเมินเรียบร้อยแล้ว';

        if ($evaluation->result === 'pass') {
            $household = $evaluation->household()->first();

            // F07/F09/F11 Sprint 4 - every household promoted this way
            // gets (or refreshes) its real `innovators` registry row, the
            // one competency_assessments/alp reporting and knowledge
            // transfers key off (see the innovators migration's
            // doc-comment) - independent of whether it also has a Farmer
            // login account, so a household without one (e.g. seeded
            // before FarmerAccountProvisioner existed) still gets counted
            // as an innovator, it just can't have its role upgraded below.
            if ($household) {
                Innovator::updateOrCreate(
                    ['household_id' => $household->id],
                    [
                        'innovator_evaluation_id' => $evaluation->id,
                        'name' => $household->head_name,
                        'phone' => $household->phone,
                        'registered_at' => $evaluation->evaluation_date,
                    ]
                );
            }

            if ($household?->user) {
                // Upgrade, not replace: assignRole() adds Innovator on top
                // of the household's existing Farmer role (Blueprint 4 -
                // an Innovator keeps doing everything a Farmer does, plus
                // Kiln Log/Product Use/Transfer/Evidence) - never
                // syncRoles(), which would strip Farmer off. role_id (the
                // quick-reference primary-role column on User) is bumped
                // to Innovator too, so lists/badges reading that column
                // show the more specific status.
                $household->user->assignRole(Role::INNOVATOR);

                $innovatorRole = Role::where('name', Role::INNOVATOR)->first();
                if ($innovatorRole) {
                    $household->user->forceFill(['role_id' => $innovatorRole->id])->save();
                }

                $statusMessage = 'บันทึกผลการประเมินเรียบร้อยแล้ว — เลื่อนสถานะเป็นนวัตกรชุมชนแล้ว';
            } else {
                $statusMessage = 'บันทึกผลการประเมินเรียบร้อยแล้ว (ผ่าน) แต่ครัวเรือนนี้ยังไม่มีบัญชีเกษตรกรให้เลื่อนสถานะ';
            }
        }

        return redirect()
            ->route('innovator-evaluations.show', $evaluation)
            ->with('status', $statusMessage);
    }

    public function show(InnovatorEvaluation $innovatorEvaluation)
    {
        $this->authorize('view', $innovatorEvaluation);

        $innovatorEvaluation->load(['household.user', 'evaluator']);

        return view('innovator-evaluations.show', ['evaluation' => $innovatorEvaluation]);
    }
}
