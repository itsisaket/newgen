<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHarvestRecordRequest;
use App\Models\Buyer;
use App\Models\CropSeason;
use App\Models\HarvestRecord;
use App\Models\Plot;
use App\Services\AreaScopeService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F08/F14-lite - Harvest recording (Blueprint Appendix C.2 harvest_records:
 * "ใช้ตรวจ Forecast Accuracy"). Plot-locked create exactly like
 * FarmActivityController (see that controller's doc-comment) - a harvest
 * can only be logged from a specific plot's page, never a free dropdown.
 * Unlike F13, this DOES go through WorkflowService (submit/verify/approve/
 * reject) - the table already had a `status` column from Sprint 1 for
 * exactly this, and Blueprint 16.1 says every F01-F15 module should share
 * the one state machine rather than inventing a simplified flow per
 * module. Cost/kg in ProductionCostService (F02) only ever counts
 * `approved` rows for exactly this reason - see that Service's doc-comment.
 */
class HarvestRecordController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', HarvestRecord::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $records = HarvestRecord::with(['plot.farm.household', 'buyer', 'recordedBy'])
            ->whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereHas('plot.farm', fn ($f) => $f->whereIn('household_id', $householdIds)))
            ->latest('harvest_date')
            ->paginate(20);

        return view('harvest-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $plot = Plot::with('farm.household')->find($request->integer('plot_id'));

        if (! $plot) {
            return redirect()
                ->route('plots.index')
                ->with('status', 'กรุณาเปิดหน้าแปลงที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกผลผลิต" จากหน้านั้น');
        }

        $this->authorize('create', [HarvestRecord::class, $plot]);

        return view('harvest-records.create', [
            'plot' => $plot,
            'cropSeasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
            'buyers' => Buyer::orderBy('name')->get(),
        ]);
    }

    public function store(StoreHarvestRecordRequest $request)
    {
        $plot = Plot::with('farm')->findOrFail($request->input('plot_id'));
        $this->authorize('create', [HarvestRecord::class, $plot]);

        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        $record = HarvestRecord::create($data);

        return redirect()
            ->route('harvest-records.show', $record)
            ->with('status', 'บันทึกผลผลิตเรียบร้อย (สถานะ: ร่าง)');
    }

    public function show(HarvestRecord $harvestRecord)
    {
        $this->authorize('view', $harvestRecord);

        $harvestRecord->load(['plot.farm.household', 'cropSeason', 'buyer', 'recordedBy', 'evidences']);

        return view('harvest-records.show', ['record' => $harvestRecord]);
    }

    public function submit(HarvestRecord $harvestRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $harvestRecord);

        $workflow->submit($harvestRecord, $request->user());

        return back()->with('status', 'ส่งข้อมูลเพื่อตรวจสอบแล้ว');
    }

    public function verify(HarvestRecord $harvestRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $harvestRecord);

        $workflow->verify($harvestRecord, $request->user());

        return back()->with('status', 'ตรวจสอบข้อมูลแล้ว รอการอนุมัติ');
    }

    public function approve(HarvestRecord $harvestRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $harvestRecord);

        $workflow->approve($harvestRecord, $request->user());

        return back()->with('status', 'อนุมัติข้อมูลแล้ว (ล็อกการแก้ไข)');
    }

    public function reject(HarvestRecord $harvestRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $harvestRecord);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $workflow->reject($harvestRecord, $request->user(), $request->string('rejection_reason'));

        return back()->with('status', 'ตีกลับข้อมูลแล้ว');
    }
}
