<?php

namespace App\Http\Controllers;

use App\Exceptions\CarbonCalculationException;
use App\Http\Requests\StoreCarbonActivityRequest;
use App\Models\CarbonActivity;
use App\Models\Household;
use App\Services\AreaScopeService;
use App\Services\CarbonCalculationService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * F15 - Carbon Activity Monitoring (Blueprint Appendix C.6). Household-
 * locked create like KilnBatchController (households/show -> "+ บันทึก
 * กิจกรรมคาร์บอน"). CO2e is only calculated on Approve, not at creation -
 * see CarbonCalculationService's doc-comment for why (mirrors
 * KilnBatchController::approve() crediting inventory only on Approve).
 */
class CarbonActivityController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', CarbonActivity::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $activities = CarbonActivity::with(['household', 'calculation'])
            ->whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->latest('activity_date')
            ->paginate(20);

        return view('carbon-activities.index', compact('activities'));
    }

    public function create(Request $request)
    {
        $household = Household::find($request->integer('household_id'));

        if (! $household) {
            return redirect()
                ->route('households.index')
                ->with('status', 'กรุณาเปิดหน้าครัวเรือนที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกกิจกรรมคาร์บอน" จากหน้านั้น');
        }

        $this->authorize('create', [CarbonActivity::class, $household]);

        return view('carbon-activities.create', ['household' => $household]);
    }

    public function store(StoreCarbonActivityRequest $request)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [CarbonActivity::class, $household]);

        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        $activity = CarbonActivity::create($data);

        return redirect()->route('carbon-activities.show', $activity)
            ->with('status', 'บันทึกกิจกรรมคาร์บอนเรียบร้อย (สถานะ: ร่าง)');
    }

    public function show(CarbonActivity $carbonActivity)
    {
        $this->authorize('view', $carbonActivity);

        $carbonActivity->load(['household', 'recordedBy', 'calculation.emissionFactor', 'evidences']);

        return view('carbon-activities.show', ['activity' => $carbonActivity]);
    }

    public function submit(CarbonActivity $carbonActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $carbonActivity);

        $workflow->submit($carbonActivity, $request->user());

        return back()->with('status', 'ส่งข้อมูลเพื่อตรวจสอบแล้ว');
    }

    public function verify(CarbonActivity $carbonActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $carbonActivity);

        $workflow->verify($carbonActivity, $request->user());

        return back()->with('status', 'ตรวจสอบข้อมูลแล้ว รอการอนุมัติ');
    }

    public function approve(CarbonActivity $carbonActivity, Request $request, WorkflowService $workflow, CarbonCalculationService $calculator)
    {
        $this->authorize('view', $carbonActivity);

        try {
            $calculation = DB::transaction(function () use ($carbonActivity, $request, $workflow, $calculator) {
                $workflow->approve($carbonActivity, $request->user());

                return $calculator->calculateFor($carbonActivity->fresh());
            });
        } catch (CarbonCalculationException $e) {
            return back()->with('status', 'อนุมัติไม่สำเร็จ: '.$e->getMessage());
        }

        return back()->with('status', 'อนุมัติข้อมูลแล้ว และคำนวณปริมาณคาร์บอนเทียบเท่าเรียบร้อย ('.number_format((float) $calculation->co2e_kg, 2).' kgCO2e)');
    }

    public function reject(CarbonActivity $carbonActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $carbonActivity);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $workflow->reject($carbonActivity, $request->user(), $request->string('rejection_reason'));

        return back()->with('status', 'ตีกลับข้อมูลแล้ว');
    }
}
