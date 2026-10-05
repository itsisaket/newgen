<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchDisposalRecordRequest;
use App\Models\BranchDisposalRecord;
use App\Models\KilnBatch;
use App\Models\Plot;
use App\Services\AreaScopeService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * CFP - การจัดการกิ่ง/เศษไม้ตามเส้นทางกำจัด (kiln / เผา / ย่อยสลาย / ปุ๋ยหมัก / อื่น ๆ)
 * ดู doc-comment ของ migration 2026_09_24_000006 - Plot-locked create และ Workflow
 * (submit/verify/approve/reject ผ่าน WorkflowService) แบบเดียวกับ HarvestRecordController
 */
class BranchDisposalRecordController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', BranchDisposalRecord::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $records = BranchDisposalRecord::with(['plot.farm.household', 'kilnBatch', 'recordedBy'])
            ->whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereHas('plot.farm', fn ($f) => $f->whereIn('household_id', $householdIds)))
            ->latest('disposal_date')
            ->paginate(20);

        return view('branch-disposal-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $plot = Plot::with('farm.household')->find($request->integer('plot_id'));

        if (! $plot) {
            return redirect()
                ->route('plots.index')
                ->with('status', 'กรุณาเปิดหน้าแปลงที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกการจัดการกิ่ง" จากหน้านั้น');
        }

        $this->authorize('create', [BranchDisposalRecord::class, $plot]);

        return view('branch-disposal-records.create', [
            'plot' => $plot,
            'kilnBatches' => $this->kilnBatchesFor($plot),
        ]);
    }

    public function store(StoreBranchDisposalRecordRequest $request)
    {
        $plot = Plot::with('farm')->findOrFail($request->input('plot_id'));
        $this->authorize('create', [BranchDisposalRecord::class, $plot]);

        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        // เชื่อมเตาได้เฉพาะเส้นทาง "เข้าเตา" และต้องเป็นรอบเดินเตาของครัวเรือนเดียวกับแปลง
        if ($data['disposal_route'] !== BranchDisposalRecord::ROUTE_KILN) {
            $data['kiln_batch_id'] = null;
        } elseif (! empty($data['kiln_batch_id'])) {
            $sameHousehold = KilnBatch::whereKey($data['kiln_batch_id'])
                ->where('household_id', $plot->farm->household_id)
                ->exists();

            if (! $sameHousehold) {
                return back()->withInput()->withErrors(['kiln_batch_id' => 'รอบเดินเตานี้ไม่ใช่ของครัวเรือนเดียวกับแปลง']);
            }
        }

        $record = BranchDisposalRecord::create($data);

        return redirect()
            ->route('branch-disposal-records.show', $record)
            ->with('status', 'บันทึกการจัดการกิ่งเรียบร้อย (สถานะ: ร่าง)');
    }

    public function show(BranchDisposalRecord $branchDisposalRecord)
    {
        $this->authorize('view', $branchDisposalRecord);

        $branchDisposalRecord->load(['plot.farm.household', 'kilnBatch', 'recordedBy', 'evidences']);

        return view('branch-disposal-records.show', ['record' => $branchDisposalRecord]);
    }

    public function submit(BranchDisposalRecord $branchDisposalRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $branchDisposalRecord);

        $workflow->submit($branchDisposalRecord, $request->user());

        return back()->with('status', 'ส่งข้อมูลเพื่อตรวจสอบแล้ว');
    }

    public function verify(BranchDisposalRecord $branchDisposalRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $branchDisposalRecord);

        $workflow->verify($branchDisposalRecord, $request->user());

        return back()->with('status', 'ตรวจสอบข้อมูลแล้ว รอการอนุมัติ');
    }

    public function approve(BranchDisposalRecord $branchDisposalRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $branchDisposalRecord);

        $workflow->approve($branchDisposalRecord, $request->user());

        return back()->with('status', 'อนุมัติข้อมูลแล้ว (ล็อกการแก้ไข)');
    }

    public function reject(BranchDisposalRecord $branchDisposalRecord, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $branchDisposalRecord);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $workflow->reject($branchDisposalRecord, $request->user(), $request->string('rejection_reason'));

        return back()->with('status', 'ตีกลับข้อมูลแล้ว');
    }

    private function kilnBatchesFor(Plot $plot)
    {
        return KilnBatch::where('household_id', $plot->farm->household_id)
            ->whereNull('original_record_id')
            ->orderByDesc('batch_date')
            ->limit(50)
            ->get(['id', 'batch_code', 'batch_date', 'biomass_input_kg']);
    }
}
