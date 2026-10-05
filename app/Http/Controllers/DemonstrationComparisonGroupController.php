<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemonstrationComparisonGroupRequest;
use App\Models\CropSeason;
use App\Models\DemonstrationComparisonGroup;
use App\Models\Technology;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * F05 - Demonstration Plot experiment registry (see the
 * demonstration_comparison_groups migration's doc-comment). Not
 * household-scoped - a group is a project-wide research design, plots
 * from any area are enrolled into it via DemonstrationPlotController.
 */
class DemonstrationComparisonGroupController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', DemonstrationComparisonGroup::class);

        $groups = DemonstrationComparisonGroup::with(['cropSeason', 'technology'])
            ->withCount('demonstrationPlots')
            ->latest('id')
            ->paginate(20);

        return view('demonstration-comparison-groups.index', compact('groups'));
    }

    public function create()
    {
        $this->authorize('create', DemonstrationComparisonGroup::class);

        return view('demonstration-comparison-groups.create', [
            'cropSeasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
            'technologies' => Technology::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreDemonstrationComparisonGroupRequest $request)
    {
        $this->authorize('create', DemonstrationComparisonGroup::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $group = DemonstrationComparisonGroup::create($data);

        return redirect()->route('demonstration-comparison-groups.show', $group)
            ->with('status', 'สร้างชุดเปรียบเทียบเรียบร้อย ขั้นตอนต่อไป: เพิ่มแปลงเข้าร่วมจากหน้าแปลงแต่ละแปลง');
    }

    public function show(DemonstrationComparisonGroup $demonstrationComparisonGroup)
    {
        $this->authorize('view', $demonstrationComparisonGroup);

        $demonstrationComparisonGroup->load([
            'cropSeason', 'technology', 'createdBy',
            'demonstrationPlots.plot.farm.household',
            'demonstrationPlots.enrolledBy',
        ]);

        return view('demonstration-comparison-groups.show', [
            'group' => $demonstrationComparisonGroup,
        ]);
    }

    public function complete(DemonstrationComparisonGroup $demonstrationComparisonGroup)
    {
        $this->authorize('update', $demonstrationComparisonGroup);

        $demonstrationComparisonGroup->update(['status' => DemonstrationComparisonGroup::STATUS_COMPLETED]);

        return back()->with('status', 'ปิดชุดเปรียบเทียบนี้แล้ว (สถานะ: เสร็จสิ้น)');
    }
}
