<?php

namespace App\Http\Controllers;

use App\Models\BranchDisposalRecord;
use App\Models\CfpCalculation;
use App\Models\FarmActivity;
use App\Models\HarvestRecord;
use App\Models\Plot;
use App\Models\PlotProductionCycle;
use App\Models\Role;
use App\Services\AreaScopeService;
use App\Services\CfpCalculationService;
use App\Support\WorkflowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * CFP (ถึงประตูสวน) - หน้าจอรอบการผลิต + คำนวณ + ผลลัพธ์/Sensitivity
 * สิทธิ์: ดูได้ทุกบทบาทที่ไม่ใช่ read-only ภายในขอบเขตครัวเรือน (AreaScopeService);
 * เปิด/ปิดรอบและคำนวณได้เฉพาะเจ้าหน้าที่/นักวิจัย (Role::STAFF)
 */
class CfpController extends Controller
{
    public function __construct(private AreaScopeService $areaScope) {}

    public function index()
    {
        $this->ensureCanView();
        $ids = $this->areaScope->householdIdsFor(auth()->user());

        $plots = Plot::with('farm.household')
            ->when($ids !== null, fn ($q) => $q->whereHas('farm', fn ($f) => $f->whereIn('household_id', $ids)))
            ->orderBy('plot_code')
            ->paginate(25);

        $latest = CfpCalculation::whereIn('plot_id', $plots->pluck('id'))
            ->orderBy('period_end')->get()->groupBy('plot_id')->map->last();
        $openCycles = PlotProductionCycle::whereIn('plot_id', $plots->pluck('id'))
            ->where('status', PlotProductionCycle::STATUS_OPEN)->get()->keyBy('plot_id');

        return view('cfp.index', compact('plots', 'latest', 'openCycles'));
    }

    public function plot(Plot $plot)
    {
        $this->authorizePlot($plot);
        $plot->load('farm.household');

        $cycles = PlotProductionCycle::where('plot_id', $plot->id)->orderByDesc('start_date')->get();
        $calcs = CfpCalculation::where('plot_id', $plot->id)->latest('period_end')->get();
        $open = $cycles->firstWhere('status', PlotProductionCycle::STATUS_OPEN);

        // ข้อเสนอวันเริ่มรอบแรก = วันถัดจากเก็บเกี่ยวสุดท้าย (approved) ของฤดู 2568 (ก่อน 1 ม.ค. 2569)
        $suggest = null;
        if ($cycles->isEmpty()) {
            $last = HarvestRecord::where('plot_id', $plot->id)->whereNull('original_record_id')
                ->where('status', WorkflowStatus::APPROVED)
                ->where('harvest_date', '<', '2026-01-01')->max('harvest_date');
            $suggest = $last ? Carbon::parse($last)->addDay()->toDateString() : null;
        }

        // ข้อเสนอวันปิดรอบ = เก็บเกี่ยวสุดท้าย (approved) นับจากต้นรอบเปิด
        $closeSuggest = null;
        if ($open) {
            $closeSuggest = HarvestRecord::where('plot_id', $plot->id)->whereNull('original_record_id')
                ->where('status', WorkflowStatus::APPROVED)
                ->where('harvest_date', '>=', $open->start_date->toDateString())->max('harvest_date');
        }

        // รายการที่ยังไม่อนุมัติในรอบที่เปิดอยู่ - ยังไม่ถูกนำมาคิด CFP
        $pending = null;
        if ($open) {
            $from = $open->start_date->toDateString();
            $notApproved = fn ($m, $col) => $m::where('plot_id', $plot->id)->whereNull('original_record_id')
                ->where('status', '!=', WorkflowStatus::APPROVED)->where($col, '>=', $from)->count();
            $pending = [
                'กิจกรรมสวน' => $notApproved(FarmActivity::class, 'activity_date'),
                'ผลผลิตที่เก็บ' => $notApproved(HarvestRecord::class, 'harvest_date'),
                'การจัดการกิ่ง' => $notApproved(BranchDisposalRecord::class, 'disposal_date'),
            ];
        }

        return view('cfp.plot', [
            'pending' => $pending,
            'plot' => $plot, 'cycles' => $cycles, 'calcs' => $calcs, 'open' => $open,
            'suggest' => $suggest, 'closeSuggest' => $closeSuggest,
            'canManage' => $this->canManage(),
        ]);
    }

