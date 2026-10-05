<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlpAssessmentRequest;
use App\Models\AlpAssessment;
use App\Models\Household;
use App\Models\Technology;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F07 - ALP Assessment (Blueprint 12.1). Household-locked create, same
 * convention as InnovatorEvaluationController/PhenologyRecordController -
 * only reachable from a household's page (or its Innovator registry
 * page), never a free household dropdown.
 */
class AlpAssessmentController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', AlpAssessment::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $assessments = AlpAssessment::with(['household', 'technology'])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->latest('assessment_date')
            ->paginate(20);

        return view('alp-assessments.index', compact('assessments'));
    }

    public function create(Request $request)
    {
        $household = Household::find($request->integer('household_id'));

        if (! $household) {
            return redirect()
                ->route('households.index')
                ->with('status', 'กรุณาเปิดหน้าครัวเรือน/นวัตกรที่ต้องการก่อน แล้วกดปุ่ม "+ เพิ่มการประเมิน ALP" จากหน้านั้น');
        }

        $this->authorize('create', [AlpAssessment::class, $household]);

        return view('alp-assessments.create', [
            'household' => $household,
            'technologies' => Technology::orderBy('name')->get(),
        ]);
    }

    public function store(StoreAlpAssessmentRequest $request)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [AlpAssessment::class, $household]);

        $data = $request->validated();
        $data['assessor_id'] = $data['assessor_id'] ?? $request->user()->id;
        $data['recorded_by'] = $request->user()->id;

        $assessment = AlpAssessment::create($data);

        return redirect()->route('alp-assessments.show', $assessment)->with('status', 'บันทึกผลการประเมิน ALP เรียบร้อยแล้ว');
    }

    public function show(AlpAssessment $alpAssessment)
    {
        $this->authorize('view', $alpAssessment);

        $alpAssessment->load(['household', 'technology', 'assessor', 'recordedBy']);

        return view('alp-assessments.show', ['assessment' => $alpAssessment]);
    }
}
