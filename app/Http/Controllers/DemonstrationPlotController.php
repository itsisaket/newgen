<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemonstrationPlotRequest;
use App\Models\DemonstrationComparisonGroup;
use App\Models\DemonstrationPlot;
use App\Models\Plot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F05 - enrolls one Plot into one DemonstrationComparisonGroup as
 * treatment/control. Plot-locked create like FarmActivityController -
 * arrives from plots/show -> "+ เพิ่มเข้าแปลงสาธิต" (?plot_id=), not a free
 * dropdown, so the area-scope check always has a concrete household to
 * check against.
 */
class DemonstrationPlotController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request)
    {
        $plot = Plot::with('farm.household')->find($request->integer('plot_id'));

        if (! $plot) {
            return redirect()
                ->route('plots.index')
                ->with('status', 'กรุณาเปิดหน้าแปลงที่ต้องการก่อน แล้วกดปุ่ม "+ เพิ่มเข้าแปลงสาธิต" จากหน้านั้น');
        }

        $this->authorize('create', [DemonstrationPlot::class, $plot]);

        $groups = DemonstrationComparisonGroup::where('status', DemonstrationComparisonGroup::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->get(['id', 'name']);

        return view('demonstration-plots.create', [
            'plot' => $plot,
            'groups' => $groups,
        ]);
    }

    public function store(StoreDemonstrationPlotRequest $request)
    {
        $plot = Plot::with('farm')->findOrFail($request->input('plot_id'));
        $this->authorize('create', [DemonstrationPlot::class, $plot]);

        $data = $request->validated();
        $data['enrolled_by'] = $request->user()->id;

        $enrollment = DemonstrationPlot::create($data);

        return redirect()
            ->route('demonstration-comparison-groups.show', $enrollment->comparison_group_id)
            ->with('status', 'เพิ่มแปลงเข้าชุดเปรียบเทียบเรียบร้อยแล้ว');
    }

    public function end(DemonstrationPlot $demonstrationPlot)
    {
        $this->authorize('create', [DemonstrationPlot::class, $demonstrationPlot->plot]);

        $demonstrationPlot->update(['ended_at' => now()->toDateString()]);

        return back()->with('status', 'สิ้นสุดการเข้าร่วมชุดเปรียบเทียบของแปลงนี้แล้ว');
    }
}
