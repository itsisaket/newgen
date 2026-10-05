<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlotRequest;
use App\Http\Requests\UpdatePlotRequest;
use App\Models\DurianVariety;
use App\Models\Farm;
use App\Models\Plot;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Plot registration (Blueprint section 7.1). Master/reference data - see
 * HouseholdController for the same reasoning (no workflow here).
 *
 * Authorization (15 ก.ย. round): see PlotPolicy/HouseholdPolicy.
 *
 * Sequential-entry hardening (15 ก.ย. round 2): see FarmController's
 * doc-comment - same treatment here one level down (สวน -> แปลง).
 */
class PlotController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', Plot::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $plots = Plot::with(['farm.household', 'durianVariety'])
            ->when($householdIds !== null, fn ($q) => $q->whereHas('farm', fn ($f) => $f->whereIn('household_id', $householdIds)))
            ->orderBy('plot_code')
            ->paginate(20);

        return view('plots.index', compact('plots'));
    }

    /**
     * No free farm dropdown here on purpose - a plot may only be created
     * starting from a specific farm's page (farms/show -> "+ เพิ่มแปลง",
     * which passes ?farm_id=). Arriving without a valid farm_id bounces
     * back to the farm list with guidance instead of showing a form with
     * no context.
     */
    public function create(Request $request)
    {
        $farm = Farm::with('household')->find($request->integer('farm_id'));

        if (! $farm) {
            return redirect()
                ->route('farms.index')
                ->with('status', 'กรุณาเปิดหน้าสวนที่ต้องการก่อน แล้วกดปุ่ม "+ เพิ่มแปลง" จากหน้านั้น (เพิ่มแปลงแบบแยกอิสระไม่ได้แล้ว เพื่อป้องกันความสับสนของลำดับ สวน → แปลง)');
        }

        $this->authorize('create', [Plot::class, $farm]);

        return view('plots.create', [
            'farm' => $farm,
            'varieties' => DurianVariety::orderBy('name')->get(),
        ]);
    }

    public function store(StorePlotRequest $request)
    {
        $farm = Farm::findOrFail($request->input('farm_id'));
        $this->authorize('create', [Plot::class, $farm]);

        $plot = Plot::create($request->validated());

        // Auto-chaining registration flow - see HouseholdController::store().
        // Plot is the last step of household/farm/plot registration itself,
        // so land on a "what's next" page offering the research tools
        // (F01/F13) instead of a bare show page.
        return redirect()
            ->route('plots.registration-complete', $plot)
            ->with('status', 'เพิ่มแปลงเรียบร้อยแล้ว ขั้นตอนต่อไป: เลือกเครื่องมือวิจัย');
    }

    public function show(Plot $plot)
    {
        $this->authorize('view', $plot);

        $plot->load(['farm.household', 'durianVariety', 'demonstrationPlots.comparisonGroup']);

        $recentActivities = $plot->farmActivities()
            ->with('activityType')
            ->latest('activity_date')
            ->limit(5)
            ->get();

        // F14/F08-lite (see PhenologyRecordController/HarvestRecordController).
        $recentPhenology = $plot->phenologyRecords()
            ->latest('observed_date')
            ->limit(5)
            ->get();

        $recentHarvests = $plot->harvestRecords()
            ->whereNull('original_record_id')
            ->latest('harvest_date')
            ->limit(5)
            ->get();

        // CFP - การจัดการกิ่ง/เศษไม้ (see BranchDisposalRecordController).
        $recentBranchDisposals = $plot->branchDisposalRecords()
            ->whereNull('original_record_id')
            ->latest('disposal_date')
            ->limit(5)
            ->get();

        // F04 Bio-product Utilization (see ProductUsageController) - product
        // consumption logged against this plot (e.g. compost/biochar applied).
        $recentProductUsages = $plot->productUsages()
            ->with('inventoryTransaction.product')
            ->latest('application_date')
            ->limit(5)
            ->get();

        return view('plots.show', compact('plot', 'recentActivities', 'recentPhenology', 'recentHarvests', 'recentProductUsages', 'recentBranchDisposals'));
    }

    /**
     * Step 4 of the auto-chaining registration flow: after a plot is
     * created, offer the two research-tool entry points (F01/F13) with
     * this household/plot pre-selected, rather than dropping the user on
     * the plain plot show page.
     */
    public function registrationComplete(Plot $plot)
    {
        $this->authorize('view', $plot);

        $plot->load('farm.household');

        return view('plots.registration-complete', compact('plot'));
    }

    public function edit(Plot $plot)
    {
        $this->authorize('update', $plot);

        $plot->load('farm.household');

        return view('plots.edit', [
            'plot' => $plot,
            'varieties' => DurianVariety::orderBy('name')->get(),
        ]);
    }

    /**
     * farm_id is no longer accepted here at all (see UpdatePlotRequest -
     * it has no rule for that key) - a plot can never be moved to a
     * different farm after creation, so there is nothing left to re-check
     * against a target farm the way this used to.
     */
    public function update(UpdatePlotRequest $request, Plot $plot)
    {
        $this->authorize('update', $plot);

        $plot->update($request->validated());

        return redirect()
            ->route('plots.show', $plot)
            ->with('status', 'บันทึกข้อมูลแปลงเรียบร้อยแล้ว');
    }
}
