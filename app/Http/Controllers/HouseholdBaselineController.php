<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseholdBaselineRequest;
use App\Models\CropSeason;
use App\Models\Household;
use App\Models\HouseholdBaseline;
use App\Models\Material;
use App\Services\AreaScopeService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F01 - Household Baseline (Blueprint section 6). CRUD is only allowed
 * while a record is still `draft`; once submitted, changes go through
 * WorkflowService, never a plain update() (Blueprint 16.1).
 *
 * Authorization (15 ก.ย. round): see HouseholdBaselinePolicy.
 */
class HouseholdBaselineController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', HouseholdBaseline::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $baselines = HouseholdBaseline::with(['household', 'cropSeason', 'recordedBy'])
            ->whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->latest()
            ->paginate(20);

        return view('household-baselines.index', compact('baselines'));
    }

    public function create(Request $request, AreaScopeService $areaScope)
    {
        $this->authorize('create', HouseholdBaseline::class);

        $householdIds = $areaScope->householdIdsFor($request->user());

        $households = Household::query()
            ->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))
            ->orderBy('household_code')
            ->get(['id', 'household_code', 'head_name']);
        $cropSeasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        // Pre-select the household when arriving from the registration
        // chain's "ไปบันทึก F01 Baseline" link (plots.registration-complete).
        $selectedHouseholdId = $request->integer('household_id') ?: null;

        return view('household-baselines.create', compact('households', 'cropSeasons', 'selectedHouseholdId'));
    }

    public function store(StoreHouseholdBaselineRequest $request)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [HouseholdBaseline::class, $household]);

        $baseline = HouseholdBaseline::create($request->validated() + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('household-baselines.show', $baseline)
            ->with('status', 'บันทึกข้อมูล Baseline เรียบร้อย (สถานะ: ร่าง)');
    }

    public function show(HouseholdBaseline $householdBaseline)
    {
        $this->authorize('view', $householdBaseline);

        $householdBaseline->load(['household', 'cropSeason', 'recordedBy', 'revisions', 'inputs.material']);

        return view('household-baselines.show', [
            'baseline' => $householdBaseline,
            'fertilizerMaterials' => Material::where('category', 'fertilizer')->orderBy('name')->get(['id', 'name', 'unit']),
            'chemicalMaterials' => Material::where('category', 'chemical')->orderBy('name')->get(['id', 'name', 'unit']),
        ]);
    }

    public function edit(HouseholdBaseline $householdBaseline, AreaScopeService $areaScope)
    {
        $this->authorize('update', $householdBaseline);
        abort_unless($householdBaseline->status === 'draft', 403, 'แก้ไขได้เฉพาะข้อมูลสถานะร่างเท่านั้น');

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $households = Household::query()
            ->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))
            ->orderBy('household_code')
            ->get(['id', 'household_code', 'head_name']);
        $cropSeasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        return view('household-baselines.edit', [
            'baseline' => $householdBaseline,
            'households' => $households,
            'cropSeasons' => $cropSeasons,
        ]);
    }

    public function update(StoreHouseholdBaselineRequest $request, HouseholdBaseline $householdBaseline, AreaScopeService $areaScope)
    {
        $this->authorize('update', $householdBaseline);
        abort_unless($householdBaseline->status === 'draft', 403, 'แก้ไขได้เฉพาะข้อมูลสถานะร่างเท่านั้น');

        // Same reasoning as FarmController::update() - household_id is
        // re-pointable on this form, so check the *target* household too.
        if (! $areaScope->canAccessHousehold($request->user(), (int) $request->input('household_id'))) {
            abort(403, 'ไม่มีสิทธิ์ย้ายข้อมูลไปยังครัวเรือนนี้');
        }

        $householdBaseline->update($request->validated());

        return redirect()
            ->route('household-baselines.show', $householdBaseline)
            ->with('status', 'บันทึกการแก้ไขเรียบร้อย');
    }

    public function submit(HouseholdBaseline $householdBaseline, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $householdBaseline);

        $workflow->submit($householdBaseline, $request->user());

        return back()->with('status', 'ส่งข้อมูลเพื่อตรวจสอบแล้ว');
    }

    public function verify(HouseholdBaseline $householdBaseline, Request $request, WorkflowService $workflow)
    {
        // WorkflowService already checks the Researcher/District Officer/
        // Super Admin role; this adds the AreaScopeService check it
        // doesn't do itself, so a District Officer scoped to one district
        // can't verify a baseline from a household in a different one.
        $this->authorize('view', $householdBaseline);

        $workflow->verify($householdBaseline, $request->user());

        return back()->with('status', 'ตรวจสอบข้อมูลแล้ว รอการอนุมัติ');
    }

    public function approve(HouseholdBaseline $householdBaseline, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $householdBaseline);

        $workflow->approve($householdBaseline, $request->user());

        return back()->with('status', 'อนุมัติข้อมูลแล้ว (ล็อกการแก้ไข)');
    }

    public function reject(HouseholdBaseline $householdBaseline, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $householdBaseline);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $workflow->reject($householdBaseline, $request->user(), $request->string('rejection_reason'));

        return back()->with('status', 'ตีกลับข้อมูลแล้ว');
    }
}
