<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFarmActivityRequest;
use App\Models\ActivityType;
use App\Models\CropSeason;
use App\Models\FarmActivity;
use App\Models\Material;
use App\Models\Plot;
use App\Services\AreaScopeService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F13 - Farm Activity Log (Blueprint section 7.2). Cost fields
 * (material qty x unit_cost, labor hours x labor rate) are summed into
 * total_cost here at save time; farm_production_costs (F02) is a
 * later rollup built from these rows by a Service, not entered by hand.
 *
 * Authorization (15 ก.ย. round): see FarmActivityPolicy.
 *
 * Sequential-entry hardening (15 ก.ย. round 2): see FarmController's
 * doc-comment - same treatment here (แปลง -> กิจกรรมสวน). There is no
 * update() action at all on this controller (see routes/web.php - only
 * index/create/store/show), so plot_id can never be re-pointed after
 * creation the way household_id/farm_id used to be able to on Farm/Plot -
 * nothing extra was needed there.
 */
class FarmActivityController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', FarmActivity::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $activities = FarmActivity::with(['plot.farm.household', 'activityType', 'material', 'recordedBy'])
            ->whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereHas('plot.farm', fn ($f) => $f->whereIn('household_id', $householdIds)))
            ->latest('activity_date')
            ->paginate(20);

        return view('farm-activities.index', compact('activities'));
    }

    /**
     * No free plot dropdown here on purpose - a farm activity may only be
     * logged starting from a specific plot's page (plots/show -> "+
     * บันทึกกิจกรรม", which passes ?plot_id=) or from the auto-chain
     * registration flow (plots.registration-complete), both of which
     * already pass plot_id. Arriving without one bounces back to the plot
     * list with guidance instead of showing a form with no context.
     */
    public function create(Request $request)
    {
        $plot = Plot::with('farm.household')->find($request->integer('plot_id'));

        if (! $plot) {
            return redirect()
                ->route('plots.index')
                ->with('status', 'กรุณาเปิดหน้าแปลงที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกกิจกรรม" จากหน้านั้น (บันทึกกิจกรรมแบบแยกอิสระไม่ได้แล้ว เพื่อป้องกันความสับสนของลำดับ แปลง → กิจกรรมสวน)');
        }

        $this->authorize('create', [FarmActivity::class, $plot]);

        return view('farm-activities.create', [
            'plot' => $plot,
            'cropSeasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
            'activityTypes' => ActivityType::orderBy('category')->get(),
            'materials' => Material::orderBy('name')->get(),
        ]);
    }

    public function store(StoreFarmActivityRequest $request)
    {
        $plot = Plot::with('farm')->findOrFail($request->input('plot_id'));
        $this->authorize('create', [FarmActivity::class, $plot]);

        $data = $request->validated();
        $data['total_cost'] = (float) ($data['quantity'] ?? 0) * (float) ($data['unit_cost'] ?? 0)
            + (float) ($data['labor_cost'] ?? 0);
        $data['recorded_by'] = $request->user()->id;

        $activity = FarmActivity::create($data);

        return redirect()
            ->route('farm-activities.show', $activity)
            ->with('status', 'บันทึกกิจกรรมสวนเรียบร้อย (สถานะ: ร่าง)');
    }

    public function show(FarmActivity $farmActivity)
    {
        $this->authorize('view', $farmActivity);

        $farmActivity->load(['plot.farm.household', 'cropSeason', 'activityType', 'material', 'recordedBy', 'evidences']);

        return view('farm-activities.show', ['activity' => $farmActivity]);
    }

    public function submit(FarmActivity $farmActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $farmActivity);

        $workflow->submit($farmActivity, $request->user());

        return back()->with('status', 'ส่งข้อมูลเพื่อตรวจสอบแล้ว');
    }

    public function verify(FarmActivity $farmActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $farmActivity);

        $workflow->verify($farmActivity, $request->user());

        return back()->with('status', 'ตรวจสอบข้อมูลแล้ว รอการอนุมัติ');
    }

    public function approve(FarmActivity $farmActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $farmActivity);

        $workflow->approve($farmActivity, $request->user());

        return back()->with('status', 'อนุมัติข้อมูลแล้ว (ล็อกการแก้ไข)');
    }

    public function reject(FarmActivity $farmActivity, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $farmActivity);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $workflow->reject($farmActivity, $request->user(), $request->string('rejection_reason'));

        return back()->with('status', 'ตีกลับข้อมูลแล้ว');
    }
}
