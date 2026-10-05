<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetencyAssessmentRequest;
use App\Models\CompetencyAssessment;
use App\Models\CompetencyIndicator;
use App\Models\Innovator;
use App\Models\Role;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * F09 - Innovator Competency (Blueprint 12.2). Innovator-locked create
 * (query param, like every other locked-parent module in this codebase) -
 * reachable from innovators/show.blade.php, either by staff (observer) or
 * by the innovator's own Farmer/Innovator login (self) - see
 * CompetencyAssessmentPolicy's doc-comment for the self/observer split.
 */
class CompetencyAssessmentController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', CompetencyAssessment::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $assessments = CompetencyAssessment::with(['innovator.household'])
            ->when(
                $householdIds !== null,
                fn ($q) => $q->whereHas('innovator', fn ($i) => $i->whereIn('household_id', $householdIds))
            )
            ->latest('assessment_date')
            ->paginate(20);

        return view('competency-assessments.index', compact('assessments'));
    }

    public function create(Request $request)
    {
        $innovator = Innovator::find($request->integer('innovator_id'));

        if (! $innovator) {
            return redirect()
                ->route('innovators.index')
                ->with('status', 'กรุณาเปิดหน้านวัตกรที่ต้องการก่อน แล้วกดปุ่ม "+ เพิ่มการประเมินสมรรถนะ" จากหน้านั้น');
        }

        $this->authorize('create', [CompetencyAssessment::class, $innovator]);

        $isSelfOnly = auth()->user()->hasAnyRole(Role::OWN_HOUSEHOLD);

        return view('competency-assessments.create', [
            'innovator' => $innovator,
            'isSelfOnly' => $isSelfOnly,
            'indicatorsByDimension' => CompetencyIndicator::where('is_active', true)
                ->orderBy('dimension')
                ->orderBy('name')
                ->get()
                ->groupBy('dimension'),
        ]);
    }

    public function store(StoreCompetencyAssessmentRequest $request)
    {
        $innovator = Innovator::findOrFail($request->input('innovator_id'));
        $this->authorize('create', [CompetencyAssessment::class, $innovator]);

        $data = $request->validated();
        $scores = $data['scores'];
        unset($data['scores']);

        // A Farmer/Innovator login can only ever record a self-assessment
        // of themselves - never trust posted assessor_type/assessor_id for
        // this, even though the FormRequest already validates their shape,
        // because the policy check above only confirmed WHICH innovator
        // they may touch, not that the assessor fields describe them.
        if ($request->user()->hasAnyRole(Role::OWN_HOUSEHOLD)) {
            $data['assessor_type'] = 'self';
            $data['assessor_id'] = $request->user()->id;
        }

        $data['recorded_by'] = $request->user()->id;

        $assessment = DB::transaction(function () use ($data, $scores) {
            $assessment = CompetencyAssessment::create($data);

            foreach ($scores as $indicatorId => $score) {
                $assessment->scores()->create([
                    'competency_indicator_id' => $indicatorId,
                    'score' => $score,
                ]);
            }

            return $assessment;
        });

        return redirect()->route('competency-assessments.show', $assessment)->with('status', 'บันทึกผลการประเมินสมรรถนะเรียบร้อยแล้ว');
    }

    public function show(CompetencyAssessment $competencyAssessment)
    {
        $this->authorize('view', $competencyAssessment);

        $competencyAssessment->load(['innovator.household', 'assessor', 'recordedBy', 'scores.indicator']);

        return view('competency-assessments.show', ['assessment' => $competencyAssessment]);
    }
}