    public function openCycle(Request $request, Plot $plot)
    {
        $this->authorizePlot($plot, true);
        $data = $request->validate(['start_date' => ['required', 'date']]);

        if (PlotProductionCycle::where('plot_id', $plot->id)->where('status', 'open')->exists()) {
            return back()->withErrors(['start_date' => 'แปลงนี้มีรอบที่เปิดอยู่แล้ว ต้องปิดรอบเดิมก่อน']);
        }
        $after = PlotProductionCycle::where('plot_id', $plot->id)->max('end_date');
        if ($after && Carbon::parse($data['start_date'])->lte(Carbon::parse($after))) {
            return back()->withErrors(['start_date' => 'วันเริ่มต้องหลังวันสิ้นสุดของรอบก่อนหน้า ('.$after.')']);
        }

        PlotProductionCycle::create(['plot_id' => $plot->id, 'start_date' => $data['start_date'], 'status' => 'open']);

        return back()->with('status', 'เปิดรอบการผลิตแล้ว');
    }

    public function closeCycle(Request $request, Plot $plot, PlotProductionCycle $cycle)
    {
        $this->authorizePlot($plot, true);
        abort_unless($cycle->plot_id === $plot->id && ! $cycle->isClosed(), 404);

        $data = $request->validate([
            'last_harvest_date' => ['required', 'date', 'after_or_equal:'.$cycle->start_date->toDateString()],
            'close_basis' => ['required', 'in:actual_last_harvest,standard_cutoff'],
        ]);

        $maxApproved = HarvestRecord::where('plot_id', $plot->id)->whereNull('original_record_id')
            ->where('status', WorkflowStatus::APPROVED)
            ->where('harvest_date', '>=', $cycle->start_date->toDateString())->max('harvest_date');
        if ($maxApproved && Carbon::parse($data['last_harvest_date'])->lt(Carbon::parse($maxApproved))) {
            return back()->withErrors(['last_harvest_date' => "มีการเก็บเกี่ยวที่อนุมัติแล้วเมื่อ {$maxApproved} วันปิดรอบต้องไม่ก่อนหน้านั้น"]);
        }

        $cycle->update([
            'end_date' => $data['last_harvest_date'], 'last_harvest_date' => $data['last_harvest_date'],
            'close_basis' => $data['close_basis'], 'status' => PlotProductionCycle::STATUS_CLOSED,
            'closed_by' => $request->user()->id, 'closed_at' => now(),
        ]);
        PlotProductionCycle::create([
            'plot_id' => $plot->id,
            'start_date' => Carbon::parse($data['last_harvest_date'])->addDay()->toDateString(),
            'status' => 'open',
        ]);

        return back()->with('status', 'ปิดรอบแล้ว และเปิดรอบถัดไปให้อัตโนมัติ');
    }

    public function calculate(Request $request, Plot $plot, CfpCalculationService $service)
    {
        $this->authorizePlot($plot, true);
        $data = $request->validate([
            'cycle_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $cycle = null;
        if (! empty($data['cycle_id'])) {
            $cycle = PlotProductionCycle::where('plot_id', $plot->id)->findOrFail($data['cycle_id']);
            $start = $cycle->start_date;
            $end = $cycle->end_date;
        } elseif (! empty($data['start_date'])) {
            $start = Carbon::parse($data['start_date']);
            $end = ! empty($data['end_date']) ? Carbon::parse($data['end_date']) : null;
        } else {
            return back()->withErrors(['cycle_id' => 'เลือกรอบการผลิต หรือระบุวันเริ่ม']);
        }

        $out = $service->calculate($plot, $start, $end, $cycle, $request->user(), true);
        if (! $out['model']) {
            return back()->withErrors(['cycle_id' => 'ไม่สามารถบันทึกผลได้ (อาจมีผลที่ตรวจสอบแล้วในช่วงเดียวกัน)']);
        }

        return redirect()->route('cfp.show', $out['model'])->with('status', 'คำนวณ CFP แล้ว');
    }

    public function show(CfpCalculation $calculation)
    {
        $plot = $calculation->plot()->with('farm.household')->firstOrFail();
        $this->authorizePlot($plot);

        return view('cfp.show', ['calc' => $calculation, 'plot' => $plot]);
    }

    private function canManage(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(Role::STAFF);
    }

    private function ensureCanView(): void
    {
        abort_if(! auth()->user() || auth()->user()->hasAnyRole(Role::READ_ONLY), 403);
    }

    private function authorizePlot(Plot $plot, bool $manage = false): void
    {
        $this->ensureCanView();
        $plot->loadMissing('farm');
        abort_unless($this->areaScope->canAccessHousehold(auth()->user(), $plot->farm->household_id), 403);
        abort_if($manage && ! $this->canManage(), 403);
    }
}
